=== Restricted Site Access ===
Contributors:      10up, jakemgold, rcbth, thinkoomph, tlovett1, jeffpaul, nomnom99
Donate link:       https://10up.com/plugins/restricted-site-access-wordpress/
Tags:              privacy, restricted, restrict, privacy, limited, permissions, security, block
Requires at least: 5.7
Tested up to:      6.2
Stable tag:        7.4.0
Requires PHP:      7.4
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Limit access to visitors who are logged in or allowed by IP addresses. Includes many options for handling blocked visitors.

== Description ==

Limit access your site to visitors who are logged in or accessing the site from a set of specified IP addresses. Send restricted visitors to the log in page, redirect them, or display a message or page. A great solution for Extranets, publicly hosted Intranets, or parallel development / staging sites.

Adds a number of new configuration options to the Reading settings panel as well as the Network Settings panel in multisite. From these panels you can:

* Enable or disable site restriction
* Change the restriction behavior: send to login, redirect, display a message, display a page
* Add IP addresses to an unrestricted list, including ranges
* Quickly add your current IP to the unrestricted list
* Customize the redirect location, including an option to send them to the same requested path and set the HTTP status code for SEO friendliness
* Define a simple message to show restricted visitors, or select a page to show them - great for "coming soon" teasers!

== Installation ==

1. Install easily with the WordPress plugin control panel or manually download the plugin and upload the extracted folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Configure the plugin by going to the "Reading" menu (WP3.5+) or "Privacy" (earlier versions) under "Settings".

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

In 7.3.2, two new filters have been added that can be utilized to help prevent IP spoofing attacks. The first filter allows you to set up a list of approved proxy IP addresses and the second allows you to set up a list of approved HTTP headers. By default, these filters will not change existing behavior. It is recommended to review these filters and utilize them appropriately for your site to secure things further.

If your site is not running behind a proxy, we recommend doing the following:

`
add_filter( 'rsa_trusted_headers', '__return_empty_array' );
`

This will then only use the `REMOTE_ADDR` HTTP header to determine the IP address of the visitor. This header can't be spoofed, so this will increase security.

If your site is running behind a proxy (like a CDN), you can't rely on the `REMOTE_ADDR` HTTP header, as this will contain the IP address of the proxy, not the user. If your proxy uses static IP addresses, we recommend using the `rsa_trusted_proxies` filter to set those trusted IP addresses:

`
add_filter( 'rsa_trusted_proxies', 'my_rsa_trusted_proxies' );

function my_rsa_trusted_proxies( $trusted_proxies = array() ) {
  // Set one or more trusted proxy IP addresses.
  $proxy_ips       = array(
    '10.0.0.0/24',
    '10.0.0.0/32',
  );
  $trusted_proxies = array_merge( $trusted_proxies, $proxy_ips );

  return array_unique( $trusted_proxies );
}
`

And then use the `rsa_trusted_headers` filter to set which HTTP headers you want to trust. Consult with your proxy provider to determine which header(s) they use to hold the original client IP:

`
add_filter( 'rsa_trusted_headers', 'my_rsa_trusted_headers' );

function my_rsa_trusted_headers( $trusted_headers = array() ) {
  // Set one or more trusted HTTP headers.
  $headers = array(
    'HTTP_X_FORWARDED',
    'HTTP_FORWARDED',
  );

  return $headers;
}
`

If your proxy does not use static IP addresses, you can still utilize the `rsa_trusted_headers` filter to change which HTTP headers you want to trust.

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

