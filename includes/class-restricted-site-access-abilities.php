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
				'description' => __( 'Abilities that read and change who can access the site: the restriction settings and the list of allowed IP addresses.', 'restricted-site-access' ),
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

		$ip_property = array(
			'type'        => 'string',
			'minLength'   => 1,
			'description' => __( 'IPv4 or IPv6 address, or a range in CIDR notation, such as 192.168.0.0/24.', 'restricted-site-access' ),
		);

		$read_meta = self::get_meta(
			array(
				'readonly'    => true,
				'destructive' => false,
				'idempotent'  => true,
			)
		);

		wp_register_ability(
			self::PREFIX . 'get-settings',
			array(
				'label'               => __( 'Get site restriction settings', 'restricted-site-access' ),
				'description'         => __( 'Returns whether the site is restricted, how restricted visitors are handled (login, redirect, message or page), and any network or code configuration that overrides these settings.', 'restricted-site-access' ),
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
				'execute_callback'    => array( __CLASS__, 'execute_get_settings' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'meta'                => $read_meta,
			)
		);

		wp_register_ability(
			self::PREFIX . 'update-settings',
			array(
				'label'               => __( 'Update site restriction settings', 'restricted-site-access' ),
				'description'         => __( 'Turns site restriction on or off and changes how restricted visitors are handled. Only the fields you send change. Logged-in users and allowed IP addresses always keep access.', 'restricted-site-access' ),
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
						'network'              => $network_property,
					),
					'additionalProperties' => false,
				),
				'output_schema'       => self::get_settings_schema(),
				'execute_callback'    => array( __CLASS__, 'execute_update_settings' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'meta'                => self::get_meta(
					array(
						'readonly'    => false,
						'destructive' => true,
					)
				),
			)
		);

		wp_register_ability(
			self::PREFIX . 'list-ips',
			array(
				'label'               => __( 'List allowed IP addresses', 'restricted-site-access' ),
				'description'         => __( 'Returns the IP addresses and ranges that can access the site while it is restricted. Addresses with source "config" come from the RSA_IP_WHITELIST constant and cannot be changed through abilities.', 'restricted-site-access' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'default'              => array(),
					'properties'           => array(
						'network' => $network_property,
					),
					'additionalProperties' => false,
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'ips' => self::get_ip_list_schema(),
					),
					'required'   => array( 'ips' ),
				),
				'execute_callback'    => array( __CLASS__, 'execute_list_ips' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'meta'                => $read_meta,
			)
		);

		wp_register_ability(
			self::PREFIX . 'add-ips',
			array(
				'label'               => __( 'Add allowed IP addresses', 'restricted-site-access' ),
				'description'         => __( 'Adds IP addresses or ranges to the allowed list, each with an optional label. When an address is already listed and a label is sent, the label is updated. Fails without changes when any address is invalid.', 'restricted-site-access' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'ips'     => array(
							'type'     => 'array',
							'minItems' => 1,
							'maxItems' => 100,
							'items'    => array(
								'type'                 => 'object',
								'properties'           => array(
									'ip'    => $ip_property,
									'label' => array(
										'type'        => 'string',
										'description' => __( 'Optional note that describes the address.', 'restricted-site-access' ),
									),
								),
								'required'             => array( 'ip' ),
								'additionalProperties' => false,
							),
						),
						'network' => $network_property,
					),
					'required'             => array( 'ips' ),
					'additionalProperties' => false,
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'added'     => self::get_string_list_schema( __( 'Addresses added to the list.', 'restricted-site-access' ) ),
						'updated'   => self::get_string_list_schema( __( 'Addresses already listed whose label changed.', 'restricted-site-access' ) ),
						'unchanged' => self::get_string_list_schema( __( 'Addresses already listed with the same label.', 'restricted-site-access' ) ),
						'ips'       => self::get_ip_list_schema(),
					),
					'required'   => array( 'added', 'updated', 'unchanged', 'ips' ),
				),
				'execute_callback'    => array( __CLASS__, 'execute_add_ips' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'meta'                => self::get_meta(
					array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => true,
					)
				),
			)
		);

		wp_register_ability(
			self::PREFIX . 'update-ip',
			array(
				'label'               => __( 'Update an allowed IP address', 'restricted-site-access' ),
				'description'         => __( 'Replaces an allowed IP address with a new address, changes its label, or both. Send at least one of new_ip or label.', 'restricted-site-access' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'ip'      => $ip_property,
						'new_ip'  => $ip_property,
						'label'   => array(
							'type'        => 'string',
							'description' => __( 'New label. Send an empty string to remove the label.', 'restricted-site-access' ),
						),
						'network' => $network_property,
					),
					'required'             => array( 'ip' ),
					'additionalProperties' => false,
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'ip'    => array(
							'type'        => 'string',
							'description' => __( 'The address after the update.', 'restricted-site-access' ),
						),
						'label' => array(
							'type'        => 'string',
							'description' => __( 'The label after the update.', 'restricted-site-access' ),
						),
						'ips'   => self::get_ip_list_schema(),
					),
					'required'   => array( 'ip', 'label', 'ips' ),
				),
				'execute_callback'    => array( __CLASS__, 'execute_update_ip' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'meta'                => self::get_meta(
					array(
						'readonly'    => false,
						'destructive' => true,
					)
				),
			)
		);

		wp_register_ability(
			self::PREFIX . 'remove-ips',
			array(
				'label'               => __( 'Remove allowed IP addresses', 'restricted-site-access' ),
				'description'         => __( 'Removes IP addresses or ranges from the allowed list. Addresses that are not listed are reported in not_found.', 'restricted-site-access' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'ips'     => array(
							'type'     => 'array',
							'minItems' => 1,
							'maxItems' => 100,
							'items'    => $ip_property,
						),
						'network' => $network_property,
					),
					'required'             => array( 'ips' ),
					'additionalProperties' => false,
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'removed'   => self::get_string_list_schema( __( 'Addresses removed from the list.', 'restricted-site-access' ) ),
						'not_found' => self::get_string_list_schema( __( 'Addresses that were not in the editable list. This includes addresses from the RSA_IP_WHITELIST constant.', 'restricted-site-access' ) ),
						'ips'       => self::get_ip_list_schema(),
					),
					'required'   => array( 'removed', 'not_found', 'ips' ),
				),
				'execute_callback'    => array( __CLASS__, 'execute_remove_ips' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
				'meta'                => self::get_meta(
					array(
						'readonly'    => false,
						'destructive' => true,
						'idempotent'  => true,
					)
				),
			)
		);

		if ( RSA_IS_NETWORK ) {
			wp_register_ability(
				self::PREFIX . 'set-network-mode',
				array(
					'label'               => __( 'Set network mode', 'restricted-site-access' ),
					'description'         => __( 'Multisite only. "enforce" applies the network settings to every site and overrides their own settings. "default" lets each site use its own settings, and new sites start from the network settings.', 'restricted-site-access' ),
					'category'            => self::CATEGORY,
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'mode' => array(
								'type' => 'string',
								'enum' => array( 'default', 'enforce' ),
							),
						),
						'required'             => array( 'mode' ),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'network_mode' => array(
								'type' => 'string',
								'enum' => array( 'default', 'enforce' ),
							),
							'changed'      => array(
								'type'        => 'boolean',
								'description' => __( 'False when the network already used this mode.', 'restricted-site-access' ),
							),
						),
						'required'   => array( 'network_mode', 'changed' ),
					),
					'execute_callback'    => array( __CLASS__, 'execute_set_network_mode' ),
					'permission_callback' => array( __CLASS__, 'can_manage_network' ),
					'meta'                => self::get_meta(
						array(
							'readonly'    => false,
							'destructive' => true,
						)
					),
				)
			);
		}
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
				'network_mode'          => array(
					'type'        => array( 'string', 'null' ),
					'enum'        => array( 'default', 'enforce', null ),
					'description' => __( 'Network mode when the plugin is network activated, otherwise null.', 'restricted-site-access' ),
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
				'network_mode',
				'overridden_by_network',
				'restriction_constant',
			),
		);
	}

	/**
	 * JSON Schema of an allowed IP list.
	 *
	 * @return array
	 */
	private static function get_ip_list_schema() {
		return array(
			'type'        => 'array',
			'description' => __( 'All allowed IP addresses after the call.', 'restricted-site-access' ),
			'items'       => array(
				'type'       => 'object',
				'properties' => array(
					'ip'     => array(
						'type' => 'string',
					),
					'label'  => array(
						'type' => 'string',
					),
					'source' => array(
						'type'        => 'string',
						'enum'        => array( 'settings', 'config' ),
						'description' => __( '"settings" for addresses saved in the plugin settings. "config" for addresses from the RSA_IP_WHITELIST constant.', 'restricted-site-access' ),
					),
				),
				'required'   => array( 'ip', 'label', 'source' ),
			),
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
		return is_array( $input ) && ! empty( $input['network'] );
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
	 * Network settings need `manage_network_options`.
	 *
	 * When network settings are requested but the plugin is not network
	 * activated, the execute callback returns a descriptive error instead.
	 *
	 * @param mixed $input Ability input.
	 * @return bool
	 */
	public static function can_manage( $input = null ) {
		if ( RSA_IS_NETWORK && self::is_network_input( $input ) ) {
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
			return new WP_Error(
				'rsa_network_unavailable',
				__( 'Network settings are only available when the plugin is network activated.', 'restricted-site-access' ),
				array( 'status' => 400 )
			);
		}

		if ( $write && ! $network && self::is_site_overridden() ) {
			return self::site_overridden_error();
		}

		return null;
	}

	/**
	 * Permission check for network-only abilities.
	 *
	 * @return bool
	 */
	public static function can_manage_network() {
		return RSA_IS_NETWORK && current_user_can( 'manage_network_options' );
	}

	/**
	 * Execute: get the settings.
	 *
	 * @param mixed $input Ability input.
	 * @return array|WP_Error
	 */
	public static function execute_get_settings( $input = null ) {
		$network     = self::is_network_input( $input );
		$scope_error = self::get_scope_error( $network, false );

		return $scope_error ? $scope_error : self::prepare_settings( $network );
	}

	/**
	 * Execute: update the settings.
	 *
	 * @param mixed $input Ability input.
	 * @return array|WP_Error
	 */
	public static function execute_update_settings( $input = null ) {
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
			$options['redirect_path'] = $input['redirect_same_path'] ? 1 : 0;
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

		self::save_options( $options, $network );

		if ( isset( $input['restricted'] ) ) {
			self::save_restricted( (bool) $input['restricted'], $network );
		}

		return self::prepare_settings( $network );
	}

	/**
	 * Execute: list the allowed IP addresses.
	 *
	 * @param mixed $input Ability input.
	 * @return array|WP_Error
	 */
	public static function execute_list_ips( $input = null ) {
		$network     = self::is_network_input( $input );
		$scope_error = self::get_scope_error( $network, false );

		return $scope_error ? $scope_error : array( 'ips' => self::get_ip_list( $network ) );
	}

	/**
	 * Execute: add allowed IP addresses.
	 *
	 * @param mixed $input Ability input.
	 * @return array|WP_Error
	 */
	public static function execute_add_ips( $input = null ) {
		$input   = is_array( $input ) ? $input : array();
		$network = self::is_network_input( $input );

		$scope_error = self::get_scope_error( $network, true );
		if ( $scope_error ) {
			return $scope_error;
		}

		$requested = array();
		$invalid   = array();

		foreach ( isset( $input['ips'] ) ? (array) $input['ips'] : array() as $item ) {
			$ip = isset( $item['ip'] ) ? trim( (string) $item['ip'] ) : '';
			if ( ! Restricted_Site_Access::is_ip( $ip ) ) {
				$invalid[] = $ip;
				continue;
			}
			// A later entry for the same address wins.
			$requested[ $ip ] = isset( $item['label'] ) ? sanitize_text_field( $item['label'] ) : null;
		}

		if ( ! empty( $invalid ) ) {
			return self::invalid_ips_error( $invalid );
		}

		$entries   = self::get_saved_ips( $network );
		$result    = array(
			'added'     => array(),
			'updated'   => array(),
			'unchanged' => array(),
		);
		$has_saved = array();

		foreach ( $entries as $index => $entry ) {
			$has_saved[ $entry['ip'] ] = $index;
		}

		foreach ( $requested as $ip => $label ) {
			$ip = (string) $ip;

			if ( ! isset( $has_saved[ $ip ] ) ) {
				$entries[]         = array(
					'ip'    => $ip,
					'label' => null === $label ? '' : $label,
				);
				$has_saved[ $ip ]  = count( $entries ) - 1;
				$result['added'][] = $ip;
			} elseif ( null !== $label && $entries[ $has_saved[ $ip ] ]['label'] !== $label ) {
				$entries[ $has_saved[ $ip ] ]['label'] = $label;
				$result['updated'][]                   = $ip;
			} else {
				$result['unchanged'][] = $ip;
			}
		}

		if ( ! empty( $result['added'] ) || ! empty( $result['updated'] ) ) {
			self::save_ips( $entries, $network );
		}

		$result['ips'] = self::get_ip_list( $network );

		return $result;
	}

	/**
	 * Execute: change an allowed IP address or its label.
	 *
	 * @param mixed $input Ability input.
	 * @return array|WP_Error
	 */
	public static function execute_update_ip( $input = null ) {
		$input   = is_array( $input ) ? $input : array();
		$network = self::is_network_input( $input );

		$scope_error = self::get_scope_error( $network, true );
		if ( $scope_error ) {
			return $scope_error;
		}

		if ( ! isset( $input['new_ip'] ) && ! isset( $input['label'] ) ) {
			return new WP_Error(
				'rsa_no_changes',
				__( 'Send new_ip, label, or both.', 'restricted-site-access' ),
				array( 'status' => 400 )
			);
		}

		$ip      = isset( $input['ip'] ) ? trim( (string) $input['ip'] ) : '';
		$entries = self::get_saved_ips( $network );
		$index   = array_search( $ip, array_column( $entries, 'ip' ), true );

		if ( false === $index ) {
			$message = in_array( $ip, Restricted_Site_Access::get_config_ips(), true )
				? __( 'This address comes from the RSA_IP_WHITELIST constant and cannot be changed.', 'restricted-site-access' )
				: __( 'This address is not in the allowed list.', 'restricted-site-access' );

			return new WP_Error( 'rsa_ip_not_found', $message, array( 'status' => 404 ) );
		}

		if ( isset( $input['new_ip'] ) ) {
			$new_ip = trim( (string) $input['new_ip'] );

			if ( ! Restricted_Site_Access::is_ip( $new_ip ) ) {
				return self::invalid_ips_error( array( $new_ip ) );
			}

			if ( $new_ip !== $ip && in_array( $new_ip, array_column( $entries, 'ip' ), true ) ) {
				return new WP_Error(
					'rsa_ip_exists',
					__( 'The new_ip is already in the allowed list.', 'restricted-site-access' ),
					array( 'status' => 409 )
				);
			}

			$entries[ $index ]['ip'] = $new_ip;
		}

		if ( isset( $input['label'] ) ) {
			$entries[ $index ]['label'] = sanitize_text_field( $input['label'] );
		}

		self::save_ips( $entries, $network );

		return array(
			'ip'    => $entries[ $index ]['ip'],
			'label' => $entries[ $index ]['label'],
			'ips'   => self::get_ip_list( $network ),
		);
	}

	/**
	 * Execute: remove allowed IP addresses.
	 *
	 * @param mixed $input Ability input.
	 * @return array|WP_Error
	 */
	public static function execute_remove_ips( $input = null ) {
		$input   = is_array( $input ) ? $input : array();
		$network = self::is_network_input( $input );

		$scope_error = self::get_scope_error( $network, true );
		if ( $scope_error ) {
			return $scope_error;
		}

		$to_remove = array_unique( array_map( 'trim', array_map( 'strval', isset( $input['ips'] ) ? (array) $input['ips'] : array() ) ) );
		$entries   = self::get_saved_ips( $network );
		$kept      = array();
		$removed   = array();

		foreach ( $entries as $entry ) {
			if ( in_array( $entry['ip'], $to_remove, true ) ) {
				$removed[] = $entry['ip'];
			} else {
				$kept[] = $entry;
			}
		}

		if ( ! empty( $removed ) ) {
			self::save_ips( $kept, $network );
		}

		return array(
			'removed'   => $removed,
			'not_found' => array_values( array_diff( $to_remove, $removed ) ),
			'ips'       => self::get_ip_list( $network ),
		);
	}

	/**
	 * Execute: set the network mode.
	 *
	 * @param mixed $input Ability input.
	 * @return array|WP_Error
	 */
	public static function execute_set_network_mode( $input = null ) {
		if ( '' !== Restricted_Site_Access::get_config_network_mode() ) {
			return new WP_Error(
				'rsa_network_mode_locked',
				__( 'The network mode is fixed by the RSA_NETWORK_MODE constant and cannot be changed.', 'restricted-site-access' ),
				array( 'status' => 409 )
			);
		}

		$mode = is_array( $input ) && isset( $input['mode'] ) ? (string) $input['mode'] : '';

		if ( ! in_array( $mode, array( 'default', 'enforce' ), true ) ) {
			return new WP_Error(
				'rsa_invalid_network_mode',
				__( 'The mode must be "default" or "enforce".', 'restricted-site-access' ),
				array( 'status' => 400 )
			);
		}

		$changed = Restricted_Site_Access::get_network_mode() !== $mode;

		if ( $changed ) {
			update_site_option( 'rsa_mode', $mode );
		}

		return array(
			'network_mode' => $mode,
			'changed'      => $changed,
		);
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
			'network_mode'          => RSA_IS_NETWORK ? Restricted_Site_Access::get_network_mode() : null,
			'overridden_by_network' => ! $network && self::is_site_overridden(),
			'restriction_constant'  => self::get_restriction_constant(),
		);
	}

	/**
	 * Get the IP addresses saved in the settings, with their labels.
	 *
	 * @param bool $network Whether to read the network-wide settings.
	 * @return array List of arrays with `ip` and `label` keys.
	 */
	private static function get_saved_ips( $network ) {
		$options = Restricted_Site_Access::get_options( $network );
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
	 * Get every allowed IP address: saved ones first, then ones from code configuration.
	 *
	 * @param bool $network Whether to read the network-wide settings.
	 * @return array List of arrays with `ip`, `label` and `source` keys.
	 */
	private static function get_ip_list( $network ) {
		$list  = array();
		$saved = array();

		foreach ( self::get_saved_ips( $network ) as $entry ) {
			$entry['source'] = 'settings';
			$list[]          = $entry;
			$saved[]         = $entry['ip'];
		}

		foreach ( Restricted_Site_Access::get_config_ips() as $ip ) {
			if ( ! in_array( $ip, $saved, true ) ) {
				$list[] = array(
					'ip'     => $ip,
					'label'  => '',
					'source' => 'config',
				);
			}
		}

		return $list;
	}

	/**
	 * Save the IP addresses and labels to the settings.
	 *
	 * @param array $entries List of arrays with `ip` and `label` keys.
	 * @param bool  $network Whether to save the network-wide settings.
	 */
	private static function save_ips( $entries, $network ) {
		$options            = Restricted_Site_Access::get_options( $network );
		$options['allowed'] = array_column( $entries, 'ip' );
		$options['comment'] = array_column( $entries, 'label' );

		self::save_options( $options, $network );
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
	 * Error returned when the network enforces its settings on the current site.
	 *
	 * @return WP_Error
	 */
	private static function site_overridden_error() {
		return new WP_Error(
			'rsa_network_enforced',
			__( 'The network enforces its settings on every site, so this site\'s settings cannot be changed. Send "network": true to change the network settings.', 'restricted-site-access' ),
			array( 'status' => 409 )
		);
	}

	/**
	 * Error returned for invalid IP addresses.
	 *
	 * @param array $ips The invalid addresses.
	 * @return WP_Error
	 */
	private static function invalid_ips_error( $ips ) {
		return new WP_Error(
			'rsa_invalid_ip',
			sprintf(
				/* translators: %s: comma-separated list of IP addresses. */
				__( 'These are not valid IP addresses or ranges: %s.', 'restricted-site-access' ),
				implode( ', ', $ips )
			),
			array( 'status' => 400 )
		);
	}
}
