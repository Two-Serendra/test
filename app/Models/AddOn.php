<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOn extends Model
{
    use HasFactory;
    protected $fillable = [
        'item',
        'qty',
        'price',
        'status',
    ];

    public function bookings()
    {
        return $this->belongsToMany(FunctionRoomBooking::class, 'add_on_function_room_bookings')
            ->withPivot(['qty', 'price'])
            ->withTimestamps();
    }
}
