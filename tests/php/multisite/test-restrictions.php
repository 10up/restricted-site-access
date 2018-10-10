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
}
