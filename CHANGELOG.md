# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.5.1] - 2025-11-10
### Fixed
- Fixed leaking Guzzle exceptions (e.g. `ConnectException`) from the HTTP client. An exception `UnexpectedErrorException` is thrown instead.

## [1.5.0] - 2024-05-23
### Added
- Added support for content type `noContent`.

## [1.4.0] - 2024-05-06
### Added
- Added integration of the new fields `close_expiration` into Position and Banner entities and also into the `data-amp-banner-external` and `data-amp-banner-fingerprint` attributes.
- Added services of type `ClosingManagerInterface` and `ClosedEntriesStoreInterface` that are used by banner resolver to check if the passed position and its banners are closed.
- Added new optional configuration option `closing.cookieName`.
- Added new Latte/phtml templates with the code `closed`.

### Changed
- Updated docs.
- Changed the default API version in the configuration to `2`.

### Fixed
- Fixed an issue with the browser downloading images for all breakpoints when it wasn't needed.

## [1.3.1] - 2024-10-11
### Added
- Added PHP 8.3 between tested versions.
- Added conditional HTML banner attributes using `exists@<attribute>` and `exists(<breakpoint>)@<attribute>` entries.

### Changed
- Moved `dimensions` field from the Position class to the `ImageContent` class according to changes in AMP v2.16.0 API changes.

## [1.3.0] - 2024-09-19
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

[Unreleased]: https://github.com/68publishers/amp-client-js/compare/v1.3.1...HEAD
[1.3.0]: https://github.com/68publishers/amp-client-php/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/68publishers/amp-client-php/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/68publishers/amp-client-php/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/68publishers/amp-client-php/compare/v1.0.0...v1.1.0
