# Change Log

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/).

Moving forward, this project will (more strictly) adhere to [Semantic Versioning](http://semver.org/).

## [ 7.0.0 ] - 2018-08-30
* Feature: WP-CLI support! 🎉
* Feature: Whitelist IPs via the `RSA_IP_WHITELIST` constant.
* Feature: Use WordPress.org-provided language packs instead of bundled translations.
* Bug fix: Restrict "virtual pages" and allow them to be used as the unrestricted page, such as with BuddyPress.
* Bug fix: Hide settings properly when no published pages exist.
* Bug fix: Avoid double slashes in asset URLs that can lead to 404 errors.

## [ 6.2.1 ] - 2018-05-21
* Bug fix: Don't redirect logged-in users viewing the site in a single site install.

## [ 6.2.0 ] - 2018-05-18
* **Functionality change:** Check user's role on a site in multisite before granting permission.
* Feature: Alter or restore previous user permission checking with the `restricted_site_access_user_can_access` filter.
* Avoid a fatal due to differing parameter counts for the `restricted_site_access_is_restricted` filter.

## [ 6.1.0 ] - 2018-02-14
* Correct a PHP notice when running PHP >= 7.1.
* Refactor logic for checking ip address is in masked ip range.

## [6.0.2] - 2018-01-29
* Add a 'restrict_site_access_ip_match' action which fires when an ip match occurs. Enables adding session_start() to the IP check, ensuring Varnish type cache will not cache the request.

## [6.0.1] - 2017-06-13
* When plugin is network activated, don't touch individual blog visiblity settings.
* When plugin is network deactivated, set all individual blogs to default visibility.

## [6.0] - 2017-06-12
* Use Grunt to manage assets.
* Network settings added for management of entire network visibility settings.
* Display warning if page caching is enabled.

## [5.1] - 2014-11-29
* Under the hood refactoring and clean up for performance and maintainability.
* Small visual refinements to the settings panel.

## [5.0.1] - 2013-01-27
* Does not block user activation page in network mode

## [5.0] - 2012-11-02
* WordPress 3.5 compatibility (3.5 eliminated the Privacy settings panel in favor of a refreshed Reading panel)
* Real validation (on the fly and on save) for IP address entries
* "Restriction message" now supports simple HTML and is edited using WordPress's simple HTML tag editor
* A bunch of visual refinements that conform better with WordPress 3.4 and newer (spacing, native "shake" effect on invalid entries just like the login form, etc.)
* A bunch of under the hood refinements (e.g. playing nicer with current screen Help API)

## [4.0] - 2011-07-16
* New restriction option - show restricted visitor a specified page; use with custom page templates for great for website teasers!
* Major improvements to settings user interface, including hiding unused fields based on settings, easier selection of restriction type, and cleaner "remove" confirmation for IP address list
* Performance improvements - catches and blocks restricted visitors earlier in the loading process
* New filter hooks for other developers: 'restricted_site_access_is_restricted', 'restricted_site_access_approach', 'restricted_site_access_redirect_url', and 'restricted_site_access_head'
* Localization ready - rough Spanish translation included!
* Basic support for no JavaScript mode
* Optimized for PHP 5.2, per new WordPress 3.2 requirements (no longer supports PHP < 5.2.4)
* Assorted other improvements and optimizations to the code base

## [3.2.1] - 2011-03-25
* Restored PHP4 compatibility

## [3.2] - 2011-03-25
* More meaningful page title in "Display Message" mode (previously WordPress > Error)
* Code clean up, prevent rare warnings in debug mode

## [3.1.1] - 2010-07-17
* Fixed PHP warning when debugging is enabled and redirect path is not checked

## [3.1] - 2010-07-11
* New feature: backwards compatibility with PHP < 5.1 (limited testing with earlier versions)
* Bug fix: disappearing blocked access message text box on configuration page
* Bug fix: login always redirects visitor back to correct page
* Improved: built in help on configuration page updated, clearer
* Improved: "IP already in list" indicator
* Improved: optimizations to code that handles restriction behavior

## [3.0] - 2010-07-05
* Integrates with Privacy settings page and site visibility option instead of adding a whole new page
* Simplified options: clearer instructions, removed unnecessary hiding / showing of some options, fewer lines
* Indicates whether the site is blocked in the admin next to the site title (WordPress 3.0+ only)
* New action hook, `restrict_site_access_handling`, allowing developers to add their own restriction handling
* Cleans up / removes settings when uninstalled
* Assorted under the hood improvements for best coding practices, sanitization of options, etc

## [2.1] - 2010-02-10
* Customize blocked visitor message
* Stronger security (patched "search" hole)
* Better display / handling of blocked visitor message

## [2.0] - 2010-01-10
* Add support for IP ranges courtesy Eric Buth
* Major UI changes and improvements; major code improvements

## [1.0.2] - 2009-10-13
* Fix login redirect to home; improve redirect handling to take advantage of wp_redirect function

## [1.0.1] - 2009-09-10
* Important fundamental change related to handling of what should be restricted

## [1.0] - 2009-08-17
* Initial public release


[Unreleased]: https://github.com/10up/restricted-site-access/compare/master...develop
[5.1]: https://github.com/10up/restricted-site-access/compare/5.0.1...5.1
[5.0.1]: https://github.com/10up/restricted-site-access/compare/5.0...5.0.1
[5.0]: https://github.com/10up/restricted-site-access/compare/4.0...5.0
[4.0]: https://github.com/10up/restricted-site-access/compare/3.2.1...4.0
[3.2.1]: https://github.com/10up/restricted-site-access/compare/3.2...3.2.1
[3.2]: https://github.com/10up/restricted-site-access/compare/3.1.1...3.2
[3.1.1]: https://github.com/10up/restricted-site-access/compare/3.1...3.1.1
[3.1]: https://github.com/10up/restricted-site-access/compare/3.0...3.1
[3.0]: https://github.com/10up/restricted-site-access/compare/2.1...3.0
[2.1]: https://github.com/10up/restricted-site-access/compare/2.0...2.1
[2.0]: https://github.com/10up/restricted-site-access/compare/1.0.2...2.0
[1.0.2]: https://github.com/10up/restricted-site-access/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/10up/restricted-site-access/compare/1.0...1.0.1
[1.0]: https://github.com/10up/restricted-site-access/releases/tag/1.0