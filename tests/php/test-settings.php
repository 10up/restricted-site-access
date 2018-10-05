<?php

class Restricted_Site_Access_Test_Settings extends WP_UnitTestCase {

	public function test_activation_deactivation() {

		if ( is_multisite() ) {
			// Public by default.
			$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );

			// Activate for the current site.
			do_action( 'activate_' . RSA_TEST_PLUGIN_BASENAME );

			// Current site should be restricted.
			$this->assertSame( 2, absint( get_option( 'blog_public' ) ) );

			// Deactivate the plugin.
			do_action( 'deactivate_' . RSA_TEST_PLUGIN_BASENAME );

			// Blog should now be public.
			$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );

			// Now network-activate the plugin.
			do_action( 'activate_' . RSA_TEST_PLUGIN_BASENAME, true);

			// Current site should still be public.
			$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );

			// Set the current site to restricted.
			update_option( 'blog_public', '2' );

			// Now network deactivate the plugin.
			do_action( 'deactivate_' . RSA_TEST_PLUGIN_BASENAME, true );

			// Site should now be public.
			$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );
		} else {

			// Public by default.
			$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );

			do_action( 'activate_' . RSA_TEST_PLUGIN_BASENAME );

			// Now it should be restricted.
			$this->assertSame( 2, absint( get_option( 'blog_public' ) ) );

			// Deactivate the plugin.
			do_action( 'deactivate_' . RSA_TEST_PLUGIN_BASENAME );

			// Site should now be public.
			$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );
		}
	}
}
