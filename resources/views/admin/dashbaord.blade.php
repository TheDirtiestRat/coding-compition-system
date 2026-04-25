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
                        onclick="setCurrentTimer()">Start</button>
                    <button class="btn btn-secondary btn-soft btn-lg" type="button"
                        onclick="setCurrentTimer()">Reset</button>
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
@endsection

<script>
    document.addEventListener("DOMContentLoaded", () => {
        let rafId = null;
        let endTime = null;
        let isTime = false;
        let curTime = null;
        let countdownTimer = null;

        const getTimerBtn = document.getElementById("getTimerBtn");
        const countdown = document.getElementById("countdown");
        const startStopButton = document.getElementById("startStopButton");

        const timeInputContainer = document.getElementById("timeInputContainer");
        const hourInput = document.getElementById("hourInput");
        const minuteInput = document.getElementById("minInput");
        const secondInput = document.getElementById("secInput");

        // get the current time
        function getCurrentTime() {
            return fetch('/admin/timer/get_timer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({}),
                })
                .then(res => res.json())
                .then(data => {
                    curTime = data;
                    return data;
                });
        }

        // gets the server timer
        setInterval(async () => {
            startTimer();
        }, 6000);

        async function startTimer() {
            const timer = await getCurrentTime();
            countdownTimer = timer['curTimeInMils'];
            endTime = timer['endTimeInMils'];
            isTime = timer['isTime'];

            // const remaining = Math.max(0, endTime - countdownTimer) * 1000;
            console.log("calling the server for time?");

            if (isTime == true) {
                // countdown.innerText = convertNumberToTimeString(remaining);
                countdown.innerText = "!00:00:00!";
                console.log("the server is now time!");
            }
        }

        // update the clients timer
        setInterval(() => {
            if (isTime) {
                return;
            }
            // Date.now() is the cleanest way to get the current timestamp in ms
            const nowMs = Date.now();
            const endTimeMs = endTime * 1000;

            // Calculate the difference (remaining time in milliseconds)
            const remaining = Math.max(0, endTimeMs - nowMs);

            // Update the display
            countdown.innerText = formatHMS(remaining);

        }, 1000);

        /**
         * Formats milliseconds into a clean H:M:S string
         */
        function formatHMS(ms) {
            const totalSeconds = Math.floor(ms / 1000);

            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = totalSeconds % 60;

            // Returns "HH:MM:SS"
            return [h, m, s]
                .map(v => String(v).padStart(2, '0'))
                .join(':');
        }



        function hideInputs(hidden) {
            timeInputContainer.hidden = hidden;
        }

        function resetCountdown() {
            setCurrentTimer();
        }

        function setCurrentTimer() {
            const hours = Number(hourInput.value) || 0;
            const minutes = Number(minuteInput.value) || 0;
            const seconds = Number(secondInput.value) || 0;

            fetch('/admin/timer/set_timer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        // values that we can use
                        setHours: hours,
                        setMinutes: minutes,
                        setSeconds: seconds
                    }),
                })
                .then(res => res.json())
                .then(data => {
                    console.log(data);
                    startTimer();

                });
        }

        // function startOrPauseCountdown() {
        //     if (startStopButton.innerText === "Pause") {
        //         pauseCountdown();
        //     } else {
        //         startCountdown();
        //     }
        // }

        // function startCountdown() {
        //     const durationMs =
        //         hours * 3600000 +
        //         minutes * 60000 +
        //         seconds * 1000;

        //     if (!durationMs) return;

        //     fetch('/admin/countdown/start', {
        //             method: 'POST',
        //             headers: {
        //                 'Content-Type': 'application/json',
        //                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
        //             },
        //             body: JSON.stringify({
        //                 duration_ms: durationMs,
        //                 // values that we can use
        //                 setHours: hours,
        //                 setMinutes: minutes,
        //                 setSeconds: seconds
        //             }),
        //         })
        //         .then(res => res.json())
        //         .then(data => {
        //             endTime = data.endTime;
        //             hideInputs(true);
        //             startStopButton.innerText = "Pause";
        //             // tick();
        //         });
        // }

        // function convertNumberToTimeString(time) {
        //     const totalSeconds = Math.floor(time / 1000);
        //     const hours = Math.floor(totalSeconds / 3600) || 0;
        //     const minutes = Math.floor((totalSeconds % 3600) / 60) || 0;
        //     const seconds = totalSeconds % 60 || 0;

        //     return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
        // }

        // setInterval(() => {
        //     const now = new Date();

        //     // Get the units directly
        //     const hours = now.getHours();
        //     const minutes = now.getMinutes();
        //     const seconds = now.getSeconds();
        //     const ms = now.getMilliseconds(); // Optional: for extra precision

        //     const totalMilliseconds = ((hours * 3600) + (minutes * 60) + seconds) * 1000 + ms;



        //     // const remaining = Math.max(0, endTime - countdownTimer) * 1000;
        //     const remaining = Math.max(0, endTime - totalMilliseconds) * 1000;
        //     countdown.innerText = convertNumberToTimeString(remaining);
        //     console.log(countdown.innerText);

        // }, 1000);

        // function pauseCountdown() {
        //     fetch('/admin/countdown/pause', {
        //             method: 'POST',
        //             headers: {
        //                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
        //             },
        //         })
        //         .then(() => {
        //             cancelAnimationFrame(rafId);
        //             endTime = null;
        //             startStopButton.innerText = "Resume"
        //         });
        // }

        // function resetCountdown() {
        //     setCurrentTimer();
        //     // fetch('/admin/countdown/reset', {
        //     //     method: 'POST',
        //     //     headers: {
        //     //         'X-CSRF-TOKEN': '{{ csrf_token() }}',
        //     //     }
        //     // }).then(() => {
        //     //     cancelAnimationFrame(rafId);
        //     //     endTime = null;

        //     //     // Reset inputs
        //     //     countdown.innerText = "00:00:00";
        //     //     startStopButton.innerText = "Start";
        //     //     hourInput.value = "";
        //     //     minuteInput.value = "";
        //     //     secondInput.value = "";
        //     //     hideInputs(false);
        //     // });
        // }

        // function tick() {
        //     if (!endTime) return;
        //     const remaining = Math.max(0, endTime - Date.now());
        //     countdown.innerText = convertNumberToTimeString(remaining);

        //     if (remaining > 0) {
        //         rafId = requestAnimationFrame(tick);
        //     } else {
        //         // In server we should send an event so that all clients know that
        //         // the game has ended
        //         resetCountdown();
        //     }
        // }


        // auto-sync on page load
        // fetch('/countdown')
        //     .then(res => res.json())
        //     .then(data => {
        //         if (data.endTime) {
        //             endTime = data.endTime;
        //             hideInputs(true);
        //             startStopButton.innerText = "Pause";
        //             tick();
        //         } else if (data.remainingMs) {
        //             const remainingTime = convertNumberToTimeString(data.remainingMs)
        //             countdown.innerText = remainingTime;
        //             isPaused = !!remainingTime;
        //             hideInputs(true);
        //             startStopButton.innerText = "Resume";
        //             tick();
        //         }
        //     });
        // window.startOrPauseCountdown = startOrPauseCountdown;


        window.setCurrentTimer = setCurrentTimer;
        window.resetCountdown = resetCountdown;
    });
</script>
