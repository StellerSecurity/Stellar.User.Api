<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use App\UserRole;
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

    public function login(Request $request) {

        $username = $request->input('username');
        $password = $request->input('password');

        $user = User::where('username', $username)->first();

        if($user === null) {
            return response()->json(['response_code' => 400]);
        }

        if(!Hash::check($password, $user->password)) {
            return response()->json(['response_code' => 400]);
        }

        if (Hash::needsRehash($user->password)) {
            $user->password = Hash::make($password);
            $user->save();
        }

        $token = $user->createToken("UserToken");

        return response()->json(['response_code' => 200, 'user' => $user, 'token' => $token]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) : JsonResponse {

        $username = time();

        if($username === null) {
            return response()->json(['response_code' => 400]);
        }

        $user = $this->userService->findByUsername($username);

        if($user !== null) {
            return response()->json(['response_code' => 401]);
        }

        $role = $request->input('role');

        if($role === null) {
            $role = UserRole::CUSTOMER;
        }

        $user = User::create([
            'name' => \Illuminate\Support\Str::random(16),
            'email' => $username,
            'password' => Hash::make($request->input('password')),
            'role' => $role
        ]);

        $token = $user->createToken("UserToken");

        return response()->json(['response_code' => 200, 'user' => $user, 'token' => $token]);
    }

}
