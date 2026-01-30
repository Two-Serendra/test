<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivitySchedule extends Model
{
    use HasFactory;
    protected $table = 'activity_schedules';
    protected $fillable = [
        'day',
        'start_time', 
        'end_time', 
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');    
    }
}
