<?php

use FontAwesome\Migrator\Http\Controllers\Cleanup\ExecuteController as CleanupExecuteController;
use FontAwesome\Migrator\Http\Controllers\Cleanup\IndexController as CleanupIndexController;
use FontAwesome\Migrator\Http\Controllers\HomeController;
use FontAwesome\Migrator\Http\Controllers\Migrations\DestroyController;
use FontAwesome\Migrator\Http\Controllers\Migrations\IndexController;
use FontAwesome\Migrator\Http\Controllers\Migrations\InspectController;
use FontAwesome\Migrator\Http\Controllers\Migrations\ShowController;
use FontAwesome\Migrator\Http\Controllers\Tests\IndexController as TestsIndexController;
use FontAwesome\Migrator\Http\Controllers\Tests\RunMultiVersionMigrationController;
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
Route::prefix('migrations')->name('migrations.')->group(function () {
    Route::get('/', IndexController::class)->name('index');
    Route::get('/{migrationId}', ShowController::class)->name('show');
    Route::get('/{migrationId}/inspect', InspectController::class)->name('inspect');
    Route::delete('/{migrationId}', DestroyController::class)->name('destroy');
});

// Tests et debug
Route::prefix('tests')->name('tests.')->group(function () {
    Route::get('/', TestsIndexController::class)->name('index');
    Route::post('/migration-multi-version', RunMultiVersionMigrationController::class)->name('migration-multi-version');
});

// Interface de nettoyage centralisée
Route::prefix('cleanup')->name('cleanup.')->group(function () {
    Route::get('/', CleanupIndexController::class)->name('index');
    Route::post('/execute', CleanupExecuteController::class)->name('execute');
    Route::get('/count-migrations/{days}', [CleanupIndexController::class, 'countMigrations'])->name('count-migrations');
});
