<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\KeyValueVersionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/keys', [KeyValueVersionController::class, 'store']);
    Route::get('/keys', [KeyValueVersionController::class, 'index']);
    Route::get('/keys/{key}', [KeyValueVersionController::class, 'show']);
    Route::get('/keys/{key}/history', [KeyValueVersionController::class, 'history']);
});
