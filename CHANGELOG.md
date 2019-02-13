# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Ability to customize the underlying SCSS Compiler via the `$compiler` attribute

### Removed
- Support for PHP 7.0, following PHP's own support schedule
- `$formatter` customization attribute

## [0.2.1] - 2018-04-22
### Added
- Make target path and filename overridable

### Fixed
- Relative asset paths were not conserved

## [0.2.0] - 2018-04-21
### Added
- Support for PHP 7.2

## [0.1.2] - 2017-07-18
### Fixed
- `@import` not working in `.scss` files

## [0.1.1] - 2016-09-29
### Added
- Only convert when source file is newer than target file

## [0.1.0] - 2016-04-29
Initial release.