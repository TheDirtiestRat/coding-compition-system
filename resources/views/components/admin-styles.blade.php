<style>
    .admin-shell {
        --color-base-100: #171717;
        --color-base-200: #202020;
        --color-base-300: #303030;
        --color-base-content: #e5e5e5;
        --color-primary: #f5f5f5;
        --color-primary-content: #171717;
        --admin-background: #0c0c0c;
        --admin-chrome: #111111;
        --admin-muted: #a3a3a3;
        --admin-edge-highlight: rgb(255 255 255 / 6%);
        --admin-shadow: rgb(0 0 0 / 18%);
        --color-secondary: var(--color-base-content);
        --color-accent: var(--color-base-content);
        --color-info: var(--color-base-content);
        --color-success: var(--color-base-content);
        --color-warning: var(--color-base-content);
        --color-error: var(--color-base-content);
        --color-secondary-content: var(--color-base-100);
        --color-accent-content: var(--color-base-100);
        --color-info-content: var(--color-base-100);
        --color-success-content: var(--color-base-100);
        --color-warning-content: var(--color-base-100);
        --color-error-content: var(--color-base-100);
        --color-neutral: var(--color-base-200);
        --color-neutral-content: var(--color-base-content);
        background: var(--admin-background);
        color: var(--color-base-content);
        color-scheme: dark;
    }

    [data-theme="light"] .admin-shell {
        --color-base-100: #fff;
        --color-base-200: #f5f5f5;
        --color-base-300: #e5e5e5;
        --color-base-content: #171717;
        --color-primary: #171717;
        --color-primary-content: #fff;
        --admin-background: #fafafa;
        --admin-chrome: #fff;
        --admin-muted: #525252;
        --admin-edge-highlight: rgb(255 255 255 / 90%);
        --admin-shadow: rgb(0 0 0 / 5%);
        color-scheme: light;
    }

    .admin-chrome {
        background: var(--admin-chrome);
    }

    .admin-muted {
        color: var(--admin-muted);
    }

    .admin-brand-mark,
    .admin-icon-badge {
        display: inline-flex;
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--color-base-300);
        border-radius: 11px;
        background: var(--color-base-200);
        box-shadow: inset 0 1px 0 var(--admin-edge-highlight);
    }

    .admin-brand-mark {
        background: var(--color-primary);
        color: var(--color-primary-content);
        border-color: var(--color-primary);
    }

    .admin-theme-moon { display: none; }
    [data-theme="light"] .admin-theme-moon { display: block; }
    [data-theme="light"] .admin-theme-sun { display: none; }

    .admin-nav-link {
        position: relative;
    }

    .admin-nav-link[aria-current="page"] {
        background: var(--color-base-200);
        border-color: var(--color-base-300);
        color: var(--color-base-content);
        box-shadow: inset 0 1px 0 var(--admin-edge-highlight);
    }

    .admin-nav-link[aria-current="page"]::before {
        content: '';
        position: absolute;
        inset: 11px auto 11px -1px;
        width: 2px;
        border-radius: 2px;
        background: var(--color-base-content);
    }

    .admin-card {
        min-width: 0;
        border: 1px solid var(--color-base-300);
        border-radius: 16px;
        background: var(--color-base-100);
        box-shadow: inset 0 1px 0 var(--admin-edge-highlight), 0 4px 12px -8px var(--admin-shadow);
    }

    .admin-shell .btn {
        min-height: 44px;
        height: auto;
        border-radius: 10px;
        box-shadow: none;
        font-size: 14px;
        padding: 10px 16px;
        font-weight: 500;
        letter-spacing: -0.01em;
    }

    .admin-shell .btn-primary {
        font-weight: 600;
        box-shadow: inset 0 1px 0 var(--admin-edge-highlight), 0 2px 3px var(--admin-shadow);
    }

    .admin-shell .admin-toolbar-button {
        min-width: 44px;
        padding: 10px 12px;
        border-color: transparent;
        background: transparent;
        box-shadow: none;
    }

    .admin-shell .admin-toolbar-button:hover {
        background: var(--color-base-200);
    }

    .admin-shell .btn-primary:hover {
        background: color-mix(in srgb, var(--color-primary), var(--color-base-100) 15%);
    }

    .admin-shell .btn-error {
        background: var(--color-base-100);
        color: var(--color-base-content);
        border-color: var(--admin-muted);
    }

    .admin-shell .btn-error:hover {
        background: var(--color-base-200);
    }

    .admin-shell :is(.input, .select, .textarea) {
        width: 100%;
        min-width: 0;
        border-color: #737373;
        border-radius: 10px;
        font-size: 14px;
        background-color: var(--color-base-100);
    }

    .admin-shell :is(.input, .textarea)::placeholder {
        color: var(--admin-muted);
        opacity: 1;
    }

    .admin-shell :is(.input, .select) {
        min-height: 44px;
    }

    .admin-shell .input[readonly] {
        background: var(--color-base-200);
        color: var(--admin-muted);
    }

    .admin-shell .fieldset {
        min-width: 0;
    }

    .admin-shell .fieldset-legend {
        font-size: 14px;
        font-weight: 500;
    }

    .admin-shell .label {
        white-space: normal;
        color: var(--admin-muted);
        line-height: 1.6;
    }

    .admin-shell .tab {
        min-height: 44px;
        font-size: 14px;
        color: var(--admin-muted);
    }

    .admin-shell .tab:checked {
        color: var(--color-base-content);
    }

    .admin-shell .tab-content {
        border-radius: 0 12px 12px 12px;
        box-shadow: inset 0 1px 0 var(--admin-edge-highlight), 0 4px 12px -8px var(--admin-shadow);
    }

    .admin-shell .table {
        font-size: 14px;
    }

    .admin-shell .table thead {
        color: var(--admin-muted);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .admin-shell .table th {
        font-weight: 500;
    }

    .admin-shell .table tbody tr:hover {
        background: color-mix(in srgb, var(--color-base-100), var(--color-base-content) 3%);
    }

    .admin-shell .table :is(th, td) {
        padding: 16px;
        border-color: var(--color-base-300);
    }

    .admin-shell .badge {
        height: auto;
        min-height: 24px;
        padding: 3px 8px;
        background: var(--color-base-200);
        color: var(--color-base-content);
        border: 1px solid var(--color-base-300);
    }

    .admin-shell .alert {
        background: var(--color-base-100);
        color: var(--color-base-content);
        border: 1px solid #737373;
        border-left: 4px solid var(--color-base-content);
        box-shadow: none;
    }

    .admin-shell :is(a, button, input, select, textarea, summary):focus-visible {
        outline: 3px solid var(--color-base-content);
        outline-offset: 4px;
    }

    .admin-submission {
        min-width: 0;
        border: 1px solid var(--color-base-300);
        border-radius: 12px;
        padding: 16px;
        text-align: left;
        cursor: pointer;
        background: var(--color-base-100);
        overflow-wrap: anywhere;
    }

    .admin-submission:hover {
        background: var(--color-base-200);
        border-color: var(--admin-muted);
    }

    @media (prefers-reduced-motion: no-preference) {
        .admin-shell .btn,
        .admin-nav-link,
        .admin-submission {
            transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease, box-shadow 150ms ease;
        }
    }

</style>
