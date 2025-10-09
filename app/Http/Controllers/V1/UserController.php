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
use Illuminate\Support\Str;


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

        $tokenSource = $request->input('token');

        if(empty($tokenSource)) {
            $tokenSource = "Stellar.User.API";
        }

        $token = $user->createToken($tokenSource)->plainTextToken;

        return response()->json(['response_code' => 200, 'user' => $user, 'token' => $token, 'response_message' => 'OK']);
    }

    public function verifyresetpasswordandupdate(Request $request) {

        $token = $request->input('token');

        if($token === null) {
            return response()->json(['response_code' => 400, 'response_message' => 'Token not found']);
        }

        $email = $request->input('email');
        $new_password = $request->input('new_password');

        $passwordReset = ResetPassword::where('email', $email)->get();

        if($passwordReset === null) {
            return response()->json(['response_code' => 400, 'response_message' => 'The combination between email and token  was not found (1).']);
        }

        $found = false;

        foreach ($passwordReset as $reset) {

            if(Hash::check($token, $reset->token)) {
                $found = true;
                $passwordReset = $reset;
                break;
            }
        }

        if(!$found) {
            return response()->json(['response_code' => 400, 'response_message' => 'The combination between email and token was not found. (2)']);
        }

        if(strlen($new_password) < 4) {
            return response()->json(['response_code' => 399, 'response_message' => 'The new password must be atleast 4 characters long.']);
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

    public function verifyresetpasswordconfirmationcode(Request $request)
    {

        $confirmation_code = $request->input('confirmation_code');

        if ($confirmation_code === null) {
            return response()->json(['response_code' => 400, 'response_message' => 'Confirmation not found']);
        }

        $email = $request->input('email');
        $new_password = $request->input('new_password');

        $passwordReset = ResetPassword::where([['email', $email], ['confirmation_code', '!=', null]])->get();

        if($passwordReset === null) {
            return response()->json(['response_code' => 400, 'response_message' => 'The combination between email and confirmation was not found (1).']);
        }

        $found = false;

        foreach ($passwordReset as $reset) {
            if(Hash::check($confirmation_code, $reset->confirmation_code)) {
                $found = true;
                $passwordReset = $reset;
                break;
            }
        }

        if(!$found) {
            return response()->json(['response_code' => 400, 'response_message' => 'The combination between email and confirmation code was not found. (2)']);
        }

        if (strlen($new_password) < 4) {
            return response()->json(['response_code' => 399, 'response_message' => 'The new password must be atleast 4 characters long.']);
        }

        if ($passwordReset->expires_at < Carbon::now()) {
            $passwordReset->status = ResetPasswordStatus::EXPIRED->value;
            $passwordReset->save();
            return response()->json(['response_code' => 401, 'response_message' => 'The RESET is expired.']);
        }

        if ($passwordReset->status !== ResetPasswordStatus::ACTIVE->value) {
            return response()->json(['response_code' => 402, 'response_message' => 'Code does not exist, already used or not known ' . $passwordReset->status]);
        }

        $passwordReset->delete();

        $user = User::where('email', $email)->first();
        $user->password = Hash::make($new_password);
        $user->save();

        return response()->json(['response_code' => 200, 'response_message' => 'Password reset successfully']);

    }

    public function sendresetpasswordlink(Request $request): JsonResponse
    {

        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if($user === null) {
            return response()->json(['response_code' => 400, 'response_message' => 'User not found (null).']);
        }

        $token = Str::random(42);

        $hashed_token = Hash::make($token);

        $expires_at = Carbon::now()->addHours(24);

        $confirmation_code = $request->input('confirmation_code');

        $confirmation_code_hashed = null;

        if($confirmation_code !== null) {
            $confirmation_code_hashed = Hash::make($confirmation_code);
        }

        $resetpassword = ResetPassword::create([
            'email' => $email,
            'token' => $hashed_token,
            'expires_at' => $expires_at,
            'status' => ResetPasswordStatus::ACTIVE->value,
            'confirmation_code' => $confirmation_code_hashed
        ]);

        // send email here
        $mail_data = [
            'name' => $email,
            'confirmation_code' => $confirmation_code,
            'from' => 'info@stellarsecurity.com',
            'url' => 'https://stellarsecurity.com/stellar-account/resetpasswordtoken?token=' . $token . '&email=' . $email,
        ];

        try {
            Mail::to($email)
                ->send(new ResetPasswordLink($mail_data));
        } catch(\Exception $e) {
            return response()->json(['response_code' => 401, 'response_message' => 'Email could not be sent.']);
        }

        return response()->json(['response_code' => 200, 'response_message' => 'Reset password link sent to your email.']);

    }

    public function patch(Request $request): JsonResponse
    {

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
     * @return JsonResponse
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

        $vpn_sdk = $request->input('vpn_sdk');
        if($vpn_sdk === null) {
            $vpn_sdk = 0;
        }

        if($request->string('eak') == null) {

            $user = User::create([
                'name' => Str::random(16),
                'email' => $username,
                'password' => Hash::make($request->input('password')),
                'encrypt_key' => '',
                'role' => $role,
                'vpn_sdk' => $vpn_sdk
            ]);

        } else {

            $user = User::create([
                'name' => Str::random(16),
                'email' => $username,
                'password' => Hash::make($request->input('password')),
                'encrypt_key' => '',
                'role' => $role,
                'vpn_sdk' => $vpn_sdk,
                // E2EE blobs (client-provided)
                'eak'            => base64_decode($request->string('eak'), true),
                'kdf_salt'       => base64_decode($request->string('kdf_salt'), true),
                'kdf_params'     => $request->input('kdf_params'),
                'crypto_version' => $request->input('crypto_version', 'v1'),
                'eak_recovery'   => $request->filled('eak_recovery') ? base64_decode($request->string('eak_recovery'), true) : null,
                'recovery_meta'  => $request->input('recovery_meta'),
            ]);

        }

        $tokenSource = $request->input('token');

        if(empty($tokenSource)) {
            $tokenSource = "Stellar.User.API";
        }

        $token = $user->createToken($tokenSource)->plainTextToken;

        return response()->json(['response_code' => 200, 'user' => $user, 'token' => $token, 'response_message' => 'OK']);
    }

}
