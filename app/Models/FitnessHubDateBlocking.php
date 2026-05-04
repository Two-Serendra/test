<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitnessHubDateBlocking extends Model
{
    use HasFactory;
    protected $table = 'fitness_hub_date_blockings';
    protected $fillable = [
        'fitness_hub_id',
        'blocking_status',
        'blocking_remarks',
        'date_blocking_start',
        'date_blocking_end',
    ];
    public function fitnessHub()
    {
        return $this->belongsTo(FitnessHub::class, 'fitness_hub_id');
    }

    
}
