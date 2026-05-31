# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0 - 2026-05-31

First tagged release.

### Added

- `ReplaceUploadsUrl` hook provider — on non-production environments, rewrites local uploads URLs to a
  configured remote URL across attachment `src`, image `srcset`, the `wp_prepare_attachment_for_js`
  payload and post content. The remote URL comes from the `replace_uploads_url` config key or the
  `REPLACE_UPLOADS_URL` constant; `ConfigProvider` and `ReplaceUploadsUrlFactory` wire it up.

### Changed

- PHP requirement is `^8.2` (PHP 8.4 is the primary target).
- Modernized the dev toolchain (PHPStan 2, PHPUnit 11 schema, composer-require-checker 4) and depend
  on `kaiseki/php-coding-standard: ^1.0` with the shared PHPStan config; `kaiseki/config` and
  `kaiseki/wp-hook` pinned to `^2.0`, `kaiseki/wp-env` to `^1.0`. CI now runs via the reusable
  workflow in `kaisekidev/.github`.

### Fixed

- PHPStan 2 (level max) fixes at the root: the `$remoteUrl` property is typed `string` (it is never
  null after construction), dropping the dead null-check and a redundant string cast. No runtime
  behaviour change.
