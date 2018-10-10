<?php

class Restricted_Site_Access_Test_Singlesite_Settings extends WP_UnitTestCase {

	public function test_activation_deactivation() {

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
