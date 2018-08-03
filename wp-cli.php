<?php

/**
 * Manages the Restricted Site Access plugin settings.
 *
 * ## EXAMPLES
 *
 *    # Restricts site access.
 *    $ wp rsa set-mode login
 *    Success: Redirecting visitors to login screen.
 *
 *    # Whitelists IP addresses.
 *    $ wp rsa ip-add 192.0.0.1
 *    Success: Added 192.0.0.1 to the whitelist.
 */
class Restricted_Site_Access_CLI extends WP_CLI_Command {
	/**
	 * Sets how the site is restricted.
	 *
	 * ## OPTIONS
	 *
	 * <mode>
	 * : What mode to set the plugin to.
	 * ---
	 * options:
	 *   - disable
	 *   - login
	 *   - redirect
	 *   - message
	 *   - page
	 * ---
	 *
	 * [--url=<url>]
	 * : What URL to redirect visitors to in case of the redirect mode.
	 *
	 * [--text=<text>]
	 * : What message to display in case of message mode.
	 *
	 * [--page=<page-id>]
	 * : What page ID to display in case of page mode.
	 *
	 * ## EXAMPLES
	 *
	 *    # Disables site restriction.
	 *    $ wp rsa set-mode disable
	 *    Success: Disabled site restrictions.
	 *
	 *    # Redirects site visitors to a URL.
	 *    $ wp rsa set-mode redirect --url=http://example.com
	 *    Success: Redirecting visitors to "http://example.com".
	 *
	 *    # Shows site visitors a message.
	 *    $ wp rsa set-mode message --text="None shall pass!"
	 *    Success: Site message set.
	 *
	 *    # Shows site visitors a page.
	 *    wp rsa set-mode page --page=123
	 *    Success: Showing visitors page "Welcome".
	 *
	 * @subcommand set-mode
	 * @alias mode
	 *
	 * @param array $args       Array with single value of what mode to set.
	 * @param array $assoc_args Array with optional flags described above.
	 */
	public function set_mode( $args, $assoc_args ) {

	}

	/**
	 * Lists current IP whitelist.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *    # Outputs currently whitelisted IPs in CSV format.
	 *    $ wp rsa ip-list --format=csv
	 *    192.0.0.1,10.10.0.0
	 *
	 * @subcommand ip-list
	 *
	 * @param array $args       Positional arguments. Not used.
	 * @param array $assoc_args Array with format value.
	 */
	public function ip_list( $args, $assoc_args ) {
		$options = get_option( 'rsa_options' );
		if ( empty( $options['allowed'] ) ) {
			return;
		}
		$format = WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );

		$items = array();
		foreach ( $options['allowed'] as $ip ) {
			$items[] = compact( 'ip' );
		}

		WP_CLI\Utils\format_items( $format, $items, array( 'ip' ) );
	}

	/**
	 * Adds one or more IP addresses to the whitelist.
	 *
	 * ## OPTIONS
	 *
	 * <ip>...
	 * : List of IP addresses to add to the whitelist.
	 *
	 * ## EXAMPLES
	 *    wp rsa ip-add 192.0.0.1
	 *
	 * @subcommand ip-add
	 *
	 * @param array $args List of IPs to whitelist.
	 */
	public function ip_add( $args ) {

	}

	/**
	 * Removes one or more IP addresses from the whitelist.
	 *
	 * ## OPTIONS
	 *
	 * <ip>...
	 * : List of IP addresses to remove from the whitelist.
	 *
	 * ## EXAMPLES
	 *    wp rsa ip-remove 192.0.0.1
	 *
	 * @subcommand ip-remove
	 *
	 * @param array $args List of IPs to blacklist.
	 */
	public function ip_remove( $args ) {

	}

	/**
	 * Sets list of IPs to whitelist. Overwrites current settings.
	 *
	 * ## OPTIONS
	 *
	 * <ip>...
	 * : List of IP addresses to whitelist.
	 *
	 * ## EXAMPLES
	 *    wp rsa ip-set 192.0.0.1
	 *
	 * @subcommand ip-set
	 *
	 * @param array $args List of IPs to set.
	 */
	public function ip_set( $args ) {

	}
}

WP_CLI::add_command( 'rsa', 'Restricted_Site_Access_CLI' );
