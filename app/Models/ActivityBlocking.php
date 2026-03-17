<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityBlocking extends Model
{
    use HasFactory;
    protected $table = 'activity_blockings';
    protected $fillable = [
        'activity_id',
        'day',
        'start_time',
        'end_time',
        'remarks',
        'repeat_weekly',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
