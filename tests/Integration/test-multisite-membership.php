<?php
/**
 * Tests for who can see a restricted site on a multisite network.
 *
 * @package Restricted_Site_Access
 */

/**
 * On a network, being logged in is not enough: a visitor needs to be a member
 * of the site with the read capability, or a super admin.
 *
 * @group ms-required
 */
class Restricted_Site_Access_Test_Multisite_Membership extends WP_UnitTestCase {

	/**
	 * Restrict the current site with the default "send to login" handling.
	 */
	public function set_up() {
		parent::set_up();

		if ( ! is_multisite() ) {
			$this->fail( 'This test requires multisite; run it with npm run test:php:multisite.' );
		}

		update_option( 'blog_public', 2 );
	}

	/**
	 * Build a WP request object for a REST API route.
	 *
	 * @return WP
	 */
	private function get_rest_wp() {
		$wp             = new WP();
		$wp->query_vars = array( 'rest_route' => '/wp/v2/posts' );

		return $wp;
	}

	/**
	 * A logged-in user who is not a member of the site is turned away.
	 */
	public function test_logged_in_non_member_is_restricted() {
		// Load the request while logged out: go_to() also runs the plugin's own handling.
		$this->go_to( home_url( '/' ) );

		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		remove_user_from_blog( $user_id, get_current_blog_id() );
		wp_set_current_user( $user_id );

		$results = Restricted_Site_Access::restrict_access_check( $this->get_rest_wp() );

		$this->assertSame( 403, $results['rest_response']['status'] );
		$this->assertSame( 'rest_forbidden', $results['rest_response']['body']['code'] );

		$this->expectException( 'WPDieException' );
		Restricted_Site_Access::restrict_access_check( $GLOBALS['wp'] );
	}

	/**
	 * A member of the site with the read capability can see it.
	 */
	public function test_member_with_read_capability_has_access() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->go_to( home_url( '/' ) );

		$this->assertEmpty( Restricted_Site_Access::restrict_access_check( $GLOBALS['wp'] ) );
		$this->assertEmpty( Restricted_Site_Access::restrict_access_check( $this->get_rest_wp() ) );
	}

	/**
	 * A super admin can see the site without being a member of it.
	 */
	public function test_super_admin_has_access_without_membership() {
		$user_id = self::factory()->user->create();
		remove_user_from_blog( $user_id, get_current_blog_id() );
		grant_super_admin( $user_id );
		wp_set_current_user( $user_id );

		$this->go_to( home_url( '/' ) );

		$this->assertEmpty( Restricted_Site_Access::restrict_access_check( $GLOBALS['wp'] ) );
	}
}
