=== Restricted Site Access ===
Contributors: jakemgold, rcbth, 10up, thinkoomph, tlovett1
Donate link: http://10up.com/plugins/restricted-site-access-wordpress/
Tags: privacy, restricted, restrict, privacy, limited, permissions, security, block
Requires at least: 3.5
Tested up to: 4.9.4
Stable tag: trunk

Limit access to visitors who are logged in or allowed by IP addresses. Includes many options for handling blocked visitors.

== Description ==

Limit access your site to visitors who are logged in or accessing the site from a set of specified IP addresses. Send restricted visitors to the log in page, redirect them, or display a message or page. A great solution for Extranets, publicly hosted Intranets, or parallel development / staging sites.

Adds a number of new configuration options to the Reading settings panel as well as the Network Settings panel in multisite. From these panels you can:

1. Enable or disable site restriction
1. Change the restriction behavior: send to login, redirect, display a message, display a page
1. Add IP addresses to an unrestricted list, including ranges
1. Quickly add your current IP to the unrestricted list
1. Customize the redirect location, including an option to send them to the same requested path and set the HTTP status code for SEO friendliness
1. Define a simple message to show restricted visitors, or select a page to show them - great for "coming soon" teasers!

== Installation ==

1. Install easily with the WordPress plugin control panel or manually download the plugin and upload the extracted folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin by going to the "Reading" menu (WP3.5+) or "Privacy" (earlier versions) under "Settings"

== Frequently Asked Questions ==

= Where do I change the restriction settings? =

Restricted Site Access settings are added to the Reading page, with WordPress’s built in site privacy options. (It was moved there from a separate Privacy settings page in 3.5.)

= It’s not working! My site is wide open! =

Most commonly, Restricted Site Access is not compatible with some page caching solutions. While the plugin hooks in as early as it can to check visitor permissions, its important to understand that some page caching plugins generate static output that prevents plugins like Restricted Site Access from ever checking individual visitors.

To the extent that sites blocked by this plugin should not need to concern themselves with high scale front end performance, we strongly recommend disabling any page caching solutions while restricting access to your site. Keep in mind that most page caching plugins do not cache the “logged in” experience, anyhow. Also note that the plugin *is* fully compatible with other caching layers, like the WordPress object cache.

= How do I allow access to specific pages or parts of my site? =

Developers can use the `restricted_site_access_is_restricted` filter to override normal restriction behavior. Note that restriction checks happen before WordPress executes any queries; it passes the query request from the global `$wp` variable so developers can investigate what the visitor is trying to load.

For instance, to unblock an RSS feed, place the following PHP code in the theme's functions.php file or in a simple plug-in:

`add_filter( 'restricted_site_access_is_restricted', 'my_rsa_feed_override’, 10, 2 );

function my_rsa_feed_override( $is_restricted, $wp ) {
	// check query variables to see if this is the feed
	if ( ! empty( $wp->query_vars['feed'] ) ) {
		$is_restricted = false;
	}
	return $is_restricted;
}`

= How secure is this plug-in? =

Visitors that are not logged in or allowed by IP address will not be able to browse your site (though be cautious of page caching plugin incompatibilities, mentioned above). Restricted Site Access does not block access to your, so direct links to files in your media and uploads folder (for instance) are not blocked. It is also important to remember that IP addresses can be spoofed. Because Restricted Site Access runs as a plug-in, it is subject to any other vulnerabilities present on your site.

Restricted Site Access is not meant to be a top secret data safe, but simply a reliable and convenient way to handle unwanted visitors.

= I received a warning about page caching. What does it mean? =

Page caching plugins often hook into WordPress to quickly serve the last cached output of a page before we can check to see if a visitor’s access should be restricted. Not all page caching plugins behave the same way, but several solutions - including external solutions we might not detect - can cause restricted pages to be publicly served regardless of your settings.

= Why can't logged-in users see all the sites on my multisite instance? =

In 6.2.0, the behavior in a multisite install changed from allowing any logged-in user to see a site to checking their role for that specific site. This is a safer default given the varying ways multisite is used; however, if you would prefer to rely on the previous behavior rather than explicitly adding users to each site, place the following PHP code in the theme's functions.php file or in a simple plug-in:

`
add_filter( 'restricted_site_access_user_can_access', 'my_rsa_user_can_access' );

function my_rsa_user_can_access( $access ) {
	if ( is_user_logged_in() ) {
		return true;
	}

	return $access;
}
`

== Screenshots ==

1. Screenshot of settings panel with simple Restricted Site Access option (send to login page).
1. Screenshot of settings panel with restriction message option enabled
1. Plenty of inline help! Looks and behaves like native WordPress help.

== Changelog ==

