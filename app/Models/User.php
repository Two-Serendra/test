<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\ResidentDetails;
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
        // 'email_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        // 'role' => 'integer',
    ];

    public function minorWorkPermits()
    {
        return $this->hasMany(MinorWorkPermit::class);
    }
    public function authorizedEmail()
    {
        return $this->hasOne(ResidentDetails::class, 'email', 'email');
    }

    public function residentDetail()
    {
        return $this->hasOne(ResidentDetails::class, 'user_id', 'id');
    }

    protected static function booted()
    {
        static::updated(function ($user) {
            if ($user->hasVerifiedEmail()) {
                $user->updateQuietly(['is_active' => true]);
            }
        });
    }


}
