<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['/api/*'], // Aturan ini hanya berlaku untuk semua rute yang diawali 'api/'

    'allowed_methods' => ['*'], // Izinkan semua metode (GET, POST, PUT, DELETE, etc.)

    'allowed_origins' => ['*'], // Izinkan semua URL asal untuk development

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Izinkan semua header

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // INI SANGAT PENTING, UBAH MENJADI TRUE

];