# Ridibooks CMS

## [Unreleased]

## [v2.1.8] - 2018-04-19
- Fix authorize error when TEST_ID set (#53)
- Support login with test domain when TEST_AUTH_DISABLE is set (#55)
- Add `authorizeByTag` API support with the update of cms-sdk v2.3.1-rc.1

## [v2.1.7] - 2018-04-16
### Fixed
- Add `/authorize` as white list | cf3efec0
- Fix the wrong in clearing tokens (#49)
- Fix the checking menu urls (#51)
- Fix TEST_AUTH_DISABLE to skip only authorization, not authentication (#51)
