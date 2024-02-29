<?php

namespace BuckhamDuffy\LaravelTwoFactor\Support;

use Spatie\LaravelData\Data;
use Illuminate\Support\Carbon;
use PragmaRX\Recovery\Recovery;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FAQRCode\Google2FA;

class UserTwoFactorSettings extends Data
{
    public ?string $recoveryCodes = null;

    public ?Carbon $authenticator = null;
    public ?string $authenticatorSecret = null;
    public ?Carbon $authenticatorConfirmedAt = null;

    public ?int $emailCode = null;
    public ?Carbon $lastEmailSent = null;
    public ?Carbon $email = null;

    public ?int $smsCode = null;
    public ?Carbon $lastSmsSent = null;
    public ?Carbon $sms = null;

    public function isEnabled(bool $checkConfirmed = false): bool
    {
        if ($checkConfirmed) {
            if ($this->authenticator) {
                return $this->authenticatorConfirmedAt !== null;
            }
        }

        return $this->isAuthenticator() || $this->isSms() || $this->isEmail();
    }

    public function isConfirmed(): bool
    {
        return $this->authenticatorConfirmedAt !== null;
    }

    public function isAuthenticator(): bool
    {
        return $this->authenticator !== null;
    }

    public function isEmail(): bool
    {
        return $this->email !== null;
    }

    public function isSms(): bool
    {
        return $this->sms !== null;
    }

    public function wasRecentlyEnabled(): bool
    {
        return $this->authenticator?->addMinutes(5)->isFuture()
            || $this->sms?->addMinutes(5)->isFuture()
            || $this->email?->addMinutes(5)->isFuture();
    }

    public function setRecoveryCodes(array $codes): self
    {
        $this->recoveryCodes = Crypt::encrypt($codes);

        return $this;
    }

    public function getRecoveryCodes(): ?array
    {
        if ($this->recoveryCodes === null) {
            $this->generateRecoveryCodes();
        }

        return Crypt::decrypt($this->recoveryCodes);
    }

    public function generateRecoveryCodes(bool $force = false): self
    {
        if ($force || !$this->recoveryCodes) {
            $recovery = new Recovery();
            $this->setRecoveryCodes($recovery->toArray());

            return $this;
        }

        return $this;
    }

    public function getAuthenticatorSecret(): ?string
    {
        if ($this->authenticatorSecret === null) {
            return null;
        }

        return Crypt::decryptString($this->authenticatorSecret);
    }

    public function setAuthenticatorSecret(?string $value): self
    {
        if (!$value) {
            $this->authenticatorSecret = null;
            return $this;
        }

        $this->authenticatorSecret = Crypt::encryptString($value);
        return $this;
    }

    public function setSms(bool $sms): self
    {
        $this->sms = $sms ? now() : null;

        return $this->generateRecoveryCodes();
    }

    public function setEmail(bool $email): self
    {
        $this->email = $email ? now() : null;
        return $this->generateRecoveryCodes();
    }

    public function setAuthenticator(bool $value): self
    {
        $enabled = $this->authenticator !== null;
        if ($enabled === $value) {
            return $this;
        }

        if ($value === false) {
            $this->authenticator = null;
            $this->authenticatorConfirmedAt = null;
            $this->authenticatorSecret = null;
            $this->recoveryCodes = null;

            return $this;
        }

        $this->authenticatorConfirmedAt = null;
        $this->authenticator = now();

        $google2fa = new Google2FA();
        $this->setAuthenticatorSecret($google2fa->generateSecretKey());

        return $this->generateRecoveryCodes();
    }

    public function setAuthenticatorConfirmedAt(?Carbon $authenticatorConfirmedAt): UserTwoFactorSettings
    {
        $this->authenticatorConfirmedAt = $authenticatorConfirmedAt;
        return $this;
    }

    public function setEmailCode(?int $emailCode): UserTwoFactorSettings
    {
        $this->emailCode = $emailCode;
        return $this;
    }

    public function setLastEmailSent(?Carbon $lastEmailSent): UserTwoFactorSettings
    {
        $this->lastEmailSent = $lastEmailSent;
        return $this;
    }

    public function canResendEmail(): bool
    {
        if (!$this->lastEmailSent) {
            return true;
        }

        return $this->lastEmailSent->addMinutes(5)->isPast();
    }

    public function setSmsCode(?int $smsCode): self
    {
        $this->smsCode = $smsCode;
        return $this;
    }

    public function setLastSmsSent(?Carbon $lastSmsSent): self
    {
        $this->lastSmsSent = $lastSmsSent;
        return $this;
    }

    public function canResendSms(): bool
    {
        if (!$this->lastSmsSent) {
            return true;
        }

        return $this->lastSmsSent->addMinutes(5)->isPast();
    }
}
