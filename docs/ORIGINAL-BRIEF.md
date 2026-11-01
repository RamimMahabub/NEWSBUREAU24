# NewsBureau24 — Complete Website & Advanced Newsroom CMS Development Brief

## ROLE & OBJECTIVE

Act as a senior full-stack architect, editorial platform engineer, premium UI/UX designer, and performance optimization specialist.

Build **NewsBureau24 (নিউজ ব্যুরো ২৪)**, a production-ready, bilingual Bangla/English crime and investigative journalism platform targeting Bangladesh.

This is not a basic news blog or WordPress-style dashboard. The objective is to combine:

1. Premium traditional newspaper design and editorial hierarchy.
2. A fast, mobile-first Bangladeshi news reading experience.
3. Advanced investigative journalism and multimedia storytelling.
4. An exceptionally smooth, visual, drag-and-drop Newsroom CMS.
5. Simple and reliable deployment on cPanel shared hosting.

**IMPORTANT: The attached homepage screenshot is the primary visual reference. Recreate its overall visual language, grid proportions, spacing, masthead placement, typography hierarchy, navigation, colors, content cards, and section structure as faithfully as possible.**

Do not substitute a generic news template, dashboard design, or futuristic dark interface.

All supplied headlines and images in the reference are demonstration content, not verified news.

---

# 1. MANDATORY TECHNOLOGY STACK

The production website must run on traditional cPanel shared hosting.

Preferred technologies:

- Backend: Laravel, latest compatible supported stable release.
- Language: PHP supported by the hosting environment.
- Database: MySQL.
- Frontend: Laravel Blade.
- CSS: Tailwind CSS, compiled for production.
- Lightweight UI interactions: Alpine.js.
- Reactive admin components: Livewire.
- Administration foundation: Filament, if compatible with the selected Laravel/PHP versions.
- Drag-and-drop: SortableJS or another lightweight, maintained and accessible solution.
- Rich-text editing: Tiptap or an equivalent actively maintained editor.
- Cache: Laravel file/database cache initially.
- Background tasks: Laravel Scheduler and database-backed queues using cPanel cron.
- File management: Laravel Filesystem with local storage and optional external object-storage support.
- Video: HTML5 playback initially, with optional Cloudflare Stream or Bunny Stream integration.

Do not require a permanently running Node.js process, Redis server, WebSocket server, Docker container, or VPS in production.

Node.js may be used locally or in CI to compile frontend assets.

Before implementation, inspect the existing repository and hosting requirements. Preserve working functionality and avoid unnecessary rewrites.

---

# 2. PUBLIC WEBSITE DESIGN

## Brand Identity

Name: **NewsBureau24**

Bengali: **নিউজ ব্যুরো ২৪**

Tagline: **FACTS | INVESTIGATIONS | A SAFER BANGLADESH**

Colors:

- Primary black: #101010
- Background: #FFFFFF
- Accent crimson: #B51222
- Secondary gray: #666666
- Border gray: #E5E5E5

Use the supplied ornate NewsBureau24 blackletter masthead, with the red "24," as the brand reference.

The logo must retain its proportions, legibility, and premium editorial character.

Use professional Bengali fonts with excellent readability. Consider Noto Serif Bengali for headlines and Noto Sans Bengali for interface text, with refined English serif and sans-serif pairings.

## Homepage Structure

Follow the reference screenshot:

1. **Utility bar:** Current date, About, Contact, Newsletter, language selector, and search.
2. **Centered masthead:** NewsBureau24 logo and tagline.
3. **Primary navigation:** Latest, National, Crime, Courts, Investigations, Districts, Special Reports, Video, Data & Maps, Opinion.
4. **Breaking news ticker:** Compact, accessible, pauseable, with links to articles.
5. **Lead story area:** Large photographic feature with headline, supporting details, and four secondary stories.
6. **In Focus strip:** Trending investigative topics and important cases.
7. **Investigations section:** Large visual case feature, smaller investigation stories, and multimedia elements.
8. **Latest news sidebar:** Chronological updates.
9. **Most-read articles:** Automatically calculated, with editorial override options.
10. **Video section:** Featured documentary with supporting video cards.
11. **District news:** Location-based news discovery.
12. **Data & Maps:** Maps, investigations, statistics, and visual reports.
13. **Newsletter subscription:** Refined and unobtrusive.
14. **Footer:** Editorial policies, corrections, privacy, staff, contact, and legal links.

