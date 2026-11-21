<?php
/**
 * Abilities API integration.
 *
 * Registers abilities that let AI agents, automation tools and other clients
 * read and change Restricted Site Access settings and the allowed IP list.
 *
 * @see https://make.wordpress.org/ai/handbook/projects/abilities-api/
 *
 * @package RestrictedSiteAccess
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the Restricted Site Access abilities.
 *
 * @since x.x.x
 */
class Restricted_Site_Access_Abilities {

	/**
	 * Ability category slug.
	 *
	 * @var string
	 */
	const CATEGORY = 'restricted-site-access';

	/**
	 * Ability name prefix.
	 *
	 * @var string
	 */
	const PREFIX = 'restricted-site-access/';

	/**
	 * Restriction modes, keyed by the `approach` value stored in `rsa_options`.
	 *
	 * @var array
	 */
	const MODES = array(
		1 => 'login',
		2 => 'redirect',
		3 => 'message',
		4 => 'page',
	);

	/**
	 * Allowed redirect status codes.
	 *
	 * @var array
	 */
	const STATUS_CODES = array( 301, 302, 307 );

	/**
	 * Network modes.
	 *
	 * @var array
	 */
	const NETWORK_MODES = array( 'default', 'enforce' );

	/**
	 * Input fields that are stored in the `rsa_options` option.
	 *
	 * @var array
	 */
	const OPTION_FIELDS = array(
		'mode',
		'message',
		'redirect_url',
		'redirect_same_path',
		'redirect_status_code',
		'page_id',
		'hide_admin_bar_roles',
		'ips',
	);

