<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionRoomGuestList extends Model
{
    use HasFactory;
    protected $fillable = ['booking_id', 'guest_name'];

    public function booking()
    {
        return $this->belongsTo(FunctionRoomBooking::class, 'booking_id');
    }
}
