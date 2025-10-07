<?php
namespace App\Http\Controllers\Line;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;

class LiffEntryController extends Controller
{
    public function entry(Request $request)
    {
        \Log::info('📩 [LiffEntryController] POST /liff/entry received', [
            'payload' => $request->all(),
        ]);

        $shopId = $request->input('shop_id');
        $lineUserId = $request->input('line_user_id');
        \Log::info('🔍 Identifying shop...', ['shop_id' => $shopId, 'line_user_id' => $lineUserId]);

        $shop = null;

        if ($shopId) {
            $shop = \App\Models\Shop::find($shopId);
            \Log::info('🏪 Shop found by ID', ['shop' => $shop?->name]);
        } elseif ($lineUserId) {
            $shop = \App\Models\Customer::where('line_user_id', $lineUserId)
                ->with('shop')->latest('updated_at')->first()?->shop;
            \Log::info('🧭 Shop found by LINE user', ['shop' => $shop?->name]);
        }

        if (!$shop) {
            $shop = \App\Models\Shop::where('is_default', true)->first() ?? \App\Models\Shop::first();
            \Log::info('🪶 Fallback shop selected', ['shop' => $shop?->name]);
        }

        $redirectUrl = "https://rezamie.com/reserve/{$shop->public_token}/form";
        \Log::info('🚚 Redirecting to', ['url' => $redirectUrl]);

        return redirect()->away($redirectUrl);
    }
}
