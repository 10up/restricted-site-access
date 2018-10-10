<?php

class Restricted_Site_Access_Test_Multisite_Settings extends WP_UnitTestCase {

	public function test_multisite_activation_deactivation() {

		$rsa = Restricted_Site_Access::get_instance();

		// Set current site to public.
		update_option( 'blog_public', 1 );

		// Activate for the current site.
		$activated = activate_plugin( RSA_TEST_PLUGIN_BASENAME, '', false );

		$this->assertEmpty( $activated );

		$this->assertFalse( $rsa::is_network( RSA_TEST_PLUGIN_BASENAME ) );

		// Current site should be restricted.
		$this->assertSame( 2, absint( get_option( 'blog_public' ) ) );

		// Deactivate the plugin.
		deactivate_plugins( RSA_TEST_PLUGIN_BASENAME, false, false );

		// Blog should now be public.
		$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );

		// Now network-activate the plugin.
		activate_plugin( RSA_TEST_PLUGIN_BASENAME, '', true );

		$this->assertTrue( $rsa::is_network( RSA_TEST_PLUGIN_BASENAME ) );

		// Current site should still be public.
		$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );

		// Set the current site to restricted.
		update_option( 'blog_public', '2' );

		// Now network deactivate the plugin.
		deactivate_plugins( RSA_TEST_PLUGIN_BASENAME, false, true );

		// Site should now be public.
		$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );
	}
}
