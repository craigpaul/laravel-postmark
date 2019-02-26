# Changelog

## [Unreleased]

## [2.4.0] - 2019-02-26

### Added
- Adds support for Laravel 5.8 [`d6aa008ca5`](https://github.com/coconutcraig/laravel-postmark/commit/d6aa008ca5) 
- Adds test to ensure custom creator is not put into framework when mail driver is not postmark [`7c22b536f6`](https://github.com/coconutcraig/laravel-postmark/commit/7c22b536f6)

## [2.3.2] - 2018-09-27

### Added
- Adds build matrix to test against multiple versions. [`ea0b27d9f7`](https://github.com/coconutcraig/laravel-postmark/commit/ea0b27d9f7) 

### Fixed
- Skip swift extension if driver isn't 'postmark' [`6bb7e3b7e5`](https://github.com/coconutcraig/laravel-postmark/commit/6bb7e3b7e5)

### Updated
- Changes style preset to laravel [`dbabf1e286`](https://github.com/coconutcraig/laravel-postmark/commit/dbabf1e286)

## [2.3.1] - 2018-09-11

### Fixed
- Throw an exception when POSTMARK_SECRET is not set. [`cabbe42473`](https://github.com/coconutcraig/laravel-postmark/commit/cabbe42473)
- Fixes long line causing phpcs error [`aa6909137`](https://github.com/coconutcraig/laravel-postmark/commit/9aa6909137)  

### Updated
- Update PostmarkServiceProvider.php [`2d03a2a0fb`](https://github.com/coconutcraig/laravel-postmark/commit/2d03a2a0fb) 

## [2.3.0] - 2018-09-04

### Added
- Adds phpunit.xml to .gitignore. [`cb798836a7`](https://github.com/coconutcraig/laravel-postmark/commit/acb798836a7)

### Changed
- Adds a postmark config file. [`e13970dd2d`](https://github.com/coconutcraig/laravel-postmark/commit/e13970dd2d) 
- Refactors the getHtmlAndTextBody() function by using a collection. [`00c36b8572`](https://github.com/coconutcraig/laravel-postmark/commit/00c36b8572)

### Fixed
- Fixes naming of a test. [`8b43757939`](https://github.com/coconutcraig/laravel-postmark/commit/8b43757939)
- Fixes phpunit.xml.dist file. [`fd75059cd1`](https://github.com/coconutcraig/laravel-postmark/commit/fd75059cd1)

### Updated
- Adds support for Laravel 5.5 up to 5.7. [`86b4b3aebd`](https://github.com/coconutcraig/laravel-postmark/commit/86b4b3aebd)

## [2.2.0] - 2018-02-19

### Updated
- Updates travis configuration. [`ae914ea34a`](https://github.com/coconutcraig/laravel-postmark/commit/ae914ea34a) 
- Updates compatibility table. [`dac237f848`](https://github.com/coconutcraig/laravel-postmark/commit/dac237f848)
- Updates dependencies for laravel framework upgrade. [`932660900b`](https://github.com/coconutcraig/laravel-postmark/commit/932660900b)

## [2.1.8] - 2017-11-16

### Updated
- Adds multipart/mixed to content types to account for attachments. [`e63fdd9e37`](https://github.com/coconutcraig/laravel-postmark/commit/e63fdd9e37)

## [2.1.7] - 2017-11-16

### Fixed
- Re-indexes attachments collection before converting to an array. [`eb39a28689`](https://github.com/coconutcraig/laravel-postmark/commit/eb39a28689)

## [2.1.6] - 2017-11-07

### Fixed
- Display names containing a comma results in API error 300. [`ed09f78472`](https://github.com/coconutcraig/laravel-postmark/commit/ed09f78472) 

## [2.1.5] - 2017-11-07

### Added
- Added suggestion of mvdnbrk/postmark-inbound to composer.json. [`2525c6bd35`](https://github.com/coconutcraig/laravel-postmark/commit/2525c6bd35)

## [2.1.4] - 2017-10-08

### Added
- Added additional test. [`5030f1774d`](https://github.com/coconutcraig/laravel-postmark/commit/5030f1774d)
- Tests for attachments are in a separate test. [`48bcaa386a`](https://github.com/coconutcraig/laravel-postmark/commit/48bcaa386a)
- Adds getHtmlAndTextBody function including tests. [`a05aa46b5f`](https://github.com/coconutcraig/laravel-postmark/commit/a05aa46b5f)
- Adds getMimePart function to get a mime part from a message. [`494cde1cd5`](https://github.com/coconutcraig/laravel-postmark/commit/494cde1cd5)
- Adds getBody function to get the body from a message. [`3f1b46911e`](https://github.com/coconutcraig/laravel-postmark/commit/3f1b46911e)
- Adds test to ensure that required fields are in the payload. [`0c15aa1dc2`](https://github.com/coconutcraig/laravel-postmark/commit/0c15aa1dc2)
- Adds getSubject function to get the subject from a message. [`5317d0d5db`](https://github.com/coconutcraig/laravel-postmark/commit/5317d0d5db)
- Added getPayload function. [`05fd0244b1`](https://github.com/coconutcraig/laravel-postmark/commit/05fd0244b1)
- Adds tests to ensure that the json payload has the proper values. [`d7f506907b`](https://github.com/coconutcraig/laravel-postmark/commit/d7f506907b)
- Adds getTag function to get the tag from a message. [`3389580b29`](https://github.com/coconutcraig/laravel-postmark/commit/3389580b29)
- Add Content-Type to payload headers. [`d4d79380c4`](https://github.com/coconutcraig/laravel-postmark/commit/d4d79380c4)

### Updated
- Updates docblock and test for getting contacts. [`7e658ceb0e`](https://github.com/coconutcraig/laravel-postmark/commit/7e658ceb0e)
- Code style. [`77fcc03887`](https://github.com/coconutcraig/laravel-postmark/commit/77fcc03887)
- Type hint params. [`a17986572d`](https://github.com/coconutcraig/laravel-postmark/commit/a17986572d)
- Remove dump. [`05ec702ff1`](https://github.com/coconutcraig/laravel-postmark/commit/05ec702ff1)
- Payload does not contain empty keys. [`3c12ede434`](https://github.com/coconutcraig/laravel-postmark/commit/3c12ede434)
- Refactor PostmarkTransportTest. [`e334420982`](https://github.com/coconutcraig/laravel-postmark/commit/e334420982)

## [2.1.3] - 2017-10-06

### Fixed
- getAttachments should return array. [`9adaf6340f`](https://github.com/coconutcraig/laravel-postmark/commit/9adaf6340f)

## [2.1.2] - 2017-10-06

### Updated
- Updates docblock return. [`ba2c7dbe8e`](https://github.com/coconutcraig/laravel-postmark/commit/ba2c7dbe8e)
- Inherit the docs from the parent class. [`8a37c8c3ef`](https://github.com/coconutcraig/laravel-postmark/commit/8a37c8c3ef)
- Refactor getAttachments method. [`a5d4b90969`](https://github.com/coconutcraig/laravel-postmark/commit/a5d4b90969)
- Missing dot at the end of the sentence. [`5a1d90406a`](https://github.com/coconutcraig/laravel-postmark/commit/5a1d90406a)
- Remove single used variables. [`69603881f4`](https://github.com/coconutcraig/laravel-postmark/commit/69603881f4)
- Typo. [`598b9b3a47`](https://github.com/coconutcraig/laravel-postmark/commit/598b9b3a47)

## [2.1.1] - 2017-07-17

### Added
- Adds upgrade guide and link to readme. [`158742412c`](https://github.com/coconutcraig/laravel-postmark/commit/158742412c)
- Adds tag link for laravel 5.4 support. [`a4b8c64f5c`](https://github.com/coconutcraig/laravel-postmark/commit/a4b8c64f5c)

### Removed
- Removes redundant getFrom method and test. [`81c555b9af`](https://github.com/coconutcraig/laravel-postmark/commit/81c555b9af)
- Removes line forgotten from previous upgrade guide. [`4910872bbe`](https://github.com/coconutcraig/laravel-postmark/commit/4910872bbe)

### Updated
- Updates docblocks. [`e3af135c8f`](https://github.com/coconutcraig/laravel-postmark/commit/e3af135c8f)

## [2.1.0] - 2017-07-17

### Updated
- Adds support table to readme. [`1f6f85b0c0`](https://github.com/coconutcraig/laravel-postmark/commit/1f6f85b0c0)
- Add support for Laravel 5.5. [`d29cdb46f6`](https://github.com/coconutcraig/laravel-postmark/commit/d29cdb46f6)

## [2.0.0] - 2017-07-17

### Added
- Adds upgrade instructions from v1 to v2. [`a3450dde6f`](https://github.com/coconutcraig/laravel-postmark/commit/a3450dde6f)
- Adds a short description about Postmark. [`17056874e6`](https://github.com/coconutcraig/laravel-postmark/commit/17056874e6)
- Added a service provider. [`5bf7472003`](https://github.com/coconutcraig/laravel-postmark/commit/5bf7472003)

### Removed
- Removed guzzle test param. [`b65d000870`](https://github.com/coconutcraig/laravel-postmark/commit/b65d000870)
- Removed unused local variable $app. [`295c02ae0a`](https://github.com/coconutcraig/laravel-postmark/commit/295c02ae0a)

### Updated
- Using helper method instead of static access to class 'Arr'. [`c891f714c9`](https://github.com/coconutcraig/laravel-postmark/commit/c891f714c9)

## [1.1.5] - 2017-07-02

### Updated
- Set X-PM-Message-Id into swift message headers. [`a7a744d53a`](https://github.com/coconutcraig/laravel-postmark/commit/a7a744d53a)
- Updates minor code styling. [`8dfb904663`](https://github.com/coconutcraig/laravel-postmark/commit/8dfb904663)

## [1.1.4] - 2017-05-24

### Fixed

- Adds check to make sure we only attach swift attachments. [`e3a4c7b86b`](https://github.com/coconutcraig/laravel-postmark/commit/e3a4c7b86b)

## [1.1.3] - 2017-05-24

### Fixed

- Fixes bug with getting filename. [`3a03b6f8eb`](https://github.com/coconutcraig/laravel-postmark/commit/3a03b6f8eb)

## [1.1.2] - 2017-05-24

### Added

- Adds attachment handling. [`24d9e889bb`](https://github.com/coconutcraig/laravel-postmark/commit/24d9e889bb)

## [1.1.1] - 2017-05-23

### Added

- Adds reply to header override. [`71695a1829`](https://github.com/coconutcraig/laravel-postmark/commit/71695a1829)

## [1.1.0] - 2017-05-17

### Changed
- Separates out addresses into proper fields. [`ddbe313660`](https://github.com/coconutcraig/laravel-postmark/commit/ddbe313660)

## [1.0.0] - 2017-03-24

### Added
- Adds documentation for attaching tag headers. [`e7f09633c8`](https://github.com/coconutcraig/laravel-postmark/commit/e7f09633c8)

## [0.2.1] - 2017-02-03

### Added
- Adds setup documentation to readme. [`2c2bc36a1b`](https://github.com/coconutcraig/laravel-postmark/commit/2c2bc36a1b)
- Adds ability to add tags to mail. [`7d63060128`](https://github.com/coconutcraig/laravel-postmark/commit/7d63060128)

### Changed
- Fixes changelog entries. [`e5370029b4`](https://github.com/coconutcraig/laravel-postmark/commit/e5370029b4)
- Cleans up docblocks and removes useless dot files. [`09ecd1b10c`](https://github.com/coconutcraig/laravel-postmark/commit/09ecd1b10c)

## [0.2.0] - 2017-01-29

### Changed
- Removes hhvm from travis.yml file. [`bfa3c1739e`](https://github.com/coconutcraig/laravel-postmark/commit/bfa3c1739e)
- Changes scrutinizer runs to 1 from 3. [`b004ef9439`](https://github.com/coconutcraig/laravel-postmark/commit/b004ef9439)
- Changes getSender to getFrom. [`b1484d0337`](https://github.com/coconutcraig/laravel-postmark/commit/b1484d0337)

## [0.1.0] - 2017-01-29

### Added
- Adds service provider, manager and mail driver with appropriate tests. [`b3c10cd221`](https://github.com/coconutcraig/laravel-postmark/commit/b3c10cd221)

## 0.0.1 - 2017-01-29

### Added
- Adds package skeleton. [`2f6fe84bcc`](https://github.com/coconutcraig/laravel-postmark/commit/2f6fe84bcc)

[Unreleased]: https://github.com/coconutcraig/laravel-postmark/compare/v2.4.0...HEAD
[2.4.0]: https://github.com/coconutcraig/laravel-postmark/compare/v2.3.2...2.4.0
[2.3.2]: https://github.com/coconutcraig/laravel-postmark/compare/v2.3.1...2.3.2
[2.3.1]: https://github.com/coconutcraig/laravel-postmark/compare/v2.3.0...2.3.1
[2.3.0]: https://github.com/coconutcraig/laravel-postmark/compare/v2.2.0...2.3.0
[2.2.0]: https://github.com/coconutcraig/laravel-postmark/compare/v2.1.8...v2.2.0
[2.1.8]: https://github.com/coconutcraig/laravel-postmark/compare/v2.1.7...v2.1.8
[2.1.7]: https://github.com/coconutcraig/laravel-postmark/compare/v2.1.6...v2.1.7
[2.1.6]: https://github.com/coconutcraig/laravel-postmark/compare/v2.1.5...v2.1.6
[2.1.5]: https://github.com/coconutcraig/laravel-postmark/compare/v2.1.4...v2.1.5
[2.1.4]: https://github.com/coconutcraig/laravel-postmark/compare/v2.1.3...v2.1.4
[2.1.3]: https://github.com/coconutcraig/laravel-postmark/compare/v2.1.2...v2.1.3
[2.1.2]: https://github.com/coconutcraig/laravel-postmark/compare/v2.1.1...v2.1.2
[2.1.1]: https://github.com/coconutcraig/laravel-postmark/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/coconutcraig/laravel-postmark/compare/v2.0.0...v2.1.0
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
