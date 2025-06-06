{{-- resources/views/reserve/calender.blade.php --}}
<x-guest-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">予約日時の選択</h2>
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
        $prevParams = array_merge(request()->all(), ['start_offset' => max($startOffset - 14, 0)]);
        $nextParams = array_merge(request()->all(), ['start_offset' => $startOffset + 14]);
    @endphp

    <div class="mb-4 px-2 sm:px-0">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <!-- 前へボタン -->
            <a href="{{ route('reserve.calender', $prevParams) }}"
            class="bg-blue-100 text-blue-700 px-3 py-1 rounded font-medium flex items-center hover:bg-blue-200 active:bg-blue-300 whitespace-nowrap">
                <span class="mr-1 text-lg">◀</span> 前へ
            </a>
            <!-- 日付範囲 -->
            <span class="font-bold text-lg text-center mx-2 flex-1">
                {{ $dates[0]->format('Y年n月j日') }} 〜 {{ $dates[13]->format('n月j日') }}
            </span>
            <!-- 次へボタン -->
            <a href="{{ route('reserve.calender', $nextParams) }}"
            class="bg-blue-100 text-blue-700 px-3 py-1 rounded font-medium flex items-center hover:bg-blue-200 active:bg-blue-300 whitespace-nowrap">
                次へ <span class="ml-1 text-lg">▶</span>
            </a>
        </div>
    </div>



    <div class="p-2 sm:p-4 text-gray-800 w-full">
        <form method="POST" action="{{ route('reserve.confirmation') }}">
            @csrf
            <input type="hidden" name="line_user_id" value="{{ request('line_user_id') }}">
            <input type="hidden" name="name" value="{{ request('name') }}">
            <input type="hidden" name="phone" value="{{ request('phone') }}">
            <input type="hidden" name="category" value="{{ request('category') }}">
            <input type="hidden" name="menu" value="{{ request('menu') }}">
            <input type="hidden" name="shop_id" value="{{ request('shop_id') }}">
            <input type="hidden" id="reserved_at" name="reserved_at" value="">

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
                                            $now = \Carbon\Carbon::now();
                                            $plusTwoHours = $now->copy()->addHours(2);
                                            $start = $date->copy()->setTimeFromTimeString($time);
                                            $end = $start->copy()->addMinutes($duration);

                                            $isToday = $date->isSameDay($now);
                                            $isBefore2HoursFromNow = $isToday && $start->lt($plusTwoHours);

                                            $dayOfWeek = $date->dayOfWeek;
                                            $isClosed = in_array($dayOfWeek, $closedDays);

                                            $lunchStartTime = $start->copy()->setTimeFromTimeString($lunchStart);
                                            $lunchEndTime   = $start->copy()->setTimeFromTimeString($lunchEnd)->subMinute();
                                            $isLunchTime    = $start->between($lunchStartTime, $lunchEndTime);


                                            $beforeOpening = $start->format('H:i') < \Carbon\Carbon::parse($businessStart)->format('H:i');
                                            $afterClosing = $end->format('H:i') > \Carbon\Carbon::parse($businessEnd)->format('H:i');

                                            $slotDateTime = $date->copy()->setTimeFromTimeString($start->format('H:i'))->format('Y-m-d H:i');
                                            $mark = $calenderMarks[$slotDateTime][0]->symbol ?? null;

                                            $isReserved = isset($reservedSlots[$slotDateTime]);
                                            $isOutOfBusiness = $beforeOpening || $afterClosing || $isLunchTime;

                                            $normalizedMark = $mark ? trim(mb_convert_kana($mark, 'as')) : null;

                                            if ($isBefore2HoursFromNow) {
                                                $displaySymbol = '×';
                                            } elseif ($normalizedMark === '◯') {
                                                $displaySymbol = '◎';
                                            } elseif ($normalizedMark) {
                                                $displaySymbol = $normalizedMark;
                                            } elseif ($isClosed || $isReserved || $isOutOfBusiness) {
                                                $displaySymbol = '×';
                                            } else {
                                                $displaySymbol = '◎';
                                            }

                                            $isSelectable = $displaySymbol === '◎';
                                            $cellClasses = 'border px-2 py-1 whitespace-nowrap text-center ' . ($isSelectable ? 'cursor-pointer' : '');
                                        @endphp
                                        <td
                                            class="{{ $cellClasses }}"
                                            data-symbol="{{ $displaySymbol }}"
                                            data-slot="{{ $slotDateTime }}"
                                            {!! $isSelectable ? "onclick=\"selectTime('{$time}', '{$i}')\"" : '' !!}
                                            onmouseenter="if('{{ $displaySymbol }}' === '◎') this.style.backgroundColor = '#ffe4e6'"
                                            onmouseleave="if('{{ $displaySymbol }}' === '◎' && this.dataset.slot !== selectedSlot) this.style.backgroundColor = ''"
                                            style="{{ $displaySymbol === '×' ? 'background-color: #f3f4f6; color: #9ca3af;' : ($displaySymbol === '◎' ? 'color: #e11d48;' : '') }}"
                                        >
                                            @if ($displaySymbol === 'TEL')
                                                <div>
                                                    TEL<br>
                                                    <a href="tel:{{ $shopPhone ?? '09012345678' }}" class="underline text-blue-600">📞</a>
                                                </div>
                                            @else
                                                {{ $displaySymbol }}
                                            @endif
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
                <button type="submit" id="confirm-btn"
                        class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50"
                        disabled>
                    この日時で決定
                </button>
            </div>
        </form>
    </div>

    <script>
        const calenderDates = @json(array_map(fn($d) => $d->format('Y-m-d'), $dates));
        let selectedSlot = null;

        function selectTime(time, dayOffset) {
            const selectedDateStr = calenderDates[parseInt(dayOffset)];
            const timeString = time.padStart(5, '0') + ':00';
            const reservedAt = `${selectedDateStr}T${timeString}`;
            const currentSlot = `${selectedDateStr} ${time.padStart(5, '0')}`;

            document.getElementById('selected-time').innerText = `${selectedDateStr} ${time}`;
            document.getElementById('reserved_at').value = reservedAt;

            selectedSlot = currentSlot;

            // 🔄 全ての◎セルの背景色をリセット
            document.querySelectorAll('td[data-symbol="◎"]').forEach(td => {
                td.style.backgroundColor = '';
            });

            // ✅ 選択したセルだけに薄いピンク色を適用
            const selectedTd = document.querySelector(`td[data-slot="${selectedSlot}"]`);
            if (selectedTd) {
                selectedTd.style.backgroundColor = '#ffe4e6';
            }

            const btn = document.getElementById('confirm-btn');
            btn.disabled = false;
            btn.classList.remove('opacity-50');
        }
    </script>

</x-guest-layout>
