<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class TestPestControlBooking extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_no',
        'user_id',
        'unit_no',
        'resident_type',
        'name',
        'booking_date',
        'booking_time_slot',
        'remarks',
        'srf_no',
        'charged_type',
        'emergency',
        'booking_status',
        'unit_area',
        'created_by',
        'cancelled_at',
        'cancelled_by',
        'has_penalty',
        'penalty_amount',
        'email',
    ];


    const STATUS_CONFIRMED = 1;
    const STATUS_CANCELLED = 2;
    const CHARGE_FREE = 1;
    const CHARGE_BILLABLE = 2;


    public function residentDetails()
    {
        return $this->belongsTo(ResidentDetails::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }


    public function isWithin24Hours()
    {
        return now()->diffInHours($this->getBookingDateTime(), false) < 24;
    }

    public function applyCancellationPenalty()
    {
        if ($this->isWithin24Hours()) {
            $this->has_penalty = true;
            $this->penalty_amount = 350;
        }
    }

    public function getBookingDateTime()
    {
        $startTime = explode(' - ', $this->booking_time_slot)[0] ?? '00:00';
        return Carbon::parse($this->booking_date . ' ' . $startTime);
    }
}

