# Architecture and hosting audit

## Audited input

The workspace initially contained only two PNG references and `.agent-cache`. No existing application or functionality was replaced. The original images are preserved. `scripts/prepare-assets.py` extracts sample photo regions and converts the masthead to WebP for this development edition; the low-resolution screenshot crops must be replaced by original licensed editorial imagery before launch.

The supplied host offers PHP 8.1–8.5 (8.2 currently selected), MariaDB 10.11.19, and a default server charset of latin1. Select **PHP 8.4** for this application. Use a dedicated database and persistent cPanel database user, not a temporary `cpses_...` phpMyAdmin login. The application explicitly uses utf8mb4 for its database connection. This has not been connected to or deployed on that host.

Local XAMPP provides PHP 8.0.30, so `.tools/php` contains an isolated PHP 8.4 runtime downloaded from windows.php.net and verified against its published SHA-256. `.tools` is development tooling, not part of deployment. Composer and npm caches are scoped to this workspace. Laravel 13.38, Filament 5.10, Livewire 4.4, Tailwind 4, Alpine and SortableJS are locked in dependency files.

## Request architecture

Public `/bn` and `/en` routes render Blade templates. Only published records with a due timestamp for the requested language are visible; missing translations return 404 and have no language-switch link. Public search uses indexed category/district/status/date filters and paginated JSON-title searching. Title searches using LIKE are appropriate for this first slice; at greater scale, add a measured MySQL search strategy without loading the entire newsroom into the browser.

`/newsroom` uses session authentication, Filament and Livewire. The CMS interface uses English; content fields are independent Bangla and English tabs. Reporters see their own stories, may edit their drafts/review submissions, and cannot publish or change the frontpage. Editors can publish, restore soft-deleted stories and manage the frontpage. No public registration exists. Role changes are currently CLI-only.

`ArticleWriter` is the write boundary: ownership, publication permission, field validation, per-language content checks, a transaction and a locked version comparison. The Filament form is not the security boundary. Stored HTML is allowlisted at persistence and again at public rendering. The first slice deliberately disables inline rich-editor attachments; featured media uploads use the separately validated media path.

## Data

- `users`: role and standard Laravel authentication fields.
- `articles`: localized title/summary/body JSON, category/district, reporter, featured media, state, per-language publication timestamps, version, demo flag, soft delete.
- `article_revisions`: snapshots of saved article content and states, actor and timestamps. Restore/diff UI is a later stage.
- `editor_drafts`: separate per-user working copies and their base article version. Livewire debounces text updates; manual save applies content and clears that user's copy. New articles begin recovery after initial creation. Browser-offline journaling for edits that never reach the server is not yet implemented.
- `media`: private file path, source filename, MIME/size/hash, localized alternative text and credit. Referenced images cannot be deleted, including references from soft-deleted stories.
- `frontpages`: mutable working draft, immutable published/archived layouts, publishing actor/version/timestamps. The first preset governs only lead/supporting slots. A database transaction protects publication and retains the previous live layout.
- `newsletter_subscribers`: consented email and preferred language. Interest registration only.
- Standard Laravel sessions/cache/jobs tables. No Redis, persistent worker, Node process or WebSocket service is needed on production hosting.

## Security and privacy limits

Private uploads are served through an authorization-checked controller, never a public storage symlink. File names are random, content is decoded/re-encoded, and active file formats such as SVG are rejected. Access checks prevent another reporter's upload hash from granting access. Public image requests are allowed only while the associated article has a published translation.

Sessions/CSRF, rate-limited Filament login, request validation, escaped titles and SQL parameter binding are provided by the framework. Security headers are added centrally. HTTPS and secure encrypted sessions are configured for production. Evidence storage, confidential tip submission, granular permissions, comprehensive activity auditing and two-factor authentication need separate implementation; the current site does not claim to provide them.

## Design system

White `#fff`, ink `#101010`, crimson `#b51222`, muted `#666`, fine rules `#e5e5e5`. Newspaper proportions, square photo areas, restrained category tags and compact chronology follow the reference. Noto Serif Bengali is self-hosted for headlines; Noto Sans Bengali for interface text, with native Latin fallbacks. Public code does not load Filament or SortableJS. The ticker pauses under reduced-motion preferences and when requested. Explicit image dimensions, mobile reflow and narrow-screen navigation preserve layout stability.

Core Web Vitals targets remain to be measured on representative Bangladeshi networks; no field-performance result is claimed.
