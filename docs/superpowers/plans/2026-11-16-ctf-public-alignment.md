# CTF public UI alignment

**Goal:** Match the actual CTF public UI and component structure, correcting the earlier independent adaptations.
**Reference:** `capture_the_flag-compition-system` current fetched `origin/main` at `9a2faed`.
**Branch:** `ui/ctf-public-alignment`, preserving the earlier UI commits.

## Scope

- Share `public-layout`, `public-navbar`, `public-styles` and `guild-members` components, following the CTF structure.
- Use the compact one/two/three-column CTF member grid, 80px portraits, left-aligned identities and full-width View GitHub rows. Preserve all existing names, roles, ages, images and destinations.
- Match homepage type, spacing, illustrative example, guide cards, footer and accessible motion. Keep coding-specific instructions and registration behavior.
- Display the existing coding timer's status honestly; read it without changing timer behavior. Retain the login form and its working validation/toggle while using the shared wrapper.
- Keep mobile overflow fixes, keyboard focus, skip navigation, reduced motion and content visibility without JavaScript.

## Verification

- [x] Add checks for the four homepage timer states and motion fallbacks before implementing those behaviors.
- [x] Port and adapt the shared components and page markup; compare directly with the CTF source.
- [x] Build styles, check member data/fallback preservation, and verify desktop/mobile navigation and forms in Chrome.
- [x] Review independently, resolve material findings and commit with human attribution. No PR or merge in this task.

## Results

- Compared against the fetched CTF source at `9a2faed`; shared public styles copied directly and page content adapted to coding challenges.
- All nine member records match the previous coding roster; all images loaded and the missing-photo fallback rendered all nine profiles.
- Chrome verified desktop, 320px homepage/Credits and 390px login, guide anchor clearance, skip focus, password toggle, rejected credentials and successful student sign-in. No horizontal overflow in those checks or browser errors.
- 24 focused PHP tests, 11 JavaScript scripts, scoped Pint and the production asset build passed. Existing Monaco chunk-size and CSS property build warnings remain.
- Independent read-only review reported no material findings. Physical phone testing and deployment were outside this task.
