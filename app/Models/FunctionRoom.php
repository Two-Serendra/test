<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionRoom extends Model
{
    use HasFactory;
    protected $table = 'function_rooms';
    protected $fillable = [
        'function_room_section',
        'function_room_name',
        'function_room_capacity',
        'function_room_rate',
        'function_room_description',
        'function_room_policy',
        'function_room_image',
        'function_room_360',
        'featured',
    ];
}
