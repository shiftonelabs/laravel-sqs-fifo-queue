# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Support for AWS STS temporary credentials session token in the config. ([#9](https://github.com/shiftonelabs/laravel-sqs-fifo-queue/pull/9), [3493a99](https://github.com/shiftonelabs/laravel-sqs-fifo-queue/commit/3493a99acd8005ca94e1c7d4cc0f86a1e6ab8a8f))

## [1.1.3] - 2020-06-26
### Changed
- Updated ramsey/uuid dependency version to support Laravel 7.x.
- Updated CI configs to run tests on Laravel 7.x.
- Updated readme with new version information.

## [1.1.2] - 2020-06-26
### Changed
- Support queuing job instances in Laravel 4 to support per job message groups.
- Updated readme with new support information.
- Updated CI config to remove composer memory limit and retry failed composer commands.

## [1.1.1] - 2019-12-10
### Changed
- Support Laravel 6.x by converting string and array helpers to use support classes.
- Updated CI configs to support newest versions of Laravel and PHP.
- Updated readme with new version information.
- Added a missing test for invalid bound deduplicators.
- Cleaned up some unused use statements and variables in tests.

## [1.1.0] - 2018-05-08
### Added
- New changelog.
- Support for automatic package discovery in Laravel 5.5+.

### Changed
- Updated CI configs for increased test coverage.
- Allow `key` and `secret` config keys to be omitted for alternative AWS credentials.
- Updated readme with version information and message group information.

## [1.0.2] - 2017-02-14
### Fixed
- Fix `onMessageGroup()` typo in the documentation.

## [1.0.1] - 2017-02-14
### Fixed
- Fix FIFO connector not registering due to the deferred service provider.

## 1.0.0 - 2017-02-13
### Added
- Initial release!

[Unreleased]: https://github.com/shiftonelabs/laravel-sqs-fifo-queue/compare/1.1.3...HEAD
[1.1.3]: https://github.com/shiftonelabs/laravel-sqs-fifo-queue/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/shiftonelabs/laravel-sqs-fifo-queue/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/shiftonelabs/laravel-sqs-fifo-queue/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/shiftonelabs/laravel-sqs-fifo-queue/compare/1.0.2...1.1.0
[1.0.2]: https://github.com/shiftonelabs/laravel-sqs-fifo-queue/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/shiftonelabs/laravel-sqs-fifo-queue/compare/1.0.0...1.0.1
