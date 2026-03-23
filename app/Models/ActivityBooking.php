<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActivityBooking extends Model
{
    use HasFactory;

    protected $table = 'activity_bookings';
    protected $fillable = [
        'activity_id',
        'user_id',
        'lobby',
        'unit',
        'resident_type',
        'name',
        'contact_number',
        'booking_status',
        'transaction_no',
        'booking_date',
        'booking_type',
        'booking_start_time',
        'booking_end_time',
        'created_by',
        'cancelled_at',
        'cancelled_by',
        'has_penalty',
        'penalty_amount',
        'cancelled_within_12hrs',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getBookingDateTime(): Carbon
    {
        return Carbon::parse($this->booking_date . ' ' . $this->booking_start_time);
    }

    public function isWithin12Hours(): bool
    {
        $bookingDateTime = $this->getBookingDateTime();
        return now()->greaterThanOrEqualTo($bookingDateTime->copy()->subHours(12))
            && now()->lessThan($bookingDateTime);
    }

    public function waivedBy()
    {
        return $this->belongsTo(User::class, 'waived_by');
    }
    public function applyCancellationPenalty(): void
    {
        if ($this->isWithin12Hours()) {
            $this->has_penalty = true;
            $this->penalty_amount = 1000;
            $this->cancelled_within_12hrs = 1;
        }
    }

    public function getPenaltyStartDateTime(): Carbon
    {
        return $this->getBookingDateTime()->copy()->subHours(12);
    }

    public function canCancel(): bool
    {
        return $this->booking_status == 1 && !$this->cancelled_at;
    }
}