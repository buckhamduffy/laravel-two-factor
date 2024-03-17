<?php

namespace BuckhamDuffy\LaravelTwoFactor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use BuckhamDuffy\LaravelTwoFactor\Interfaces\HasTwoFactorInterface;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (session()->get('is_two_factor_confirmed')) {
            return $next($request);
        }

        /** @var HasTwoFactorInterface $user */
        $user = $request->user();

        if (!$user->canUseTwoFactor()) {
            return $next($request);
        }

        if ($user->enforceTwoFactor() || $user->two_factor_settings->isEnabled(true)) {
            session()->flash('two_factor_initial', true);

            if (class_exists(\Inertia\Inertia::class)) {
                return \Inertia\Inertia::location(route('two-factor-confirm'));
            }

            return redirect()->route('two-factor-confirm');
        }

        return $next($request);
    }
}
