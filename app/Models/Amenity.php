<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;
    protected $table = 'amenities';
    protected $fillable = [
        'amenity_name',
        'amenity_image',
        'amenity_description',
        'amenity_remarks',
        'amenity_status',
    ];

}
