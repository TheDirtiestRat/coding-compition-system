@extends('layouts.admin')

@section('admin-content')
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
                    <input type="number" class="input min-w-24" name="" id="hourInput" placeholder="Hour" min="0" max="23">

                    <input type="number" class="input min-w-24" name="" id="minInput" placeholder="Minute" min="0" max="59">

                    <input type="number" class="input min-w-24" name="" id="secInput" placeholder="Second" min="0" max="59">
                </div>

                <div class="space-x-4">
                    <button id="startStopButton" class="btn btn-primary btn-lg" type="button"
                        onclick="startOrPauseCountdown()">Start</button>
                    <button class="btn btn-secondary btn-soft btn-lg" type="button"
                        onclick="resetCountdown()">Reset</button>
                </div>
            </div>
        </div>

        <div class="basis-full bg-base-100 rounded-xl p-3 h-80 overflow-y-scroll">
            <table class="table table-zebra">
                <!-- head -->
                <thead class="bg-slate-700/50 text-center text-neutral-200">
                    <tr>
                        <th>Ranking</th>
                        <th>Name</th>
                        <th>Points</th>
                        <th>Group</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($candidates as $candidate)
                        <tr class="text-center">
                            @if ($candidate->total_points == 0)
                                <th class="text-neutral-400 font-light">N/A</th>
                            @else
                                <th>{{ $candidate->rank }}</th>
                            @endif
                            <td>{{ $candidate->fullname }}</td>
                            <td>{{ $candidate->total_points }}</td>
                            <td>
                                <p class="badge badge-primary badge-soft">{{ $candidate->group }}</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

<script>
    document.addEventListener("DOMContentLoaded", () => {
        let rafId = null;
        let endTime = null;
        let isPaused = false;

        const countdown = document.getElementById("countdown");
        const startStopButton = document.getElementById("startStopButton");

        const timeInputContainer = document.getElementById("timeInputContainer");
        const hourInput = document.getElementById("hourInput");
        const minuteInput = document.getElementById("minInput");
        const secondInput = document.getElementById("secInput");

        function startOrPauseCountdown() {
            if (startStopButton.innerText === "Pause") {
                pauseCountdown();
            } else {
                startCountdown();
            }
        }

        function startCountdown() {
            const hours = Number(hourInput.value) || 0;
            const minutes = Number(minuteInput.value) || 0;
            const seconds = Number(secondInput.value) || 0;

            const durationMs =
                hours * 3600000 +
                minutes * 60000 +
                seconds * 1000;

            if (!durationMs) return;

            fetch('/admin/countdown/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ duration_ms: durationMs }),
            })
                .then(res => res.json())
                .then(data => {
                    endTime = data.endTime;
                    hideInputs(true);
                    startStopButton.innerText = "Pause";
                    tick();
                });
        }

        function pauseCountdown() {
            fetch('/admin/countdown/pause', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            })
                .then(() => {
                    cancelAnimationFrame(rafId);
                    endTime = null;
                    startStopButton.innerText = "Resume"
                });
        }

        function resetCountdown() {
            fetch('/admin/countdown/reset', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            }).then(() => {
                cancelAnimationFrame(rafId);
                endTime = null;

                // Reset inputs
                countdown.innerText = "00:00:00";
                startStopButton.innerText = "Start";
                hourInput.value = "";
                minuteInput.value = "";
                secondInput.value = "";
                hideInputs(false);
            });
        }

        function tick() {
            if (!endTime) return;

            const remaining = Math.max(0, endTime - Date.now());

            countdown.innerText = convertNumberToTimeString(remaining);

            if (remaining > 0) {
                rafId = requestAnimationFrame(tick);
            } else {
                // In server we should send an event so that all clients know that
                // the game has ended
                resetCountdown();
            }
        }

        function hideInputs(hidden) {
            timeInputContainer.hidden = hidden;
        }

        function convertNumberToTimeString(time) {
            const totalSeconds = Math.floor(time / 1000);
            const hours = Math.floor(totalSeconds / 3600) || 0;
            const minutes = Math.floor((totalSeconds % 3600) / 60) || 0;
            const seconds = totalSeconds % 60 || 0;

            return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
        }

        // auto-sync on page load
        fetch('/countdown')
            .then(res => res.json())
            .then(data => {
                if (data.endTime) {
                    endTime = data.endTime;
                    hideInputs(true);
                    startStopButton.innerText = "Pause";
                    tick();
                } else if (data.remainingMs) {
                    const remainingTime = convertNumberToTimeString(data.remainingMs)
                    countdown.innerText = remainingTime;
                    isPaused = !!remainingTime;
                    hideInputs(true);
                    startStopButton.innerText = "Resume";
                    tick();
                }
            });

        window.startOrPauseCountdown = startOrPauseCountdown;
        window.resetCountdown = resetCountdown;
    });
</script>
