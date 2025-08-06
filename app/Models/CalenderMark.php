<?php

// app/Models/CalenderMark.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalenderMark extends Model
{
    protected $fillable = [
        'shop_id',
        'date',
        'time',
        'symbol',
    ];
}
