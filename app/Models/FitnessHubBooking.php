<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FitnessHubBooking extends Model
{
    use HasFactory;
    protected $table = 'fitness_hub_bookings';
    protected $fillable = [
        'fitness_hub_id',
        'user_id',
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
        'penalty_waived',
        'penalty_waived_at',
        'waived_by',
        'penalty_applied_by',
        'penalty_applied_at',
        'cancelled_within_12hrs',
    ];

    public function fitnessHub()
    {
        return $this->belongsTo(FitnessHub::class, 'fitness_hub_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
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
            $this->penalty_applied_by = auth()->id();
            $this->penalty_applied_at = now();
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

    public function applyManualPenalty(): void
    {
        $this->has_penalty = true;
        $this->penalty_amount = 1000;
        $this->cancelled_within_12hrs = 0;
        $this->penalty_applied_by = auth()->id();
        $this->penalty_applied_at = now();
    }

    public function penaltyAppliedBy()
    {
        return $this->belongsTo(User::class, 'penalty_applied_by');
    }

    public function waivePenalty(): void
    {
        if ($this->penalty_amount <= 0) {
            $this->penalty_amount = 1000; // optional safeguard
        }

        $this->penalty_waived = true;
        $this->waived_by = auth()->id();
        $this->penalty_waived_at = now();
    }
}
