@extends('layouts.admin')

@section('admin-content')
    @if (session('success'))
        <div role="alert" class="alert alert-success w-full mb-3">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div role="alert" class="alert alert-error w-full mb-3">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-row gap-3 w-full">
        {{-- candidate ranking --}}
        <div class="basis-full bg-base-100 rounded-xl p-3 h-80 flex flex-col items-center justify-center gap-8">
            <!-- Countdown display -->
            <div>
                <h3 id="countdown"
                    class="text-8xl font-mono font-semibold text-center underline decoration-2 decoration-indigo-400 decoration-double">
                    00:00:00
                </h3>
            </div>



            <div class="flex flex-col items-center justify-center gap-4">
                <div id="timeInputContainer" class="space-x-4 flex flex-row gap-2">
                    <input type="number" class="input min-w-24" name="" id="hourInput" placeholder="Hour"
                        min="0" max="23">

                    <input type="number" class="input min-w-24" name="" id="minInput" placeholder="Minute"
                        min="0" max="59">

                    <input type="number" class="input min-w-24" name="" id="secInput" placeholder="Second"
                        min="0" max="59">
                </div>

                <div class="space-x-4">
                    <button id="getTimerBtn" class="btn btn-primary btn-lg" type="button">Get</button>
                    <button id="startStopButton" class="btn btn-primary btn-lg" type="button"
                        onclick="setCurrentTimer()">Set Timer</button>
                    <button class="btn btn-secondary btn-soft btn-lg" type="button"
                        onclick="resetCountdown()">Reset</button>
                </div>
            </div>
        </div>

        <div class="basis-full bg-base-100 rounded-xl p-3 h-80 overflow-y-auto">
            <table class="table table-zebra">
                <!-- head -->
                <thead class=" text-center text-neutral-200">
                    <tr>
                        <th class="bg-slate-700/50 rounded-tl-xl">Ranking</th>
                        <th class="bg-slate-700/50">Name</th>
                        <th class="bg-slate-700/50">Points</th>
                        <th class="bg-slate-700/50 rounded-tr-xl">Group</th>
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
                            <td>
                                <p class="badge badge-primary badge-soft">{{ $candidate->group }}</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col bg-base-100 rounded-xl p-3 mt-3">
        <div class="flex flex-col">
            @php
                $regOpen = DB::table('options')->where('option_name', 'registration_open')->value('option_value');
            @endphp
            <div class="flex items-center gap-3 mb-2 p-2 bg-base-300 rounded-xl">
                <span class="text-sm">Registration:
                    @if ($regOpen === '1')
                        <span class="badge btn btn-sm btn-success">Open</span>
                    @else
                        <span class="badge btn btn-sm btn-ghost">Closed</span>
                    @endif
                </span>
                <form method="POST" action="{{ url('/admin/toggle-registration') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $regOpen === '1' ? 'btn-error' : 'btn-success' }}">
                        {{ $regOpen === '1' ? 'Close' : 'Open' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener("DOMContentLoaded", () => {
        let endTime = null;
        let tickInterval = null;
        let pollInterval = null;

        const countdown = document.getElementById("countdown");
        const hourInput = document.getElementById("hourInput");
        const minuteInput = document.getElementById("minInput");
        const secondInput = document.getElementById("secInput");
        const getTimerBtn = document.getElementById("getTimerBtn");

        function formatHMS(ms) {
            const totalSeconds = Math.floor(ms / 1000);
            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = totalSeconds % 60;

            return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
        }

        async function checkServerTimer() {
            try {
                const res = await fetch('/admin/timer/get_timer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({}),
                });
                const data = await res.json();

                if (!data.started) {
                    if (tickInterval) {
                        clearInterval(tickInterval);
                        tickInterval = null;
                    }
                    endTime = null;
                    countdown.innerText = "00:00:00";
                    return;
                }

                endTime = data.endTimeInMils;

                if (data.isTime) {
                    countdown.innerText = "!00:00:00!";
                    if (tickInterval) {
                        clearInterval(tickInterval);
                        tickInterval = null;
                    }
                    return;
                }

                if (!tickInterval) {
                    startCountdownTick();
                }
            } catch {}
        }

        function startCountdownTick() {
            if (tickInterval) clearInterval(tickInterval);

            tickInterval = setInterval(() => {
                if (!endTime) return;

                const nowMs = Date.now();
                const endTimeMs = endTime * 1000;
                const remaining = Math.max(0, endTimeMs - nowMs);

                if (remaining <= 0) {
                    countdown.innerText = "!00:00:00!";
                    clearInterval(tickInterval);
                    tickInterval = null;
                    endTime = null;
                    return;
                }

                countdown.innerText = formatHMS(remaining);
            }, 1000);
        }

        function setCurrentTimer() {
            const hours = Number(hourInput.value) || 0;
            const minutes = Number(minuteInput.value) || 0;
            const seconds = Number(secondInput.value) || 0;

            if (hours === 0 && minutes === 0 && seconds === 0) return;

            fetch('/admin/timer/set_timer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    setHours: hours,
                    setMinutes: minutes,
                    setSeconds: seconds
                }),
            }).then(() => checkServerTimer());
        }

        function resetCountdown() {
            if (tickInterval) {
                clearInterval(tickInterval);
                tickInterval = null;
            }
            hourInput.value = '';
            minuteInput.value = '';
            secondInput.value = '';
            countdown.innerText = "00:00:00";
            endTime = null;

            fetch('/admin/countdown/reset', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            });
        }

        getTimerBtn.addEventListener('click', checkServerTimer);

        pollInterval = setInterval(checkServerTimer, 8000);

        window.setCurrentTimer = setCurrentTimer;
        window.resetCountdown = resetCountdown;
    });
</script>
