{{-- resources/views/reserve/form.blade.php --}}

<x-guest-layout>
    <x-slot name="header">
        <div class="flex justify-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Rezamie Logo" class="w-24 h-auto">
        </div>
        <h2 class="text-xl font-semibold text-gray-800">新規予約</h2>
    </x-slot>

    <div class="p-6 text-gray-800">

    <!-- <div class="mb-4 text-right">
        <a id="my-reserves-link" href="{{ route('reserve.my') }}" class="text-blue-600 hover:underline text-sm">
            👉 現在の予約を確認する
        </a>
    </div> -->


        @if (session('status'))
            <div class="mb-4 text-green-600 font-semibold">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('reserve.calender', ['token' => $shop->public_token]) }}" onsubmit="return checkBeforeSubmit();">
            @csrf
            <input type="hidden" name="line_user_id" value="" id="hidden_line_user_id">
            <input type="hidden" name="name" id="hidden_name">
            <input type="hidden" name="phone" id="hidden_phone">
            <input type="hidden" name="category" id="hidden_category">
            <input type="hidden" name="menu" id="hidden_menu">
            <input type="hidden" name="shop_id" value="{{ $shop->id }}">

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

            <button id="submitBtn" type="submit"
                class="mt-6 w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-md text-center hover:bg-blue-700 transition opacity-50 cursor-not-allowed"
                disabled>
                日時選択へ進む
            </button>
        </form>
    </div>
</x-guest-layout>

<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>

<script>
// 入力チェックとhiddenへのコピー
function checkBeforeSubmit() {
    const lineUserId = document.getElementById('hidden_line_user_id').value;
    if (!lineUserId) {
        alert('LINE認証が完了していません。LIFFの初期化/ログインを確認してください。');
        return false;
    }

    document.getElementById('hidden_name').value     = document.getElementById('name').value;
    document.getElementById('hidden_phone').value    = document.getElementById('phone').value;
    document.getElementById('hidden_category').value = document.getElementById('category').value;
    document.getElementById('hidden_menu').value     = document.getElementById('menu').value;
    return true;
}

function updateMenuOptions() {
    const category  = document.getElementById('category').value;
    const menuSelect = document.getElementById('menu');
    menuSelect.innerHTML = '';

    const menuOptions = {
        cut:   ['一般 4600円', 'カットのみ 3500円', '高校生 3600円', '中学生 3100円', '小学生 2700円'],
        perm:  ['ノーマル 9500円〜', 'ピンパーマ 13500円〜', 'スパイラル 13500円〜'],
        color: ['ブリーチ 5500円〜（2回目以降から+4500円ずつ）', 'ノーマルカラー 5000円〜', 'グレイカラー 2300円〜'],
    };

    if (menuOptions[category]) {
        menuOptions[category].forEach(text => {
            const opt = document.createElement('option');
            opt.value = text;
            opt.textContent = text;
            menuSelect.appendChild(opt);
        });
    } else {
        const opt = document.createElement('option');
        opt.textContent = '-- 先にカテゴリーを選んでください --';
        opt.value = '';
        menuSelect.appendChild(opt);
    }
}

(async () => {
    const submitBtn = document.getElementById('submitBtn');
    const hiddenUserId = document.getElementById('hidden_line_user_id');

    try {
        const liffId = @json($shop->liff_id ?? config('services.liff.id') ?? null);
        if (!liffId) return;

        await liff.init({ liffId });

        if (!liff.isLoggedIn()) {
            return liff.login({ redirectUri: window.location.href });
        }

        let userId = null;

        // profile.userId を優先
        try {
            const profile = await liff.getProfile();
            if (profile?.userId) userId = profile.userId;
        } catch (e) {
            const decoded = liff.getDecodedIDToken?.();
            userId = decoded?.sub || null;
        }

        if (userId) {
            hiddenUserId.value = userId;
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    } catch (err) {
        console.error('LIFF初期化エラー:', err);
    }
})();

</script>
