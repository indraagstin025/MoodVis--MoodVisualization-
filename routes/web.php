<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

// Public Routes (tidak memerlukan autentikasi)
$router->post('/register', 'AuthController@register');
$router->post('/login', 'AuthController@login');

// Protected Routes (memerlukan autentikasi JWT)
// Menggunakan 'jwt.auth' yang mengarah ke JwtMiddleware.php
$router->group(['middleware' => 'jwt.auth'], function () use ($router) {
    $router->get('/me', 'AuthController@me');
    $router->post('/logout', 'AuthController@logout');
});

$router->get('/', function () use ($router) {
    return response()->json([
        'status' => 'success',
        'message' => 'Welcome to your Lumen API!',
        'version' => $router->app->version(),
    ]);
});

// Route khusus untuk refresh token
// Menggunakan 'jwt.auth' untuk memastikan token awal valid
// dan 'jwt.refresh' untuk me-refresh dan menambahkan token baru di header
$router->group(['middleware' => ['jwt.auth', 'jwt.refresh']], function () use ($router) {
    $router->post('/refresh-token', 'AuthController@refresh');
});