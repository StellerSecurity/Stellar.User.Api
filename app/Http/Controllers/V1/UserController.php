<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordLink;
use App\Models\ResetPassword;
use App\Models\User;
use App\ResetPasswordStatus;
use App\Services\UserService;
use App\UserRole;
use Carbon\Carbon;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class UserController extends Controller
{

    private UserService $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function user(int $id): JsonResponse
    {
        $user = User::find($id);

        $token = $user->createToken("UserToken")->plainTextToken;

        return response()->json(['response_code' => 200, 'user' => $user, 'token' => $token]);
    }

    public function login(Request $request) {

        $username = $request->input('username');
        $password = $request->input('password');

        $user = User::where([['email', $username]])->first();

        if($user === null) {
            return response()->json(['response_code' => 400, 'response_message' => 'Username not found']);
        }

        if(!Hash::check($password, $user->password)) {
            return response()->json(['response_code' => 400, 'response_message' => 'Password is wrong.']);
        }

        if (Hash::needsRehash($user->password)) {
            $user->password = Hash::make($password);
            $user->save();
        }

        $token = $user->createToken("UserToken")->plainTextToken;

        return response()->json(['response_code' => 200, 'user' => $user, 'token' => $token, 'response_message' => 'OK']);
    }

    public function verifyresetpasswordandupdate(Request $request) {

        $token = $request->input('token');

        if($token === null) {
            return response()->json(['response_code' => 400, 'response_message' => 'Token not found']);
        }

        $token = Hash::make($token);
        $email = $request->input('email');
        $new_password = $request->input('new_password');

        $passwordReset = ResetPassword::where([['email', $email], ['token', $token]])->first();

        if($passwordReset === null) {
            return response()->json(['response_code' => 400, 'response_message' => 'The combination between email and token was not found.']);
        }

        if(strlen($new_password) < 2) {
            return response()->json(['response_code' => 399, 'response_message' => 'The new password must be atleast 2 characters long.']);
        }

        if($passwordReset->expires_at < Carbon::now()) {
            $passwordReset->status = ResetPasswordStatus::EXPIRED->value;
            $passwordReset->save();
            return response()->json(['response_code' => 401, 'response_message' => 'The token is expired.']);
        }

        if($passwordReset->status !== ResetPasswordStatus::ACTIVE->value) {
            return response()->json(['response_code' => 402, 'response_message' => 'Token does not exist, already used or not known ' . $passwordReset->status]);
        }

        $passwordReset->delete();

        $user = User::where('email', $email)->first();
        $user->password = Hash::make($new_password);
        $user->save();

        return response()->json(['response_code' => 200, 'response_message' => 'Password reset successfully']);

    }

    public function sendresetpasswordlink(Request $request)
    {

        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if($user === null) {
            return response()->json(['response_code' => 400, 'response_message' => 'User not found (null).']);
        }

        $token = \Illuminate\Support\Str::random(42);

        $hashed = Hash::make($token);

        $expires_at = Carbon::now()->addHours(24);

        $resetpassword = ResetPassword::create([
            'email' => $email,
            'token' => $hashed,
            'expires_at' => $expires_at,
            'status' => ResetPasswordStatus::ACTIVE->value
        ]);

        // send email here
        $mail_data = [
            'name' => $email,
            'from' => 'info@stellarsecurity.com',
            'url' => 'https://stellarsecurity.com/stellar-account/resetpasswordtoken?token=' . $token . '&email=' . $email,
        ];

        try {
            Mail::to($email)
                ->send(new ResetPasswordLink($mail_data));
        } catch(\Exception $e) {
            return response()->json(['response_code' => 401, 'response_message' => $e->getMessage()]);
        }

        return response()->json(['response_code' => 200, 'response_message' => 'OK. Reset password link sent to your email.']);

    }

    public function patch(Request $request) {

        $user = User::find($request->input('id'));

        if($user === null) {
            return response()->json(['response_code' => 400]);
        }

        $user->fill($request->all());
        $user->save();


        return response()->json(['response_code' => 200, 'user' => $user]);


    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) : JsonResponse {

        $username = $request->input('username');

        if($username === null) {
            return response()->json(['response_code' => 400, 'response_message' => 'No username provided']);
        }

        $user = $this->userService->findByUsername($username);

        if($user !== null) {
            return response()->json(['response_code' => 401, 'response_message' => 'Username already exists']);
        }

        $role = $request->input('role');

        if($role === null) {
            $role = UserRole::CUSTOMER->value;
        }

        $user = User::create([
            'name' => \Illuminate\Support\Str::random(16),
            'email' => $username,
            'password' => Hash::make($request->input('password')),
            'encrypt_key' => '',
            'role' => $role
        ]);

        $token = $user->createToken("UserToken")->plainTextToken;

        return response()->json(['response_code' => 200, 'user' => $user, 'token' => $token, 'response_message' => 'OK']);
    }

}
