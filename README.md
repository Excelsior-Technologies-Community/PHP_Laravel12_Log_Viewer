# PHP_Laravel12_Log_Viewer

<p align="center">
<a href="#"><img src="https://img.shields.io/badge/Laravel-12-red" alt="Laravel 12"></a>
<a href="#"><img src="https://img.shields.io/badge/PHP-8.2+-blue" alt="PHP 8.2+"></a>
<a href="#"><img src="https://img.shields.io/badge/LogViewer-Installed-green" alt="Log Viewer"></a>
</p>

---

##  Overview

PHP_Laravel12_Log_Viewer is a Laravel 12 based project that demonstrates how to implement and manage application logging using Laravel’s built-in logging system. This project includes both the official Log Viewer package and a custom-built log viewer interface to help understand how logs are stored, accessed, and displayed within a Laravel application.

The project runs using Laravel’s built-in development server (`php artisan serve`) and does not require XAMPP or additional server configuration.

---

##  Features

* Laravel 12 fresh project setup
* Built-in logging configuration
* Integration of official Log Viewer package
* Log generation using Laravel Tinker
* Custom log viewer using Controller, Route, and Blade
* Color-coded log display (INFO, WARNING, ERROR)
* Organized project structure following MVC pattern

---

#  Project Folder Structure 

```
laravel-log-viewer
│
├── app/
│   └── Http/
│       └── Controllers/
│           └── Admin/
│               └── CustomLogController.php
│
├── routes/
│   └── web.php
│
├── resources/
│   └── views/
│       └── admin/
│           └── logs/
│               └── index.blade.php
│
├── storage/
│   └── logs/
│       └── laravel.log
│
└── vendor/
    └── opcodesio/
        └── log-viewer/
```


##  Introduction

This project demonstrates how to:

* Install Laravel 12
* Configure logging
* Install and use the official Log Viewer package
* Generate logs using Tinker
* Create a custom log viewer using Controller, Route, and Blade
* Understand the full Laravel project structure

The application runs using:

```bash
php artisan serve
```

No XAMPP or Apache configuration is required.

---

#  Step 1 – Create Laravel 12 Project

Open terminal and run:

```bash
composer create-project laravel/laravel laravel-log-viewer
```

Move into the project folder:

```bash
cd laravel-log-viewer
```

---

#  Step 2 – Configure Environment

Open the `.env` file and ensure the following configuration:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
LOG_VIEWER_ENABLED=true
```

Generate application key:

```bash
php artisan key:generate
```

---

#  Step 3 – Run Laravel Project

Start the development server:

```bash
php artisan serve
```

Open in browser:

```
http://127.0.0.1:8000
```

Laravel homepage should load successfully.

---

#  Step 4 – Install Official Log Viewer Package

Install the package:

```bash
composer require opcodesio/log-viewer
```

Publish assets:

```bash
php artisan vendor:publish --tag=log-viewer-assets --force
```

Clear cache:

```bash
php artisan optimize:clear
```

Restart server:

```bash
php artisan serve
```

---

#  Step 5 – Access Log Viewer

Open in browser:

```
http://127.0.0.1:8000/log-viewer
```

The Log Viewer dashboard will appear.

---

#  Step 6 – Generate Logs Using Tinker

Open another terminal and run:

```bash
php artisan tinker
```

Paste the following:

```php
Log::info('User registered', ['user_id' => 1]);
Log::info('Profile updated', ['user_id' => 1]);

Log::warning('Low stock warning', ['product_id' => 10]);
Log::warning('Multiple failed login attempts');

Log::error('Payment failed', ['order_id' => 101]);
Log::error('File upload failed');

Log::debug('Cart updated');
Log::debug('Coupon applied');

Log::critical('Database connection lost');
Log::alert('Admin password changed');

Log::notice('Email verified');
Log::emergency('System is down!');

Log::info('Background job processed');
Log::warning('API response delayed');
Log::error('Invalid API token');
```

Exit Tinker:

```php
exit
```

Refresh:

```
http://127.0.0.1:8000/log-viewer
```

All logs will be visible.

<img width="1917" height="897" alt="Screenshot 2026-02-09 134537" src="https://github.com/user-attachments/assets/ee73973a-a7ab-47c7-ac94-50ed163fa180" />


---

#  Log File Location

Logs are stored inside:

```
storage/logs/laravel.log
```

If daily logging is enabled:

```
storage/logs/laravel-YYYY-MM-DD.log
```

---

#  Custom Log Viewer Implementation

In addition to the official package, we can create a custom log viewer.

---

## Step 1 – Create Controller

Run:

```bash
php artisan make:controller Admin/CustomLogController
```

File location:

```
app/Http/Controllers/Admin/CustomLogController.php
```

Replace file content with:

```php
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
```

---

## Step 2 – Add Route

Open:

```
routes/web.php
```

Add:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CustomLogController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/logs', [CustomLogController::class, 'index']);
```

---

## Step 3 – Create Blade View

Create folder:

```
resources/views/admin/logs
```

Create file:

```
index.blade.php
```

Add the following code:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Custom Log Viewer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 min-h-screen p-10">

<div class="bg-white/10 backdrop-blur-xl shadow-2xl rounded-2xl p-6">

    <h1 class="text-3xl font-bold text-white mb-6">
        Log Viewer
    </h1>

    <div class="bg-black rounded-xl p-4 max-h-[600px] overflow-y-auto text-sm font-mono">

        @foreach($logs as $log)

            @php
                $color = 'text-gray-300';

                if(str_contains($log, 'ERROR')) {
                    $color = 'text-red-400';
                } elseif(str_contains($log, 'WARNING')) {
                    $color = 'text-yellow-400';
                } elseif(str_contains($log, 'INFO')) {
                    $color = 'text-blue-400';
                }
            @endphp

            <div class="mb-2 border-b border-gray-800 pb-1 {{ $color }}">
                {{ $log }}
            </div>

        @endforeach

    </div>

</div>

</body>
</html>
```

---

## Step 4 – Access Custom Viewer

Open in browser:

```
http://127.0.0.1:8000/admin/logs
```

Logs will be displayed with color highlighting.

<img width="1849" height="726" alt="Screenshot 2026-02-09 134553" src="https://github.com/user-attachments/assets/e6c9b48d-5577-4e27-a438-0b221e1ad952" />

---


