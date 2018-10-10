<?php

class Restricted_Site_Access_Test_Multisite_Restrictions extends WP_UnitTestCase {

	public function test_multisite_restrict_access_not_restricted() {

		$rsa = Restricted_Site_Access::get_instance();

		$this->assertTrue( RSA_IS_NETWORK );

		// Set network to enforced, but public.
		update_site_option( 'rsa_mode', 'enforce' );
		update_site_option( 'blog_public', 1 );

		// Set the individual site to restricted.
		update_option( 'blog_public', 2 );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		// The network public setting should allow access.
		$this->assertEmpty( $rsa::restrict_access_check( $wp ) );
	}

	public function test_multiesite_restrict_access_restricted_default() {

		$rsa = Restricted_Site_Access::get_instance();

		$this->assertTrue( RSA_IS_NETWORK );

		// Set network to enforced and restricted.
		update_site_option( 'rsa_mode', 'enforce' );
		update_site_option( 'blog_public', 2 );

		// Set the individual site to not restricted.
		update_option( 'blog_public', 1 );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		// The network restricted setting should override the
		// site's public setting.
		$results = $rsa::restrict_access_check( $wp );

		// Check the default results.
		$url = add_query_arg( 'redirect_to', rawurlencode( '/' ), home_url( 'wp-login.php' ) );

		$this->assertNotEmpty( $results );
		$this->assertSame( 302, $results['code'] );
		$this->assertSame( $url, $results['url'] );

	}

	public function test_multisite_restrict_access_restricted_whitelist() {

		$rsa = Restricted_Site_Access::get_instance();

		$this->assertTrue( RSA_IS_NETWORK );

		// Set network to enforced and restricted.
		update_site_option( 'rsa_mode', 'enforce' );
		update_site_option( 'blog_public', 2 );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		// Check the default results.
		$url = add_query_arg( 'redirect_to', rawurlencode( '/' ), home_url( 'wp-login.php' ) );

		$results = $rsa::restrict_access_check( $wp );

		$this->assertNotEmpty( $results );
		$this->assertSame( 302, $results['code'] );
		$this->assertSame( $url, $results['url'] );

		// Add our IP to the allowed list.
		$options = $rsa::get_options( true );
		$options['allowed'][] = '127.0.0.1';

		update_site_option( 'rsa_options', $options );

		// Now set the client IP to one of the whitelisted IPs.
		$_SERVER['HTTP_CLIENT_IP'] = '127.0.0.1';

		$this->assertSame( '127.0.0.1', $rsa::get_client_ip_address() );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		$this->assertEmpty( $results );

		unset( $_SERVER['HTTP_CLIENT_IP'] );

		// Reset the site's whitelist.
		$options['allowed'] = [];
		update_site_option( 'rsa_option', $options );
	}

	public function test_multisite_restrict_access_show_them_a_message() {

		$rsa = Restricted_Site_Access::get_instance();

		$this->assertTrue( RSA_IS_NETWORK );

		// Set network to enforced and restricted.
		update_site_option( 'rsa_mode', 'enforce' );
		update_site_option( 'blog_public', 2 );

		$options = $rsa::get_options( true );
		$options['approach'] = 3; // Show them a message.
		$options['message'] = 'You shall not pass this multisite!';

		update_site_option( 'rsa_options', $options );

		// Go to the home page.
		$this->go_to( home_url( '/' ) );
		$wp = $GLOBALS['wp'];

		$results = $rsa::restrict_access_check( $wp );

		$this->assertNotEmpty( $results );

		$this->assertArrayNotHasKey( 'url', $results );
		$this->assertArrayHasKey( 'die_code', $results );
		$this->assertArrayHasKey( 'die_title', $results );
		$this->assertArrayHasKey( 'die_message', $results );

		$this->assertSame( 403, $results['die_code'] );
		$this->assertSame( get_bloginfo( 'name' ) . ' - Site Access Restricted', $results['die_title'] );
		$this->assertContains( 'You shall not pass this multisite!', $results['die_message'] );
	}

}
