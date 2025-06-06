<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">カレンダーに記号を登録</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-8">
        <form method="POST" action="{{ route('admin.calender_marks.store') }}">
            @csrf

            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-gray-700">日付</label>
                <input type="date" name="date" required class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label for="time" class="block text-sm font-medium text-gray-700">時間</label>
                <input type="time" name="time" required class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label for="symbol" class="block text-sm font-medium text-gray-700">記号</label>
                <select name="symbol" class="w-full border rounded px-3 py-2" required>
                    <option value="">選択してください</option>
                    <option value="×">×（予約不可）</option>
                    <option value="tel">tel（電話）</option>
                    <option value="△">△（残りわずか）</option>
                    <option value="○">○（空きあり）</option>
                </select>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                登録する
            </button>
        </form>
    </div>
</x-app-layout>
