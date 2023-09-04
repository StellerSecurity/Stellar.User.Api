<?php

namespace App\Services;

use App\Models\User;

class UserService
{

    public function findByUsername(string $username) {
        $user = User::where('email', $username)->first();
        return $user;
    }

}
