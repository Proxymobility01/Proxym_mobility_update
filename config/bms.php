<?php

return [
    'endpoints' => [
        'huiming' => env('BMS_HUIMING_ENDPOINT'), // OK if includes loginSystem — we trim it
        'vercom'  => env('BMS_VERCOM_ENDPOINT'),
    ],
    'auth' => [
        'huiming' => ['LoginName' => env('BMS_HUIMING_LOGIN'), 'Password' => env('BMS_HUIMING_PASSWORD')],
        'vercom'  => ['LoginName' => env('BMS_VERCOM_LOGIN'),  'Password' => env('BMS_VERCOM_PASSWORD')],
    ],
    'token_ttl'          => 18,
    'login_type'         => 'ENTERPRISE',
    'language'           => 'en',
    'timezone'           => '+01',
    'apply'              => 'APP',
    'is_md5'             => 0,
    'numbers_chunk'      => 100,
    'auto_default_param' => true, // try baseline-from-device if param is missing for required cmds
    'encoder'            => \App\Services\BmsApi::class, // optional: implement encodeSettings()
];

