# Audit cleanup implementation plan

Goal: fix the source audit findings with small changes in the existing Laravel, Blade and vanilla JavaScript style.

Constraints: eight agents, separate local branches, focused commits, configured human author only, no pushes or pull requests. Follow Ponytail and .editorconfig; avoid broad renaming or new dependencies.

1. `fix/timer-state`: use one persisted timer state across controls, polling and admission; retain route contracts.
2. `fix/submission-rules`: enforce candidate/event boundaries, reject unrun auto-grading, cap scores, handle missing records and serialize review writes.
3. `fix/execution`: share execution logic, preserve standard input including zero, clean temporary files and report timeouts/exit failures.
4. `fix/admin-confirmation`: settle confirmation on every exit and prevent canceled requests from being submitted later.
5. `fix/editor-client`: guard initialization, show submission errors, select the challenge language and pass competition lock state explicitly.
6. `fix/admin-review`: use the selected language for admin execution, clear stale scores and ignore out-of-order selections.
7. `fix/safe-templates`: render data as text and serialize edit arguments safely, including markup and apostrophes.
8. `fix/data-consistency`: transact related database writes, remove runtime factory use, repair rollback ordering and update misleading setup documentation.

Collect the commits on `fix/audit-cleanup` for source review. Resolve shared-file conflicts by preserving both changes, inspect the resulting callers, run syntax/build and focused behavior checks where supported, and verify authorship and clean working trees. PHP 8.2 is installed but composer.json requires 8.3; do not claim full application verification without a compatible runtime.
