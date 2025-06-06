<?php

// app/Http/Controllers/Admin/CalenderMarkController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CalenderMark;
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

}
