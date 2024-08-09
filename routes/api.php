<?php

use App\Http\Controllers\Api\v1\Users\AuthController;
use App\Http\Controllers\Api\v1\Users\UserController;
use App\Http\Controllers\Api\v1\Users\UsersInvitationController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
    });

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('confirm-invitation', [UsersInvitationController::class, 'confirmInvitation'])->name('users.confirm.invitation');

    Route::middleware('auth:api')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::apiResource('users', UserController::class, ['except' => 'index']);

    });
});
