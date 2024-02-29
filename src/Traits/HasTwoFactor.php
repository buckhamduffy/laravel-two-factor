<?php

namespace BuckhamDuffy\LaravelTwoFactor\Traits;

use BuckhamDuffy\LaravelTwoFactor\Support\UserTwoFactorSettings;
use Illuminate\Database\Eloquent\Model;

/**
 * @phpstan-require-extends Model
 */
trait HasTwoFactor
{
    public function canUseTwoFactor(): bool
    {
        return true;
    }

    public function enforceTwoFactor(): bool
    {
        return false;
    }

    protected function initializeHasTwoFactor(): void
    {
        $this->casts['two_factor_settings'] = UserTwoFactorSettings::class . ':default';
    }
}
