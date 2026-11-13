<script>
    document.addEventListener('DOMContentLoaded', () => {
        const countdown = document.getElementById('countdown');
        const floatingDisplay = document.getElementById('countdownDisplay');
        const getButton = document.getElementById('getTimerBtn');
        let timer = null;
        let syncedAt = 0;

        function renderTimer() {
            if (!timer) return;
            floatingDisplay?.classList.toggle('hidden', !timer.started && !timer.paused);

            let remaining = timer.remainingMs || 0;
            if (timer.started && timer.endTime) {
                remaining = Math.max(0, timer.endTime - timer.serverTime - (performance.now() - syncedAt));
            }

            const seconds = Math.ceil(remaining / 1000);
            countdown.textContent = timer.isTime ? '!00:00:00!' : [
                Math.floor(seconds / 3600),
                Math.floor((seconds % 3600) / 60),
                seconds % 60,
            ].map(value => String(value).padStart(2, '0')).join(':');

            if (floatingDisplay && timer.isTime) {
                window.location.href = '/competition-ended';
            }
        }

        async function syncTimer() {
            try {
                const response = await fetch('/countdown', { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Failed to load the timer.');
                timer = await response.json();
                syncedAt = performance.now();
                renderTimer();
            } catch (error) {
                countdown.title = error.message;
            }
        }

        async function updateTimer(url, values = {}) {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(values),
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || result.error || 'Failed to update the timer.');
                await syncTimer();
            } catch (error) {
                alert(error.message);
            }
        }

        getButton?.addEventListener('click', syncTimer);
        document.getElementById('setTimerBtn')?.addEventListener('click', () => {
            updateTimer('/admin/timer/set_timer', {
                setHours: Number(document.getElementById('hourInput').value) || 0,
                setMinutes: Number(document.getElementById('minInput').value) || 0,
                setSeconds: Number(document.getElementById('secInput').value) || 0,
            });
        });
        document.getElementById('resetTimerBtn')?.addEventListener('click', () => {
            updateTimer('/admin/countdown/reset');
        });

        syncTimer();
        setInterval(syncTimer, 6000);
        setInterval(renderTimer, 1000);
    });
</script>
