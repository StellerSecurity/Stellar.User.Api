<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Psy\Util\Str;

class UserController extends Controller
{

    private UserService $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) : JsonResponse {

        $username = $request->input('username');

        if($username === null) {
            return response()->json(['response_code' => 400]);
        }

        $user = $this->userService->findByUsername($username);

        if($user !== null) {
            return response()->json(['response_code' => 401]);
        }

        $user = User::create([
            'name' => \Illuminate\Support\Str::random(16),
            'email' => $username,
            'password' => Hash::make($request->input('password'))
        ]);

        return response()->json(['response_code' => 200, 'user' => $user]);
    }

}
