<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'line_user_id', 'name', 'phone','shop_id'
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
}

