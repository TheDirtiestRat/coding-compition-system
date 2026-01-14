<div id="countdownDisplay"
    class="w-56 min-h-14 fixed bottom-4 right-4 rounded-2xl p-4 bg-base-200 border-2 border-primary shadow-sm">
    <h2 id="countdown" class="text-3xl text-center font-mono">00:00:00</h2>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        let rafId = null;
        let endTime = null;

        const countdownDisplay = document.getElementById("countdownDisplay");
        countdownDisplay.hidden = true; // Hide countdown by default

        const countdown = document.getElementById("countdown");

        function tick() {
            if (!endTime) return;

            const remaining = Math.max(0, endTime - Date.now());

            countdown.innerText = convertNumberToTimeString(remaining);

            if (remaining > 0) {
                rafId = requestAnimationFrame(tick);
            } else {
                countdownDisplay.hidden = true;
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
            const remainingTimeInTimeStr
                = convertNumberToTimeString(remainingTimeInMs)
            endTime = null;
            countdown.innerText = remainingTimeInTimeStr;
            countdownDisplay.hidden = false;
            tick();
        }

        function startCountdown(endTimeInMs) {
            endTime = endTimeInMs;
            countdownDisplay.hidden = false;

            tick();
        }

        function resetCountdown() {
            endTime = null;
            countdownDisplay.hidden = true;

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

    });
</script>
