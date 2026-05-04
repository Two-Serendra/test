<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitnessHub extends Model
{
    use HasFactory;
    protected $table = 'fitness_hubs';
    protected $fillable = [
        'fitness_hub_name',
        'fitness_hub_image',
        'fitness_hub_description',
        'fitness_hub_remarks',
        'fitness_hub_max_booking',
        'fitness_hub_status',
        'fitness_hub_start_time',
        'fitness_hub_end_time',
    ];


    public function getStartTimeFormattedAttribute()
    {
        return \Carbon\Carbon::parse($this->fitness_hub_start_time)->format('h:i A');
    }

    public function getEndTimeFormattedAttribute()
    {
        return \Carbon\Carbon::parse($this->fitness_hub_end_time)->format('h:i A');
    }
}
