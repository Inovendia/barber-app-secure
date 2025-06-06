<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'closed_days',
        'business_start',
        'business_end',
        'break_start',
        'break_end',
        'public_token', // 忘れずにここにも追加
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shop) {
            if (empty($shop->public_token)) {
                $shop->public_token = Str::random(10);
            }
        });
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function stamps()
    {
        return $this->hasMany(Stamp::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }
}
