<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PestControlBooking extends Model
{
    use HasFactory;    
    protected $table = 'pest_control_bookings';
    protected $fillable = [
        'transaction_no',
        'user_id',
        'unit_no',
        'resident_type',
        'booking_date',
        'booking_time_slot',
        'remarks',
        'srf_no',
        'charged_type',
        'emergency',
        'booking_status',
        'unit_area',
        'created_by', 
    ];

    public function residentDetails()
    {
        return $this->belongsTo(ResidentDetails::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
