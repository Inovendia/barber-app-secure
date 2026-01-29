<?php

// app/Http/Controllers/Admin/CalenderMarkController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CalenderMark;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalenderMarkController extends Controller
{
    public function create()
    {
        return view('admin.calender_marks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'symbol' => 'required|string|in:×,tel,△,◎',
        ]);

        CalenderMark::updateOrCreate([
            'shop_id' => Auth::user()->shop_id,
            'date' => $request->date,
            'time' => $request->time,
        ],
        [   'symbol' => $request->symbol,
        ]
        );

        return redirect()->route('admin.reservations.calender', ['symbol_mode' => 1])
        ->with('status', 'カレンダー記号を登録しました');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
        ]);

        CalenderMark::where('shop_id', Auth::user()->shop_id)
            ->where('date', $request->date)
            ->where('time', $request->time)
            ->delete();

        return redirect()->back()->with('status', 'カレンダー記号を削除しました');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'required|date_format:H:i',
            'symbol' => 'required|string|in:×,◎',
        ]);

        $admin = Auth::guard('admin')->user();
        $start = Carbon::parse($request->start_date . ' ' . $request->start_time);
        $end = Carbon::parse($request->end_date . ' ' . $request->end_time);

        $shop = $admin->shop;
        $lunchStartTime = $shop && $shop->break_start ? Carbon::parse($shop->break_start) : null;
        $lunchEndTime = $shop && $shop->break_end ? Carbon::parse($shop->break_end) : null;
        $closedDays = $shop ? explode(',', $shop->closed_days ?? '') : [];
        $closedDayIndexes = collect(['日', '月', '火', '水', '木', '金', '土'])
            ->filter(fn($d) => in_array($d, $closedDays, true))
            ->keys()
            ->toArray();

        if (!in_array($start->minute, [0, 30], true) || !in_array($end->minute, [0, 30], true)) {
            return back()->withErrors(['time' => '30分刻みで指定してください。']);
        }

        if ($end->lt($start)) {
            return back()->withErrors(['time' => '終了日時は開始日時以降を指定してください。']);
        }

        $confirmedReservations = Reservation::where('shop_id', $admin->shop_id)
            ->whereBetween('reserved_at', [$start, $end])
            ->where('status', 'confirmed')
            ->get();

        $reservedSlots = [];
        foreach ($confirmedReservations as $reservation) {
            $resStart = Carbon::parse($reservation->reserved_at);
            $duration = $reservation->duration ?? 60;
            $intervals = (int) ceil($duration / 30);
            for ($i = 0; $i < $intervals; $i++) {
                $slot = $resStart->copy()->addMinutes(30 * $i)->format('Y-m-d H:i');
                $reservedSlots[$slot] = true;
            }
        }

        $rows = [];
        $skipped = 0;
        $skippedBreak = 0;
        $skippedClosed = 0;
        for ($t = $start->copy(); $t->lte($end); $t->addMinutes(30)) {
            $slotKey = $t->format('Y-m-d H:i');
            if (in_array($t->dayOfWeek, $closedDayIndexes, true)) {
                $skippedClosed++;
                continue;
            }
            if ($lunchStartTime && $lunchEndTime) {
                $slotTime = Carbon::parse($t->format('H:i'));
                if ($slotTime->between($lunchStartTime, $lunchEndTime->copy()->subMinute())) {
                    $skippedBreak++;
                    continue;
                }
            }
            if (isset($reservedSlots[$slotKey])) {
                $skipped++;
                continue;
            }

            $rows[] = [
                'shop_id' => $admin->shop_id,
                'date' => $t->toDateString(),
                'time' => $t->format('H:i:s'),
                'symbol' => $request->symbol,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($rows) > 0) {
            CalenderMark::upsert($rows, ['shop_id', 'date', 'time'], ['symbol', 'updated_at']);
        }

        return redirect()
            ->route('admin.reservations.calender', ['symbol_mode' => 1])
            ->withInput()
            ->with(
                'status',
                '一括設定しました（対象: ' . count($rows) .
                ' / 予約済み除外: ' . $skipped .
                ' / 休憩時間除外: ' . $skippedBreak .
                ' / 定休日除外: ' . $skippedClosed . '）'
            );
    }
}
