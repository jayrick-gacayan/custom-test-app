<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(
    function () {
    }
);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:api');


Route::resource('users', UserController::class)->only(['index', 'show']);
Route::resource('posts', PostController::class)->only(['index', 'show']);

Route::middleware('guest:api')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::post('/register', 'store');
        Route::put('/verify/{id}', 'verify');
        Route::post('/forgot_password', 'forgot_password');
        Route::post('/reset_password', 'reset_password');
        Route::post('/login', 'login');
    });
});
