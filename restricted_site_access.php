<?php // phpcs:disable WordPress.Files.FileName
/**
 * Plugin Name: Restricted Site Access
 * Plugin URI: http://10up.com/plugins/restricted-site-access-wordpress/
 * Description: <strong>Limit access your site</strong> to visitors who are logged in or accessing the site from a set of specific IP addresses. Send restricted visitors to the log in page, redirect them, or display a message or page. <strong>Powerful control over redirection</strong>, including <strong>SEO friendly redirect headers</strong>. Great solution for Extranets, publicly hosted Intranets, or parallel development sites.
 * Version: 7.1.0
 * Author: Jake Goldman, 10up, Oomph
 * Author URI: http://10up.com
 * License: GPLv2 or later
 * Text Domain: restricted-site-access
 */

define( 'RSA_VERSION', '7.1.0' );

/**
 * Class responsible for all plugin funcitonality.
 */
class Restricted_Site_Access {

	/**
	 * Plugin basename.
	 *
	 * @var array $basename The plugin base name.
	 */
	private static $basename;

	/**
	 * Plugin options.
	 *
	 * @var array $rsa_options The plugin options.
	 */
	private static $rsa_options;

	/**
	 * Settings page slug.
	 *
	 * @var array $settings_page The settings page slug.
	 */
	private static $settings_page = 'reading';

	/**
	 * Settings fields.
	 *
	 * @var array $fields The plugin settings fields.
	 */
	private static $fields;

	/**
	 * Handles initializing this class and returning the singleton instance after it's been cached.
	 *
	 * @return null|Restricted_Site_Access
	 * @codeCoverageIgnore
	 */
	public static function get_instance() {
		// Store the instance locally to avoid private static replication.
		static $instance = null;

		if ( null === $instance ) {
			$instance = new self();
			self::add_actions();
			self::populate_fields_array();
		}

		return $instance;
	}

