<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">新規顧客を登録</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-6 px-2 sm:px-0">
        <form method="POST" action="{{ route('admin.customers.store') }}" enctype="multipart/form-data" class="space-y-4 text-base">
            @csrf

            <div>
                <label class="block text-sm mb-1">氏名</label>
                <input type="text" name="name" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-400 focus:border-blue-400" />
            </div>

            <div>
                <label class="block text-sm mb-1">電話番号</label>
                <input type="text" name="phone"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-400 focus:border-blue-400" />
            </div>

            <div>
                <label class="block text-sm mb-1">顧客メモ</label>
                <textarea name="note" rows="3"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-400 focus:border-blue-400"></textarea>
            </div>

            <div>
                <label class="block text-sm mb-1">画像を添付（複数選択可）</label>
                <input type="file" name="images[]" multiple
                    accept="image/*"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-400 focus:border-blue-400" />
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full sm:w-auto max-w-xs mx-auto block bg-blue-600 text-white font-medium px-4 py-2 rounded hover:bg-blue-700 transition">
                    登録する
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
