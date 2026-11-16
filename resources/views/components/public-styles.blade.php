<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    body {
        --color-primary: #404040;
        --color-primary-content: #ffffff;
        --color-base-content: #171717;
        --color-neutral: #171717;
        --color-neutral-content: #ffffff;
        font-family: 'Inter', sans-serif;
        background: #ffffff;
        color: #171717;
    }

    section[id],
    main[id] {
        scroll-margin-top: 9rem;
    }

    a:focus-visible,
    button:focus-visible,
    input:focus-visible {
        outline: 3px solid #171717;
        outline-offset: 4px;
    }

    .motion-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .terminal-cursor {
        display: inline-block;
        width: 0.45em;
        height: 1em;
        margin-left: 0.35em;
        vertical-align: -0.1em;
        background: currentColor;
        opacity: 0;
    }

    @keyframes enter {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes blink {
        0%, 49% { opacity: 1; }
        50%, 100% { opacity: 0; }
    }

    @media (prefers-reduced-motion: no-preference) {
        .motion-enter,
        .motion-reveal.is-visible {
            animation: enter 400ms cubic-bezier(0.22, 1, 0.36, 1) var(--motion-delay, 0ms) backwards;
        }

        .motion-enter:focus-within,
        .motion-reveal:focus-within {
            animation: none;
        }

        .motion-button,
        .motion-button span {
            transition: transform 200ms ease, background-color 200ms ease, box-shadow 200ms ease;
        }

        .is-visible .terminal-cursor {
            animation: blink 700ms step-end 600ms 3;
        }
    }

    @media (prefers-reduced-motion: no-preference) and (hover: hover) and (pointer: fine) {
        .motion-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgb(0 0 0 / 8%);
        }

        .motion-button:hover span {
            transform: translateX(3px);
        }

        .motion-button:active {
            transform: translateY(0);
            box-shadow: none;
        }
    }
</style>
