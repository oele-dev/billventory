# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Billventory — a Laravel 10 inventory & billing manager. PHP ^8.1, MySQL by default, Blade UI scaffolded via `laravel/ui`, PDF invoices via `barryvdh/laravel-dompdf`.

## Commands

```bash
# First-time setup
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed       # seeds an admin user (johndoe@example.com, type=1) via DatabaseSeeder
npm install && npm run dev       # Vite dev server

# Run app
php artisan serve

# Tests (PHPUnit, configured in phpunit.xml)
vendor/bin/phpunit
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature
vendor/bin/phpunit tests/Feature/HomeTest.php          # single file
vendor/bin/phpunit --filter=methodName                  # single test

# Code style / refactor
vendor/bin/pint                  # Laravel Pint formatter
vendor/bin/rector process        # Rector — config in rector.php, Laravel-up-to-100 + PhpSets, type-coverage level 0
vendor/bin/rector process --dry-run

# Asset build
npm run build
```

Note: `tests/Feature/HomeTest.php` is written in **Pest** syntax (`it()`, `Pest\Laravel\actingAs`), but Pest is **not** in `composer.json`. The Pest plugin is merely allowed in `config.allow-plugins`. Either install `pestphp/pest` + `pestphp/pest-plugin-laravel`, or rewrite the test as a PHPUnit class before relying on it.

Note: `vite.config.js` declares inputs `resources/css/app.css` and `resources/js/app.js`, but `resources/` currently only contains `assets/sass/` and `views/`. The Vite entrypoints need to be created (or the config adjusted) before `npm run dev`/`npm run build` will succeed.

## Architecture

Plain Laravel MVC — no service/repository layer, no API (web only). All business logic lives directly in controllers.

**Domain model** (`app/Models/`):
- `User` — has many `Sale`. Custom `updatePassword()` returns a hashed value or the existing password if input is `" "` (single space sentinel — preserve this when editing user update flows). Has a `type` column used as a role flag.
- `Provider`, `Category` — taxonomy parents of `Product`.
- `Product` — belongs to provider/category/user; has a `stock` integer that `SalesController::store` decrements directly (no transaction, no validation that stock ≥ qty).
- `Customer` — belongs-to from `Sale`.
- `Sale` — header row with `customer_id`, `user_id`, `total`. Line items live in the `sale_products` pivot table with extra columns `qty`, `desc`, `total` (accessed via `$prod->pivot->...`). Always traverse sale lines through `$sale->products` and read pivot values, never assume a separate line-item model.

**Controllers** (`app/Http/Controllers/`):
- One `*Controller` per resource: `Categories`, `Customers`, `Products`, `Providers`, `Sales`, `Users`, plus `HomeController` (dashboard) and `Auth/*` (Laravel UI scaffold).
- `routes/web.php` wires everything: `Auth::routes()` + an `auth`-middleware group that maps each resource via `Route::resource(...)`. All resources except `sales` exclude `create` and `show`; forms are rendered inline from index views (modals/partials in Blade).
- `SalesController::invoice($id)` renders `resources/views/pdf/invoice.blade.php` through dompdf and returns a download response. **The selling company's name, NIT, address, phone, and email are hardcoded inside this method** — there is no settings table. Any business-detail change requires editing this controller.
- `SalesController::store` is the core write path: creates `Sale`, attaches each product to the pivot with qty/desc/total, decrements `Product->stock` per line, then flashes `download.sale` to the session so the next page can offer the invoice download.

**Views** (`resources/views/`): Blade templates organized one folder per resource (`sales/`, `products/`, etc.) plus `pdf/invoice.blade.php` for the dompdf template. `app.blade.php` and `navbar.blade.php` are the layout shell. `vendor/` holds published package views.

**Database**: migrations in `database/migrations/` cover users, providers, categories, products, customers, sales, sale_products (pivot). Only `UserFactory` exists; there are no factories for the domain models, so feature tests that need products/sales must build them manually or new factories must be created.

**Auth**: session-based via Laravel UI; Sanctum is installed but no API routes are defined (`routes/api.php` is the default empty file). The `auth` middleware guards every non-login route in `web.php`.

## Conventions in this codebase

- Models reference each other with fully-qualified class names (`\App\Models\Foo::class`) inside relationship methods — match that style when editing existing models.
- Controller methods still carry the default Laravel scaffolding docblocks and `//` placeholders for unused actions (e.g., `edit`, `update`, `destroy` on `SalesController`). Removing them is fine when you're touching the method, but don't do drive-by cleanup on unrelated methods.
- The Rector config (`rector.php`) targets `LaravelLevelSetList::UP_TO_LARAVEL_100` and `withPhpSets()` — running it will rewrite legacy patterns aggressively. Run `--dry-run` first and review on a clean working tree.
