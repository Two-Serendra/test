<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitnessHubScheduleBlocking extends Model
{
   
    use HasFactory;
    protected $table = 'fitness_hubs_schedule_blockings';
    protected $fillable = [
        'fitness_hub_id',
        'day',
        'start_time',
        'end_time',
        'remarks',
        'repeat_weekly',
    ];

    public function fitnessHub()
    {
        return $this->belongsTo(FitnessHub::class);
    }
}
