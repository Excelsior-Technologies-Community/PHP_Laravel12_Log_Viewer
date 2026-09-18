<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Custom Log Viewer</title>

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
    </style>
</head>


<body class="bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 min-h-screen p-6 md:p-10">

<div class="max-w-7xl mx-auto">


    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-2xl p-6 mb-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>

                <h1 class="text-3xl font-bold text-gray-800">
                    Laravel Log Viewer
                </h1>

                <p class="text-gray-500 mt-1">
                    Search, filter, monitor and export application logs
                </p>

            </div>


            <div class="flex flex-wrap gap-2">

                <a
                    href="{{ route('admin.logs') }}"
                    class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition"
                >
                    Refresh
                </a>


                <a
                    href="{{ route('admin.logs.export', [
                        'search' => $search,
                        'level' => $level
                    ]) }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                >
                    Export CSV
                </a>


                <a
                    href="{{ url('/log-viewer') }}"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
                >
                    Official Log Viewer
                </a>

            </div>

        </div>

    </div>



    <!-- Statistics Dashboard -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">


        <!-- Total -->
        <div class="bg-white rounded-2xl shadow-xl p-5 border-l-4 border-gray-700">

            <p class="text-sm font-semibold text-gray-500 uppercase">
                Total Logs
            </p>

            <p
                id="statTotal"
                class="text-3xl font-bold text-gray-800 mt-2"
            >
                {{ $statistics['total'] }}
            </p>

        </div>



        <!-- Info -->
        <div class="bg-white rounded-2xl shadow-xl p-5 border-l-4 border-blue-500">

            <p class="text-sm font-semibold text-blue-600 uppercase">
                Info
            </p>

            <p
                id="statInfo"
                class="text-3xl font-bold text-gray-800 mt-2"
            >
                {{ $statistics['info'] }}
            </p>

        </div>



        <!-- Warning -->
        <div class="bg-white rounded-2xl shadow-xl p-5 border-l-4 border-yellow-500">

            <p class="text-sm font-semibold text-yellow-600 uppercase">
                Warning
            </p>

            <p
                id="statWarning"
                class="text-3xl font-bold text-gray-800 mt-2"
            >
                {{ $statistics['warning'] }}
            </p>

        </div>



        <!-- Error -->
        <div class="bg-white rounded-2xl shadow-xl p-5 border-l-4 border-red-500">

            <p class="text-sm font-semibold text-red-600 uppercase">
                Error
            </p>

            <p
                id="statError"
                class="text-3xl font-bold text-gray-800 mt-2"
            >
                {{ $statistics['error'] }}
            </p>

        </div>

    </div>



    <!-- Additional Statistics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">


        <!-- Debug -->
        <div class="bg-white rounded-xl shadow-lg p-4">

            <p class="text-xs font-semibold text-gray-500 uppercase">
                Debug
            </p>

            <p
                id="statDebug"
                class="text-2xl font-bold text-gray-800"
            >
                {{ $statistics['debug'] }}
            </p>

        </div>



        <!-- Critical -->
        <div class="bg-white rounded-xl shadow-lg p-4">

            <p class="text-xs font-semibold text-gray-500 uppercase">
                Critical
            </p>

            <p
                id="statCritical"
                class="text-2xl font-bold text-gray-800"
            >
                {{ $statistics['critical'] }}
            </p>

        </div>



        <!-- Alert -->
        <div class="bg-white rounded-xl shadow-lg p-4">

            <p class="text-xs font-semibold text-gray-500 uppercase">
                Alert
            </p>

            <p
                id="statAlert"
                class="text-2xl font-bold text-gray-800"
            >
                {{ $statistics['alert'] }}
            </p>

        </div>



        <!-- Emergency -->
        <div class="bg-white rounded-xl shadow-lg p-4">

            <p class="text-xs font-semibold text-gray-500 uppercase">
                Emergency
            </p>

            <p
                id="statEmergency"
                class="text-2xl font-bold text-gray-800"
            >
                {{ $statistics['emergency'] }}
            </p>

        </div>

    </div>



    <!-- Search and Filter -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">

        <h2 class="text-xl font-bold text-gray-800 mb-4">
            Search & Filter Logs
        </h2>


        <form
            method="GET"
            action="{{ route('admin.logs') }}"
            class="grid grid-cols-1 md:grid-cols-12 gap-4"
        >


            <!-- Search -->
            <div class="md:col-span-6">

                <label class="block text-sm font-semibold text-gray-600 mb-2">
                    Search Logs
                </label>

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search for payment, user, database, exception..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none"
                >

            </div>



            <!-- Level -->
            <div class="md:col-span-4">

                <label class="block text-sm font-semibold text-gray-600 mb-2">
                    Log Level
                </label>

                <select
                    name="level"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none"
                >

                    <option
                        value="ALL"
                        {{ $level === 'ALL' ? 'selected' : '' }}
                    >
                        All Levels
                    </option>


                    <option
                        value="DEBUG"
                        {{ $level === 'DEBUG' ? 'selected' : '' }}
                    >
                        DEBUG
                    </option>


                    <option
                        value="INFO"
                        {{ $level === 'INFO' ? 'selected' : '' }}
                    >
                        INFO
                    </option>


                    <option
                        value="NOTICE"
                        {{ $level === 'NOTICE' ? 'selected' : '' }}
                    >
                        NOTICE
                    </option>


                    <option
                        value="WARNING"
                        {{ $level === 'WARNING' ? 'selected' : '' }}
                    >
                        WARNING
                    </option>


                    <option
                        value="ERROR"
                        {{ $level === 'ERROR' ? 'selected' : '' }}
                    >
                        ERROR
                    </option>


                    <option
                        value="CRITICAL"
                        {{ $level === 'CRITICAL' ? 'selected' : '' }}
                    >
                        CRITICAL
                    </option>


                    <option
                        value="ALERT"
                        {{ $level === 'ALERT' ? 'selected' : '' }}
                    >
                        ALERT
                    </option>


                    <option
                        value="EMERGENCY"
                        {{ $level === 'EMERGENCY' ? 'selected' : '' }}
                    >
                        EMERGENCY
                    </option>

                </select>

            </div>



            <!-- Search Button -->
            <div class="md:col-span-2 flex items-end">

                <button
                    type="submit"
                    class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition"
                >
                    Search
                </button>

            </div>

        </form>



        <!-- Active filters -->

        @if($search !== '' || $level !== 'ALL')

            <div class="mt-4 flex flex-wrap items-center gap-2">

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


                <a
                    href="{{ route('admin.logs') }}"
                    class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm hover:bg-gray-300"
                >
                    Clear Filters
                </a>

            </div>

        @endif

    </div>



    <!-- Real-Time Log Monitoring -->
    <div class="bg-white rounded-2xl shadow-xl p-5 mb-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">


            <!-- Monitoring Information -->
            <div>

                <div class="flex items-center gap-3">

                    <span
                        id="liveIndicator"
                        class="w-3 h-3 rounded-full bg-gray-400 inline-block"
                    >
                    </span>


                    <h2 class="text-xl font-bold text-gray-800">
                        🔴 Real-Time Log Monitoring
                    </h2>


                    <span
                        id="liveStatus"
                        class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600"
                    >
                        Monitoring OFF
                    </span>

                </div>


                <p class="text-sm text-gray-500 mt-2">
                    Automatically checks for new Laravel logs every 5 seconds.
                </p>


                <p class="text-xs text-gray-400 mt-1">

                    Last updated:

                    <span id="lastUpdated">
                        Not started
                    </span>

                </p>

            </div>



            <!-- Auto Refresh Toggle -->
            <div class="flex items-center gap-3">

                <label class="flex items-center cursor-pointer">

                    <input
                        type="checkbox"
                        id="liveToggle"
                        class="sr-only"
                    >


                    <div
                        id="toggleTrack"
                        class="w-14 h-8 bg-gray-300 rounded-full relative transition"
                    >

                        <div
                            id="toggleCircle"
                            class="absolute left-1 top-1 w-6 h-6 bg-white rounded-full shadow transition"
                        >
                        </div>

                    </div>


                    <span class="ml-3 text-sm font-medium text-gray-700">
                        Auto Refresh
                    </span>

                </label>

            </div>

        </div>

    </div>



    <!-- Log List -->
    <div class="bg-black rounded-2xl shadow-2xl overflow-hidden">


        <!-- Log Header -->
        <div class="bg-gray-900 px-6 py-4 border-b border-gray-700">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">


                <div>

                    <h2 class="text-xl font-bold text-white">
                        Log Entries
                    </h2>


                    <p class="text-gray-400 text-sm mt-1">

                        Showing

                        <span id="logCount">
                            {{ $logs->count() }}
                        </span>

                        matching log entries

                    </p>

                </div>


                <div class="text-sm text-gray-400">
                    storage/logs/laravel.log
                </div>

            </div>

        </div>



        <!-- Logs -->
        <div
            id="logsContainer"
            class="p-6 max-h-[700px] overflow-y-auto"
        >

            @forelse($logs as $log)

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



                <div class="mb-3 pb-3 border-b border-gray-800">

                    <div class="flex flex-col md:flex-row md:items-start gap-3">


                        <!-- Level Badge -->
                        <div class="shrink-0">

                            <span
                                class="inline-block px-2 py-1 rounded text-xs font-bold {{ $badge }}"
                            >
                                {{ $levelName }}
                            </span>

                        </div>



                        <!-- Log Content -->
                        <div class="flex-1">

                            <div
                                class="{{ $color }} text-sm font-mono whitespace-pre-wrap break-words"
                            >
                                {{ $log }}
                            </div>

                        </div>

                    </div>

                </div>


            @empty

                <div
                    id="emptyState"
                    class="text-center py-16"
                >

                    <div class="text-5xl mb-4">
                        🔍
                    </div>


                    <h3 class="text-xl font-bold text-white">
                        No logs found
                    </h3>


                    <p class="text-gray-400 mt-2">
                        Try changing your search keyword or log level filter.
                    </p>


                    <a
                        href="{{ route('admin.logs') }}"
                        class="inline-block mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                    >
                        Clear Filters
                    </a>

                </div>

            @endforelse

        </div>

    </div>



    <!-- Footer -->
    <div class="text-center text-white/80 text-sm mt-6">

        Laravel Custom Log Monitoring Dashboard

    </div>

