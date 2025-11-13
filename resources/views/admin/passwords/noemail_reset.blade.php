<x-guest-layout>
    <h2 class="text-xl font-bold mb-4 text-center">新しいパスワード設定</h2>

    <form method="POST" action="{{ route('admin.password.reset') }}">
        @csrf

        <input type="hidden" name="email" value="{{ $email }}">

        <!-- 新しいパスワード -->
        <div class="mb-4" x-data="{ show: false }">
            <label class="block mb-2">新しいパスワード</label>

            <div class="flex items-center gap-2">
                <input 
                    :type="show ? 'text' : 'password'"
                    name="password" 
                    class="w-full border rounded p-2"
                    required
                >

                <button 
                    type="button"
                    @click="show = !show"
                    class="px-3 py-1 text-sm border rounded bg-gray-100 hover:bg-gray-200"
                >
                    <span x-show="!show">表示</span>
                    <span x-show="show">非表示</span>
                </button>
            </div>

            @error('password') 
                <p class="text-red-600 text-sm">{{ $message }}</p> 
            @enderror
        </div>

        <!-- パスワード確認 -->
        <div class="mb-4" x-data="{ showConfirm: false }">
            <label class="block mb-2">パスワード（確認）</label>

            <div class="flex items-center gap-2">
                <input
                    :type="showConfirm ? 'text' : 'password'"
                    name="password_confirmation"
                    class="w-full border rounded p-2"
                    required
                >

                <button
                    type="button"
                    @click="showConfirm = !showConfirm"
                    class="px-3 py-1 text-sm border rounded bg-gray-100 hover:bg-gray-200"
                >
                    <span x-show="!showConfirm">表示</span>
                    <span x-show="showConfirm">非表示</span>
                </button>
            </div>
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded w-full">
            パスワードを更新する
        </button>
    </form>
</x-guest-layout>
