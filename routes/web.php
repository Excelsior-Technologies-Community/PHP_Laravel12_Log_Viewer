<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CustomLogController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Custom Log Viewer
|--------------------------------------------------------------------------
*/

Route::get(
    '/admin/logs',
    [CustomLogController::class, 'index']
)->name('admin.logs');

/*
|--------------------------------------------------------------------------
| Real-Time Logs
|--------------------------------------------------------------------------
*/

Route::get(
    '/admin/logs/live',
    [CustomLogController::class, 'live']
)->name('admin.logs.live');

/*
|--------------------------------------------------------------------------
| Export CSV
|--------------------------------------------------------------------------
*/

Route::get(
    '/admin/logs/export',
    [CustomLogController::class, 'export']
)->name('admin.logs.export');

/*
|--------------------------------------------------------------------------
| Download Raw Log
|--------------------------------------------------------------------------
*/

Route::get(
    '/admin/logs/download',
    [CustomLogController::class, 'download']
)->name('admin.logs.download');

/*
|--------------------------------------------------------------------------
| Clear Log
|--------------------------------------------------------------------------
*/

Route::post(
    '/admin/logs/clear',
    [CustomLogController::class, 'clear']
)->name('admin.logs.clear');