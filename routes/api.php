<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FlavorController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SpicyLevelController;
use App\Http\Controllers\Api\TransactionController;
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
    //Route::apiResource('categories', CategoryController::class);

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

Route::middleware(['json','auth:api', 'role:admin' ])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

});

Route::middleware(['json','auth:api', 'role:admin' ])->group(function () {
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
    Route::get('/payment-methods/{id}', [PaymentMethodController::class, 'show']);
    Route::put('/payment-methods/{id}', [PaymentMethodController::class, 'update']);
    Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy']);
});

Route::middleware(['json','auth:api', 'role:admin' ])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
});

Route::middleware(['json','auth:api', 'role:admin' ])->group(function () {
    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/reports/monthly', [ReportController::class, 'monthly']);
    Route::get('/reports/top-products', [ReportController::class, 'topProducts']);
    Route::get('/reports/by-payment', [ReportController::class, 'byPayment']);
});


