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
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class UserController extends Controller
{

    private UserService $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    // LEAVE AS-IS (per your request)
    public function user(int $id): JsonResponse
    {
        $user = User::find($id);

        $token = $user->createToken("UserToken")->plainTextToken;

        return response()->json(['response_code' => 200, 'user' => $user, 'token' => $token]);
    }

    public function login(Request $request): JsonResponse
    {
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, $perMinute = 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'response_code'    => 429,
                'response_message' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
            ], 429);
        }

        // Decay: 60 seconds
        RateLimiter::hit($throttleKey, 60);

        $username = $request->input('username');
        $password = $request->input('password');

        if ($username === null || $password === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'Username or password is wrong',
            ]);
        }

        $user = User::where('email', $username)->first();

        if ($user === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'Username or password is wrong',
            ]);
        }

        if (! Hash::check($password, $user->password)) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'Username or password is wrong',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Upgrade hash if needed (e.g. when rounds/driver changed)
        if (Hash::needsRehash($user->password)) {
            $user->password = Hash::make($password);
            $user->save();
        }

        $tokenSource = $request->input('token');

        if (empty($tokenSource)) {
            $tokenSource = "Stellar.User.API";
        }

        $token = $user->createToken($tokenSource)->plainTextToken;

        return response()->json([
            'response_code'    => 200,
            'user'             => $user,
            'token'            => $token,
            'response_message' => 'OK',
        ]);
    }

    public function verifyresetpasswordandupdate(Request $request): JsonResponse
    {
        $token        = $request->input('token');
        $email        = $request->input('email');
        $new_password = $request->input('new_password');

        if ($token === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'Token not found',
            ]);
        }

        if ($email === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'Email not found',
            ]);
        }

        // Rate limit per email on reset attempts
        $resetKey = 'reset_token:' . Str::lower($email);
        if (RateLimiter::tooManyAttempts($resetKey, 5)) {
            $seconds = RateLimiter::availableIn($resetKey);

            return response()->json([
                'response_code'    => 429,
                'response_message' => 'Too many reset attempts. Please try again in ' . $seconds . ' seconds.',
            ], 429);
        }
        RateLimiter::hit($resetKey, 300); // 5 minutes decay

        if ($new_password === null || strlen($new_password) < 6) {
            return response()->json([
                'response_code'    => 399,
                'response_message' => 'The new password must be at least 6 characters long.',
            ]);
        }

        $resets = ResetPassword::where('email', $email)->get();

        if ($resets->isEmpty()) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'The combination between email and token was not found (1).',
            ]);
        }

        $passwordReset = null;

        foreach ($resets as $reset) {
            if (Hash::check($token, $reset->token)) {
                $passwordReset = $reset;
                break;
            }
        }

        if (! $passwordReset) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'The combination between email and token was not found. (2)',
            ]);
        }

        if ($passwordReset->expires_at < Carbon::now()) {
            $passwordReset->status = ResetPasswordStatus::EXPIRED->value;
            $passwordReset->save();

            return response()->json([
                'response_code'    => 401,
                'response_message' => 'The token is expired.',
            ]);
        }

        if ($passwordReset->status !== ResetPasswordStatus::ACTIVE->value) {
            return response()->json([
                'response_code'    => 402,
                'response_message' => 'Token does not exist, already used or not known ' . $passwordReset->status,
            ]);
        }

        $passwordReset->delete();

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'response_code'    => 404,
                'response_message' => 'User not found.',
            ], 404);
        }

        $user->password = Hash::make($new_password);
        $user->save();

        RateLimiter::clear($resetKey);

        return response()->json([
            'response_code'    => 200,
            'response_message' => 'Password reset successfully',
        ]);
    }

    public function verifyresetpasswordconfirmationcode(Request $request): JsonResponse
    {
        $confirmation_code = $request->input('confirmation_code');
        $email             = $request->input('email');
        $new_password      = $request->input('new_password');

        if ($confirmation_code === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'Confirmation not found',
            ]);
        }

        if ($email === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'Email not found',
            ]);
        }

        // Rate limit per email on confirmation attempts
        $resetKey = 'reset_code:' . Str::lower($email);
        if (RateLimiter::tooManyAttempts($resetKey, 5)) {
            $seconds = RateLimiter::availableIn($resetKey);

            return response()->json([
                'response_code'    => 429,
                'response_message' => 'Too many reset attempts. Please try again in ' . $seconds . ' seconds.',
            ], 429);
        }
        RateLimiter::hit($resetKey, 300);

        if ($new_password === null || strlen($new_password) < 8) {
            return response()->json([
                'response_code'    => 399,
                'response_message' => 'The new password must be at least 8 characters long.',
            ]);
        }

        $resets = ResetPassword::where('email', $email)
            ->whereNotNull('confirmation_code')
            ->get();

        if ($resets->isEmpty()) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'The combination between email and confirmation was not found (1).',
            ]);
        }

        $passwordReset = null;

        foreach ($resets as $reset) {
            if (Hash::check($confirmation_code, $reset->confirmation_code)) {
                $passwordReset = $reset;
                break;
            }
        }

        if (! $passwordReset) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'The combination between email and confirmation code was not found. (2)',
            ]);
        }

        if ($passwordReset->expires_at < Carbon::now()) {
            $passwordReset->status = ResetPasswordStatus::EXPIRED->value;
            $passwordReset->save();

            return response()->json([
                'response_code'    => 401,
                'response_message' => 'The RESET is expired.',
            ]);
        }

        if ($passwordReset->status !== ResetPasswordStatus::ACTIVE->value) {
            return response()->json([
                'response_code'    => 402,
                'response_message' => 'Code does not exist, already used or not known ' . $passwordReset->status,
            ]);
        }

        $passwordReset->delete();

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'response_code'    => 404,
                'response_message' => 'User not found.',
            ], 404);
        }

        $user->password = Hash::make($new_password);
        $user->save();

        RateLimiter::clear($resetKey);

        return response()->json([
            'response_code'    => 200,
            'response_message' => 'Password reset successfully',
        ]);
    }

    public function sendresetpasswordlink(Request $request): JsonResponse
    {
        $email = $request->input('email');

        if ($email === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'Email is required.',
            ]);
        }

        // Basic rate limit on sending reset links per email
        $sendKey = 'reset_send:' . Str::lower($email);
        if (RateLimiter::tooManyAttempts($sendKey, 5)) {
            $seconds = RateLimiter::availableIn($sendKey);

            return response()->json([
                'response_code'    => 429,
                'response_message' => 'Too many reset requests. Please try again in ' . $seconds . ' seconds.',
            ], 429);
        }

        RateLimiter::hit($sendKey, 900); // 15 minutes

        $user = User::where('email', $email)->first();

        // Do NOT leak if user exists or not
        if ($user === null) {
            return response()->json([
                'response_code'    => 200,
                'response_message' => 'If your email is registered, a reset link has been sent.',
            ]);
        }

        $token       = Str::random(42);
        $hashedToken = Hash::make($token);
        $expires_at  = Carbon::now()->addHours(24);

        $confirmation_code        = $request->input('confirmation_code');
        $confirmation_code_hashed = null;

        if ($confirmation_code !== null) {
            $confirmation_code_hashed = Hash::make($confirmation_code);
        }

        // delete all previous records, so we always know only one is active.
        ResetPassword::where('email', $email)->delete();

        ResetPassword::create([
            'email'             => $email,
            'token'             => $hashedToken,
            'expires_at'        => $expires_at,
            'status'            => ResetPasswordStatus::ACTIVE->value,
            'confirmation_code' => $confirmation_code_hashed,
        ]);

        $mail_data = [
            'name'              => $email,
            'confirmation_code' => $confirmation_code,
            'from'              => 'info@stellarsecurity.com',
            'url'               => 'https://stellarsecurity.com/stellar-account/resetpasswordtoken?token=' . $token . '&email=' . $email,
        ];

        try {
            Mail::to($email)->send(new ResetPasswordLink($mail_data));
        } catch (\Exception $e) {
            // Still return generic success
            return response()->json([
                'response_code'    => 200,
                'response_message' => 'If your email is registered, a reset link has been sent.',
            ]);
        }

        return response()->json([
            'response_code'    => 200,
            'response_message' => 'If your email is registered, a reset link has been sent.',
        ]);
    }

    public function patch(Request $request): JsonResponse
    {
        $user = User::find($request->input('id'));

        if ($user === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'User not found.',
            ]);
        }

        // Only allow specific fields to be updated
        $data = $request->all();

        if ($request->has('eak')) {
            $e = base64_decode($request->string('eak'), true);
            if ($e === false) {
                return response()->json([
                    'response_code'    => 422,
                    'response_message' => 'Invalid eak',
                ], 422);
            }
            $data['eak'] = $e;
        }

        if ($request->has('kdf_salt')) {
            $s = base64_decode($request->string('kdf_salt'), true);
            if ($s === false) {
                return response()->json([
                    'response_code'    => 422,
                    'response_message' => 'Invalid kdf_salt',
                ], 422);
            }
            $data['kdf_salt'] = $s;
        }

        if ($request->filled('eak_recovery')) {
            $r = base64_decode($request->string('eak_recovery'), true);
            if ($r === false) {
                return response()->json([
                    'response_code'    => 422,
                    'response_message' => 'Invalid eak_recovery',
                ], 422);
            }
            $data['eak_recovery'] = $r;
        }

        $user->fill($data);
        $user->save();

        return response()->json([
            'response_code' => 200,
            'user'          => $user,
        ]);
    }

    public function create(Request $request) : JsonResponse
    {
        $username = $request->input('username');

        if ($username === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'No username provided',
            ]);
        }

        $user = $this->userService->findByUsername($username);

        if ($user !== null) {
            return response()->json([
                'response_code'    => 401,
                'response_message' => 'Username already exists',
            ]);
        }

        $role = $request->input('role');

        if ($role === null) {
            $role = UserRole::CUSTOMER->value;
        }

        $vpn_sdk = $request->input('vpn_sdk');
        if ($vpn_sdk === null) {
            $vpn_sdk = 0;
        }

        $password = $request->input('password');

        if ($password === null || strlen($password) < 8) {
            return response()->json([
                'response_code'    => 399,
                'response_message' => 'The password must be at least 8 characters long.',
            ]);
        }

        if ($request->string('eak') == null) {

            $user = User::create([
                'name'        => Str::random(16),
                'email'       => $username,
                'password'    => Hash::make($password),
                'encrypt_key' => '',
                'role'        => $role,
                'vpn_sdk'     => $vpn_sdk,
            ]);

        } else {

            $user = User::create([
                'name'        => Str::random(16),
                'email'       => $username,
                'password'    => Hash::make($password),
                'role'        => $role,
                'encrypt_key' => '',
                'vpn_sdk'     => $vpn_sdk,

                // E2EE blobs (client-provided)
                'eak'            => ($e = base64_decode($request->string('eak'), true)) !== false ? $e : abort(422,'Invalid eak'),
                'kdf_salt'       => ($s = base64_decode($request->string('kdf_salt'), true)) !== false ? $s : abort(422,'Invalid kdf_salt'),
                'kdf_params'     => $request->input('kdf_params'),
                'crypto_version' => $request->input('crypto_version', 'v1'),
                'eak_recovery'   => $request->filled('eak_recovery')
                    ? (($r = base64_decode($request->string('eak_recovery'), true)) !== false ? $r : abort(422,'Invalid eak_recovery'))
                    : null,
                'recovery_meta'  => $request->input('recovery_meta'),
            ]);

        }

        $tokenSource = $request->input('token');

        if (empty($tokenSource)) {
            $tokenSource = "Stellar.User.API";
        }

        $token = $user->createToken($tokenSource)->plainTextToken;

        return response()->json([
            'response_code'    => 200,
            'user'             => $user,
            'token'            => $token,
            'response_message' => 'OK',
        ]);
    }

    /**
     * Generate a unique throttle key per username.
     */
    protected function throttleKey(Request $request): string
    {
        // Only username-based throttling – IP is ignored on purpose
        return Str::lower($request->input('username', ''));
    }

}
