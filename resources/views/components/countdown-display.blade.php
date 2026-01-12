<div id="countdownDisplay"
    class="w-56 min-h-14 fixed bottom-4 right-4 rounded-2xl p-4 bg-base-200 border-2 border-primary shadow-sm">
    <h2 id="countdown" class="text-3xl text-center font-mono">00:00:00</h2>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        let rafId = null;
        let endTime = null;
        let isPaused = false;

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

        // auto-sync on page load
        fetch('/countdown')
            .then(res => res.json())
            .then(data => {
                if (data.endTime) {
                    endTime = data.endTime;
                    countdownDisplay.hidden = false;

                    tick();
                } else if (data.remainingMs) {
                    const remainingTime = convertNumberToTimeString(data.remainingMs - 1000)
                    isPaused = !!remainingTime;
                    countdown.innerText = remainingTime;
                    countdownDisplay.hidden = false;
                    tick();
                }
            });
    });
</script>
