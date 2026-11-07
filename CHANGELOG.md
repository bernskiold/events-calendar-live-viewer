# Changelog

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- Dropped the scoped `bernskioldmedia/wp-plugin-base` library and the archived `bm-wp-devtools`. The plugin now carries the little it used itself.
- The plugin shows an admin notice and stays inactive when The Events Calendar is not active, instead of failing.
- The plugin no longer needs a `vendor` directory to run.
- Requires PHP 8.2 and WordPress 7.0.
- Build tooling moved to `@wordpress/scripts` 37 and Node 26.
- Coding standards are checked with WPCS 3 and PHPCompatibilityWP (`composer lint`).
- The Live Viewer block uses block API version 3 and is registered from a generated blocks manifest.
- The front-end player is now a script module (`viewScriptModule`), loaded only when the block renders. This includes templates and widgets as well as post content. It reads its settings from the block's data attributes, so the `eclw-live-viewer` script handle and the `window.ECLW` global are gone.
- The block respects custom class names from the editor.

### Added

- PHPUnit integration tests against WordPress and The Events Calendar (`composer test`), and Vitest unit tests for the player (`npm run test:unit`).

### Fixed

- Switching to the next event compares UTC timestamps, so it no longer depends on the site's time zone.
- No PHP warning on pages without a global post.
- Events without a video render an empty player instead of passing an empty URL to oEmbed.
- The editor strings and the placeholder text are translatable.

## [1.0.0] - 2021-10-31

First version released.
