<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityBooking extends Model
{
    use HasFactory;
    protected $table = 'activity_bookings';
    protected $fillable = [
        'activity_id',
        'user_id',
        'lobby',
        'unit',
        'name',
        'contact_number',
        'booking_status',
        'transaction_no',
        'booking_date',
        'booking_start_time',
        'booking_end_time',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
