<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOnFunctionRoomBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'function_room_booking_id',
        'add_on_id',
        'qty',
        'price',
    ];
    public function booking()
    {
        return $this->belongsTo(FunctionRoomBooking::class, 'function_room_booking_id');
    }
}
