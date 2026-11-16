(() => {
    const button = document.getElementById('aiReviewButton');
    if (!button) return;
    const status = document.getElementById('aiReviewStatus');
    const result = document.getElementById('aiReviewResult');
    const findings = document.getElementById('aiReviewFindings');
    let selected = null;
    let generation = 0;
    let timer = null;

    window.setAiReviewSubmission = submission => {
        selected = submission;
        generation++;
        clearTimeout(timer);
        button.disabled = !selected || button.dataset.enabled !== 'true';
        result.hidden = true;
        findings.replaceChildren();
        status.textContent = button.dataset.enabled === 'true'
            ? 'Select a submission, then request an optional review.'
            : 'Local AI is not enabled. Manual scoring remains available.';
    };

    button.addEventListener('click', async () => {
        if (!selected || button.disabled) return;
        const requestGeneration = ++generation;
        const submission = selected;
        const started = Date.now();
        button.disabled = true;
        result.hidden = true;
        findings.replaceChildren();
        status.textContent = 'Queuing local AI review. You can continue reviewing or scoring.';

        function fail(message) {
            if (requestGeneration !== generation) return;
            status.textContent = message;
            button.disabled = false;
        }

        async function poll(reviewId) {
            if (requestGeneration !== generation) return;
            try {
                const response = await fetch(`/admin/api/ai-reviews/${encodeURIComponent(reviewId)}`, {
                    headers: { Accept: 'application/json' },
                });
                const data = await response.json();
                if (requestGeneration !== generation) return;
                if (!response.ok) throw new Error(data.message || 'Review expired. Request another review.');
                if (data.state === 'completed') {
                    document.getElementById('aiReviewSummary').textContent = data.review.summary;
                    data.review.findings.forEach(text => {
                        const item = document.createElement('li');
                        item.textContent = text;
                        findings.appendChild(item);
                    });
                    document.getElementById('aiReviewLimitations').textContent = data.review.limitations;
                    status.textContent = `Review complete (${data.model}). Advisory feedback; official scoring is unchanged.`;
                    result.hidden = false;
                    button.disabled = false;
                } else if (data.state === 'failed') {
                    fail(data.message || 'Local AI could not finish. Retry or review manually.');
                } else if (Date.now() - started > 10 * 60 * 1000) {
                    fail('The review is taking too long. Check the AI worker, then retry or review manually.');
                } else {
                    status.textContent = data.state === 'running'
                        ? 'Local AI is reviewing the selected code. Official scoring remains available.'
                        : 'Review is queued. Waiting for the local AI worker.';
                    timer = setTimeout(() => poll(reviewId), 1500);
                }
            } catch (error) {
                fail(error.message || 'Unable to load the review. Retry or review manually.');
            }
        }

        try {
            const response = await fetch(`/admin/api/submissions/${submission.id}/ai-review`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ code_hash: submission.codeHash }),
            });
            const data = await response.json();
            if (requestGeneration !== generation) return;
            if (!response.ok) throw new Error(data.message || 'Unable to queue the review. Retry or review manually.');
            await poll(data.review_id);
        } catch (error) {
            fail(error.message || 'Unable to start the review. Retry or review manually.');
        }
    });

    window.setAiReviewSubmission(null);
})();
