<x-guest-layout>
    <x-slot name="header">
        <!-- ヘッダーは中央寄せ＋フォントサイズ調整 -->
        <h2 class="text-lg font-semibold text-gray-800 text-center sm:text-left">
            予約内容の確認
        </h2>
    </x-slot>

    <!-- コンテナに max-w-md + mx-auto を付けて中央寄せしつつ左右にパディング -->
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <p class="mb-4 text-sm text-gray-800">
            以下の内容で予約を確定します。問題なければ「これで予約」ボタンを押してください。
        </p>

        <form method="POST" action="{{ route('reserve.store') }}">
            @csrf
            <input type="hidden" name="line_user_id"  value="{{ $line_user_id }}">
            <input type="hidden" name="name"          value="{{ $name }}">
            <input type="hidden" name="phone"         value="{{ $phone }}">
            <input type="hidden" name="category"      value="{{ $category }}">
            <input type="hidden" name="menu"          value="{{ $menu }}">
            <input type="hidden" name="shop_id" value="{{ request('shop_id') }}">
            <input type="hidden" name="reserved_at"   value="{{ $reserved_at }}">

            <!-- 各項目をリスト化して spacing を均等に -->
            <div class="space-y-2 mb-6 text-sm">
                <div>
                    <span class="font-medium">お名前：</span>{{ $name }}
                </div>
                <div>
                    <span class="font-medium">電話番号：</span>{{ $phone }}
                </div>
                <div>
                    <span class="font-medium">メニュー：</span>{{ $menu }}
                </div>
                <div>
                    <span class="font-medium">予約日時：</span>
                    {{ \Carbon\Carbon::parse($reserved_at)->format('Y年n月j日 H:i') }}
                </div>
            </div>

            <!-- ボタンはスマホで full width、PCで auto に -->
            <div>
                <button
                    type="submit"
                    class="w-full sm:w-auto block mx-auto bg-blue-600 text-white font-medium px-4 py-2 rounded hover:bg-blue-700"
                >
                    これで予約
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
