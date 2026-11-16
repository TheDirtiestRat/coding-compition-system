# CTF admin alignment implementation plan

**Goal:** Follow the CTF admin design in the coding competition system on `ui/ctf-admin-alignment`, based on `43f742d`.
**Reference:** CTF fetched `origin/main` at `9a2faed`. The user selected this existing design; use its Blade components, DaisyUI controls, Inter typography, neutral surfaces, fixed desktop sidebar, mobile menu and light/dark toggle.
**Architecture:** Copy the CTF shell/style/icon/theme components, adapt branding and retain all coding routes. Restyle existing page markup while preserving form actions, IDs, validation, timer endpoints, scoring and editor behavior. Do not copy CTF-specific event restrictions, flags or timer implementation.
**Stack:** Existing Laravel Blade, Tailwind/DaisyUI, vanilla JavaScript and Monaco; no new dependencies.

- [x] Port and test the shared admin shell and theme behavior, including blocked storage and keyboard/mobile navigation. Commit separately.
- [x] Adapt dashboard, events, candidates, challenges, submissions, language management and standalone monitor/results views to the CTF card/table/form layout. Preserve coding controls and add useful labels, empty states and responsive containment. Commit separately.
- [x] Verify focused backend/frontend checks and build, inspect populated and empty screens in existing Chrome at desktop/390px/320px, exercise reversible forms and dialogs, and obtain independent code review. Fix material findings before completing.

**Review focus:** No missing routes or controls; no changes to timer/scoring semantics; hidden review sections stay hidden; Monaco follows theme and viewport; long content/table scrolling must not expand the page. Preserve human Git attribution. Leave this as a local branch with commits; no new PR or merge requested.

## Verification results

- Independent review found two incorrectly paired Blade loops and an encoded photo-delete symbol; corrected all three and added empty-table plus validation-error rendering coverage. Re-review found no remaining material findings.
- Final PHP run: 116 tests, 736 assertions, 8 environment-dependent checks skipped. All 12 JavaScript files, scoped Pint and `git diff --check` passed. Production asset build passed with existing CSS property and Monaco chunk-size warnings.
- Existing Chrome verified all eight admin screens at 320px with the real admin content present and no horizontal overflow, plus 390px dialogs/review and desktop overview/challenges. Checked long event titles, skip-link focus, grouped mobile navigation, light/dark persistence and Monaco theme switching.
- Native flows checked: event validation, event/competitor editing, cancelled deletion, language dialog/cancel, challenge loading and test-case add/remove, keyboard-opened solved-code dialog, and manual scoring of a synthetic submission. The review queue's empty state appeared after scoring.
- Existing timer set/reset and registration open/close worked on the isolated SQLite preview. Restored the preview timer to zero, registration to closed and the original event title. No production data or CTF source files changed.
- Physical phone testing and sandbox code execution were not performed. Preview remains signed in as admin. No new PR, push or merge for this branch.
