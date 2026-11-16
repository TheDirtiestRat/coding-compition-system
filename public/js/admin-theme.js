(() => {
    const root = document.documentElement;
    const storageKey = 'coding-admin-theme';

    // Apply the saved choice before the page renders; dark is the default.
    try {
        const saved = localStorage.getItem(storageKey);
        if (saved === 'light' || saved === 'dark') root.dataset.theme = saved;
    } catch {
        // Theme switching still works when browser storage is unavailable.
    }

    document.addEventListener('DOMContentLoaded', () => {
        const button = document.querySelector('[data-theme-toggle]');
        if (!button) return;
        const label = button.querySelector('[data-theme-label]');

        function updateButton() {
            const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
            button.setAttribute('aria-label', `Switch to ${next} theme`);
            button.title = `Switch to ${next} theme`;
            label.textContent = next === 'light' ? 'Light mode' : 'Dark mode';
        }

        updateButton();
        button.hidden = false;
        button.addEventListener('click', () => {
            const theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
            root.dataset.theme = theme;
            window.monaco?.editor.setTheme(theme === 'light' ? 'vs' : 'vs-dark');
            updateButton();
            try {
                localStorage.setItem(storageKey, theme);
            } catch {
                // Keep the chosen theme for this page even if it cannot be saved.
            }
        });
    });
})();
