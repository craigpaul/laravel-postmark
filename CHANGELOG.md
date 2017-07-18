# Changelog

## [Unreleased]

### Updated
- Added support for Laravel 5.5.

## [2.0.0]

### Added
- Adds upgrade instructions from v1 to v2 [`a3450dde6f`](https://github.com/coconutcraig/laravel-postmark/commit/a3450dde6f) 
- Adds a short description about Postmark [`17056874e6`](https://github.com/coconutcraig/laravel-postmark/commit/17056874e6) 
- Added a service provider. [`5bf7472003`](https://github.com/coconutcraig/laravel-postmark/commit/5bf7472003) 

### Removed
- Removed guzzle test param. [`b65d000870`](https://github.com/coconutcraig/laravel-postmark/commit/b65d000870) 
- Removed unused local variable $app [`295c02ae0a`](https://github.com/coconutcraig/laravel-postmark/commit/295c02ae0a)

### Updated
- Using helper method instead of static access to class 'Arr' [`c891f714c9`](https://github.com/coconutcraig/laravel-postmark/commit/c891f714c9) 

## [1.1.5]

### Updated
- Set X-PM-Message-Id into swift message headers [`a7a744d53a`](https://github.com/coconutcraig/laravel-postmark/commit/a7a744d53a) 
- Updates minor code styling [`8dfb904663`](https://github.com/coconutcraig/laravel-postmark/commit/8dfb904663)  

## [1.1.4]

### Fixed

- Adds check to make sure we only attach swift attachments [`e3a4c7b86b`](https://github.com/coconutcraig/laravel-postmark/commit/e3a4c7b86b) 

## [1.1.3]

### Fixed

- Fixes bug with getting filename [`3a03b6f8eb`](https://github.com/coconutcraig/laravel-postmark/commit/3a03b6f8eb) 

## [1.1.2]

### Added

- Adds attachment handling [`24d9e889bb`](https://github.com/coconutcraig/laravel-postmark/commit/24d9e889bb) 

## [1.1.1]

### Added

- Adds reply to header override [`71695a1829`](https://github.com/coconutcraig/laravel-postmark/commit/71695a1829) 

## [1.1.0]

### Changed
- Separates out addresses into proper fields [`ddbe313660`](https://github.com/coconutcraig/laravel-postmark/commit/ddbe313660) 

## [1.0.0] - 2017-03-24

### Added
- Adds documentation for attaching tag headers [`e7f09633c8`](https://github.com/coconutcraig/laravel-postmark/commit/e7f09633c8) 

## [0.2.1] - 2017-02-03

### Added
- Adds setup documentation to readme [`2c2bc36a1b`](https://github.com/coconutcraig/laravel-postmark/commit/2c2bc36a1b)
- Adds ability to add tags to mail [`7d63060128`](https://github.com/coconutcraig/laravel-postmark/commit/7d63060128) 
 
### Changed
- Fixes changelog entries [`e5370029b4`](https://github.com/coconutcraig/laravel-postmark/commit/e5370029b4) 
- Cleans up docblocks and removes useless dot files [`09ecd1b10c`](https://github.com/coconutcraig/laravel-postmark/commit/09ecd1b10c)  

## [0.2.0] - 2017-01-29

### Changed
- Removes hhvm from travis.yml file [`bfa3c1739e`](https://github.com/coconutcraig/laravel-postmark/commit/bfa3c1739e) 
- Changes scrutinizer runs to 1 from 3 [`b004ef9439`](https://github.com/coconutcraig/laravel-postmark/commit/b004ef9439)
- Changes getSender to getFrom [`b1484d0337`](https://github.com/coconutcraig/laravel-postmark/commit/b1484d0337) 

## [0.1.0] - 2017-01-29

### Added
- Adds service provider, manager and mail driver with appropriate tests [`b3c10cd221`](https://github.com/coconutcraig/laravel-postmark/commit/b3c10cd221) 

## 0.0.1 - 2017-01-29

### Added
- Adds package skeleton [`2f6fe84bcc`](https://github.com/coconutcraig/laravel-postmark/commit/2f6fe84bcc)

[Unreleased]: https://github.com/coconutcraig/laravel-postmark/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/coconutcraig/laravel-postmark/compare/v1.1.5...v2.0.0
[1.1.5]: https://github.com/coconutcraig/laravel-postmark/compare/v1.1.4...v1.1.5
[1.1.4]: https://github.com/coconutcraig/laravel-postmark/compare/v1.1.3...v1.1.4
[1.1.3]: https://github.com/coconutcraig/laravel-postmark/compare/v1.1.2...v1.1.3
[1.1.2]: https://github.com/coconutcraig/laravel-postmark/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/coconutcraig/laravel-postmark/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/coconutcraig/laravel-postmark/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/coconutcraig/laravel-postmark/compare/v0.2.1...v1.0.0
[0.2.1]: https://github.com/coconutcraig/laravel-postmark/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/coconutcraig/laravel-postmark/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/coconutcraig/laravel-postmark/compare/v0.0.1...v0.1.0
