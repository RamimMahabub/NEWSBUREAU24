# Validation — 25 October 2026

## Passed locally

- **16 PHPUnit tests / 67 assertions:** article draft/review/publication, ownership and publishing permission boundaries, per-language visibility, image re-encoding/private access/deduplication, disguised executable rejection, HTML allowlisting, conflicting article versions, draft/live frontpage separation and history, stale frontpage versions, unpublished placement rejection, scheduled publication, Filament creation, working-copy recovery, layout restoration, search/structured metadata and newsletter consent.
- **3 Playwright browser scenarios:** responsive homepage/search/English articles; reporter image upload and review submission followed by editor publication, recovery and lead promotion; actual pointer dragging with undo. These passed together after the compiled drag-handler fix. A subsequent focused public-page run also verified the card-query optimization retains full article bodies.
- Responsive screenshots and overflow checks at **1440, 1024, 768, 375 and 320 pixels**. No horizontal page overflow was observed. Desktop and mobile screenshots were visually inspected.
- Vite production asset build, Composer manifest validation and locked dependency resolution.
- Laravel config, route and Blade view cache compilation. Local caches were cleared afterwards to keep development changes visible.

## Artifacts

`artifacts/homepage-desktop.png`, `artifacts/homepage-1024.png`, `artifacts/homepage-768.png`, `artifacts/homepage-375.png`, `artifacts/homepage-320.png` and `artifacts/newsroom-frontpage.png`. Browser traces/reports are generated under `artifacts/` and are development artifacts, excluded from deployment.

## Not validated

The tests use the local SQLite database and PHP 8.4 runtime. The actual cPanel/MariaDB 10.11 host has not been accessed. No production deployment, live-email test, slow-network field measurement, Core Web Vitals claim, load test, accessibility audit, full security audit or backup-restoration drill has been performed. Later-stage requirements are tracked in `IMPLEMENTATION.md`.