	/**
	 * An empty constructor
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		/* Purposely do nothing here */ }

	/**
	 * Handles registering hooks that initialize this plugin.
	 */
	public static function add_actions() {
		self::$basename = plugin_basename( __FILE__ );

		add_action( 'parse_request', array( __CLASS__, 'restrict_access' ), 1 );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ), 1 );
		add_action( 'wp_ajax_rsa_ip_check', array( __CLASS__, 'ajax_rsa_ip_check' ) );

		add_action( 'activate_' . self::$basename, array( __CLASS__, 'activation' ), 10, 1 );
		add_action( 'deactivate_' . self::$basename, array( __CLASS__, 'deactivation' ), 10, 1 );
		add_action( 'wpmu_new_blog', array( __CLASS__, 'set_defaults' ), 10, 6 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_script' ) );
		add_action( 'wp_ajax_rsa_notice_dismiss', array( __CLASS__, 'ajax_notice_dismiss' ) );

		add_filter( 'pre_option_blog_public', array( __CLASS__, 'pre_option_blog_public' ), 10, 1 );
		add_filter( 'pre_site_option_blog_public', array( __CLASS__, 'pre_option_blog_public' ), 10, 1 );
	}

	/**
	 * Ajax handler for dismissing the network controlled settings notice.
	 */
	public static function ajax_notice_dismiss() {

		// @codeCoverageIgnoreStart
		if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {
			if ( ! check_ajax_referer( 'rsa_admin_nonce', 'nonce', false ) ) {
				wp_send_json_error();
				exit;
			}

			if ( RSA_IS_NETWORK ) {
				if ( ! is_super_admin() ) {
					wp_send_json_error();
					exit;
				}
			} else {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error();
					exit;
				}
			}
		}
		// @codeCoverageIgnoreEnd
		if ( RSA_IS_NETWORK ) {
			update_site_option( 'rsa_hide_page_cache_notice', true );
		} else {
			update_option( 'rsa_hide_page_cache_notice', true );
		}

		// @codeCoverageIgnoreStart
		if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {
			wp_send_json_success();
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Set RSA defaults for new site.
	 *
	 * @param int    $blog_id Blog ID.
	 * @param int    $user_id User ID.
	 * @param string $domain  Site domain.
	 * @param string $path    Site path.
	 * @param int    $site_id Site ID. Only relevant on multi-network installs.
	 * @param array  $meta    Meta data. Used to set initial site options.
	 */
	public static function set_defaults( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		if ( 'enforce' === self::get_network_mode() ) {
			return;
		}

		$network_options = self::get_options( true );
		$blog_public     = get_site_option( 'blog_public', 2 );

		switch_to_blog( $blog_id );
		update_option( 'rsa_options', self::sanitize_options( $network_options ) );
		update_option( 'blog_public', (int) $blog_public );
		restore_current_blog();
	}

	/**
	 * Populate Restricted_Site_Access::$fields with internationalization-ready field information.
	 *
	 * @codeCoverageIgnore
	 */
	protected static function populate_fields_array() {
		self::$fields = array(
			'approach'      => array(
				'default' => 1,
				'label'   => esc_html__( 'Handle restricted visitors', 'restricted-site-access' ),
				'field'   => 'settings_field_handling',
			),
			'message'       => array(
				'default' => esc_html_x( 'Access to this site is restricted.', 'default restriction message', 'restricted-site-access' ),
				'label'   => esc_html__( 'Restriction message', 'restricted-site-access' ),
				'field'   => 'settings_field_message',
			),
			'redirect_url'  => array(
				'default' => '',
				'label'   => esc_html__( 'Redirect web address', 'restricted-site-access' ),
				'field'   => 'settings_field_redirect',
			),
			'redirect_path' => array(
				'default' => 0,
				'label'   => esc_html__( 'Redirect to same path', 'restricted-site-access' ),
				'field'   => 'settings_field_redirect_path',
			),
			'head_code'     => array(
				'default' => 302,
				'label'   => esc_html__( 'Redirection status code', 'restricted-site-access' ),
				'field'   => 'settings_field_redirect_code',
			),
			'page'          => array(
				'default' => 0,
				'label'   => esc_html__( 'Restricted notice page', 'restricted-site-access' ),
				'field'   => 'settings_field_rsa_page',
			),
			'allowed'       => array(
				'default' => array(),
				'label'   => esc_html__( 'Unrestricted IP addresses', 'restricted-site-access' ),
				'field'   => 'settings_field_allowed',
			),
		);
	}

	/**
	 * Get current plugin network mode
	 */
	private static function get_network_mode() {
		if ( RSA_IS_NETWORK ) {
			return get_site_option( 'rsa_mode', 'default' );
		}

		return 'default';
	}

	/**
	 * Populate the option with defaults.
	 *
	 * @param boolean $network Whther this is a network install. Default false.
	 */
	public static function get_options( $network = false ) {
		$options = array();

		if ( $network ) {
			$options = get_site_option( 'rsa_options' );
		} else {
			$options = get_option( 'rsa_options' );
		}

		// Fill in defaults where values aren't set.
		foreach ( self::$fields as $field_name => $field_details ) {
			if ( ! isset( $options[ $field_name ] ) ) {
				$options[ $field_name ] = $field_details['default'];
			}
		}

		return $options;
	}

	/**
	 * Determine if site should be restricted
	 */
	protected static function is_restricted() {
		$mode = self::get_network_mode();

		if ( RSA_IS_NETWORK ) {
			if ( 'enforce' === $mode ) {
				self::$rsa_options = self::get_options( true );
			}
		}

		$blog_public = get_option( 'blog_public', 2 );

		$user_check = self::user_can_access();

		$checks = is_admin() || $user_check || 2 !== (int) $blog_public || ( defined( 'WP_INSTALLING' ) && isset( $_GET['key'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		return ! $checks;
	}

	/**
	 * Check if current user has access.
	 *
	 * Can be short-circuited using the `restricted_site_access_user_can_access` filter
	 * to return a value other than null (boolean recommended).
	 *
	 * @return bool Whether the user has access
	 */
	protected static function user_can_access() {
		/**
		 * Filters whether the user can access the site before any other checks.
		 *
		 * Returning a non-null value will short-circuit the function
		 * and return that value instead.
		 *
		 * @param null|bool $access Whether the user can access the site.
		 */
		$access = apply_filters( 'restricted_site_access_user_can_access', null );

		if ( null !== $access ) {
			return $access;
		}

		if ( is_multisite() ) {
			$user_id = get_current_user_id();

			if ( is_super_admin( $user_id ) ) {
				return true;
			}

			if ( is_user_member_of_blog( $user_id ) && current_user_can( 'read' ) ) {
				return true;
			}
		} elseif ( is_user_logged_in() ) {
			return true;
		}

		return false;
	}

	/**
	 * Redirects restricted requests.
	 *
	 * @param array $wp WordPress request.
	 * @codeCoverageIgnore
	 */
	public static function restrict_access( $wp ) {

		$results = self::restrict_access_check( $wp );

		if ( is_array( $results ) && ! empty( $results ) ) {

			// Don't redirect during unit tests.
			if ( ! empty( $results['url'] ) && ! defined( 'WP_TESTS_DOMAIN' ) ) {
				wp_redirect( $results['url'], $results['code'] ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				die();
			}

			// Don't die during unit tests.
			if ( ! empty( $results['die_message'] ) && ! defined( 'WP_TESTS_DOMAIN' ) ) {
				wp_die( wp_kses_post( $results['die_message'] ), esc_html( $results['die_title'] ), array( 'response' => esc_html( $results['die_code'] ) ) );
			}
		}
	}

	/**
	 * Determine whether page should be restricted at point of request.
	 *
	 * @param array $wp WordPress The main WP request.
	 * @return array              List of URL and code, otherwise empty.
	 */
	public static function restrict_access_check( $wp ) {
		self::$rsa_options = self::get_options();
		$is_restricted     = self::is_restricted();

		// Check to see if it's _not_ restricted.
		if ( apply_filters( 'restricted_site_access_is_restricted', $is_restricted, $wp ) === false ) {
			return;
		}

		$allowed_ips = self::get_config_ips();
		if (
			! empty( self::$rsa_options['allowed'] ) &&
			is_array( self::$rsa_options['allowed'] )
		) {
			$allowed_ips = array_merge( $allowed_ips, self::$rsa_options['allowed'] );
		}

		// check for the allow list, if its empty block everything.
		if ( count( $allowed_ips ) > 0 ) {
			$remote_ip = self::get_client_ip_address();

			// iterate through the allow list.
			foreach ( $allowed_ips as $line ) {
				if ( self::ip_in_range( $remote_ip, $line ) ) {

					/**
					 * Fires when an ip address match occurs.
					 *
					 * Enables adding session_start() to the IP check, ensuring Varnish type cache will
					 * not cache the request. Passes the matched line; previous to 6.1.0 this action passed
					 * the matched ip and mask.
					 *
					 * @since 6.0.2
					 *
					 * @param string $remote_ip The remote IP address being checked.
					 * @param string $line      The matched masked IP address.
					 */
					do_action( 'restrict_site_access_ip_match', $remote_ip, $line );
					return;
				}
			}
		}

		$rsa_restrict_approach = apply_filters( 'restricted_site_access_approach', self::$rsa_options['approach'] );
		do_action( 'restrict_site_access_handling', $rsa_restrict_approach, $wp ); // allow users to hook handling.

		switch ( $rsa_restrict_approach ) {
			case 4: // Show them a page.
				if ( ! empty( self::$rsa_options['page'] ) ) {
					$page = get_post( self::$rsa_options['page'] );

					// If the selected page isn't found or isn't published, fall back to default values.
					if ( ! $page || 'publish' !== $page->post_status ) {
						self::$rsa_options['head_code']    = 302;
						$current_path                      = empty( $_SERVER['REQUEST_URI'] ) ? home_url() : sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
						self::$rsa_options['redirect_url'] = wp_login_url( $current_path );
						break;
					}

					// Are we already on the selected page?
					$on_selected_page = false;
					if ( isset( $wp->query_vars['page_id'] ) && absint( $wp->query_vars['page_id'] ) === $page->ID ) {
						$on_selected_page = true;
					}

					if ( ! $on_selected_page && ( isset( $wp->query_vars['pagename'] ) && $wp->query_vars['pagename'] === $page->post_name ) ) {
						$on_selected_page = true;
					}

					// There's a separate unpleasant conditional to match the page on front because of the way query vars are (not) filled at this point.
					if ( $on_selected_page
					||
						(
							empty( $wp->query_vars ) &&
							'page' === get_option( 'show_on_front' ) &&
							(int) get_option( 'page_on_front' ) === (int) self::$rsa_options['page']
						)
					) {
						return;
					}

					self::$rsa_options['redirect_url'] = get_permalink( $page->ID );
					break;
				}
				// Fall thru to case 3 if case 2 not handled.
			case 3:
				$message  = esc_html( self::$rsa_options['message'] );
				$message .= "\n<!-- protected by Restricted Site Access http://10up.com/plugins/restricted-site-access-wordpress/ -->";
				$message  = apply_filters( 'restricted_site_access_message', $message, $wp );

				return array(
					'die_message' => $message,
					'die_title'   => esc_html( get_bloginfo( 'name' ) ) . ' - Site Access Restricted',
					'die_code'    => 403,
				);

			case 2:
				if ( ! empty( self::$rsa_options['redirect_url'] ) ) {
					if ( ! empty( self::$rsa_options['redirect_path'] ) ) {
						self::$rsa_options['redirect_url'] = untrailingslashit( self::$rsa_options['redirect_url'] ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
					}
					break;
				}
				// No break, fall thru to default.
			default:
				self::$rsa_options['head_code']    = 302;
				$current_path                      = empty( $_SERVER['REQUEST_URI'] ) ? home_url() : sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
				self::$rsa_options['redirect_url'] = wp_login_url( $current_path );
		}

		$redirect_url  = apply_filters( 'restricted_site_access_redirect_url', self::$rsa_options['redirect_url'], $wp );
		$redirect_code = apply_filters( 'restricted_site_access_head', self::$rsa_options['head_code'], $wp );

		return array(
			'url'  => $redirect_url,
			'code' => $redirect_code,
		);
	}

	/**
	 * Admin only hooks
	 */
	public static function admin_init() {
		// customize privacy message.
		add_filter( 'privacy_on_link_text', array( __CLASS__, 'privacy_on_link_text' ) );
		add_filter( 'privacy_on_link_title', array( __CLASS__, 'privacy_on_link_title' ) );

		// customize privacy page.
		add_action( 'load-options-' . self::$settings_page . '.php', array( __CLASS__, 'load_options_page' ) );

		// add new choice for blog privacy.
		add_action( 'blog_privacy_selector', array( __CLASS__, 'blog_privacy_selector' ) );

		// settings for restricted site access.
		register_setting( self::$settings_page, 'rsa_options', array( __CLASS__, 'sanitize_options' ) ); // array of fundamental options including ID and caching info.
		add_settings_section( 'restricted-site-access', '', '__return_empty_string', self::$settings_page );

		// Limit when additional settings fields show up.
		if (
			is_network_admin() || // Show on the network admin.
			( RSA_IS_NETWORK && 'enforce' !== self::get_network_mode() ) || // Show on single (network) site when not enforced at the network level.
			! RSA_IS_NETWORK // Show on single non-network sites.
		) {
			foreach ( self::$fields as $field_name => $field_data ) {
				add_settings_field(
					$field_name,
					$field_data['label'],
					array( __CLASS__, $field_data['field'] ),
					self::$settings_page,
					'restricted-site-access',
					array( 'class' => 'rsa-setting rsa-setting_' . esc_attr( $field_data['field'] ) )
				);
			}
		}

		add_filter( 'plugin_action_links_' . self::$basename, array( __CLASS__, 'plugin_action_links' ) );

		// This is for Network Site Settings.
		if ( RSA_IS_NETWORK && is_network_admin() ) {
			add_action( 'load-settings.php', array( __CLASS__, 'load_network_settings_page' ) );
			add_action( 'network_admin_notices', array( __CLASS__, 'page_cache_notice' ) );
		}

		add_action( 'admin_notices', array( __CLASS__, 'page_cache_notice' ) );
	}

	/**
	 * Show RSA Settings in Network Settings
	 */
	public static function show_network_settings() {
		$mode = self::get_network_mode();
		?>
			<h2><?php esc_html_e( 'Restricted Site Access Settings', 'restricted-site-access' ); ?></h2>
			<table id="restricted-site-access-mode" class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Mode', 'restricted-site-access' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php esc_html_e( 'Mode', 'restricted-site-access' ); ?></legend>
							<label><input name="rsa_mode" type="radio" id="rsa-mode-default" value="default"<?php checked( $mode, 'default' ); ?> /> <?php esc_html_e( 'Default to the settings below when creating a new site', 'restricted-site-access' ); ?></label><br />
							<label><input name="rsa_mode" type="radio" id="rsa-mode-enforce" value="enforce"<?php checked( $mode, 'enforce' ); ?> /> <?php esc_html_e( 'Enforce the settings below across all sites', 'restricted-site-access' ); ?></label><br />
						</fieldset>
					</td>
				</tr>
				<tr class="option-site-visibility">
					<th scope="row"><?php esc_html_e( 'Site Visibility', 'restricted-site-access' ); ?></th>
					<?php
					$blog_public = get_site_option( 'blog_public' );

					if ( false === $blog_public ) {
						$blog_public = 1;
					}
					?>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Site Visibility', 'restricted-site-access' ); ?></span></legend>
							<input id="blog-public" type="radio" name="blog_public" value="1" <?php checked( $blog_public, '1' ); ?>>
							<label for="blog-public"><?php esc_html_e( 'Allow search engines to index this site', 'restricted-site-access' ); ?></label><br>
							<input id="blog-norobots" type="radio" name="blog_public" value="0" <?php checked( $blog_public, '0' ); ?>>
							<label for="blog-norobots"><?php esc_html_e( 'Discourage search engines from indexing this site', 'restricted-site-access' ); ?></label>
							<p class="description"><?php esc_html_e( 'Note: Neither of these options blocks access to your site — it is up to search engines to honor your request.', 'restricted-site-access' ); ?></p>
							<p>
								<input id="blog-restricted" type="radio" name="blog_public" value="2" <?php checked( $blog_public, '2' ); ?>>
								<label for="blog-restricted"><?php esc_html_e( 'Restrict site access to visitors who are logged in or allowed by IP address', 'restricted-site-access' ); ?></label>
							</p>
						</fieldset>
					</td>
				</tr>
			</table>
		<?php
		if ( ( defined( 'RSA_FORCE_RESTRICTION' ) && RSA_FORCE_RESTRICTION === true )
			|| ( defined( 'RSA_FORBID_RESTRICTION' ) && RSA_FORBID_RESTRICTION === true ) ) {
			$message = __( 'Site visibility settings are currently enforced by code configuration.', 'restricted-site-access' );
			?>
			<div class="notice notice-warning inline">
				<p><strong><?php echo esc_html( $message ); ?></strong></p>
			</div>
		<?php } ?>
			<table id="restricted-site-access" class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Handle restricted visitors', 'restricted-site-access' ); ?></th>
					<td>
						<?php
							self::settings_field_handling();
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Redirect web address', 'restricted-site-access' ); ?></th>
					<td>
						<?php
							self::settings_field_redirect();
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Redirect to same path', 'restricted-site-access' ); ?></th>
					<td>
						<?php
							self::settings_field_redirect_path();
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Redirection status code', 'restricted-site-access' ); ?></th>
					<td>
						<?php
						self::settings_field_redirect_code();
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Restriction message', 'restricted-site-access' ); ?></th>
					<td>
						<?php
						self::settings_field_message();
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Unrestricted IP addresses', 'restricted-site-access' ); ?></th>
					<td>
						<?php
						self::settings_field_allowed();
						?>
					</td>
				</tr>
			</table>

		<?php
	}

	/**
	 * Handle Save Options for RSA Settings in Network Settings.
	 */
	public static function save_network_settings() {
		$options = array(
			'rsa_mode',
			'blog_public',
			'rsa_options',
		);

		foreach ( $options as $option_name ) {
			if ( ! isset( $_POST[ $option_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				continue;
			}

			switch ( $option_name ) {
				case 'rsa_options':
					$value = self::sanitize_options( wp_unslash( $_POST[ $option_name ] ) );  // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
					break;
				case 'blog_public':
					$value = absint( $_POST[ $option_name ] );
					break;
				default:
					$value = sanitize_key( $_POST[ $option_name ] );
					break;
			}

			update_site_option( $option_name, $value );
		}
	}

	/**
	 * Overrides text in the dashboard Right Now widget.
	 *
	 * @param string $text The text for the dashboard 'right now' widget.
	 *
	 * @return string New text to show in widget
	 */
	public static function privacy_on_link_text( $text ) {
		if ( 2 === (int) get_option( 'blog_public' ) ) {
			$text = esc_html__( 'Public access to this site has been restricted.', 'restricted-site-access' );
		}
		return $text;
	}

	/**
	 * Title attribute for link about site status on Right Now widget.
	 *
	 * @param string $text The title attribute.
	 *
	 * @return string New title attribute
	 */
	public static function privacy_on_link_title( $text ) {
		if ( 2 === (int) get_option( 'blog_public' ) ) {
			$text = esc_html__( 'Restricted Site Access plug-in is blocking public access to this site.', 'restricted-site-access' );
		}
		return $text;
	}

	/**
	 * Enqueue Settings page scripts.
	 */
	public static function enqueue_settings_script() {
		$min    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$folder = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'src/' : '';

		wp_enqueue_script(
			'rsa-settings',
			plugin_dir_url( __FILE__ ) . 'assets/js/' . $folder . 'settings' . $min . '.js',
			array( 'jquery-effects-shake' ),
			RSA_VERSION,
			true
		);
	}

	/**
	 * Enqueue wp-admin scripts.
	 */
	public static function enqueue_admin_script() {
		$js_path = plugin_dir_url( __FILE__ ) . 'assets/js/admin.min.js';

		$min    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$folder = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'src/' : '';

		wp_enqueue_script(
			'rsa-admin',
			plugin_dir_url( __FILE__ ) . 'assets/js/' . $folder . 'admin' . $min . '.js',
			array( 'jquery' ),
			RSA_VERSION,
			true
		);

		wp_localize_script(
			'rsa-admin',
			'rsaAdmin',
			array(
				'nonce' => wp_create_nonce( 'rsa_admin_nonce' ),
			)
		);
	}

	/**
	 * Loads needed scripts and assets on the Reading page
	 */
	public static function load_options_page() {
		self::enqueue_settings_script();

		add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
		add_action( 'admin_head', array( __CLASS__, 'admin_head' ) );
		add_action( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );

		add_filter( 'wp_dropdown_pages', array( __CLASS__, 'filter_page_dropdown' ), 10, 2 );

		self::$rsa_options = self::get_options();
	}

	/**
	 * Load needed scripts and assets on Network Settings page
	 */
	public static function load_network_settings_page() {
		self::enqueue_settings_script();

		self::$rsa_options = self::get_options( true );

		add_action( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );
		add_action( 'admin_head', array( __CLASS__, 'admin_head' ) );
		add_action( 'wpmu_options', array( __CLASS__, 'show_network_settings' ) );
		add_action( 'update_wpmu_options', array( __CLASS__, 'save_network_settings' ) );
	}

	/**
	 * Customize admin notices to ensure user completes restriction setup properly
	 */
	public static function admin_notice() {
		if ( empty( self::$rsa_options['approach'] ) ) {
			return;
		}

		if ( 4 === (int) self::$rsa_options['approach'] && empty( self::$rsa_options['page'] ) ) {
			$message = esc_html__( 'Please select the page you want to show restricted visitors. If no page is selected, WordPress will simply show a general restriction message.', 'restricted-site-access' );
		} elseif ( 2 === (int) self::$rsa_options['approach'] && empty( self::$rsa_options['redirect_url'] ) ) {
			$message = esc_html__( 'Please enter the web address you would like to redirect restricted visitors to. If no address is entered, visitors will be redirected to the login screen.', 'restricted-site-access' );
		}

		if ( isset( $message ) ) {
			echo '<div class="notice notice-error"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
		}
	}

	/**
	 * Check if the page caching is on, and notify the admin
	 */
	public static function page_cache_notice() {
		// If WP_CACHE is on we show notification.
		$show_notification = apply_filters( 'restricted_site_access_show_page_cache_notice', defined( 'WP_CACHE' ) && true === WP_CACHE );

		if ( $show_notification ) {

			if ( RSA_IS_NETWORK ) {
				if ( get_site_option( 'rsa_hide_page_cache_notice' ) ) {
					return;
				}
			} else {
				if ( get_option( 'rsa_hide_page_cache_notice' ) ) {
					return;
				}
			}

			$mode = self::get_network_mode();

			$blog_public = get_option( 'blog_public', 2 );

			if ( RSA_IS_NETWORK && 'enforce' === $mode ) {
				$blog_public = get_site_option( 'blog_public', 2 );
			}

			if ( 2 !== (int) $blog_public ) {
				return;
			}
			?>
			<div data-rsa-notice="page-cache" class="notice notice-error is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Page caching appears to be enabled. Restricted Site Access may not work as expected. <a href="https://wordpress.org/plugins/restricted-site-access/#faq">Learn more</a>.', 'restricted-site-access' ); ?></strong>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Add RSA help tab and a tiny amount of CSS to Reading options.
	 */
	public static function admin_head() {
		$screen  = get_current_screen();
		$content = array();

		// Populate the tab contents.
		$content[] = sprintf(
			'<p><strong>%1$s</strong> - %2$s</p>',
			_x( 'Handle restricted visitors', 'help topic', 'restricted-site-access' ),
			__( 'Choose the method for handling visitors to your site that are restricted.', 'restricted-site-access' )
		);

		$content[] = sprintf(
			'<p><strong>%1$s</strong> - %2$s %3$s</p><p>%4$s</p>',
			_x( 'Allowed IP addresses', 'help topic', 'restricted-site-access' ),
			__( 'enter a single IP address (for example, 192.168.1.105) or an IP range using a network prefix (for example, 10.0.0.1/24). Enter your addresses carefully!', 'restricted-site-access' ),
			sprintf(
				/* translators: %s: link to http://www.csgnetwork.com/ipinfocalc.html */
				'<a href="http://www.csgnetwork.com/ipinfocalc.html">%s</a>',
				__( 'Here is a handy calculator to check your prefix.', 'restricted-site-access' )
			),
			__( 'The redirection fields are only used when "Handle restricted visitors" is set to "Redirect them to a specified web address".', 'restricted-site-access' )
		);

		$content[] = sprintf(
			'<p><strong>%1$s</strong> - %2$s</p>',
			_x( 'Redirect web address', 'help topic', 'restricted-site-access' ),
			__( 'The web address of the site you want the visitor redirected to.', 'restricted-site-access' )
		);

		$content[] = sprintf(
			'<p><strong>%1$s</strong> - %2$s</p>',
			_x( 'Redirect to the same path', 'help topic', 'restricted-site-access' ),
			__( 'redirect the visitor to the same path (URI) entered at this site. Typically used when there are two, very similar sites at different public web addresses; for instance, a parallel development server accessible on the Internet but not intended for the public.', 'restricted-site-access' )
		);

		$content[] = sprintf(
			'<p><strong>%1$s</strong> - %2$s</p>',
			_x( 'Redirection status code', 'help topic', 'restricted-site-access' ),
			__( 'Redirect status codes can provide certain visitors, particularly search engines, more information about the nature of the redirect. A 301 redirect tells search engines that a page has moved permanently to the new location. 307 indicates a temporary redirect. 302 is an undefined redirect.', 'restricted-site-access' )
		);

		$screen->add_help_tab(
			array(
				'id'      => 'restricted-site-access',
				'title'   => esc_html_x( 'Restricted Site Acccess', 'help screen title', 'restricted-site-access' ),
				'content' => implode( PHP_EOL, $content ),
			)
		);
		?>
<style>
.rsa-enforced .option-site-visibility {
	opacity: 0.5;
	pointer-events: none;
}
</style>
		<?php
	}

	/**
	 * Adds admin body classes to the Reading options screen.
	 *
	 * Adds `.rsa-network-enforced` if settings are network enforced.
	 *
	 * @param  string $classes Space-separated list of classes to apply to the body element.
	 * @return string
	 */
	public static function admin_body_class( $classes ) {
		if ( self::is_enforced() ) {
			$classes .= ' rsa-enforced';
		}

		return $classes;
	}

	/**
	 * Determines if site restriction is enforced either on a code or network level.
	 *
	 * Important: this is only meant for admin UI purposes.
	 *
	 * @return boolean
	 */
	public static function is_enforced() {
		if (
			( ! is_network_admin() && ( RSA_IS_NETWORK && 'enforce' === self::get_network_mode() ) ) ||
			( defined( 'RSA_FORCE_RESTRICTION' ) && RSA_FORCE_RESTRICTION === true ) ||
			( defined( 'RSA_FORBID_RESTRICTION' ) && RSA_FORBID_RESTRICTION === true )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Add a new choice to the privacy selector.
	 */
	public static function blog_privacy_selector() {
		global $wp;
		$is_restricted = ( 2 === (int) get_option( 'blog_public' ) );
		$is_restricted = apply_filters( 'restricted_site_access_is_restricted', $is_restricted, $wp );
		?>
		<p>
			<input id="blog-restricted" type="radio" name="blog_public" value="2" <?php checked( $is_restricted ); ?> />
			<label for="blog-restricted"><?php esc_html_e( 'Restrict site access to visitors who are logged in or allowed by IP address', 'restricted-site-access' ); ?></label>
		</p>
		<?php
		if ( self::is_enforced() ) {
			// The network enforcement message takes precedence because it's more restrictive and technically still correct with the constants.
			if ( RSA_IS_NETWORK && 'enforce' === self::get_network_mode() ) {
				$message = __( 'Site visibility settings are currently enforced across all sites on the network.', 'restricted-site-access' );
			} else {
				$message = __( 'Site visibility settings are currently enforced by code configuration.', 'restricted-site-access' );
			}
			// Important note: the weird HTML structure below has to match where `blog_privacy_selector` is fired.
			?>
			</fieldset>
		</td>
	</tr>
	<tr class="rsa-network-enforced-warning">
		<td colspan="2">
			<fieldset>
				<div class="notice notice-warning inline">
					<p><strong><?php echo esc_html( $message ); ?></strong></p>
				</div>
			<?php
		}
	}

	/**
	 * Sanitize RSA options.
	 *
	 * @param array $input The options to sanitize.
	 *
	 * @return array Sanitized input
	 */
	public static function sanitize_options( $input ) {
		$new_input['approach'] = (int) $input['approach'];
		if ( $new_input['approach'] < 1 || $new_input['approach'] > 4 ) {
			$new_input['approach'] = self::$fields['approach']['default'];
		}

		global $allowedtags;
		$new_input['message'] = wp_kses( $input['message'], $allowedtags );

		$new_input['redirect_path'] = empty( $input['redirect_path'] ) ? 0 : 1;
		$new_input['head_code']     = in_array( (int) $input['head_code'], array( 301, 302, 307 ), true ) ? (int) $input['head_code'] : self::$fields['head_code']['default'];
		$new_input['redirect_url']  = empty( $input['redirect_url'] ) ? '' : esc_url_raw( $input['redirect_url'], array( 'http', 'https' ) );
		$new_input['page']          = empty( $input['page'] ) ? 0 : (int) $input['page'];

		$new_input['allowed'] = array();
		if ( ! empty( $input['allowed'] ) && is_array( $input['allowed'] ) ) {
			foreach ( $input['allowed'] as $ip_address ) {
				if ( self::is_ip( $ip_address ) ) {
					$new_input['allowed'][] = $ip_address;
				}
			}
		}
		$new_input['comment'] = array();
		if ( ! empty( $input['comment'] ) && is_array( $input['comment'] ) ) {
			foreach ( $input['comment'] as $comment ) {
				if ( is_scalar( $comment ) ) {
					$new_input['comment'][] = sanitize_text_field( $comment );
				}
			}
		}

		return $new_input;
	}

	/**
	 * Fieldset for choosing restriction handling.
	 */
	public static function settings_field_handling() {
		if ( ! isset( self::$rsa_options['approach'] ) ) {
			// @codeCoverageIgnoreStart
			self::$rsa_options['approach'] = 1;
			// @codeCoverageIgnoreEnd
		}
		?>
		<fieldset id="rsa_handle_fields">
			<input id="rsa-send-to-login" name="rsa_options[approach]" type="radio" value="1" <?php checked( self::$rsa_options['approach'], 1 ); ?> />
			<label for="rsa-send-to-login"><?php esc_html_e( 'Send them to the WordPress login screen', 'restricted-site-access' ); ?></label>
			<br />
			<input id="rsa-redirect-visitor" name="rsa_options[approach]" type="radio" value="2" <?php checked( self::$rsa_options['approach'], 2 ); ?> />
			<label for="rsa-redirect-visitor"><?php esc_html_e( 'Redirect them to a specified web address', 'restricted-site-access' ); ?></label>
			<br />
			<input id="rsa-display-message" name="rsa_options[approach]" type="radio" value="3" <?php checked( self::$rsa_options['approach'], 3 ); ?> />
			<label for="rsa-display-message"><?php esc_html_e( 'Show them a simple message', 'restricted-site-access' ); ?></label>

			<?php if ( ! is_network_admin() ) : ?>
				<br />
				<input id="rsa-unblocked-page" name="rsa_options[approach]" type="radio" value="4" <?php checked( self::$rsa_options['approach'], 4 ); ?> />
				<label for="rsa-unblocked-page"><?php esc_html_e( 'Show them a page', 'restricted-site-access' ); ?></label>
			<?php endif; ?>
		</fieldset>
		<?php
	}

	/**
	 * Fieldset for managing allowed IP addresses.
	 */
	public static function settings_field_allowed() {
		?>
		<div class="hide-if-no-js">
			<div id="ip_list">
				<div id="ip_list_empty" style="display: none;"><input type="text" name="rsa_options[allowed][]" class="ip code" value="" readonly="true" size="20" /> <input type="text" name="rsa_options[comment][]" value="" class="comment" size="20" /> <a href="#remove" class="remove_btn"><?php echo esc_html( _x( 'Remove', 'remove IP address action', 'restricted-site-access' ) ); ?></a></div>
			<?php
			$ips      = (array) self::$rsa_options['allowed'];
			$comments = isset( self::$rsa_options['comment'] ) ? (array) self::$rsa_options['comment'] : array();
			foreach ( $ips as $key => $ip ) {
				if ( ! empty( $ip ) ) {
					echo '<div><input type="text" name="rsa_options[allowed][]" value="' . esc_attr( $ip ) . '" class="ip code" readonly="true" size="20" /> <input type="text" name="rsa_options[comment][]" value="' . ( isset( $comments[ $key + 1 ] ) ? esc_attr( wp_unslash( $comments[ $key + 1 ] ) ) : '' ) . '" size="20" /> <a href="#remove" class="remove_btn">' . esc_html_x( 'Remove', 'remove IP address action', 'restricted-site-access' ) . '</a></div>';
				}
			}
			?>
			</div>
			<div>
				<input type="text" name="newip" id="newip" class="ip code" placeholder="<?php esc_attr_e( 'IP Address or Range' ); ?>" size="20" />
				<input type="text" name="newipcomment" id="newipcomment" placeholder="<?php esc_attr_e( 'Identify this entry' ); ?>" size="20" /> <input class="button" type="button" id="addip" value="<?php esc_attr_e( 'Add' ); ?>" />
				<p class="description"><label for="newip"><?php esc_html_e( 'Enter a single IP address or a range using a subnet prefix', 'restricted-site-access' ); ?></label></p>
				<?php if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) : ?>
					<input class="button" type="button" id="rsa_myip" value="<?php esc_attr_e( 'Add My Current IP Address', 'restricted-site-access' ); ?>" style="margin-top: 5px;" data-myip="<?php echo esc_attr( self::get_client_ip_address() ); ?>" /><br />
				<?php endif; ?>
			</div>

			<?php
			$config_ips = self::get_config_ips();
			if ( ! empty( $config_ips ) ) :
				?>
			<div class="config_ips" style="margin-top: 10px;">
				<h4>
					<?php esc_html_e( 'Unrestricted IP addresses set by code configuration', 'restricted-site-access' ); ?>
				</h4>
				<ul class="ul-disc">
					<?php
					foreach ( $config_ips as $ip ) {
						echo '<li><code>' . esc_attr( $ip ) . '</code></li>';
					}
					?>
				</ul>
			</div>
		<?php endif; ?>
		</div>
		<p class="hide-if-js"><strong><?php esc_html_e( 'To manage IP addresses, you must use a JavaScript enabled browser.', 'restricted-site-access' ); ?></strong></p>
		<?php
	}

	/**
	 * Field for custom message.
	 */
	public static function settings_field_message() {
		if ( empty( self::$rsa_options['message'] ) ) {
			self::$rsa_options['message'] = esc_html__( 'Access to this site is restricted.', 'restricted-site-access' );
		}

		wp_editor(
			self::$rsa_options['message'],
			'rsa_message',
			array(
				'media_buttons' => false,
				'textarea_name' => 'rsa_options[message]',
				'textarea_rows' => 4,
				'tinymce'       => false,
			)
		);
	}

	/**
	 * Field for redirection.
	 */
	public static function settings_field_redirect() {
		if ( ! isset( self::$rsa_options['redirect_url'] ) ) {
			// @codeCoverageIgnoreStart
			self::$rsa_options['redirect_url'] = '';
			// @codeCoverageIgnoreEnd
		}
		?>
		<input type="text" name="rsa_options[redirect_url]" id="redirect" class="rsa_redirect_field regular-text" value="<?php echo esc_attr( self::$rsa_options['redirect_url'] ); ?>" />
		<?php
	}

	/**
	 * Field for redirect path option.
	 */
	public static function settings_field_redirect_path() {
		if ( ! isset( self::$rsa_options['redirect_path'] ) ) {
			// @codeCoverageIgnoreStart
			self::$rsa_options['redirect_path'] = 0;
			// @codeCoverageIgnoreEnd
		}
		?>
		<fieldset><legend class="screen-reader-text"><span><?php esc_html( self::$rsa_options['redirect_path']['label'] ); ?></span></legend>
			<label for="redirect_path">
				<input type="checkbox" name="rsa_options[redirect_path]" value="1" id="redirect_path" class="rsa_redirect_field" <?php checked( self::$rsa_options['redirect_path'] ); ?> />
				<?php esc_html_e( 'Send restricted visitor to same path (relative URL) at the new web address', 'restricted-site-access' ); ?></label>
		</fieldset>
		<?php
	}

	/**
	 * Field for specifying redirect code.
	 */
	public static function settings_field_redirect_code() {
		if ( empty( self::$rsa_options['head_code'] ) ) {
			// @codeCoverageIgnoreStart
			self::$rsa_options['head_code'] = 302;
			// @codeCoverageIgnoreEnd
		}
		?>
		<select name="rsa_options[head_code]" id="redirect_code" class="rsa_redirect_field">
			<option value="301" <?php selected( self::$rsa_options['head_code'], 301 ); ?>><?php esc_html_e( '301 Permanent', 'restricted-site-access' ); ?></option>
			<option value="302" <?php selected( self::$rsa_options['head_code'], 302 ); ?>><?php esc_html_e( '302 Undefined', 'restricted-site-access' ); ?></option>
			<option value="307" <?php selected( self::$rsa_options['head_code'], 307 ); ?>><?php esc_html_e( '307 Temporary', 'restricted-site-access' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Field for choosing a page to redirect to.
	 */
	public static function settings_field_rsa_page() {
		if ( ! isset( self::$rsa_options['page'] ) ) {
			// @codeCoverageIgnoreStart
			self::$rsa_options['page'] = 0;
			// @codeCoverageIgnoreEnd
		}

		wp_dropdown_pages(
			array(
				'selected'         => esc_html( self::$rsa_options['page'] ),
				'show_option_none' => esc_html__( 'Select a page', 'restricted-site-access' ),
				'name'             => 'rsa_options[page]',
				'id'               => 'rsa_page',
			)
		);
	}

	/**
	 * Filters the page dropdown to display text if no pages are found.
	 *
	 * @param string $html The HTML to be output by wp_dropdown_pages.
	 * @param array  $args Array of arguments for wp_dropdown_pages.
	 * @return string      Dropdown HTML, or text saying no pages are found.
	 */
	public static function filter_page_dropdown( $html, $args ) {
		if ( '' !== $html || 'rsa_page' !== $args['id'] ) {
			return $html;
		}

		return sprintf(
			'<p class="description" id="%2$s">%1$s</p>',
			esc_html__( 'No published pages found.', 'restricted-site-access' ),
			esc_attr( $args['id'] )
		);
	}

	/**
	 * Validate IP address entry on demand (AJAX).
	 *
	 * @codeCoverageIgnore
	 */
	public static function ajax_rsa_ip_check() {

		if ( ! check_ajax_referer( 'rsa_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error();
			exit;
		}

		if ( empty( $_POST['ip_address'] ) || ! self::is_ip( stripslashes( sanitize_text_field( wp_unslash( $_POST['ip_address'] ) ) ) ) ) {
			die( '1' );
		}
		die;
	}

	/**
	 * Is it a valid IP address? v4/v6 with subnet range.
	 *
	 * @param string $ip_address IP Address to check.
	 *
	 * @return bool True if its a valid IP address.
	 */
	public static function is_ip( $ip_address ) {
		// very basic validation of ranges.
		if ( strpos( $ip_address, '/' ) ) {
			$ip_parts = explode( '/', $ip_address );
			if ( empty( $ip_parts[1] ) || ! is_numeric( $ip_parts[1] ) || strlen( $ip_parts[1] ) > 3 ) {
				return false;
			}
			$ip_address = $ip_parts[0];
		}

		// confirm IP part is a valid IPv6 or IPv4 IP.
		if ( empty( $ip_address ) || ! inet_pton( stripslashes( $ip_address ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets an array of valid IP addresses from constant.
	 *
	 * @return array
	 */
	public static function get_config_ips() {
		if ( ! defined( 'RSA_IP_WHITELIST' ) || ! RSA_IP_WHITELIST ) {
			return array();
		}

		if ( ! is_string( RSA_IP_WHITELIST ) ) {
			return array();
		}

		// Filter out valid IPs from configured ones.
		$raw_ips   = explode( '|', RSA_IP_WHITELIST );
		$valid_ips = array();
		foreach ( $raw_ips as $ip ) {
			$trimmed = trim( $ip );
			if ( self::is_ip( $trimmed ) ) {
				$valid_ips[] = $trimmed;
			}
		}
		return $valid_ips;
	}

	/**
	 * Short-circuit filter the `blog_public` option to match network if necessary.
	 *
	 * This runs for both `get_option()` and `get_site_option()`,
	 * hence the `doing_filter()` check.
	 *
	 * @param  bool $value Value of `blog_public` option, typically false.
	 * @return int
	 */
	public static function pre_option_blog_public( $value ) {
		if ( 'pre_option_blog_public' === current_filter() && RSA_IS_NETWORK && 'enforce' === self::get_network_mode() ) {
			$value = get_site_option( 'blog_public', 2 );
		}

		// Check if constant disallowing restriction is defined.
		if ( defined( 'RSA_FORBID_RESTRICTION' ) && RSA_FORBID_RESTRICTION === true ) {
			$value = 1;
		}

		// Check if constant forcing restriction is defined.
		if ( defined( 'RSA_FORCE_RESTRICTION' ) && RSA_FORCE_RESTRICTION === true ) {
			$value = 2;
		}

		return $value;
	}

	/**
	 * Add settings link directing user to privacy page on plug-in page.
	 *
	 * @param array $links Array of links for plugin actions.
	 *
	 * @return array
	 */
	public static function plugin_action_links( $links ) {
		$links[] = sprintf(
			'<a href="options-%s.php">%s</a>',
			esc_attr( self::$settings_page ),
			esc_html__( 'Settings', 'restricted-site-access' )
		);

		return $links;
	}

	/**
	 * Activation of plugin: upgrades old versions, immediately sets privacy.
	 *
	 * @param boolean $network_active Whether the plugin network active.
	 */
	public static function activation( $network_active ) {
		if ( ! $network_active ) {
			update_option( 'blog_public', 2 );
		}
	}

	/**
	 * Restore privacy option to default value upon deactivating.
	 *
	 * @param boolean $network_active Whether the plugin network active.
	 */
	public static function deactivation( $network_active ) {
		if ( $network_active ) {
			$sites = get_sites();

			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );

				if ( 2 === (int) get_option( 'blog_public' ) ) {
					update_option( 'blog_public', 1 );
				}

				restore_current_blog();
			}
		} else {
			if ( 2 === (int) get_option( 'blog_public' ) ) {
				update_option( 'blog_public', 1 );
			}
		}
	}

	/**
	 * Determine if plugin is network activated.
	 *
	 * @param string $plugin The plugin slug to check.
	 */
	public static function is_network( $plugin ) {

		$plugins = get_site_option( 'active_sitewide_plugins' );

		if ( is_multisite() && isset( $plugins[ $plugin ] ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check if a given ip is in a network.
	 * Source: https://gist.github.com/tott/7684443
	 *
	 * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1.
	 * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed.
	 * @return boolean true if the ip is in this range / false if not.
	 */
	public static function ip_in_range( $ip, $range ) {
		if ( strpos( $range, '/' ) === false ) {
			$range .= '/32';
		}
		// $range is in IP/CIDR format eg 127.0.0.1/24
		list( $range, $netmask ) = explode( '/', $range, 2 );
		$range_decimal           = ip2long( $range );
		$ip_decimal              = ip2long( $ip );
		$wildcard_decimal        = pow( 2, ( 32 - $netmask ) ) - 1;
		$netmask_decimal         = ~ $wildcard_decimal;
		return ( ( $ip_decimal & $netmask_decimal ) === ( $range_decimal & $netmask_decimal ) );
	}

	/**
	 * Retrieve the visitor ip address, even it is behind a proxy.
	 *
	 * @return string
	 */
	public static function get_client_ip_address() {
		$ip      = '';
		$headers = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);
		foreach ( $headers as $key ) {

			if ( ! isset( $_SERVER[ $key ] ) ) {
				continue;
			}

			foreach ( explode(
				',',
				sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) )
			) as $ip ) {
				$ip = trim( $ip ); // just to be safe.

				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
					return $ip;
				}
			}
		}

		return $ip;
	}
}

if ( ! defined( 'RSA_IS_NETWORK' ) ) {
	define( 'RSA_IS_NETWORK', Restricted_Site_Access::is_network( plugin_basename( __FILE__ ) ) );
}

Restricted_Site_Access::get_instance();

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/wp-cli.php';
}

/**
 * Uninstall routine for the plugin
 */
function restricted_site_access_uninstall() {
	if ( RSA_IS_NETWORK ) {
		delete_site_option( 'blog_public' );
		delete_site_option( 'rsa_options' );
		delete_site_option( 'rsa_mode' );

		$sites = get_sites();

		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );

			if ( 2 === (int) get_option( 'blog_public' ) ) {
				update_option( 'blog_public', 1 );
			}
			delete_option( 'rsa_options' );

			restore_current_blog();
		}
	} else {
		if ( 2 === (int) get_option( 'blog_public' ) ) {
			update_option( 'blog_public', 1 );
		}
		delete_option( 'rsa_options' );
	}
}

register_uninstall_hook( __FILE__, 'restricted_site_access_uninstall' );

if ( ! function_exists( 'inet_pton' ) ) :

	/**
	 * Inet_pton is not included in PHP < 5.3 on Windows (WP requires PHP 5.2).
	 *
	 * @param string $ip IP Address.
	 *
	 * @return array|string
	 *
	 * @codeCoverageIgnore
	 */
	function inet_pton( $ip ) {
		if ( strpos( $ip, '.' ) !== false ) {
			// ipv4.
			$ip = pack( 'N', ip2long( $ip ) );
		} elseif ( strpos( $ip, ':' ) !== false ) {
			// ipv6.
			$ip  = explode( ':', $ip );
			$res = str_pad( '', ( 4 * ( 8 - count( $ip ) ) ), '0000', STR_PAD_LEFT );
			foreach ( $ip as $seg ) {
				$res .= str_pad( $seg, 4, '0', STR_PAD_LEFT );
			}
			$ip = pack( 'H' . strlen( $res ), $res );
		}
			return $ip;
	}

endif;
