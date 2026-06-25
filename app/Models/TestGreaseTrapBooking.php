<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TestGreaseTrapBooking extends Model
{
    use HasFactory;

    protected $table = 'test_grease_trap_bookings';

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
        'created_by',
        'cancelled_at',
        'cancelled_by',
        'has_penalty',
        'penalty_amount',
        'cancelled_within_24hrs',
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
        return now()->diffInHours($this->getBookingDateTime()) < 24;
    }

    public function applyCancellationPenalty()
    {
        if ($this->isWithin24Hours()) {
            $this->has_penalty = true;
            $this->penalty_amount = 448;
        }
    }

    public function getBookingDateTime()
    {
        try {
            if (!$this->booking_date || !$this->booking_time_slot) {
                return null;
            }

            $normalized = str_replace(' - ', '-', $this->booking_time_slot);
            $startTime = trim(explode('-', $normalized)[0] ?? '00:00');

            return Carbon::parse($this->booking_date . ' ' . $startTime);

        } catch (\Exception $e) {
            \Log::error('Invalid booking datetime', [
                'date' => $this->booking_date,
                'slot' => $this->booking_time_slot,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    public static function getUsedFreeBookings($unitNo)
    {
        return self::where('unit_no', $unitNo)
            ->whereYear('booking_date', now()->year)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('booking_status', self::STATUS_CONFIRMED)
                        ->where('booking_date', '<', now()->toDateString());
                })
                    ->orWhere(function ($q3) {
                        $q3->where('booking_status', self::STATUS_CANCELLED)
                            ->where('cancelled_within_24hrs', 1);
                    });
            })
            ->count();
    }
}

