# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

A Laravel 13 + Livewire 4 application, built from the official `laravel/livewire-starter-kit` (Single-File Component variant) with a Teams feature layered on top. Frontend is Tailwind CSS v4 + the Flux UI component library, built with Vite.

## Commands

Run from the project root (Windows/PowerShell environment; `php` and `composer` must be on PATH).

- `composer dev` — run the full local dev stack (Laravel server, queue listener, log viewer (`pail`), and Vite) concurrently via `php artisan dev`.
- `npm run dev` — Vite dev server only.
- `npm run build` — build frontend assets.
- `composer lint` — fix code style with Pint.
- `composer lint:check` — check code style without fixing (Pint `--test`).
- `composer types:check` — static analysis via PHPStan/Larastan (level 7, see `phpstan.neon`).
- `composer test` — full CI suite: config clear, lint check, types check, then `php artisan test`.
- `php artisan test` — run the test suite directly.
- `php artisan test --filter=test_name_or_class` — run a single test.
- `php artisan test tests/Feature/Teams/TeamTest.php` — run a single test file.
- `composer ci:check` — what CI runs (`.github/workflows/tests.yml`), equivalent to `composer test`.

There is no separate `phpunit` binary invocation expected — always go through `php artisan test` / `composer test` so Laravel's test bootstrapping runs correctly.

## Architecture

### Livewire 4 single-file "pages" convention

Routable Livewire components live under `resources/views/pages/` and are registered as the `pages::` view namespace by Livewire 4 itself (no manual `loadViewsFrom` — this is a framework convention, not app config). Routes reference them by dotted view name via the `Route::livewire()` macro, e.g. `Route::livewire('settings/teams', 'pages::teams.index')`.

Files prefixed with `⚡` (e.g. `⚡profile.blade.php`) are components that `livewire/blaze` folds into their parent template at build/render time for performance — this is a build optimization, not a naming quirk to "fix". Don't rename or remove the `⚡` prefix without understanding blaze folding.

Single-file components follow Laravel's anonymous class syntax: a `new class extends Component { ... }` block at the top of the `.blade.php` file, followed by the Blade template. Reusable Livewire actions/logic go in `app/Livewire/`.

### Chisel feature-toggle markers

This starter kit uses `laravel/chisel` (see `chisel.php`, `chisel-paths.php`) to let a project owner select which auth features to keep (registration, email verification, 2FA, passkeys, password confirmation) at install time via `php artisan install:features`. Throughout the codebase you'll see comment markers like:

```php
/* @chisel-registration */
...code...
/* @end-chisel-registration */
```

and the Blade equivalent `{{-- @chisel-x --}}` / `{{-- @end-chisel-x --}}`. These mark code blocks tied to a specific feature. **Do not strip these markers** when editing surrounding code — they are load-bearing for the installer script, even after installation. If a feature was already decided against for this project, its blocks were physically removed by chisel (not just commented out), so absence of a marker for a feature means that feature was declined, not forgotten.

### Teams / multi-tenancy

This app adds a full Teams system not present in the vanilla starter kit:

- **Models**: `App\Models\Team`, `App\Models\Membership` (pivot with `role`), `App\Models\TeamInvitation`. `App\Models\User` uses the `App\Concerns\HasTeams` trait for team relationships, current-team switching, and permission checks.
- **Roles/permissions**: `App\Enums\TeamRole` (e.g. Owner) and `App\Enums\TeamPermission` define what a role can do; `HasTeams::hasTeamPermission()` / `toTeamPermissions()` are the check points. `App\Policies\TeamPolicy` is the authorization entry point for team actions (view/update/delete/addMember/removeMember/inviteMember/etc).
- **Current-team routing**: team-scoped routes are prefixed with `{current_team}` (see `routes/web.php`). `App\Http\Middleware\SetTeamUrlDefaults` sets the `current_team`/`team` URL defaults from the authenticated user's current team so route/URL generation doesn't need it passed explicitly. `App\Http\Middleware\EnsureTeamMembership` (applied per-route, optionally with a `?minimumRole` param) authorizes that the user belongs to the team in the route and auto-switches `currentTeam` when navigating into a different team's URL.
- **Invitations**: `App\Models\TeamInvitation` + `App\Notifications\Teams\TeamInvitation`. Pending invitations are resolved by `?invitation=` query param during login/register (see `FortifyServiceProvider::teamInvitation()`) and surfaced via `resources/views/components/team-invitation-alert.blade.php`.
- **Slugs**: `App\Concerns\GeneratesUniqueTeamSlugs` handles unique slug generation (e.g. `acme`, `acme-1`, `acme-10`) used when creating teams.

### Auth (Fortify)

Auth is handled by `laravel/fortify`, wired up in `App\Providers\FortifyServiceProvider`: custom views (pointing at `pages::auth.*`), custom actions (`App\Actions\Fortify\*`), custom responses (`App\Http\Responses\*` — these redirect into the user's current team context via `RedirectsToCurrentTeam`), and rate limiters for login/2FA/passkeys. Passkey support (`@laravel/passkeys` JS + `laravel/passkeys` equivalent server-side glue) is one of the chisel-toggleable features.

### Testing

Feature tests under `tests/Feature/` mirror this structure (`Auth/`, `Settings/`, `Teams/`). Livewire components are tested via `Livewire::test('pages::...')` (the dotted `pages::` view name, not a class name). `Tests\TestCase::skipUnlessFortifyHas()` skips a test when a given Fortify feature was chiseled out. Tests use SQLite in-memory (`RefreshDatabase`), array session/cache/mail drivers, and sync queue (see `phpunit.xml`).

---

# Project Preferences

These preferences apply to every task unless explicitly overridden.

## Coding Style

- Follow Laravel conventions.
- Write code in the style commonly seen in the Laravel framework and Laracasts.
- Prefer expressive, readable code over clever code.
- Keep controllers thin.
- Use Form Requests for validation.
- Use Eloquent relationships instead of unnecessary queries.
- Prefer Laravel features over custom abstractions.
- Avoid over-engineering.

## Architecture

- Introduce Services only when business logic becomes sufficiently complex.
- Do not create repositories, DTOs, actions, traits, or patterns unless there is a clear benefit.
- Prefer the simplest Laravel-native solution.

## Workflow

Before implementing a feature:

1. Explain the proposed approach.
2. Mention important trade-offs if they exist.
3. Wait for approval before making large architectural changes.

## Safety

Do not:

- run destructive Git commands
- delete files
- reset the database
- update dependencies

without asking first.

## Code Quality

Before considering a task complete:

- Check for Laravel best practices.
- Check for readability.
- Check for security concerns.
- Check for N+1 query issues.
- Check for unnecessary complexity.
