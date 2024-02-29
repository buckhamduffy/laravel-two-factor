<?php

namespace BuckhamDuffy\LaravelTwoFactor\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use BuckhamDuffy\LaravelTwoFactor\Interfaces\HasTwoFactorInterface;

class EmailSendCodeEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(private int $code, private HasTwoFactorInterface $user)
    {
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getUser(): HasTwoFactorInterface
    {
        return $this->user;
    }
}
