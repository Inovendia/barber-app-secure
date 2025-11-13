<x-guest-layout>
    <h2 class="text-xl font-bold mb-4 text-center">パスワード再設定</h2>

    <form method="POST" action="{{ route('admin.password.check') }}">
        @csrf

        <div class="mb-4">
            <label class="block mb-2">登録しているID（メールアドレス）</label>
            <input type="email" name="email" class="w-full border rounded p-2" required>
            @error('email')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded w-full">
            次へ
        </button>
    </form>
</x-guest-layout>
