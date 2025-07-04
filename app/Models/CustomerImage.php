<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'image_path',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function images()
    {
        return $this->hasMany(CustomerImage::class);
    }

}
