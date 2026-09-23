<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Laravel Custom Log Viewer</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .live-pulse {
            animation: livePulse 1.5s infinite;
        }

        @keyframes livePulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.35;
            }

            100% {
                opacity: 1;
            }
        }

        .new-log {
            animation: newLog 0.8s ease;
        }

        @keyframes newLog {
            from {
                opacity: 0.4;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .log-content {
            max-height: 120px;
            overflow: hidden;
            position: relative;
        }

        .log-content.expanded {
            max-height: none;
        }

        /* =========================================================
           SUCCESS TOAST
        ========================================================= */

        #successToast {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 99999;
            min-width: 300px;
            max-width: 420px;
            transform: translateX(500px);
            opacity: 0;
            transition: all 0.35s ease;
            pointer-events: none;
        }

        #successToast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast-progress {
            height: 4px;
            width: 100%;
            transform-origin: left;
            animation: toastProgress 2.5s linear forwards;
        }

        @keyframes toastProgress {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }

        .copy-success {
            animation: copySuccess 0.8s ease;
        }

        @keyframes copySuccess {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>

</head>


<body class="bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 min-h-screen p-6 md:p-10">


    {{-- =========================================================
         SUCCESS TOAST
    ========================================================== --}}

    <div
        id="successToast"
        class="bg-white rounded-xl shadow-2xl overflow-hidden border border-green-200">

        <div class="flex items-start gap-3 p-4">

            <div
                class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">

                <span class="text-xl">
                    ✅
                </span>

            </div>

            <div class="flex-1">

                <p class="font-bold text-green-700">
                    Success
                </p>

                <p
                    id="successToastMessage"
                    class="text-sm text-gray-600 mt-1">
                    Action completed successfully.
                </p>

            </div>

            <button
                type="button"
                onclick="hideSuccessToast()"
                class="text-gray-400 hover:text-gray-700 text-xl leading-none">
                ×
            </button>

        </div>

        <div class="toast-progress bg-green-500"></div>

    </div>


    <div class="max-w-7xl mx-auto">


        {{-- =========================================================
             SERVER SUCCESS MESSAGE
        ========================================================== --}}

        @if(session('success'))

        <div
            id="serverSuccessMessage"
            class="bg-green-500 text-white px-6 py-4 rounded-xl shadow-xl mb-6">

            <div class="flex items-center justify-between">

                <span class="font-semibold">
                    ✅ {{ session('success') }}
                </span>

                <button
                    onclick="this.parentElement.parentElement.remove()"
                    class="text-white text-xl">
                    ×
                </button>

            </div>

        </div>

        @endif


        {{-- =========================================================
             HEADER
        ========================================================== --}}

        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-6">

            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <div>

                    <h1 class="text-3xl font-bold text-gray-800">
                        Laravel Log Viewer
                    </h1>

                    <p class="text-gray-500 mt-1">
                        Search, filter, monitor, export and manage Laravel logs
                    </p>

                </div>


                <div class="flex flex-wrap gap-2">

                    <a
                        href="{{ route('admin.logs') }}"
                        onclick="showSuccessToast('Refreshing Laravel logs...')"
                        class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition">
                        🔄 Refresh
                    </a>


                    <a
                        href="{{ route('admin.logs.export', [
                            'search' => $search,
                            'level' => $level,
                            'date' => $date
                        ]) }}"
                        onclick="showSuccessToast('CSV export started successfully.')"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        📊 Export CSV
                    </a>


                    <a
                        href="{{ route('admin.logs.download') }}"
                        onclick="showSuccessToast('Laravel log download started.')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        📥 Download Log
                    </a>


                    <a
                        href="{{ url('/log-viewer') }}"
                        onclick="showSuccessToast('Opening Official Log Viewer...')"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        Official Viewer
                    </a>

                </div>

            </div>

        </div>


        {{-- =========================================================
             STATISTICS
        ========================================================== --}}

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">


            <div class="bg-white rounded-2xl shadow-xl p-5 border-l-4 border-gray-700">

                <p class="text-sm font-semibold text-gray-500 uppercase">
                    Total Logs
                </p>

                <p
                    id="statTotal"
                    class="text-3xl font-bold text-gray-800 mt-2">
                    {{ $statistics['total'] }}
                </p>

            </div>


            <div class="bg-white rounded-2xl shadow-xl p-5 border-l-4 border-blue-500">

                <p class="text-sm font-semibold text-blue-600 uppercase">
                    Info
                </p>

                <p
                    id="statInfo"
                    class="text-3xl font-bold text-gray-800 mt-2">
                    {{ $statistics['info'] }}
                </p>

            </div>


            <div class="bg-white rounded-2xl shadow-xl p-5 border-l-4 border-yellow-500">

                <p class="text-sm font-semibold text-yellow-600 uppercase">
                    Warning
                </p>

                <p
                    id="statWarning"
                    class="text-3xl font-bold text-gray-800 mt-2">
                    {{ $statistics['warning'] }}
                </p>

            </div>


            <div class="bg-white rounded-2xl shadow-xl p-5 border-l-4 border-red-500">

                <p class="text-sm font-semibold text-red-600 uppercase">
                    Error
                </p>

                <p
                    id="statError"
                    class="text-3xl font-bold text-gray-800 mt-2">
                    {{ $statistics['error'] }}
                </p>

            </div>

        </div>


        {{-- =========================================================
             ADDITIONAL STATISTICS
        ========================================================== --}}

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">

            <div class="bg-white rounded-xl shadow-lg p-4">

                <p class="text-xs font-semibold text-gray-500 uppercase">
                    Debug
                </p>

                <p
                    id="statDebug"
                    class="text-2xl font-bold text-gray-800">
                    {{ $statistics['debug'] }}
                </p>

            </div>


            <div class="bg-white rounded-xl shadow-lg p-4">

                <p class="text-xs font-semibold text-gray-500 uppercase">
                    Critical
                </p>

                <p
                    id="statCritical"
                    class="text-2xl font-bold text-red-600">
                    {{ $statistics['critical'] }}
                </p>

            </div>


            <div class="bg-white rounded-xl shadow-lg p-4">

                <p class="text-xs font-semibold text-gray-500 uppercase">
                    Alert
                </p>

                <p
                    id="statAlert"
                    class="text-2xl font-bold text-red-600">
                    {{ $statistics['alert'] }}
                </p>

            </div>


            <div class="bg-white rounded-xl shadow-lg p-4">

                <p class="text-xs font-semibold text-gray-500 uppercase">
                    Emergency
                </p>

                <p
                    id="statEmergency"
                    class="text-2xl font-bold text-red-700">
                    {{ $statistics['emergency'] }}
                </p>

            </div>

        </div>


        {{-- =========================================================
             LOG TELEMETRY ANALYTICS & 24-HOUR HEALTH HEATMAP
        ========================================================== --}}
        @if(isset($telemetry))
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        📊 Log Telemetry Analytics & 24-Hour Health Heatmap
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Hourly system health distribution and error severity metrics</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-600">Health Status:</span>
                    @if($telemetry['healthStatus'] === 'CRITICAL')
                        <span class="px-4 py-1.5 bg-red-100 text-red-700 font-bold text-xs rounded-full border border-red-300 animate-pulse">🔴 CRITICAL SEVERITY</span>
                    @elseif($telemetry['healthStatus'] === 'ATTENTION NEEDED')
                        <span class="px-4 py-1.5 bg-yellow-100 text-yellow-800 font-bold text-xs rounded-full border border-yellow-300">⚠️ ATTENTION NEEDED</span>
                    @else
                        <span class="px-4 py-1.5 bg-green-100 text-green-700 font-bold text-xs rounded-full border border-green-300">✅ SYSTEM HEALTHY</span>
                    @endif
                </div>
            </div>

            <!-- Severity Progress Bar -->
            <div class="mb-6">
                <div class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                    <span>Error Severity Ratio: <strong>{{ $telemetry['errorRatio'] }}%</strong></span>
                    <span>Total Exceptions/Errors: <strong>{{ $telemetry['totalErrors'] }}</strong></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden flex">
                    <div class="bg-red-500 h-3 transition-all duration-500" style="width: {{ min(100, $telemetry['errorRatio']) }}%"></div>
                    <div class="bg-green-500 h-3 flex-1"></div>
                </div>
            </div>

            <!-- 24-Hour Heatmap Grid -->
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">24-Hour Hourly Log Activity Distribution (00:00 - 23:00)</p>
                <div class="grid grid-cols-6 sm:grid-cols-12 md:grid-cols-24 gap-1.5 text-center">
                    @foreach($telemetry['hourlyHeatmap'] as $hour => $count)
                        @php
                            $intensityClass = 'bg-gray-100 text-gray-500';
                            if ($count > 20) {
                                $intensityClass = 'bg-red-600 text-white font-bold shadow-md';
                            } elseif ($count > 10) {
                                $intensityClass = 'bg-orange-500 text-white font-bold';
                            } elseif ($count > 5) {
                                $intensityClass = 'bg-yellow-400 text-gray-900 font-semibold';
                            } elseif ($count > 0) {
                                $intensityClass = 'bg-blue-100 text-blue-800 font-semibold';
                            }
                        @endphp
                        <div class="p-2 rounded-lg {{ $intensityClass }} text-xs flex flex-col justify-between h-14 border border-gray-200/50" title="Hour {{ $hour }}:00 - {{ $count }} logs">
                            <span class="text-[10px] opacity-75">{{ $hour }}h</span>
                            <span class="text-sm font-bold mt-1">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
             SEARCH AND FILTER
        ========================================================== --}}

        <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">

            <h2 class="text-xl font-bold text-gray-800 mb-4">
                🔎 Search & Advanced Filters
            </h2>


            <form
                method="GET"
                action="{{ route('admin.logs') }}"
                onsubmit="showSuccessToast('Applying log filters...')"
                class="grid grid-cols-1 md:grid-cols-12 gap-4">


                <div class="md:col-span-5">

                    <label class="block text-sm font-semibold text-gray-600 mb-2">
                        Search Logs
                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search payment, user, exception..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">

                </div>


                <div class="md:col-span-3">

                    <label class="block text-sm font-semibold text-gray-600 mb-2">
                        Log Level
                    </label>

                    <select
                        name="level"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">

                        @foreach([
                        'ALL',
                        'DEBUG',
                        'INFO',
                        'NOTICE',
                        'WARNING',
                        'ERROR',
                        'CRITICAL',
                        'ALERT',
                        'EMERGENCY'
                        ] as $item)

                        <option
                            value="{{ $item }}"
                            {{ $level === $item ? 'selected' : '' }}>
                            {{ $item === 'ALL' ? 'All Levels' : $item }}
                        </option>

                        @endforeach

                    </select>

                </div>


                <div class="md:col-span-2">

                    <label class="block text-sm font-semibold text-gray-600 mb-2">
                        Date
                    </label>

                    <input
                        type="date"
                        name="date"
                        value="{{ $date }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">

                </div>


                <div class="md:col-span-2">

                    <label class="block text-sm font-semibold text-gray-600 mb-2">
                        Per Page
                    </label>

                    <select
                        name="per_page"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">

                        @foreach([25, 50, 100, 200] as $number)

                        <option
                            value="{{ $number }}"
                            {{ $perPage === $number ? 'selected' : '' }}>
                            {{ $number }}
                        </option>

                        @endforeach

                    </select>

                </div>


                <div class="md:col-span-12 flex gap-3">

                    <button
                        type="submit"
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700">
                        🔍 Apply Filters
                    </button>


                    <a
                        href="{{ route('admin.logs') }}"
                        onclick="showSuccessToast('Filters cleared successfully.')"
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">
                        Clear Filters
                    </a>

                </div>

            </form>


            @if(
            $search !== ''
            || $level !== 'ALL'
            || $date !== ''
            )

            <div class="mt-5 flex flex-wrap items-center gap-2">

                <span class="text-sm font-semibold text-gray-600">
                    Active Filters:
                </span>


                @if($search !== '')

                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">
                    Search: {{ $search }}
                </span>

                @endif


                @if($level !== 'ALL')

                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">
                    Level: {{ $level }}
                </span>

                @endif


                @if($date !== '')

                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                    Date: {{ $date }}
                </span>

                @endif

            </div>

            @endif

        </div>


        {{-- =========================================================
             INTERACTIVE TEST LOG GENERATOR STUDIO & EXCEPTION TESTER
        ========================================================== --}}
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-2 flex items-center gap-2">
                🧪 Interactive Test Log Generator Studio & Exception Tester
            </h2>
            <p class="text-sm text-gray-500 mb-5">Instantly generate test logs & mock exception stack traces to test monitoring filters and audio alarms.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Log Level Generator Form -->
                <form method="POST" action="{{ route('admin.logs.generate') }}" class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                    @csrf
                    <h3 class="font-bold text-gray-700 text-sm mb-3">⚡ Quick Log Entry Generator</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Log Level</label>
                            <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                                <option value="info">INFO - Informational Message</option>
                                <option value="warning">WARNING - System Warning</option>
                                <option value="error">ERROR - Application Error</option>
                                <option value="critical">CRITICAL - Severe Failure</option>
                                <option value="debug">DEBUG - Debug Trace</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Custom Message (Optional)</label>
                            <input type="text" name="message" placeholder="e.g. Payment Gateway Response Received" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                        </div>
                        <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-sm transition">
                            ➕ Inject Test Log
                        </button>
                    </div>
                </form>

                <!-- Mock Exception Generator Form -->
                <form method="POST" action="{{ route('admin.logs.generate') }}" class="bg-red-50 p-4 rounded-xl border border-red-200">
                    @csrf
                    <input type="hidden" name="type" value="exception">
                    <h3 class="font-bold text-red-800 text-sm mb-3">💥 Mock Exception & Stack Trace Simulator</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-red-700 mb-1">Preset Exception Type</label>
                            <select name="exception_type" class="w-full px-3 py-2 border border-red-300 rounded-lg text-sm bg-white">
                                <option value="database">Database Query Exception (SQLSTATE[HY000])</option>
                                <option value="404">NotFoundHttpException (404 Route Not Found)</option>
                                <option value="validation">ValidationException (Form Input Failed)</option>
                                <option value="runtime">Runtime System Failure Exception</option>
                            </select>
                        </div>
                        <p class="text-xs text-red-600">Simulates real production stack traces to trigger audio alarms & auto-scroll.</p>
                        <button type="submit" class="w-full py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold text-sm transition">
                            🚨 Trigger Mock Exception
                        </button>
                    </div>
                </form>
            </div>
        </div>


        {{-- =========================================================
             LIVE MONITORING
        ========================================================== --}}

        <div class="bg-white rounded-2xl shadow-xl p-5 mb-6">

            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">


                <div>

                    <div class="flex items-center gap-3">

                        <span
                            id="liveIndicator"
                            class="w-3 h-3 rounded-full bg-gray-400 inline-block">
                        </span>


                        <h2 class="text-xl font-bold text-gray-800">
                            🔴 Real-Time Log Monitoring
                        </h2>


                        <span
                            id="liveStatus"
                            class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                            Monitoring OFF
                        </span>

                    </div>


                    <p class="text-sm text-gray-500 mt-2">
                        Automatically checks for new Laravel logs.
                    </p>


                    <p class="text-xs text-gray-400 mt-1">

                        Last updated:

                        <span id="lastUpdated">
                            Not started
                        </span>

                    </p>

                </div>


                <div class="flex flex-wrap items-center gap-3">


                    <label class="flex items-center cursor-pointer">

                        <input
                            type="checkbox"
                            id="liveToggle"
                            class="sr-only">

                        <div
                            id="toggleTrack"
                            class="w-14 h-8 bg-gray-300 rounded-full relative transition">

                            <div
                                id="toggleCircle"
                                class="absolute left-1 top-1 w-6 h-6 bg-white rounded-full shadow transition">
                            </div>

                        </div>

                        <span class="ml-3 text-sm font-medium text-gray-700">
                            Auto Refresh
                        </span>

                    </label>


                    <button
                        id="pauseButton"
                        onclick="togglePause()"
                        class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                        ⏸ Pause
                    </button>


                    <select
                        id="refreshInterval"
                        class="px-3 py-2 border border-gray-300 rounded-lg">

                        <option value="3000">
                            3 sec
                        </option>

                        <option value="5000" selected>
                            5 sec
                        </option>

                        <option value="10000">
                            10 sec
                        </option>

                        <option value="30000">
                            30 sec
                        </option>

                    </select>


                    <label class="flex items-center gap-2 text-sm">

                        <input
                            type="checkbox"
                            id="autoScroll"
                            checked
                            class="w-4 h-4">

                        Auto Scroll

                    </label>

                    <label class="flex items-center gap-2 text-sm font-semibold text-red-600 cursor-pointer bg-red-50 px-3 py-1.5 rounded-lg border border-red-200">
                        <input
                            type="checkbox"
                            id="audioAlarmToggle"
                            checked
                            class="w-4 h-4 text-red-600 rounded focus:ring-red-500">
                        🔊 Audio Alarm
                    </label>

                </div>

            </div>

        </div>


        {{-- =========================================================
             LOG LIST
        ========================================================== --}}

        <div class="bg-black rounded-2xl shadow-2xl overflow-hidden">


            <div class="bg-gray-900 px-6 py-4 border-b border-gray-700">

                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">

                    <div>

                        <h2 class="text-xl font-bold text-white">
                            Log Entries
                        </h2>

                        <p class="text-gray-400 text-sm mt-1">

                            Showing

                            <span id="logCount">
                                {{ $paginatedLogs->count() }}
                            </span>

                            of

                            <span>
                                {{ $totalLogs }}
                            </span>

                            matching entries

                        </p>

                    </div>


                    <div class="text-sm text-gray-400">
                        storage/logs/laravel.log
                    </div>

                </div>

            </div>


            <div
                id="logsContainer"
                class="p-6 max-h-[700px] overflow-y-auto">

                @forelse($paginatedLogs as $log)

                @php

                $color = 'text-gray-300';
                $badge = 'bg-gray-700 text-gray-200';
                $levelName = 'LOG';

                if (
                str_contains($log, '.EMERGENCY:')
                || str_contains($log, '.EMERGENCY ')
                ) {
                $color = 'text-red-200';
                $badge = 'bg-red-900 text-red-200';
                $levelName = 'EMERGENCY';
                }

                elseif (
                str_contains($log, '.ALERT:')
                || str_contains($log, '.ALERT ')
                ) {
                $color = 'text-red-300';
                $badge = 'bg-red-800 text-red-100';
                $levelName = 'ALERT';
                }

                elseif (
                str_contains($log, '.CRITICAL:')
                || str_contains($log, '.CRITICAL ')
                ) {
                $color = 'text-red-400';
                $badge = 'bg-red-700 text-white';
                $levelName = 'CRITICAL';
                }

                elseif (
                str_contains($log, '.ERROR:')
                || str_contains($log, '.ERROR ')
                ) {
                $color = 'text-red-400';
                $badge = 'bg-red-600 text-white';
                $levelName = 'ERROR';
                }

                elseif (
                str_contains($log, '.WARNING:')
                || str_contains($log, '.WARNING ')
                ) {
                $color = 'text-yellow-400';
                $badge = 'bg-yellow-700 text-yellow-100';
                $levelName = 'WARNING';
                }

                elseif (
                str_contains($log, '.NOTICE:')
                || str_contains($log, '.NOTICE ')
                ) {
                $color = 'text-cyan-400';
                $badge = 'bg-cyan-800 text-cyan-100';
                $levelName = 'NOTICE';
                }

                elseif (
                str_contains($log, '.INFO:')
                || str_contains($log, '.INFO ')
                ) {
                $color = 'text-blue-400';
                $badge = 'bg-blue-700 text-white';
                $levelName = 'INFO';
                }

                elseif (
                str_contains($log, '.DEBUG:')
                || str_contains($log, '.DEBUG ')
                ) {
                $color = 'text-gray-400';
                $badge = 'bg-gray-700 text-gray-200';
                $levelName = 'DEBUG';
                }

                @endphp


                <div
                    class="mb-4 pb-4 border-b border-gray-800 log-item">

                    <div
                        class="flex flex-col md:flex-row md:items-start gap-3">


                        <div class="shrink-0">

                            <span
                                class="inline-block px-2 py-1 rounded text-xs font-bold {{ $badge }}">
                                {{ $levelName }}
                            </span>

                        </div>


                        <div class="flex-1 min-w-0">

                            <div
                                class="{{ $color }} text-sm font-mono whitespace-pre-wrap break-words log-content">
                                {{ $log }}
                            </div>


                            <div class="flex flex-wrap gap-2 mt-3">

                                <button
                                    type="button"
                                    onclick="copyLog(this)"
                                    class="px-3 py-1 bg-gray-700 text-gray-200 rounded text-xs hover:bg-gray-600">
                                    📋 Copy
                                </button>


                                <button
                                    type="button"
                                    onclick="toggleLog(this)"
                                    class="px-3 py-1 bg-blue-700 text-white rounded text-xs hover:bg-blue-600">
                                    🔍 Expand
                                </button>

                            </div>

                        </div>

                    </div>

                </div>


                @empty

                <div
                    id="emptyState"
                    class="text-center py-16">

                    <div class="text-5xl mb-4">
                        🔍
                    </div>

                    <h3 class="text-xl font-bold text-white">
                        No logs found
                    </h3>

                    <p class="text-gray-400 mt-2">
                        No log entries match the current filters.
                    </p>

                    <a
                        href="{{ route('admin.logs') }}"
                        onclick="showSuccessToast('Filters cleared successfully.')"
                        class="inline-block mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg">
                        Clear Filters
                    </a>

                </div>

                @endforelse

            </div>

        </div>


        {{-- =========================================================
             PAGINATION
        ========================================================== --}}

        @if($totalPages > 1)

        <div class="bg-white rounded-2xl shadow-xl p-5 mt-6">

            <div class="flex flex-wrap justify-center gap-2">

                @for($page = 1; $page <= $totalPages; $page++)

                    <a
                    href="{{ route('admin.logs', [
                            'page' => $page,
                            'search' => $search,
                            'level' => $level,
                            'date' => $date,
                            'per_page' => $perPage
                        ]) }}"
                    onclick="showSuccessToast('Loading page {{ $page }}...')"
                    class="px-4 py-2 rounded-lg font-semibold
                        {{ $page === $currentPage
                            ? 'bg-purple-600 text-white'
                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                        }}">

                    {{ $page }}

                    </a>

                    @endfor

            </div>

        </div>

        @endif


        {{-- =========================================================
             DANGER ZONE
        ========================================================== --}}

        <div
            class="bg-white rounded-2xl shadow-xl p-6 mt-6 border-l-4 border-red-600">

            <h2 class="text-xl font-bold text-red-600">
                ⚠️ Log Management
            </h2>

            <p class="text-gray-500 mt-1 mb-4">
                Clearing the log permanently removes the current
                <code>laravel.log</code> contents.
            </p>


            <form
                method="POST"
                action="{{ route('admin.logs.clear') }}"
                onsubmit="return confirmClearLog();">

                @csrf

                <button
                    type="submit"
                    class="px-5 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700">
                    🗑️ Clear Laravel Log
                </button>

            </form>

        </div>


        {{-- =========================================================
             FOOTER
        ========================================================== --}}

        <div class="text-center text-white/80 text-sm mt-6">
            Laravel Custom Log Monitoring Dashboard
        </div>

    </div>


    <script>
        /*
        |--------------------------------------------------------------------------
        | Current Filters
        |--------------------------------------------------------------------------
        */

        const searchValue = @json($search);
        const levelValue = @json($level);
        const dateValue = @json($date);


        /*
        |--------------------------------------------------------------------------
        | DOM Elements
        |--------------------------------------------------------------------------
        */

        const liveToggle =
            document.getElementById('liveToggle');

        const liveIndicator =
            document.getElementById('liveIndicator');

        const liveStatus =
            document.getElementById('liveStatus');

        const lastUpdated =
            document.getElementById('lastUpdated');

        const toggleTrack =
            document.getElementById('toggleTrack');

        const toggleCircle =
            document.getElementById('toggleCircle');

        const logsContainer =
            document.getElementById('logsContainer');

        const logCount =
            document.getElementById('logCount');

        const refreshInterval =
            document.getElementById('refreshInterval');

        const autoScroll =
            document.getElementById('autoScroll');

        const pauseButton =
            document.getElementById('pauseButton');


        /*
        |--------------------------------------------------------------------------
        | Monitoring State
        |--------------------------------------------------------------------------
        */

        let liveInterval = null;

        let isPaused = false;

        let toastTimer = null;


        /*
        |--------------------------------------------------------------------------
        | SUCCESS TOAST
        |--------------------------------------------------------------------------
        */

        function showSuccessToast(message) {

            const toast =
                document.getElementById('successToast');

            const toastMessage =
                document.getElementById('successToastMessage');

            if (!toast || !toastMessage) {
                return;
            }

            toastMessage.textContent =
                message;

            toast.classList.add('show');

            clearTimeout(toastTimer);

            const progress =
                toast.querySelector('.toast-progress');

            if (progress) {

                progress.style.animation = 'none';

                void progress.offsetWidth;

                progress.style.animation =
                    'toastProgress 2.5s linear forwards';

            }

            toastTimer =
                setTimeout(function() {

                    hideSuccessToast();

                }, 2500);
        }


        /*
        |--------------------------------------------------------------------------
        | HIDE SUCCESS TOAST
        |--------------------------------------------------------------------------
        */

        function hideSuccessToast() {

            const toast =
                document.getElementById('successToast');

            if (toast) {

                toast.classList.remove('show');

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Escape HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            const div =
                document.createElement('div');

            div.textContent =
                value;

            return div.innerHTML;
        }


        /*
        |--------------------------------------------------------------------------
        | Get Log Level
        |--------------------------------------------------------------------------
        */

        function getLogLevel(log) {

            const levels = [
                'EMERGENCY',
                'ALERT',
                'CRITICAL',
                'ERROR',
                'WARNING',
                'NOTICE',
                'INFO',
                'DEBUG'
            ];

            for (const level of levels) {

                if (
                    log.includes('.' + level + ':') ||
                    log.includes('.' + level + ' ')
                ) {

                    return level;

                }

            }

            return 'LOG';
        }


        /*
        |--------------------------------------------------------------------------
        | Badge Class
        |--------------------------------------------------------------------------
        */

        function getBadgeClass(level) {

            switch (level) {

                case 'EMERGENCY':
                    return 'bg-red-900 text-red-200';

                case 'ALERT':
                    return 'bg-red-800 text-red-100';

                case 'CRITICAL':
                    return 'bg-red-700 text-white';

                case 'ERROR':
                    return 'bg-red-600 text-white';

                case 'WARNING':
                    return 'bg-yellow-700 text-yellow-100';

                case 'NOTICE':
                    return 'bg-cyan-800 text-cyan-100';

                case 'INFO':
                    return 'bg-blue-700 text-white';

                case 'DEBUG':
                    return 'bg-gray-700 text-gray-200';

                default:
                    return 'bg-gray-700 text-gray-200';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Text Color
        |--------------------------------------------------------------------------
        */

        function getTextColor(level) {

            switch (level) {

                case 'EMERGENCY':
                    return 'text-red-200';

                case 'ALERT':
                    return 'text-red-300';

                case 'CRITICAL':
                    return 'text-red-400';

                case 'ERROR':
                    return 'text-red-400';

                case 'WARNING':
                    return 'text-yellow-400';

                case 'NOTICE':
                    return 'text-cyan-400';

                case 'INFO':
                    return 'text-blue-400';

                case 'DEBUG':
                    return 'text-gray-400';

                default:
                    return 'text-gray-300';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Update Logs
        |--------------------------------------------------------------------------
        */

        function updateLogs(logs) {

            if (!logsContainer) {
                return;
            }


            if (logs.length === 0) {

                logsContainer.innerHTML = `

                    <div class="text-center py-16">

                        <div class="text-5xl mb-4">
                            🔍
                        </div>

                        <h3 class="text-xl font-bold text-white">
                            No logs found
                        </h3>

                        <p class="text-gray-400 mt-2">
                            No log entries match the current filters.
                        </p>

                    </div>

                `;

                logCount.textContent = '0';

                return;
            }


            logsContainer.innerHTML =
                logs.map(function(log) {

                    const level =
                        getLogLevel(log);

                    const badgeClass =
                        getBadgeClass(level);

                    const textColor =
                        getTextColor(level);

                    const safeLog =
                        escapeHtml(log);


                    return `

                        <div
                            class="mb-4 pb-4 border-b border-gray-800 log-item new-log">

                            <div
                                class="flex flex-col md:flex-row md:items-start gap-3">

                                <div class="shrink-0">

                                    <span
                                        class="inline-block px-2 py-1 rounded text-xs font-bold ${badgeClass}">
                                        ${level}
                                    </span>

                                </div>


                                <div class="flex-1 min-w-0">

                                    <div
                                        class="${textColor} text-sm font-mono whitespace-pre-wrap break-words log-content">
                                        ${safeLog}
                                    </div>


                                    <div class="flex flex-wrap gap-2 mt-3">

                                        <button
                                            type="button"
                                            onclick="copyLog(this)"
                                            class="px-3 py-1 bg-gray-700 text-gray-200 rounded text-xs hover:bg-gray-600">
                                            📋 Copy
                                        </button>


                                        <button
                                            type="button"
                                            onclick="toggleLog(this)"
                                            class="px-3 py-1 bg-blue-700 text-white rounded text-xs hover:bg-blue-600">
                                            🔍 Expand
                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    `;

                }).join('');


            logCount.textContent =
                logs.length;


            if (autoScroll && autoScroll.checked) {

                logsContainer.scrollTop =
                    0;

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Update Statistics
        |--------------------------------------------------------------------------
        */

        function updateStatistics(statistics) {

            document.getElementById('statTotal').textContent =
                statistics.total;

            document.getElementById('statInfo').textContent =
                statistics.info;

            document.getElementById('statWarning').textContent =
                statistics.warning;

            document.getElementById('statError').textContent =
                statistics.error;

            document.getElementById('statDebug').textContent =
                statistics.debug;

            document.getElementById('statCritical').textContent =
                statistics.critical;

            document.getElementById('statAlert').textContent =
                statistics.alert;

            document.getElementById('statEmergency').textContent =
                statistics.emergency;
        }


        /*
        |--------------------------------------------------------------------------
        | Audio Alarm Synthesizer
        |--------------------------------------------------------------------------
        */

        function playAudioAlarmBeep() {
            const audioAlarmToggle = document.getElementById('audioAlarmToggle');
            if (!audioAlarmToggle || !audioAlarmToggle.checked) return;

            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const audioCtx = new AudioCtx();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.2, audioCtx.currentTime);

                osc.connect(gain);
                gain.connect(audioCtx.destination);

                osc.start();
                osc.stop(audioCtx.currentTime + 0.25);
            } catch (e) {
                console.warn('Audio alarm playback error:', e);
            }
        }

        let previousLogCount = null;

        /*
        |--------------------------------------------------------------------------
        | Fetch Live Logs
        |--------------------------------------------------------------------------
        */

        async function fetchLiveLogs() {

            if (isPaused) {
                return;
            }


            try {

                const params =
                    new URLSearchParams();


                if (searchValue !== '') {

                    params.append(
                        'search',
                        searchValue
                    );

                }


                if (levelValue !== '') {

                    params.append(
                        'level',
                        levelValue
                    );

                }


                if (dateValue !== '') {

                    params.append(
                        'date',
                        dateValue
                    );

                }


                const response =
                    await fetch(
                        `{{ route('admin.logs.live') }}?${params.toString()}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        }
                    );


                if (!response.ok) {

                    throw new Error(
                        'Unable to fetch live logs.'
                    );

                }


                const data =
                    await response.json();


                if (data.success) {

                    if (previousLogCount !== null && data.logs.length > previousLogCount) {
                        const newLogs = data.logs.slice(0, data.logs.length - previousLogCount);
                        const hasCriticalNewLog = newLogs.some(log => {
                            const level = getLogLevel(log);
                            return ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'].includes(level);
                        });

                        if (hasCriticalNewLog) {
                            playAudioAlarmBeep();
                        }
                    }

                    previousLogCount = data.logs.length;

                    updateLogs(
                        data.logs
                    );


                    updateStatistics(
                        data.statistics
                    );


                    lastUpdated.textContent =
                        data.updated_at;

                }

            } catch (error) {

                console.error(
                    'Real-time monitoring error:',
                    error
                );

                lastUpdated.textContent =
                    'Connection error';

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Start Monitoring
        |--------------------------------------------------------------------------
        */

        function startMonitoring() {

            if (liveInterval !== null) {
                return;
            }


            isPaused = false;

            liveToggle.checked =
                true;


            liveIndicator.classList.remove(
                'bg-gray-400'
            );

            liveIndicator.classList.add(
                'bg-green-500',
                'live-pulse'
            );


            liveStatus.textContent =
                'Monitoring ON';


            liveStatus.classList.remove(
                'bg-gray-100',
                'text-gray-600'
            );


            liveStatus.classList.add(
                'bg-green-100',
                'text-green-700'
            );


            toggleTrack.classList.remove(
                'bg-gray-300'
            );


            toggleTrack.classList.add(
                'bg-green-500'
            );


            toggleCircle.classList.add(
                'translate-x-6'
            );


            fetchLiveLogs();


            liveInterval =
                setInterval(
                    fetchLiveLogs,
                    parseInt(refreshInterval.value)
                );


            showSuccessToast(
                'Real-time monitoring started successfully.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Stop Monitoring
        |--------------------------------------------------------------------------
        */

        function stopMonitoring() {

            liveToggle.checked =
                false;


            if (liveInterval !== null) {

                clearInterval(
                    liveInterval
                );

                liveInterval =
                    null;
            }


            liveIndicator.classList.remove(
                'bg-green-500',
                'live-pulse'
            );


            liveIndicator.classList.add(
                'bg-gray-400'
            );


            liveStatus.textContent =
                'Monitoring OFF';


            liveStatus.classList.remove(
                'bg-green-100',
                'text-green-700'
            );


            liveStatus.classList.add(
                'bg-gray-100',
                'text-gray-600'
            );


            toggleTrack.classList.remove(
                'bg-green-500'
            );


            toggleTrack.classList.add(
                'bg-gray-300'
            );


            toggleCircle.classList.remove(
                'translate-x-6'
            );


            showSuccessToast(
                'Real-time monitoring stopped.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Refresh Interval
        |--------------------------------------------------------------------------
        */

        refreshInterval.addEventListener(
            'change',
            function() {

                const seconds =
                    parseInt(this.value) / 1000;


                if (liveInterval !== null) {

                    clearInterval(
                        liveInterval
                    );


                    liveInterval =
                        setInterval(
                            fetchLiveLogs,
                            parseInt(this.value)
                        );
                }


                showSuccessToast(
                    'Auto refresh interval changed to ' +
                    seconds +
                    ' seconds.'
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Toggle Monitoring
        |--------------------------------------------------------------------------
        */

        liveToggle.addEventListener(
            'change',
            function() {

                if (this.checked) {

                    startMonitoring();

                } else {

                    stopMonitoring();

                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Pause / Resume
        |--------------------------------------------------------------------------
        */

        function togglePause() {

            isPaused = !isPaused;


            if (isPaused) {

                pauseButton.textContent =
                    '▶ Resume';


                pauseButton.classList.remove(
                    'bg-orange-500',
                    'hover:bg-orange-600'
                );


                pauseButton.classList.add(
                    'bg-green-600',
                    'hover:bg-green-700'
                );


                liveStatus.textContent =
                    'Monitoring PAUSED';


                liveStatus.classList.remove(
                    'bg-green-100',
                    'text-green-700'
                );


                liveStatus.classList.add(
                    'bg-orange-100',
                    'text-orange-700'
                );


                showSuccessToast(
                    'Real-time monitoring paused.'
                );

            } else {

                pauseButton.textContent =
                    '⏸ Pause';


                pauseButton.classList.remove(
                    'bg-green-600',
                    'hover:bg-green-700'
                );


                pauseButton.classList.add(
                    'bg-orange-500',
                    'hover:bg-orange-600'
                );


                liveStatus.textContent =
                    'Monitoring ON';


                liveStatus.classList.remove(
                    'bg-orange-100',
                    'text-orange-700'
                );


                liveStatus.classList.add(
                    'bg-green-100',
                    'text-green-700'
                );


                fetchLiveLogs();


                showSuccessToast(
                    'Real-time monitoring resumed.'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Copy Individual Log
        |--------------------------------------------------------------------------
        */

        function copyLog(button) {

            const content =
                button
                .closest('.log-item')
                .querySelector('.log-content')
                .innerText;


            if (
                navigator.clipboard &&
                window.isSecureContext
            ) {

                navigator.clipboard
                    .writeText(content)
                    .then(function() {

                        showCopiedState(button);

                    })
                    .catch(function() {

                        fallbackCopy(content, button);

                    });

            } else {

                fallbackCopy(
                    content,
                    button
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Copy Fallback
        |--------------------------------------------------------------------------
        */

        function fallbackCopy(
            content,
            button
        ) {

            const textarea =
                document.createElement('textarea');


            textarea.value =
                content;


            textarea.style.position =
                'fixed';

            textarea.style.opacity =
                '0';


            document.body.appendChild(
                textarea
            );


            textarea.select();


            try {

                document.execCommand(
                    'copy'
                );


                showCopiedState(
                    button
                );

            } catch (error) {

                showSuccessToast(
                    'Unable to copy the log entry.'
                );

            }


            document.body.removeChild(
                textarea
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Copied State
        |--------------------------------------------------------------------------
        */

        function showCopiedState(button) {

            const original =
                button.textContent;


            button.textContent =
                '✅ Copied!';


            button.classList.add(
                'copy-success'
            );


            showSuccessToast(
                'Log entry copied to clipboard successfully.'
            );


            setTimeout(
                function() {

                    button.textContent =
                        original;


                    button.classList.remove(
                        'copy-success'
                    );

                },
                1500
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Expand / Collapse
        |--------------------------------------------------------------------------
        */

        function toggleLog(button) {

            const content =
                button
                .closest('.log-item')
                .querySelector('.log-content');


            const isExpanded =
                content.classList.toggle(
                    'expanded'
                );


            if (isExpanded) {

                button.textContent =
                    '🔽 Collapse';


                button.classList.remove(
                    'bg-blue-700',
                    'hover:bg-blue-600'
                );


                button.classList.add(
                    'bg-purple-700',
                    'hover:bg-purple-600'
                );


                showSuccessToast(
                    'Log entry expanded successfully.'
                );

            } else {

                button.textContent =
                    '🔍 Expand';


                button.classList.remove(
                    'bg-purple-700',
                    'hover:bg-purple-600'
                );


                button.classList.add(
                    'bg-blue-700',
                    'hover:bg-blue-600'
                );


                showSuccessToast(
                    'Log entry collapsed successfully.'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Auto Scroll
        |--------------------------------------------------------------------------
        */

        autoScroll.addEventListener(
            'change',
            function() {

                if (this.checked) {

                    showSuccessToast(
                        'Auto Scroll enabled.'
                    );


                    logsContainer.scrollTop =
                        0;

                } else {

                    showSuccessToast(
                        'Auto Scroll disabled.'
                    );
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Confirm Clear
        |--------------------------------------------------------------------------
        */

        function confirmClearLog() {

            const confirmed =
                confirm(
                    'Are you sure you want to permanently clear the Laravel log file?'
                );


            if (confirmed) {

                showSuccessToast(
                    'Laravel log is being cleared...'
                );

            }


            return confirmed;
        }


        /*
        |--------------------------------------------------------------------------
        | Auto Start Monitoring
        |--------------------------------------------------------------------------
        */

        startMonitoring();
    </script>


</body>

</html>