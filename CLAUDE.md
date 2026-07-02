# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **Laravel 12** on **PHP 8.3** (`platform.php` is pinned in `composer.json`)
- **Filament 4** admin panel mounted at `/admin` (`app/Providers/Filament/AdminPanelProvider.php`)
- **Livewire 3** drives all storefront pages (see `routes/web.php` — routes map directly to `App\Livewire\*` classes, there are no traditional controllers besides the base `Controller.php`)
- **Tailwind CSS 4** + **Preline UI** + Vite for frontend assets
- **Stripe** (`stripe/stripe-php`) for payments
- **Spatie laravel-permission** for roles/permissions (see `App\Enums\RolesEnum`, `PermissionsEnum`)
- **maatwebsite/excel** for spreadsheet exports (`App\Exports\OrdersExport`)
- **Pest 3** (+ pest-plugin-livewire, pest-plugin-laravel) for tests

## Commands

```bash
composer run setup    # install deps, copy .env, key:generate, migrate, npm install, npm run build
composer run dev      # concurrent: php artisan serve + queue:listen + pail (log tail) + vite
composer run test     # config:clear then artisan test (runs Pest)

php artisan test --filter=ProductDetailPageTest   # single test file
php artisan test tests/Feature/HomeTest.php       # single file by path

npm run dev           # vite dev server only
npm run build         # production asset build

./vendor/bin/pint     # code style (Laravel Pint)
```

`compose.yaml` provides a Laravel Sail stack (redis, meilisearch, mailpit, selenium); the MySQL service is commented out — DB is expected to be provided externally or via SQLite. `phpunit.xml` forces `DB_DATABASE=testing` and `QUEUE_CONNECTION=sync` for tests.

## Architecture

This is a layered Laravel application. Read this section before adding business logic — the layering is enforced by convention, not by the framework.

### Request flow

1. **Storefront**: `routes/web.php` routes directly to full-page Livewire components in `app/Livewire/` (e.g. `HomePage`, `ProductsPage`, `CheckoutPage`). Auth pages live under `app/Livewire/Auth/`. There are no `Http/Controllers/*` for storefront pages — do not add them; add Livewire components instead.
2. **Admin**: Filament resources under `app/Filament/Resources/{Domain}/` follow Filament 4's split layout — each resource directory contains `Pages/`, `Schemas/`, `Tables/`, `RelationManagers/`, optionally `Widgets/`. `OrderResource` is the most complete example.
3. **Services** in `app/Services/` hold business logic (`CartService`, `OrderService`, `StripeService`, `UserService`, `UiService`). They are **registered as singletons in `AppServiceProvider::register()`** with their repository dependencies — when adding a new service, wire it there rather than relying on auto-resolution, to keep dependencies explicit.
4. **Repositories** in `app/Repositories/` own data access. Services depend on repositories, not on Eloquent models directly, for queries beyond trivial lookups.
5. **DTOs** in `app/Dtos/` carry structured input between layers (e.g. `CheckoutDTO`, `OrderSearchRequestDTO`, `AddToCartDto`). Use them at service boundaries — don't pass Livewire form state directly into services.
6. **Enums** in `app/Enums/` are the source of truth for statuses and fixed value sets (`OrderStatusEnum`, `OrderPaymentStatusEnum`, `PaymentMethodEnum`, `StripePaymentStatusEnum`, `ProductStatusEnum`, etc.). Never use raw strings/ints for these fields.
7. **Custom exceptions** in `app/Exceptions/` (e.g. `CartException`, `OrderException`, `EmptyCartException`, `PaymentException`, `ProductNotFoundException`) signal domain errors — throw these from services rather than generic exceptions.

### Cart system (non-obvious)

The cart supports both guests and authenticated users and `CartService` switches storage based on `Auth::check()`:

- **Guest cart**: serialized into two cookies (`cart`, `cartItems`) with a 1-year lifetime. `CartItem` instances are reconstituted from cookie JSON; the cookie's item id is stashed on a dynamic `id_from_cookie` property since it isn't part of the DB schema.
- **Authenticated cart**: persisted via `CartRepository` to the `carts` / `cart_items` tables.
- **Login transition**: `App\Listeners\TransferGuestCartToUser` listens on the `Login` event and calls `CartService::moveCartItemsToDatabase()` for buyer users. Be aware of this when changing login flow or cart shape.
- `CartService` caches the resolved cart on `$cachedCart` for the lifetime of the request — singleton binding matters here.

### Orders & payments

`OrderService` orchestrates `OrderRepository`, `AddressRepository`, `PaymentMethodRepository`, `StripeOrderDetailRepository`, `CartService`, and `StripeService`. Stripe-specific state is stored separately in `stripe_order_details` rather than on the `orders` table — keep that split. Order exports go through `OrdersExport` (PhpSpreadsheet via maatwebsite/excel) and return a `BinaryFileResponse`.

### Shared frontend pieces

- `app/Livewire/Partials/` — `Navbar`, `Footer` (rendered across the layout)
- `app/Livewire/Traits/WithCartActions.php` — shared add-to-cart behaviour for Livewire pages
- `app/Traits/HasStatusClasses.php` — maps enum statuses to Tailwind badge classes
- `resources/js/app.js` is registered as a Filament asset (`sweetalert2`) via `FilamentAsset` in `AppServiceProvider::boot()` so the admin panel can use SweetAlert2 too.

## Conventions

- Put business logic in **Services**, data access in **Repositories**, request/response shapes in **DTOs**. Models should stay focused on relationships, casts, and scopes.
- Use **`readonly` constructor-promoted properties** for service/listener dependencies (`TransferGuestCartToUser` is the canonical pattern).
- Use the existing **Enums** for any field with a constrained value set — do not introduce string constants alongside them.
- Tests under `tests/Feature/` use `RefreshDatabase` (configured in `tests/Pest.php`); `tests/Unit/` uses plain PHPUnit. There is a global `actingAs()` helper defined in `Pest.php`.
- The `_ide_helper.php` file at the repo root is generated by `barryvdh/laravel-ide-helper` — do not edit it by hand.

## Agent skills

### Issue tracker

Issues live as GitHub issues on `SolonKarandanis/ecomshop`, managed via the `gh` CLI. See `docs/agents/issue-tracker.md`.

### Triage labels

Canonical roles map 1:1 to GitHub label names (`needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`). See `docs/agents/triage-labels.md`.

### Domain docs

Single-context repo — one `CONTEXT.md` and `docs/adr/` at the repo root. See `docs/agents/domain.md`.
