<?php

namespace App\Http\Controllers\Reserve;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reservation;
use App\Services\LineNotificationService;
use Carbon\Carbon;
use App\Models\Shop;
use App\Models\CalenderMark;
use Illuminate\Support\Str;

class ReservationFormController extends Controller
{
    protected $lineService;

    public function __construct(LineNotificationService $lineService)
    {
        $this->lineService = $lineService;
    }

    public function create(Request $request, $token)
    {
        $shop = Shop::where('public_token', $token)->firstOrFail();
        $lineUserId = $request->query('line_user_id');

        return view('reserve.form', [
            'lineUserId' => $lineUserId,
            'shop' => $shop,
        ]);
    }

    public function store(Request $request, $token)
    {

        \Log::debug('予約リクエスト受信', $request->all());

        $validated = $request->validate([
            'line_user_id' => 'required|string',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'category' => 'required|string|max:255',
            'menu' => 'required|string|max:255',
            'reserved_at' => 'required|date',
        ]);

        $shop = Shop::where('public_token', $token)->firstOrFail();

        // ユーザー情報を保存 or 更新
        $user = User::updateOrCreate(
            ['line_user_id' => $validated['line_user_id']],
            ['name' => $validated['name'], 'phone' => $validated['phone']]
        );

        // 予約を作成（line_tokenを生成）
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'category' => $validated['category'],
            'menu' => $validated['menu'],
            'reserved_at' => $validated['reserved_at'],
            'status' => 'confirmed',
            'line_token' => Str::random(40),
            'line_user_id' => $validated['line_user_id'],
        ]);

        // LINE通知内容の作成
        $url = route('reserve.verify') . '?token=' . $reservation->line_token;

        \Log::info('生成された予約確認URL', ['url' => $url]);

        $message = "✅ ご予約ありがとうございます！\n\n"
                . "📅 日時：{$reservation->reserved_at->format('Y年m月d日 H:i')}\n"
                . "✂️ メニュー：{$reservation->menu}\n\n"
                . "▼ ご確認・キャンセルはこちら：\n{$url}";

        // LINE通知送信（ユーザー／管理者）
        $this->lineService->notifyUser($user->line_user_id, $message);
        $this->lineService->notifyAdmin("新しい予約が入りました！\nメニュー: {$reservation->menu}\n日時: {$reservation->reserved_at}");

        // セッション保存（フォーム戻りなどに使用）
        session(['line_user_id' => $validated['line_user_id']]);

        // 完了画面へ（$reservationを渡す）
        return view('reserve.complete', [
            'reservation' => $reservation,
        ]);

    }

    public function confirm(Request $request)
    {
        $lineUserId = $request->query('line_user_id');
        $user = User::where('line_user_id', $lineUserId)->first();

        if (!$user) {
            return redirect()->route('reserve.form', ['token' => $request->query('token')])->with('status', '予約情報が見つかりませんでした。');
        }

        $reservations = $user->reservations()->with('shop')->orderByDesc('reserved_at')->get();

        return view('reserve.confirm', compact('reservations', 'lineUserId'));
    }

    public function cancel(Request $request)
    {
        $reservation = Reservation::where('line_token', $request->line_token)
            ->firstOrFail();

        $reservation->cancelWithNotification($this->lineService);

        return redirect()->route('reserve.verify', ['token' => $reservation->line_token])
        ->with('status', '予約をキャンセルしました');
    }

    public function calender(Request $request, $token)
    {
        {
            if (!$request->filled(['line_user_id', 'name', 'phone', 'category', 'menu'])) {
                return redirect()->route('reserve.form')->with('status', '必要な情報が不足しています。');
            }
        }

        $startOffset = (int) $request->query('start_offset', 0);
        $baseDate = Carbon::today()->copy()->addDays($startOffset);

        $dates = collect();
        for ($i = 0; $i < 14; $i++) {
            $dates->push($baseDate->copy()->addDays($i));
        }

        $menuDurations = [
            '一般 4500円' => 60,
            'カットのみ 3500円' => 60,
            '高校生 3600円' => 60,
            '中学生 3100円' => 60,
            '小学生 2700円' => 60,
            'ノーマル 9500円〜' => 180,
            'ピンパーマ 13500円〜' => 180,
            'スパイラル 13500円〜' => 180,
            'ブリーチ 5500円（2回目以降から+4500円ずつ）' => 120,
            'ノーマルカラー 5000円' => 120,
            'グレイカラー 2300円' => 120,
        ];

        $menu = $request->menu;
        $duration = $menuDurations[$menu] ?? 60;

        $shop = Shop::where('public_token', $token)->firstOrFail();
        $shopId = $shop->id;
        $closedDays = explode(',', $shop->closed_days ?? '');
        $closedDayIndexes = collect(['日','月','火','水','木','金','土'])
            ->filter(fn($d) => in_array($d, $closedDays))
            ->keys()
            ->toArray();

        $startDate = $dates->first()->copy()->startOfDay();
        $endDate = $dates->last()->copy()->endOfDay();

        $confirmedReservations = Reservation::whereBetween('reserved_at', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->where('shop_id', $shopId)
            ->get();

        $calenderMarks = CalenderMark::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('shop_id', $shopId)
            ->get()
            ->groupBy(fn ($mark) => $mark->date . ' ' . substr($mark->time, 0, 5));

        $businessHours = [
            'start' => $shop->start_time,
            'end' => $shop->end_time,
        ];

        return view('reserve.calender', [
            'dates' => $dates,
            'startOffset' => $startOffset,
            'line_user_id' => $request->line_user_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'category' => $request->category,
            'menu' => $menu,
            'duration' => $duration,
            'closedDays' => $closedDayIndexes,
            'lunchStart' => $shop->break_start,
            'lunchEnd' => $shop->break_end,
            'businessHours' => $businessHours,
            'businessStart' => $shop->business_start,
            'businessEnd' => $shop->business_end,
            'shopPhone' => $shop->phone,
            'confirmedReservations' => $confirmedReservations,
            'menuDurations' => $menuDurations,
            'calenderMarks' => $calenderMarks,
            'token' => $token,
            'shop' => $shop,
        ]);
    }

    public function showConfirmation(Request $request, $token)
    {
        $validated = $request->validate([
            'line_user_id' => 'required|string',
            'name' => 'required|string',
            'phone' => 'required|string',
            'category' => 'required|string',
            'menu' => 'required|string',
            'reserved_at' => 'required|date',
        ]);

        return view('reserve.confirmation', array_merge($validated, ['token' => $token]));
    }

    public function showCalender(Request $request)
    {
        $dates = [];
        $today = Carbon::today();
        for ($i = 0; $i < 14; $i++) {
            $dates[] = $today->copy()->addDays($i);
        }

        $menuDurations = [
            '一般 4500円' => 60,
            'カットのみ 3500円' => 60,
            '高校生 3600円' => 60,
            '中学生 3100円' => 60,
            '小学生 2700円' => 60,
            'ノーマル 9500円〜' => 180,
            'ピンパーマ 13500円〜' => 180,
            'スパイラル 13500円〜' => 180,
            'ブリーチ 5500円（2回目以降から+4500円ずつ）' => 120,
            'ノーマルカラー 5000円' => 120,
            'グレイカラー 2300円' => 120,
        ];

        $menu = $request->menu;
        $duration = $menuDurations[$menu] ?? 60;

        $shopId = $request->input('shop_id', 1); // デフォルト1店舗目
        $shop = Shop::findOrFail($shopId);
        $closedDays = explode(',', $shop->closed_days ?? '');
        $closedDayIndexes = collect(['日','月','火','水','木','金','土'])
            ->filter(fn($d) => in_array($d, $closedDays))
            ->keys()
            ->toArray();

        return view('reserve.calender', [
            'dates' => $dates,
            'line_user_id' => $request->line_user_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'menu' => $menu,
            'duration' => $duration,
            'closedDays' => $closedDayIndexes,
            'lunchStart' => $shop->break_start,
            'lunchEnd' => $shop->break_end,
            'businessStart' => $shop->business_start,
            'businessEnd' => $shop->business_end,
            'shopPhone' => $shop->phone,
        ]);
    }

    public function verify(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            abort(400, '予約トークンが必要です');
        }

        $reservation = Reservation::where('line_token', $token)
            ->where('status', 'confirmed')
            ->where('reserved_at', '>=', now())
            ->with('shop', 'user')
            ->firstOrFail();

        return view('reserve.confirm', [
            'reservations' => collect([$reservation]),
            'reservation'  => $reservation,
        ]);
    }

    // ReservationFormController.php

    public function my()
    {
        return view('reserve.my'); // LIFF入口用ビュー
    }

    public function resolve(Request $request)
    {
        $lineUserId = $request->input('line_user_id');
        if (!$lineUserId) {
            return response()->json(['error' => 'line_user_id is required'], 400);
        }

        $reservation = Reservation::where('line_user_id', $lineUserId)
            ->where('status', 'confirmed')
            ->where('reserved_at', '>=', now())
            ->orderBy('reserved_at', 'asc')
            ->first();

        return response()->json([
            'token' => $reservation?->line_token
        ]);
    }

}
