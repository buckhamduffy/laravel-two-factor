<?php

// config for BuckhamDuffy/LaravelTwoFactor
return [
    'enable' => [
        'authenticator'  => true,
        'sms'            => false,
        'email'          => true,
        'recovery_codes' => true,
    ],

    'redirect_to' => '/'
];
