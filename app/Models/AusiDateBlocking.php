<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class AusiDateBlocking extends Model
{
    use HasFactory;
    protected $table = 'ausi_date_blockings';

    protected $fillable = [
        'start_date',
        'end_date',
        'reason',
        'created_by',
    ];


    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

}