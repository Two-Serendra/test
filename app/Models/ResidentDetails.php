<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidentDetails extends Model
{
    use HasFactory;
    protected $table = 'resident_details';
    protected $fillable = [
        'unit_no',
        'email',
        'resident_type',
        'invite_token',
        'last_token_sent_at',
        'token_expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
