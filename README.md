# مركز المصطفى — The Mostapha Center

The public website and library management system for **مركز المصطفى (The Mostapha Center)**, an Islamic educational and research center with a physical scientific library of roughly 10,000 books. The site is Arabic-first (RTL) and serves Sharia professors, Islamic studies students, researchers, and library visitors, alongside an admin area for library staff.

The most important feature is the **library catalog**: visitors can search for a book, see its details and availability, and get its exact physical shelf location — including a printable QR code that links straight to that location page.

## Technology stack

- **Laravel 13** / **PHP 8.4+**
- **Livewire 4** (native single-file components, `pages::` routing convention — see `CLAUDE.md`)
- **Laravel Fortify** for authentication
- **Tailwind CSS v4** with a custom Material Design 3–inspired token system (`resources/css/app.css`) and **Alpine.js** (bundled with Livewire)
- **MySQL** in production; **SQLite in-memory** for the automated test suite (configured in `phpunit.xml` — no MySQL server is needed to run `php artisan test`)
- **Laravel Scout + Meilisearch** for book search (`Book` model); the test suite uses Scout's in-memory `collection` driver instead (also configured in `phpunit.xml` — no Meilisearch server needed to run tests)
- **IBM Plex Sans Arabic** + **Material Symbols** for typography/iconography
- `endroid/qr-code` for on-demand QR code generation (SVG, no GD/Imagick dependency)

## Requirements

- PHP 8.4+ with the extensions Laravel 13 requires, plus **`zip`** (needed by `maatwebsite/excel`/PhpSpreadsheet to read `.xlsx` files — the book importer will error without it). `gd` is used for favicon/branding assets but not required at runtime.
- Composer 2
- Node.js 18+ / npm
- MySQL 8+ (production/local dev) — SQLite is fine for a quick local trial
- Meilisearch (production/local dev, for real book search) — optional for a quick local trial, since `SCOUT_DRIVER=collection` (the shipped local default, see `.env`) needs no external service

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

