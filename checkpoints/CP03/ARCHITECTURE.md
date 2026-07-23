# CHECKPOINT 3 — Architecture

> Status: ROADMAP_DEFINED — full architecture TBD by Planning Authority (ChatGPT)

## Context

Checkpoint 3 focuses on layout compaction and responsive hardening for the news module views. The existing architecture uses plain PHP MVC with:

- `app/controllers/NewsController.php` — request handling
- `app/models/News.php` — data/persistence semantics
- `app/views/news/` — presentation templates (index, detail, search, admin)
- `public/assets/css/news.css` — stylesheet
- `public/assets/js/news.js` — client-side behavior

## Constraints

1. No new JavaScript frameworks or libraries
2. No backend controller/API changes unless required for layout data
3. MVC convention must be preserved (no Domain/Service/Repository layers)
4. Accessibility must not regress (WCAG 2.1 AA baseline)

## Responsive Breakpoints

| Breakpoint | Target |
|---|---|
| <= 767px | Mobile-first: single column, compact first-fold |
| 768px - 1023px | Tablet: 2-column grid, adjusted hero |
| >= 1024px | Desktop: full layout, mega menu static |

## Key Components Affected

- `news.js`: `syncCategoryDrawerResponsiveState()`, `handleViewportChange()`
- `news.css`: First-fold `min-height`, overflow rules, breakpoint media queries
- View templates: Structure tags for responsive layout containers
