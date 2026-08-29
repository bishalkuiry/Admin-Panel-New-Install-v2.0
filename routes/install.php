<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallController;

/*
|--------------------------------------------------------------------------
| Installation Routes
|--------------------------------------------------------------------------
|
| Web-based installation wizard for Quixko.
| These routes are loaded when the application is in install mode.
|
*/

Route::get('/', [InstallController::class, 'step0'])->name('step0');
Route::get('/step1', [InstallController::class, 'step1'])->name('step1');
Route::get('/step3/{error?}', [InstallController::class, 'step3'])->name('step3');
Route::get('/step4', [InstallController::class, 'step4'])->name('step4');
Route::get('/step5', [InstallController::class, 'step5'])->name('step5');

Route::post('/database_installation', [InstallController::class, 'database_installation'])->name('install.db');
Route::get('/import_sql', [InstallController::class, 'import_sql'])->name('import_sql');
Route::get('/force-import-sql', [InstallController::class, 'force_import_sql'])->name('force-import-sql');
Route::post('/system_settings', [InstallController::class, 'system_settings'])->name('system_settings');

Route::fallback(function () {
    return redirect('/');
});
