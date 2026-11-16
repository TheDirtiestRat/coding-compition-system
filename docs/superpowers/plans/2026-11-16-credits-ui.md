# Credits refresh plan

**Goal:** Match the light login/homepage style while preserving all nine guild profiles and their existing details, images and GitHub destinations.
**Branch:** `ui/credits-refresh`, based on the homepage refresh so the preview stays consistent.
**Scope:** `resources/views/pages/credits.blade.php` and its existing `components/public-navbar.blade.php`, whose only caller is Credits. Add no dependencies or backend behavior.

## Design and checks

- [x] Keep Elder, Officers and Members grouping, original profile data, photo fallback and safe external GitHub links.
- [x] Apply neutral surfaces, readable headings, rounded cards, matching navigation/footer, visible focus and mobile wrapping.
- [x] Build styles and check Credits in existing Chrome at desktop, 390px and 320px; verify all nine images, names, roles, ages and GitHub links, skip navigation and Home/Login links.
- [x] Review independently, address material findings and commit with human authorship. No PR or merge in this task.

## Outcome

The nine member records are unchanged. All nine photos loaded in Chrome with matching alternative text; GitHub links retain their destinations, safe external attributes and distinct accessible labels. A separate isolated render confirmed all nine fallback profiles when photos are absent. Native checks showed no horizontal overflow at 390px and 320px, 44px profile link targets, visible keyboard focus, a working skip link and correct navigation to the homepage, guide and login. The production build and 20 existing homepage/login checks passed; independent review found no blockers. Physical-phone testing and live external GitHub profile availability were not checked.
