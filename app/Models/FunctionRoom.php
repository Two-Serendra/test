<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionRoom extends Model
{
    use HasFactory;
    protected $table = 'function_rooms';
    protected $fillable = [
        'function_room_section',
        'function_room_name',
        'function_room_capacity',
        'function_room_rate',
        'function_room_description',
        'function_room_policy',
        'function_room_360',
        'featured',
        'function_room_status',
        'function_room_remarks',
        'discount',
    ];

    public function images()
    {
        return $this->hasMany(FunctionRoomImages::class);
    }

    public function firstImage()
    {
        return $this->hasOne(FunctionRoomImages::class, 'function_room_id')->oldest();
    }

    public function bookings()
    {
        return $this->hasMany(FunctionRoomBooking::class, 'function_room_id');
    }

    public function getDiscountedRateAttribute()
    {
        if ($this->discount > 0) {
            return $this->function_room_rate - ($this->function_room_rate * ($this->discount / 100));
        }
        return $this->function_room_rate;
    }
}
