<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class AusiBooking extends Model
{
    use HasFactory;
    protected $table = 'ausi_bookings';
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
        'emergency',
        'booking_status',
        'unit_area',
        'created_by',
        'cancelled_at',
        'cancelled_by',
    ]; // Optional: automatically include in JSON/array
    protected $appends = ['display_status'];

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

    public function getBookingDateTime()
    {
        $startTime = explode(' - ', $this->booking_time_slot)[0] ?? '00:00';

        return Carbon::parse($this->booking_date . ' ' . $startTime);
    }

    /**
     * Dynamic booking status
     */
    public function getDisplayStatusAttribute()
    {
        // Cancelled
        if ($this->booking_status == 2) {
            return 'Cancelled';
        }

        // Active booking logic
        if ($this->booking_status == 1) {

            $timeParts = explode(' - ', $this->booking_time_slot);

            $startTime = trim($timeParts[0] ?? '00:00');
            $endTime = trim($timeParts[1] ?? '23:59');

            // Convert NN to PM
            $startTime = str_replace('NN', 'PM', $startTime);
            $endTime = str_replace('NN', 'PM', $endTime);

            $startDateTime = Carbon::parse($this->booking_date . ' ' . $startTime);
            $endDateTime = Carbon::parse($this->booking_date . ' ' . $endTime);

            $now = Carbon::now();

            // Ongoing
            if ($now->between($startDateTime, $endDateTime)) {
                return 'On Going';
            }

            // Completed
            if ($now->gt($endDateTime)) {
                return 'Completed';
            }

            // Upcoming booking
            return 'Booked';
        }

        return 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->display_status) {
            'Booked' => 'primary',
            'On Going' => 'warning',
            'Completed' => 'primary',
            'Cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
