<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomLogController
{
    /**
     * Display the custom log viewer.
     */
    public function index(Request $request)
    {
        $path = storage_path('logs/laravel.log');

        if (!File::exists($path)) {
            abort(404, 'Log file not found.');
        }

        // Read the log file
        $logs = collect(explode("\n", File::get($path)))
            ->map(fn ($log) => trim($log))
            ->filter()
            ->reverse()
            ->take(300)
            ->values();

        // Search keyword
        $search = trim($request->input('search', ''));

        // Selected log level
        $level = strtoupper(trim($request->input('level', 'ALL')));

        // Filter by search text
        if ($search !== '') {
            $logs = $logs->filter(function ($log) use ($search) {
                return str_contains(
                    strtolower($log),
                    strtolower($search)
                );
            });
        }

        // Filter by log level
        if ($level !== '' && $level !== 'ALL') {
            $logs = $logs->filter(function ($log) use ($level) {
                return str_contains($log, ".{$level}:")
                    || str_contains($log, ".{$level} ");
            });
        }

        $logs = $logs->values();

        // Calculate statistics from the filtered logs
        $statistics = [
            'total' => $logs->count(),
            'info' => $this->countLogLevel($logs, 'INFO'),
            'warning' => $this->countLogLevel($logs, 'WARNING'),
            'error' => $this->countLogLevel($logs, 'ERROR'),
            'debug' => $this->countLogLevel($logs, 'DEBUG'),
            'critical' => $this->countLogLevel($logs, 'CRITICAL'),
            'alert' => $this->countLogLevel($logs, 'ALERT'),
            'notice' => $this->countLogLevel($logs, 'NOTICE'),
            'emergency' => $this->countLogLevel($logs, 'EMERGENCY'),
        ];

        return view('admin.logs.index', compact(
            'logs',
            'statistics',
            'search',
            'level'
        ));
    }

    /**
     * Return latest logs for real-time monitoring.
     */
    public function live(Request $request): JsonResponse
    {
        $path = storage_path('logs/laravel.log');

        if (!File::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Log file not found.',
            ], 404);
        }

        // Read the latest logs
        $logs = collect(explode("\n", File::get($path)))
            ->map(fn ($log) => trim($log))
            ->filter()
            ->reverse()
            ->take(300)
            ->values();

        // Search keyword
        $search = trim($request->input('search', ''));

        // Selected log level
        $level = strtoupper(trim($request->input('level', 'ALL')));

        // Apply search filter
        if ($search !== '') {
            $logs = $logs->filter(function ($log) use ($search) {
                return str_contains(
                    strtolower($log),
                    strtolower($search)
                );
            });
        }

        // Apply level filter
        if ($level !== '' && $level !== 'ALL') {
            $logs = $logs->filter(function ($log) use ($level) {
                return str_contains($log, ".{$level}:")
                    || str_contains($log, ".{$level} ");
            });
        }

        $logs = $logs->values();

        // Calculate live statistics
        $statistics = [
            'total' => $logs->count(),
            'info' => $this->countLogLevel($logs, 'INFO'),
            'warning' => $this->countLogLevel($logs, 'WARNING'),
            'error' => $this->countLogLevel($logs, 'ERROR'),
            'debug' => $this->countLogLevel($logs, 'DEBUG'),
            'critical' => $this->countLogLevel($logs, 'CRITICAL'),
            'alert' => $this->countLogLevel($logs, 'ALERT'),
            'notice' => $this->countLogLevel($logs, 'NOTICE'),
            'emergency' => $this->countLogLevel($logs, 'EMERGENCY'),
        ];

        return response()->json([
            'success' => true,
            'logs' => $logs->all(),
            'statistics' => $statistics,
            'updated_at' => now()->format('d M Y, h:i:s A'),
            'total' => $logs->count(),
        ]);
    }

    /**
     * Export filtered logs as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $path = storage_path('logs/laravel.log');

        if (!File::exists($path)) {
            abort(404, 'Log file not found.');
        }

        // Read the log file
        $logs = collect(explode("\n", File::get($path)))
            ->map(fn ($log) => trim($log))
            ->filter()
            ->reverse()
            ->take(300)
            ->values();

        // Search keyword
        $search = trim($request->input('search', ''));

        // Selected log level
        $level = strtoupper(trim($request->input('level', 'ALL')));

        // Apply search filter
        if ($search !== '') {
            $logs = $logs->filter(function ($log) use ($search) {
                return str_contains(
                    strtolower($log),
                    strtolower($search)
                );
            });
        }

        // Apply level filter
        if ($level !== '' && $level !== 'ALL') {
            $logs = $logs->filter(function ($log) use ($level) {
                return str_contains($log, ".{$level}:")
                    || str_contains($log, ".{$level} ");
            });
        }

        $logs = $logs->values();

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // CSV header
            fputcsv($handle, [
                'Log Entry',
            ]);

            // CSV data
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log,
                ]);
            }

            fclose($handle);
        }, 'laravel-logs.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Count logs by level.
     */
    private function countLogLevel($logs, string $level): int
    {
        return $logs->filter(function ($log) use ($level) {
            return str_contains($log, ".{$level}:")
                || str_contains($log, ".{$level} ");
        })->count();
    }
}