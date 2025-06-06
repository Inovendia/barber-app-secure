{{-- resources/views/reserve/complete.blade.php --}}
<x-guest-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">予約完了</h2>
    </x-slot>

    <div class="p-6 text-gray-800">
        <p class="text-lg mb-4">ご予約ありがとうございました。</p>
        <p>ご希望の日時にて予約を承りました。</p>
        <p class="mt-4 text-sm text-gray-500">LINEにも通知をお送りしています。</p>

        <div class="mt-6">
            <a href="{{ route('reserve.verify', ['token' => $reservation->line_token]) }}"
                class="text-blue-600 hover:underline">
                    👉 予約内容を確認する
            </a>

        </div>
    </div>
</x-guest-layout>
