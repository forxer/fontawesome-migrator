<?php

use FontAwesome\Migrator\Http\Controllers\HomeController;
use FontAwesome\Migrator\Http\Controllers\ReportsController;
use FontAwesome\Migrator\Http\Controllers\TestsController;
use Illuminate\Support\Facades\Route;

/*
|---------------------------------------------------------------------------
| FontAwesome Migrator Web Routes
|---------------------------------------------------------------------------
|
| Routes pour l'interface web du package FontAwesome Migrator
|
*/

// Page d'accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// Migrations
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportsController::class, 'index'])->name('index');
    Route::get('/{sessionId}', [ReportsController::class, 'show'])->name('show');
    Route::delete('/{sessionId}', [ReportsController::class, 'destroy'])->name('destroy');
    Route::post('/cleanup', [ReportsController::class, 'cleanup'])->name('cleanup');
});

// Tests et debug
Route::prefix('tests')->name('tests.')->group(function () {
    Route::get('/', [TestsController::class, 'index'])->name('index');
    Route::post('/migration-multi-version', [TestsController::class, 'runMultiVersionMigration'])->name('migration-multi-version');
    Route::get('/session/{sessionId}', [TestsController::class, 'inspectSession'])->name('session');
    Route::post('/cleanup-sessions', [TestsController::class, 'cleanupSessions'])->name('cleanup');
});
