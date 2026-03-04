<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Countdown</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="w-screen h-screen flex justify-center items-center">
    <div class="flex flex-col">
        <p class="p-0 text-center">@include('components.title')</p>
        <p id="countdown" class="text-[200px] font-semibold text-center p-0 leading-tight">
            00:00:00
        </p>
        <p class="p-0 text-center">Kompetition Ends in ...</p>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let rafId = null;
            let endTime = null;

            const countdown = document.getElementById("countdown");

            function tick() {
                if (!endTime) return;

                const remaining = Math.max(0, endTime - Date.now());

                countdown.innerText = convertNumberToTimeString(remaining);

                if (remaining > 0) {
                    rafId = requestAnimationFrame(tick);
                }
            }

            function convertNumberToTimeString(time) {
                const totalSeconds = Math.floor(time / 1000);
                const hours = Math.floor(totalSeconds / 3600) || 0;
                const minutes = Math.floor((totalSeconds % 3600) / 60) || 0;
                const seconds = totalSeconds % 60 || 0;

                return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
            }

            function displayPaused(remainingTimeInMs) {
                const remainingTimeInTimeStr = convertNumberToTimeString(remainingTimeInMs)
                endTime = null;
                countdown.innerText = remainingTimeInTimeStr;
                tick();
            }

            function startCountdown(endTimeInMs) {
                endTime = endTimeInMs;
                tick();
            }

            function resetCountdown() {
                endTime = null;
                tick();
            }

            // auto-sync on page load
            fetch('/countdown')
                .then(res => res.json())
                .then(data => {
                    if (data.endTime) {
                        startCountdown(data.endTime)
                    } else if (data.remainingMs) {
                        displayPaused(data.remainingMs)
                    }
                });

            Echo.channel('countdown')
                .listen('CountdownEvent', (e) => {
                    switch (e.type) {
                        case 'paused':
                            displayPaused(e.data.remainingMs)
                            break;
                        case 'start':
                            startCountdown(e.data.endTime)
                            break;
                        case 'resumed':
                            startCountdown(e.data.endTime)
                            break;
                        case 'reset':
                            resetCountdown()
                            break;
                    }
                });

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

            setInterval(async () => {
                const timer = await getCurrentTime();
                let countdownTimer = timer['curTimeInMils'];
                let endTime = timer['endTimeInMils'];

                // console.log({
                //     "curr" : countdownTimer,
                //     "end" : endTime,
                //     "time?" : timer['isTime'],
                // });

                const remaining = Math.max(0, endTime - countdownTimer) * 1000;
                // console.log(remaining);

                if (timer['isTime'] == false) {
                    countdown.innerText = convertNumberToTimeString(remaining);
                } else {
                    countdown.innerText = "Time!!"
                }
            }, 1000);
        });
    </script>
</body>

</html>
