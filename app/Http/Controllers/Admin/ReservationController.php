<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Shop;
use Carbon\Carbon;
use App\Models\CalenderMark;
use App\Services\LineNotificationService;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    protected $lineService;
    public function __construct(LineNotificationService $lineService)
    {
        $this->lineService = $lineService;
    }

    public function create()
    {
        return view('admin.create');
    }

    public function calender(Request $request)
    {
        $startOffset = (int) $request->query('start_offset', 0);
        $baseDate = Carbon::today()->copy()->addDays($startOffset);

        $dates = collect();
        for ($i = 0; $i < 14; $i++) {
            $dates->push($baseDate->copy()->addDays($i));
        }

        $menuDurations = [
            '一般 4600円' => 60,
            'カットのみ 3500円' => 60,
            '高校生 3600円' => 60,
            '中学生 3100円' => 60,
            '小学生 2700円' => 60,
            'ノーマル 9500円〜' => 180,
            'ピンパーマ 13500円〜' => 180,
            'スパイラル 13500円〜' => 180,
            'ブリーチ 5500円〜（2回目以降から+4500円ずつ）' => 150,
            'ノーマルカラー 5000円〜' => 150,
            'グレイカラー 2300円〜' => 150,
        ];

        $admin = Auth::guard('admin')->user();

        $menu = $request->menu;
        $duration = $menuDurations[$menu] ?? 60;

        $shop = $admin->shop;
        $closedDays = explode(',', $shop->closed_days ?? '');
        $closedDayIndexes = collect(['日', '月', '火', '水', '木', '金', '土'])
            ->filter(fn($d) => in_array($d, $closedDays))
            ->keys()
            ->toArray();

        $startDate = $dates->first()->copy()->startOfDay();
        $endDate = $dates->last()->copy()->endOfDay();
        $confirmedReservations = Reservation::where('shop_id', $shop->id) // ✅ 追加
            ->whereBetween('reserved_at', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->get();

        $calenderMarks = CalenderMark::where('shop_id', $shop->id) // ✅ 追加
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy(function ($mark) {
                return $mark->date . ' ' . \Carbon\Carbon::parse($mark->time)->format('H:i');
            });

        return view('admin.admin_calender', [
            'dates' => $dates,
            'startOffset' => $startOffset,
            'name' => $request->name,
            'phone' => $request->phone,
            'category' => $request->category,
            'menu' => $menu,
            'duration' => $duration,
            'closedDays' => $closedDayIndexes,
            'lunchStart' => $shop->break_start,
            'lunchEnd' => $shop->break_end,
            'businessStart' => $shop->business_start,
            'businessEnd' => $shop->business_end,
            'shopPhone' => $shop->phone,
            'note' => $request->note,
            'confirmedReservations' => $confirmedReservations,
            'menuDurations' => $menuDurations,
            'calenderMarks' => $calenderMarks,
        ]);
    }

    // POST：確認画面へ進むときの入力データ保存
    public function postConfirmation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'category' => 'required|string|max:255',
            'menu' => 'required|string|max:255',
            'reserved_at' => 'required|date',
            'note' => 'nullable|string|max:1000',
        ]);

        $validated['note'] = $request->input('note');

        session()->put('reservation_data', $validated);

        return redirect()->route('admin.reservations.confirmation');
    }

    // GET：セッションから確認画面表示
    public function getConfirmation()
    {
        $data = session('reservation_data');

        if (!$data) {
            return redirect()->route('admin.reservations.create')->withErrors(['message' => '予約情報が見つかりません。']);
        }

        return view('admin.admin_confirmation', $data);
    }

    // POST：予約をDBに保存
    public function store()
    {
        $data = session('reservation_data');

        if (!$data) {
            return redirect()->route('admin.reservations.create')->withErrors(['message' => '再度ご入力ください。']);
        }

        $admin = Auth::guard('admin')->user();

        // 重複チェック
        $exists = Reservation::where('reserved_at', $data['reserved_at'])
            ->where('status', '!=', 'canceled')
            ->exists();

        if ($exists) {
            return redirect()->route('admin.reservations.confirmation')
                ->withErrors(['reserved_at' => 'この時間はすでに予約が入っています。別の時間でお試しください。']);
        }

        $user = User::firstOrCreate(
            [
                'phone'   => $data['phone'],
                'shop_id' => $admin->shop_id,
            ],
            [
                'name'         => $data['name'],
                'line_user_id' => null,
                'shop_id'      => $admin->shop_id,
            ]
        );

        // メニューごとの基本施術時間
        $menuDurations = [
            '一般 4600円' => 60,
            'カットのみ 3500円' => 60,
            '高校生 3600円' => 60,
            '中学生 3100円' => 60,
            '小学生 2700円' => 60,
            'ノーマル 9500円〜' => 180,
            'ピンパーマ 13500円〜' => 180,
            'スパイラル 13500円〜' => 180,
            'ブリーチ 5500円〜（2回目以降から+4500円ずつ）' => 150,
            'ノーマルカラー 5000円〜' => 150,
            'グレイカラー 2300円〜' => 150,
        ];

        $baseDuration = $menuDurations[$data['menu']] ?? 60;
        $reservedAt = Carbon::parse($data['reserved_at']);

        // 連続予約チェック: 直前の予約を検索
        $previousReservation = Reservation::where('shop_id', $admin->shop_id)
            ->where('status', 'confirmed')
            ->whereDate('reserved_at', $reservedAt->toDateString())
            ->get()
            ->first(function ($res) use ($reservedAt, $menuDurations) {
                $resDuration = $res->duration ?? ($menuDurations[$res->menu] ?? 60);
                $resEnd = Carbon::parse($res->reserved_at)->addMinutes($resDuration);
                return $resEnd->equalTo($reservedAt);
            });

        // 直前の予約が存在 & 延長されていない場合のみ+30分
        $shouldExtend = $previousReservation && !$previousReservation->is_extended;
        $finalDuration = $shouldExtend ? $baseDuration + 30 : $baseDuration;

        DB::transaction(function () use ($user, $data, $admin, $finalDuration, $shouldExtend) {
            Reservation::create([
                'user_id' => $user->id,
                'shop_id' => $admin->shop_id,
                'category' => $data['category'],
                'menu' => $data['menu'],
                'reserved_at' => $data['reserved_at'],
                'status' => 'confirmed',
                'note' => $data['note'] ?? null,
                'duration' => $finalDuration,
                'is_extended' => $shouldExtend,
            ]);
        });

        session()->forget('reservation_data');

        return redirect()->route('admin.dashboard')->with('status', '予約を追加しました');
    }

    public function cancel($id)
    {
        $reservation = Reservation::findOrFail($id);

        // 共通メソッドでキャンセル＋通知
        $reservation->cancelWithNotification($this->lineService);

        return redirect()->route('admin.dashboard')
            ->with('status', '予約をキャンセルしました');
    }

    public function json()
    {
        $admin = Auth::guard('admin')->user();
        $shopId = $admin->shop_id;

        // 🔽 確定済み予約のみ取得
        $reservations = \App\Models\Reservation::where('reservations.shop_id', $shopId)
            ->where('reservations.status', 'confirmed')
            ->join('users', 'reservations.user_id', '=', 'users.id')
            ->select(
                'reservations.id',
                'users.name as user_name',
                'reservations.menu',
                'reservations.reserved_at'
            )
            ->get();

        $events = $reservations->map(fn($r) => [
            'id' => $r->id,
            'title' => $r->user_name . '（' . $r->menu . '）',
            'start' => $r->reserved_at,
        ]);

        return response()->json($events);
    }

}
