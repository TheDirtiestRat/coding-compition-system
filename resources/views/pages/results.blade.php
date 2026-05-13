<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Results</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="w-screen h-screen flex justify-center">
    <div class="flex flex-row gap-3 p-3 basis-full h-dvh">
        {{-- top ranks --}}
        <div class="basis-full grid grid-cols-1 grid-rows-3 gap-3">
            @php
                $rank = 0;
            @endphp
            @foreach ($candidates->take(3) as $candidate)
                @php
                    $rank += 1;
                @endphp
                <div class="bg-base-300 rounded-xl p-3 w-full grid grid-cols-2 grid-rows-2">
                    <span class="text-5xl col-start-1 row-span-full">
                        @if ($candidate->total_score == 0)
                            N/A
                        @else
                            {{ $rank }}
                        @endif
                    </span>
                    <span class="text-2xl col-start-2">{{ $candidate->fullname }}</span>
                    <span class="text-5xl col-span-2 text-center">{{ $candidate->total_score }}pts</span>
                </div>
            @endforeach

        </div>
        {{-- all ranks --}}
        <div class="basis-full bg-base-100 rounded-xl overflow-y-auto">
            <table class="table table-zebra">
                <!-- head -->
                <thead class=" text-center text-neutral-200">
                    <tr>
                        <th class="bg-slate-700/50 rounded-tl-xl">Ranking</th>
                        <th class="bg-slate-700/50">Name</th>
                        <th class="bg-slate-700/50 rounded-tr-xl">Points</th>
                        {{-- <th class="bg-slate-700/50 rounded-tr-xl">Group</th> --}}
                    </tr>
                </thead>
                <tbody>
                    {{-- genereates the rank --}}
                    @php
                        $rank = 0;
                    @endphp
                    @foreach ($candidates as $candidate)
                        @php
                            $rank += 1;
                        @endphp
                        <tr class="text-center">
                            @if ($candidate->total_score == 0)
                                <th class="text-neutral-400 font-light">N/A</th>
                            @else
                                <th>{{ $rank }}</th>
                            @endif
                            <td>{{ $candidate->fullname }}</td>
                            <td>{{ $candidate->total_score }}</td>
                            {{-- <td>
                            <p class="badge badge-primary badge-soft">{{ $candidate->group }}</p>
                        </td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
