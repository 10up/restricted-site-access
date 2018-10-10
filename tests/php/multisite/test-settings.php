<?php

class Restricted_Site_Access_Test_Multisite_Settings extends WP_UnitTestCase {

	public function test_multisite_activation_deactivation() {

		// Set current site to public.
		update_option( 'blog_public', 1 );

		// Activate for the current site.
		$activated = activate_plugin( RSA_TEST_PLUGIN_BASENAME, '', false );

		$this->assertEmpty( $activated );

		// Current site should be restricted.
		$this->assertSame( 2, absint( get_option( 'blog_public' ) ) );

		// Deactivate the plugin.
		deactivate_plugins( RSA_TEST_PLUGIN_BASENAME, false, false );

		// Blog should now be public.
		$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );

		// Now network-activate the plugin.
		activate_plugin( RSA_TEST_PLUGIN_BASENAME, '', true );

		// Current site should still be public.
		$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );

		// Set the current site to restricted.
		update_option( 'blog_public', '2' );

		// Now network deactivate the plugin.
		deactivate_plugins( RSA_TEST_PLUGIN_BASENAME, false, true );

		// Site should now be public.
		$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );
	}

	public function test_get_options() {

		$rsa = Restricted_Site_Access::get_instance();
		$options = $rsa::get_options( true );

		$defaults = array(
			'approach'      =>  1,
			'message'       => _x( 'Access to this site is restricted.', 'default restriction message', 'restricted-site-access' ),
			'redirect_url'  => '',
			'redirect_path'	=> 0,
			'head_code'     => 302,
			'page'          => 0,
			'allowed'       => array(),
		);

		$this->assertSame( $defaults, $options );
	}
}
