<?php

return [
    /*
     |--------------------------------------------------------------------------
     | CORS настроить параметры.
     |--------------------------------------------------------------------------
     |
     | здесь вы можете настроить параметры CORS.
     |
     | The allowed_origins, allowed_headers и allowed_methods can be set to array('*')
     | to accept any value.
     |
     */
    'paths' => ['api/*', 'register', 'login', 'logout', 'me', 'moods/*', '*'], // Sesuaikan path API Anda
    'allowed_methods' => ['*'], // Izinkan semua metode (GET, POST, PUT, DELETE, OPTIONS)
    'allowed_origins' => ['http://127.0.0.1:5501', 'http://localhost:5501'], // URL frontend Anda
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'], // Izinkan semua header, termasuk Authorization
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];