# Ridibooks CMS
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [v2.1.13] - 2018-05-18
### Added
- Add a new API `introspectToken` (#64)
- Use auth subdomain (#62)

## [v2.1.12] - 2018-05-16
### Changed
- Update test server URL for platform team | f7658f4
- Replace menu component with CMS UI (#61)

### Fixed
- Fix invalid test assert | 39c62eb
- Fix sentry not working (#60)

## [v2.1.11] - 2018-05-03
### Fixed
- Fix invoking error in `getHashesFromMenus`

## [v2.1.8] - 2018-04-19
### Fixed
- Fix authorize error when TEST_ID set (#53)
- Support login with test domain when TEST_AUTH_DISABLE is set (#55)

### Added
- New `authorizeByTag` API support with the update of cms-sdk v2.3.1-rc.1

## [v2.1.7] - 2018-04-16
### Fixed
- Add `/authorize` as white list | cf3efec0
- Fix the wrong in clearing tokens (#49)
- Fix the checking menu urls (#51)
- Fix TEST_AUTH_DISABLE to skip only authorization, not authentication (#51)
