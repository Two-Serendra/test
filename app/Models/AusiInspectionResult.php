<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AusiInspectionResult extends Model
{
    use HasFactory;
    protected $table = 'ausi_inspection_results';

    protected $fillable = [
        'ausi_booking_id',
        'inspection_item_id',
        'status',
        'remarks'
    ];


    public function booking()
    {
        return $this->belongsTo(
            AusiBooking::class,
            'ausi_booking_id'
        );
    }

    public function inspectionItem()
    {
        return $this->belongsTo(
            AusiInspectionItem::class,
            'inspection_item_id'
        );
    }
}
