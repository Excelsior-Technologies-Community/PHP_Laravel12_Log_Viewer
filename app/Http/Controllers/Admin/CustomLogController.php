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
            File::put($path, '');
        }

        $search = trim($request->input('search', ''));

        $level = strtoupper(
            trim($request->input('level', 'ALL'))
        );

        $date = trim(
            $request->input('date', '')
        );

        $perPage = (int) $request->input('per_page', 50);

        if (!in_array($perPage, [25, 50, 100, 200], true)) {
            $perPage = 50;
        }

        /*
        |--------------------------------------------------------------------------
        | Read Log File
        |--------------------------------------------------------------------------
        */

        $logs = $this->readLogs($path);

        /*
        |--------------------------------------------------------------------------
        | Search Filter
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $logs = $logs->filter(function ($log) use ($search) {
                return str_contains(
                    strtolower($log),
                    strtolower($search)
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Level Filter
        |--------------------------------------------------------------------------
        */

        if ($level !== '' && $level !== 'ALL') {
            $logs = $logs->filter(function ($log) use ($level) {
                return $this->hasLogLevel($log, $level);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */

        if ($date !== '') {
            $logs = $logs->filter(function ($log) use ($date) {
                return $this->logMatchesDate($log, $date);
            });
        }

        $logs = $logs->values();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [
            'total' => $logs->count(),

            'info' => $this->countLogLevel(
                $logs,
                'INFO'
            ),

            'warning' => $this->countLogLevel(
                $logs,
                'WARNING'
            ),

            'error' => $this->countLogLevel(
                $logs,
                'ERROR'
            ),

            'debug' => $this->countLogLevel(
                $logs,
                'DEBUG'
            ),

            'critical' => $this->countLogLevel(
                $logs,
                'CRITICAL'
            ),

            'alert' => $this->countLogLevel(
                $logs,
                'ALERT'
            ),

            'notice' => $this->countLogLevel(
                $logs,
                'NOTICE'
            ),

            'emergency' => $this->countLogLevel(
                $logs,
                'EMERGENCY'
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $currentPage = max(
            1,
            (int) $request->input('page', 1)
        );

        $totalLogs = $logs->count();

        $totalPages = max(
            1,
            (int) ceil($totalLogs / $perPage)
        );

        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }

        $paginatedLogs = $logs
            ->slice(
                ($currentPage - 1) * $perPage,
                $perPage
            )
            ->values();

        return view(
            'admin.logs.index',
            compact(
                'paginatedLogs',
                'statistics',
                'search',
                'level',
                'date',
                'perPage',
                'currentPage',
                'totalPages',
                'totalLogs'
            )
        );
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

        $logs = $this->readLogs($path);

        $search = trim(
            $request->input('search', '')
        );

        $level = strtoupper(
            trim($request->input('level', 'ALL'))
        );

        $date = trim(
            $request->input('date', '')
        );

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $logs = $logs->filter(function ($log) use ($search) {
                return str_contains(
                    strtolower($log),
                    strtolower($search)
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Level
        |--------------------------------------------------------------------------
        */

        if ($level !== '' && $level !== 'ALL') {
            $logs = $logs->filter(function ($log) use ($level) {
                return $this->hasLogLevel($log, $level);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        if ($date !== '') {
            $logs = $logs->filter(function ($log) use ($date) {
                return $this->logMatchesDate($log, $date);
            });
        }

        $logs = $logs->values();

        /*
        |--------------------------------------------------------------------------
        | Live Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [
            'total' => $logs->count(),

            'info' => $this->countLogLevel(
                $logs,
                'INFO'
            ),

            'warning' => $this->countLogLevel(
                $logs,
                'WARNING'
            ),

            'error' => $this->countLogLevel(
                $logs,
                'ERROR'
            ),

            'debug' => $this->countLogLevel(
                $logs,
                'DEBUG'
            ),

            'critical' => $this->countLogLevel(
                $logs,
                'CRITICAL'
            ),

            'alert' => $this->countLogLevel(
                $logs,
                'ALERT'
            ),

            'notice' => $this->countLogLevel(
                $logs,
                'NOTICE'
            ),

            'emergency' => $this->countLogLevel(
                $logs,
                'EMERGENCY'
            ),
        ];

        return response()->json([
            'success' => true,

            'logs' => $logs
                ->take(300)
                ->values()
                ->all(),

            'statistics' => $statistics,

            'updated_at' => now()->format(
                'd M Y, h:i:s A'
            ),

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

        $logs = $this->getFilteredLogs(
            $request,
            $path
        );

        return response()->streamDownload(
            function () use ($logs) {

                $handle = fopen(
                    'php://output',
                    'w'
                );

                fputcsv(
                    $handle,
                    ['Log Entry']
                );

                foreach ($logs as $log) {
                    fputcsv(
                        $handle,
                        [$log]
                    );
                }

                fclose($handle);
            },
            'laravel-logs.csv',
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }

    /**
     * Clear Laravel log file.
     */
    public function clear(Request $request)
    {
        $path = storage_path('logs/laravel.log');

        if (!File::exists($path)) {
            File::put($path, '');
        } else {
            File::put($path, '');
        }

        return redirect()
            ->route('admin.logs')
            ->with(
                'success',
                'Laravel log file cleared successfully.'
            );
    }

    /**
     * Download complete raw log file.
     */
    public function download(): StreamedResponse
    {
        $path = storage_path('logs/laravel.log');

        if (!File::exists($path)) {
            abort(404, 'Log file not found.');
        }

        return response()->download(
            $path,
            'laravel.log',
            [
                'Content-Type' => 'text/plain',
            ]
        );
    }

    /**
     * Read log file.
     */
    private function readLogs(string $path)
    {
        return collect(
            explode(
                "\n",
                File::get($path)
            )
        )
            ->map(
                fn($log) => trim($log)
            )
            ->filter()
            ->reverse()
            ->values();
    }

    /**
     * Get filtered logs.
     */
    private function getFilteredLogs(
        Request $request,
        string $path
    ) {
        $logs = $this->readLogs($path);

        $search = trim(
            $request->input('search', '')
        );

        $level = strtoupper(
            trim($request->input('level', 'ALL'))
        );

        $date = trim(
            $request->input('date', '')
        );

        if ($search !== '') {
            $logs = $logs->filter(function ($log) use ($search) {
                return str_contains(
                    strtolower($log),
                    strtolower($search)
                );
            });
        }

        if ($level !== '' && $level !== 'ALL') {
            $logs = $logs->filter(function ($log) use ($level) {
                return $this->hasLogLevel($log, $level);
            });
        }

        if ($date !== '') {
            $logs = $logs->filter(function ($log) use ($date) {
                return $this->logMatchesDate($log, $date);
            });
        }

        return $logs->values();
    }

    /**
     * Check log level.
     */
    private function hasLogLevel(
        string $log,
        string $level
    ): bool {
        return str_contains(
            $log,
            ".{$level}:"
        ) || str_contains(
            $log,
            ".{$level} "
        );
    }

    /**
     * Check log date.
     *
     * Laravel log example:
     * [2026-09-21 11:20:30] local.INFO: Message
     */
    private function logMatchesDate(
        string $log,
        string $date
    ): bool {
        return str_contains(
            $log,
            "[{$date}"
        );
    }

    /**
     * Count logs by level.
     */
    private function countLogLevel(
        $logs,
        string $level
    ): int {
        return $logs
            ->filter(function ($log) use ($level) {
                return $this->hasLogLevel(
                    $log,
                    $level
                );
            })
            ->count();
    }
}
