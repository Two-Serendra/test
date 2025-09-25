<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionRoomImages extends Model
{
    use HasFactory;
    protected $fillable = ['function_room_id', 'image'];
    public function functionRoom()
    {
        return $this->belongsTo(FunctionRoom::class);
    }
}
