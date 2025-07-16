<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class walkinWorkPermit extends Model
{
    use HasFactory;
    protected $table = 'walkin_work_permits';
    protected $fillable = [
        'permit_type',
        'unit_no',
        'section',
        'no_days',
        'work_permit_booking_date',
        'under_renovation',
        'description',
        'contractor',
        'approved_by'
    ];
}