The homepage should look extremely close to the supplied screenshot.

Do not replace the editorial layout with generic rounded cards, excessive gradients, giant shadows, or unnecessary animations.

## Responsive Design

Implement carefully designed layouts for:

- Large desktop: 1440px and above.
- Laptop: 1024–1439px.
- Tablet: 768–1023px.
- Mobile: 320–767px.

On mobile, rearrange content intelligently instead of shrinking desktop columns.

Preserve strong headline hierarchy, readable Bengali text, fast image delivery, and touch-friendly navigation.

---

# 3. ADVANCED NEWSROOM CMS

This is the highest-priority functional requirement.

Build a dedicated newsroom administration application with polished interactions, low latency, keyboard shortcuts, useful feedback, and reliable autosaving.

The interface must be smooth enough for editors publishing dozens of stories per day.

## 3.1 News Publishing Workspace

Provide a professional article editor supporting:

- Create, edit, duplicate, archive, restore, and delete articles.
- Bangla and English article versions.
- Draft, review, approved, scheduled, published, updated, and archived states.
- Headline, subheadline, summary, and article body.
- Author, category, location, tags, and investigation association.
- Featured image and article gallery.
- Video attachments and embeds.
- Publication and last-updated timestamps.
- Reading-time estimation.
- SEO title, description, and social sharing image.
- Related articles.
- Internal editorial notes.
- Custom URL slugs.
- Article revisions and restore history.
- Bulk actions.

Editors must be able to write, paste, rearrange, and publish without page reloads wherever practical.

## 3.2 Butter-Smooth Rich Text Editor

Build an excellent writing experience comparable in polish to modern document editors.

Required:

- Rich-text typing and formatting.
- Paste from Word, Google Docs, websites, and plain text.
- Intelligent cleanup of copied formatting.
- Headings, paragraphs, lists, tables, quotes, links, and dividers.
- Inline images and captions.
- Image alignment and resizing.
- Block reordering through drag-and-drop.
- Slash-command insertion menu.
- Floating formatting toolbar.
- Undo and redo.
- Keyboard shortcuts.
- Autosaving with clear save status.
- Draft restoration after connection interruption.
- Word count and reading time.
- Media embedding.
- Link previews where practical.
- Search within the document.

Pasting an image from the clipboard should initiate an image upload where supported.

Dragging an image directly into the article should place it at the intended block position.

Validate and sanitize pasted HTML and embedded content to prevent XSS and unsafe scripts.

The editing experience must remain responsive while large stories are being edited.

---

# 4. DRAG-AND-DROP MEDIA LIBRARY

Create a professional media management application.

Users must be able to:

- Drag files from their desktop into the browser.
- Upload multiple files simultaneously.
- Paste screenshots directly from the clipboard.
- See individual upload progress.
- Cancel and retry uploads.
- Browse media in grid and list views.
- Search files instantly.
- Filter by image, video, audio, document, uploader, date, and tag.
- Create folders or collections.
- Move files between collections.
- Drag images into article content.
- Select multiple files.
- Replace an image without breaking its references.
- Edit filenames and metadata.
- Crop, rotate, resize, and choose focal points.
- Add Bengali and English alternative text.
- Add photo credit, caption, copyright, and source details.
- See where each file is currently used.
- Prevent accidental deletion of referenced media.

Generate optimized thumbnails and responsive image sizes.

Use WebP/AVIF where supported, with suitable fallbacks.

Implement sensible upload size limits, MIME validation, file-content checks, and secure filenames.

Support duplicate detection using content hashes.

For large uploads, implement resumable/chunked uploads only if reliably compatible with the hosting setup; otherwise provide a clear retry mechanism.

---

# 5. VISUAL FRONTPAGE BUILDER

This is one of the most important features.

**I want to organize the homepage visually through drag-and-drop, without writing code.**

Build a full visual frontpage management workspace.

## Layout Manager

Display a representation of the actual NewsBureau24 homepage inside the CMS.

Editors should be able to:

