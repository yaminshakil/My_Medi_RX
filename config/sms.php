<?php

return [
    'default' => env('SMS_DRIVER', 'sandbox'),

    'drivers' => [
        'sandbox' => [
            'log_channel' => 'daily',
        ],
        'greenweb' => [
            'url' => env('GREENWEB_URL', 'https://api.greenweb.com.bd/api.php'),
            'token' => env('GREENWEB_TOKEN', ''),
        ],
        'bulksmsbd' => [
            'url' => env('BULKSMSBD_URL', 'https://bulksmsbd.net/api/smsapi'),
            'api_key' => env('BULKSMSBD_API_KEY', ''),
            'sender_id' => env('BULKSMSBD_SENDER_ID', ''),
        ],
    ],
];
