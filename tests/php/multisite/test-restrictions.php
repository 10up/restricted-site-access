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
}
