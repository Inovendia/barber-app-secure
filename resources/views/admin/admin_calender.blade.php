<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            @if (request()->query('symbol_mode') == 1)
                営業時間の選択
            @else
                予約日時の選択（管理者）
            @endif
        </h2>
    </x-slot>

    @php
        $startOffset = (int) request()->query('start_offset', 0);
        $dates = [];
        $baseDate = \Carbon\Carbon::today()->copy()->addDays($startOffset);
        for ($i = 0; $i < 14; $i++) {
            $dates[] = $baseDate->copy()->addDays($i);
        }

        $reservedSlots = [];
        foreach ($confirmedReservations as $reservation) {
            $start = \Carbon\Carbon::parse($reservation->reserved_at);
            $menuDuration = $menuDurations[$reservation->menu] ?? 60;
            $intervals = ceil($menuDuration / 30);
            for ($i = 0; $i < $intervals; $i++) {
                $slot = $start->copy()->addMinutes(30 * $i)->format('Y-m-d H:i');
                $reservedSlots[$slot] = true;
            }
        }

        $calenderDates = array_map(fn($d) => $d->format('Y-m-d'), $dates);
    @endphp

    <div
        x-data="{
            symbolMode: {{ request()->query('symbol_mode') == 1 ? 'true' : 'false' }},
            selectedTime: '',
            selectedDate: '',
            selectedSlot: null,
            selectTime(time, dayOffset) {
                const selectedDateStr = calenderDates[parseInt(dayOffset)];
                const timeString = time.padStart(5, '0') + ':00';
                const reservedAt = `${selectedDateStr}T${timeString}`;
                document.getElementById('selected-time').innerText = `${selectedDateStr} ${time}`;
                document.getElementById('reserved_at').value = reservedAt;
                const btn = document.getElementById('confirm-btn');
                btn.disabled = false;
                btn.classList.remove('opacity-50');
                this.selectedSlot = `${selectedDateStr} ${time.padStart(5, '0')}`;
            }
        }"
    >

        <div class="flex justify-between items-center mb-4 px-2 sm:px-0">
            <a href="{{ route('admin.reservations.calender', ['start_offset' => max($startOffset - 14, 0), 'symbol_mode' => request('symbol_mode')]) }}"
                class="text-blue-500 underline whitespace-nowrap">← 前の2週間</a>

            <span class="font-bold text-lg whitespace-nowrap">
                {{ $dates[0]->format('Y年n月j日') }} 〜 {{ $dates[13]->format('n月j日') }}
            </span>

            <a href="{{ route('admin.reservations.calender', ['start_offset' => $startOffset + 14, 'symbol_mode' => request('symbol_mode')]) }}"
                class="text-blue-500 underline whitespace-nowrap">次の2週間 →</a>
        </div>

        <template x-if="symbolMode">
            <div class="mb-2 px-2 sm:px-0">
                <span class="text-sm text-gray-600">※記号を設定するには日時をクリックしてください</span>
            </div>
        </template>

        <div class="p-2 sm:p-4 text-gray-800 w-full">
            <form method="POST" action="{{ route('admin.reservations.confirmation.store') }}">
                @csrf
                <input type="hidden" name="name" value="{{ request('name') }}">
                <input type="hidden" name="phone" value="{{ request('phone') }}">
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="hidden" name="menu" value="{{ request('menu') }}">
                <input type="hidden" id="reserved_at" name="reserved_at" value="">
                <input type="hidden" id="note" name="note" value="{{ e(request('note')) }}">

                <div class="w-full overflow-x-auto border rounded">
                    <div class="min-w-[900px]">
                        <table class="w-full text-center text-sm table-auto border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-xs">
                                    <th class="border px-1 py-1 w-[50px] whitespace-nowrap">時間</th>
                                    @foreach ($dates as $date)
                                        <th class="border px-1 py-1 w-[50px] whitespace-nowrap leading-tight">
                                            <div>{{ $date->format('n/j') }}</div>
                                            <div>({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }})</div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ([
                                    '8:00','8:30','9:00','9:30','10:00','10:30',
                                    '11:00','11:30','12:00','12:30','13:00','13:30',
                                    '14:00','14:30','15:00','15:30','16:00','16:30',
                                    '17:00','17:30','18:00','18:30','19:00','19:30','20:00',
                                ] as $time)
                                    <tr>
                                        <td class="border px-2 py-1 bg-gray-50 font-medium whitespace-nowrap">{{ $time }}</td>
                                        @foreach ($dates as $i => $date)
                                            @php

                                                $start = \Carbon\Carbon::parse($time);
                                                $end = $start->copy()->addMinutes($duration);

                                                $dayOfWeek = $date->dayOfWeek;
                                                $isClosed = in_array($dayOfWeek, $closedDays);
                                                $isToday = $date->isSameDay(\Carbon\Carbon::today());

                                                $lunchStartTime = \Carbon\Carbon::parse($lunchStart);
                                                $lunchEndTime = \Carbon\Carbon::parse($lunchEnd);

                                                $isLunchTime = $start->between($lunchStartTime, $lunchEndTime->subMinute());

                                                $beforeOpening = $start->format('H:i') < \Carbon\Carbon::parse($businessStart)->format('H:i');
                                                $afterClosing = $end->format('H:i') > \Carbon\Carbon::parse($businessEnd)->format('H:i');

                                                $slotDateTime = $date->copy()->setTimeFromTimeString($start->format('H:i'))->format('Y-m-d H:i');
                                                $mark = $calenderMarks[$slotDateTime][0]->symbol ?? null;

                                                $isReserved = isset($reservedSlots[$slotDateTime]);
                                                $isOutOfBusiness = $beforeOpening || $afterClosing || $isLunchTime;

                                                $normalizedMark = null;

                                                if ($mark) {
                                                    $normalizedMark = trim(mb_convert_kana($mark, 'as'));
                                                    $displaySymbol = $normalizedMark;
                                                } elseif ($isClosed || $isReserved || $isOutOfBusiness) {
                                                    $displaySymbol = '×';
                                                } else {
                                                    $displaySymbol = '◎';
                                                }

                                                // 選択可能条件をモードに応じて切り替え
                                                if (request()->query('symbol_mode') == 1) {
                                                    // 記号設定モード → すべて選択可能
                                                    $isSelectable = true;
                                                } else {
                                                    // 通常予約モード → ×とtelは除外
                                                    $isSelectable = !$isClosed && !$isReserved && !$isOutOfBusiness &&
                                                        !in_array($normalizedMark, ['×', 'tel']);
                                                }

                                                $cellClasses = "border px-2 py-1 whitespace-nowrap text-center ";

                                            @endphp <!--下記部分的にCSSで実装中-->
                                            <td
                                                class="border px-2 py-1 whitespace-nowrap text-center {{ $isSelectable ? 'cursor-pointer' : '' }}"
                                                data-symbol="{{ $displaySymbol === '◯' ? '◎' : $displaySymbol }}"
                                                x-bind:style="(() => {
                                                    const el = $el;
                                                    const key = '{{ $slotDateTime }}';
                                                    const symbol = el.dataset.symbol;
                                                    const isSelected = selectedSlot === key;

                                                    // console.log(symbol); // ← 必要に応じて確認

                                                    if (symbol === '◎') {
                                                        return isSelected
                                                            ? 'background-color: #ffe4e6; color: #e11d48; font-weight: bold;'
                                                            : 'color: #e11d48;';
                                                    }

                                                    if (symbol === '×') {
                                                        return 'background-color: #e5e7eb; color: #6b7280;';
                                                    }

                                                    if (isSelected) {
                                                        return 'background-color: #ffe4e6;';
                                                    }

                                                    return '';
                                                })()"
                                                @mouseenter="
                                                    if ({{ $isSelectable ? 'true' : 'false' }}) {
                                                        const symbol = $el.dataset.symbol;
                                                        if (symbol !== '×') {
                                                            $el.style.backgroundColor = '#ffe4e6';
                                                        }
                                                    }
                                                "
                                                @mouseleave="
                                                    if ({{ $isSelectable ? 'true' : 'false' }}) {
                                                        const symbol = $el.dataset.symbol;
                                                        if (symbol !== '×') {
                                                            $el.style.backgroundColor = (selectedSlot === '{{ $slotDateTime }}') ? '#ffe4e6' : '';
                                                        }
                                                    }
                                                "
                                                @click="
                                                    symbolMode
                                                        ? (
                                                            selectedTime = '{{ $start->format('H:i') }}',
                                                            selectedDate = '{{ $date->format('Y-m-d') }}',
                                                            selectedSlot = '{{ $slotDateTime }}'
                                                        )
                                                        : selectTime('{{ $time }}', '{{ $i }}')"
                                            >
                                                {{ $displaySymbol === '◯' ? '◎' : $displaySymbol }}
                                            </td>

                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    <p>選択された日時: <span id="selected-time" class="font-bold text-blue-600">未選択</span></p>
                </div>

                <div class="mt-4 pb-24 sm:pb-8">
                    <!-- symbolModeがOFFのとき：通常の予約ボタン -->
                    <button
                        type="submit"
                        id="confirm-btn"
                        class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50"
                        x-show="!symbolMode"
                        :disabled="symbolMode || document.getElementById('reserved_at').value === ''"
                    >
                        この日時で決定
                    </button>

                    <!-- symbolModeがONのとき：戻るリンクのみ -->
                    <a
                        href="{{ route('admin.dashboard') }}"
                        x-show="symbolMode"
                        class="inline-block ml-4 text-blue-600 underline hover:text-blue-800"
                    >
                        ← ダッシュボードに戻る
                    </a>
                </div>
            </form>
        </div>

        <!-- 記号選択ドロップダウン -->
        <div
            x-show="symbolMode && selectedSlot"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="selectedSlot = null"
        >
            <form
                method="POST"
                action="{{ route('admin.calender_marks.store') }}"
                class="bg-white p-6 rounded shadow-md w-80"
            >
                @csrf
                <input type="hidden" name="date" :value="selectedDate">
                <input type="hidden" name="time" :value="selectedTime">

                <label for="symbol" class="block text-sm font-medium text-gray-700 mb-2">記号を選択</label>
                <select name="symbol" id="symbol" class="w-full border px-3 py-2 rounded mb-4" required>
                    <option value="">選択してください</option>
                    <option value="×">×（受付不可）</option>
                    <option value="tel">tel（電話）</option>
                    <option value="△">△（残りわずか）</option>
                    <option value="◎">◎（空き）</option>
                </select>

                <div class="flex justify-between items-center space-x-2">
                    <button type="button" @click="selectedSlot = null" class="px-4 py-2 bg-gray-300 rounded">
                        キャンセル
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                        登録
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const calenderDates = @json(array_map(fn($d) => $d->format('Y-m-d'), $dates));
    </script>
</x-app-layout>