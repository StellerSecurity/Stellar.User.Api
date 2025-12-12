<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /** Mass assignable */
    protected $fillable = [
        'name','email','password','role','vpn_sdk',
        // E2EE envelope (client-provided)
        'eak','kdf_salt','kdf_params','crypto_version','eak_recovery','recovery_meta',
        // Future (OPAQUE)
        'opaque_record',
    ];

    /** Normalize email to lowercase on set */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = mb_strtolower((string)$value);
    }

    /** Append base64 views of BLOBs */
    protected $appends = ['eak_b64','kdf_salt_b64'];

    /** Hide raw BLOBs & secrets from JSON */
    protected $hidden = [
        'password',
        'remember_token',
        'encrypt_key',
    ];

    /** Casts */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'kdf_params'        => 'array',
        'recovery_meta'     => 'array',
    ];

    /** Accessors: base64 encode BLOBs for API output */
    public function getEakB64Attribute(): ?string
    {
        return array_key_exists('eak', $this->attributes) && !is_null($this->attributes['eak'])
            ? base64_encode($this->attributes['eak'])
            : null;
    }

    public function getKdfSaltB64Attribute(): ?string
    {
        return array_key_exists('kdf_salt', $this->attributes) && !is_null($this->attributes['kdf_salt'])
            ? base64_encode($this->attributes['kdf_salt'])
            : null;
    }



}
