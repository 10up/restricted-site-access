<?php
/**
 * Plugin Name: Restricted Site Access E2E Helpers
 * Description: Test-only support plugin for the E2E suite. Sets plugin state for specs and lets them send a client IP. Loaded only by .wp-env.test.json.
 * Version: 0.1.0
 *
 * Lives at tests/e2e-plugins/restricted-site-access-e2e-helpers/restricted-site-access-e2e-helpers.php.
 * Kept out of the release package by the /tests entry in .distignore and .gitattributes.
 *
 * @package RestrictedSiteAccess
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Let specs send the visitor's IP in the X-Forwarded header.
 *
 * Every request from the test runner reaches WordPress from the same Docker
 * gateway address, so specs that check the IP allow list send a per-request
 * client IP in X-Forwarded, which the plugin reads only because this helper
 * lists it as a trusted header.
 */
add_filter(
	'rsa_trusted_headers',
	static function () {
		return array( 'HTTP_X_FORWARDED' );
	}
);

/*
 * Fixture endpoint for state the UI and core REST routes can't reach.
 *
 * Specs call it with requestUtils.rest( { method: 'POST', path: '/rsa-e2e/v1/state', data } ).
 * Every key is optional:
 *
 * - rsa_mode, blog_public: network (site) options.
 * - rsa_options:           network (site) option, merged onto the plugin defaults.
 * - user_role:             role to give the current user on this site.
 * - remove_user_role:      true removes every role the current user has on this site.
 * - add_role:              array( 'role' => slug, 'name' => display name ), adds a role with the read capability.
 * - deactivate:            true network-deactivates Restricted Site Access.
 */
add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'rsa-e2e/v1',
			'/state',
			array(
				'methods'             => 'POST',
				'permission_callback' => static function () {
					return current_user_can( 'manage_network_options' );
				},
				'callback'            => static function ( WP_REST_Request $request ) {
					if ( null !== $request->get_param( 'rsa_mode' ) ) {
						update_site_option( 'rsa_mode', sanitize_key( $request->get_param( 'rsa_mode' ) ) );
					}

					if ( null !== $request->get_param( 'blog_public' ) ) {
						update_site_option( 'blog_public', (string) absint( $request->get_param( 'blog_public' ) ) );
					}

					if ( is_array( $request->get_param( 'rsa_options' ) ) ) {
						$defaults = array(
							'approach'      => 1,
							'message'       => 'Access to this site is restricted.',
							'redirect_path' => 0,
							'head_code'     => 302,
							'redirect_url'  => '',
							'page'          => 0,
							'allowed'       => array(),
							'comment'       => array(),
						);

						update_site_option( 'rsa_options', array_merge( $defaults, $request->get_param( 'rsa_options' ) ) );
					}

					$add_role = $request->get_param( 'add_role' );
					if ( is_array( $add_role ) && ! empty( $add_role['role'] ) ) {
						add_role( sanitize_key( $add_role['role'] ), (string) $add_role['name'], array( 'read' => true ) );
					}

					if ( null !== $request->get_param( 'user_role' ) ) {
						$user = wp_get_current_user();
						$user->set_role( sanitize_key( $request->get_param( 'user_role' ) ) );
					}

					if ( true === $request->get_param( 'remove_user_role' ) ) {
						delete_user_meta( get_current_user_id(), $GLOBALS['wpdb']->get_blog_prefix() . 'capabilities' );
					}

					if ( true === $request->get_param( 'deactivate' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
						deactivate_plugins( 'restricted-site-access/restricted_site_access.php', false, true );
					}

					return rest_ensure_response( array( 'ok' => true ) );
				},
			)
		);
	}
);
