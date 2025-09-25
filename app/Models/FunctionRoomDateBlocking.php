<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionRoomDateBlocking extends Model
{
    use HasFactory;
    use HasFactory;
    protected $table = 'function_room_date_blockings';
    protected $fillable = [
        'function_room_id',
        'blocking_status',
        'blocking_remarks',
        'date_blocking_start',
        'date_blocking_end',
    ];

    public function functionRoom()
    {
        return $this->belongsTo(FunctionRoom::class, 'function_room_id');
    }

}
