<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use app\Models\AusiInspectionItem;
class AusiBooking extends Model
{
    use HasFactory;
    protected $table = 'ausi_bookings';
    protected $fillable = [
        'transaction_no',
        'user_id',
        'email',
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
        'completed_at',
        'completed_by',

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

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }



    public function getBookingDateTime()
    {
        $startTime = explode(' - ', $this->booking_time_slot)[0] ?? '00:00';

        return Carbon::parse($this->booking_date . ' ' . $startTime);
    }

    public function inspectionItems()
    {
        return $this->hasMany(AusiInspectionItem::class);
    }
    public function inspectionsResult()
    {
        return $this->hasMany(
            AusiInspection::class,
            'ausi_booking_id'
        );
    }

    /**
     * Dynamic booking status
     */
    public function getDisplayStatusAttribute()
    {
        return match ($this->booking_status) {
            0 => 'Cancelled',
            1 => 'Scheduled',
            2 => 'Completed',
            default => 'Unknown',
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->booking_status) {
            0 => 'danger',
            1 => 'warning',
            2 => 'primary',
            default => 'secondary',
        };
    }
}
