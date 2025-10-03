<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionRoomDiscount extends Model
{
    use HasFactory;
    protected $table = 'function_room_discounts';
    protected $fillable = [
        'function_room_id',
        'discount',
        'remarks',
        'start_date',
        'end_date',
    ];

    public function functionRoom()
    {
        return $this->belongsTo(FunctionRoom::class, 'function_room_id');
    }
}
