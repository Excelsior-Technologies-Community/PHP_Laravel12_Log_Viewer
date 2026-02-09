<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\File;

class CustomLogController
{
    public function index()
    {
        $path = storage_path('logs/laravel.log');

        if (!File::exists($path)) {
            abort(404, 'Log file not found.');
        }

        $logs = collect(explode("\n", File::get($path)))
                    ->filter()
                    ->reverse()
                    ->take(300);

        return view('admin.logs.index', compact('logs'));
    }
}
