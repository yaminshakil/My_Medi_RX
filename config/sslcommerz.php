<?php
return [
    'store_id' => env('SSLCOMMERZ_STORE_ID'),
    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
    'sandbox' => (bool) env('SSLCOMMERZ_SANDBOX', true),
    'currency' => env('SSLCOMMERZ_CURRENCY', 'BDT'),
    'timeout' => (int) env('SSLCOMMERZ_TIMEOUT', 30),
    'session_url' => env('SSLCOMMERZ_SANDBOX', true)
        ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
        : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php',
    'validation_url' => env('SSLCOMMERZ_SANDBOX', true)
        ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
        : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php',
];
