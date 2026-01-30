<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityDateBlocking extends Model
{
    use HasFactory;
    protected $table = 'activity_date_blockings';
    protected $fillable = [
        'amenity_id',
        // 'activity_id',
        'blocking_status',
        'blocking_remarks',
        'date_blocking_start', 
        'date_blocking_end', 
    ];

    public function amenity()
    {
        return $this->belongsTo(Amenity::class, 'amenity_id');
    }

    // Relationship with Activity
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
}
