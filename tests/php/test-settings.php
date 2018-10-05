<?php

class Restricted_Site_Access_Test_Settings extends WP_UnitTestCase {

	public function test_activation() {

		$rsa = Restricted_Site_Access::get_instance();

		$blog_public = get_option( 'blog_public' );

		// Public by default.
		$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );

		do_action( 'activate_' . RSA_TEST_PLUGIN_BASENAME );

		// Now it should be restricted.
		$this->assertSame( 2, absint( get_option( 'blog_public' ) ) );

		// Reset it.
		update_option( 'blog_public', '1' );
	}

}
