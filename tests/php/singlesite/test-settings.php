<?php

class Restricted_Site_Access_Test_Singlesite_Settings extends WP_UnitTestCase {

	public function test_singlesite_activation_deactivation() {

		$this->assertSame( 0, validate_plugin( RSA_TEST_PLUGIN_BASENAME ) );

		// Set current site to public.
		update_option( 'blog_public', 1 );

		// Activate for the current site.
		$activated = activate_plugin( RSA_TEST_PLUGIN_BASENAME, '', false );

		$this->assertEmpty( $activated );

		// Now it should be restricted.
		$this->assertSame( 2, absint( get_option( 'blog_public' ) ) );

		// Deactivate the plugin.
		deactivate_plugins( RSA_TEST_PLUGIN_BASENAME, false, false );

		// Site should now be public.
		$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );
	}

	public function test_get_options() {

		$rsa = Restricted_Site_Access::get_instance();
		$options = $rsa::get_options( false );

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
