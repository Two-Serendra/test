<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionRoomBookingSupplier extends Model
{
    use HasFactory;
    protected $fillable = ['booking_id', 'name', 'attachment'];

    public function booking()
    {
        return $this->belongsTo(FunctionRoomBooking::class, 'booking_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
