<?php

use App\Http\Controllers\Auth\YandexOAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'Family Finance API',
        'status' => 'running',
    ]);
});

Route::get('/auth/yandex/redirect', [YandexOAuthController::class, 'redirect']);
Route::get('/auth/yandex/callback', [YandexOAuthController::class, 'callback']);

