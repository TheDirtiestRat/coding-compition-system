## About the System

This system is created soely to have a one outlet for use to held a coding compition its purpose is to make it easier to check the code make it gamify and to streamline the process of the coding compition.

## Local Setup

Use PHP 8.3 or later with PDO SQLite enabled, and Composer. Check `php -v` and `php --ri pdo_sqlite` first. If multiple PHP versions are installed, put the supported PHP directory first on `PATH` so Composer and Artisan use the same executable.

1. Install PHP dependencies:
   `composer install`
2. Prepare environment file and key:
   `copy .env.example .env`
   `php artisan key:generate`
3. Install frontend dependencies:
   `npm install`
4. Use SQLite for local development (no MySQL required):
   the template already sets `DB_CONNECTION=sqlite`. Leave `DB_DATABASE` unset to
   use this project's `database/database.sqlite`. For another file, set
   `DB_DATABASE` to its quoted absolute path (use forward slashes on Windows).
5. Prepare database:
   `if (!(Test-Path database\database.sqlite)) { New-Item -ItemType File -Path database\database.sqlite }`
   `php artisan migrate --seed`
6. Build assets for Blade pages:
   `npm run build`
7. Start the app:
   `php artisan serve --no-reload`

If the page renders without styles, run `npm run build` again and refresh the browser.
The seeder creates only an `Admin` account. Use the password configured in
`database/seeders/DatabaseSeeder.php` for your local setup. It does not create a
contestant account; create one through the admin page or open registration.

## Isolated code execution

Code execution requires a current, patched Docker Engine running Linux containers.
Build the Python 3 / Java 25 sandbox once before accepting submissions:

```sh
docker build -t coding-competition-sandbox:1 docker/code-runner
```

The application uses `CODE_RUNNER_IMAGE` (default `coding-competition-sandbox:1`)
and never pulls an image during a request. If Docker or the image is unavailable,
execution fails closed with HTTP 503; no submitted code runs on the application host.
Do not mount application files, credentials, or the Docker socket into the sandbox.
Only the trusted application process needs Docker access. Keep Docker access away
from contestants and use a dedicated worker host for production contests.

Each request runs as a nonroot user with no network, a read-only image, a 64 MB
writable workspace, 512 MB memory, one CPU, 64 processes and a 1 MB output limit.
Compilation and execution share that workspace and each have the requested timeout.
The container is force-removed after success, errors or timeouts. Language commands
configured by administrators run inside this Linux image: use `javac {file}` /
`java -cp {dir} Main` for Java and `python {file}` for Python. The original seeded
Windows JDK 23 commands are translated to these Java commands; other host-specific
paths must be updated. Additional languages require installing their runtime in
the trusted image before configuring them. No environment variables from the
application are forwarded to submitted code.

Run the actual sandbox acceptance tests after building the image (these tests are
skipped by default, and process-fake tests do not establish live isolation):

```powershell
$env:RUN_SANDBOX_TESTS = '1'
php artisan test --filter=CodeRunnerSandboxTest
Remove-Item Env:RUN_SANDBOX_TESTS
```

## Score timeline

The results page plots cumulative points for all competitors. Automatic grading
and organizer scoring record `scored_at` when a submission receives its final score.
Run the migrations before enabling this version. Existing scores retain their standings
but are excluded from the timeline because their award times were not recorded.
