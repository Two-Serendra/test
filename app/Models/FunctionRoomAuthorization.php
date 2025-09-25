<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionRoomAuthorization extends Model
{
    use HasFactory;
    protected $fillable = [
        'booking_id',
        'granted_by',
        'requires_owner_approval',
        'approval_file',
        'status',
        'verified_by'
    ];

    public function booking()
    {
        return $this->belongsTo(FunctionRoomBooking::class, 'booking_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
