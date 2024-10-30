<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetPassword extends Model
{

    protected $table = 'resetpasswords';

    protected $fillable = [
        'email',
        'token',
        'expires_at'
    ];

}
