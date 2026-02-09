<x-app-layout>
    <x-slot name="header">
        <div class="relative">
            <h2 class="text-xl font-semibold text-gray-800">管理者ダッシュボード</h2>
            <a href="{{ route('admin.shop.edit') }}"
                class="absolute right-0 top-0 text-sm text-blue-600 hover:underline">
                店舗情報を編集する
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    これは管理者専用のダッシュボードです。予約の管理ができます。
                </div>
            </div>

            @if (session('status'))
                <div class="mb-4 text-green-600 font-semibold">{{ session('status') }}</div>
            @endif

            <div class="flex flex-wrap ml-2">
                <a href="{{ route('admin.reservations.create') }}" class="btn-reserve mr-2 mb-2">
                    ＋予約を追加
                </a>
                <!-- <a href="{{ route('admin.customers.create') }}" class="btn-reserve mr-2 mb-2">
                    ＋ 新規顧客を登録
                </a> -->
                <a href="{{ route('admin.reservations.calender', ['symbol_mode' => 1]) }}" class="btn-reserve mb-2">
                    ＋ カレンダー記号設定
                </a>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📅 本日の予約一覧</h3>
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">名前</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">カテゴリー</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">メニュー</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">時間</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">備考</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">ステータス</th>
                                </tr>
                            </thead>
                            @php
                                $categoryLabels = [
                                    'cut' => 'カット',
                                    'cut_color' => 'カット＋カラー',
                                    'perm' => 'パーマ',
                                    'color' => 'カラー',
                                ];
                            @endphp
                            <tbody>
                                @forelse ($reservations as $reservation)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $reservation->user->name ?? '未登録' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $categoryLabels[$reservation->category] ?? '未設定' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $reservation->menu }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $reservation->reserved_at->format('H:i') }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            @if (!empty($reservation->note))
                                                <div class="text-sm text-gray-600 mb-1">{{ $reservation->note }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <form method="POST" action="{{ route('admin.reservations.updateStatus', $reservation->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" onchange="this.form.submit()" class="border rounded px-2 py-1">
                                                    <!-- <option value="pending" {{ $reservation->status === 'pending' ? 'selected' : '' }}>保留</option> -->
                                                    <option value="confirmed" {{ $reservation->status === 'confirmed' ? 'selected' : '' }}>確定</option>
                                                    <option value="canceled" {{ $reservation->status === 'canceled' ? 'selected' : '' }}>キャンセル</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-2 text-center text-gray-500 whitespace-nowrap">
                                            本日の予約はありません。
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📆 明日以降の予約一覧</h3>
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">名前</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">カテゴリー</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">メニュー</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">時間</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">備考</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">ステータス</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($upcomingReservations as $reservation)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $reservation->user->name ?? '未登録' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $categoryLabels[$reservation->category] ?? '未設定' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $reservation->menu }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $reservation->reserved_at->format('n/j H:i') }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            @if (!empty($reservation->note))
                                                <div class="text-sm text-gray-600 mb-1">{{ $reservation->note }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <form method="POST" action="{{ route('admin.reservations.updateStatus', $reservation->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" onchange="this.form.submit()" class="border rounded px-2 py-1">
                                                    <!-- <option value="pending" {{ $reservation->status === 'pending' ? 'selected' : '' }}>保留</option> -->
                                                    <option value="confirmed" {{ $reservation->status === 'confirmed' ? 'selected' : '' }}>確定</option>
                                                    <option value="canceled" {{ $reservation->status === 'canceled' ? 'selected' : '' }}>キャンセル</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-2 text-center text-gray-500 whitespace-nowrap">
                                            明日以降の予約はありません。
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



<!-- (スタンプ履歴)必要に応じてコメントアウト解除

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">🏷️ スタンプ履歴（直近10件）</h3>

                    <table class="table-auto w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">名前</th>
                                <th class="px-4 py-2 text-left">来店日</th>
                                <th class="px-4 py-2 text-left">特典使用</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stamps as $stamp)
                                <tr class="border-t">
                                    <td class="px-4 py-2">{{ $stamp->user->name ?? '未登録' }}</td>
                                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($stamp->visit_date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">
                                        {{ $stamp->reward_claimed ? '✅ 済' : '❌ 未使用' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-center text-gray-500">
                                        スタンプ履歴がありません。
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
-->
@if(false)
            <!-- 顧客セレクト検索フォーム -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📇 顧客を選択</h3>
                    <div class="overflow-x-auto">
                        <form method="GET" action="{{ route('admin.dashboard') }}">
                            <select name="selected_customer" class="w-full border rounded px-3 py-2 mb-2" required>
                                <optgroup label="LINEユーザー">
                                    @foreach ($users as $user)
                                        <option value="user_{{ $user->id }}">{{ $user->name }}（{{ $user->phone }}）</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="手動登録顧客">
                                    @foreach ($customers as $customer)
                                        <option value="customer_{{ $customer->id }}">{{ $customer->name }}（{{ $customer->phone }}）</option>
                                    @endforeach
                                </optgroup>
                            </select>

                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                表示
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if (isset($searchResult))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">{{ $searchResult['name'] }} さんのメモ履歴</h3>
                        <p><strong>電話番号：</strong> {{ $searchResult['phone'] }}</p>

                        <!-- Alpine.jsの読み込み（1回だけ） -->
                        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
                        <div x-data="{ showModal: false, modalImage: '' }">
                            <!-- メモ履歴一覧 -->
                            <div class="overflow-x-auto">
                                <table class="table-auto w-full bg-white border mt-4 mb-6">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left whitespace-nowrap">メモ内容</th>
                                            <th class="px-4 py-2 text-left whitespace-nowrap">画像</th>
                                            <th class="px-4 py-2 text-left whitespace-nowrap">作成者</th>
                                            <th class="px-4 py-2 text-left whitespace-nowrap">作成日</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($searchResult['notes'] as $note)
                                            <tr class="border-t">
                                                <td class="px-4 py-2 whitespace-nowrap">{{ $note->content }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    <div class="flex flex-wrap gap-1">
                                                        {{-- Noteの画像がある場合 --}}
                                                        @if ($note->signed_url)
                                                            <img src="{{ $note->signed_url }}"
                                                                alt="画像"
                                                                class="w-16 h-16 object-cover rounded shadow cursor-pointer"
                                                                @click="modalImage = '{{ $note->signed_url }}'; showModal = true">
                                                        @elseif (!empty($note->customer) && $note->customer->images && $note->customer->images->count())
                                                            {{-- Customerの画像がある場合 --}}
                                                            @foreach ($note->customer->images as $image)
                                                                <img src="{{ $note->signed_url }}"
                                                                    alt="画像"
                                                                    class="w-16 h-16 object-cover rounded shadow cursor-pointer"
                                                                    @click="modalImage = '{{ $note->signed_url }}'; showModal = true">
                                                            @endforeach
                                                        @else
                                                            <span class="text-gray-400 text-sm">画像なし</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap">{{ $note->created_by }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    {{ \Carbon\Carbon::parse($note->created_at)->format('n/j G:i') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-gray-500 py-4 whitespace-nowrap">メモはまだありません。</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- モーダル：クリックで拡大表示 -->

                            <div
                                x-show="showModal"
                                class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50"
                                @click.self="showModal = false"
                            >
                                <div class="relative">
                                    <!-- ✖ ボタン -->
                                    <button
                                        @click="showModal = false"
                                        class="absolute top-0 right-0 mt-2 mr-2 font-bold hover:text-red-400"
                                        style="color: red; font-size: 2rem; line-height: 1;"
                                    >
                                        &times;
                                    </button>

                                    <!-- 画像本体 -->
                                    <img
                                        :src="modalImage"
                                        alt="拡大画像"
                                        style="width: 18rem; height: 18rem; object-fit: contain;"
                                        class="rounded shadow-lg"
                                    />
                                </div>
                            </div>

                        </div>

                        <!-- メモ追加フォーム -->
                        <form method="POST" action="{{ route('admin.notes.store') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="customer_type_and_id" value="{{ $searchResult['type'] . '_' . $request->selected_customer_id }}">

                            <div class="mb-4">
                                <label for="content" class="block text-sm font-medium text-gray-700">メモ内容</label>
                                <textarea name="content" id="content" rows="3" class="w-full border rounded" required></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="image" class="block text-sm font-medium text-gray-700">画像（任意）</label>
                                <input type="file" name="image" accept="image/*" class="w-full border rounded px-3 py-2">
                            </div>

                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                メモを追加
                            </button>
                        </form>

                    </div>
                </div>
            @endif

            <!-- 顧客メモ一覧 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📖 顧客メモ履歴（直近3日間）</h3>
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">顧客名</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">メモ内容</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">作成者</th>
                                    <th class="px-4 py-2 text-left whitespace-nowrap">作成日</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notes as $note)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 text-left whitespace-nowrap">
                                            @if ($note->user)
                                                <a href="{{ route('admin.notes.show', ['type' => 'user', 'id' => $note->user->id]) }}" class="text-blue-600 underline">
                                                    {{ $note->user->name }}
                                                </a>
                                            @elseif ($note->customer)
                                                <a href="{{ route('admin.notes.show', ['type' => 'customer', 'id' => $note->customer->id]) }}" class="text-blue-600 underline">
                                                    {{ $note->customer->name }}
                                                </a>
                                            @else
                                                未登録
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-left whitespace-nowrap">{{ $note->content }}</td>
                                        <td class="px-4 py-2 text-left whitespace-nowrap">{{ $note->created_by }}</td>
                                        <td class="px-4 py-2 text-left whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($note->created_at)->format('n/j G:i') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-center text-gray-500 whitespace-nowrap">
                                            メモはまだありません。
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

@endif

<!-- 顧客メモ追加フォーム
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📝 顧客メモの追加</h3>
                    <form method="POST" action="{{ route('admin.notes.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="customer_type_and_id" class="block text-sm font-medium text-gray-700">顧客を選択</label>
                            <select name="customer_type_and_id" id="customer_type_and_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <optgroup label="LINEユーザー">
                                    @foreach ($users as $user)
                                        <option value="user_{{ $user->id }}">{{ $user->name ?? '未登録' }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="電話予約など（手動登録）">
                                    @foreach ($customers as $customer)
                                        <option value="customer_{{ $customer->id }}">{{ $customer->name ?? '未登録' }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="block text-sm font-medium text-gray-700">メモ内容</label>
                            <textarea name="content" id="content" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>

                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            メモを追加
                        </button>
                    </form>
                </div>
            </div>         -->

            <div class="mt-10">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6 2a1 1 0 000 2h8a1 1 0 100-2H6zM3 6a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2H3zm2 3h10a1 1 0 110 2H5a1 1 0 010-2z" />
                    </svg>
                    予約状況カレンダー
                </h2>

                <div id="calendar"></div>
            </div>

        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridWeek',
        locale: 'ja',
        height: 'auto',
        firstDay: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridWeek,dayGridMonth'
        },
        buttonText: {
            today: '今日',
            week: '週',
            month: '月'
        },
        events: '/admin/reservations/json',

        eventTextColor: '#000',

        dayMaxEventRows: false,
        moreLinkClick: 'popover',
        eventDisplay: 'block',

        // イベントごとに色を設定
        eventDidMount: function(info) {
            const isBreak = info.event.extendedProps.isBreak;
            
            if (isBreak) {
                info.el.style.backgroundColor = '#bfdbfe';
                info.el.style.borderColor = '#93c5fd';
            } else {
                info.el.style.backgroundColor = '#f9a8d4';
                info.el.style.borderColor = '#f472b6';
            }
        },

        // イベントの内容をカスタマイズ
        eventContent: function(arg) {
            const event = arg.event;
            const props = event.extendedProps;
            
            const container = document.createElement('div');
            container.style.padding = '4px';
            container.style.lineHeight = '1.6';
            
            if (props.isBreak) {
                // 休憩ボックスの表示
                const breakLabel = document.createElement('div');
                breakLabel.style.fontWeight = 'bold';
                breakLabel.style.fontSize = '0.9rem';
                breakLabel.style.color = '#1e40af';
                breakLabel.textContent = '30分休憩';
                container.appendChild(breakLabel);
                
                const name = document.createElement('div');
                name.style.fontSize = '0.85rem';
                name.style.color = '#1e40af';
                name.textContent = props.name || '';
                container.appendChild(name);
            } else {
                // 通常の予約ボックスの表示
                const time = document.createElement('div');
                time.style.fontWeight = 'bold';
                time.style.fontSize = '0.95rem';
                time.textContent = event.start.toLocaleTimeString('ja-JP', { hour: '2-digit', minute: '2-digit' });
                container.appendChild(time);
                
                const name = document.createElement('div');
                name.style.fontSize = '0.9rem';
                name.textContent = props.name || '未登録';
                container.appendChild(name);
                
                const menu = document.createElement('div');
                menu.style.fontSize = '0.85rem';
                menu.textContent = props.menu || '未設定';
                container.appendChild(menu);
            }
            
            return { domNodes: [container] };
        },

        // ✅ 時間表記を明示的に2桁＋コロン付きにして見切れ防止
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
    });

    calendar.render();
});
</script>
@endpush

<style>
/* FullCalendar イベント見切れ対策 */
.fc .fc-daygrid-event {
  white-space: normal !important;
  line-height: 1.6 !important;
  padding: 6px !important;
  border-radius: 6px !important;
  overflow: visible !important;
  font-size: 1.0rem !important;
  font-weight: normal !important;
  min-height: 3.5rem !important;
}

.fc .fc-event-title,
.fc .fc-event-time {
  overflow: visible !important;
  text-overflow: unset !important;
  white-space: normal !important;
  display: block !important;
}

/* 週表示用の追加スタイル */
.fc-dayGridWeek-view .fc-daygrid-day-frame {
  min-height: 150px !important;
}
</style>



</x-app-layout>
