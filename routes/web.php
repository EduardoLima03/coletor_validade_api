<?php

use Illuminate\Support\Facades\Route;

Route::get('/migrate', function () {
    Artisan::call('migrate', ['--force' => true]);
    return 'ok';
});

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [App\Http\Controllers\Web\AuthController::class, 'showLoginForm'])
    ->name('admin.login.form');
Route::post('/login', [App\Http\Controllers\Web\AuthController::class, 'login'])
    ->name('admin.login');

Route::middleware(['auth', 'role:GERENCIA,ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Web\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/logout', [App\Http\Controllers\Web\AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/perfil', [App\Http\Controllers\Web\ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/perfil', [App\Http\Controllers\Web\ProfileController::class, 'update'])
        ->name('profile.update');

    Route::get('/coletas', [App\Http\Controllers\Web\ColetaController::class, 'index'])
        ->name('coletas.index');
    Route::get('/coletas/trashed', [App\Http\Controllers\Web\ColetaController::class, 'trashed'])
        ->name('coletas.trashed');
    Route::put('/coletas/{coleta}/restore', [App\Http\Controllers\Web\ColetaController::class, 'restore'])
        ->name('coletas.restore');
    Route::get('/coletas/exportar/xlsx', [App\Http\Controllers\Web\ColetaController::class, 'exportXlsx'])
        ->name('coletas.export.xlsx');
    Route::get('/coletas/exportar/csv', [App\Http\Controllers\Web\ColetaController::class, 'exportCsv'])
        ->name('coletas.export.csv');
    Route::get('/coletas/{coleta}/edit', [App\Http\Controllers\Web\ColetaController::class, 'edit'])
        ->name('coletas.edit');
    Route::put('/coletas/{coleta}', [App\Http\Controllers\Web\ColetaController::class, 'update'])
        ->name('coletas.update');
    Route::delete('/coletas/{coleta}', [App\Http\Controllers\Web\ColetaController::class, 'destroy'])
        ->name('coletas.destroy');

    Route::middleware('role:ADMIN')->group(function () {
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
        Route::post('/importar/iniciar', [App\Http\Controllers\Web\ImportController::class, 'start'])
            ->name('import.start');
        Route::post('/importar/processar-lote', [App\Http\Controllers\Web\ImportController::class, 'chunk'])
            ->name('import.chunk');
        Route::get('/importar/progresso', [App\Http\Controllers\Web\ImportController::class, 'progress'])
            ->name('import.progress');

        Route::resource('lojas', App\Http\Controllers\Web\LojaController::class)
            ->names(['index' => 'lojas.index', 'create' => 'lojas.create', 'store' => 'lojas.store',
                     'edit' => 'lojas.edit', 'update' => 'lojas.update',
                     'destroy' => 'lojas.destroy']);

        Route::resource('users', App\Http\Controllers\Web\UserController::class)
            ->names(['index' => 'users.index', 'create' => 'users.create', 'store' => 'users.store',
                     'show' => 'users.show', 'edit' => 'users.edit', 'update' => 'users.update',
                     'destroy' => 'users.destroy']);

        Route::resource('areas-auditoria', App\Http\Controllers\Web\AreaAuditoriaController::class)
            ->parameters(['areas-auditoria' => 'area_auditorium'])
            ->names(['index' => 'areas-auditoria.index', 'create' => 'areas-auditoria.create',
                     'store' => 'areas-auditoria.store', 'edit' => 'areas-auditoria.edit',
                     'update' => 'areas-auditoria.update', 'destroy' => 'areas-auditoria.destroy']);
        Route::post('areas-auditoria/{area_auditorium}/excluir', [App\Http\Controllers\Web\AreaAuditoriaController::class, 'destroy'])
            ->name('areas-auditoria.excluir');
        Route::post('areas-auditoria/merge-duplicadas', [App\Http\Controllers\Web\AreaAuditoriaController::class, 'mergeDuplicates'])
            ->name('areas-auditoria.merge');

        Route::get('/importar/coletas', [App\Http\Controllers\Web\ColetaImportController::class, 'showForm'])
            ->name('importar.coletas.form');
        Route::post('/importar/coletas/processar', [App\Http\Controllers\Web\ColetaImportController::class, 'import'])
            ->name('importar.coletas.processar');
        Route::post('/importar/coletas/iniciar', [App\Http\Controllers\Web\ColetaImportController::class, 'start'])
            ->name('importar.coletas.start');
        Route::post('/importar/coletas/processar-lote', [App\Http\Controllers\Web\ColetaImportController::class, 'chunk'])
            ->name('importar.coletas.chunk');
        Route::get('/importar/coletas/progresso', [App\Http\Controllers\Web\ColetaImportController::class, 'progress'])
            ->name('importar.coletas.progress');

        Route::get('/auditoria', [App\Http\Controllers\Web\AuditController::class, 'index'])
            ->name('audit.index');
        Route::get('/auditoria/{id}', [App\Http\Controllers\Web\AuditController::class, 'show'])
            ->name('audit.show');

        // Route::prefix('notificacoes')->name('notificacoes.')->group(function () {
        //     Route::get('/', [App\Http\Controllers\Web\NotificationController::class, 'index'])
        //         ->name('index');
        //     Route::get('/nao-lidas', [App\Http\Controllers\Web\NotificationController::class, 'unreadCount'])
        //         ->name('unread-count');
        //     Route::post('/{id}/marcar-lida', [App\Http\Controllers\Web\NotificationController::class, 'markAsRead'])
        //         ->name('mark-read');
        //     Route::post('/marcar-todas-lidas', [App\Http\Controllers\Web\NotificationController::class, 'markAllAsRead'])
        //         ->name('mark-all-read');
        // });

        Route::resource('recolhimento-regras', App\Http\Controllers\Web\RecolhimentoRegraController::class)
            ->names(['index' => 'recolhimento-regras.index', 'create' => 'recolhimento-regras.create',
                     'store' => 'recolhimento-regras.store', 'edit' => 'recolhimento-regras.edit',
                     'update' => 'recolhimento-regras.update', 'destroy' => 'recolhimento-regras.destroy']);
    });

    Route::get('/recolhimento', [App\Http\Controllers\Web\RecolhimentoDashboardController::class, 'index'])
        ->name('recolhimento.dashboard');
});
