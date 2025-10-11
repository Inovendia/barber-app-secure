<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">予約を手動で追加</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-6 px-2 sm:px-0">
        <form method="GET" action="{{ route('admin.reservations.calender') }}" class="space-y-4 text-base">
            <input type="hidden" name="line_user_id" value="">

            <div>
                <label class="block text-sm mb-1">氏名</label>
                <input type="text" name="name" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-400 focus:border-blue-400" placeholder="名前と名字の間はスペースを入れてください"/>
            </div>

            <div>
                <label class="block text-sm mb-1">電話番号</label>
                <input type="text" name="phone" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-400 focus:border-blue-400" placeholder="ハイフン不要です"/>
            </div>

            <div>
                <label class="block text-sm mb-1">カテゴリー</label>
                <select id="category" name="category"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-400 focus:border-blue-400"
                    required onchange="updateMenuOptions()">
                    <option value="">-- 選択してください --</option>
                    <option value="cut">カット</option>
                    <option value="perm">パーマ</option>
                    <option value="color">カラー</option>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">メニュー</label>
                <select id="menu" name="menu"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-400 focus:border-blue-400"
                    required>
                    <option value="">-- 先にカテゴリーを選んでください --</option>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">備考（任意）</label>
                <textarea name="note" rows="3"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-400 focus:border-blue-400"></textarea>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full sm:w-auto max-w-xs mx-auto block bg-blue-600 text-white font-medium px-4 py-2 rounded hover:bg-blue-700 transition">
                    日時選択へ進む
                </button>
            </div>
        </form>
    </div>

    <script>
        const menuOptions = {
            cut: [
                '一般 4600円',
                'カットのみ 3500円',
                '高校生 3600円',
                '中学生 3100円',
                '小学生 2700円'
            ],
            perm: [
                'ノーマル 9500円〜',
                'ピンパーマ 13500円〜',
                'スパイラル 13500円〜'
            ],
            color: [
                'ブリーチ 5500円（2回目以降から+4500円ずつ）',
                'ノーマルカラー 5000円',
                'グレイカラー 2300円'
            ]
        };

        function updateMenuOptions() {
            const category = document.getElementById('category').value;
            const menuSelect = document.getElementById('menu');

            menuSelect.innerHTML = '<option value="">-- メニューを選んでください --</option>';

            if (menuOptions[category]) {
                menuOptions[category].forEach(menu => {
                    const option = document.createElement('option');
                    option.value = menu;
                    option.textContent = menu;
                    menuSelect.appendChild(option);
                });
            }
        }
    </script>
</x-app-layout>
