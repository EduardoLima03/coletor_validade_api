<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [App\Http\Controllers\Web\AuthController::class, 'showLoginForm'])
    ->name('admin.login.form');
Route::post('/login', [App\Http\Controllers\Web\AuthController::class, 'login'])
    ->name('admin.login');

Route::middleware(['auth', 'role:GERENCIA,ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Web\AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/perfil', [App\Http\Controllers\Web\ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/perfil', [App\Http\Controllers\Web\ProfileController::class, 'update'])
        ->name('profile.update');

    Route::resource('produtos', App\Http\Controllers\Web\ProductController::class)
        ->names(['index' => 'products.index', 'create' => 'products.create', 'store' => 'products.store',
                 'show' => 'products.show', 'edit' => 'products.edit', 'update' => 'products.update',
                 'destroy' => 'products.destroy']);

    Route::resource('barcodes', App\Http\Controllers\Web\BarcodeController::class)
        ->names(['index' => 'barcodes.index', 'create' => 'barcodes.create', 'store' => 'barcodes.store',
                 'show' => 'barcodes.show', 'edit' => 'barcodes.edit', 'update' => 'barcodes.update',
                 'destroy' => 'barcodes.destroy']);

    Route::get('/importar', [App\Http\Controllers\Web\ImportController::class, 'showForm'])
        ->name('import.form');
    Route::post('/importar/processar', [App\Http\Controllers\Web\ImportController::class, 'processFile'])
        ->name('import.process');

    Route::middleware('role:ADMIN')->group(function () {
        Route::resource('users', App\Http\Controllers\Web\UserController::class)
            ->names(['index' => 'users.index', 'create' => 'users.create', 'store' => 'users.store',
                     'show' => 'users.show', 'edit' => 'users.edit', 'update' => 'users.update',
                     'destroy' => 'users.destroy']);
    });
});
