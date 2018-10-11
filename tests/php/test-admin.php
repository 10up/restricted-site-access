<?php

class Restricted_Site_Access_Test_Admin extends WP_UnitTestCase {

	private $_init_done = false;

	public function run_admin_init() {
		if ( ! $this->_init_done ) {
			$rsa = Restricted_Site_Access::get_instance();
			$rsa::admin_init();
			$this->_init_done = true;
		}
	}

	public function test_admin_init() {
		global $wp_settings_sections, $wp_settings_fields;

		$settings_page = 'reading';

		$rsa = Restricted_Site_Access::get_instance();
		$this->run_admin_init();

		$this->assertSame( 10, has_filter( 'privacy_on_link_text', [ 'Restricted_Site_Access', 'privacy_on_link_text' ] ) );
		$this->assertSame( 10, has_filter( 'privacy_on_link_title', [ 'Restricted_Site_Access', 'privacy_on_link_title' ] ) );
		$this->assertSame( 10, has_filter( 'plugin_action_links_' . RSA_TEST_PLUGIN_BASENAME, [ 'Restricted_Site_Access', 'plugin_action_links' ] ) );
		$this->assertSame( 10, has_action( 'load-options-' . $settings_page . '.php', [ 'Restricted_Site_Access', 'load_options_page' ] ) );
		$this->assertSame( 10, has_action( 'blog_privacy_selector', [ 'Restricted_Site_Access', 'blog_privacy_selector' ] ) );
		$this->assertSame( 10, has_action( 'admin_notices', [ 'Restricted_Site_Access', 'page_cache_notice' ] ) );

		$settings = get_registered_settings();

		$this->assertArrayHasKey( 'rsa_options', $settings );

		$this->assertArrayHasKey( $settings_page, $wp_settings_sections );
		$this->assertArrayHasKey( 'restricted-site-access', $wp_settings_sections[ $settings_page ] );

		$this->assertArrayHasKey( $settings_page, $wp_settings_fields );
		$this->assertArrayHasKey( 'restricted-site-access', $wp_settings_fields[ $settings_page ] );
		$this->assertArrayHasKey( 'approach', $wp_settings_fields[ $settings_page ]['restricted-site-access'] );
		$this->assertArrayHasKey( 'message', $wp_settings_fields[ $settings_page ]['restricted-site-access'] );
		$this->assertArrayHasKey( 'redirect_url', $wp_settings_fields[ $settings_page ]['restricted-site-access'] );
		$this->assertArrayHasKey( 'redirect_path', $wp_settings_fields[ $settings_page ]['restricted-site-access'] );
		$this->assertArrayHasKey( 'head_code', $wp_settings_fields[ $settings_page ]['restricted-site-access'] );
		$this->assertArrayHasKey( 'page', $wp_settings_fields[ $settings_page ]['restricted-site-access'] );
		$this->assertArrayHasKey( 'allowed', $wp_settings_fields[ $settings_page ]['restricted-site-access'] );
	}

	public function test_privacy_link_filters() {

		$rsa = Restricted_Site_Access::get_instance();

		// Set blog to public.
		update_option( 'blog_public', 1 );

		$this->assertSame( 'test-link-text', $rsa::privacy_on_link_text( 'test-link-text' ) );
		$this->assertSame( 'test-link-title', $rsa::privacy_on_link_title( 'test-link-title' ) );

		// Set blog to restricted.
		update_option( 'blog_public', 2 );

		$this->assertSame( 'Public access to this site has been restricted.', $rsa::privacy_on_link_text( 'test-link-text' ) );
		$this->assertSame( 'Restricted Site Access plug-in is blocking public access to this site.', $rsa::privacy_on_link_title( 'test-link-title' ) );
	}
}
