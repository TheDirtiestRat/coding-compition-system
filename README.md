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
