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

    Route::post('coleta', [App\Http\Controllers\Api\ColetaController::class, 'store']);
    Route::put('coleta/{id}', [App\Http\Controllers\Api\ColetaController::class, 'update']);
    Route::get('coleta/check', [App\Http\Controllers\Api\ColetaController::class, 'check']);

    Route::get('lojas', [App\Http\Controllers\Api\LojaController::class, 'index']);
    Route::get('areas-auditoria', [App\Http\Controllers\Api\AreaAuditoriaController::class, 'index']);

    // Route::prefix('notifications')->group(function () {
    //     Route::get('/', [App\Http\Controllers\Api\NotificationController::class, 'index']);
    //     Route::post('/{id}/read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    //     Route::post('/read-all', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    // });

    Route::get('recolhimento/produtos/{lojaId}', [App\Http\Controllers\Api\RecolhimentoController::class, 'produtos']);
    Route::post('recolhimento/registrar', [App\Http\Controllers\Api\RecolhimentoController::class, 'registrar']);
});

Route::middleware(['auth:sanctum', 'role:GERENCIA,ADMIN'])->group(function () {
    Route::get('coleta/trashed', [App\Http\Controllers\Api\ColetaController::class, 'trashed']);
    Route::put('coleta/{id}/restore', [App\Http\Controllers\Api\ColetaController::class, 'restore']);

    Route::apiResource('product', 'ProductController');
    Route::post('product-save-all', [App\Http\Controllers\ProductController::class, 'saveAll']);
    Route::apiResource('ean', 'BarcodeController');
    Route::post('ean-save-all', [App\Http\Controllers\BarcodeController::class, 'saveAll']);
});

Route::middleware(['auth:sanctum', 'role:ADMIN'])->group(function () {
    Route::post('user-update/{id}', [App\Http\Controllers\UserController::class, 'update']);
});
