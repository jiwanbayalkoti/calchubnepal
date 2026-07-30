# AGENTS.md

## Cursor Cloud specific instructions

This is a **Laravel 13 / PHP 8.3** app (project "calchubnepal", an AI Calculator Hub — a public catalog of calculators plus QR/visiting-card tools, a blog, and member/admin/advertiser areas). Frontend is Vite + Tailwind + Alpine. Calculators are public and computed by a local PHP engine (`app/Services/Calculators/Handlers`); auth (Breeze) only gates `/account`, `/admin`, `/advertiser`.

### Database: use MySQL, not SQLite (important, non-obvious)
`.env.example` defaults to `DB_CONNECTION=sqlite`, but the app **does not run on SQLite**: the ads partial (`resources/views/partials/ads/render.blade.php`) orders by the MySQL-only `FIELD()` function, so any page rendering ads (including the home page) returns HTTP 500 on SQLite (`no such function: FIELD`). Run against **MySQL**.

A MySQL server is installed in the VM. Start it and ensure the app DB/user exist:
```
sudo service mysql start
sudo mysql -e "CREATE DATABASE IF NOT EXISTS calchubnepal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS 'calchub'@'localhost' IDENTIFIED BY 'calchub'; GRANT ALL PRIVILEGES ON calchubnepal.* TO 'calchub'@'localhost'; FLUSH PRIVILEGES;"
```

### First-run setup (only if `.env` is missing / DB is empty)
`.env` is gitignored, so a fresh checkout has none. Create it from the example, point it at MySQL, then key/migrate/seed:
```
cp .env.example .env
# In .env set: DB_CONNECTION=mysql, DB_HOST=127.0.0.1, DB_PORT=3306,
#              DB_DATABASE=calchubnepal, DB_USERNAME=calchub, DB_PASSWORD=calchub
php artisan key:generate
php artisan migrate --seed --force   # seeds ~450 calculators, categories, blog posts, admin user, etc.
```
Seed data is required — without it the site is empty. Seeded admin credentials live in `database/seeders/AdminUserSeeder.php`.

### Running (dev mode)
Two long-lived processes (run each in its own tmux session, do not block):
```
php artisan serve --host=0.0.0.0 --port=8000   # app: http://localhost:8000
npm run dev                                     # Vite dev server (hot reload) on :5173
```
`composer dev` also works (runs server + queue + logs + vite concurrently). For a one-off static asset build use `npm run build`. Queue/scheduler/Redis/AI keys are optional and not needed for core browsing or calculators.

### Lint / test
- Lint (formatter): `./vendor/bin/pint` (use `--test` to check only). The repo is **not** currently Pint-clean; `--test` reports many pre-existing style deviations — do not treat that as an environment failure and do not mass-reformat.
- Tests: `php artisan test` (PHPUnit, uses in-memory SQLite per `phpunit.xml`). Several stock Laravel Breeze scaffolding tests fail as-is (`ExampleTest`, `AuthenticationTest`, `RegistrationTest`, `ProfileTest`) because the app customized routes (`/dashboard` → `/account`, login/register pages gated) and the default `ExampleTest` hits the DB without `RefreshDatabase`. These are pre-existing app-vs-test mismatches, not environment problems.

### Sanity check
`curl http://localhost:8000/` should return 200 (not 500). Core calc engine: `curl -X POST http://localhost:8000/api/v1/calculators/bmi-calculator/calculate -H 'Accept: application/json' -H 'Content-Type: application/json' -d '{"inputs":{"weight":70,"height":175}}'` returns BMI 22.9 / "Normal weight".
