<x-guest-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">キャンセル完了</h2>
    </x-slot>

    <div class="p-6 text-gray-800">
        @if (!empty($alreadyCanceled) && $alreadyCanceled)
            <p class="text-red-600 text-lg mb-4">この予約はすでにキャンセル済みです。</p>
        @else
            <p class="text-green-600 text-lg mb-4">ご予約をキャンセルしました。</p>
        @endif

        <p>日時：{{ $reservation->reserved_at->format('Y年m月d日 H:i') }}</p>
        <p>メニュー：{{ $reservation->menu }}</p>

        <div class="mt-6">
            <a href="{{ route('reserve.form', ['token' => $reservation->shop->public_token]) }}"
               class="text-blue-600 hover:underline">
                ← 新しい予約をする
            </a>
        </div>
    </div>
</x-guest-layout>
