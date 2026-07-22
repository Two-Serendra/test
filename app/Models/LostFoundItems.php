<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostFoundItems extends Model
{
    use HasFactory;
    protected $table = 'lost_found_items';

    protected $fillable = [
        'user_id',
        'unit_no',
        'report_type',
        'item_name',
        'item_description',
        'date_lost_found',
        'location',
        'status',
        'image',
        'claimed_by',
        'approved_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
