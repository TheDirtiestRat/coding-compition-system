# Output checking and local AI review

User-approved scope: stronger output checking and an admin-only local AI review button, preserving official scoring and accepting different correct implementations. Branch `feature/local-ai-review`, base `230a7b4`. Simple existing Laravel/Blade/vanilla JS; no new package dependencies or PR/merge.

## Task 1: Output checking
- [x] Add `output_mode` (`exact`, `tokens`, `numeric`) and `output_tolerance` (absolute numeric tolerance, default 0.000001) per challenge. Existing challenges default to `exact`, retaining trimmed string equality.
- [x] Implement `App/Support/OutputChecker::matches(string $actual, string $expected, string $mode = 'exact', float $tolerance = 0.000001): bool`. Token mode compares ordered whitespace-separated tokens. Numeric mode requires equal counts of finite numeric tokens and an absolute difference within the configured finite nonnegative tolerance. Reject unknown modes and malformed/nonfinite numeric output. Document numeric mode for floating point outputs; exact/token modes preserve large integer precision.
- [x] Wire checker into CandidateController, challenge model/migration, AdminController validation/save, challenge UI/loading/draft recovery. Preserve rubric locks, grading privacy and existing form fields. An omitted setting while editing must preserve the saved setting for compatibility; new challenges use defaults. Numeric tolerance validation is required for numeric mode, finite and between 0 and 1.
- [x] Add unit and feature tests, including two different source implementations with equivalent results, whitespace differences, numeric edge cases, invalid configuration, persisted settings and rubric immutability. Commit separately using human identity only.

## Task 2: Local advisory AI
- [x] Use Ollama locally at loopback, no tools, cloud access or code execution. Install standalone runtime/models on D:/AI if absent. CPU-sized coding model; verify real output and document hardware limits.
- [x] Admin POST `/admin/api/submissions/{id}/ai-review` accepts current `code_hash`, returns 202 `{review_id}`. GET `/admin/api/ai-reviews/{reviewId}` returns a private owner-bound status `{state, model, review?, message?}`. Use database cache with 15-minute expiry and database queue `ai-reviews`, independent of the default sync queue. No score/submission mutation. Background job verifies the code hash before/after inference. Bounded input/output, timeouts, throttle, friendly offline/malformed response handling.
- [x] Structured review fields: `summary` string, `findings` array of strings, `limitations` string. Model never supplies an official score/verdict. Send task/language/checking settings/source/execution results without candidate identity or reference solution. Treat all supplied source/comments as untrusted data. PHP validates output; UI renders plain text only.
- [x] Config `local_ai.enabled` false by default, model and loopback URL configurable. Explicit local-only URL validation, no redirects. One AI worker to limit CPU contention. UI shows advisory label, pending/completed/failed feedback, retry, and discards stale results on selection changes. A queued review expiring or service failure must leave manual scoring available.
- [x] Tests cover authorization, queue/status ownership, stale code, no score mutation, schema and network failures, hostile model/source text and frontend selection races. Run real local model on synthetic code and verify browser workflow on isolated SQLite preview.

## Verification and handoff
- [x] Focused tests, existing regression suite, lint/build; independent whole-feature review and fix material findings. Preview desktop/mobile, leave admin signed in. Record limitations and no custom uploaded checker execution in this first version.

## Coordination
Task 1 owns output checker, migration, challenge fields/form/tests and CandidateController/AdminController. Task 2 owns AI service/job/controller/routes/config, submissions UI and AI tests; shared interface is the Task 1 challenge fields. Existing sandbox stays unchanged. Installation is local and reversible; no production database migration or data changes.

## Completed verification

- Output checking committed separately as d9cdcff; independent task review found no material issues.
- Final full suite: 168 tests, 939 assertions, 8 environment-dependent checks skipped. All 13 frontend JavaScript files, scoped Pint, diff checks and production build passed. Existing build warnings remain.
- Whole-feature review and SQLite follow-up review found no remaining material issues. Fixed associative model findings with list validation and a regression test.
- Live execution exposed a SQLite deferred write-lock upgrade race in queue reservation. Reproduced it with two processes; busy_timeout=5000 and IMMEDIATE transactions fixed it. Added the concurrent reservation regression. PHP 8.4 is required for transaction mode handling.
- Installed official Ollama v0.40.16 CPU runtime at D:/AI/Ollama; signed executable validated. CPU files extracted from the official ZIP with entry size/CRC checks. Model qwen2.5-coder:3b downloaded and verified by Ollama under D:/AI/Models/Ollama. Listener is loopback only and server reports cloud disabled.
- Real queued local AI review completed in existing Chrome on synthetic alternative-solution code. Manual scoring stayed enabled. Review panel fit at 320px during progress; model failure/retry and stale selections are covered. Browser connection was interrupted and recovered in the existing Chrome window. Physical phone testing, sandbox execution and large-competition model evaluation were not performed.
- Preview-only migrations/configuration and worker were used. Real project environment/database were unchanged. Feature remains opt-in by default. No PR, push or merge requested for this branch.
