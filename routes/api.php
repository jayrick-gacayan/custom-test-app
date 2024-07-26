<?php

use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;

Route::controller(UserController::class)->group(function () {
  Route::put('/verify/{id}', 'verify');
  Route::post('/forgot_password', 'forgot_password');
  Route::post('/reset_password', 'reset_password');
  Route::post('/login', 'login');
});

Route::post('/logout', [UserController::class, 'logout']);
Route::apiResource('users', UserController::class);
Route::apiResource('posts', PostController::class);
Route::apiResource('posts.comments', PostCommentController::class);
