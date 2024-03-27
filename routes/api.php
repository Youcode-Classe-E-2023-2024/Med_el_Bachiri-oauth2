<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\isAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::namespace('Api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
    });

    Route::middleware('auth:api')->group( function () {
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('me', [UserController::class, 'getUserDetails']);

        Route::middleware('permission:manage-users')->group(function () {
            Route::get('users', [UserController::class, 'getAllUsers']);
            Route::prefix('user')->group(function () {
                Route::post('create', [AuthController::class, 'register']);
                Route::delete('delete/{user_id}', [UserController::class, 'delete']);
                Route::put('update/{user_id}', [UserController::class, 'update']);
                Route::put('password/new/{user_id}',[UserController::class, 'updateUserPw']);
            });
        });
        Route::put('/me/password/new', [UserController::class, 'updateMyPw']);
        Route::middleware('permission:manage-roles')->group(function () {

        });

    });
});
