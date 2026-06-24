<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AusiInspectionItem extends Model
{
    use HasFactory;
    protected $table = 'ausi_inspection_items';
    protected $fillable = [
        'item_name',
        'option_1',
        'option_2',
        'status',
    ];
}
