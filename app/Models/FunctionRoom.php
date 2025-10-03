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


    public function discounts()
    {
        return $this->hasMany(FunctionRoomDiscount::class, 'function_room_id');
    }

    // Get only the active discount (based on current date)
    public function activeDiscount()
    {
        return $this->hasOne(FunctionRoomDiscount::class, 'function_room_id')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }

    // Calculate discounted rate
    public function getDiscountedRateAttribute()
    {
        $discount = $this->activeDiscount()->first();

        if ($discount) {
            return $this->function_room_rate - ($this->function_room_rate * ($discount->discount / 100));
        }

        return $this->function_room_rate;
    }
}
