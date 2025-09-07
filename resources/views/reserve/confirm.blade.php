<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>予約確認</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-4 text-gray-800 bg-gray-50 leading-relaxed max-w-md mx-auto">

    <h2 class="text-2xl font-bold mb-6 text-center">あなたの予約</h2>

    @if (session('status'))
        <p class="mb-4 text-green-600 font-semibold text-sm text-center">{{ session('status') }}</p>
    @endif

    @if ($reservations->isEmpty())
        <p class="text-center">現在予約はありません。</p>
    @else
        <ul class="space-y-4">
            @foreach ($reservations as $r)
                @if ($r->status !== 'canceled')
                    <li class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm">
                        <p class="text-sm"><span class="font-semibold">店舗：</span>{{ $r->shop->name ?? '未設定' }}</p>
                        <p class="text-sm mt-1"><span class="font-semibold">メニュー：</span>{{ $r->menu }}</p>
                        <p class="text-sm mt-1"><span class="font-semibold">日時：</span>{{ \Carbon\Carbon::parse($r->reserved_at)->format('Y年m月d日 H:i') }}</p>
                        <p class="text-sm mt-1"><span class="font-semibold">ステータス：</span>{{ $r->status }}</p>

                        <form method="POST" action="{{ route('reserve.cancel') }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="line_token" value="{{ $r->line_token }}">
                        <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 text-sm">
                            キャンセルする
                        </button>
                    </form>

                    </li>
                @endif
            @endforeach
        </ul>
    @endif

    <div class="mt-8 text-center">
        @if (!empty($lineUserId))
            <a href="{{ route('reserve.form', ['token' => $reservation->shop->public_token, 'line_user_id' => $lineUserId]) }}"
                class="text-blue-600 hover:underline text-sm">
                    ← 予約フォームに戻る
            </a>
        @endif
    </div>

</body>
</html>
