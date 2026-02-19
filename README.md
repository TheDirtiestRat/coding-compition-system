## About the System

This system is created soely to have a one outlet for use to held a coding compition its purpose is to make it easier to check the code make it gamify and to streamline the process of the coding compition.

## Local Setup

1. Install PHP dependencies:
   `composer install`
2. Prepare environment file and key:
   `copy .env.example .env`
   `php artisan key:generate`
3. Install frontend dependencies:
   `npm install`
4. Use SQLite for local development (no MySQL required):
   set `DB_CONNECTION=sqlite`
   set `DB_DATABASE=database/database.sqlite`
5. Prepare database:
   `New-Item -ItemType File -Path database\database.sqlite -Force`
   `php artisan migrate:fresh --seed`
6. Build assets for Blade pages:
   `npm run build`
7. Start the app:
   `php artisan serve --no-reload`

If the page renders without styles, run `npm run build` again and refresh the browser.
Default seeded logins:
`Admin / admin`
`User / user`
