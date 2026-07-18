<div id="countdownDisplay"
    class="w-56 min-h-14 fixed bottom-4 right-4 rounded-2xl p-4 bg-base-200 border-2 border-primary shadow-sm hidden">
    <h2 id="countdown" class="text-3xl text-center font-mono">00:00:00</h2>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        let endTime = null;
        let tickInterval = null;
        let pollInterval = null;

        const countdownDisplay = document.getElementById("countdownDisplay");
        const countdown = document.getElementById("countdown");

        async function fetchTimerState() {
            try {
                const res = await fetch('/timer/get_timer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({}),
                });
                return await res.json();
            } catch {
                return null;
            }
        }

        async function syncTimer() {
            const timer = await fetchTimerState();
            if (!timer) return;

            const started = timer['started'] ?? false;
            const isTime = timer['isTime'] ?? false;

            if (!started) {
                countdownDisplay.classList.add('hidden');
                if (tickInterval) {
                    clearInterval(tickInterval);
                    tickInterval = null;
                }
                return;
            }

            countdownDisplay.classList.remove('hidden');

            if (isTime) {
                countdown.innerText = "!00:00:00!";
                if (tickInterval) {
                    clearInterval(tickInterval);
                    tickInterval = null;
                }
                setTimeout(() => {
                    window.location.href = "/competition-ended";
                }, 500);
                return;
            }

            endTime = timer['endTimeInMils'];
            if (!endTime) return;

            if (!tickInterval) {
                tickInterval = setInterval(() => {
                    const nowMs = Date.now();
                    const remaining = Math.max(0, (endTime * 1000) - nowMs);
                    countdown.innerText = formatHMS(remaining);
                }, 1000);
            }
        }

        function formatHMS(ms) {
            const totalSeconds = Math.floor(ms / 1000);
            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = totalSeconds % 60;
            return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
        }

        pollInterval = setInterval(syncTimer, 8000);
        syncTimer();
    });
</script>
