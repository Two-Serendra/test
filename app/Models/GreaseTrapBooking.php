<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GreaseTrapBooking extends Model
{
    use HasFactory;

    protected $table = 'grease_trap_bookings';

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
        'completed_at',
        'completed_by',
        'email',
    ];
    const STATUS_CANCELLED_OLD = 2;
    const STATUS_CANCELLED = 0;
    const STATUS_SCHEDULED = 1;
    const STATUS_COMPLETED = 2;
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

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function isWithin24Hours()
    {
        $bookingDateTime = $this->getBookingDateTime();

        if (!$bookingDateTime) {
            return false;
        }

        return now()->gte($bookingDateTime->copy()->subHours(24));
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
                $q->whereIn('booking_status', [
                    self::STATUS_SCHEDULED,
                    self::STATUS_COMPLETED,
                ])->orWhere(function ($q2) {
                    $q2->where('booking_status', self::STATUS_CANCELLED)
                        ->where('cancelled_within_24hrs', 1);
                });
            })
            ->count();
    }

    public function getGTStatusAttribute()
    {
        return match ($this->booking_status) {

            self::STATUS_CANCELLED => [
                'label' => 'CANCELLED',
                'badge' => 'danger',
            ],

            self::STATUS_SCHEDULED => [
                'label' => 'SCHEDULED',
                'badge' => 'warning',
            ],

            self::STATUS_COMPLETED => [
                'label' => 'COMPLETED',
                'badge' => 'primary',
            ],

            default => [
                'label' => 'UNKNOWN',
                'badge' => 'secondary',
            ],
        };
    }
}

