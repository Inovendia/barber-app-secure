<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
            $file = $request->file('image');
            $path = 'note_images/' . uniqid() . '.' . $file->getClientOriginalExtension();

            Storage::disk('s3')->put($path, file_get_contents($file), [
                'ACL' => 'bucket-owner-full-control',
                'ContentType' => $file->getMimeType(), // 明示すると便利
            ]);

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

        foreach ($notes as $note) {
            if ($note->image_path) {
                $note->signed_url = Storage::disk('s3')->temporaryUrl(
                    $note->image_path,
                    now()->addMinutes(10)
                );
            }
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
