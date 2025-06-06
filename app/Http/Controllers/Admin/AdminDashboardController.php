<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Stamp;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Note;
use App\Models\Customer;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();
        $admin = Auth::guard('admin')->user();
        $shopId = $admin->shop_id;

        $reservations = Reservation::where('shop_id', $shopId)
            ->whereBetween('reserved_at', [$today, $tomorrow])
            ->with('user')
            ->orderBy('reserved_at')
            ->get();

        $upcomingReservations = Reservation::where('shop_id', $shopId)
            ->where('reserved_at', '>=', $tomorrow)
            ->with('user')
            ->orderBy('reserved_at')
            ->get();

        $stamps = Stamp::where('shop_id', $shopId)
            ->with('user')
            ->orderByDesc('visit_date')
            ->limit(10)
            ->get();

        $users = User::where('shop_id', $shopId)->get();
        $customers = Customer::where('shop_id', $shopId)->get();

        $notes = Note::where('shop_id', $shopId)
            ->with('user', 'customer')
            ->orderByDesc('created_at')
            ->where('created_at', '>=', now()->subDays(3))
            ->get();

        $searchResult = null;

        if ($request->filled('selected_customer')) {
            $value = $request->query('selected_customer');

            if (Str::startsWith($value, 'user_')) {
                $id = Str::after($value, 'user_');
                $user = User::find($id);

                if ($user && $user->shop_id === $shopId) { // ✅ 店舗IDが一致するか確認
                    $request->merge(['selected_customer_id' => $id]);

                    $searchResult = [
                        'type' => 'user',
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'notes' => Note::where('user_id', $user->id)
                            ->where('shop_id', $shopId) // ✅ 他店舗のノート防止
                            ->orderByDesc('created_at')->get(),
                    ];
                }
            } elseif (Str::startsWith($value, 'customer_')) {
                $id = Str::after($value, 'customer_');
                $customer = Customer::find($id);

                if ($customer && $customer->shop_id === $shopId) { // ✅ 店舗IDが一致するか確認
                    $request->merge(['selected_customer_id' => $id]);

                    $searchResult = [
                        'type' => 'customer',
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'notes' => Note::where('customer_id', $customer->id)
                            ->where('shop_id', $shopId) // ✅ 他店舗のノート防止
                            ->orderByDesc('created_at')->get(),
                    ];
                }
            }
        }

        return view('admin.dashboard', compact(
            'reservations',
            'upcomingReservations',
            'stamps',
            'users',
            'notes',
            'customers',
            'searchResult',
            'request',
            'admin'
        ));
    }

}
