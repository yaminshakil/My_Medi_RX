<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\LoginRequest;
use App\Http\Resources\Auth\UserBasicResource;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\AuthService;
use App\Services\RewardSystemService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'email'             => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password'          => ['required', 'confirmed', Rules\Password::defaults()],
            'registration_type' => 'required|string|in:Doctor,Patient',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
        ]);

        $roles = $request->registration_type;
        $user->syncRoles($roles);

        event(new Registered($user));

        if ($user) {
            return $this->generateLoginResponse($user);
        } else {
            return ApiResponseService::error('Invalid user creation!', [], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function login(LoginRequest $request)
    {
        // 1. Handle Social Logins
        if (in_array($request->login_type, ['google', 'facebook', 'apple'])) {
            return $this->handleSocialLogin($request);
        }

        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();

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
        try {
            app(RewardSystemService::class)->awardDailyLoginPointsIfEligible($user);
        } catch (\Throwable $exception) {
            logger()->warning('Daily login reward could not be awarded: '.$exception->getMessage());
        }

        // Generate Tokens using your AuthService
        $tokens = AuthService::generateTokens($user);

        $userResource = new UserBasicResource($user);
        $roles = $user->getRoleNames();
        $permissions = $user->getPermissionsViaRoles()->pluck('name');

        $userData = [
            'user'       => $userResource->resolve(),
            'token_type' => 'Bearer',
            'tokens'     => [
                'accessToken'  => $tokens['accessToken'],
                'refreshToken' => $tokens['refreshToken'],
            ],
        ];

        return ApiResponseService::success($userData, 'Login successful!');
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $request->user()->currentAccessToken()->delete();
            $user->tokens()->delete();
        }

        return ApiResponseService::success([], 'Successfully logged out!');
    }

    public function refresh(Request $request)
    {
        $user = $request->user();

        // Delete the old tokens
        $user->tokens()->delete();

        // Generate new tokens
        $tokens = AuthService::generateTokens($user);

        $userResource = new UserBasicResource($user);
        $roles = $user->getRoleNames();
        $permissions = $user->getPermissionsViaRoles()->pluck('name');

        $userData = [
            'user'   => $userResource->resolve(),
            'tokens' => [
                'accessToken'  => $tokens['accessToken'],
                'refreshToken' => $tokens['refreshToken'],
            ],
            'authorization' => [
                'roles'       => $roles,
                'permissions' => $permissions,
            ],
        ];

        return ApiResponseService::success($userData, 'Token refreshed successfully!');
    }

    public function user(Request $request)
    {
        $userData = $request->user();

        return ApiResponseService::success($userData, 'Token refreshed successfully!');
    }
}
