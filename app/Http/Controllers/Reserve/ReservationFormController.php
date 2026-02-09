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

        $existingUser = null;
        if ($lineUserId) {
            $existingUser = User::where('shop_id', $shop->id)
                ->where('line_user_id', $lineUserId)
                ->first();
        }

        return view('reserve.form', [
            'lineUserId' => $lineUserId,
            'shop' => $shop,
            'existingUser' => $existingUser,
        ]);
    }

    public function store(Request $request, $token)
    {

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
            ['shop_id' => $shop->id, 'line_user_id' => $validated['line_user_id']],
            ['name' => $validated['name'], 'phone' => $validated['phone']]
        );

        $categoryDurations = [
            'cut' => 60,
            'color' => 60,
            'cut_color' => 120,
            'perm' => 150,
        ];
        $legacyMenuDurations = [
            '一般 4600円' => 60,
            'カットのみ 3500円' => 60,
            '高校生 3600円' => 60,
            '中学生 3100円' => 60,
            '小学生 2700円' => 60,
            'ノーマル 9500円〜' => 150,
            'ピンパーマ 13500円〜' => 150,
            'スパイラル 13500円〜' => 150,
            'ブリーチ 5500円〜（2回目以降から+4500円ずつ）' => 60,
            'ノーマルカラー 5000円〜' => 60,
            'グレイカラー 2300円〜' => 60,
            'ノーマルカラー (白髪染め・ブラック・ブラウン系) 9600円~' => 120,
            'グレイカラー (白髪ぼかし) 7100円~' => 120,
            'ハイトーンカラー (青・金など要ブリーチ ※要相談) 14,700円~' => 120,
            'ノーマルカラー (白髪染め・ブラック・ブラウン系) 5000円~' => 60,
            'グレイカラー (白髪ぼかし) 2300円~' => 60,
            'ハイトーン (青・金など要ブリーチ ※要相談) 10000円~' => 60,
        ];

        $baseDuration = $categoryDurations[$validated['category']]
            ?? ($legacyMenuDurations[$validated['menu']] ?? 60);
        $reservedAt = Carbon::parse($validated['reserved_at']);

        // 連続予約チェック: 直前の予約を検索
        $previousReservation = Reservation::where('shop_id', $shop->id)
            ->where('status', 'confirmed')
            ->whereDate('reserved_at', $reservedAt->toDateString())
            ->get()
            ->first(function ($res) use ($reservedAt, $categoryDurations, $legacyMenuDurations) {
                $categoryDuration = $res->category ? ($categoryDurations[$res->category] ?? null) : null;
                $resDuration = $res->duration
                    ?? $categoryDuration
                    ?? ($legacyMenuDurations[$res->menu] ?? 60);
                $resEnd = Carbon::parse($res->reserved_at)->addMinutes($resDuration);
                return $resEnd->equalTo($reservedAt);
            });

        // 直前の予約が存在 & 延長されていない場合のみ+30分
        $shouldExtend = $previousReservation && !$previousReservation->is_extended;
        $finalDuration = $shouldExtend ? $baseDuration + 30 : $baseDuration;

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
            'duration' => $finalDuration,
            'is_extended' => $shouldExtend,
        ]);

        // LINE通知内容の作成
        $url = route('reserve.verify') . '?token=' . $reservation->line_token;

        $message = "✅ ご予約ありがとうございます！\n\n"
            . "📅 日時：{$reservation->reserved_at->format('Y年m月d日 H:i')}\n"
            . "✂️ メニュー：{$reservation->menu}\n\n"
            . "▼ ご確認・キャンセルはこちら：\n{$url}";
        // LINE通知送信（ユーザー／管理者）
        $this->lineService->notifyUser($shop, $user->line_user_id, $message);
        $this->lineService->notifyAdmin($shop, "新しい予約が入りました！\nメニュー: {$reservation->menu}\n日時: {$reservation->reserved_at}");


        // セッション保存（フォーム戻りなどに使用）
        session(['line_user_id' => $validated['line_user_id']]);

        // 完了画面へ（$reservationを渡す）
        return view('reserve.complete', [
            'reservation' => $reservation,
        ]);
    }

    public function confirm(Request $request, $token)
    {
        $shop = Shop::where('public_token', $token)->firstOrFail();
        $lineUserId = $request->query('line_user_id');

        $user = User::where('shop_id', $shop->id)
            ->where('line_user_id', $lineUserId)
            ->first();

        if (!$user) {
            return redirect()->route('reserve.form', ['token' => $token])
                ->with('status', '予約情報が見つかりませんでした。');
        }

        $reservations = $user->reservations()
            ->where('shop_id', $shop->id)
            ->orderByDesc('reserved_at')
            ->get();

        return view('reserve.confirm', compact('reservations', 'lineUserId'));
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'line_token' => 'required|string',
        ]);

        $reservation = Reservation::where('line_token', $request->line_token)
            ->with('shop', 'user')
            ->first();

        if (!$reservation) {
            abort(404); // トークン自体が不正
        }

        // すでにキャンセル済みならそのまま完了画面へ
        if ($reservation->status === 'canceled') {
            return view('reserve.cancel_complete', [
                'reservation'      => $reservation,
                'alreadyCanceled'  => true,
            ]);
        }

        // 初回キャンセル処理
        $reservation->cancelWithNotification($this->lineService);

        return view('reserve.cancel_complete', [
            'reservation'      => $reservation,
            'alreadyCanceled'  => false,
        ]);
    }

    public function calender(Request $request, $token)
    {
        if (!$request->filled(['line_user_id', 'name', 'phone', 'category', 'menu'])) {
            return redirect()->route('reserve.form')->with('status', '必要な情報が不足しています。');
        }

        $startOffset = (int) $request->query('start_offset', 0);
        $baseDate = Carbon::today()->copy()->addDays($startOffset);

        $dates = collect();
        for ($i = 0; $i < 14; $i++) {
            $dates->push($baseDate->copy()->addDays($i));
        }

        $categoryDurations = [
            'cut' => 60,
            'color' => 60,
            'cut_color' => 120,
            'perm' => 150,
        ];
        $legacyMenuDurations = [
            '一般 4600円' => 60,
            'カットのみ 3500円' => 60,
            '高校生 3600円' => 60,
            '中学生 3100円' => 60,
            '小学生 2700円' => 60,
            'ノーマル 9500円〜' => 150,
            'ピンパーマ 13500円〜' => 150,
            'スパイラル 13500円〜' => 150,
            'ブリーチ 5500円〜（2回目以降から+4500円ずつ）' => 60,
            'ノーマルカラー 5000円〜' => 60,
            'グレイカラー 2300円〜' => 60,
            'ノーマルカラー (白髪染め・ブラック・ブラウン系) 9600円~' => 120,
            'グレイカラー (白髪ぼかし) 7100円~' => 120,
            'ハイトーンカラー (青・金など要ブリーチ ※要相談) 14,700円~' => 120,
            'ノーマルカラー (白髪染め・ブラック・ブラウン系) 5000円~' => 60,
            'グレイカラー (白髪ぼかし) 2300円~' => 60,
            'ハイトーン (青・金など要ブリーチ ※要相談) 10000円~' => 60,
        ];

        $menu = $request->menu;
        $duration = $categoryDurations[$request->category]
            ?? ($legacyMenuDurations[$menu] ?? 60);

        $shop = Shop::where('public_token', $token)->firstOrFail();
        $shopId = $shop->id;
        $closedDays = explode(',', $shop->closed_days ?? '');
        $closedDayIndexes = collect(['日', '月', '火', '水', '木', '金', '土'])
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
            ->groupBy(fn($mark) => $mark->date . ' ' . substr($mark->time, 0, 5));

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
            'categoryDurations' => $categoryDurations,
            'legacyMenuDurations' => $legacyMenuDurations,
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

        $categoryDurations = [
            'cut' => 60,
            'color' => 60,
            'cut_color' => 120,
            'perm' => 150,
        ];
        $legacyMenuDurations = [
            '一般 4600円' => 60,
            'カットのみ 3500円' => 60,
            '高校生 3600円' => 60,
            '中学生 3100円' => 60,
            '小学生 2700円' => 60,
            'ノーマル 9500円〜' => 150,
            'ピンパーマ 13500円〜' => 150,
            'スパイラル 13500円〜' => 150,
            'ブリーチ 5500円〜（2回目以降から+4500円ずつ）' => 60,
            'ノーマルカラー 5000円〜' => 60,
            'グレイカラー 2300円〜' => 60,
            'ノーマルカラー (白髪染め・ブラック・ブラウン系) 9600円~' => 120,
            'グレイカラー (白髪ぼかし) 7100円~' => 120,
            'ハイトーンカラー (青・金など要ブリーチ ※要相談) 14,700円~' => 120,
            'ノーマルカラー (白髪染め・ブラック・ブラウン系) 5000円~' => 60,
            'グレイカラー (白髪ぼかし) 2300円~' => 60,
            'ハイトーン (青・金など要ブリーチ ※要相談) 10000円~' => 60,
        ];

        $menu = $request->menu;
        $duration = $categoryDurations[$request->category]
            ?? ($legacyMenuDurations[$menu] ?? 60);

        $shopId = $request->input('shop_id', 1); // デフォルト1店舗目
        $shop = Shop::findOrFail($shopId);
        $closedDays = explode(',', $shop->closed_days ?? '');
        $closedDayIndexes = collect(['日', '月', '火', '水', '木', '金', '土'])
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
            'categoryDurations' => $categoryDurations,
            'legacyMenuDurations' => $legacyMenuDurations,
        ]);
    }

    public function verify(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            abort(400, '予約トークンが必要です');
        }

        // ★ トークンだけで取得し、状態は後段で判定する
        $reservation = Reservation::where('line_token', $token)
            ->with('shop', 'user')
            ->firstOrFail();

        // すでにキャンセル済みなら、404にせず完了画面を出す
        if ($reservation->status === 'canceled') {
            return view('reserve.cancel_complete', [
                'reservation'     => $reservation,
                'alreadyCanceled' => true,
            ]);
        }

        // ここまで来たら（キャンセルされていない）予約の確認画面
        // ※もし「過去の予約はメッセージを出したい」ならここで分岐を追加してください
        // if ($reservation->reserved_at->lt(now())) { ... }

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

    public function resolve(Request $request, $token)
    {
        $shop = Shop::where('public_token', $token)->firstOrFail();
        $lineUserId = $request->string('line_user_id');

        if ($lineUserId->isEmpty()) {
            return response()->json(['error' => 'line_user_id is required'], 400);
        }

        $reservation = Reservation::where('shop_id', $shop->id)
            ->where('line_user_id', $lineUserId) // ※将来は user_id に寄せてOK
            ->where('status', 'confirmed')
            ->where('reserved_at', '>=', now())
            ->orderBy('reserved_at', 'asc')
            ->first();

        return response()->json(['token' => $reservation?->line_token]);
    }

    public function entry(Request $request)
    {
        $shopToken = $request->query('shop_token'); // LIFF URL から渡すクエリ
        $lineUserId = $request->query('line_user_id');

        $shop = Shop::where('public_token', $shopToken)->first();

        if (!$shop) {
            abort(404, '店舗が見つかりません');
        }

        // /reserve/{token}/form にリダイレクト
        return redirect()->route('reserve.form', [
            'token' => $shop->public_token,
            'line_user_id' => $lineUserId,
        ]);
    }

    public function showForm(string $token)
    {
        \Log::info('🌀 [ReservationFormController] showForm accessed', [
            'token' => $token,
            'url' => request()->fullUrl(),
        ]);

        if ($token === config('liff.entry_token')) {
            \Log::info('🚀 [Trampoline] LIFF entry page returned');
            return view('liff.entry');
        }

        $shop = \App\Models\Shop::where('public_token', $token)->first();
        \Log::info('✅ [Shop resolved]', [
            'shop_id' => $shop->id ?? null,
            'shop_name' => $shop->name ?? 'not found',
        ]);

        return view('reserve.form', compact('shop'));
    }
}