- Set `DB_*` to a real MySQL database (or switch `DB_CONNECTION=sqlite` and `touch database/database.sqlite` for a zero-config local trial).
- Set `ADMIN_EMAIL` / `ADMIN_PASSWORD` / `ADMIN_NAME` — this becomes the first super-admin account.
- Set `CENTER_PHONE` / `CENTER_WHATSAPP` / `CENTER_ADDRESS` / `CENTER_MAP_URL` / `CONTACT_EMAIL` to show real contact details on the public Contact page and receive its form submissions (left blank, those sections simply don't render rather than showing placeholder data).
- Set real `MAIL_*` credentials before deploying — `MAIL_MAILER=log` (the default) writes mail to the log file instead of sending it, which is fine for local development only.

```bash
php artisan migrate --seed
npm install
npm run build   # or `npm run dev` while developing
```

`php artisan migrate --seed` seeds exactly one thing outside `local`: the first Super Admin account, from `ADMIN_EMAIL`/`ADMIN_PASSWORD`/`ADMIN_NAME` in `.env`. That's it — no invented categories, sections, authors, or books. A production deployment starts with an **empty catalog** and gets its real ~10,000 books through the Excel importer (see below), not seeded placeholder data, so there's never a risk of the app's own bootstrap data colliding with the center's real taxonomy.

If you'd like a quick starter subject taxonomy and a physical section/shelf layout to try the admin screens before importing real data, those seeders still exist and are production-safe — just opt-in:

```bash
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=LibrarySectionSeeder
```

When `APP_ENV=local`, `migrate --seed` additionally runs both of those plus ~320 realistic demo books/authors automatically, so search and pagination have something to exercise locally — none of this demo data is ever seeded outside `local`.

Log in with the `ADMIN_EMAIL` / `ADMIN_PASSWORD` you set, and you'll land in the admin dashboard at `/dashboard`.

## Development

```bash
composer dev
```

Runs the Laravel dev server, queue listener, log viewer (`pail`), and Vite dev server concurrently. See `CLAUDE.md` for the codebase's architectural conventions (Livewire SFC pages, `⚡`-prefixed blaze-folded components, the `chisel` auth-feature markers still present in a few auth files, etc.) if you're contributing code.

### Running Meilisearch locally (optional, per-machine)

`.env` defaults `SCOUT_DRIVER` to `collection` (no external service, no typo tolerance — see the Search note below), so this step is entirely optional. To get real typo-tolerant search locally too:

- **With Docker**: `docker run -d -p 7700:7700 -v meili_data:/meili_data getmeili/meilisearch --master-key=<your-key>`, then set `SCOUT_DRIVER=meilisearch` / `MEILISEARCH_HOST` / `MEILISEARCH_KEY` in `.env`.
- **On Windows without Docker or admin rights**: download the `meilisearch-windows-amd64.exe` binary from the [Meilisearch releases page](https://github.com/meilisearch/meilisearch/releases) to a **short path** (e.g. `C:\Users\<you>\meilisearch\`) — a deeply nested path can exceed Windows' 260-character `MAX_PATH` limit and break Meilisearch's temp-file writes with a confusing "cannot find the path specified" error. Since `sc.exe`-based Windows Services need admin rights, the lightest no-admin way to keep it running is a hidden launcher (a `.vbs` wrapping `WScript.Shell.Run "start-meilisearch.bat", 0, False`, avoiding a visible console window) copied into the Startup folder (`shell:startup`, i.e. `%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup`) so it starts automatically on login. Check it's up with `curl http://127.0.0.1:7700/health`; stop it with `Stop-Process -Name meilisearch -Force`.

Either way, after it's running: `php artisan scout:sync-index-settings` then `php artisan scout:import "App\Models\Book"`.

## Testing

```bash
composer test
```

Runs, in order: config cache clear → Pint style check → PHPStan (level 7) → the full `php artisan test` suite. Individually:

```bash
composer lint:check     # Pint, check only
composer lint            # Pint, auto-fix
composer types:check     # PHPStan level 7
php artisan test
php artisan test --filter=SomeTestName
```

The test suite always runs against an in-memory SQLite database (see `phpunit.xml`) regardless of your `.env`'s `DB_CONNECTION` — no MySQL server is required to run tests.

## Roles & authorization

Roles are a single nullable `role` column on `users` (`App\Enums\UserRole`), not a separate permissions table — deliberately simple for a single-center site with four staff roles:

| Role | Access |
|---|---|
| `super_admin` | Everything, including user role management (`/admin/users`) |
| `librarian` | Full catalog management: books, authors, categories, library sections/shelves, moving books between locations |
| `editor` | Reserved for future public-content management (see Limitations below) |
| `staff` | Can reach the admin dashboard; no catalog-management access by default |
| *(no role / `null`)* | A registered account with no admin access at all — indistinguishable from a public visitor outside the admin area. Public visitors don't need an account to search the catalog. |

Authorization is enforced at two levels: `Gate::define()` abilities (`access-admin`, `manage-catalog`, `manage-content`, `manage-users`, in `AppServiceProvider`) gate entire route groups in `routes/admin.php`, and Policies (`BookPolicy`, `AuthorPolicy`, `CategoryPolicy`, `LibrarySectionPolicy`) gate individual actions inside each Livewire component via `$this->authorize(...)`. `/admin/users` (super-admin only) can create accounts, edit name/email, change roles, and disable/re-enable a user — a disabled account can't log in (`Fortify::authenticateUsing` in `FortifyServiceProvider`) and an already-logged-in session is force-logged-out on its very next request (`App\Http\Middleware\EnsureAccountIsActive`).

## Architecture notes

- **No Teams/multi-tenancy.** The original Laravel starter kit ships a full SaaS-style Teams system; it was removed entirely in favor of the flat role column above, since this is one physical center, not a multi-tenant product.
- **Physical location model**: `LibrarySection` (a hall/room, e.g. "قاعة العلوم الشرعية") → `Shelf` (belongs to a section) → a `position` string directly on `Book`. There's no separate `ShelfPosition` table — a book's position is just an attribute of the book while it's shelved, not a pre-provisioned physical slot record, so moving 10,000 books around never requires renumbering anything. `Book::location_code` renders this as e.g. `F03-02-45`. Every relocation is recorded in `book_movements` (`App\Actions\Library\MoveBookToLocation`) so staff can answer both "where is this now" and "where was it before."
- **Stable identifiers for QR/barcode**: every `Book` gets a `barcode` (e.g. `MC-XXXXXXXX`) generated once at creation and never tied to its shelf location, so relocating a book never changes its identity. `/books/{book}/qr` renders an SVG QR code (via `endroid/qr-code`) encoding the book's public location URL — printable today; a USB barcode scanner (which types the scanned code as keystrokes, no special hardware integration needed) is a natural next step for the admin "move book" form.
- **Search** (`SearchController`) is Laravel Scout + Meilisearch, indexing title/author/category/publisher/ISBN/editors/commentators/annotators (`Book::toSearchableArray()`, index settings in `config/scout.php`). Category filtering uses Meilisearch's `filterableAttributes` (`category_id`). Browsing without a query term (empty search box, category-only) stays a plain Eloquent query — no need to round-trip to the search engine just to list all books. The test suite swaps in Scout's `collection` driver (in-memory, matches against `toSearchableArray()`'s actual values rather than real DB columns — unlike Scout's `database` driver, which can't handle flattened relation fields like `author`/`category`), so no Meilisearch server is needed for `php artisan test`.
- **Slugs use the original Unicode text**, not ASCII transliteration (`Str::slug($title, '-', null)`) — Arabic titles don't survive `Str::slug`'s default ASCII pass, so book/author/category URLs are Arabic slugs (e.g. `/books/فتح-الباري`), which every modern browser handles natively.
- **Categories can have one level of subcategory** (`Category.parent_id`, self-referencing, nullable) — e.g. "الفقه المقارن" under "الفقه الإسلامي". A category with books or subcategories attached can't be deleted.

## Importing the real book catalog (Excel)

`/admin/import` (librarian/super-admin, same `manage-catalog` gate as the rest of the catalog) is a step-by-step wizard for bulk-loading the center's real spreadsheet — built to adapt to whatever columns the real file has rather than assuming a fixed layout, since spreadsheets from different library systems vary:

1. **Upload** — `.xlsx`, `.xls`, or `.csv`, up to 20 MB (`App\Models\ImportBatch` tracks the batch; the file itself is stored under `storage/app/private/imports/`, never publicly accessible).
2. **Column mapping** — every column in the file is shown with a data preview, alongside a dropdown for what it means (title, author, category, publisher, ISBN, publication year, edition, pages, language, description, barcode/call number, and optionally section code / shelf code / shelf position for physical location). Common Arabic and English headers (`العنوان`/`title`, `المؤلف`/`author`, `الناشر`/`publisher`, `رقم الردمك`/`isbn`, …) are auto-suggested (`App\Support\Import\ColumnGuesser`) but every suggestion is editable — only **العنوان/title** is required. Unmapped columns are ignored.
3. **Category mapping** — every distinct value found in the mapped category column is listed once, and for each one you choose: map to an existing category, create it as a new top-level category, create it as a subcategory of an existing one, or skip (the book imports with no category). Nothing is auto-created without this explicit review, so importing never silently duplicates a category that already exists under a slightly different spelling.
4. **Confirm & import** — dispatches `App\Jobs\ProcessBookImport` on the queue. It re-reads the file in 200-row chunks, each inside its own DB transaction, with a try/catch around every individual row — one malformed row is logged and skipped, never aborts the batch. Progress is shown live (polls every 2s) and survives a closed browser tab, since it's a real background job, not a request.
5. **Report** — final counts (imported / duplicate / validation-failed / no-category / no-author / other-error) plus a per-row list of everything that *wasn't* imported and why, downloadable as CSV so the file can be corrected and the missed rows re-uploaded.

**Duplicate detection** (never on title alone): a row is treated as a duplicate — logged, not imported — if its ISBN matches an existing book's ISBN, or its barcode/call-number column matches an existing barcode, or (only when neither is available) its normalized title + author + edition/year combination matches. This checks both the database and earlier rows in the same file, so a spreadsheet with an accidental repeat doesn't create two records either.

**Author de-duplication**: an author name is matched against existing authors only after trimming/whitespace normalization — an exact match reuses the existing `Author`; anything else becomes a new `Author` row. Names are never fuzzy- or phonetically-matched, so two genuinely different people are never merged on a guess.

**Search indexing**: Scout/Meilisearch syncing is deferred during the whole import (`Book::withoutSyncingToSearch`) and done once at the end for every row actually imported — far fewer round-trips than indexing per row, and the catalog is fully searchable, typo-tolerance included, the moment the batch completes. No manual `scout:import` step needed after a normal import; it's only for bulk-reindexing after direct DB changes (see Deployment below).

**Physical location from the file**: if section-code and shelf-code columns are both mapped, matching `LibrarySection`/`Shelf` rows are found-or-created (same as `LibrarySectionSeeder`'s pattern) and the book is shelved immediately; otherwise it's imported unshelved, same as adding a book manually and shelving it later from `/admin/books`.

Because the import job runs on the queue, a queue worker must be running for imports to process — `composer dev` already runs one locally (`php artisan queue:listen`); see Deployment below for production.

## Deployment (Laravel Forge)

1. Provision the site, point it at this repo, PHP 8.4.
2. Set the `.env` values described in Installation above, plus:
   - `APP_ENV=production`, `APP_DEBUG=false`, a real `APP_KEY` (`php artisan key:generate --force`).
   - `DB_CONNECTION=mysql` with real credentials.
   - `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` are already `database` by default, which is fine at this scale (fewer than 100 concurrent users) — no Redis needed.
   - `FILESYSTEM_DISK=local` is fine for book cover images at this scale; the app only ever talks to storage through the `Storage` facade, so switching to an S3-compatible disk later is a config change, not a code change.
3. Have a Meilisearch instance reachable at `MEILISEARCH_HOST` (a small self-hosted instance is plenty at this scale — e.g. `docker run -d -p 7700:7700 -v meili_data:/meili_data getmeili/meilisearch --master-key=...`), and set `MEILISEARCH_KEY` to its master/API key.
4. Deployment script:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   npm ci && npm run build
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan storage:link
   php artisan scout:sync-index-settings   # applies config/scout.php's filterable/sortable/searchable attributes
   php artisan scout:import "App\Models\Book"
   ```
5. **A queue worker must be running** (`php artisan queue:work`, kept alive via Forge's daemon/Supervisor process manager) — the Excel import (`App\Jobs\ProcessBookImport`) runs on the queue so a ~10,000-row file doesn't tie up a web request. Nothing else currently needs a worker: Scout's search-index syncing runs synchronously on save by design (see `config/scout.php`), so day-to-day book edits don't depend on the worker staying up — only bulk imports do.
6. Never run `DemoBooksSeeder` (or the default `db:seed` in a non-`local` `APP_ENV`, which already skips it automatically) against production — it exists purely to make local development/staging realistic to click through. The default seed path already only creates the Super Admin (see Installation above); the real catalog goes in through `/admin/import`.

## Limitations / not in scope

Implemented per an explicit, deliberately-scoped brief. Left out on purpose, not by oversight:

- **No borrowing/loan system.** `Book.status` (`available` / `borrowed` / `reference_only` / `unavailable`) is a manually-set field, not backed by a `Loan` model with due dates, reservations, or overdue tracking. Add this as its own subsystem if the center wants to track physical lending.
- **No public content CMS.** About/Activities/Donations/Contact are static Blade views (`resources/views/site/`) with real drafted Arabic copy the client should review and adjust — intentionally not a database-backed content model, per the "don't over-engineer" guidance for content that changes rarely. The `editor` role and `manage-content` gate exist for when/if that becomes worth building.
- **Bilingual (ar/en) support is not built**, only scaffolded: `APP_FALLBACK_LOCALE=en` is set and Laravel's own English strings exist as a fallback, but there's no language switcher and most content is Arabic-only text, not translated pairs.
- **Camera-based QR scanning isn't built** — the QR codes are real and scannable with any phone camera app today; an in-admin camera scanner (vs. a $20 USB barcode scanner, which needs no special integration since it types the scanned value as keystrokes) was deliberately deferred as unnecessary complexity.
- **Demo book data is invented** (realistic Arabic Islamic classics, randomly assigned publishers/years/locations) for local development only — see Installation above. The real catalog comes in through the Excel importer (`/admin/import`, see above) for bulk loading, or `/admin/books` for one-off additions/edits afterward.
