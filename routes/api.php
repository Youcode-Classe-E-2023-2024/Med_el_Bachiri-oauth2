<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
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
    Route::middleware('auth:api')->post('/auth/check', [AuthController::class, 'check']);

    Route::middleware('auth:api')->group( function () {
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('@me', [UserController::class, 'getUserDetails']);

        Route::get('users', [UserController::class, 'getAllUsers'])->middleware('permission:view-users');

        Route::middleware('permission:manage-users')->group(function () {
            Route::prefix('user')->group(function () {
                Route::post('create', [AuthController::class, 'register']);
                Route::delete('delete/{user_id}', [UserController::class, 'delete']);
                Route::put('update/{user_id}', [UserController::class, 'update']);
                Route::put('password/new/{user_id}',[UserController::class, 'updateUserPw']);
            });
        });

        Route::get('permissions', [PermissionController::class, 'showAll'])->middleware('permission:view-permissions');

        Route::put('/@me/password/new', [UserController::class, 'updateMyPw']);

        Route::get('roles', [RoleController::class, 'showAll'])->middleware('permission:view-roles');

        Route::middleware('permission:manage-roles')->group(function () {
            Route::prefix('role')->group(function () {
                Route::post('create', [RoleController::class, 'create']);
                Route::put('update/{role_id}', [RoleController::class, 'update']);
                Route::delete('delete/{role_id}', [RoleController::class, 'destroy']);
            });
            Route::get('/roles/{role_id}', [RoleController::class, 'show']);
        });

    });
});
