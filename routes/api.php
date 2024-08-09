<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Support\Facades\Route;



Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
    });
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::middleware('auth:api')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('confirm-invitation', [UserController::class, 'confirmInvitation'])->name('users.confirm.invitation');
        Route::apiResource('users', UserController::class, ['except' => 'index']);

    });
});
