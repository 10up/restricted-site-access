# Changelog

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/).  Moving forward, this project will (more strictly) adhere to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [7.2.0] - 2019-11-27
### Added
- Warn and confirm before network disabling the plugin (props [@pereirinha](https://github.com/pereirinha), [@adamsilverstein](https://github.com/adamsilverstein) via [#29](https://github.com/10up/restricted-site-access/pull/29))
- WP Acceptance integration tests (props [@dkotter](https://github.com/dkotter), [@adamsilverstein](https://github.com/adamsilverstein) via [#86](https://github.com/10up/restricted-site-access/pull/86))

### Fixed
- Ensure comments associated with IPs stay associated correctly (props [@adamsilverstein](https://github.com/adamsilverstein), [@ivankruchkoff](https://github.com/ivankruchkoff), [@helen](https://github.com/helen) via [#106](https://github.com/10up/restricted-site-access/pull/106))
- Don't show escaped HTML in page caching notice (props [@adamsilverstein](https://github.com/adamsilverstein), [@aaemnnosttv](https://github.com/aaemnnosttv) via [#99](https://github.com/10up/restricted-site-access/pull/99))
- Multisite: Avoid a redirect loop when logging in as user with no role (props [@JayWood](https://github.com/JayWood), [@adamsilverstein](https://github.com/adamsilverstein), [@roytanck](https://github.com/roytanck), [@helen](https://github.com/helen), [@rmccue](https://github.com/rmccue) via [#98](https://github.com/10up/restricted-site-access/pull/98))

### Changed
- GitHub Actions workflow files to YAML format (props [@helen](https://github.com/helen) via [#100](https://github.com/10up/restricted-site-access/pull/100))
- Header and icon images (props [@jenniferbourn](https://profiles.wordpress.org/jenniferbourn/) via [#91](https://github.com/10up/restricted-site-access/pull/91))
- Bump WordPress "tested up to" version (props [@adamsilverstein](https://github.com/adamsilverstein) via [#84](https://github.com/10up/restricted-site-access/pull/84))

## [7.1.0] - 2019-04-11
### Added
- IP whitelist: Add a Comment field next to each IP address to help identify IP addresses added to the whitelist.
- Add constants to force enable/disable restrictions. Set `RSA_FORCE_RESTRICTION` to `true` to force restriction or `RSA_FORBID_RESTRICTION` to disable restriction. `RSA_FORCE_RESTRICTION` will override `RSA_FORBID_RESTRICTION` if both are set.
- Unit tests accross plugin. Note that when the `WP_TESTS_DOMAIN` constant is set, plugin redirects are disabled. Only set this constant when running the tests.
- Deploy plugin from GitHub to WordPress.org using GitHub Actions.
- Various GitHub community files.

### Fixed
- Disable individual site settings when network enforced mode is on to avoid confusion about why your settings are not being respected.
- Correctly load admin JS.
- Improve coding standards across plugin and introduce continuous integration linting against the WordPress coding standards. Update code to VIP Go coding standards.

## [7.0.1] - 2018-09-06
### Fixed
- Avoid redirect loop when the unrestricted page is set to be the static front page.
- Fall back to the login screen if the unrestricted page is no longer published.

## [7.0.0] - 2018-08-30
### Added
- WP-CLI support! 🎉 Try `wp rsa` to get started.
- Whitelist IPs via the `RSA_IP_WHITELIST` constant.
- Use WordPress.org-provided language packs instead of bundled translations.

### Fixed
- Restrict "virtual pages" and allow them to be used as the unrestricted page, such as with BuddyPress.
- Hide settings properly when no published pages exist.
- Avoid double slashes in asset URLs that can lead to 404 errors.

## [6.2.1] - 2018-05-21
### Fixed
- Don't redirect logged-in users viewing the site in a single site install.

## [6.2.0] - 2018-05-18
### Added
- Alter or restore previous user permission checking with the `restricted_site_access_user_can_access` filter.

### Changed
- **Functionality change:** Check user's role on a site in multisite before granting permission.

### Fixed
- Avoid a fatal due to differing parameter counts for the `restricted_site_access_is_restricted` filter.

## [6.1.0] - 2018-02-14
### Changed
- Correct a PHP notice when running PHP >= 7.1.
- Refactor logic for checking ip address is in masked ip range.

## [6.0.2] - 2018-01-29
### Added
- 'restrict_site_access_ip_match' action which fires when an ip match occurs. Enables adding session_start() to the IP check, ensuring Varnish type cache will not cache the request.

## [6.0.1] - 2017-06-13
### Changed
- When plugin is network activated, don't touch individual blog visiblity settings.
- When plugin is network deactivated, set all individual blogs to default visibility.

## [6.0] - 2017-06-12
### Added
- Use Grunt to manage assets.
- Network settings added for management of entire network visibility settings.
- Display warning if page caching is enabled.

## [5.1] - 2014-11-29
### Changed
- Under the hood refactoring and clean up for performance and maintainability.
- Small visual refinements to the settings panel.

## [5.0.1] - 2013-01-27
### Fixed
- Does not block user activation page in network mode

## [5.0] - 2012-11-02
### Added
- WordPress 3.5 compatibility (3.5 eliminated the Privacy settings panel in favor of a refreshed Reading panel)

### Changed
- Real validation (on the fly and on save) for IP address entries
- "Restriction message" now supports simple HTML and is edited using WordPress's simple HTML tag editor
- A bunch of visual refinements that conform better with WordPress 3.4 and newer (spacing, native "shake" effect on invalid entries just like the login form, etc.)
- A bunch of under the hood refinements (e.g. playing nicer with current screen Help API)

## [4.0] - 2011-07-16
### Added
- New restriction option - show restricted visitor a specified page; use with custom page templates for great for website teasers!
- New filter hooks for other developers: 'restricted_site_access_is_restricted', 'restricted_site_access_approach', 'restricted_site_access_redirect_url', and 'restricted_site_access_head'
- Localization ready - rough Spanish translation included!
- Basic support for no JavaScript mode

### Changed
- Major improvements to settings user interface, including hiding unused fields based on settings, easier selection of restriction type, and cleaner "remove" confirmation for IP address list
- Performance improvements - catches and blocks restricted visitors earlier in the loading process
- Optimized for PHP 5.2, per new WordPress 3.2 requirements (no longer supports PHP < 5.2.4)
- Assorted other improvements and optimizations to the code base

## [3.2.1] - 2011-03-25
### Changed
- Restored PHP4 compatibility

## [3.2] - 2011-03-25
### Changed
- More meaningful page title in "Display Message" mode (previously WordPress > Error)
- Code clean up, prevent rare warnings in debug mode

## [3.1.1] - 2010-07-17
### Fixed
- PHP warning when debugging is enabled and redirect path is not checked

## [3.1] - 2010-07-11
### Added
- Backwards compatibility with PHP < 5.1 (limited testing with earlier versions)

### Changed
- Built in help on configuration page updated, clearer
- "IP already in list" indicator
- Optimizations to code that handles restriction behavior

### Fixed
- Disappearing blocked access message text box on configuration page
- Login always redirects visitor back to correct page

## [3.0] - 2010-07-05
### Added
- Indicates whether the site is blocked in the admin next to the site title (WordPress 3.0+ only)
- New action hook, `restrict_site_access_handling`, allowing developers to add their own restriction handling

### Changed
- Integrates with Privacy settings page and site visibility option instead of adding a whole new page
- Simplified options: clearer instructions, removed unnecessary hiding / showing of some options, fewer lines
- Cleans up / removes settings when uninstalled
- Assorted under the hood improvements for best coding practices, sanitization of options, etc

## [2.1] - 2010-02-10
### Changed
- Customize blocked visitor message
- Better display / handling of blocked visitor message

### Security
- Stronger security (patched "search" hole)

## [2.0] - 2010-01-10
### Added
- Support for IP ranges courtesy Eric Buth

### Changed
- Major UI changes and improvements; major code improvements

## [1.0.2] - 2009-10-13
### Fixed
- Login redirect to home; improve redirect handling to take advantage of wp_redirect function

## [1.0.1] - 2009-09-10
### Changed
- Important fundamental change related to handling of what should be restricted

## [1.0] - 2009-08-17
### Added
- Initial public release

[Unreleased]: https://github.com/10up/restricted-site-access/compare/7.2.0...HEAD
[7.2.0]: https://github.com/10up/restricted-site-access/compare/7.1.0...7.2.0
[7.1.0]: https://github.com/10up/restricted-site-access/compare/7.0.1...7.1.0
[7.0.1]: https://github.com/10up/restricted-site-access/compare/7.0.0...7.0.1
[7.0.0]: https://github.com/10up/restricted-site-access/compare/6.2.1...7.0.0
[6.2.1]: https://github.com/10up/restricted-site-access/compare/6.2.0...6.2.1
[6.2.0]: https://github.com/10up/restricted-site-access/compare/6.1.0...6.2.0
[6.1.0]: https://github.com/10up/restricted-site-access/compare/6.0.2...6.1.0
[6.0.2]: https://github.com/10up/restricted-site-access/compare/6.0.1...6.0.2
[6.0.1]: https://github.com/10up/restricted-site-access/compare/6.0...6.0.1
[6.0]: https://github.com/10up/restricted-site-access/compare/5.1..6.0
[5.1]: https://github.com/10up/restricted-site-access/compare/5.0.1..5.1
[5.0.1]: https://github.com/10up/restricted-site-access/compare/5.0...5.0.1
[5.0]: https://github.com/10up/restricted-site-access/compare/4.0...5.0
[4.0]: https://github.com/10up/restricted-site-access/compare/3.2.1...4.0
[3.2.1]: https://github.com/10up/restricted-site-access/compare/3.2...3.2.1
[3.2]: https://github.com/10up/restricted-site-access/compare/3.1.1...3.2
[3.1.1]: https://github.com/10up/restricted-site-access/compare/3.1...3.1.1
[3.1]: https://github.com/10up/restricted-site-access/compare/3.0...3.1
[3.0]: https://github.com/10up/restricted-site-access/compare/2.1..3.0
[2.1]: https://github.com/10up/restricted-site-access/compare/2.0..2.1
[2.0]: https://github.com/10up/restricted-site-access/compare/1.0.2..2.0
[1.0.2]: https://github.com/10up/restricted-site-access/compare/1.0.1..1.0.2
[1.0.1]: https://github.com/10up/restricted-site-access/compare/1.0..1.0.1
[1.0]: https://github.com/10up/restricted-site-access/releases/tag/1.0
