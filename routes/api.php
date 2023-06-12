<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//funcionou assim, mas ta muito estranho, vou verificar com mais calma depois, pq uso o Laravel 8
/*Route::apiResource('product', 'ProductController');

//O laravel deve está identificando a paravra find-by-code como se fosse o id do produto
Route::get('product-by-code', [App\Http\Controllers\ProductController::class, 'findByCode']);
Route::get('product-by-code/{code}', [App\Http\Controllers\ProductController::class, 'findByCode2']);

Route::post('product-save-all', [App\Http\Controllers\ProductController::class, 'saveAll']);
Route::delete('product/{id}', [App\Http\Controllers\ProductController::class, 'destroy']);


//Só fazer igual de produto
Route::apiResource('ean', 'BarcodeController');

Route::post('ean-save-all', [App\Http\Controllers\BarcodeController::class, 'saveAll']);
Route::get('by-ean/{ean}', [App\Http\Controllers\BarcodeController::class, 'findByEan']);*/



//Autenticação
Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);
Route::post('/user', [App\Http\Controllers\UserController::class, 'store']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function(){
    Route::get('by-ean/{ean}', [App\Http\Controllers\BarcodeController::class, 'findByEan']);
    Route::apiResource('ean', 'BarcodeController');
    Route::post('ean-save-all', [App\Http\Controllers\BarcodeController::class, 'saveAll']);
    // product
    Route::apiResource('product', 'ProductController');
    Route::get('product-by-code', [App\Http\Controllers\ProductController::class, 'findByCode']);
    Route::get('product-by-code/{code}', [App\Http\Controllers\ProductController::class, 'findByCode2']);
    Route::post('product-save-all', [App\Http\Controllers\ProductController::class, 'saveAll']);
    Route::delete('product/{id}', [App\Http\Controllers\ProductController::class, 'destroy']);
    Route::post('user-update/{id}', [App\Http\Controllers\UserController::class, 'update']);
});
