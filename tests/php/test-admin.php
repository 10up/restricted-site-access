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

	public function test_enqueue_admin_script() {

		global $wp_scripts;

		$rsa = Restricted_Site_Access::get_instance();
		$rsa::enqueue_admin_script();

		$this->assertTrue( wp_script_is( 'rsa-admin' ) );

		$data = $wp_scripts->get_data( 'rsa-admin', 'data' );

		$this->assertStringContainsString( 'var rsaAdmin = {"nonce":"' . wp_create_nonce( 'rsa_admin_nonce' ), $data );
	}

	public function test_load_options_page() {
		$rsa = Restricted_Site_Access::get_instance();

		$options = $rsa::get_options( false );
		$options['approach'] = 4;

		update_option( 'rsa_options', $options );

		$rsa::load_options_page();

		$this->assertSame( 10, has_action( 'admin_notices', [ 'Restricted_Site_Access', 'admin_notice' ] ) );
		$this->assertSame( 10, has_action( 'admin_head', [ 'Restricted_Site_Access', 'admin_head' ] ) );
		$this->assertSame( 10, has_filter( 'wp_dropdown_pages', [ 'Restricted_Site_Access', 'filter_page_dropdown' ] ) );

		// Run tests for specific fields that aren't covered in the
		// multisite tests.
		ob_start();
		$rsa::settings_field_handling();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'id="rsa-unblocked-page" name="rsa_options[approach]" type="radio" value="4"  checked=\'checked\' />', $html );
	}

	public function test_filter_page_dropdown() {

		$rsa = Restricted_Site_Access::get_instance();

		$html = $rsa::filter_page_dropdown( 'test', [] );

		$this->assertSame( 'test', $html );

		$html = $rsa::filter_page_dropdown( 'test', [
			'id' => 'not-rsa',
		] );

		$this->assertSame( 'test', $html );

		$html = $rsa::filter_page_dropdown( '', [
			'id' => 'rsa_page',
		] );

		$this->assertSame( '<p class="description" id="rsa_page">No published pages found.</p>', $html );
	}

	public function test_admin_notice() {
		$rsa = Restricted_Site_Access::get_instance();

		$options = $rsa::get_options( false );
		$options['approach'] = 0;

		update_option( 'rsa_options', $options );

		$rsa::load_options_page();

		ob_start();
		$rsa::admin_notice();
		$html = ob_get_clean();

		$this->assertEmpty( $html );

		$options = $rsa::get_options( false );
		$options['approach'] = 4;
		$options['page'] = 0;

		update_option( 'rsa_options', $options );

		$rsa::load_options_page();

		ob_start();
		$rsa::admin_notice();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'Please select the page you want to show restricted visitors. If no page is selected, WordPress will simply show a general restriction message.', $html );

		$options = $rsa::get_options( false );
		$options['approach'] = 2;
		$options['redirect_url'] = '';

		update_option( 'rsa_options', $options );

		$rsa::load_options_page();

		ob_start();
		$rsa::admin_notice();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'Please enter the web address you would like to redirect restricted visitors to. If no address is entered, visitors will be redirected to the login screen.', $html );
	}

	public function test_page_cache_notice() {
		$rsa = Restricted_Site_Access::get_instance();

		add_filter( 'restricted_site_access_show_page_cache_notice', '__return_true' );

		update_option( 'blog_public', 1 );

		ob_start();
		$rsa::page_cache_notice();
		$html = ob_get_clean();

		$this->assertEmpty( $html );

		update_option( 'rsa_hide_page_cache_notice', false );
		update_option( 'blog_public', 2 );

		ob_start();
		$rsa::page_cache_notice();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'Page caching appears to be enabled. Restricted Site Access may not work as expected', $html );

		$rsa::ajax_notice_dismiss();

		ob_start();
		$rsa::page_cache_notice();
		$html = ob_get_clean();

		$this->assertEmpty( $html );

		remove_filter( 'restricted_site_access_show_page_cache_notice', '__return_true' );
	}

	public function test_plugin_action_links() {
		$rsa = Restricted_Site_Access::get_instance();

		$links = $rsa::plugin_action_links( [] );

		$this->assertCount( 1, $links );

		$this->assertSame( '<a href="options-reading.php">Settings</a>', $links[0] );
	}

	public function test_settings_field_rsa_page() {
		$rsa = Restricted_Site_Access::get_instance();

		$page_id = self::factory()->post->create(
			[
				'post_type' => 'page',
				'post_title' => 'test_settings_field_rsa_page',
				'post_status' => 'publish',
			]
		);

		$options = $rsa::get_options( false );
		$options['page'] = $page_id;

		update_option( 'rsa_options', $options );

		$rsa::load_options_page();

		ob_start();
		$rsa::settings_field_rsa_page();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'value="' . $page_id . '" selected="selected"', $html );
	}

	public function test_blog_privacy_selector() {
		$rsa = Restricted_Site_Access::get_instance();

		update_option( 'blog_public', 1 );

		ob_start();
		$rsa::blog_privacy_selector();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'name="blog_public" value="2"  />', $html );

		update_option( 'blog_public', 2 );

		ob_start();
		$rsa::blog_privacy_selector();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'name="blog_public" value="2"  checked=\'checked\' />', $html );
	}

	public function test_admin_head() {

		set_current_screen( 'reading' );

		$rsa = Restricted_Site_Access::get_instance();

		$rsa::admin_head();

		$screen = get_current_screen();
		$tabs = $screen->get_help_tabs();

		$this->assertArrayHasKey( 'restricted-site-access', $tabs );
		$this->assertSame( 'Restricted Site Acccess', $tabs['restricted-site-access']['title'] );

		$content = $tabs['restricted-site-access']['content'];

		$this->assertStringContainsString( '<p><strong>Handle restricted visitors</strong> - Choose the method for handling visitors to your site that are restricted.</p>', $content );
		$this->assertStringContainsString( 'enter a single IP address (for example, 192.168.1.105) or an IP range using a network prefix (for example, 10.0.0.1/24). Enter your addresses carefully!', $content );
		$this->assertStringContainsString( 'The redirection fields are only used when "Handle restricted visitors" is set to "Redirect them to a specified web address".', $content );
		$this->assertStringContainsString( 'The web address of the site you want the visitor redirected to.', $content );
		$this->assertStringContainsString( 'redirect the visitor to the same path (URI) entered at this site.', $content );
		$this->assertStringContainsString( 'Redirect status codes can provide certain visitors, particularly search engines, more information about the nature of the redirect.', $content );
	}
}
