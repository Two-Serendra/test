<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinorWorkPermit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_permit_booking_date',
        'work_type',
        'contractor',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function minorWorkPermits()
    {
        return $this->hasMany(MinorWorkPermit::class);
    }
}
