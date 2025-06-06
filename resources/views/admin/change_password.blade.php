<x-app-layout>
    <x-slot name="header">
        パスワード変更
    </x-slot>

    <div class="max-w-xl mx-auto mt-8">
        <form method="POST" action="{{ route('admin.password.update') }}">
            @csrf

            <div class="mb-4">
                <label class="block font-bold mb-1">新しいパスワード</label>
                <input type="password" name="password" required class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block font-bold mb-1">確認用パスワード</label>
                <input type="password" name="password_confirmation" required class="w-full border rounded px-3 py-2">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                パスワードを変更する
            </button>
        </form>
    </div>
</x-app-layout>
