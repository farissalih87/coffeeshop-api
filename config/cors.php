<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

'allowed_origins' => [
    'https://coffeeshop-frontend.onrender.com',
    'http://localhost:5173',
    'http://192.168.1.100:5173',
],

    'allowed_origins_patterns' => ['http://localhost:*', 'http://127.0.0.1:*'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
