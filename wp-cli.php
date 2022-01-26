<?php // phpcs:disable WordPress.Files.FileName
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
	 * Stored command positional arguments.
	 *
	 * @var array
	 */
	private $args;

	/**
	 * Stored command associative arguments.
	 *
	 * @var array
	 */
	private $assoc_args;

	/**
	 * Whether the command is operating on the network or a single site.
	 *
	 * @var bool
	 */
	private $is_network = false;

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
	 * [--redirect=<url>]
	 * : What URL to redirect visitors to in case of the redirect mode.
	 *
	 * [--same-path]
	 * : Preserve the path in case of the redirect mode.
	 *
	 * [--status-code=<code>]
	 * : What status code to use, in case of the redirect mode.
	 * ---
	 * options:
	 *   - 301
	 *   - 302
	 *   - 307
	 * default: 302
	 * ---
	 *
	 * [--text=<text>]
	 * : What message to display in case of message mode.
	 *
	 * [--page=<page-id>]
	 * : What page ID to display in case of page mode.
	 *
	 * [--network]
	 * : Multisite only. Sets this configuration for the network.
	 *
	 * ## EXAMPLES
	 *
	 *    # Disables site restriction.
	 *    $ wp rsa set-mode disable
	 *    Success: Site restrictions disabled.
	 *
	 *    # Redirects site visitors to a URL.
	 *    $ wp rsa set-mode redirect --url=http://example.com
	 *    Success: Site redirecting visitors to "http://example.com".
	 *
	 *    # Shows site visitors a message.
	 *    $ wp rsa set-mode message --text="None shall pass!"
	 *    Success: Site message set.
	 *
	 *    # Shows site visitors a page.
	 *    wp rsa set-mode page --page=123
	 *    Success: Site showing visitors page "Welcome".
	 *
	 * @subcommand set-mode
	 * @alias mode
	 *
	 * @param array $args       Array with single value of what mode to set.
	 * @param array $assoc_args Array with optional flags described above.
	 * @return void
	 */
	public function set_mode( $args, $assoc_args ) {
		// We don't need to validate the mode, as WP-CLI ensures that's correct.
		$mode = $args[0];

		// Sets up and gets options.
		$this->setup( $args, $assoc_args );
		$options = $this->get_options();

		// Gets the current setting.
		$blog_public = (int) get_option( 'blog_public', 2 );
		if ( $this->is_network ) {
			$blog_public = (int) get_site_option( 'blog_public', 2 );
		}

		// Handles disabling the plugin.
		if ( 'disable' === $mode ) {
			if ( 2 !== $blog_public ) {
				WP_CLI::success(
					sprintf(
						/* translators: %s: What the user is updating: "Site" or "Network". */
						__( '%s already not under restricted access.', 'restricted-site-access' ),
						$this->update_text()
					)
				);
				return;
			}

			if ( $this->is_network ) {
				update_site_option( 'blog_public', 1 );
			} else {
				update_option( 'blog_public', 1 );
			}

			WP_CLI::success(
				sprintf(
					/* translators: %s: What the user is updating: "Site" or "Network". */
					__( '%s restrictions disabled.', 'restricted-site-access' ),
					$this->update_text()
				)
			);
			return; // Exit.
		}

		// Enables RSA if it's not already enabled.
		if ( 2 !== $blog_public ) {
			if ( $this->is_network ) {
				update_site_option( 'blog_public', 2 );
				WP_CLI::debug( 'Enabled RSA on network.' );
			} else {
				update_option( 'blog_public', 2 );
				WP_CLI::debug( 'Enabled RSA.' );
			}
		}

		// Login mode. Simple!
		if ( 'login' === $mode ) {
			$options['approach'] = 1;

		} elseif ( 'redirect' === $mode ) {
			$url = WP_CLI\Utils\get_flag_value( $assoc_args, 'redirect' );
			if ( ! $url ) {
				WP_CLI::error( __( 'Redirect URL required.', 'restricted-site-access' ) );
			}

			// Let WP-CLI validate the status code.
			$options = array_merge(
				$options,
				array(
					'approach'      => 2,
					'redirect_url'  => $url,
					'head_code'     => WP_CLI\Utils\get_flag_value( $assoc_args, 'status-code' ),
					'redirect_path' => (int) WP_CLI\Utils\get_flag_value( $assoc_args, 'same-path', 0 ),
				)
			);

			// End redirect mode.
		} elseif ( 'message' === $mode ) {
			// Set default for message text.
			$message = WP_CLI\Utils\get_flag_value( $assoc_args, 'text' );
			if ( ! $message ) {
				$message = __( 'Access to this site is restricted.', 'restricted-site-access' );
			}
			$options['approach'] = 3;
			$options['message']  = $message;

			// End message mode.
		} elseif ( 'page' === $mode ) {
			// Validate page ID passed.
			$page_id = (int) WP_CLI\Utils\get_flag_value( $assoc_args, 'page' );
			if ( ! $page_id ) {
				WP_CLI::error( __( 'Page required.', 'restricted-site-access' ) );
			}
			$page = get_post( $page_id );
			if ( ! $page || 'page' !== $page->post_type ) {
				WP_CLI::error( __( 'Page is invalid.', 'restricted-site-access' ) );
			}

			$options['approach'] = 4;
			$options['page']     = $page_id;
			// End page mode.
		}

		$updated_options = $this->update_options( $options );

		// Send update messages.
		$success_msg = '';
		switch ( $mode ) {
			case 'login':
				/* translators: %s: Context: "Site" or "Network". */
				$success_msg = __( '%s redirecting visitors to login.', 'restricted-site-access' );
				break;
			case 'redirect':
				$success_msg = sprintf(
					/* translators: %s: Context: "Site" or "Network". %s: Redirect URL. */
					__( '%%s redirecting visitors to "%s"', 'restricted-site-access' ),
					$updated_options['redirect_url']
				);
				break;
			case 'message':
				/* translators: %s: Context: "Site" or "Network". */
				$success_msg = __( '%s showing message to visitors.', 'restricted-site-access' );
				break;
			case 'page':
				$success_msg = sprintf(
					/* translators: %s: "Site" or "Network". %s: Page title. */
					__( '%%s showing visitors page "%s"', 'restricted-site-access' ),
					get_the_title( $page )
				);
				break;
			default:
				/* translators: %s: What the user is updating: "Site" or "Network". */
				$success_msg = __( '%s settings updated.', 'restricted-site-access' );
		}

		WP_CLI::success(
			sprintf(
				$success_msg,
				$this->update_text()
			)
		);
	}

	/**
	 * Sets the network mode.
	 *
	 * ## OPTIONS
	 *
	 * <mode>
	 * : Mode to set network.
	 * ---
	 * default: default
	 * options:
	 *   - default
	 *   - enforce
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *    # Sets the multisite to enforce mode.
	 *    $ wp rsa set-network-mode enforce
	 *    Success: Set network mode to enforced.
	 *
	 * @subcommand set-network-mode
	 *
	 * @param array $args       Array with single value of what mode to set.
	 * @param array $assoc_args Associative arguments. Not used.
	 */
	public function set_network_mode( $args, $assoc_args ) {
		if ( ! RSA_IS_NETWORK ) {
			WP_CLI::error( __( 'Cannot set network mode when plugin not activated on network.', 'restricted-site-access' ) );
		}

		// We don't need to validate the mode, as WP-CLI ensures that's correct.
		$new_mode     = $args[0];
		$current_mode = get_site_option( 'rsa_mode', 'default' );

		// Sets mode and shows message.
		if ( $new_mode === $current_mode ) {
			WP_CLI::warning(
				sprintf(
					/* translators: %s: Network mode. */
					__( 'Mode is already set to %s.', 'restricted-site-access' ),
					$current_mode
				)
			);
		} else {
			update_site_option( 'rsa_mode', sanitize_key( $new_mode ) );
			WP_CLI::success(
				sprintf(
					/* translators: %s: Network mode. */
					__( 'Set network mode to %s.', 'restricted-site-access' ),
					$new_mode
				)
			);
		}
	}

	/**
	 * Lists current IP whitelist.
	 *
	 * ## OPTIONS
	 *
	 * [--exclude-config]
	 * : Don't include IPs from the configuration file.
	 *
	 * [--include-labels]
	 * : Include labels.
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
	 * [--network]
	 * : Multisite only. Sets configuration for the network as a whole.
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
		$this->setup( $args, $assoc_args );

		$show_labels = WP_CLI\Utils\get_flag_value( $assoc_args, 'include-labels', false );
		$no_config   = WP_CLI\Utils\get_flag_value( $assoc_args, 'exclude-config', false );
		$ips         = $this->get_current_ips( ! $no_config, $show_labels );
		$items       = array();
		$fields      = $show_labels ? array( 'ip', 'label' ) : array( 'ip' );

		if ( 0 === count( $ips ) ) {
			WP_CLI::line( __( 'No IP addresses configured.', 'restricted-site-access' ) );
			return;
		}

		if ( $show_labels ) {
			foreach ( $ips as $label => $ip ) {
				$items[] = compact( 'ip', 'label' );
			}
		} else {
			foreach ( $ips as $ip ) {
				$items[] = compact( 'ip' );
			}
		}

		$format = WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );
		WP_CLI\Utils\format_items( $format, $items, $fields );
	}

	/**
	 * Adds one or more IP addresses to the whitelist.
	 *
	 * ## OPTIONS
	 *
	 * <ip>...
	 * : List of IP addresses to add to the whitelist.
	 *
	 * [--network]
	 * : Multisite only. Sets configuration for the network as a whole.
	 *
	 * ## EXAMPLES
	 *
	 *    # Adds 192.0.0.1 to IP whitelist.
	 *    $ wp rsa ip-add 192.0.0.1
	 *    Success: Added 192.0.0.1 to site whitelist.
	 *
	 *    # Adds 8.8.8.8 1.1.1.1 with labels Google and Cloudflare.
	 *    $ wp rsa ip-add 8.8.8.8=Google 1.1.1.1=Cloudflare
	 *    Success: Added 8.8.8.8, 1.1.1.1 to site whitelist.
	 *
	 * @subcommand ip-add
	 *
	 * @param array $args       List of IPs to whitelist.
	 * @param array $assoc_args Optional flags.
	 */
	public function ip_add( $args, $assoc_args ) {
		$this->setup( $args, $assoc_args );

		/**
		 * The input arguments can be of the form:
		 * wp rsa ip-add 8.8.8.8=Google 9.9.9.9 1.1.1.1=Cloudflare.
		 *
		 * Some input IP addresses may be provided with a label while
		 * other might not.
		 *
		 * We will normalise the input as:
		 * array(
		 *     array(
		 *         'key' => '8.8.8.8',
		 *         'label' => 'Google'
		 *     ),
		 *     array(
		 *         'key' => '9.9.9.9',
		 *         'label' => ''
		 *     ),
		 *      array(
		 *         'key' => '1.1.1.1',
		 *         'label' => 'Cloudflare'
		 *     ),
		 * )
		 */
		$ips_and_labels_array = array();
		foreach ( $args as $index => $item ) {
			$fragments = explode( '=', $item );
			/**
			 * If the IP doesn't have a corressponding label,
			 * then set label to '[null]:x', where 'x' is an
			 * integer.
			 */
			if ( ! isset( $fragments[1] ) ) {
				$fragments[1] = '';
			}

			$structure_ip_label_array = array(
				'ip'    => $fragments[0],
				'label' => $fragments[1],
			);

			$ips_and_labels_array[] = $structure_ip_label_array;
		}

		/**
		 * Get all whitelisted IPs saved in DB.
		 */
		$current_ips = $this->get_current_ips();

		/**
		 * This will only hold those input IP addresses
		 * which are not already whitelisted.
		 */
		$filtered_ips_and_labels = array();

		/**
		 * A simple for loop to filter the input IP addresses.
		 */
		foreach ( $ips_and_labels_array as $ip_label_pair ) {
			if ( ! in_array( $ip_label_pair['ip'], $current_ips, true ) ) {
				$filtered_ips_and_labels[] = array(
					'ip'    => $ip_label_pair['ip'],
					'label' => $ip_label_pair['label'],
				);
			}
		}

		/**
		 * Extract all IP address from the filtered array
		 * as an indexed array.
		 */
		$new_ips = array_map(
			function( $ip_label_pair ) {
				return $ip_label_pair['ip'];
			},
			$filtered_ips_and_labels
		);

		// Validate the IP addresses.
		$valid_ips = array_filter( $new_ips, array( 'Restricted_Site_Access', 'is_ip' ) );
		if ( 0 === count( $valid_ips ) ) {
			WP_CLI::error( __( 'No valid IP addresses provided.', 'restricted-site-access' ) );
		}

		if ( 0 === count( $new_ips ) ) {
			// Only show a warning as this may be an automated process.
			WP_CLI::warning(
				sprintf(
					/* translators: %s: Context: "Site" or "Network". */
					__( 'Provided IPs are already on %s whitelist.', 'restricted-site-access' ),
					$this->update_text( false )
				)
			);
			return;
		}

		$ips_with_label    = array();
		$ips_without_label = array();

		foreach ( $filtered_ips_and_labels as $ip_label_pair ) {
			if ( empty( $ip_label_pair['label'] ) ) {
				$ips_without_label[] = $ip_label_pair['ip'];
			} else {
				$ips_with_label[ $ip_label_pair['label'] ] = $ip_label_pair['ip'];
			}
		}

		// Updates the option.
		Restricted_Site_Access::add_ips( array_merge( $ips_without_label, $ips_with_label ) );

		WP_CLI::success(
			sprintf(
				/* translators: %1$s: IP addresses. %2$s: Context: "Site" or "Network". */
				__( 'Added %1$s to %2$s whitelist.', 'restricted-site-access' ),
				implode( ', ', $new_ips ),
				$this->update_text( false )
			)
		);

		WP_CLI::debug(
			sprintf(
				/* translators: %2$s: IP addresses. %1$s: Context: "Site" or "Network". */
				__( 'Current %2$s whitelisted IPs are: %1$s', 'restricted-site-access' ),
				implode( ', ', Restricted_Site_Access::get_ips() ),
				$this->update_text( false )
			)
		);
	}

	/**
	 * Removes one or more IP addresses from the whitelist.
	 *
	 * ## OPTIONS
	 *
	 * <ip>...
	 * : List of IP addresses to remove from the whitelist.
	 *
	 * [--network]
	 * : Multisite only. Sets configuration for the network as a whole.
	 *
	 * ## EXAMPLES
	 *
	 *    # Removes IP address from whitelist.
	 *    $ wp rsa ip-remove 192.0.0.1
	 *    Success: Removed 192.0.0.1 from whitelist.
	 *
	 * @subcommand ip-remove
	 *
	 * @param array $args       List of IPs to blacklist.
	 * @param array $assoc_args Optional flags.
	 */
	public function ip_remove( $args, $assoc_args ) {
		$this->setup( $args, $assoc_args );

		// Validate the IP addresses.
		$valid_ips = array_filter( $args, array( 'Restricted_Site_Access', 'is_ip' ) );
		if ( 0 === count( $valid_ips ) ) {
			WP_CLI::error( __( 'No valid IP addresses provided.', 'restricted-site-access' ) );
		}

		// Get the IPs to remove.
		$current_ips = $this->get_current_ips( false );
		$removed_ips = array_intersect( $valid_ips, $current_ips );

		if ( 0 === count( $removed_ips ) ) {
			// Only show warning as this may be an automated process.
			WP_CLI::warning(
				sprintf(
					/* translators: %s: Context: "Site" or "Network". */
					__( 'Provided IPs are not on %s whitelist.', 'restricted-site-access' ),
					$this->update_text( false )
				)
			);
			return;
		}

		// Updates the option.
		Restricted_Site_Access::remove_ips( $removed_ips );

		WP_CLI::success(
			sprintf(
				/* translators: %1$s: IP addresses. %2$s: Context: "Site" or "Network". */
				__( 'Removed IPs %1$s from %2$s whitelist.', 'restricted-site-access' ),
				implode( ', ', $removed_ips ),
				$this->update_text( false )
			)
		);

		WP_CLI::debug(
			sprintf(
				/* translators: %2$s: IP addresses. %1$s: Context: "Site" or "Network". */
				__( 'Current %2$s whitelisted IPs are: %1$s', 'restricted-site-access' ),
				implode( ', ', Restricted_Site_Access::get_ips() ),
				$this->update_text( false )
			)
		);
	}

	/**
	 * Used to update an existing IP address or to
	 * update the label of an existing IP address.
	 *
	 * ## OPTIONS
	 *
	 * <ip>
	 * : IP address to update.
	 *
	 * [--new-ip]
	 * : The IP address to replace with.
	 *
	 * [--new-label]
	 * : The new label for the IP address.
	 *
	 * [--network]
	 * : Multisite only. Sets configuration for the network as a whole.
	 *
	 * ## EXAMPLES
	 *
	 *    # Update the label of IP 192.0.0.1 to "New label"
	 *    $ wp rsa ip-update 192.0.0.1 --new-label="New label"
	 *    Success: Fields correctly updated.
	 *
	 *    # Replace the IP IP 192.0.0.1 to 200.1.2.3
	 *    $ wp rsa ip-update 192.0.0.1 --new-ip=200.1.2.3
	 *    Success: Fields correctly updated.
	 *
	 * @subcommand ip-update
	 *
	 * @param array $args       IP to update.
	 * @param array $assoc_args Optional flags.
	 */
	public function ip_update( $args, $assoc_args ) {
		$this->setup( $args, $assoc_args );

		if ( 0 === count( $assoc_args ) ) {
			\WP_CLI::error( __( 'Provide the arguments to update.', 'restricted-site-access' ) );
		}

		$valid_ips = array_filter( $args, array( 'Restricted_Site_Access', 'is_ip' ) );

		if ( 0 === count( $valid_ips ) ) {
			WP_CLI::error( __( 'No valid IP addresses provided.', 'restricted-site-access' ) );
		}

		$new_ip    = \WP_CLI\Utils\get_flag_value( $assoc_args, 'new-ip', false );
		$new_label = \WP_CLI\Utils\get_flag_value( $assoc_args, 'new-label', false );

		$update_status = Restricted_Site_Access::update_ip_or_label( $valid_ips[0], $new_ip, $new_label );

		if ( is_wp_error( $update_status ) ) {
			WP_CLI::error(
				sprintf(
					'%s (%s)',
					$update_status->get_error_message(),
					$update_status->get_error_code()
				)
			);
		}

		WP_CLI::success( __( 'IP updated.', 'restricted-site-access' ) );
	}

	/**
	 * Sets list of IPs to whitelist. Overwrites current settings.
	 *
	 * ## OPTIONS
	 *
	 * <ip>...
	 * : List of IP addresses to whitelist.
	 *
	 * [--network]
	 * : Multisite only. Sets configuration for the network as a whole.
	 *
	 * ## EXAMPLES
	 *
	 *    # Sets IP whitelist to 192.0.0.1.
	 *    $ wp rsa ip-set 192.0.0.1
	 *    Success: Updated site IP whitelist to 192.0.0.1.
	 *
	 * @subcommand ip-set
	 *
	 * @param array $args       List of IPs to set.
	 * @param array $assoc_args Optional flags.
	 */
	public function ip_set( $args, $assoc_args ) {
		$this->setup( $args, $assoc_args );

		// Validate the IP addresses.
		$valid_ips = array_filter( $args, array( 'Restricted_Site_Access', 'is_ip' ) );
		if ( 0 === count( $valid_ips ) ) {
			WP_CLI::error( __( 'No valid IP addresses provided.', 'restricted-site-access' ) );
		}

		// Updates the option.
		Restricted_Site_Access::set_ips( $valid_ips );

		WP_CLI::success(
			sprintf(
				/* translators: %2$s: IPs to whitelist, %1$s: Context: "Site" or "Network". */
				__( 'Set %2$s IP whitelist to %1$s.', 'restricted-site-access' ),
				implode( ', ', Restricted_Site_Access::get_ips() ),
				$this->update_text( false )
			)
		);
	}

	/**
	 * Sets up the instance correctly.
	 *
	 * @param array $args       Array with positional arguments.
	 * @param array $assoc_args Array with associative arguments.
	 * @return void
	 */
	private function setup( $args = array(), $assoc_args = array() ) {
		$this->args       = $args;
		$this->assoc_args = $assoc_args;

		$is_network = WP_CLI\Utils\get_flag_value( $assoc_args, 'network', false );
		if ( $is_network && ! RSA_IS_NETWORK ) {
			WP_CLI::error( __( 'Cannot get network settings when plugin not activated on network.', 'restricted-site-access' ) );
		}

		$this->is_network = (bool) $is_network;
	}

	/**
	 * Gets plugin options, either from the network or specified site.
	 *
	 * @return array Array of options from database.
	 */
	private function get_options() {
		return Restricted_Site_Access::get_options( $this->is_network );
	}

	/**
	 * Gets all current IPs, optionally including config IPs.
	 *
	 * @param bool $include_config Whether to include the config file IPs. Default true.
	 * @param bool $include_labels Whether to include the comments. Default false.
	 * @return array
	 */
	private function get_current_ips( $include_config = true, $include_labels = false ) {
		return Restricted_Site_Access::get_ips( $include_config, $include_labels );
	}

	/**
	 * Updates options, potentially on the site.
	 *
	 * @param array $new_options Array of unsanitized options to save.
	 * @return array             The newly set options.
	 */
	private function update_options( $new_options ) {
		$options           = wp_parse_args( $new_options, $this->get_options() );
		$sanitized_options = Restricted_Site_Access::sanitize_options( $options );
		if ( $this->is_network ) {
			update_site_option( 'rsa_options', $sanitized_options );
		} else {
			update_option( 'rsa_options', $sanitized_options );
		}

		return $this->get_options();
	}

	/**
	 * Text used to indicate whether the user is updating a site or the network.
	 *
	 * @param bool $capitalize Whether to capitalize the text or not.
	 * @return string
	 */
	private function update_text( $capitalize = true ) {
		$text = _x( 'Site', 'update type', 'restricted-site-access' );
		if ( $this->is_network ) {
			$text = _x( 'Network', 'update type', 'restricted-site-access' );
		}

		if ( $capitalize ) {
			return $text;
		}
		return strtolower( $text );
	}
}

WP_CLI::add_command( 'rsa', 'Restricted_Site_Access_CLI' );
