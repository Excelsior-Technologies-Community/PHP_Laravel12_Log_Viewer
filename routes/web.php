<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CustomLogController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/logs', [CustomLogController::class, 'index']);
