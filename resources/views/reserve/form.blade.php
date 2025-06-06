{{-- resources/views/reserve/form.blade.php --}}

<x-guest-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">予約フォーム</h2>
    </x-slot>

    <div class="p-6 text-gray-800">

        @if (!empty($lineUserId))
            <div class="mb-4 text-right">
                <a href="{{ route('reserve.confirm', ['line_user_id' => $lineUserId]) }}"
                    class="text-blue-600 hover:underline text-sm">
                    👉 現在の予約を確認する
                </a>
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 text-green-600 font-semibold">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="{{ route('reserve.calender') }}" onsubmit="return checkBeforeSubmit();"class="space-y-4">
            @csrf
            <input type="hidden" name="line_user_id" value="" id="hidden_line_user_id">
            <input type="hidden" name="name" id="hidden_name">
            <input type="hidden" name="phone" id="hidden_phone">
            <input type="hidden" name="category" id="hidden_category">
            <input type="hidden" name="menu" id="hidden_menu">
            <input type="hidden" name="shop_id" value="{{ request('shop_id', 1) }}">

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">お名前</label>
                <input id="name" type="text" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">電話番号</label>
                <input id="phone" type="text" class="w-full border p-2 rounded" required>
            </div>

            <!-- カテゴリー選択 -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">カテゴリー</label>
                <select id="category" class="w-full border p-2 rounded" required onchange="updateMenuOptions()">
                    <option value="">-- 選択してください --</option>
                    <option value="cut">カット</option>
                    <option value="perm">パーマ</option>
                    <option value="color">カラー</option>
                </select>
            </div>

            <!-- メニュー選択 -->
            <div>
                <label for="menu" class="block text-sm font-medium text-gray-700">メニュー</label>
                <select id="menu" class="w-full border p-2 rounded" required>
                    <option value="">-- 先にカテゴリーを選んでください --</option>
                </select>
            </div>

            <div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                    日時選択へ進む
                </button>
            </div>
        </form>

    </div>
</x-guest-layout>

<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    liff.init({ liffId: '{{ config('services.liff.id') }}' })
        .then(() => {
            if (!liff.isLoggedIn()) {
                liff.login();
            } else {
                liff.getProfile().then(profile => {
                    const lineUserId = profile.userId;
                    document.getElementById('hidden_line_user_id').value = lineUserId;
                });
            }
        })
        .catch(err => {
            alert('LINE連携に失敗しました');
            console.error(err);
        });
});

// 入力チェックとhiddenへのコピー
function checkBeforeSubmit() {
    const name = document.getElementById('name').value;
    const phone = document.getElementById('phone').value;
    const category = document.getElementById('category').value;
    const menu = document.getElementById('menu').value;
    const lineUserId = document.getElementById('hidden_line_user_id').value;

    if (!lineUserId) {
        alert('LINE認証が完了していません。しばらく待ってから送信してください。');
        return false;
    }

    document.getElementById('hidden_name').value = name;
    document.getElementById('hidden_phone').value = phone;
    document.getElementById('hidden_category').value = category;
    document.getElementById('hidden_menu').value = menu;
    return true;
}
</script>

<script>
function updateMenuOptions() {
    const category = document.getElementById('category').value;
    const menuSelect = document.getElementById('menu');
    menuSelect.innerHTML = '';

    const menuOptions = {
        cut: ['一般 4500円', 'カットのみ 3500円', '高校生 3600円', '中学生 3100円', '小学生 2700円'],
        perm: ['ノーマル 9500円〜', 'ピンパーマ 13500円〜', 'スパイラル 13500円〜'],
        color: ['ブリーチ 5500円（2回目以降から+4500円ずつ）', 'ノーマルカラー 5000円', 'グレイカラー 2300円'],
    };

    if (menuOptions[category]) {
        menuOptions[category].forEach(optionText => {
            const opt = document.createElement('option');
            opt.value = optionText;
            opt.textContent = optionText;
            menuSelect.appendChild(opt);
        });
    } else {
        const opt = document.createElement('option');
        opt.textContent = '-- 先にカテゴリーを選んでください --';
        opt.value = '';
        menuSelect.appendChild(opt);
    }
}
</script>
