<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'note',
        'shop_id',
    ];

    // 顧客に紐づくメモ
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function images()
    {
        return $this->hasMany(CustomerImage::class);
    }

}