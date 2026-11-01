# NewsBureau24

Local Laravel 13 application based on the supplied homepage and masthead PNGs. This implements the brief's **first functional vertical slice**, plus an initial visual frontpage manager. It is not the complete six-stage production CMS.

## Open the local application

- Homepage: <http://127.0.0.1:8000/bn>
- English: <http://127.0.0.1:8000/en>
- Newsroom: <http://127.0.0.1:8000/newsroom>
- Generated local editor/reporter access: `.tools/local-access.txt` (ignored by Git).

Start the server from PowerShell:

```powershell
.\.tools\php\php.exe artisan serve --host=127.0.0.1 --port=8000
```

The project-local PHP 8.4 runtime leaves XAMPP unchanged. Production assets are already compiled in `public/build`. The local database is SQLite; production uses MariaDB/MySQL.

For a fresh checkout with PHP 8.4 and Composer installed:

```sh
composer install
cp .env.example .env
php artisan key:generate
# Configure DB_CONNECTION=sqlite and create database/database.sqlite for local use.
php artisan migrate
php artisan db:seed
npm ci --ignore-scripts
npm run build
php scripts/setup-local.php
php artisan serve
```

The demo seeder refuses to populate production and creates no default administrative password. `scripts/setup-local.php` creates random development credentials only in `APP_ENV=local`. For a real account, run `php artisan newsroom:user editor@example.com --role=editor` and enter a strong password at the prompt.

## Working features

- Server-rendered, responsive newspaper homepage using the actual masthead, locally hosted Bengali fonts and WebP reference artwork. Search, category/district filters, ticker pause controls, article pages, and corresponding published translations.
- Filament 5 / Livewire 4 newsroom: reporter and editor login, article creation, rich text, bilingual tabs, review submission, language-specific publication times, archive/soft-delete recovery, filters and revisions stored in the database.
- Editor working-copy autosave, explicit recovery and optimistic version checks. Autosave never changes the public article. Create the initial draft before autosave begins; an unsaved new article does not yet have server recovery.
- Drag-and-drop **featured image** uploads with file-content/MIME/dimension checks, a 10 MB / 24 MP limit, decoding and WebP re-encoding, EXIF removal, content-hash duplicate detection, searchable library, bilingual alternative text and credits. Originals are not retained. Private media becomes public only when referenced by a published article.
- Initial visual frontpage preset: a lead and up to four supporting stories, SortableJS dragging, keyboard buttons, undo/redo, draft/live separation, saved draft previews at desktop/tablet/mobile widths, publication history and restoration into a working copy. Other homepage sections currently follow categories automatically.
- Scheduled article publishing through a minute-based Artisan task; independent future translation dates remain private until due. Database queue worker runs in bounded cron jobs.
- Newsletter **interest registration**, with explicit consent and no outgoing email; editorial/contact/privacy/tips pages clearly disclose development status. No confidential submission form or analytics tracking is enabled.

## Verification

```powershell
.\.tools\php\php.exe artisan test
npx.cmd playwright test
node scripts/preview.mjs
npm.cmd run build
```

Browser tests require the running local server, Chrome, seeded demonstration content, and `scripts/setup-local.php`. They write a clearly named sample article to the development database. Do not point browser tests at a production instance. Screenshots and reports are written under `artifacts/`.

## Scope still to implement

The remaining brief includes inline/clipboard image blocks, upload batches and resumable progress, cropping/focal points/collections, video and subtitle ingestion, fully reorderable sections, frontpage scheduling, timed promotions, a permission matrix and approval/audit tools, revision comparison/restore UI, assignments/kanban, evidence/timelines/maps, RSS/sitemaps/redirects, newsletter delivery, analytics, password reset mail/2FA and production backup automation. These are not represented by simulated controls.

Production launch also requires real editorial content, verified staff/contact/policy details, licensed source images, mail configuration, retention rules, MariaDB validation on the actual host, deployment review and load/performance testing. No production deployment has been performed.

See [architecture](docs/ARCHITECTURE.md), [cPanel deployment](docs/CPANEL.md) and [implementation status](docs/IMPLEMENTATION.md).
The exact checks and their limits are recorded in [validation](docs/VALIDATION.md).
