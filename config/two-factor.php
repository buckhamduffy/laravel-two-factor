<?php

// config for BuckhamDuffy/LaravelTwoFactor
return [
    'user_model' => 'App\\Models\\User',

    'enable' => [
        'authenticator'  => true,
        'sms'            => true,
        'email'          => true,
        'recovery_codes' => true,
    ],

    'redirect_to' => '/'
];
