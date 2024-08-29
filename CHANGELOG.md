# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Added support for new banner option `fetchpriority`.
- Added support for banner options defined in the AMP administration.

### Changed
- The option `loading` is now processed as an expression. The option `loading-offset` is ignored.
- Updated docs.

## [1.2.0] - 2024-04-04
### Added
- Added integration of the new AMP API fields `mode` and `dimensions`.
- Added possibility to render embed banners.
- Added new rendering mode `embed` (`EmbedRenderingMode`) for the Latte bridge.

### Changed
- Updated docs

## [1.1.0] - 2023-12-01
### Added
- Added ability to provide custom options for each banner through Renderer or Latte macro.
- Added support for native lazy loading. Feature can be enabled through banner options `'loading' => 'lazy'` and `'loading-offset' => <offset>` (for multiple positions only).

### Changed
- Updated default templates due to implementation of custom banner options and native lazy loading.
- Updated docs.

## 1.0.0 - 2023-11-30
### Added

- Initial release.

[Unreleased]: https://github.com/68publishers/amp-client-js/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/68publishers/amp-client-php/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/68publishers/amp-client-php/compare/v1.0.0...v1.1.0
