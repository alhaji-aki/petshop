<?php

namespace App\Actions\Authentication;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Actions\JwtToken\CreateJwtTokenAction;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function __construct(
        private readonly CreateJwtTokenAction $createJwtTokenAction
    ) {
    }
    public function execute(Request $request, bool $allowAdmins): string
    {
        $this->ensureIsNotRateLimited($request);

        $user = User::query()
            ->firstOrNew(array_merge($request->only('email'), [
                'is_admin' => $allowAdmins,
            ]));

        $this->validatePassword($request, $user);

        return DB::transaction(function () use ($user) {
            $token = $this->createJwtTokenAction->execute($user, $user->uuid);

            // update the last login at for user
            $user->forceFill(['last_login_at' => now()])->save();

            return $token;
        });
    }

    /**
     * Ensure the login request is not rate limited.
     */
    private function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        $this->throwValidationError(trans('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]), 429);
    }

    private function validatePassword(Request $request, User $user): void
    {
        if (!$user->exists || !Hash::check($request->string('password'), $user->getAuthPassword())) {
            $this->increaseLoginAttempt($request);

            $this->throwValidationError(trans('auth.failed'));
        }

        $this->clearLoginAttempt($request);
    }

    /**
     * Increase login attempt.
     */
    private function increaseLoginAttempt(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request));
    }

    private function throwValidationError(mixed $message, int $status = 422): void
    {
        throw ValidationException::withMessages([
            'email' => $message,
        ])->status($status);
    }

    /**
     * Clear login attempt.
     */
    private function clearLoginAttempt(Request $request): void
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    private function throttleKey(Request $request): string
    {
        return $request->string('email')
            ->lower()
            ->append('|', $request->ip() ?? '');
    }
}
