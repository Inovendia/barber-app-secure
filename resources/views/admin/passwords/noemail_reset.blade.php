<x-guest-layout>
    <h2 class="text-xl font-bold mb-4 text-center">新しいパスワード設定</h2>

    <form method="POST" action="{{ route('admin.password.reset') }}">
        @csrf

        <input type="hidden" name="email" value="{{ $email }}">

        <div class="mb-4">
            <label class="block mb-2">新しいパスワード</label>
            <input type="password" name="password" class="w-full border rounded p-2" required>
            @error('password') 
                <p class="text-red-600 text-sm">{{ $message }}</p> 
            @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-2">パスワード（確認）</label>
            <input type="password" name="password_confirmation" class="w-full border rounded p-2" required>
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded w-full">
            パスワードを更新する
        </button>
    </form>
</x-guest-layout>
