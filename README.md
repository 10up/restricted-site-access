# Restricted Site Access

> Limit access to visitors who are logged in or allowed by IP addresses. Includes many options for handling blocked visitors.

[![Support Level](https://img.shields.io/badge/support-active-green.svg)](#support-level) [![E2E Tests](https://github.com/10up/restricted-site-access/actions/workflows/cypress.yml/badge.svg)](https://github.com/10up/restricted-site-access/actions/workflows/cypress.yml) [![PHPUnit](https://github.com/10up/restricted-site-access/actions/workflows/phpunit.yml/badge.svg)](https://github.com/10up/restricted-site-access/actions/workflows/phpunit.yml) [![Release Version](https://img.shields.io/github/release/10up/restricted-site-access.svg)](https://github.com/10up/restricted-site-access/releases/latest) ![WordPress tested up to version](https://img.shields.io/wordpress/plugin/tested/restricted-site-access?label=WordPress) [![GPLv2 License](https://img.shields.io/github/license/10up/restricted-site-access.svg)](https://github.com/10up/restricted-site-access/blob/develop/LICENSE.md)

## Table of Contents
* [Features](#features)
* [Installation](#installation)
* [FAQs](#frequently-asked-questions)
  * [Where do I change the restriction settings?](#where-do-i-change-the-restriction-settings)
  * [It’s not working! My site is wide open!](#its-not-working-my-site-is-wide-open)
  * [How do I allow access to specific parts of my site?](#how-do-i-allow-access-to-specific-pages-or-parts-of-my-site)
  * [How secure is this plug-in?](#how-secure-is-this-plug-in)
  * [Why can't logged-in multisite users see all my sites?](#why-cant-logged-in-users-see-all-the-sites-on-my-multisite-instance)
  * [Is there a way to configure this with WP-CLI?](#is-there-a-way-to-configure-this-with-wp-cli)
  * [How can I programmatically define whitelisted IPs?](#how-can-i-programmatically-define-whitelisted-ips)
  * [Is there a constant to control my site restriction?](#is-there-a-constant-i-can-set-to-ensure-my-site-is-or-is-not-restricted)
  * [Can I provide access to my site based on custom HTTP headers?](#can-i-provide-access-to-my-site-based-on-custom-http-headers)
* [Support](#support-level)
* [Changelog](#changelog)
* [Contributing](#contributing)

## Features

Limit access your site to visitors who are logged in or accessing the site from a set of specified IP addresses. Send restricted visitors to the log in page, redirect them, or display a message or page. A great solution for Extranets, publicly hosted Intranets, or parallel development / staging sites.

Adds a number of new configuration options to the Reading settings panel as well as the Network Settings panel in multisite. From this panel you can:

* Enable or disable site restriction
* Change the restriction behavior: send to login, redirect, display a message, display a page
* Add IP addresses to an unrestricted list, including ranges
* Quickly add your current IP to the unrestricted list
* Customize the redirect location, including an option to send them to the same requested path and set the HTTP status code for SEO friendliness
* Define a simple message to show restricted visitors, or select a page to show them - great for "coming soon" teasers!

## Installation

1. Install easily with the WordPress plugin control panel or manually download the plugin and upload the extracted folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Configure the plugin by going to the Settings &rsaquo; Reading page in WordPress.

## Frequently Asked Questions

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

In 7.3.2, two new filters have been added that can be utilized to help prevent IP spoofing attacks. The first filter allows you to set up a list of approved proxy IP addresses and the second allows you to set up a list of approved HTTP headers. By default, these filters will not change existing behavior. It is recommended to review these filters and utilize them appropriately for your site to secure things further.

If your site is not running behind a proxy, we recommend doing the following:

```php
add_filter( 'rsa_trusted_headers', '__return_empty_array' );
```

This will then only use the `REMOTE_ADDR` HTTP header to determine the IP address of the visitor. This header can't be spoofed, so this will increase security.

If your site is running behind a proxy (like a CDN), you can't rely on the `REMOTE_ADDR` HTTP header, as this will contain the IP address of the proxy, not the user. If your proxy uses static IP addresses, we recommend using the `rsa_trusted_proxies` filter to set those trusted IP addresses:

```php
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
```

And then use the `rsa_trusted_headers` filter to set which HTTP headers you want to trust. Consult with your proxy provider to determine which header(s) they use to hold the original client IP:

```php
add_filter( 'rsa_trusted_headers', 'my_rsa_trusted_headers' );

function my_rsa_trusted_headers( $trusted_headers = array() ) {
  // Set one or more trusted HTTP headers.
  $headers = array(
    'HTTP_X_FORWARDED',
    'HTTP_FORWARDED',
  );

  return $headers;
}
```

If your proxy does not use static IP addresses, you can still utilize the `rsa_trusted_headers` filter to change which HTTP headers you want to trust.

### I received a warning about page caching. What does it mean?

Page caching plugins often hook into WordPress to quickly serve the last cached output of a page before we can check to see if a visitor’s access should be restricted. Not all page caching plugins behave the same way, but several solutions - including external solutions we might not detect - can cause restricted pages to be publicly served regardless of your settings.

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

In 7.1.1, the capacity to programmatically add / remove / set access IPs programmatically was introduced.

The following are valid statements:

Set IPs, ignoring all stored values (but not the constant defined values), if you're going to use the approach with array indices rather than mixing the two.
```php
Restricted_Site_Access::set_ips( array( '192.168.0.1', '192.168.0.2', '192.168.0.3' ) );
Restricted_Site_Access::set_ips( array( 'labelfoo' => '192.168.0.1', 'labelbar' => 192.168.0.2', 'labelbaz' => 192.168.0.3' ) );
```

Add IPs, if they're not already added.
```php
Restricted_Site_Access::add_ips( array( 'five' => '192.168.1.5', 'six' => '192.168.1.6') );
```

Remove IPs, if they are in the list.
```php
Restricted_Site_Access::remove_ips( array( '192.168.1.2','192.168.1.5','192.168.1.6', ) );
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

### Can I provide access to my site based on custom HTTP headers?
You can use the `restricted_site_access_is_restricted` filter hook to allow access based on custom headers.
The custom header you want to allow should be present in the request and should contain a unique value. If needed, you can allow more than one header.
If these header/value pairs are ever compromised, you should change the accepted values in order to protect your site.

See below for an example code snippet you can utilize:

```php
<?php
/**
 * Add custom trusted header validation.
 *
 * IP restriction will be bypassed if the trusted custom header is present and has the correct value.
 */
add_filter( 'restricted_site_access_is_restricted', function ( $is_restricted ) {
	// Custom trusted headers; array key should be the header name and value should be the header value.
	$allowed_custom_trusted_headers = array(
		'HTTP_RSA_CUSTOM_HEADER' => 'value' // Replace header and value with your custom details.
	);

	// Ensure trusted headers exist in request.
	if ( ! array_intersect_key( $_SERVER, $allowed_custom_trusted_headers ) ) {
		return $is_restricted;
	}

	// Ensure all the trusted headers have the correct value.
	foreach ( $allowed_custom_trusted_headers as $header => $value ) {
		if ( $value !== $_SERVER[ $header ] ) { // phpcs:ignore

			// Return true to apply ip restriction.
			return true;
		}
	}

	// Return false to bypass ip restriction.
	return false;
} );
```

## Support Level

**Active:** 10up is actively working on this, and we expect to continue work for the foreseeable future including keeping tested up to the most recent version of WordPress.  Bug reports, feature requests, questions, and pull requests are welcome.

## Changelog

A complete listing of all notable changes to Restricted Site Access are documented in [CHANGELOG.md](https://github.com/10up/restricted-site-access/blob/develop/CHANGELOG.md).

## Contributing

Please read [CODE_OF_CONDUCT.md](https://github.com/10up/restricted-site-access/blob/develop/CODE_OF_CONDUCT.md) for details on our code of conduct, [CONTRIBUTING.md](https://github.com/10up/restricted-site-access/blob/develop/CONTRIBUTING.md) for details on the process for submitting pull requests to us, and [CREDITS.md](https://github.com/10up/restricted-site-access/blob/develop/CREDITS.md) for a listing of maintainers of, contributors to, and libraries used by Restricted Site Access.

## Like what you see?

<p align="center">
<a href="http://10up.com/contact/"><img src="https://10up.com/uploads/2016/10/10up-Github-Banner.png" width="850"></a>
</p>
