<?php

return [
    'terminal_id' => env('SBER_SBP_TERMINAL_ID'),
    'member_id' => env('SBER_SBP_MEMBER_ID'),
    'client_id' => env('SBER_SBP_CLIENT_ID'),
    'client_secret' => env('SBER_SBP_CLIENT_SECRET'),
    'cert_path' => env('SBER_SBP_CERT_PATH'),
    'cert_password' => env('SBER_SBP_CERT_PASSWORD'),
    'is_production' => env('SBER_SBP_IS_PRODUCTION', false),
];
