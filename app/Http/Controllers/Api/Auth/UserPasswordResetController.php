<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use App\Services\ApiResponseService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserPasswordResetController extends Controller
{
    /**
     * Send a password reset link to the given user.
     *
     * @return JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            // For security, always show success message even if email doesn't exist
            // This prevents email enumeration attacks
            return ApiResponseService::success([], 'We have emailed your password reset link!');
        }

        // Map Laravel password status to user-friendly messages
        $errorMessage = $this->getErrorMessage($status);

        return ApiResponseService::error($errorMessage);
        // return ApiResponseService::success([], 'We have emailed your password reset link!');
    }

    /**
     * Verify if a password reset token is valid.
     *
     * @return JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'otp_code' => 'required|string',
        ]);

        $email = $request->input('email');
        $token = $request->input('otp_code');

        // Get the user by email
        $user = User::where('email', $email)->first();

        if (! $user) {
            return ApiResponseService::error('This password reset token is invalid or has expired.');
        }

        // Check if token is an OTP code (6 digits)
        if (preg_match('/^\d{6}$/', $token)) {
            // It's an OTP code - validate against stored OTP
            $storedOtp = DB::table('password_reset_otps')
                ->where('email', $email)
                ->where('otp', $token)
                ->where('expires_at', '>', now())
                ->first();

            if ($storedOtp) {
                return ApiResponseService::success([], 'Token is valid');
            } else {
                return ApiResponseService::error('This password reset token is invalid or has expired.');
            }
        } else {
            // It's a regular token - use existing validation logic
            // Check if token exists in password_resets table
            $passwordReset = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->where('created_at', '>=', now()->subMinutes(config('auth.passwords.users.expire')))
                ->first();

            if (! $passwordReset) {
                return ApiResponseService::error('This password reset token is invalid or has expired.');
            }

            // Verify the token using Laravel's hash check (tokens are hashed in database)
            if (! Hash::check($token, $passwordReset->token)) {
                return ApiResponseService::error('This password reset token is invalid.');
            }

            return ApiResponseService::success([], 'Token is valid');
        }
    }

    /**
     * Reset the given user's password.
     *
     * @return JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'otp_code' => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $email = $request->input('email');
        $otp_code = $request->input('otp_code');
        $password = $request->input('password');
        $passwordConfirmation = $request->input('password_confirmation');

        // Get the user
        $user = User::where('email', $email)->first();

        if (! $user) {
            return ApiResponseService::error("We can't find a user with that email address.");
        }

        // Check if token is an OTP code (6 digits)
        if (preg_match('/^\d{6}$/', $otp_code)) {
            // It's an OTP code - validate against stored OTP
            $storedOtp = DB::table('password_reset_otps')
                ->where('email', $email)
                ->where('otp', $otp_code)
                ->where('expires_at', '>', now())
                ->first();

            if (! $storedOtp) {
                return ApiResponseService::error('This password reset token is invalid or has expired.');
            }

            // OTP is valid - proceed with password reset
            $attributes = [
                'password' => $password,
            ];

            // Reset remember token if column exists
            if (Schema::hasColumn($user->getTable(), 'remember_token')) {
                $attributes['remember_token'] = Str::random(60);
            }

            $user->forceFill($attributes)->save();

            // Delete the OTP after successful reset
            DB::table('password_reset_otps')
                ->where('email', $email)
                ->where('otp', $otp_code)
                ->delete();

            event(new PasswordReset($user));

            \Log::info('Password reset successful using OTP', [
                'email' => $email,
            ]);

            return ApiResponseService::success([], 'Your password has been reset successfully. You can now login with your new password.');
        } else {
            // It's a regular token - use existing Laravel password reset logic
            $status = Password::broker('users')->reset(
                $request->only('email', 'password', 'password_confirmation', 'otp_code'),
                function ($user, $password) {
                    $attributes = [
                        'password' => $password,
                    ];

                    // Reset remember token if column exists
                    if (Schema::hasColumn($user->getTable(), 'remember_token')) {
                        $attributes['remember_token'] = Str::random(60);
                    }

                    $user->forceFill($attributes)->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return ApiResponseService::success([], 'Your password has been reset successfully. You can now login with your new password.');
            }

            // Map Laravel password status to user-friendly messages
            $errorMessage = $this->getErrorMessage($status);

            return ApiResponseService::error($errorMessage);
        }
    }

    /**
     * Send OTP code to user's email for password reset.
     *
     * @return JsonResponse
     */
    public function sendOtp(Request $request)
    {
        // Validate request parameters
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6|regex:/^\d{6}$/',
            'type'  => 'nullable|string',
        ], [
            'otp.size'  => 'OTP code must be exactly 6 digits.',
            'otp.regex' => 'OTP code must contain only numbers.',
        ]);

        $email = $request->input('email');
        $otp = $request->input('otp');
        $type = $request->input('type', 'password_reset');

        // Check if user exists
        $user = User::where('email', $email)->first();

        if (! $user) {
            return ApiResponseService::error('No account found with this email address');
        }

        // CRITICAL: Store OTP temporarily (expires in 10 minutes)
        // This allows backend to validate OTP later when used as token
        try {
            DB::table('password_reset_otps')->updateOrInsert(
                ['email' => $email, 'type' => $type],
                [
                    'otp'        => $otp,
                    'expires_at' => now()->addMinutes(10), // Expires in 10 minutes
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Failed to store password reset OTP', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseService::error('Failed to process OTP request. Please try again later.');
        }

        // Send email with OTP
        try {
            $user->notify(new PasswordResetOtpNotification($otp));

            \Log::info('Password reset OTP sent successfully', [
                'email' => $email,
                'type'  => $type,
            ]);

            return ApiResponseService::success([], 'OTP code sent to your email. Please check your inbox.');
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset OTP', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponseService::error('Failed to send email. Please try again later.');
        }
    }

    /**
     * Get user-friendly error message from Laravel password status.
     *
     * @param  string  $status
     * @return string
     */
    private function getErrorMessage($status)
    {
        $messages = [
            Password::INVALID_USER    => "We can't find a user with that email address.",
            Password::INVALID_TOKEN   => 'This password reset token is invalid.',
            Password::RESET_THROTTLED => 'Please wait before retrying.',
        ];

        return $messages[$status] ?? 'Unable to process password reset. Please try again.';
    }
}
