# Output checking and local AI review

Different implementations can receive the same score. Automatic grading executes submitted code against the configured cases; it does not compare source code with the reference solution.

## Checking modes

- **Exact:** existing behavior: compare strings after trimming surrounding whitespace.
- **Whitespace-separated tokens:** ignore whitespace differences between ordered tokens. Token spelling and order still matter.
- **Numeric:** require matching counts of finite numeric tokens, each within the configured absolute tolerance (0 through 1, default 0.000001). This uses floating point arithmetic; use exact/token checking for large integers or exact decimal strings.

Existing challenges retain exact mode. Checking settings are part of the rubric and cannot change after a submission exists. Custom executable checkers are outside this version; use manual review for problems with domain-specific output validation. Prepare normal, boundary and adversarial cases for each challenge.

## Enable advisory reviews

Use PHP 8.4 and apply project migrations to the intended application database. The cache/jobs tables and new challenge columns are required.

1. Install [Ollama](https://docs.ollama.com/windows) on the application host, then download a local coding model: `ollama pull qwen2.5-coder:3b`.
2. Start Ollama bound to loopback with process environment variables `OLLAMA_NO_CLOUD=1`, `OLLAMA_NUM_PARALLEL=1` and `OLLAMA_MAX_LOADED_MODELS=1`. Use `OLLAMA_MODELS` to choose the model storage directory, then run `ollama serve`.
3. Configure the application:

```dotenv
LOCAL_AI_ENABLED=true
LOCAL_AI_URL=http://127.0.0.1:11434
LOCAL_AI_MODEL=qwen2.5-coder:3b
```

4. Refresh cached Laravel configuration if applicable. Start **one** separate worker:

```sh
php artisan queue:work ai-reviews --queue=ai-reviews --tries=1 --timeout=180 --sleep=1
```

SQLite uses a 5-second busy timeout and IMMEDIATE transactions to avoid read-to-write reservation races between the queue worker and web requests (PHP 8.4 required). The dedicated connection uses the database driver and a 240-second retry window, even when the default queue is `sync`. Local HTTP requests time out after 120 seconds. Windows workers may lack process-level timeout enforcement, so the HTTP timeout remains necessary. Keep the worker running while reviews are offered. The feature is disabled by default.

## Admin workflow

Select a submission, then choose **Request AI review**. The panel displays progress, model feedback, or a failure with a retry path. Reviews expire after 15 minutes. After 10 minutes of waiting, the UI stops polling and suggests checking the worker. Switching submissions discards the previous selection's display.

The model receives the task, language, checking settings, source and available execution results. Candidate identity and the reference solution are excluded. Serialized context is limited to 8,000 bytes; larger submissions remain available for manual scoring. A code hash is checked before/after inference. Output is validated and rendered as plain text; model-supplied scores are ignored. Feedback is private to the requesting admin and never updates the submission, score, ranking or acceptance state.

AI feedback is static advisory analysis, not proof of correctness. The model has no tools and never runs student code; the existing isolated runner handles execution. Hidden test evidence stays in the admin workflow. Keep Ollama cloud features disabled for local-only operation; the app rejects non-loopback URLs and redirects.

If the worker, service or model is unavailable, manual scoring still works. A small quantized CPU model is intended for occasional requested review; evaluate its advice and performance before using it in a larger competition.

API references: [Ollama chat](https://docs.ollama.com/api/chat), [structured outputs](https://docs.ollama.com/capabilities/structured-outputs).