	/**
	 * Register hooks when the Abilities API is available.
	 */
	public static function add_hooks() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', array( __CLASS__, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( __CLASS__, 'register_abilities' ) );
	}

	/**
	 * Whether the plugin should register its abilities.
	 *
	 * @return bool
	 */
	private static function is_enabled() {
		/**
		 * Filter whether Restricted Site Access registers its abilities.
		 *
		 * @since x.x.x
		 *
		 * @param bool $enabled Whether to register the abilities. Default true.
		 */
		return (bool) apply_filters( 'restricted_site_access_register_abilities', true );
	}

	/**
	 * Register the ability category.
	 */
	public static function register_category() {
		if ( ! self::is_enabled() ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'Restricted Site Access', 'restricted-site-access' ),
				'description' => __( 'Abilities that read and change who can access the site: the restriction settings, the allowed IP addresses and the network mode.', 'restricted-site-access' ),
			)
		);
	}

	/**
	 * Register the abilities.
	 */
	public static function register_abilities() {
		if ( ! self::is_enabled() ) {
			return;
		}

		$network_property = array(
			'type'        => 'boolean',
			'default'     => false,
			'description' => __( 'Multisite only. True to act on the network-wide settings instead of the current site. Requires the plugin to be network activated.', 'restricted-site-access' ),
		);

		wp_register_ability(
			self::PREFIX . 'settings-query',
			array(
				'label'               => __( 'Query site restriction settings', 'restricted-site-access' ),
				'description'         => __( 'Returns all Restricted Site Access settings: whether the site is restricted, how restricted visitors are handled (login, redirect, message or page), the allowed IP addresses, the network mode, and any network or code configuration that overrides these settings.', 'restricted-site-access' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'default'              => array(),
					'properties'           => array(
						'network' => $network_property,
					),
					'additionalProperties' => false,
				),
				'output_schema'       => self::get_settings_schema(),
				'execute_callback'    => array( __CLASS__, 'execute_settings_query' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'meta'                => self::get_meta(
					array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					)
				),
			)
		);

		wp_register_ability(
			self::PREFIX . 'settings-update',
			array(
				'label'               => __( 'Update site restriction settings', 'restricted-site-access' ),
				'description'         => __( 'Changes Restricted Site Access settings: turns restriction on or off, changes how restricted visitors are handled, replaces the allowed IP addresses, and sets the network mode. Only the fields you send change. Logged-in users and allowed IP addresses always keep access.', 'restricted-site-access' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'restricted'           => array(
							'type'        => 'boolean',
							'description' => __( 'True to restrict the site to logged-in users and allowed IP addresses. False to make the site visible again.', 'restricted-site-access' ),
						),
						'mode'                 => array(
							'type'        => 'string',
							'enum'        => array_values( self::MODES ),
							'description' => __( 'How to handle restricted visitors. "login" sends them to the login screen. "redirect" sends them to redirect_url. "message" shows message. "page" shows the page in page_id.', 'restricted-site-access' ),
						),
						'message'              => array(
							'type'        => 'string',
							'description' => __( 'Message shown in "message" mode. Basic HTML is allowed.', 'restricted-site-access' ),
						),
						'redirect_url'         => array(
							'type'        => 'string',
							'description' => __( 'HTTP or HTTPS address used in "redirect" mode.', 'restricted-site-access' ),
						),
						'redirect_same_path'   => array(
							'type'        => 'boolean',
							'description' => __( 'In "redirect" mode, keep the requested path when redirecting.', 'restricted-site-access' ),
						),
						'redirect_status_code' => array(
							'type'        => 'integer',
							'enum'        => self::STATUS_CODES,
							'description' => __( 'HTTP status code used in "redirect" mode.', 'restricted-site-access' ),
						),
						'page_id'              => array(
							'type'        => 'integer',
							'minimum'     => 1,
							'description' => __( 'ID of the page shown in "page" mode.', 'restricted-site-access' ),
						),
						'hide_admin_bar_roles' => array(
							'type'        => 'array',
							'items'       => array( 'type' => 'string' ),
							'description' => __( 'Role slugs that do not see the admin bar on the front end. Replaces the current list.', 'restricted-site-access' ),
						),
						'ips'                  => array(
							'type'        => 'array',
							'items'       => self::get_ip_schema(),
							'description' => __( 'Allowed IP addresses and ranges. Replaces the saved list, so send the full list: to add an address, send the current ips plus the new one. An empty list removes every saved address. Addresses from the RSA_IP_WHITELIST constant are not part of this list.', 'restricted-site-access' ),
						),
						'network_mode'         => array(
							'type'        => 'string',
							'enum'        => self::NETWORK_MODES,
							'description' => __( 'Multisite only, and only with "network": true. "enforce" applies the network settings to every site and overrides their own settings. "default" lets each site use its own settings, and new sites start from the network settings.', 'restricted-site-access' ),
						),
						'network'              => $network_property,
					),
					'additionalProperties' => false,
				),
				'output_schema'       => self::get_settings_schema(),
				'execute_callback'    => array( __CLASS__, 'execute_settings_update' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'meta'                => self::get_meta(
					array(
						'readonly'    => false,
						'destructive' => true,
					)
				),
			)
		);
	}

	/**
	 * Build the ability meta array.
	 *
	 * @param array $annotations Behavior annotations.
	 * @return array
	 */
	private static function get_meta( $annotations ) {
		return array(
			'annotations'  => $annotations,
			'public'       => true,
			'show_in_rest' => true,
		);
	}

	/**
	 * JSON Schema of the settings result.
	 *
	 * @return array
	 */
	private static function get_settings_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'scope'                 => array(
					'type'        => 'string',
					'enum'        => array( 'site', 'network' ),
					'description' => __( 'Whether these are the current site settings or the network-wide settings.', 'restricted-site-access' ),
				),
				'restricted'            => array(
					'type'        => 'boolean',
					'description' => __( 'Whether restriction is on. This value includes any override from the network or from code configuration.', 'restricted-site-access' ),
				),
				'mode'                  => array(
					'type' => 'string',
					'enum' => array_values( self::MODES ),
				),
				'message'               => array(
					'type' => 'string',
				),
				'redirect_url'          => array(
					'type' => 'string',
				),
				'redirect_same_path'    => array(
					'type' => 'boolean',
				),
				'redirect_status_code'  => array(
					'type' => 'integer',
				),
				'page_id'               => array(
					'type' => 'integer',
				),
				'hide_admin_bar_roles'  => self::get_string_list_schema( __( 'Role slugs that do not see the admin bar on the front end.', 'restricted-site-access' ) ),
				'ips'                   => array(
					'type'        => 'array',
					'items'       => self::get_ip_schema(),
					'description' => __( 'Allowed IP addresses and ranges saved in the settings.', 'restricted-site-access' ),
				),
				'config_ips'            => self::get_string_list_schema( __( 'Allowed IP addresses from the RSA_IP_WHITELIST constant. They are set in code and cannot be changed through abilities.', 'restricted-site-access' ) ),
				'network_mode'          => array(
					'type'        => array( 'string', 'null' ),
					'enum'        => array( 'default', 'enforce', null ),
					'description' => __( 'Network mode when the plugin is network activated, otherwise null.', 'restricted-site-access' ),
				),
				'network_mode_locked'   => array(
					'type'        => 'boolean',
					'description' => __( 'True when the RSA_NETWORK_MODE constant fixes the network mode in code, so it cannot be changed.', 'restricted-site-access' ),
				),
				'overridden_by_network' => array(
					'type'        => 'boolean',
					'description' => __( 'True when the network enforces its settings, so these site settings have no effect and cannot be changed.', 'restricted-site-access' ),
				),
				'restriction_constant'  => array(
					'type'        => array( 'string', 'null' ),
					'enum'        => array( 'force', 'forbid', null ),
					'description' => __( 'Set when RSA_FORCE_RESTRICTION or RSA_FORBID_RESTRICTION fixes the restricted value in code. The restricted value then cannot be changed.', 'restricted-site-access' ),
				),
			),
			'required'   => array(
				'scope',
				'restricted',
				'mode',
				'message',
				'redirect_url',
				'redirect_same_path',
				'redirect_status_code',
				'page_id',
				'hide_admin_bar_roles',
				'ips',
				'config_ips',
				'network_mode',
				'network_mode_locked',
				'overridden_by_network',
				'restriction_constant',
			),
		);
	}

	/**
	 * JSON Schema of one allowed IP address.
	 *
	 * The same shape is used for input and output, so a client can send back
	 * the `ips` list it read.
	 *
	 * @return array
	 */
	private static function get_ip_schema() {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'ip'    => array(
					'type'        => 'string',
					'minLength'   => 1,
					'description' => __( 'IPv4 or IPv6 address, or a range in CIDR notation, such as 192.168.0.0/24.', 'restricted-site-access' ),
				),
				'label' => array(
					'type'        => 'string',
					'description' => __( 'Optional note that describes the address.', 'restricted-site-access' ),
				),
			),
			'required'             => array( 'ip' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * JSON Schema of a list of strings.
	 *
	 * @param string $description Field description.
	 * @return array
	 */
	private static function get_string_list_schema( $description ) {
		return array(
			'type'        => 'array',
			'items'       => array( 'type' => 'string' ),
			'description' => $description,
		);
	}

	/**
	 * Whether the input asks for the network-wide settings.
	 *
	 * @param mixed $input Ability input.
	 * @return bool
	 */
	private static function is_network_input( $input ) {
		return is_array( $input ) && isset( $input['network'] ) && rest_sanitize_boolean( $input['network'] );
	}

	/**
	 * Whether the network enforces its settings on the current site.
	 *
	 * @return bool
	 */
	private static function is_site_overridden() {
		return RSA_IS_NETWORK && 'enforce' === Restricted_Site_Access::get_network_mode();
	}

	/**
	 * The restriction constant set in code, if any.
	 *
	 * RSA_FORCE_RESTRICTION wins when both are set, matching pre_option_blog_public().
	 *
	 * @return string|null "force", "forbid" or null.
	 */
	private static function get_restriction_constant() {
		if ( defined( 'RSA_FORCE_RESTRICTION' ) && true === RSA_FORCE_RESTRICTION ) {
			return 'force';
		}

		if ( defined( 'RSA_FORBID_RESTRICTION' ) && true === RSA_FORBID_RESTRICTION ) {
			return 'forbid';
		}

		return null;
	}

	/**
	 * Permission check for site or network settings.
	 *
	 * Site settings need `manage_options`, like the Reading settings screen.
	 * Network settings and the network mode need `manage_network_options`.
	 *
	 * When network settings are requested but the plugin is not network
	 * activated, the execute callback returns a descriptive error instead.
	 *
	 * @param mixed $input Ability input.
	 * @return bool
	 */
	public static function can_manage( $input = null ) {
		$network_mode = is_array( $input ) && isset( $input['network_mode'] );

		if ( RSA_IS_NETWORK && ( $network_mode || self::is_network_input( $input ) ) ) {
			return current_user_can( 'manage_network_options' );
		}

		return current_user_can( 'manage_options' );
	}

	/**
	 * Check that the requested scope can be used.
	 *
	 * @param bool $network Whether the network-wide settings were requested.
	 * @param bool $write   Whether the ability changes settings.
	 * @return WP_Error|null Error, or null when the scope can be used.
	 */
	private static function get_scope_error( $network, $write ) {
		if ( $network && ! RSA_IS_NETWORK ) {
			return self::network_unavailable_error();
		}

		if ( $write && ! $network && self::is_site_overridden() ) {
			return new WP_Error(
				'rsa_network_enforced',
				__( 'The network enforces its settings on every site, so this site\'s settings cannot be changed. Send "network": true to change the network settings.', 'restricted-site-access' ),
				array( 'status' => 409 )
			);
		}

		return null;
	}

	/**
	 * Execute: query the settings.
	 *
	 * @param mixed $input Ability input.
	 * @return array|WP_Error
	 */
	public static function execute_settings_query( $input = null ) {
		$network     = self::is_network_input( $input );
		$scope_error = self::get_scope_error( $network, false );

		return $scope_error ? $scope_error : self::prepare_settings( $network );
	}

	/**
	 * Execute: update the settings.
	 *
	 * Every field is checked before anything is saved, so a call either makes
	 * all of its changes or none of them.
	 *
	 * @param mixed $input Ability input.
	 * @return array|WP_Error
	 */
	public static function execute_settings_update( $input = null ) {
		$input   = is_array( $input ) ? $input : array();
		$network = self::is_network_input( $input );
		$fields  = array_diff_key( $input, array( 'network' => true ) );

		if ( empty( $fields ) ) {
			return new WP_Error(
				'rsa_no_changes',
				__( 'Send at least one setting to change.', 'restricted-site-access' ),
				array( 'status' => 400 )
			);
		}

		// The network mode is checked first, so a missing network flag is not reported as a site error.
		if ( isset( $input['network_mode'] ) ) {
			$network_mode_error = self::get_network_mode_error( (string) $input['network_mode'], $network );
			if ( $network_mode_error ) {
				return $network_mode_error;
			}
		}

		$scope_error = self::get_scope_error( $network, true );
		if ( $scope_error ) {
			return $scope_error;
		}

		if ( isset( $input['restricted'] ) && null !== self::get_restriction_constant() ) {
			return new WP_Error(
				'rsa_restriction_locked',
				__( 'Site restriction is fixed by the RSA_FORCE_RESTRICTION or RSA_FORBID_RESTRICTION constant and cannot be changed.', 'restricted-site-access' ),
				array( 'status' => 409 )
			);
		}

		$options = Restricted_Site_Access::get_options( $network );

		if ( isset( $input['mode'] ) ) {
			$options['approach'] = (int) array_search( $input['mode'], self::MODES, true );
		}

		if ( isset( $input['message'] ) ) {
			$options['message'] = (string) $input['message'];
		}

		if ( isset( $input['redirect_url'] ) ) {
			$redirect_url = esc_url_raw( trim( (string) $input['redirect_url'] ), array( 'http', 'https' ) );
			if ( '' === $redirect_url ) {
				return new WP_Error(
					'rsa_invalid_redirect_url',
					__( 'The redirect_url must be a valid HTTP or HTTPS address.', 'restricted-site-access' ),
					array( 'status' => 400 )
				);
			}
			$options['redirect_url'] = $redirect_url;
		}

		if ( isset( $input['redirect_same_path'] ) ) {
			$options['redirect_path'] = rest_sanitize_boolean( $input['redirect_same_path'] ) ? 1 : 0;
		}

		if ( isset( $input['redirect_status_code'] ) ) {
			$options['head_code'] = (int) $input['redirect_status_code'];
		}

		if ( isset( $input['page_id'] ) ) {
			$options['page'] = absint( $input['page_id'] );
		}

		if ( isset( $input['hide_admin_bar_roles'] ) ) {
			$roles         = array_values( array_unique( array_map( 'strval', (array) $input['hide_admin_bar_roles'] ) ) );
			$invalid_roles = array_diff( $roles, array_keys( wp_roles()->roles ) );
			if ( ! empty( $invalid_roles ) ) {
				return new WP_Error(
					'rsa_invalid_roles',
					sprintf(
						/* translators: %s: comma-separated list of role slugs. */
						__( 'These roles do not exist: %s.', 'restricted-site-access' ),
						implode( ', ', $invalid_roles )
					),
					array( 'status' => 400 )
				);
			}
			$options['hide_admin_bar_roles'] = $roles;
		}

		if ( isset( $input['ips'] ) ) {
			$entries = self::parse_ips( (array) $input['ips'] );
			if ( is_wp_error( $entries ) ) {
				return $entries;
			}
			$options['allowed'] = array_column( $entries, 'ip' );
			$options['comment'] = array_column( $entries, 'label' );
		}

		// The chosen mode must have what it needs, whether it was sent now or saved earlier.
		$mode = isset( self::MODES[ (int) $options['approach'] ] ) ? self::MODES[ (int) $options['approach'] ] : 'login';

		if ( 'redirect' === $mode && empty( $options['redirect_url'] ) ) {
			return new WP_Error(
				'rsa_redirect_url_required',
				__( 'The "redirect" mode needs a redirect_url.', 'restricted-site-access' ),
				array( 'status' => 400 )
			);
		}

		if ( 'page' === $mode ) {
			$page = empty( $options['page'] ) ? null : get_post( (int) $options['page'] );
			if ( ! $page || 'page' !== $page->post_type ) {
				return new WP_Error(
					'rsa_invalid_page',
					__( 'The "page" mode needs the page_id of an existing page.', 'restricted-site-access' ),
					array( 'status' => 400 )
				);
			}
		}

		if ( array_intersect_key( $input, array_flip( self::OPTION_FIELDS ) ) ) {
			self::save_options( $options, $network );
		}

		if ( isset( $input['restricted'] ) ) {
			self::save_restricted( rest_sanitize_boolean( $input['restricted'] ), $network );
		}

		if ( isset( $input['network_mode'] ) ) {
			update_site_option( 'rsa_mode', (string) $input['network_mode'] );
		}

		return self::prepare_settings( $network );
	}

	/**
	 * Check that the network mode can be changed.
	 *
	 * @param string $mode    Requested network mode.
	 * @param bool   $network Whether the network-wide settings were requested.
	 * @return WP_Error|null Error, or null when the network mode can be changed.
	 */
	private static function get_network_mode_error( $mode, $network ) {
		if ( ! RSA_IS_NETWORK ) {
			return self::network_unavailable_error();
		}

		if ( ! $network ) {
			return new WP_Error(
				'rsa_network_scope_required',
				__( 'The network_mode is a network setting. Send it with "network": true.', 'restricted-site-access' ),
				array( 'status' => 400 )
			);
		}

		if ( '' !== Restricted_Site_Access::get_config_network_mode() ) {
			return new WP_Error(
				'rsa_network_mode_locked',
				__( 'The network mode is fixed by the RSA_NETWORK_MODE constant and cannot be changed.', 'restricted-site-access' ),
				array( 'status' => 409 )
			);
		}

		if ( ! in_array( $mode, self::NETWORK_MODES, true ) ) {
			return new WP_Error(
				'rsa_invalid_network_mode',
				__( 'The network_mode must be "default" or "enforce".', 'restricted-site-access' ),
				array( 'status' => 400 )
			);
		}

		return null;
	}

	/**
	 * Validate and clean a list of allowed IP addresses.
	 *
	 * When the same address is sent more than once, the last entry wins.
	 *
	 * @param array $items List of arrays with `ip` and optional `label` keys.
	 * @return array|WP_Error List of arrays with `ip` and `label` keys, or an error.
	 */
	private static function parse_ips( $items ) {
		$entries = array();
		$invalid = array();

		foreach ( $items as $item ) {
			$ip = is_array( $item ) && isset( $item['ip'] ) ? trim( (string) $item['ip'] ) : '';

			if ( ! Restricted_Site_Access::is_ip( $ip ) ) {
				$invalid[] = $ip;
				continue;
			}

			$entries[ $ip ] = array(
				'ip'    => $ip,
				'label' => isset( $item['label'] ) ? sanitize_text_field( (string) $item['label'] ) : '',
			);
		}

		if ( ! empty( $invalid ) ) {
			return new WP_Error(
				'rsa_invalid_ip',
				sprintf(
					/* translators: %s: comma-separated list of IP addresses. */
					__( 'These are not valid IP addresses or ranges: %s.', 'restricted-site-access' ),
					implode( ', ', $invalid )
				),
				array( 'status' => 400 )
			);
		}

		return array_values( $entries );
	}

	/**
	 * Build the settings result.
	 *
	 * @param bool $network Whether to read the network-wide settings.
	 * @return array
	 */
	private static function prepare_settings( $network ) {
		$options     = Restricted_Site_Access::get_options( $network );
		$blog_public = $network ? get_site_option( 'blog_public', 2 ) : get_option( 'blog_public', 2 );
		$approach    = (int) $options['approach'];

		return array(
			'scope'                 => $network ? 'network' : 'site',
			'restricted'            => 2 === (int) $blog_public,
			'mode'                  => isset( self::MODES[ $approach ] ) ? self::MODES[ $approach ] : 'login',
			'message'               => (string) $options['message'],
			'redirect_url'          => (string) $options['redirect_url'],
			'redirect_same_path'    => ! empty( $options['redirect_path'] ),
			'redirect_status_code'  => (int) $options['head_code'],
			'page_id'               => (int) $options['page'],
			'hide_admin_bar_roles'  => array_values( array_map( 'strval', (array) $options['hide_admin_bar_roles'] ) ),
			'ips'                   => self::get_saved_ips( $options ),
			'config_ips'            => array_values( Restricted_Site_Access::get_config_ips() ),
			'network_mode'          => RSA_IS_NETWORK ? Restricted_Site_Access::get_network_mode() : null,
			'network_mode_locked'   => RSA_IS_NETWORK && '' !== Restricted_Site_Access::get_config_network_mode(),
			'overridden_by_network' => ! $network && self::is_site_overridden(),
			'restriction_constant'  => self::get_restriction_constant(),
		);
	}

	/**
	 * Get the IP addresses saved in the settings, with their labels.
	 *
	 * @param array $options The `rsa_options` values.
	 * @return array List of arrays with `ip` and `label` keys.
	 */
	private static function get_saved_ips( $options ) {
		$ips     = array_values( (array) $options['allowed'] );
		$labels  = isset( $options['comment'] ) ? array_values( (array) $options['comment'] ) : array();
		$entries = array();

		foreach ( $ips as $index => $ip ) {
			$entries[] = array(
				'ip'    => (string) $ip,
				'label' => isset( $labels[ $index ] ) ? (string) $labels[ $index ] : '',
			);
		}

		return $entries;
	}

	/**
	 * Sanitize and save the `rsa_options` option.
	 *
	 * @param array $options Options to save.
	 * @param bool  $network Whether to save the network-wide settings.
	 */
	private static function save_options( $options, $network ) {
		$sanitized = Restricted_Site_Access::sanitize_options( $options );

		if ( $network ) {
			update_site_option( 'rsa_options', $sanitized );
			return;
		}

		update_option( 'rsa_options', $sanitized );

		// Restricted_Site_Access caches the site options in a static property, and
		// some of its methods reuse that copy. get_ips() reloads it, so later calls
		// in the same request see the saved values.
		Restricted_Site_Access::get_ips( false );
	}

	/**
	 * Turn restriction on or off.
	 *
	 * Turning restriction off makes the site public (blog_public 1), matching
	 * `wp rsa set-mode disable`. Turning it off when it is already off keeps the
	 * current visibility, such as "discourage search engines".
	 *
	 * @param bool $restricted Whether the site should be restricted.
	 * @param bool $network    Whether to change the network-wide setting.
	 */
	private static function save_restricted( $restricted, $network ) {
		$current = (int) ( $network ? get_site_option( 'blog_public', 2 ) : get_option( 'blog_public', 2 ) );

		if ( $restricted ) {
			$value = 2;
		} elseif ( 2 === $current ) {
			$value = 1;
		} else {
			return;
		}

		if ( $network ) {
			update_site_option( 'blog_public', $value );
		} else {
			update_option( 'blog_public', $value );
		}
	}

	/**
	 * Error returned when network settings are requested without network activation.
	 *
	 * @return WP_Error
	 */
	private static function network_unavailable_error() {
		return new WP_Error(
			'rsa_network_unavailable',
			__( 'Network settings are only available when the plugin is network activated.', 'restricted-site-access' ),
			array( 'status' => 400 )
		);
	}
}