- Drag articles from a news library onto the homepage.
- Move an article between sections.
- Reorder articles within sections.
- Swap two story positions.
- Select a new lead story.
- Resize or choose predefined featured-story layouts.
- Organize story collections.
- Add, hide, reorder, and restore sections.
- Change the number of visible articles in a section.
- Control promotional story placements.
- Pin priority news.
- Unpin or automatically expire promotions.
- Choose alternate headline and image treatments for homepage cards.

Provide predefined layout presets based on the approved reference design.

The layout must remain visually consistent. Editors should not be able to accidentally break the responsive grid.

## Drag-and-Drop Interactions

Make drag-and-drop exceptionally polished:

- Immediate visual response.
- Smooth movement and reordering.
- Visible drop-zone indicators.
- Drag previews.
- Auto-scroll near container edges.
- Keyboard-accessible alternatives.
- Touch support.
- Undo/redo for layout changes.
- Confirmation for destructive actions.
- Clear successful-save indicators.

Use optimistic interface updates, but roll back gracefully if the server rejects a change.

Avoid unnecessary full-page reloads.

## Draft vs Live Homepage

Maintain separate states:

1. Live homepage.
2. Working draft.
3. Scheduled homepage.
4. Archived homepage versions.

Editors must be able to preview the draft at desktop, tablet, and mobile sizes.

Include:

- Save draft.
- Preview changes.
- Publish now.
- Schedule publication.
- Restore previous version.
- View change history.
- Compare published and draft layouts.

Prevent conflicting edits through appropriate version checks and conflict resolution.

Only authorized users may publish homepage changes.

---

# 6. ARTICLE PROMOTION & DISTRIBUTION

Build a centralized promotion manager.

An editor should be able to select any story and choose:

- Promote as lead story.
- Feature in Investigations.
- Feature in Crime.
- Place in Breaking News.
- Add to In Focus.
- Pin to a category.
- Highlight in District News.
- Promote in Video.
- Include in newsletter.
- Prepare social media sharing.

Allow promotions to have start and expiration times.

When a promotion expires, restore the predefined normal ordering rules.

Support editorial ordering alongside automated chronological and popularity-based ordering.

Avoid a complicated or unreliable ranking system.

---

# 7. BILINGUAL PUBLISHING

Bangla should be the default language.

English should be a complete alternative experience, not merely a translated navigation bar.

Use separate localized article content and URLs:

- `/bn/...`
- `/en/...`

Required:

- Independent Bangla/English headlines.
- Separate body content and summaries.
- Localized navigation.
- Localized image captions where applicable.
- Localized category names.
- Localized SEO metadata.
- Translation availability indicators.
- Separate language publication status.
- Language switching between corresponding published translations.
- `hreflang` and canonical URL support.

Editors should see both language versions within the same article management workspace.

An unpublished English translation must not accidentally become publicly accessible.

---

# 8. VIDEO NEWSROOM

Create a multimedia publishing system.

Features:

- Drag-and-drop MP4 upload.
- Video title and description.
- Thumbnail and poster selection.
- Chapters.
- Subtitle tracks.
- Bengali and English captions.
- Transcript support.
- Related articles.
- Video categories.
- Featured video placement.
- Responsive video player.
- Fullscreen and playback-speed controls.
- Picture-in-picture when supported.
- Shareable video pages.

Support cPanel-hosted MP4 files for limited initial use.

Create a clean abstraction that can later use a dedicated streaming provider without changing existing article content.

Do not attempt resource-intensive video transcoding on shared hosting.

Use `preload="metadata"` or equivalent lightweight defaults and lazy-load offscreen video players.

---

# 9. INVESTIGATIVE JOURNALISM MODULE

Build dedicated templates for long-form investigations.

Each investigation may contain:

- Case introduction.
- Timeline of verified events.
- Evidence references.
- Documents and source citations.
- Photo gallery.
- Documentary video.
- Interactive map.
- Related reporting.
- Methodology.
- Corrections and updates.
- Publication date and revision history.

Use MapLibre for suitable geographic investigations.

Support an optional Three.js reconstruction component, loaded only on pages that need it.

Clearly separate verified evidence, allegations, analysis, and reconstructions.

Do not automatically publish confidential source materials.

Develop specialized visual reporting progressively without adding excessive weight to the standard homepage.

---

# 10. NEWSROOM ROLES & APPROVALS

Implement role-based access control.

Suggested roles:

