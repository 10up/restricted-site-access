<?php
/**
 * Tests for network-level settings when the plugin is network activated.
 *
 * @package Restricted_Site_Access
 */

/**
 * In "enforce" mode the network settings apply to every site and a site's own
 * settings are ignored; in "default" mode each site uses its own settings, and
 * new sites start from the network settings.
 *
 * Runs in the `network` testsuite only (npm run test:php:network).
 */
class Restricted_Site_Access_Test_Network_Enforcement extends WP_UnitTestCase {

	/**
	 * The login URL a restricted visitor to the home page is sent to.
	 *
	 * @return string
	 */
	private function login_url() {
		return add_query_arg( 'redirect_to', rawurlencode( '/' ), home_url( 'wp-login.php' ) );
	}

	/**
	 * Run the restriction check for the home page as a logged-out visitor.
	 *
	 * @return array|null
	 */
	private function check_home_page() {
		$this->go_to( home_url( '/' ) );

		return Restricted_Site_Access::restrict_access_check( $GLOBALS['wp'] );
	}

	/**
	 * Under enforcement a site's own visibility and handling settings are ignored.
	 */
	public function test_enforce_mode_ignores_site_settings() {
		update_site_option( 'rsa_mode', 'enforce' );
		update_site_option( 'blog_public', 2 );
		update_site_option( 'rsa_options', array_merge( Restricted_Site_Access::get_options( true ), array( 'approach' => 1 ) ) );

		// The site asks to be public and to show a message instead.
		update_option( 'blog_public', 1 );
		update_option( 'rsa_options', array_merge( Restricted_Site_Access::get_options( false ), array( 'approach' => 3 ) ) );

		$this->assertSame( 2, (int) get_option( 'blog_public' ) );

		$results = $this->check_home_page();

		$this->assertSame( 302, $results['code'] );
		$this->assertSame( $this->login_url(), $results['url'] );
	}

	/**
	 * Under enforcement the network's admin bar roles apply, not the site's.
	 */
	public function test_enforce_mode_uses_network_admin_bar_roles() {
		update_site_option( 'rsa_mode', 'enforce' );
		update_site_option( 'rsa_options', array_merge( Restricted_Site_Access::get_options( true ), array( 'hide_admin_bar_roles' => array( 'subscriber' ) ) ) );
		update_option( 'rsa_options', array_merge( Restricted_Site_Access::get_options( false ), array( 'hide_admin_bar_roles' => array() ) ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->assertFalse( Restricted_Site_Access::hide_admin_bar_for_roles( true ) );
	}

	/**
	 * In default mode each site uses its own visibility and handling settings.
	 */
	public function test_default_mode_uses_site_settings() {
		update_site_option( 'rsa_mode', 'default' );
		update_site_option( 'blog_public', 2 );
		update_site_option( 'rsa_options', array_merge( Restricted_Site_Access::get_options( true ), array( 'approach' => 1 ) ) );

		update_option( 'blog_public', 1 );

		$this->assertEmpty( $this->check_home_page() );

		update_option( 'blog_public', 2 );
		update_option(
			'rsa_options',
			array_merge(
				Restricted_Site_Access::get_options( false ),
				array(
					'approach' => 3,
					'message'  => 'Site message.',
				)
			)
		);

		$results = $this->check_home_page();

		$this->assertSame( 403, $results['die_code'] );
		$this->assertStringContainsString( 'Site message.', $results['die_message'] );
	}

	/**
	 * A site created in default mode starts with the network's settings.
	 */
	public function test_new_site_starts_with_network_settings_in_default_mode() {
		update_site_option( 'rsa_mode', 'default' );
		update_site_option( 'blog_public', 2 );
		update_site_option(
			'rsa_options',
			array_merge(
				Restricted_Site_Access::get_options( true ),
				array(
					'approach' => 3,
					'message'  => 'Network message.',
				)
			)
		);

		$blog_id = self::factory()->blog->create();

		switch_to_blog( $blog_id );
		$blog_public = (int) get_option( 'blog_public' );
		$options     = get_option( 'rsa_options' );
		$version     = get_option( 'rsa_activation_version' );
		restore_current_blog();

		$this->assertSame( 2, $blog_public );
		$this->assertSame( 3, $options['approach'] );
		$this->assertSame( 'Network message.', $options['message'] );
		$this->assertSame( RSA_VERSION, $version );
	}

	/**
	 * Network deactivation lifts the restriction on every site that had it.
	 */
	public function test_network_deactivation_lifts_restriction_on_every_site() {
		$blog_ids = array( get_current_blog_id(), self::factory()->blog->create(), self::factory()->blog->create() );

		foreach ( $blog_ids as $blog_id ) {
			update_blog_option( $blog_id, 'blog_public', 2 );
		}

		// A site set to discourage search engines keeps that setting.
		update_blog_option( $blog_ids[2], 'blog_public', 0 );

		Restricted_Site_Access::deactivation( true );

		$this->assertSame( 1, (int) get_blog_option( $blog_ids[0], 'blog_public' ) );
		$this->assertSame( 1, (int) get_blog_option( $blog_ids[1], 'blog_public' ) );
		$this->assertSame( 0, (int) get_blog_option( $blog_ids[2], 'blog_public' ) );
	}

	/**
	 * Network activation stores the plugin version on each site that was never configured.
	 */
	public function test_network_activation_records_version_on_unconfigured_sites() {
		$unconfigured = self::factory()->blog->create();
		$configured   = self::factory()->blog->create();

		delete_blog_option( $unconfigured, 'rsa_activation_version' );
		delete_blog_option( $unconfigured, 'rsa_options' );
		delete_blog_option( $configured, 'rsa_activation_version' );
		update_blog_option( $configured, 'rsa_options', array( 'approach' => 1 ) );

		Restricted_Site_Access::activation( true );

		$this->assertSame( RSA_VERSION, get_blog_option( $unconfigured, 'rsa_activation_version' ) );
		$this->assertFalse( get_blog_option( $configured, 'rsa_activation_version' ) );
	}
}
