<?php

use FontAwesome\Migrator\Http\Controllers\HomeController;
use FontAwesome\Migrator\Http\Controllers\ReportsController;
use FontAwesome\Migrator\Http\Controllers\SessionsController;
use FontAwesome\Migrator\Http\Controllers\TestController;
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

// Rapports de migration
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportsController::class, 'index'])->name('index');
    Route::get('/{filename}', [ReportsController::class, 'show'])->name('show');
    Route::delete('/{filename}', [ReportsController::class, 'destroy'])->name('destroy');
    Route::post('/cleanup', [ReportsController::class, 'cleanup'])->name('cleanup');
});

// Gestion des sessions de migration
Route::prefix('sessions')->name('sessions.')->group(function () {
    Route::get('/', [SessionsController::class, 'index'])->name('index');
    Route::get('/{sessionId}', [SessionsController::class, 'show'])->name('show');
    Route::delete('/{sessionId}', [SessionsController::class, 'destroy'])->name('destroy');
    Route::post('/cleanup', [SessionsController::class, 'cleanup'])->name('cleanup');
});

// Panneau de test et debug
Route::prefix('test')->name('test.')->group(function () {
    Route::get('/panel', [TestController::class, 'panel'])->name('panel');
    Route::post('/migration', [TestController::class, 'runMigration'])->name('migration');
    Route::get('/session/{sessionId}', [TestController::class, 'inspectSession'])->name('session');
    Route::post('/cleanup-sessions', [TestController::class, 'cleanupSessions'])->name('cleanup');
});
