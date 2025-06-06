<x-app-layout>
    <x-slot name="header">管理者登録</x-slot>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-xl mx-auto mt-6">
        <form method="POST" action="{{ route('admin.admins.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block font-bold mb-1">管理者の名前</label>
                <input type="text" name="admin_name" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block font-bold mb-1">メールアドレス</label>
                <input type="email" name="admin_email" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block font-bold mb-1">店舗名</label>
                <input type="text" name="shop_name" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block font-bold mb-1">住所</label>
                <input type="text" name="shop_address" class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block font-bold mb-1">電話番号</label>
                <input type="text" name="shop_phone" class="w-full border rounded px-3 py-2">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                登録する
            </button>
        </form>
    </div>
</x-app-layout>
