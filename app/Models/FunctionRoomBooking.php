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
        'discount_remarks',
        'final_rate',

        'room_total',
        'addons_total',
        'total_amount',

        // Approval tracking
        'concierge_approval',
        'concierge_user_id',
        'concierge_action_at',
        'concierge_remarks',


        'admin_approval',
        'admin_user_id',
        'admin_action_at',
        'admin_remarks',

        'finance_approval',
        'finance_user_id',
        'finance_action_at',
        'finance_remarks',



        'engineering_approval',
        'engineering_user_id',
        'engineering_action_at',
        'engineering_remarks',

        'manager_approval',
        'manager_user_id',
        'manager_action_at',
        'manager_remarks',


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

    public function conciergeApprover()
    {
        return $this->belongsTo(User::class, 'concierge_user_id');
    }

    public function adminApprover()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function engineeringApprover()
    {
        return $this->belongsTo(User::class, 'engineering_user_id');
    }

    public function managerApprover()
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }
    public function financeApprover()
    {
        return $this->belongsTo(User::class, 'finance_user_id');
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
        if ($this->concierge_approval) {
            return false;
        }

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
