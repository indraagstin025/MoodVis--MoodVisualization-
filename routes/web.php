<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

// --- AUTH ROUTES ---
$router->post('/register', 'AuthController@register');
$router->post('/login', 'AuthController@login');
$router->post('/refresh-token', 'AuthController@refresh'); // Kalau pakai fitur refresh token

// Grup route yang butuh token JWT (user harus login)
$router->group(['middleware' => 'jwt.auth'], function () use ($router) {
    $router->get('/me', 'AuthController@me'); // Ambil data user yang sedang login
    $router->post('/logout', 'AuthController@logout'); // Logout dan blacklist token
    $router->put('/user/profile', 'ProfileController@update'); // Update profil user
    $router->post('/user/profile', 'ProfileController@update');

    $router->group(['middleware' => 'admin', 'prefix' => 'admin'], function () use ($router) {
        // Endpoint untuk membuat pengguna baru oleh admin
        $router->post('users', 'UserController@createUser');

        // ===============================================
        // == ROUTE BARU: Tambahkan baris di bawah ini ==
        // ===============================================
        // Endpoint untuk mengambil daftar semua pengguna
        $router->get('users', 'UserController@index');
    });
});





// Default root route
$router->get('/', function () use ($router) {
    return response()->json([
        'status' => 'success',
        'message' => 'Welcome to your Lumen API!',
        'version' => $router->app->version(),
    ]);
});


// === ROUTE BARU UNTUK EMOTION RECORDS ===
// Semua route di bawah ini akan memiliki prefix /api dan dilindungi oleh jwt.auth
$router->group(['prefix' => 'api', 'middleware' => 'jwt.auth'], function () use ($router) {
    $router->get('emotion-records', 'EmotionRecordController@index');
    $router->post('emotion-records', 'EmotionRecordController@store');
    $router->get('emotion-records/{id}', 'EmotionRecordController@show');
    $router->delete('emotion-records/{id}', 'EmotionRecordController@destroy');

    $router->get('emotion-history/summary', 'EmotionHistoryController@getEmotionSummary');
    $router->get('emotion-history/frequency-trend', 'EmotionHistoryController@getEmotionFrequencyTrend');
});