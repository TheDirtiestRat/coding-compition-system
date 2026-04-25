<div id="countdownDisplay"
    class="w-56 min-h-14 fixed bottom-4 right-4 rounded-2xl p-4 bg-base-200 border-2 border-primary shadow-sm">
    <h2 id="countdown" class="text-3xl text-center font-mono">00:00:00</h2>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        let rafId = null;
        let endTime = null;
        let isTime = false;
        let curTime = null;
        let countdownTimer = null;

        const countdownDisplay = document.getElementById("countdownDisplay");
        // countdownDisplay.hidden = true; // Hide countdown by default

        const countdown = document.getElementById("countdown");

        // get the current time
        function getCurrentTime() {
            return fetch('/timer/get_timer', {
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

            startTimer();
        }, 6000);

        async function startTimer() {
            const timer = await getCurrentTime();
            countdownTimer = timer['curTimeInMils'];
            endTime = timer['endTimeInMils'];
            isTime = timer['isTime'];

            // const remaining = Math.max(0, endTime - countdownTimer) * 1000;
            // console.log("calling the server for time?");

            // if (isTime == true) {
            //     // countdown.innerText = convertNumberToTimeString(remaining);
            //     countdown.innerText = "!00:00:00!";
            //     console.log("the server is now time!");
            //     countdownDisplay.hidden = true;

            // }
        }

        // update the clients timer
        setInterval(() => {
            if (isTime) {
                countdownDisplay.hidden = true;
                competitionComplete();
                return;
            }
            // Date.now() is the cleanest way to get the current timestamp in ms
            const nowMs = Date.now();
            const endTimeMs = endTime * 1000;

            // Calculate the difference (remaining time in milliseconds)
            const remaining = Math.max(0, endTimeMs - nowMs);

            // Update the display
            countdown.innerText = formatHMS(remaining);
            countdownDisplay.hidden = false;
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

        function competitionComplete() {
            setTimeout(() => {
                window.location.href = "/competition-ended";
            }, 200);
        }

        startTimer();

        // function tick() {
        //     if (!endTime) return;

        //     const remaining = Math.max(0, endTime - Date.now());

        //     countdown.innerText = convertNumberToTimeString(remaining);

        //     if (remaining > 0) {
        //         rafId = requestAnimationFrame(tick);
        //     } else {
        //         countdownDisplay.hidden = true;
        //     }
        // }

        // function convertNumberToTimeString(time) {
        //     const totalSeconds = Math.floor(time / 1000);
        //     const hours = Math.floor(totalSeconds / 3600) || 0;
        //     const minutes = Math.floor((totalSeconds % 3600) / 60) || 0;
        //     const seconds = totalSeconds % 60 || 0;

        //     return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
        // }

        // const interID = setInterval(async () => {
        //     const timer = await getCurrentTime();
        //     let countdownTimer = timer['curTimeInMils'];
        //     let endTime = timer['endTimeInMils'];

        //     const remaining = Math.max(0, endTime - countdownTimer) * 1000;

        //     if (timer['isTime'] == false) {
        //         // countdown.innerText = convertNumberToTimeString(remaining);
        //         time_remaining = convertNumberToTimeString(remaining);
        //         countdownDisplay.hidden = false;
        //     } else {
        //         countdown.innerText = "Time!!";
        //         countdownDisplay.hidden = true;
        //         if (window.location.href != ("http://" + (window.location.hostname + ':' + window
        //                 .location.port + '/code-problems'))) {
        //             window.location.href = "/competition-ended";
        //         }
        //     }
        // }, 6000);

        // // perform tick timer in client side
        // setInterval((countdown.innerText = time_remaining) => {}, 1000);

        // function displayPaused(remainingTimeInMs) {
        //     const remainingTimeInTimeStr = convertNumberToTimeString(remainingTimeInMs)
        //     endTime = null;
        //     countdown.innerText = remainingTimeInTimeStr;
        //     countdownDisplay.hidden = false;
        //     tick();
        // }

        // function startCountdown(endTimeInMs) {
        //     endTime = endTimeInMs;
        //     countdownDisplay.hidden = false;

        //     tick();
        // }

        // function resetCountdown() {
        //     endTime = null;
        //     countdownDisplay.hidden = true;

        //     // redirects to the end page
        //     window.location.href = "/competition-ended";
        //     alert("timer done!");

        //     tick();
        // }

        // // auto-sync on page load
        // fetch('/countdown')
        //     .then(res => res.json())
        //     .then(data => {
        //         if (data.endTime) {
        //             startCountdown(data.endTime)
        //         } else if (data.remainingMs) {
        //             displayPaused(data.remainingMs)
        //         }
        //     });

        // Echo.channel('countdown')
        //     .listen('CountdownEvent', (e) => {
        //         switch (e.type) {
        //             case 'paused':
        //                 displayPaused(e.data.remainingMs)
        //                 break;
        //             case 'start':
        //                 startCountdown(e.data.endTime)
        //                 break;
        //             case 'resumed':
        //                 startCountdown(e.data.endTime)
        //                 break;
        //             case 'reset':
        //                 resetCountdown()
        //                 break;
        //         }
        //     });

    });
</script>
