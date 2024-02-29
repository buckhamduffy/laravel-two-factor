<?php

namespace BuckhamDuffy\LaravelTwoFactor;

use Exception;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Color\Rgb;
use Illuminate\Support\Facades\Mail;
use BaconQrCode\Renderer\ImageRenderer;
use PragmaRX\Google2FAQRCode\Google2FA;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BuckhamDuffy\LaravelTwoFactor\Mail\TwoFactorCodeMail;
use BuckhamDuffy\LaravelTwoFactor\Events\SmsSendCodeEvent;
use BuckhamDuffy\LaravelTwoFactor\Events\EmailSendCodeEvent;
use BuckhamDuffy\LaravelTwoFactor\Interfaces\HasTwoFactorInterface;

class LaravelTwoFactor
{
    public function canShowType(string $type, HasTwoFactorInterface $user, ?string $currentType = null): bool
    {
        if ($type === $currentType) {
            return false;
        }

        if ($type === 'authenticator') {
            if (!config('two-factor.enable.authenticator')) {
                return false;
            }

            return $user->two_factor_settings->isAuthenticator();
        }

        if ($type === 'email') {
            if ($user->enforceTwoFactor()) {
                return true;
            }

            if (!config('two-factor.enable.email')) {
                return false;
            }

            return $user->two_factor_settings->isEmail();
        }

        if ($type === 'sms') {
            if (!config('two-factor.enable.sms')) {
                return false;
            }

            // SMS is not implemented yet
            return false;
        }

        return false;
    }

    public function verify(string $code, HasTwoFactorInterface $user, string $type): bool
    {
        $result = false;

        if ($type === 'authenticator') {
            $result = $this->verifyAuthenticator($code, $user);
        }

        if ($type === 'email') {
            $result = $this->verifyEmail($code, $user);
        }

        if ($type === 'sms') {
            $result = $this->verifySms($code, $user);
        }

        if ($result) {
            $user->two_factor_settings = $user->two_factor_settings
                ->setSmsCode(null)
                ->setEmailCode(null)
                ->setLastSmsSent(null)
                ->setLastEmailSent(null);
            $user->save();
        }

        return $result;
    }

    public function verifyAuthenticator(string $code, HasTwoFactorInterface $user): bool
    {
        if (!$user->two_factor_settings->getAuthenticatorSecret()) {
            return false;
        }

        $google2fa = new Google2FA();

        return $google2fa->verify($code, $user->two_factor_settings->getAuthenticatorSecret());
    }

    public function verifyEmail(string $code, HasTwoFactorInterface $user): bool
    {
        return (int) $code === $user->two_factor_settings->emailCode;
    }

    public function verifySms(string $code, HasTwoFactorInterface $user): bool
    {
        return (int) $code === $user->two_factor_settings->smsCode;
    }

    public function getType(?string $type, HasTwoFactorInterface $user): string
    {
        if ($type) {
            if ($this->canShowType($type, $user)) {
                return $type;
            }
        }

        foreach (['authenticator', 'email', 'sms'] as $type) {
            if ($this->canShowType($type, $user)) {
                return $type;
            }
        }

        throw new Exception('No two factor method enabled');
    }

    public function sendMessage(string $type, HasTwoFactorInterface $user): void
    {
        if ($type === 'email') {
            if (!$user->two_factor_settings->canResendEmail()) {
                session()->flash('error', 'Email requested too recently, please try again soon.');
                return;
            }

            $code = mt_rand(100000, 999999);
            $user->two_factor_settings = $user->two_factor_settings
                ->setSmsCode(null)
                ->setEmailCode($code)
                ->setLastEmailSent(now());

            $user->save();

            Mail::to($user)->send(new TwoFactorCodeMail($code));
            EmailSendCodeEvent::dispatch($code, $user);

            session()->flash('success', 'Code has been sent.');
        }

        if ($type === 'sms') {
            if (!$user->two_factor_settings->canResendSms()) {
                session()->flash('error', 'SMS requested too recently, please try again soon.');
                return;
            }

            $code = mt_rand(100000, 999999);
            $user->two_factor_settings = $user->two_factor_settings
                ->setSmsCode($code)
                ->setEmailCode(null)
                ->setLastSmsSent(now());

            $user->save();

            SmsSendCodeEvent::dispatch($code, $user);

            session()->flash('success', 'Code has been sent.');
        }
    }

    public function getQrCodeSvg(HasTwoFactorInterface $user): string
    {
        $google2fa = new Google2FA();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_settings->getAuthenticatorSecret()
        );

        $renderer = new ImageRenderer(
            new RendererStyle(192, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(45, 55, 72))),
            new SvgImageBackEnd()
        );

        $svg = (new Writer($renderer))->writeString($qrCodeUrl);

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }
}
