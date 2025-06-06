<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // 認証対応
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password', // フォームから受け取ってOK（ハッシュ化されるので）
        'shop_id',
    ];

    /**
     * password を自動でハッシュ化して保存
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Laravelの認証処理がこのメソッドでパスワードを取得
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * 管理している店舗とのリレーション
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
