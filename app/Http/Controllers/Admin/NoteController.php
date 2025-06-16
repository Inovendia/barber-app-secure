<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NoteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_type_and_id' => 'required|string',
            'content' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,JPEG,PNG,JPG|max:4096',
        ]);

        $value = $request->input('customer_type_and_id');

        $note = new Note();
        $note->content = $request->input('content');
        $note->shop_id = Auth::guard('admin')->user()->shop_id;
        $note->created_by = Auth::guard('admin')->user()->name ?? '管理者';

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('note_images', 's3');
            $note->image_path = $path;
        }

        if (Str::startsWith($value, 'user_')) {
            $note->user_id = Str::after($value, 'user_');
        } elseif (Str::startsWith($value, 'customer_')) {
            $note->customer_id = Str::after($value, 'customer_');
        }

        $note->save();

        return redirect()->back()->with('status', 'メモを追加しました');
    }

    public function show($type, $id)
    {
        if ($type === 'user') {
            $notes = Note::where('user_id', $id)->with('user')->orderByDesc('created_at')->get();
            $name = optional($notes->first()->user)->name ?? '未登録ユーザー';
        } elseif ($type === 'customer') {
            $notes = Note::where('customer_id', $id)->with('customer.images')->orderByDesc('created_at')->get();
            $name = optional($notes->first()->customer)->name ?? '未登録顧客';
        } else {
            abort(404);
        }

        return view('admin.notes.show', compact('notes', 'name', 'type', 'id'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $user = \App\Models\User::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->first();

        $customer = \App\Models\Customer::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->first();

        if ($user) {
            $notes = \App\Models\Note::where('user_id', $user->id)->orderByDesc('created_at')->get();
            return view('admin.notes.search_result', [
                'name' => $user->name,
                'phone' => $user->phone,
                'notes' => $notes,
                'type' => 'user',
            ]);
        } elseif ($customer) {
            $notes = \App\Models\Note::where('customer_id', $customer->id)->with(['customer.images'])->orderByDesc('created_at')->get();
            return view('admin.notes.search_result', [
                'name' => $customer->name,
                'phone' => $customer->phone,
                'notes' => $notes,
                'type' => 'customer',
            ]);
        } else {
            return redirect()->route('admin.dashboard')->with('status', '該当する顧客が見つかりませんでした');
        }
    }

}
