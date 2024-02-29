<?php

namespace BuckhamDuffy\LaravelTwoFactor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use BuckhamDuffy\LaravelTwoFactor\LaravelTwoFactor;
use BuckhamDuffy\LaravelTwoFactor\Interfaces\HasTwoFactorInterface;

class TwoFactorWebController extends Controller
{
    public function __construct(private LaravelTwoFactor $twoFactorService)
    {
    }

    public function show(Request $request, ?string $type = null): View|RedirectResponse
    {
        /** @var HasTwoFactorInterface $user */
        $user = $request->user();

        $type = $this->twoFactorService->getType($type, $user);

        if (session()->get('two_factor_initial')) {
            $this->twoFactorService->sendMessage($type, $user);
        }

        return view('two-factor::two-factor-confirmation')
            ->with('type', $type)
            ->with('other_types', [
                'authenticator' => $this->twoFactorService->canShowType('authenticator', $user, $type),
                'email'         => $this->twoFactorService->canShowType('email', $user, $type),
                'sms'           => $this->twoFactorService->canShowType('sms', $user, $type),
            ])
            ->with('can_resend_email', $user->two_factor_settings->canResendEmail())
            ->with('settings', $user->two_factor_settings);
    }

    public function showRecovery(): View
    {
        if (!config('two-factor.enable.recovery_codes')) {
            abort(404);
        }

        return view('two-factor::two-factor-recovery');
    }

    public function storeRecovery(Request $request): RedirectResponse
    {
        if (!config('two-factor.enable.recovery_codes')) {
            abort(404);
        }

        $request->validate([
            'code' => ['required', 'string'],
        ]);

        /** @var HasTwoFactorInterface $user */
        $user = $request->user();

        foreach ($user->two_factor_settings->getRecoveryCodes() as $code) {
            if (hash_equals($code, $request->get('code'))) {
                session()->put('is_two_factor_confirmed', true);

                return redirect()->to(config('two-factor.redirect_to'));
            }
        }

        throw ValidationException::withMessages([
            'code' => 'This code does not match.'
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'type' => ['required', 'in:sms,email,authenticator'],
        ]);

        $type = $request->get('type');
        $code = (string) $request->get('code');

        /** @var HasTwoFactorInterface $user */
        $user = $request->user();

        if (!$this->twoFactorService->verify($code, $user, $type)) {
            throw ValidationException::withMessages([
                'code' => 'This code does not match.'
            ]);
        }

        if ($type === 'email') {
            $user->two_factor_settings = $user->two_factor_settings
                ->setEmailCode(null)
                ->setLastEmailSent(null);
            $user->save();
        }

        session()->put('is_two_factor_confirmed', true);

        return redirect()->to(config('two-factor.redirect_to'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => ['required', 'in:sms,email,authenticator'],
        ]);

        $this->twoFactorService->sendMessage($request->get('type'), $request->user());

        session()->flash('success', 'Code has been sent.');

        return redirect()
            ->to(route('two-factor-confirm', ['type' => $request->get('type')]));
    }
}
