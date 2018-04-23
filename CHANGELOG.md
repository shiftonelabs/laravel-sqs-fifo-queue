# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- New changelog.
- Support for automatic package discovery in Laravel 5.5+.

### Changed
- Updated CI configs for increased test coverage.
- Allow `key` and `secret` config keys to be omitted for alternative AWS credentials.

## [1.0.2] - 2017-02-14
### Fixed
- Fix `onMessageGroup()` typo in the documentation.

## [1.0.1] - 2017-02-14
### Fixed
- Fix FIFO connector not registering due to the deferred service provider.

## 1.0.0 - 2017-02-13
### Added
- Initial release!

[Unreleased]: https://github.com/shiftonelabs/laravel-sqs-fifo-queue/compare/1.0.2...HEAD
[1.0.2]: https://github.com/shiftonelabs/laravel-sqs-fifo-queue/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/shiftonelabs/laravel-sqs-fifo-queue/compare/1.0.0...1.0.1
