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
