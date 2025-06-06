<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
            <h2 class="text-xl font-semibold text-gray-800">🏪 店舗情報編集</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:underline sm:ml-4">
                ダッシュボードに戻る
            </a>
        </div>
    </x-slot>

    <div class="py-12 max-w-3xl mx-auto px-2 sm:px-0">
        @if (session('status'))
            <div class="mb-4 text-green-600 font-semibold">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.shop.update') }}" class="text-base">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">店名</label>
                <input type="text" name="name" value="{{ old('name', $shop->name) }}"
                    class="w-full border-gray-300 rounded px-3 py-2 shadow-sm mt-1" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">住所</label>
                <input type="text" name="address" value="{{ old('address', $shop->address) }}"
                    class="w-full border-gray-300 rounded px-3 py-2 shadow-sm mt-1" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">電話番号</label>
                <input type="text" name="phone" value="{{ old('phone', $shop->phone) }}"
                    class="w-full border-gray-300 rounded px-3 py-2 shadow-sm mt-1" />
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">定休日（複数選択可）</label>
                @php
                    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                    $selectedDays = explode(',', old('closed_days', $shop->closed_days ?? ''));
                @endphp
                <div class="flex flex-wrap gap-2">
                    @foreach ($weekdays as $day)
                        <label class="flex items-center mr-3 mb-1">
                            <input type="checkbox" name="closed_days[]" value="{{ $day }}"
                                {{ in_array($day, $selectedDays) ? 'checked' : '' }}
                                class="mr-2">
                            {{ $day }}曜
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">営業時間（開始）</label>
                <input type="time" name="business_start" value="{{ old('business_start', $shop->business_start) }}"
                    class="w-full border-gray-300 rounded px-3 py-2 shadow-sm mt-1" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">営業時間（終了）</label>
                <input type="time" name="business_end" value="{{ old('business_end', $shop->business_end) }}"
                    class="w-full border-gray-300 rounded px-3 py-2 shadow-sm mt-1" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">休憩時間（開始）</label>
                <input type="time" name="break_start" value="{{ old('break_start', $shop->break_start) }}"
                    class="w-full border-gray-300 rounded px-3 py-2 shadow-sm mt-1" />
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">休憩時間（終了）</label>
                <input type="time" name="break_end" value="{{ old('break_end', $shop->break_end) }}"
                    class="w-full border-gray-300 rounded px-3 py-2 shadow-sm mt-1" />
            </div>

            <div class="text-center sm:text-right">
                <button type="submit"
                    class="w-full sm:w-auto max-w-xs mx-auto sm:mx-0 bg-blue-600 text-white font-medium px-4 py-2 rounded hover:bg-blue-700 transition">
                    更新する
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
