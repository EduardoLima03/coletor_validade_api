<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);
Route::post('/user', [App\Http\Controllers\UserController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/profile/update', [App\Http\Controllers\UserController::class, 'updateProfile']);

    Route::get('product-by-code', [App\Http\Controllers\ProductController::class, 'findByCode']);
    Route::get('product-by-code/{code}', [App\Http\Controllers\ProductController::class, 'findByCode2']);
    Route::get('by-ean/{ean}', [App\Http\Controllers\BarcodeController::class, 'findByEan']);
});

Route::middleware(['auth:sanctum', 'role:GERENCIA,ADMIN'])->group(function () {
    Route::apiResource('product', 'ProductController');
    Route::post('product-save-all', [App\Http\Controllers\ProductController::class, 'saveAll']);
    Route::apiResource('ean', 'BarcodeController');
    Route::post('ean-save-all', [App\Http\Controllers\BarcodeController::class, 'saveAll']);
});

Route::middleware(['auth:sanctum', 'role:ADMIN'])->group(function () {
    Route::post('user-update/{id}', [App\Http\Controllers\UserController::class, 'update']);
});
