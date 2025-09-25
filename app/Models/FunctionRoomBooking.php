<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FunctionRoomBooking extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_no',
        'user_id',
        'unit_no',
        'resident_type',
        'function_room_id',
        'purpose_of_event',
        'event_type',
        'function_room_booking_date',
        'event_start_time',
        'event_end_time',
        'contact_number',
        'pax',
        'payment_mode',
        'has_suppliers',
        'authorization_file',


        // Pricing snapshot
        'base_rate',
        'discount',
        'final_rate',

        'room_total',
        'addons_total',
        'total_amount',

        // Approval tracking
        'admin_approval',
        'admin_approved_by',
        'admin_approved_at',
        'finance_approval',
        'finance_approved_by',
        'finance_approved_at',
        'engineering_approval',
        'engineering_approved_by',
        'engineering_approved_at',
        'manager_approval',
        'manager_approved_by',
        'manager_approved_at',
        'booking_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function functionRoom()
    {
        return $this->belongsTo(FunctionRoom::class, 'function_room_id');
    }

    public function residentDetails()
    {
        return $this->belongsTo(ResidentDetails::class);
    }


    public function authorization()
    {
        return $this->hasOne(FunctionRoomAuthorization::class, 'booking_id');
    }

    public function suppliers()
    {
        return $this->hasMany(FunctionRoomBookingSupplier::class, 'booking_id');
    }

    public function guests()
    {
        return $this->hasMany(FunctionRoomGuestList::class, 'booking_id');
    }
    // Generate Transaction No when creating
    public function adminApprover()
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function engineeringApprover()
    {
        return $this->belongsTo(User::class, 'engineering_approved_by');
    }

    public function managerApprover()
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }
    public function financeApprover()
    {
        return $this->belongsTo(User::class, 'finance_approved_by');
    }

    public function addOns()
    {
        return $this->belongsToMany(AddOn::class, 'add_on_function_room_bookings', 'function_room_booking_id', 'add_on_id')
            ->withPivot('qty', 'price');
    }

    public function getDurationInHoursAttribute()
    {
        $start = Carbon::parse($this->function_room_booking_date . ' ' . $this->event_start_time);
        $end = Carbon::parse($this->function_room_booking_date . ' ' . $this->event_end_time);

        return $start->floatDiffInHours($end); // e.g. 2.5 hours possible
    }


    public function isReadyForConfirmation()
    {
        // If authorization is uploaded, admin approval is required
        if ($this->authorization_file && !$this->admin_approval) {
            return false;
        }

        // Finance approval is always required
        if (!$this->finance_approval) {
            return false;
        }

        // If supplier is involved, engineering approval is required
        if ($this->has_suppliers && !$this->engineering_approval) {
            return false;
        }

        // Manager approval is always required
        if (!$this->manager_approval) {
            return false;
        }

        return true;
    }

}
