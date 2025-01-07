<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FlavorController;
use App\Http\Controllers\Api\SpicyLevelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['json','auth:api', 'role:admin' ])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware(['json','auth:api', 'role:admin' ])->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
});

Route::middleware(['json','auth:api', 'role:admin' ])->group(function () {
    Route::get('/flavors', [FlavorController::class, 'index']);
    Route::post('/flavors', [FlavorController::class, 'store']);
    Route::get('/flavors/{id}', [FlavorController::class, 'show']);
    Route::put('/flavors/{id}', [FlavorController::class, 'update']);
    Route::delete('/flavors/{id}', [FlavorController::class, 'destroy']);
});

Route::middleware(['json','auth:api', 'role:admin' ])->group(function () {
    Route::get('/spicy-levels', [SpicyLevelController::class, 'index']);
    Route::post('/spicy-levels', [SpicyLevelController::class, 'store']);
    Route::get('/spicy-levels/{id}', [SpicyLevelController::class, 'show']);
    Route::put('/spicy-levels/{id}', [SpicyLevelController::class, 'update']);
    Route::delete('/spicy-levels/{id}', [SpicyLevelController::class, 'destroy']);
});


