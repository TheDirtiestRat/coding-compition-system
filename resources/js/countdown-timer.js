class CountDownTimer {

    getCurrentTime() {
        return fetch("/admin/timer/get_timer", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            },
            body: JSON.stringify({}),
        })
            .then((res) => res.json())
            .then((data) => {
                // curTime = data;
                return data;
            });
    }

    startCountdown() {
        const durationMs = hours * 3600000 + minutes * 60000 + seconds * 1000;

        if (!durationMs) return;

        fetch("/admin/countdown/start", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            },
            body: JSON.stringify({
                duration_ms: durationMs,
                // values that we can use
                setHours: hours,
                setMinutes: minutes,
                setSeconds: seconds,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                endTime = data.endTime;
            });
    }
}
