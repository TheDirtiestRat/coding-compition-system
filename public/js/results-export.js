document.querySelector('[data-export-pdf]')?.addEventListener('click', () => window.print());

document.querySelector('[data-certificate-form]')?.addEventListener('change', () => {
    document.querySelector('[data-export-pdf]').disabled = true;
    document.querySelector('[data-certificate-status]').textContent = 'Generate certificates to update the preview before printing.';
});

document.querySelector('[data-results-fullscreen]')?.addEventListener('click', async () => {
    const results = document.querySelector('.results-page');
    const status = document.querySelector('[data-results-status]');
    status.hidden = true;
    try {
        await results.requestFullscreen();
    } catch {
        status.textContent = 'Fullscreen is unavailable. Allow fullscreen in your browser and try again.';
        status.hidden = false;
    }
});
