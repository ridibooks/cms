# Ridibooks CMS
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
## [3.0.7] - 2020-04-30
### Changed
- Print x-forwarded-for header in apache log

## [3.0.6] - 2020-02-25
### Fixed
- Fix logout error and logout is now overriden by auth type

### Removed
- Remove a csp report header on response (#101)

## [3.0.5] - 2019-12-04
### Fixed
- Fix not showing menus

## [3.0.4] - 2019-12-03
### Fixed
- Fix security alerts: symfony/http-foundation

## [3.0.3] - 2019-12-02
### Changed
- Fix error on XDEBUG_ENABLE not set

## [3.0.2] - 2019-12-02
### Changed
- Remove auth for dev

## [3.0.1] - 2019-11-28
### Changed
- Accept scheme from azure path

## [3.0.0] - 2019-11-22
### Added 
- Cloudflare Access integration (#99)

## [2.3.3] - 2019-07-18
### Added
- Apply a security header with CMS Middleware (#95)
### Improved
- Improved a getFilteredReturnUrl() function to strictly (#97)

## [2.3.2] - 2019-07-08
### Fixed
- Fixed a open redirect and reflected xss vuln on login/logout pages (#94)

## [2.3.1] - 2019-06-17
### Fixed
- Fixed unintended initialization of authentication methods

## [2.3.0] - 2019-04-09
### Added
- Added robots.txt (#88)
- Added token API (#91)
### Fixed
- Updated a legacy; gitlab.ridi.io -> gitlab.com(#89)
- Fix the bug that prevents authorizing-by-tag on dev environment (#90)

## [2.2.0] - 2018-08-31
### Changed
- Apply Docker multistage build

## [2.1.25] - 2018-08-29
### Changed
- Reconfigure CI to run with `terraform-aws`

## [2.1.24] - 2018-08-22
### Added
- Add /v2 endpoint for thrift

## [2.1.23] - 2018-07-20
### Fixed
- Fix 500 at `/authorize` due to `NoCredentialException` when no refresh token given
### Changed
- Add email column in user DB  (#84)
- The return of getUser API includes user email.

## [2.1.22] - 2018-07-13
### Fixed
- Fix `findTagsByName` not to return all tag ids

## [2.1.21] - 2018-07-10
### Fixed
- Fix token expired exception on authorize call over thrift

## [2.1.20] - 2018-07-10
### Fixed
- Fix access token expired exception (#80)

## [2.1.19] - 2018-07-04
### Fixed
- Fix to refresh token at `/auth/oauth2/authorize` (#78)
- Fix test server login id error
- Configure viewport for mobile UI (#77)

### Changed
- Make test id available in `introspectToken`

## [2.1.18] - 2018-06-29
### Changed
- Use 'mailNickName' for azure id instead of 'unique_name'

## [2.1.17] - 2018-06-28
### Changed
- Set trusted proxies

## [2.1.16] - 2018-06-28
### Added
- Set domain "admin.*.test.ridi.io" enable test
### Changed
- Changed authentication related endpoints, /authorize -> /auth/oauth2/oauth/authorize, /login-azure -> /auth/oauth2/callback
- Refactor authentication logics

## [2.1.15] - 2018-06-15
### Added
- Add user group for tags (#68, #69)
### Fixed
- Fix unexpected menu positioning (#66)

## [2.1.14] - 2018-05-29
### Fixed
- Fix cms-ui menu scrolling bug (#67)

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
