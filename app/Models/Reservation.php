<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\LineNotificationService;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'category',
        'menu',
        'reserved_at',
        'status',
        'note',
        'line_token',
        'line_user_id',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    // app/Models/Reservation.php

    public function cancelWithNotification(LineNotificationService $lineService)
    {
        $this->status = 'canceled';
        $this->save();

        // ユーザー通知
        $messageUser = "❌ ご予約をキャンセルしました\n\n"
            . "📅 日時：{$this->reserved_at->format('Y年m月d日 H:i')}\n"
            . "✂️ メニュー：{$this->menu}";

        $lineService->notifyUser($this->shop, $this->line_user_id, $messageUser);

        // 管理者通知
        $messageAdmin = "⚠️ キャンセルが発生しました\n\n"
            . "👤 顧客：{$this->user->name}\n"
            . "📞 電話：{$this->user->phone}\n"
            . "📅 日時：{$this->reserved_at->format('Y年m月d日 H:i')}\n"
            . "✂️ メニュー：{$this->menu}";

        $lineService->notifyAdmin($this->shop, $messageAdmin);
    }
}
