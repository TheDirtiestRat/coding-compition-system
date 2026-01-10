@extends('layouts.admin')

@section('admin-content')
    <div class="flex flex-row gap-3 w-full">
        {{-- candidate ranking --}}
        <div class="basis-full bg-base-100 rounded-xl p-3 h-80 flex flex-col items-center justify-center gap-8">
            <!-- Countdown display -->
            <div>
                <h3 id="countdown"
                    class="text-9xl font-mono font-semibold text-center underline decoration-2 decoration-indigo-400 decoration-double">
                    00:00
                </h3>
            </div>

            <div class="flex flex-col items-center justify-center gap-4">
                <div class="space-x-4 flex flex-row gap-2">
                    <input type="number" class="input min-w-24" name="" id="hourInput" placeholder="Hour" min="0" max="23">

                    <input type="number" class="input min-w-24" name="" id="minInput" placeholder="Minute" min="0" max="59">

                    <input type="number" class="input min-w-24" name="" id="secInput" placeholder="Second" min="0" max="59">
                </div>

                <div class="space-x-4">
                    <button id="startStopButton" class="btn btn-primary btn-lg" type="button"
                        onclick="startCountdown()">Start</button>
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
        let intervalId = null;
        let remainingTime = 0;
        let isRunning = false;

        const countdown = document.getElementById("countdown");
        const startStopButton = document.getElementById("startStopButton");

        const hourInput = document.getElementById("hourInput");
        const minuteInput = document.getElementById("minInput");
        const secondInput = document.getElementById("secInput");

        function startCountdown() {
            if (isRunning) {
                stopCountdown();
                return;
            }

            // If starting fresh, compute time from inputs
            if (remainingTime <= 0) {
                const hours = Number(hourInput.value) || 0;
                const minutes = Number(minuteInput.value) || 0;
                const seconds = Number(secondInput.value) || 0;

                remainingTime =
                    hours * 60 * 60 * 1000 +
                    minutes * 60 * 1000 +
                    seconds * 1000;
            }

            if (remainingTime <= 0) return;

            isRunning = true;
            toggleInputs(true);
            startStopButton.innerText = "Stop";

            intervalId = setInterval(() => {
                remainingTime -= 1000;

                if (remainingTime <= 0) {
                    resetCountdown();
                    return;
                }

                countdown.innerText = convertNumberToTimeString(remainingTime);
            }, 1000);
        }

        function stopCountdown() {
            clearInterval(intervalId);
            intervalId = null;
            isRunning = false;
            startStopButton.innerText = "Start";
            toggleInputs(false);
        }

        function resetCountdown() {
            clearInterval(intervalId);
            intervalId = null;
            isRunning = false;
            remainingTime = 0;
            countdown.innerText = "00:00";
            startStopButton.innerText = "Start";
            toggleInputs(false);
        }

        function toggleInputs(disabled) {
            hourInput.disabled = disabled;
            minuteInput.disabled = disabled;
            secondInput.disabled = disabled;
        }

        function convertNumberToTimeString(time) {
            if (typeof time !== "number" || time < 0) return "00:00";

            const hours = Math.floor(time / (1000 * 60 * 60));
            const minutes = Math.floor((time % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((time % (1000 * 60)) / 1000);

            if (hours > 0) {
                return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(
                    2,
                    "0"
                )}:${String(seconds).padStart(2, "0")}`;
            }

            return `${String(minutes).padStart(2, "0")}:${String(seconds).padStart(
                2,
                "0"
            )}`;
        }

        // Expose functions globally for onclick=""
        window.startCountdown = startCountdown;
        window.resetCountdown = resetCountdown;
    })
</script>