- Super Admin.
- Editor-in-Chief.
- Editor.
- Reporter.
- Video Producer.
- Contributor.

Example workflow:

Reporter creates story → Editor reviews → Editor-in-Chief approves → Story is published or scheduled.

Permissions must be granular.

A reporter must not be able to publish or alter the live homepage unless explicitly authorized.

Include:

- Editorial assignments.
- Internal comments.
- Revision comparison.
- Article ownership.
- Approval logs.
- Publication history.
- User activity auditing.
- Conflict handling for simultaneous editors.
- Optional two-factor authentication for privileged accounts.

---

# 11. ORGANIZATION & SEARCH

The CMS must support large amounts of content.

Provide:

- Global article search.
- Filters by author, section, district, date, status, and language.
- Saved filters.
- Bulk editing.
- Advanced tag management.
- Category hierarchy.
- District and upazila organization.
- Related investigations.
- Duplicate-story warnings.
- Scheduled-content calendar.
- Kanban-style editorial workflow.
- Archive and soft-delete recovery.

Use appropriate database indexes and pagination.

Do not load thousands of articles into the browser just to implement drag-and-drop.

---

# 12. SEO, MONETIZATION & ANALYTICS

Implement:

- Search-engine-friendly URLs.
- XML sitemaps.
- Google News-compatible publication metadata.
- NewsArticle structured data.
- Open Graph and social previews.
- Canonical URLs.
- Breadcrumbs.
- Author profiles.
- Publisher information.
- RSS feeds.
- Redirect management.
- Related-story recommendations.
- Newsletter management.
- Clearly labeled advertising slots.
- Editorially controlled promotional placements.
- Basic analytics and article performance dashboards.

Advertising must not disrupt the premium editorial layout.

Keep advertisements visibly separate from journalism.

Implement privacy-conscious analytics and avoid assuming third-party tracking is automatically permitted.

---

# 13. SECURITY & EDITORIAL INTEGRITY

This is a crime and investigative journalism publication.

Security and editorial accountability are mandatory.

Implement:

- CSRF protection.
- XSS and SQL injection defenses.
- Secure authentication and authorization.
- Login rate limiting.
- Upload validation.
- Safe media storage.
- Server-side validation for all administrative changes.
- Detailed permission checks.
- Admin activity logging.
- Secure password resets.
- HTTPS-only production configuration.
- Backup and restoration procedures.
- Correction notices.
- Article update history.
- Editorial source-attribution fields.

For confidential tips, provide a carefully designed submission process with encrypted storage, retention rules, limited access, and abuse prevention.

Do not claim a normal website form provides guaranteed anonymity. Provide a separate plan for genuinely sensitive source submissions using appropriate specialist infrastructure.

Do not fabricate reports, timestamps, allegations, crime statistics, photographs, or sources.

Use clearly marked demonstration content during development.

---

# 14. PERFORMANCE REQUIREMENTS

The website must perform well on Bangladeshi mobile networks and shared hosting.

Priorities:

- Server-rendered public pages.
- Minimal frontend JavaScript.
- Responsive images.
- Lazy loading.
- Efficient database queries.
- Pagination.
- Caching for high-traffic pages.
- Cache invalidation after publishing.
- Compiled, minified assets.
- No unnecessary JavaScript frameworks on public pages.
- No heavy animation libraries on the homepage.
- Stable layouts without image-related shifting.
- Optimized Bangla font loading.

Target good Core Web Vitals, including LCP around or below 2.5 seconds, INP at or below 200 ms, and CLS at or below 0.1 at the 75th percentile, under representative conditions.

The administration interface must prioritize perceived responsiveness without sacrificing data correctness.

Use debounced autosaving, lazy-loaded media results, and asynchronous UI updates where appropriate.

Never lose article content silently.

---

# 15. CPANEL DEPLOYMENT

The complete production system must work on shared hosting.

Requirements:

- MySQL configuration.
- Secure Laravel directory structure.
- Environment configuration.
- Public document root pointed to Laravel's `public` directory.
- Writable storage and cache directories.
- Compiled frontend assets.
- Cron-based Laravel scheduling.
- Database-backed queues when needed.
- Backup and recovery commands.
- Production logging.
- Cache management.
- Deployment documentation.

Do not assume SSH, Composer, Node.js, or long-running processes are available on the production server.

