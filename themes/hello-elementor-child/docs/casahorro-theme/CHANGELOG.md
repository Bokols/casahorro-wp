# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-11-04

### Added
- Home hero CSS variables printer in `functions.php` (reads Customizer keys `cas_home_hero_bg_desktop/tablet/mobile`, outputs to `body.home`)
- Assistant glue enqueue (front page only, after consent-events.js, deferred, version with `filemtime()`)
- Autoptimize exclusion guard (excludes `home-hero.css`, `buttons-flow.css`, `assistant-glue.js` from aggregation)
- Enqueue logger (admin-only, logs CSS/JS load order to `debug.log` for front page verification)
- Asset scanner tool (`inc/asset-scanner.php`, WP-CLI + Admin page at Tools > Asset Scanner)
- Image optimization scanner (`inc/image-optimizer-scanner.php`, reports PNG/JPG >150KB, suggests WebP/AVIF conversion)

### Changed
- Home hero CSS rewritten: minimal selectors (`.home-hero`, `.home-hero__container`, `.home-hero__title`, `.home-hero__subtitle`, `.home-hero__cta`)
- Home hero grid: two-column desktop (58/42 split via `minmax(0, 0.58fr) 0.42fr`), single-column mobile (≤1023px)
- Fluid typography: H1 `clamp(1.75rem, 3.5vw, 2.25rem)`, subtitle `clamp(1rem, 2vw, 1.125rem)`
- Safe spacing: top padding 96/72/56px (D/T/M), bottom 48/40/32px, `min-height: 65vh`, `scroll-margin-top: 96px`
- Background images: Desktop gradient + `var(--hero-bg-desktop)`, tablet `var(--hero-bg-tablet)`, mobile `var(--hero-bg-mobile)`, all `!important` to override Elementor inline styles
- Customizer settings: Renamed to `cas_home_hero_bg_desktop/tablet/mobile` (consistent prefix)
- Home hero enqueue: Version with `filemtime()`, load after tokens, before Elementor page CSS
- Secondary CTA hidden in hero: `.home-hero__cta .flow-button.secondary { display: none !important; }`
- Container z-index guard: `.home-hero__container { position: relative; z-index: 1; }` to keep text above background

### Removed
- Placeholder comments and ellipsis (`...existing code...`) purged from all CSS files
- Unused `.home-hero__visual` selector (not needed in minimal version)
- Excessive media query overrides (consolidated to essential breakpoints only)
- Accessibility/print styles in home-hero.css (kept minimal for performance)

### Fixed
- Grid overflow issues: All direct children have `min-width: 0` to prevent text overflow
- Elementor specificity battles: Multiple selector chains + `!important` on typography
- Background image flickering: Single `!important` on `background-image` declarations with comment explaining override
- Mobile layout: Proper single-column collapse at ≤1023px with centered text and CTAs

## [1.0.0] - 2025-10-01

### Added
- Initial child theme setup
- Design tokens system (`assets/tokens.css`)
- Color guard for Elementor/WooCommerce overrides
- Header sticky styles and nav drawer
- PLP filters and analytics (consent-gated)
- Consent events handler (CookieYes integration)
- Accessibility compliance tools
- WP-CLI utilities and debug helpers

---

## Unreleased

_No unreleased changes yet._
