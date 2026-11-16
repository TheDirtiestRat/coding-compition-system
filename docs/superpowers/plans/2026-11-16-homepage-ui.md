# Homepage refresh plan

**Goal:** Match the approved login and CTF reference with a light, readable competition homepage.
**Scope:** The homepage Blade view only; keep the login, registration, credits and section links. Use the existing Tailwind build and add no dependencies or backend behavior.
**Branch:** `ui/homepage-refresh`, based on `ui/login-refresh` while PR #25 remains open.

## Design

- White and neutral surfaces, dark sans-serif headings, black primary buttons, visible keyboard focus, and the existing code mark.
- Centered event title and headline, a labeled illustrative Python example, and a real three-step explanation of logging in, solving and submitting.
- Keep navigation visible and wrapping on narrow screens rather than requiring a JavaScript mobile menu.
- Link the team through the existing Credits page instead of duplicating its full roster on the homepage. Remove placeholder social links.
- Use native anchors and adequate sticky-header scroll offsets. Content remains visible without JavaScript and motion preferences need no special reveal behavior.

## Verification

- [x] Replace the old homepage markup and decorative scripts with the scoped presentation.
- [x] Build styles and run the existing homepage/login checks and frontend scripts.
- [x] In existing Chrome, check desktop, 390px and 320px layouts, a long event title, keyboard focus, section links, login, registration and credits.
- [x] Request an independent read-only review, resolve material findings, then commit under the human Git identity. No PR or merge in this task.

## Outcome

The production build, 20 existing homepage/login tests and all ten frontend scripts passed. Native Chrome confirmed no horizontal scrolling at 390px and 320px, including a 216-character event title; section targets stayed below the sticky header. Keyboard Skip to content focused the main region. Login, registration-gate, /home alias and Credits navigation worked, and the original nine-member roster remained on Credits. The static homepage has no JavaScript dependency. Independent review found no blockers. Physical-phone testing remains outside this pass.
