<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\UserBasicResource;
use App\Models\Doctor;
use App\Models\User;
use App\Notifications\UserRegisteredNotification;
use App\Services\ApiResponseService;
use App\Services\AuthService;
use App\Services\Sms\SmsInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        try {
            $user = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'first_name' => $validated['first_name'],
                    'last_name'  => $validated['last_name'],
                    'email'      => $validated['email'],
                    'mobile'     => $validated['mobile'],
                    'password'   => Hash::make($validated['password']),
                ]);

                // Assign role
                $user->syncRoles($validated['registration_type']);

                // Doctor registration
                if ($validated['registration_type'] === 'Doctor') {
                    $doctor = Doctor::create([
                        'user_id'         => $user->id,
                        'registration_no' => $validated['bmdc_number'],
                        'phone'           => $validated['mobile'],
                    ]);
                }

                return $user;
            });

            // Notify administrators
            $admins = User::role('Admin')->get();

            Notification::send(
                $admins,
                new UserRegisteredNotification($user)
            );

            event(new Registered($user));

            return $this->generateLoginResponse($user);
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'registration_type' => $validated['registration_type'] ?? null,
                'email'             => $validated['email'] ?? null,
                'error'             => $e->getMessage(),
                'file'              => $e->getFile(),
                'line'              => $e->getLine(),
            ]);

            return ApiResponseService::error(
                'Registration failed. Please try again.',
                [],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function login(Request $request)
    {
        // 1. Handle Social Logins
        if (in_array($request->login_type, ['google', 'facebook', 'apple'])) {
            return $this->handleSocialLogin($request);
        }

        $request->validate([
            'identifier' => ['required', 'string'],
            'password'   => ['required', 'string'],
        ]);

        $login = $request->identifier;

        // Find user by email or mobile
        $user = User::where('email', $login)
            ->orWhere('mobile', $login)
            ->first();

        // Find user by BMDC number
        if (! $user) {
            $user = User::whereHas('doctor', function ($query) use ($login) {
                $query->where('registration_no', $login);
            })->first();
        }

        if (empty($user)) {
            return ApiResponseService::error('Invalid Login Credentials', [], Response::HTTP_UNAUTHORIZED);
        }

        if (! Hash::check($request->password, $user->password)) {
            return ApiResponseService::error('Invalid Login Credentials', [], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->status === 'suspended') {
            return ApiResponseService::error('Your account has been suspended. Please contact support', [], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->status === 'inactive') {
            return ApiResponseService::error('This account is currently inactive.', [], Response::HTTP_UNAUTHORIZED);
        }

        // Authenticate using users table
        $credentials = [
            'id'       => $user->id,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $user = $request->user();

            return $this->generateLoginResponse($user);
        } else {
            return ApiResponseService::error('Invalid login credentials!', [], Response::HTTP_UNAUTHORIZED);
        }
    }

    protected function handleSocialLogin($request)
    {
        try {
            // Use stateless() for APIs.
            // $request->token is the token sent from your frontend (Vue/React/Mobile)
            $socialUser = Socialite::driver($request->login_type)->userFromToken($request->token);

            // Find or Create the user in your database
            $user = User::updateOrCreate([
                'email' => $socialUser->getEmail(),
            ], [
                'name'              => $socialUser->getName(),
                'google_id'         => $socialUser->getId(), // Ensure you have this column or a social_id column
                'email_verified_at' => now(),
            ]);

            if ($user->status === 'suspended') {
                return ApiResponseService::error('Your account has been suspended. Please contact support', [], Response::HTTP_UNAUTHORIZED);
            }

            if ($user->status === 'inactive') {
                return ApiResponseService::error('This account is currently inactive.', [], Response::HTTP_UNAUTHORIZED);
            }

            return $this->generateLoginResponse($user);
        } catch (\Exception $e) {
            return ApiResponseService::error('Social authentication failed.', [], 401);
        }
    }

    protected function generateLoginResponse($user)
    {
        // Generate Tokens using your AuthService
        // $tokens = AuthService::generateTokens($user);
        $token = auth('api')->login($user);

        $userResource = new UserBasicResource($user);

        $userData = [
            'user'       => $userResource->resolve(),
            'token_type' => 'Bearer',
            'tokens'     => [
                'accessToken' => $token,
                'tokenType'   => 'bearer',
                'expiresIn'   => auth('api')->factory()->getTTL() * 60,
            ],
        ];

        return ApiResponseService::success($userData, 'Login successful!');
    }

    public function logout(Request $request)
    {
        auth()->logout();

        return ApiResponseService::success([], 'Successfully logged out!');
    }

    public function refresh(Request $request)
    {
        $user = $request->user();

        // Generate new tokens
        // $tokens = AuthService::generateTokens($user);
        $token = auth('api')->refresh();

        $userResource = new UserBasicResource($user);

        $userData = [
            'user'   => $userResource->resolve(),
            'tokens' => [
                'accessToken' => $token,
                'tokenType'   => 'bearer',
                'expiresIn'   => auth('api')->factory()->getTTL() * 60,
            ],
        ];

        return ApiResponseService::success($userData, 'Token refreshed successfully!');
    }

    public function user(Request $request)
    {
        $userData = $request->user();

        return ApiResponseService::success($userData, 'Token refreshed successfully!');
    }

    protected function respondWithToken(string $token)
    {
        return ApiResponseService::success([
            'accessToken' => $token,
            'tokenType'   => 'Bearer',
            'expiresIn'   => auth('api')->factory()->getTTL() * 60,
        ], 'Token refreshed successfully!');
    }

    public function sendOtp(Request $request, SmsInterface $sms)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^(\+88)?01[3-9]\d{8}$/'],
        ]);

        $otp = rand(1000, 9999);

        $sms->send($request->phone, "Your OTP code is: {$otp}");

        return response()->json(['success' => true, 'message' => 'OTP sent successfully.']);
    }
}
