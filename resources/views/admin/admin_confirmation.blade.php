<x-app-layout>
    <x-slot name="header">
        <!-- スマホ:中央寄せ, PC:左寄せ -->
        <h2 class="text-lg font-semibold text-gray-800 text-center sm:text-left">
            予約内容の確認（管理者）
        </h2>
    </x-slot>

    <!-- max-w-md で読みやすい幅に制限し、mx-auto で中央寄せ -->
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- エラー表示 -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 mb-4 rounded">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li class="text-red-700 font-semibold">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.reservations.store') }}">
            @csrf

            <!-- 各項目を space-y-2 で縦間隔を均等に -->
            <div class="space-y-2 mb-6 text-sm text-center">
                <div>
                    <span class="font-medium text-lg">氏名：</span>
                    <span class="font-bold text-lg text-gray-900">{{ $name }}</span>
                </div>
                <div>
                    <span class="font-medium text-lg">電話番号：</span>
                    <span class="font-bold text-lg text-gray-900">{{ $phone }}</span>
                </div>
                <div>
                    <span class="font-medium text-lg">メニュー：</span>
                    <span class="font-bold text-lg text-gray-900">{{ $menu }}</span>
                </div>
                <div>
                    <span class="font-medium text-lg">予約日時：</span>
                    <span class="font-bold text-lg text-gray-900">{{ \Carbon\Carbon::parse($reserved_at)->format('Y年n月j日 H:i') }}</span>
                </div>

                @if (!empty($note))
                    <div class="mt-4">
                        <label class="font-semibold block">備考</label>
                        <p class="text-gray-800 text-sm">{{ $note }}</p>
                    </div>
                @endif
            </div>


            <!-- ボタンはスマホ full width, PC auto -->
            <button
                type="submit"
                class="w-auto max-w-xs mx-auto block bg-blue-600 text-white font-medium px-4 py-2 rounded hover:bg-blue-700"
            >
                予約を確定する
            </button>

        </form>
    </div>
</x-app-layout>
