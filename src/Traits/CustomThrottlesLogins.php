<?php

namespace BuckhamDuffy\LaravelTwoFactor\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

trait CustomThrottlesLogins
{
    public const LOGIN_ATTEMPT_KEY = 'login-attempt-count:';
    public const LOGIN_ATTEMPT_TIME_KEY = 'login-attempt-time:';

    protected function hasTooManyLoginAttempts(Request $request): bool
    {
        $attempts = $this->getLoginAttemptCount($request);
        $lastAttempt = $this->getLoginAttemptedAt($request);

        // First 5 attempts are allowed
        if (5 >= $attempts) {
            return false;
        }

        // If more than 8, we lock them out for an hour
        if ($attempts > 8) {
            return now()->subHour()->isBefore($lastAttempt);
        }

        // On the 8th try we lock for 15 minutes
        if ($attempts === 8) {
            return now()->subMinutes(15)->isBefore($lastAttempt);
        }

        // Between 5 and 8 attempts, we lock for 5 minutes
        return now()->subMinutes(5)->isBefore($lastAttempt);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendLockoutResponse(Request $request): void
    {
        throw ValidationException::withMessages([
            $this->username() => ['Too many login attempts. Please try again soon.'],
        ])->status(Response::HTTP_TOO_MANY_REQUESTS);
    }

    protected function clearLoginAttempts(Request $request): void
    {
        cache()->forget(self::LOGIN_ATTEMPT_KEY . $this->getLoginAttemptKey($request));
        cache()->forget(self::LOGIN_ATTEMPT_TIME_KEY . $this->getLoginAttemptKey($request));
    }

    protected function incrementLoginAttempts(Request $request): void
    {
        $attempts = $this->getLoginAttemptCount($request);
        cache()->put(self::LOGIN_ATTEMPT_KEY . $this->getLoginAttemptKey($request), $attempts + 1, now()->addDay());
        cache()->put(self::LOGIN_ATTEMPT_TIME_KEY . $this->getLoginAttemptKey($request), now(), now()->addDay());
    }

    private function getLoginAttemptCount(Request $request): int
    {
        return (int) cache()->get(self::LOGIN_ATTEMPT_KEY . $this->getLoginAttemptKey($request), 0);
    }

    private function getLoginAttemptedAt(Request $request): Carbon
    {
        return cache()->get(self::LOGIN_ATTEMPT_TIME_KEY . $this->getLoginAttemptKey($request)) ?: now()->subYear();
    }

    private function getLoginAttemptKey(Request $request): string
    {
        return Str::of($request->input($this->username()))->trim()->slug()->toString();
    }
}
