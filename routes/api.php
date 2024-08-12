<?php

use App\Http\Controllers\Api\v1\Users\AuthController;
use App\Http\Controllers\Api\v1\Users\UserController;
use App\Http\Controllers\Api\v1\Users\UsersInvitationController;
use App\Http\Middleware\ReformatQueryParameters;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
    });

    Route::middleware(ReformatQueryParameters::class)->group(function () {
        Route::get('users-guest', [UserController::class, 'indexForGuest'])->name('users.index.guest');
    });
    Route::post('confirm-invitation', [UsersInvitationController::class, 'confirmInvitation'])->name('users.confirm.invitation');

    Route::apiResource('users', UserController::class)->only(['index']);
    Route::middleware('auth:api')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::apiResource('users', UserController::class);
        Route::delete('users/{user}/force', [UserController::class, 'forceDelete'])->name('users.forceDelete');

    });
});
