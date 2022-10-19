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

	public function test_multisite_uninstall() {

		update_site_option( 'blog_public', 2 );
		update_site_option( 'rsa_options', array() );
		update_site_option( 'rsa_mode', 'enforce' );

		$sites = get_sites();

		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );

			update_option( 'blog_public', 2 );
			update_option( 'rsa_options', array() );

			restore_current_blog();
		}

		restricted_site_access_uninstall();

		$this->assertFalse( get_site_option( 'blog_mode' ) );
		$this->assertFalse( get_site_option( 'rsa_options' ) );
		$this->assertFalse( get_site_option( 'rsa_mode' ) );

		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );

			$this->assertSame( 1, absint( get_option( 'blog_public' ) ) );
			$this->assertFalse( get_option( 'rsa_options' ) );

			restore_current_blog();
		}
	}

	public function test_multisite_save_network_settings() {

		$rsa = Restricted_Site_Access::get_instance();

		delete_site_option( 'rsa_mode' );
		delete_site_option( 'blog_public' );
		delete_site_option( 'rsa_options' );

		$rsa::save_network_settings();

		$this->assertFalse( get_site_option( 'rsa_mode' ) );
		$this->assertFalse( get_site_option( 'blog_public' ) );
		$this->assertFalse( get_site_option( 'rsa_options' ) );

		// Setup the $_POST variable.
		$_POST['rsa_mode']    = ' enforce';
		$_POST['blog_public'] = 2;
		$_POST['rsa_options'] = array(
			'approach'     => 99,
			'message'      => 'Hello world<script>',
			'head_code'    => 404,
			'redirect_url' => 'https://10up.com',
			'allowed'      => array(
				'127.0.0.1',
			),
		);

		$rsa::save_network_settings();

		$this->assertSame( 'enforce', get_site_option( 'rsa_mode' ) );
		$this->assertSame( 2, absint( get_site_option( 'blog_public' ) ) );

		$options = $rsa::get_options( true );

		$this->assertNotEmpty( $options );
		$this->assertNotEmpty( $options['allowed'] );
		$this->assertSame( 1, $options['approach'] );
		$this->assertSame( 'Hello world', $options['message'] );
		$this->assertSame( 302, $options['head_code'] );
		$this->assertSame( 'https://10up.com', $options['redirect_url'] );
		$this->assertSame( 0, $options['page'] );
		$this->assertContains( '127.0.0.1', $options['allowed'] );

	}

	public function test_set_defaults() {

		$rsa = Restricted_Site_Access::get_instance();

		update_site_option( 'blog_public', 2 );

		update_site_option( 'blog_public', 2 );

		foreach ( get_sites() as $site ) {

			switch_to_blog( $site->blog_id );

			delete_option( 'rsa_options' );
			delete_option( 'blog_public' );

			restore_current_blog();

			$rsa::set_defaults( $site->blog_id, null, null, null, null, null );

			switch_to_blog( $site->blog_id );

			$this->assertSame( 2, absint( get_option( 'blog_public' ) ) );

			$options = get_option( 'rsa_options' );
			$this->assertNotEmpty( $options );

			$this->assertSame( 1, $options['approach'] );
			$this->assertSame( 'Access to this site is restricted.', $options['message'] );
			$this->assertSame( 302, $options['head_code'] );
			$this->assertSame( '', $options['redirect_url'] );
			$this->assertSame( 0, $options['page'] );
			$this->assertEmpty( $options['allowed'] );

			restore_current_blog();
		}

		// No options will be set when enforce is turned on.
		update_site_option( 'rsa_mode', 'enforce' );

		foreach ( get_sites() as $site ) {

			switch_to_blog( $site->blog_id );

			delete_option( 'rsa_options' );
			delete_option( 'blog_public' );

			restore_current_blog();

			$rsa::set_defaults( $site->blog_id, null, null, null, null, null );

			switch_to_blog( $site->blog_id );

			$this->assertSame( 2, get_option( 'blog_public' ) );
			$this->assertFalse( get_option( 'rsa_options' ) );

			restore_current_blog();
		}
	}
}
