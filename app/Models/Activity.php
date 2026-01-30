<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;
    protected $table = 'activities';
    protected $fillable = [
        'activity_name',
        'activity_image',
        'activity_description',
        'activity_remarks',
        'activity_status',
        'activity_space',
        'activity_max_booking',
    ];

    public function amenity()
    {
        return $this->belongsTo(Amenity::class, 'amenity_id');
    }

    public function ActivityBooking()
    {
        return $this->hasMany(ActivityBooking::class);
    }
    public function schedules()
    {
        return $this->hasMany(ActivitySchedule::class);
    }
}