= 6.2.0 =
* **Functionality change:** Check user's role on a site in multisite before granting permission.
* Feature: Alter or restore previous user permission checking with the `restricted_site_access_user_can_access` filter.
* Avoid a fatal due to differing parameter counts for the `restricted_site_access_is_restricted` filter.

= 6.1.0 =
* Correct a PHP notice when running PHP >= 7.1.
* Refactor logic for checking ip address is in masked ip range.
* Add PHPUnit tests validating the ip_in_mask functionality.

= 6.0.2 =
* Add a 'restrict_site_access_ip_match' action which fires when an ip match occurs. Enables adding session_start() to the IP check, ensuring Varnish type cache will not cache the request.

= 6.0.1 =
* When plugin is network activated, don't touch individual blog visiblity settings.
* When plugin is network deactivated, set all individual blogs to default visibility.

= 6.0 =
* Use Grunt to manage assets.
* Network settings added for management of entire network visibility settings.
* Display warning if page caching is enabled.

__Note: There is currently an edge case bug affecting IP whitelisting. This bug is on the docket to be fixed shortly.__

= 5.1 =
* Under the hood refactoring and clean up for performance and maintainability.
* Small visual refinements to the settings panel.

= 5.0.1 =
* Does not block user activation page in network mode

= 5.0 =
* WordPress 3.5 compatibility (3.5 eliminated the Privacy settings panel in favor of a refreshed Reading panel)
* Real validation (on the fly and on save) for IP address entries
* "Restriction message" now supports simple HTML and is edited using WordPress's simple HTML tag editor
* A bunch of visual refinements that conform better with WordPress 3.4 and newer (spacing, native "shake" effect on invalid entries just like the login form, etc.)
* A bunch of under the hood refinements (e.g. playing nicer with current screen Help API)

= 4.0 =
* New restriction option - show restricted visitor a specified page; use with custom page templates for great for website teasers!
* Major improvements to settings user interface, including hiding unused fields based on settings, easier selection of restriction type, and cleaner "remove" confirmation for IP address list
* Performance improvements - catches and blocks restricted visitors earlier in the loading process
* New filter hooks for other developers: 'restricted_site_access_is_restricted', 'restricted_site_access_approach', 'restricted_site_access_redirect_url', and 'restricted_site_access_head'
* Localization ready - rough Spanish translation included!
* Basic support for no JavaScript mode
* Optimized for PHP 5.2, per new WordPress 3.2 requirements (no longer supports PHP < 5.2.4)
* Assorted other improvements and optimizations to the code base

= 3.2.1 =
* Restored PHP4 compatibility

= 3.2 =
* More meaningful page title in "Display Message" mode (previously WordPress > Error)
* Code clean up, prevent rare warnings in debug mode

= 3.1.1 =
* Fixed PHP warning when debugging is enabled and redirect path is not checked

= 3.1 =
* New feature: backwards compatibility with PHP < 5.1 (limited testing with earlier versions)
* Bug fix: disappearing blocked access message text box on configuration page
* Bug fix: login always redirects visitor back to correct page
* Improved: built in help on configuration page updated, clearer
* Improved: "IP already in list" indicator
* Improved: optimizations to code that handles restriction behavior

= 3.0 =
* Integrates with Privacy settings page and site visibility option instead of adding a whole new page
* Simplified options: clearer instructions, removed unnecessary hiding / showing of some options, fewer lines
* Indicates whether the site is blocked in the admin next to the site title (WordPress 3.0+ only)
* New action hook, `restrict_site_access_handling`, allowing developers to add their own restriction handling
* Cleans up / removes settings when uninstalled
* Assorted under the hood improvements for best coding practices, sanitization of options, etc

= 2.1 =
* Customize blocked visitor message
* Stronger security (patched "search" hole)
* Better display / handling of blocked visitor message

= 2.0 =
* Add support for IP ranges courtesy Eric Buth
* Major UI changes and improvements; major code improvements

= 1.0.2 =
* Fix login redirect to home; improve redirect handling to take advantage of wp_redirect function

= 1.0.1 =
* Important fundamental change related to handling of what should be restricted

== Upgrade Notice ==

= 5.1 =
Drops support for versions of WordPress prior to 3.5.

= 4.0 =
This update improves performance, refines the user interface, and adds support for showing restricted visitors a specific page. Please be advised that this udpate is specifically designed for WordPress 3.2+, and like WordPress 3.2, <strong>no longer supports PHP < 5.2.4</strong>.

== Upgrade Notice ==

= 6.2.0 =
IMPORTANT MULTISITE FUNCTIONALITY CHANGE: User access is now checked against their role on a given site in multisite. To restore previous behavior, use the new restricted_site_access_user_can_access filter.

= 6.1.0 =
* Important: version 6.1 improves testing visitors for allowed IP addresses ("Unrestricted IP addresses"). We recommend testing IP based restrictions after updating.
