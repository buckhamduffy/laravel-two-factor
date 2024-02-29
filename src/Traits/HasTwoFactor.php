<?php

namespace BuckhamDuffy\LaravelTwoFactor\Traits;

use Illuminate\Database\Eloquent\Model;
use BuckhamDuffy\LaravelTwoFactor\Support\UserTwoFactorSettings;

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