</div>



<script>

    /*
    |--------------------------------------------------------------------------
    | Real-Time Log Monitoring
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


    /*
    |--------------------------------------------------------------------------
    | Current Search & Level Filters
    |--------------------------------------------------------------------------
    */

    const searchValue =
        @json($search);

    const levelValue =
        @json($level);


    /*
    |--------------------------------------------------------------------------
    | Monitoring Timer
    |--------------------------------------------------------------------------
    */

    let liveInterval = null;



    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value)
    {
        const div = document.createElement('div');

        div.textContent = value;

        return div.innerHTML;
    }



    /*
    |--------------------------------------------------------------------------
    | Detect Log Level
    |--------------------------------------------------------------------------
    */

    function getLogLevel(log)
    {
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
                log.includes('.' + level + ':')
                ||
                log.includes('.' + level + ' ')
            ) {

                return level;

            }

        }


        return 'LOG';
    }



    /*
    |--------------------------------------------------------------------------
    | Get Badge Style
    |--------------------------------------------------------------------------
    */

    function getBadgeClass(level)
    {
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
    | Get Text Color
    |--------------------------------------------------------------------------
    */

    function getTextColor(level)
    {
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
    | Update Log List
    |--------------------------------------------------------------------------
    */

    function updateLogs(logs)
    {
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

                    <a
                        href="{{ route('admin.logs') }}"
                        class="inline-block mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                    >
                        Clear Filters
                    </a>

                </div>
            `;

            logCount.textContent = '0';

            return;
        }


        logsContainer.innerHTML = logs.map(function(log) {

            const level =
                getLogLevel(log);

            const badgeClass =
                getBadgeClass(level);

            const textColor =
                getTextColor(level);

            const safeLog =
                escapeHtml(log);


            return `
                <div class="mb-3 pb-3 border-b border-gray-800 new-log">

                    <div class="flex flex-col md:flex-row md:items-start gap-3">

                        <div class="shrink-0">

                            <span
                                class="inline-block px-2 py-1 rounded text-xs font-bold ${badgeClass}"
                            >
                                ${level}
                            </span>

                        </div>

                        <div class="flex-1">

                            <div
                                class="${textColor} text-sm font-mono whitespace-pre-wrap break-words"
                            >
                                ${safeLog}
                            </div>

                        </div>

                    </div>

                </div>
            `;

        }).join('');


        logCount.textContent =
            logs.length;
    }



    /*
    |--------------------------------------------------------------------------
    | Update Statistics
    |--------------------------------------------------------------------------
    */

    function updateStatistics(statistics)
    {
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
    | Fetch Live Logs
    |--------------------------------------------------------------------------
    */

    async function fetchLiveLogs()
    {
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


            const response =
                await fetch(
                    `{{ route('admin.logs.live') }}?${params.toString()}`,
                    {
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

                updateLogs(
                    data.logs
                );


                updateStatistics(
                    data.statistics
                );


                logCount.textContent =
                    data.total;


                lastUpdated.textContent =
                    data.updated_at;

            }

        }
        catch (error) {

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

    function startMonitoring()
    {
        if (liveInterval !== null) {
            return;
        }


        liveToggle.checked = true;


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


        /*
        | Fetch immediately
        */

        fetchLiveLogs();


        /*
        | Fetch every 5 seconds
        */

        liveInterval =
            setInterval(
                fetchLiveLogs,
                5000
            );
    }



    /*
    |--------------------------------------------------------------------------
    | Stop Monitoring
    |--------------------------------------------------------------------------
    */

    function stopMonitoring()
    {
        liveToggle.checked = false;


        if (liveInterval !== null) {

            clearInterval(
                liveInterval
            );

            liveInterval = null;
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
    }



    /*
    |--------------------------------------------------------------------------
    | Toggle Monitoring
    |--------------------------------------------------------------------------
    */

    liveToggle.addEventListener(
        'change',
        function () {

            if (this.checked) {

                startMonitoring();

            } else {

                stopMonitoring();

            }

        }
    );



    /*
    |--------------------------------------------------------------------------
    | Start Monitoring Automatically
    |--------------------------------------------------------------------------
    */

    startMonitoring();

</script>


</body>

</html>