Provide alternatives for preparing dependencies and assets locally or through CI before uploading them.

Never expose `.env`, private evidence, backups, logs, or application source files through the web root.

Use the hosting provider's supported PHP configuration.

---

# 16. REQUIRED USER EXPERIENCE DETAILS

Pay special attention to interaction quality.

Every button, tab, dropdown, modal, uploader, and sortable list must have real functionality.

Provide:

- Skeleton loading states.
- Clear error messages.
- Nonintrusive toast notifications.
- Save-state indicators.
- Progress bars.
- Keyboard shortcuts.
- Accessible focus states.
- Confirmation for irreversible actions.
- Undo where appropriate.
- Responsive modal dialogs.
- Search-as-you-type.
- Empty-state guidance.
- Drag handles.
- Smooth list reordering.
- Consistent interaction patterns.

Animations should be subtle, ideally around 120–220 ms for small interface transitions, with reduced-motion support.

Prioritize reliability and responsiveness over decorative effects.

---

# 17. TESTING & ACCEPTANCE CRITERIA

Create automated tests for:

- Authentication and authorization.
- News creation and editing.
- Autosave and recovery.
- Language-specific publishing.
- Media uploads.
- Media permissions.
- Drag-and-drop homepage changes.
- Homepage version restoration.
- Scheduled publication.
- Article promotion expiration.
- SEO metadata.
- Search and filtering.
- File upload security.
- Queue and cron workflows.

Add browser-level end-to-end tests for the most important editor workflows.

Test the website at mobile, tablet, and desktop sizes.

**Critical demonstration scenarios:**

1. A reporter creates a Bangla article, pastes formatted text, drags in images, saves, and submits it for review.
2. An editor opens the article, changes its headline, reorders image blocks, and approves publication.
3. An authorized editor drags the article into the homepage lead-story position and previews the result.
4. Another story is promoted to Breaking News for a scheduled period.
5. The homepage is published successfully and previous layout history remains available.
6. The article is translated into English and published independently.
7. A video producer uploads a documentary and attaches it to an investigation.
8. The website remains usable on mobile and on a slow connection.
9. A failed upload or lost connection does not silently destroy editorial work.
10. An unauthorized reporter cannot change the live homepage.

These scenarios must work with real database-backed functionality, not simulated buttons or frontend-only demonstration state.

---

# 18. IMPLEMENTATION STRATEGY

Implement the platform in controlled stages:

**Stage 1 — Architecture and Design Foundation**

Inspect the current repository and hosting constraints. Define the database structure, route architecture, design tokens, responsive layout, and component system. Build the public homepage to match the supplied reference.

**Stage 2 — Editorial CMS**

Implement authentication, users, roles, article management, rich-text editing, and bilingual publishing.

**Stage 3 — Media Management**

Implement reliable drag-and-drop uploads, media browsing, transformations, embedding, and video publishing.

**Stage 4 — Visual Homepage Builder**

Implement sorting, promotions, section management, previewing, versioning, publication, and scheduling.

**Stage 5 — Investigations and Advanced Features**

Add timelines, multimedia stories, interactive maps, editorial workflows, and advanced reporting tools.

**Stage 6 — Optimization and Deployment**

Complete functional tests, security checks, performance optimization, migration procedures, production configuration, and cPanel deployment documentation.

At every stage:

- Implement actual working code.
- Run relevant tests.
- Fix errors before moving on.
- Explain the files and systems changed.
- Preserve existing functionality.
- Avoid unnecessary package installation.
- Do not claim completion of features that remain unimplemented.
- Do not deploy or make destructive production changes without explicit authorization.

## FINAL QUALITY STANDARD

NewsBureau24 must have two equally strong experiences.

**Public website:** A premium, trusted, visually polished Bangladeshi newspaper experience matching the provided screenshot.

**Newsroom CMS:** A modern, fast, intuitive publishing workspace where reporters and editors can upload, write, paste, organize, drag, preview, promote, schedule, publish, and manage every part of the publication.

The final result must be an extensible, maintainable, secure, and production-ready platform—not just an attractive homepage.

Begin by auditing the repository, confirming hosting compatibility, establishing the architecture, and implementing the first functional vertical slice: create an article, upload an image, publish it, and show it on the screenshot-matched homepage.