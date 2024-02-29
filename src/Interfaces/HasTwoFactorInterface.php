<?php

namespace BuckhamDuffy\LaravelTwoFactor\Interfaces;

use Illuminate\Database\Eloquent\Model;
use BuckhamDuffy\LaravelTwoFactor\Support\UserTwoFactorSettings;

/**
 * @property UserTwoFactorSettings $two_factor_settings
 * @property string                $email
 * @method save()
 * @phpstan-require-extends Model
 */
interface HasTwoFactorInterface
{
    public function canUseTwoFactor(): bool;
    public function enforceTwoFactor(): bool;
}
