<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidentDetails extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'resident_type',
        'section',
        'unit_no',
    ];
}

