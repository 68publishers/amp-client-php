# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

## 1.1.0 - 2023-12-01
### Added
- Added ability to provide custom options for each banner through Renderer or Latte macro.
- Added support for native lazy loading. Feature can be enabled through banner options `'loading' => 'lazy'` and `'loading-offset' => <offset>` (for multiple positions only).

### Changed
- Updated default templates due to implementation of custom banner options and native lazy loading.
- Updated docs.

## 1.0.0 - 2023-11-30
### Added

- Initial release.
