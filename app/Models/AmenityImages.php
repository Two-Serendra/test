<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmenityImages extends Model
{
    use HasFactory;
    protected $fillable = ['amenity_id', 'image'];
    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }
}
