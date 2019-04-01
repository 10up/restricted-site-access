[![Build Status](https://travis-ci.org/10up/restricted-site-access.svg?branch=develop)](https://travis-ci.org/10up/restricted-site-access)

# Restricted Site Access

Limit access your site to visitors who are logged in or accessing the site from a set of specified IP addresses. Send restricted visitors to the log in page, redirect them, or display a message or page. A great solution for Extranets, publicly hosted Intranets, or parallel development / staging sites.

Adds a number of new configuration options to the Reading settings panel. From this panel you can:

* Enable or disable site restriction
* Change the restriction behavior: send to login, redirect, display a message, display a page
* Add IP addresses to an unrestricted list, including ranges
* Quickly add your current IP to the unrestricted list
* Customize the redirect location, including an option to send them to the same requested path and set the HTTP status code for SEO friendliness
* Define a simple message to show restricted visitors, or select a page to show them - great for "coming soon" teasers!

<p align="center">
<a href="http://10up.com/contact/"><img src="https://10updotcom-wpengine.s3.amazonaws.com/uploads/2016/10/10up-Github-Banner.png" width="850"></a>
</p>

## Installation

1. Install easily with the WordPress plugin control panel or manually download the plugin and upload the extracted folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the "Plugins" menu in WordPress
3. Configure the plugin by going to the Settings &rsaquo; Reading page in WordPress.


## Frequently-asked Questions

### Where do I change the restriction settings?

Restricted Site Access settings are added to the Reading page, with WordPress’s built in site privacy options. (It was moved there from a separate Privacy settings page in 3.5.)

### It’s not working! My site is wide open!

Most commonly, Restricted Site Access is not compatible with some page caching solutions. While the plugin hooks in as early as it can to check visitor permissions, its important to understand that some page caching plugins generate static output that prevents plugins like Restricted Site Access from ever checking individual visitors.

To the extent that sites blocked by this plugin should not need to concern themselves with high scale front end performance, we strongly recommend disabling any page caching solutions while restricting access to your site. Keep in mind that most page caching plugins do not cache the “logged in” experience, anyhow. Also note that the plugin *is* fully compatible with other caching layers, like the WordPress object cache.

### How do I allow access to specific pages or parts of my site?

Developers can use the `restricted_site_access_is_restricted` filter to override normal restriction behavior. Note that restriction checks happen before WordPress executes any queries; it passes the query request from the global `$wp` variable so developers can investigate what the visitor is trying to load.

For instance, to unblock an RSS feed, place the following PHP code in the theme's functions.php file or in a simple plug-in:

```php
add_filter( 'restricted_site_access_is_restricted', 'my_rsa_feed_override', 10, 2 );

function my_rsa_feed_override( $is_restricted, $wp ) {
	// check query variables to see if this is the feed
	if ( ! empty( $wp->query_vars['feed'] ) ) {
		$is_restricted = false;
	}
	return $is_restricted;
}
```

### How secure is this plug-in?

Visitors that are not logged in or allowed by IP address will not be able to browse your site (though be cautious of page caching plugin incompatibilities, mentioned above). Restricted Site Access does not block access to your, so direct links to files in your media and uploads folder (for instance) are not blocked. It is also important to remember that IP addresses can be spoofed. Because Restricted Site Access runs as a plug-in, it is subject to any other vulnerabilities present on your site.

Restricted Site Access is not meant to be a top secret data safe, but simply a reliable and convenient way to handle unwanted visitors.

### Why can't logged-in users see all the sites on my multisite instance?

In 6.2.0, the behavior in a multisite install changed from allowing any logged-in user to see a site to checking their role for that specific site. This is a safer default given the varying ways multisite is used; however, if you would prefer to rely on the previous behavior rather than explicitly adding users to each site, place the following PHP code in the theme's functions.php file or in a simple plug-in:

```php
add_filter( 'restricted_site_access_user_can_access', 'my_rsa_user_can_access' );

function my_rsa_user_can_access( $access ) {
	if ( is_user_logged_in() ) {
		return true;
	}

	return $access;
}
```

### Is there a way to configure this with [WP-CLI](https://make.wordpress.org/cli/)?

As of version 7.0.0, CLI integration has been added. To see the available commands, type the following in your WordPress directory:

```bash
$ wp rsa
```

### How can I programmatically define whitelisted IPs?

In 7.0.0, the capacity to define a pipe delimited array of whitelisted IP addresses via constant was introduced.

In your `wp-config.php` file, you can define the following:

```php
define( 'RSA_IP_WHITELIST', '192.0.0.1|192.0.0.10' );
```

### Is there a constant I can set to ensure my site is (or is not) restricted?

As of version 7.1.0, two constants were introduced that give you the ability to specify if the site should be in restricted mode.

You can force the plugin to be in restricted mode by adding the following to your `wp-config.php` file:

```php
define( 'RSA_FORCE_RESTRICTION', true );
```
Or to ensure your site won't be in restricted mode:

```php
define( 'RSA_FORBID_RESTRICTION', true );
```

Make sure you add it before the `/* That's all, stop editing! Happy blogging. */` line.

Please note that setting `RSA_FORCE_RESTRICTION` will override `RSA_FORBID_RESTRICTION` if both are set.

## Contributing

Please read [CODE_OF_CONDUCT.md](https://github.com/10up/restricted-site-access/blob/develop/CODE_OF_CONDUCT.md) for details on our code of conduct and [CONTRIBUTING.md](https://github.com/10up/restricted-site-access/blob/develop/CONTRIBUTING.md) for details on the process for submitting pull requests to us.

## License

Restricted Site Access utilizes the [GNU General Public License v2.0 (or later)](https://github.com/10up/restricted-site-access/blob/develop/LICENSE.md).
