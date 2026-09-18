<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CustomLogController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/logs', [CustomLogController::class, 'index'])
    ->name('admin.logs');

Route::get('/admin/logs/live', [CustomLogController::class, 'live'])
    ->name('admin.logs.live');

Route::get('/admin/logs/export', [CustomLogController::class, 'export'])
    ->name('admin.logs.export');