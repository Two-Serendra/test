<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
     protected $table = 'services';
    protected $fillable = [
        'service_name',
        'service_short_description',
        'service_long_description',
        'service_image'
    ];
}
