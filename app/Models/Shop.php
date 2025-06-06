<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    ];

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
