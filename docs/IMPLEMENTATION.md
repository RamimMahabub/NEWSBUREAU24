# Implementation status

This is an executable foundation and first vertical slice, not a completed production platform.

| Brief stage | Delivered here | Remaining |
|---|---|---|
| 1 — Foundation | Host/repo audit, Laravel/Blade architecture, database schema, responsive reference-based homepage, self-hosted fonts, bilingual routes, article pages/search | Host MariaDB verification, original editorial imagery, measured network performance |
| 2 — Editorial | Filament/Livewire auth, reporter/editor boundaries, bilingual rich text and publication, review state, scheduled dates, revisions stored, working-copy autosave/recovery/version checks | Granular permission matrix, approval/event audits, revisions UI, offline journaling, reset mail/2FA, assignments, slash commands, block rearrangement, keyboard shortcut suite |
| 3 — Media | Featured-image drop/upload, private storage, validated WebP conversion, metadata, duplicate checks, searchable library, referenced-media delete protection | Inline/pasted images, batch progress/retry, collections, editing/focal points, responsive variants, replacement, video/audio/documents |
| 4 — Frontpage | One visual preset, lead/supporting slots, Sortable dragging and keyboard controls, draft/live/version conflict protection, preview, undo/redo/history/restore | Movable sections, scheduling, alternate presets/treatments, promotion placements and expiration |
| 5 — Investigations | Reference-based category presentation and safe demonstration content | Evidence/timelines/citations/maps, video workflows, reconstruction templates, source protection infrastructure |
| 6 — Deployment | Production asset build, core feature/browser tests, cPanel guide, bounded cron tasks, production env example | Actual deployment, all remaining brief acceptance tests, MariaDB integration tests, security/load/performance review, backup automation |

## Manual first-slice demonstration

1. Sign in as local reporter. In **Image library**, upload a JPEG/PNG/WebP with alternative text and credit.
2. In **Stories**, create a Bangla headline/body, select the uploaded featured image, and choose **Submit for review**. Create the draft; it opens in the editor. Subsequent writing autosaves into a separate working copy. **Save changes** applies changes to the stored article.
3. Sign in as editor, open the submitted story, review it, set status **Published**, and enter a Bangla publication date. Leave English date empty to keep that translation private. Save changes.
4. In **Frontpage**, search the published library. Drag the story onto the lead position, or use **Set as lead**. Save draft, toggle preview and inspect desktop/tablet/mobile widths. Publish now.
5. Open `/bn` as an anonymous reader. The lead and image are served from the database/private media controller. The English article route stays unavailable until the corresponding English title/body/date is published.
6. Change positions, undo/redo, save or publish, and restore an earlier layout into the working copy from publication history.

The Video area contains reference artwork and article templates only; it explicitly states that no playable video was supplied. Editor's picks is an editorial list, not invented readership analytics. Newsletter interest registration stores consented addresses without pretending to send a newsletter.
