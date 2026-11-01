# cPanel deployment guide — first slice

Deployment is not performed by this project setup. Complete the remaining acceptance work before a public launch.

Framework baseline: [official Laravel deployment requirements](https://laravel.com/framework/docs/13.x/deployment) and [support policy](https://laravel.com/framework/docs/13.x/releases).

## Host settings

1. In MultiPHP Manager select PHP **8.4** for the domain. Enable ctype, curl, DOM/XML, fileinfo, filter, GD with WebP, hash, intl, mbstring, OpenSSL, PDO MySQL, session, tokenizer and zip. Laravel 13 requires PHP 8.3+, but the dependency lock in this project is prepared and tested on PHP 8.4.
2. Create a dedicated MariaDB database/user through cPanel's database wizard. Grant access only to that application's database. Use utf8mb4 / utf8mb4_unicode_ci. The server-wide latin1 default must not be used for Bangla tables.
3. Store the application at `/home/CPANEL_USER/newsbureau24`. Set the domain document root to `/home/CPANEL_USER/newsbureau24/public`.
4. If the provider cannot change document roots, keep the entire application outside `public_html`, copy only the **contents of public/** into the domain's public directory, and adjust the two bootstrap paths in `index.php` to the private application directory. Do not copy `.env`, storage, vendor, source code, database files or backups into the web root. Ask the host to configure the proper root if this arrangement is unsupported.

## Prepare outside the hosting server

Build on Linux CI or a compatible local PHP 8.4 environment with all required extensions:

```sh
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci --ignore-scripts
npm run build
php artisan filament:assets
composer check-platform-reqs --no-dev
```

Upload app/bootstrap/config/database/resources/routes/vendor, `artisan`, composer files, and `public` including **public/build and all published Filament JS/CSS/fonts**. Include empty writable storage subdirectories. Do not upload `.tools`, `.agent-cache`, node_modules, artifacts, local SQLite database, local credentials, reference PNGs or development `.env`. Composer and Node are unnecessary on the host after preparation.

Copy `.env.production.example` to a private `.env` and configure the real domain/database. Generate `APP_KEY` once in the prepared environment with `php artisan key:generate --show`, then transfer it privately; retain it for backups and restores. Never regenerate the key during a routine update. Do not upload cached local configuration from `bootstrap/cache`.

## One-time setup

With Terminal/SSH, from the private application directory:

```sh
php artisan migrate --force
php artisan newsroom:user editor@example.com --role=editor
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Use the provider's PHP 8.4 executable, which may be `/opt/cpanel/ea-php84/root/usr/bin/php`; confirm the actual path. Storage and bootstrap/cache must be writable by the PHP user; use normal 755/775 directory and 644/664 file permissions as the host requires, never world-writable 777. Do not run the demo seeder in production.

Without Terminal/SSH, use a temporary cPanel cron entry to run migrations with the provider's PHP path, directing output to a private log. Run `newsroom:user ... --generate` once through the same mechanism to generate an initial password in a private log, retrieve it through cPanel File Manager, then remove the temporary cron entry and password log. Never add a public web migration/installer route. Prepare caches through a temporary private CLI cron after host configuration is in place, or run uncached until the host can help.

## Ongoing cron

```cron
* * * * * cd /home/CPANEL_USER/newsbureau24 && /opt/cpanel/ea-php84/root/usr/bin/php artisan schedule:run >> /home/CPANEL_USER/newsbureau24/storage/logs/scheduler.log 2>&1
```

The scheduler publishes due scheduled stories and processes the database queue for up to 50 seconds, then exits. Confirm the hosting process-time limits, minimum cron interval and CLI PHP version. A permanent worker is not required. Public translation timestamps still protect future translations independently. Automatic frontpage scheduling and timed promotions are not implemented yet.

## Backups, restoration and updates

Use cPanel Backup/Backup Wizard for consistent database exports and private application/storage backups. Preserve `.env` / APP_KEY separately in access-controlled backup storage. Never place backups in public. Test restoration into a separate staging database before relying on it; production backup automation is a later implementation stage.

For an update: back up DB/uploads/key, prepare a new dependency/asset build, enable maintenance with `php artisan down`, upload the release, run `php artisan migrate --force`, refresh config/route/view caches, and run `php artisan up`. A database backup is required before schema changes; code rollback alone cannot undo arbitrary database changes.

For disaster recovery: restore the matching application release, database and private media; restore the original APP_KEY and host settings; run `php artisan optimize:clear`, rebuild caches and validate both languages, private media, authentication and article publishing before reopening traffic.

## Deployment validation still required

- Test Bangla round-trip storage against the actual MariaDB 10.11 host.
- Verify HTTPS, secure cookies, actual extension availability and private filesystem boundaries.
- Run publishing/permissions/upload tests on a staging copy; check cron publication and private logs.
- Configure mail and real editorial contacts/policies. Password reset mail and newsletters are not enabled in this first slice.
- Replace demonstration artwork/content and test mobile/network performance.
