<?php

namespace BuckhamDuffy\LaravelTwoFactor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use PragmaRX\Google2FAQRCode\Google2FA;
use BuckhamDuffy\LaravelTwoFactor\LaravelTwoFactor;
use BuckhamDuffy\LaravelTwoFactor\Interfaces\HasTwoFactorInterface;

class TwoFactorApiController extends Controller
{
    public function __construct(private LaravelTwoFactor $twoFactorService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        /** @var HasTwoFactorInterface $user */
        $user = $request->user('api');

        return response()->json([
            'can_enable'              => $user->canUseTwoFactor(),
            'enforce'                 => $user->enforceTwoFactor(),
            'enabled'                 => $user->two_factor_settings->isEnabled(),
            'sms'                     => $user->two_factor_settings->isSms(),
            'email'                   => $user->two_factor_settings->isEmail(),
            'authenticator'           => $user->two_factor_settings->isAuthenticator(),
            'authenticator_confirmed' => $user->two_factor_settings->isConfirmed(),
            'recently_enabled'        => $user->two_factor_settings->wasRecentlyEnabled(),
            'types'                   => [
                'authenticator' => config('two-factor.enable.authenticator'),
                'email'         => config('two-factor.enable.email'),
                'sms'           => config('two-factor.enable.sms'),
                'recovery_codes' => config('two-factor.enable.recovery_codes')
            ]
        ]);
    }

    public function update(Request $request): void
    {
        /** @var HasTwoFactorInterface $user */
        $user = $request->user('api');

        $request->validate([
            'sms'           => ['nullable', 'boolean'],
            'email'         => ['nullable', 'boolean'],
            'authenticator' => ['nullable', 'boolean'],
        ]);

        $user->two_factor_settings = $user->two_factor_settings
            ->setEmail($request->boolean('email'))
            ->setSms($request->boolean('sms'))
            ->setAuthenticator($request->boolean('authenticator'));

        $user->save();

        session()->put('is_two_factor_confirmed', true);
    }

    public function showSecret(Request $request): JsonResponse
    {
        /** @var HasTwoFactorInterface $user */
        $user = $request->user('api');

        if (!$user->two_factor_settings->isAuthenticator()) {
            return response()->json(['message' => 'Two factor authentication is not enabled'], 400);
        }

        return response()->json([
            'secret' => $user->two_factor_settings->getAuthenticatorSecret()
        ]);
    }

    public function showQrCode(Request $request): JsonResponse
    {
        /** @var HasTwoFactorInterface $user */
        $user = $request->user('api');

        if (!$user->two_factor_settings->isAuthenticator() || !$user->two_factor_settings->getAuthenticatorSecret()) {
            return response()->json(['message' => 'Two factor authentication is not enabled'], 400);
        }

        return response()->json([
            'svg' => $this->twoFactorService->getQrCodeSvg($user),
        ]);
    }

    public function showRecoveryCodes(Request $request): JsonResponse
    {
        /** @var HasTwoFactorInterface $user */
        $user = $request->user('api');

        if (!$user->two_factor_settings->isAuthenticator() || !$user->two_factor_settings->getRecoveryCodes()) {
            return response()->json(['message' => 'Two factor authentication is not enabled'], 400);
        }

        return response()->json([
            'codes' => $user->two_factor_settings->getRecoveryCodes()
        ]);
    }

    public function confirm(Request $request): JsonResponse
    {
        /** @var HasTwoFactorInterface $user */
        $user = $request->user('api');

        if (!$user->two_factor_settings->isAuthenticator() || !$user->two_factor_settings->getAuthenticatorSecret()) {
            return response()->json(['message' => 'Two factor authentication is not enabled'], 400);
        }

        $request->validate([
            'code' => ['required', 'string']
        ]);

        $google2fa = new Google2FA();

        if (!$google2fa->verify((string) $request->get('code'), $user->two_factor_settings->getAuthenticatorSecret())) {
            return response()->json(['message' => 'Invalid TOTP code'], 400);
        }

        $user->two_factor_settings = $user->two_factor_settings->setAuthenticatorConfirmedAt(now());
        $user->save();

        return response()->json(['message' => 'Two factor authentication has been confirmed']);
    }
}