= Is there a way to configure this with [WP-CLI](https://make.wordpress.org/cli/)? =

As of version 7.0.0, CLI integration has been added. To see the available commands, type the following in your WordPress directory:

`
$ wp rsa
`

= How can I programatically define whitelisted IPs? =

In 7.0.0, the capacity to define a pipe delimited array of whitelisted IP addresses via constant was introduced.

In your `wp-config.php` file, you can define the following:

`
define( 'RSA_IP_WHITELIST', '192.0.0.1|192.0.0.10' );
`

In 7.1.1, the capacity to programmatically add / remove / set access IPs programmatically was introduced.

The following are valid statements:

Set IPs, ignoring all stored values (but not the constant defined values), if you're going to use the approach with array indices rather than mixing the two.

`
Restricted_Site_Access::set_ips( array( '192.168.0.1', '192.168.0.2', '192.168.0.3' ) );
Restricted_Site_Access::set_ips( array( 'labelfoo' => '192.168.0.1', 'labelbar' => 192.168.0.2', 'labelbaz' => 192.168.0.3' ) );
`

Add IPs, if they're not already added.

`
Restricted_Site_Access::add_ips( array( 'five' => '192.168.1.5', 'six' => '192.168.1.6') );
`

Remove IPs, if they are in the list.

`
Restricted_Site_Access::remove_ips( array( '192.168.1.2','192.168.1.5','192.168.1.6', ) );
`

= Is there a constant I can set to ensure my site is (or is not) restricted? =

As of version 7.1.0, two constants were introduced that give you the ability to specify if the site should be in restricted mode.

You can force the plugin to be in restricted mode by adding the following to your `wp-config.php` file:

`
define( 'RSA_FORCE_RESTRICTION', true );
`

Or to ensure your site won't be in restricted mode:

`
define( 'RSA_FORBID_RESTRICTION', true );
`

Make sure you add it before the `/* That's all, stop editing! Happy blogging. */` line.

Please note that setting `RSA_FORCE_RESTRICTION` will override `RSA_FORBID_RESTRICTION` if both are set.

== Screenshots ==

1. Screenshot of settings panel with simple Restricted Site Access option (send to login page).
1. Screenshot of settings panel with restriction message option enabled
1. Plenty of inline help! Looks and behaves like native WordPress help.

== Changelog ==

= 7.4.0 - TBD =
* **Added:** Support for application passwords (props [@kirtangajjar](https://github.com/kirtangajjar), [@peterwilsoncc](https://github.com/peterwilsoncc), [@Sidsector9](https://github.com/Sidsector9) via [#247](https://github.com/10up/restricted-site-access/pull/247)).
* **Added:** Support for custom header based allow-listing (props [@mikelking](https://github.com/mikelking), [@ravinderk](https://github.com/ravinderk), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#242](https://github.com/10up/restricted-site-access/pull/242)).
* **Changed:** [Support Level](https://github.com/10up/restricted-site-access#support-level) from `Active` to `Stable` (props [@jeffpaul](https://github.com/jeffpaul, [@Sidsector9](https://github.com/Sidsector9)) via [#244](https://github.com/10up/Ad-Refresh-Control/pull/244)).
* **Changed:** Bump WordPress "tested up to" version 6.2 (props [@jayedul](https://github.com/jayedul), [@Sidsector9](https://github.com/Sidsector9) via [#251](https://github.com/10up/restricted-site-access/pull/251))
* **Changed:** Improve Github actions workflow (props [@Sidsector9](https://github.com/Sidsector9), [@dkotter](https://github.com/dkotter) via [#227](https://github.com/10up/restricted-site-access/pull/227), [#253](https://github.com/10up/restricted-site-access/pull/253)).
* **Fixed:** Plugin settings header UX (props [@barryceelen](https://github.com/barryceelen), [@Sidsector9](https://github.com/Sidsector9) via [#236](https://github.com/10up/restricted-site-access/pull/236)).
* **Fixed:** Issue that caused redirect loop (props [@Sidsector9](https://github.com/Sidsector9), [@cadic](https://github.com/cadic), [@peterwilsoncc](https://github.com/peterwilsoncc)) via [#221](https://github.com/10up/restricted-site-access/issues/221).
* **Security:** Run E2E tests on the final ZIP build (props [@iamdharmesh](https://github.com/iamdharmesh), [@jayedul](https://github.com/jayedul) via [#249](https://github.com/10up/restricted-site-access/pull/249)).
* **Security:** Bump `json5` from `1.0.1` to `1.0.2` (props [@Sidsector9](https://github.com/Sidsector9) via [#241](https://github.com/10up/restricted-site-access/pull/241)).
* **Security:** Bump `simple-git` from `3.15.0` to `3.16.0` (props [@Sidsector9](https://github.com/Sidsector9) via [#243](https://github.com/10up/restricted-site-access/pull/243)).
* **Security:** Bump `http-cache-semantics` from 4.1.0 to 4.1.1 (props [@Sidsector9](https://github.com/Sidsector9) via [#245](https://github.com/10up/restricted-site-access/pull/245)).
* **Security:** Bump `@sideway/formula` from 3.0.0 to 3.0.1 (props [@Sidsector9](https://github.com/Sidsector9) via [#246](https://github.com/10up/restricted-site-access/pull/246)).
* **Security:** Bump `webpack` from `5.74.0` to `5.76.1` (props [@Sidsector9](https://github.com/Sidsector9) via [#248](https://github.com/10up/restricted-site-access/pull/248)).

= 7.3.5 - 2022-12-14 =
* **Added:** Show an admin notice if our autoloader doesn't exist (props [@dkotter](https://github.com/dkotter), [@pablojmarti](https://github.com/pablojmarti), [@shahzaib10up](https://github.com/shahzaib10up), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#231](https://github.com/10up/restricted-site-access/pull/231)).
* **Fixed:** Ensure we load our autoloader from the root of our plugin directory (props [@dkotter](https://github.com/dkotter), [@pablojmarti](https://github.com/pablojmarti), [@shahzaib10up](https://github.com/shahzaib10up), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#231](https://github.com/10up/restricted-site-access/pull/231)).
* **Changed:** Improved performance of our E2E tests (props [@Sidsector9](https://github.com/Sidsector9), [@iamdharmesh](https://github.com/iamdharmesh) via [#218](https://github.com/10up/restricted-site-access/pull/218)).
* **Changed:** Release instructions and release ZIP building via GitHub Action (props [@dkotter](https://github.com/dkotter), [@faisal-alvi](https://github.com/faisal-alvi) via [#232](https://github.com/10up/restricted-site-access/pull/232)).
* **Security:** Bump `loader-utils` from 2.0.3 to 2.0.4 (props [@dependabot](https://github.com/apps/dependabot) via [#226](https://github.com/10up/restricted-site-access/pull/226)).
* **Security:** Bump `simple-git` from 3.6.0 to 3.15.0 (props [@dependabot](https://github.com/apps/dependabot) via [#230](https://github.com/10up/restricted-site-access/pull/230)).

= 7.3.4 - 2022-11-01 =
* **Fixed:** Fatal error due to missing vendor directory.

= 7.3.3 - 2022-10-31 =
* **Added:** Support for IPv6 addresses (props [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9), [@cadic](https://github.com/cadic)).
* **Added:** Support for subnet range and pattern formats for IPv4 and IPv6 addresses (props [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9), [@cadic](https://github.com/cadic)).
* **Added:** WP VIP Coding Standards (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@faisal-alvi](https://github.com/faisal-alvi), [@eflorea](https://github.com/eflorea)).
* **Changed:** Improved adding IP user experience via settings (props [@ankitguptaindia](https://github.com/ankitguptaindia), [@dhanendran](https://github.com/dhanendran), [@Sidsector9](https://github.com/Sidsector9), [@dinhtungdu](https://github.com/dinhtungdu)).
* **Changed:** Replace Grunt with Webpack (props [@cadic](https://github.com/cadic), [@Sidsector9](https://github.com/Sidsector9)).
* **Fixed:** Missing textdomains to translatable strings (props [@pedro-mendonca](https://github.com/pedro-mendonca), [@Sidsector9](https://github.com/Sidsector9)).

= 7.3.2 - 2022-08-29 =
**Note:** this release contains two new filters that we recommend using to further secure your site. See the [readme](https://wordpress.org/plugins/restricted-site-access/#how%20secure%20is%20this%20plug-in%3F) for full details.

* **Added:** New filter - `rsa_get_client_ip_address_filter_flags` to modify the range of accepted IP addresses.
* **Changed:** Avoid disjointed plugin settings (props [@helen](https://github.com/helen), [@peterwilsoncc](https://github.com/peterwilsoncc), [@Sidsector9](https://github.com/Sidsector9)).
* **Changed:** Bump minimum WordPress version from 5.0 to 5.7 (props [@vikrampm1](https://github.com/vikrampm1), [@Sidsector9](https://github.com/Sidsector9), [@faisal-alvi](https://github.com/faisal-alvi)).
* **Changed:** Bump minimum PHP version from 5.6 to 7.4 (props [@vikrampm1](https://github.com/vikrampm1), [@Sidsector9](https://github.com/Sidsector9), [@faisal-alvi](https://github.com/faisal-alvi)).
* **Security:** New filters - `rsa_trusted_proxies` and `rsa_trusted_headers` have been added to help prevent IP spoofing attacks.

= 7.3.1 - 2022-06-30 =
* **Added:** PHP8 compatibility check GitHub Action (props [@Sidsector9](https://github.com/Sidsector9), [dkotter](https://github.com/dkotter)).
* **Added:** Dependency security scanning GitHub Action (props [@jeffpaul](https://github.com/jeffpaul)).
* **Changed:** Admin settings HTML semantics for easier testing (props [@Sidsector9](https://github.com/Sidsector9), [@faisal-alvi](https://github.com/faisal-alvi)).
* **Changed:** Bump WordPress "tested up to" version 6.0 (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@faisal-alvi](https://github.com/faisal-alvi), [@cadic](https://github.com/cadic), [@jeffpaul](https://github.com/jeffpaul)).
* **Changed:** Documentation, asset, and e2e test updates (props [@Sidsector9](https://github.com/Sidsector9), [@iamdharmesh](https://github.com/iamdharmesh)).
* **Fixed:** Check netmask range before IP is added (props [@Sidsector9](https://github.com/Sidsector9), [@PypWalters](https://github.com/PypWalters)).
* **Security:** Bump `minimist` from 1.2.5 to 1.2.6 (props [@dependabot](https://github.com/apps/dependabot)).
* **Security:** Bump `grunt` from 1.4.1 to 1.5.3 (props [@dependabot](https://github.com/apps/dependabot)).
* **Security:** Bump `async` from 2.6.3 to 2.6.4 (props [@dependabot](https://github.com/apps/dependabot)).

= 7.3.0 - 2022-02-08 =
* **Added:** Ability to add, remove, and set IPs programatically (props [@ivankruchkoff](https://github.com/ivankruchkoff), [@helen](https://github.com/helen), [@paulschreiber](https://github.com/paulschreiber)).
* **Added:** Cloudflare IP detection compatibility (props [@eightam](https://github.com/eightam), [@dinhtungdu](https://github.com/dinhtungdu)).
* **Added:** WP-CLI option to modify and retrieve IP entry labels (props [@Sidsector9](https://github.com/Sidsector9), [@dinhtungdu](https://github.com/dinhtungdu), [@mikelking](https://github.com/mikelking)).
* **Added:** Acceptance and end-to-end tests (props [@dinhtungdu](https://github.com/dinhtungdu), [@helen](https://github.com/helen), [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9), [@cadic](https://github.com/cadic)).
* **Added:** Issue management automation, JavaScript linting, and PHPUnit testing via GitHub Actions (props [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9), [@dinhtungdu](https://github.com/dinhtungdu), [@mitogh](https://github.com/mitogh)).
* **Changed:** Update WP-CLI code to use new API for add/remove/set IPs (props [@paulschreiber](https://github.com/paulschreiber), [@dinhtungdu](https://github.com/dinhtungdu)).
* **Changed:** Bump WordPress "tested up to" version 5.8 (props [@dinhtungdu](https://github.com/dinhtungdu), [@jeffpaul](https://github.com/jeffpaul), [@ankitguptaindia](https://github.com/ankitguptaindia), [@BBerg10up](https://github.com/BBerg10up), [@sudip-10up](https://github.com/sudip-10up)).
* **Changed:** Improved Composer configuration and support (props [@kopepasah](https://github.com/kopepasah), [@dinhtungdu](https://github.com/dinhtungdu)).
* **Changed:** Improved documentation (props [@jeffpaul](https://github.com/jeffpaul), [@dinhtungdu](https://github.com/dinhtungdu), [@helen](https://github.com/helen)).
* **Changed:** The default constant `WP_TESTS_DOMAIN` is replaced by a new constant `PHP_UNIT_TESTS_ENV` to allow testing correct redirections for restricted users by Cypress end-to-end tests (props [@faisal-alvi](https://github.com/faisal-alvi), [@Sidsector9](https://github.com/Sidsector9), [@dkotter](https://github.com/dkotter)).
* **Fixed:** Issue with allowed IPs and associated comments being offset (props [@adamsilverstein](https://github.com/adamsilverstein), [@helen](https://github.com/helen), [@ivankruchkoff](https://github.com/ivankruchkoff)).
* **Fixed:** Prevents new users from getting WordPress setup email, new user flow in multisite installations now work as expected (props [@dinhtungdu](https://github.com/dinhtungdu), [@wkw](https://github.com/wkw), [@jeffpaul](https://github.com/jeffpaul), [@ivanlopez](https://github.com/ivanlopez)).
* **Fixed:** Ensure assets are enqueued on correct screen only (props [@kopepasah](https://github.com/kopepasah), [@dinhtungdu](https://github.com/dinhtungdu), [@paulschreiber](https://github.com/paulschreiber), [@n8dnx](https://github.com/n8dnx)).
* **Fixed:** Use correct variable for screen reader text (props [@dinhtungdu](https://github.com/dinhtungdu), [@lkraav](https://github.com/lkraav)).
* **Fixed:** Set the correct filter option value to `site_public` if `RSA_FORBID_RESTRICTION` is defined (props [@pabamato](https://github.com/pabamato), [@dinhtungdu](https://github.com/dinhtungdu)).
* **Fixed:** Prevent redirect loops when Redirect URL set on the same domain with or without Redirect to same path enabled (props [@Sidsector9](https://github.com/Sidsector9), [@faisal-alvi](https://github.com/faisal-alvi), [@cadic](https://github.com/cadic) via [#158](https://github.com/10up/restricted-site-access/pull/158)).
* **Fixed:** Undefined key "url" warning (props [@Sidsector9](https://github.com/Sidsector9)).
* **Fixed:** `Redirect to same path` setting screen-reader-text (props [@pedro-mendonca](https://github.com/pedro-mendonca)).
* **Fixed:** No loading of JS admin scripts on the network admin page (props [@Sidsector9](https://github.com/Sidsector9), [@dinhtungdu](https://github.com/dinhtungdu)).
* **Security:** Bump `websocket-extensions` from 0.1.3 to 0.1.4 (props [@dependabot](https://github.com/apps/dependabot)).
* **Security:** Bump `lodash` from 4.17.15 to 4.17.21 (props [@dependabot](https://github.com/apps/dependabot)).
* **Security:** Bump `rmccue/requests` from 1.7.0 to 1.8.0 (props [@dependabot](https://github.com/apps/dependabot)).
* **Security:** Bump `grunt` from 1.0.4 to 1.3.0 (props [@dependabot](https://github.com/apps/dependabot)).
* **Security:** Bump `path-parse` from 1.0.6 to 1.0.7 (props [@dependabot](https://github.com/apps/dependabot)).

= 7.2.0 - 2019-11-27 =
* **Added:** Warn and confirm before network disabling the plugin (props [@pereirinha](profiles.wordpress.org/pereirinha), [@adamsilverstein](https://profiles.wordpress.org/adamsilverstein/))
* **Fixed:** Ensure comments associated with IPs stay associated correctly (props [@adamsilverstein](https://profiles.wordpress.org/adamsilverstein/), [@ivankk](https://profiles.wordpress.org/ivankk/), [@helen](https://profiles.wordpress.org/helen/))
* **Fixed:** Don't show escaped HTML in page caching notice (props [@adamsilverstein](https://profiles.wordpress.org/adamsilverstein/), [@aaemnnosttv](https://profiles.wordpress.org/aaemnnosttv/))
* **Fixed:** Multisite: Avoid a redirect loop when logging in as user with no role (props [@phyrax](https://profiles.wordpress.org/phyrax/), [@adamsilverstein](https://profiles.wordpress.org/adamsilverstein/), [@roytanck](https://profiles.wordpress.org/roytanck/), [@helen](https://profiles.wordpress.org/helen/), [@rmccue](https://profiles.wordpress.org/rmccue/))

= 7.1.0 - 2019-04-11 =
* Added: IP whitelist: Add a Comment field next to each IP address to help identify IP addresses added to the whitelist.
* Added: Add constants to force enable/disable restrictions. Set `RSA_FORCE_RESTRICTION` to `true` to force restriction or `RSA_FORBID_RESTRICTION` to disable restriction. `RSA_FORCE_RESTRICTION` will override `RSA_FORBID_RESTRICTION` if both are set.
* Fixed: Disable individual site settings when network enforced mode is on to avoid confusion about why your settings are not being respected.
* Fixed: Correctly load admin JS.
* Fixed: Improve coding standards across plugin and introduce continuous integration linting against the WordPress coding standards. Update code to VIP Go coding standards.
* Developers: Add unit tests accross plugin. Note that when the `WP_TESTS_DOMAIN` constant is set, plugin redirects are disabled. Only set this constant when running the tests.
* Developers: Deploy plugin from GitHub to WordPress.org using GitHub Actions.
* Developers: Add various GitHub community files.

= 7.0.1 - 2018-09-06 =
* Bug fix: Avoid redirect loop when the unrestricted page is set to be the static front page.
* Bug fix: Fall back to the login screen if the unrestricted page is no longer published.

= 7.0.0 - 2018-08-30 =
* Feature: WP-CLI support! 🎉 Try `wp rsa` to get started.
* Feature: Whitelist IPs via the `RSA_IP_WHITELIST` constant.
* Feature: Use WordPress.org-provided language packs instead of bundled translations.
* Bug fix: Restrict "virtual pages" and allow them to be used as the unrestricted page, such as with BuddyPress.
* Bug fix: Hide settings properly when no published pages exist.
* Bug fix: Avoid double slashes in asset URLs that can lead to 404 errors.

= 6.2.1 - 2018-05-21 =
* Bug fix: Don't redirect logged-in users viewing the site in a single site install.

= 6.2.0 - 2018-05-18 =
* **Functionality change:** Check user's role on a site in multisite before granting permission.
* Feature: Alter or restore previous user permission checking with the `restricted_site_access_user_can_access` filter.
* Avoid a fatal due to differing parameter counts for the `restricted_site_access_is_restricted` filter.

= 6.1.0 - 2018-02-14 =
* Correct a PHP notice when running PHP >= 7.1.
* Refactor logic for checking ip address is in masked ip range.
* Add PHPUnit tests validating the ip_in_mask functionality.

= 6.0.2 - 2018-01-29 =
* Add a 'restrict_site_access_ip_match' action which fires when an ip match occurs. Enables adding session_start() to the IP check, ensuring Varnish type cache will not cache the request.

= 6.0.1 - 2017-06-13 =
* When plugin is network activated, don't touch individual blog visiblity settings.
* When plugin is network deactivated, set all individual blogs to default visibility.

= 6.0 - 2017-06-12 =
* Use Grunt to manage assets.
* Network settings added for management of entire network visibility settings.
* Display warning if page caching is enabled.

__Note: There is currently an edge case bug affecting IP whitelisting. This bug is on the docket to be fixed shortly.__

= 5.1 - 2014-11-29 =
* Under the hood refactoring and clean up for performance and maintainability.
* Small visual refinements to the settings panel.

= 5.0.1 - 2013-01-27 =
* Does not block user activation page in network mode

= 5.0 - 2012-11-02 =
* WordPress 3.5 compatibility (3.5 eliminated the Privacy settings panel in favor of a refreshed Reading panel)
* Real validation (on the fly and on save) for IP address entries
* "Restriction message" now supports simple HTML and is edited using WordPress's simple HTML tag editor
* A bunch of visual refinements that conform better with WordPress 3.4 and newer (spacing, native "shake" effect on invalid entries just like the login form, etc.)
* A bunch of under the hood refinements (e.g. playing nicer with current screen Help API)

= 4.0 - 2011-07-16 =
* New restriction option - show restricted visitor a specified page; use with custom page templates for great for website teasers!
* Major improvements to settings user interface, including hiding unused fields based on settings, easier selection of restriction type, and cleaner "remove" confirmation for IP address list
* Performance improvements - catches and blocks restricted visitors earlier in the loading process
* New filter hooks for other developers: 'restricted_site_access_is_restricted', 'restricted_site_access_approach', 'restricted_site_access_redirect_url', and 'restricted_site_access_head'
* Localization ready - rough Spanish translation included!
* Basic support for no JavaScript mode
* Optimized for PHP 5.2, per new WordPress 3.2 requirements (no longer supports PHP < 5.2.4)
* Assorted other improvements and optimizations to the code base

= 3.2.1 - 2011-03-25 =
* Restored PHP4 compatibility

= 3.2 - 2011-03-25 =
* More meaningful page title in "Display Message" mode (previously WordPress > Error)
* Code clean up, prevent rare warnings in debug mode

= 3.1.1 - 2010-07-17 =
* Fixed PHP warning when debugging is enabled and redirect path is not checked

= 3.1 - 2010-07-11 =
* New feature: backwards compatibility with PHP < 5.1 (limited testing with earlier versions)
* Bug fix: disappearing blocked access message text box on configuration page
* Bug fix: login always redirects visitor back to correct page
* Improved: built in help on configuration page updated, clearer
* Improved: "IP already in list" indicator
* Improved: optimizations to code that handles restriction behavior

= 3.0 - 2010-07-05 =
* Integrates with Privacy settings page and site visibility option instead of adding a whole new page
* Simplified options: clearer instructions, removed unnecessary hiding / showing of some options, fewer lines
* Indicates whether the site is blocked in the admin next to the site title (WordPress 3.0+ only)
* New action hook, `restrict_site_access_handling`, allowing developers to add their own restriction handling
* Cleans up / removes settings when uninstalled
* Assorted under the hood improvements for best coding practices, sanitization of options, etc

= 2.1 - 2010-02-10 =
* Customize blocked visitor message
* Stronger security (patched "search" hole)
* Better display / handling of blocked visitor message

= 2.0 - 2010-01-10 =
* Add support for IP ranges courtesy Eric Buth
* Major UI changes and improvements; major code improvements

= 1.0.2 - 2009-10-13 =
* Fix login redirect to home; improve redirect handling to take advantage of wp_redirect function

= 1.0.1 - 2009-09-10 =
* Important fundamental change related to handling of what should be restricted

= 1.0 - 2009-08-17 =
* **Added:** Initial public release.

== Upgrade Notice ==

= 7.4.0 =
Changes the [Support Level](https://github.com/10up/restricted-site-access#support-level) from `Active` to `Stable`.

= 7.3.2 =
Drops support for versions of WordPress prior to 5.7.
Drops support for versions of PHP prior to 7.4.

= 6.2.1 =
IMPORTANT MULTISITE FUNCTIONALITY CHANGE: User access is now checked against their role on a given site in multisite. To restore previous behavior, use the new restricted_site_access_user_can_access filter.

= 6.2.0 =
IMPORTANT MULTISITE FUNCTIONALITY CHANGE: User access is now checked against their role on a given site in multisite. To restore previous behavior, use the new restricted_site_access_user_can_access filter.

= 6.1.0 =
* Important: version 6.1 improves testing visitors for allowed IP addresses ("Unrestricted IP addresses"). We recommend testing IP based restrictions after updating.

= 5.1 =
Drops support for versions of WordPress prior to 3.5.

= 4.0 =
This update improves performance, refines the user interface, and adds support for showing restricted visitors a specific page. Please be advised that this udpate is specifically designed for WordPress 3.2+, and like WordPress 3.2, <strong>no longer supports PHP < 5.2.4</strong>.
