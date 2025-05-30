<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

// Route bagian AuthController
$router->post('/register', 'AuthController@register');
$router->post('/login', 'AuthController@login');


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


$router->group(['middleware' => ['jwt.auth', 'jwt.refresh']], function () use ($router) {
    $router->post('/refresh-token', 'AuthController@refresh');
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