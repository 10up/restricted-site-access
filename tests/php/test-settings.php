<?php

class Restricted_Site_Access_Test_Settings extends WP_UnitTestCase {

	public function test_activation() {

		$blog_public = get_option( 'blog_public' );

		// Public by default.
		$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );

		do_action( 'activate_' . RSA_TEST_PLUGIN_BASENAME );

		// Now it should be restricted.
		$this->assertSame( 2, absint( get_option( 'blog_public' ) ) );

		// Reset it.
		update_option( 'blog_public', '1' );

		// TODO needs to be updated to test multi-site.
	}

	public function test_deactivation() {

		// Set the site to restricted
		update_option( 'blog_public', '2' );


		do_action( 'deactivate_' . RSA_TEST_PLUGIN_BASENAME );

		// Now it should be un-restricted.
		$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );

		// TODO needs to be updated to test multi-site.
	}

}
