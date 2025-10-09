<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
        'role',
        'vpn_sdk',

        // E2EE envelope (client-provided)
        'eak',
        'kdf_salt',
        'kdf_params',
        'crypto_version',
        'eak_recovery',
        'recovery_meta',

        // Migration flags
        'e2ee_status',       // 'legacy' | 'enabled'
        'e2ee_enabled_at',

        // Future (OPAQUE)
        'opaque_record',
    ];

    // Normalize email to lowercase on set
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = mb_strtolower($value);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password','remember_token',
        'eak','kdf_salt','eak_recovery','opaque_record',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'kdf_params'    => 'array',
        'recovery_meta' => 'array'
    ];
}
