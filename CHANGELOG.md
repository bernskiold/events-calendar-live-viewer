# Changelog

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 2.0.0 - 2026-11-07

### Requirements

- **PHP 8.2** or later and **WordPress 7.0** or later are now required. Tested up to WordPress 7.1. The Events Calendar is declared under `Requires Plugins`.

### Breaking

- The front-end player is a script module (`viewScriptModule`) loaded only when the block renders, configured from data attributes. The `eclw-live-viewer` script handle and the `window.ECLW` global are gone.
- The `events_calendar_live_viewer_blocks` filter is removed; the block registers from a generated blocks manifest.

### Changed

- Dropped the scoped `wp-plugin-base` library and the archived `bm-wp-devtools`; the plugin no longer needs a `vendor/` directory to run.
- Shows an admin notice and stays inactive when The Events Calendar is not active, instead of failing.
- The block uses block API version 3 and respects custom class names.
- Build tooling moved to `@wordpress/scripts` 37 and Node 26; WPCS 3 and PHPCompatibilityWP linting.

### Fixed

- Switching to the next event compares UTC timestamps, so it no longer depends on the site's time zone.
- No PHP warning on pages without a global post.
- Events without a video render an empty player instead of passing an empty URL to oEmbed.
- Editor strings and placeholder text are translatable.

### Development

- PHPUnit (against WordPress and The Events Calendar) and Vitest suites on PHP 8.2–8.5 × WordPress 7.0 and latest via the shared Bernskiold workflows.

## [1.0.0] - 2021-10-31

First version released.
