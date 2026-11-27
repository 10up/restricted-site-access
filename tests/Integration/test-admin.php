<?php

class Restricted_Site_Access_Test_Admin extends WP_UnitTestCase {

	/**
	 * Boolean that shows if RSA instance is loaded.
	 *
	 * @var boolean
	 */
	private $init_done = false;

	public function run_admin_init() {
		if ( ! $this->init_done ) {
			$rsa = Restricted_Site_Access::get_instance();
			$rsa::admin_init();
			$this->init_done = true;
		}
	}

	public function test_admin_init() {
		global $wp_settings_sections, $wp_settings_fields;

		$settings_page = 'reading';

		$rsa = Restricted_Site_Access::get_instance();
		$this->run_admin_init();

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

	public function test_load_options_page() {
		$rsa = Restricted_Site_Access::get_instance();

		$options             = $rsa::get_options( false );
		$options['approach'] = 4;

		update_option( 'rsa_options', $options );

		$rsa::load_options_page();

		// Run tests for specific fields that aren't covered in the
		// multisite tests.
		ob_start();
		$rsa::settings_field_handling();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'id="rsa-unblocked-page" name="rsa_options[approach]" type="radio" value="4"  checked=\'checked\' />', $html );
	}

	public function test_admin_notice() {
		$rsa = Restricted_Site_Access::get_instance();

		$options             = $rsa::get_options( false );
		$options['approach'] = 0;

		update_option( 'rsa_options', $options );

		$rsa::load_options_page();

		ob_start();
		$rsa::admin_notice();
		$html = ob_get_clean();

		$this->assertEmpty( $html );

		$options             = $rsa::get_options( false );
		$options['approach'] = 4;
		$options['page']     = 0;

		update_option( 'rsa_options', $options );

		$rsa::load_options_page();

		ob_start();
		$rsa::admin_notice();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'Please select the page you want to show restricted visitors. If no page is selected, WordPress will simply show a general restriction message.', $html );

		$options                 = $rsa::get_options( false );
		$options['approach']     = 2;
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

	public function test_settings_field_rsa_page() {
		$rsa = Restricted_Site_Access::get_instance();

		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'test_settings_field_rsa_page',
				'post_status' => 'publish',
			)
		);

		$options         = $rsa::get_options( false );
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

	/**
	 * Test admin bar hiding functionality.
	 */
	public function test_hide_admin_bar_for_roles() {
		$rsa = Restricted_Site_Access::get_instance();

		// Test with no user logged in - should return original value.
		$this->assertTrue( $rsa::hide_admin_bar_for_roles( true ) );
		$this->assertFalse( $rsa::hide_admin_bar_for_roles( false ) );

		// Create a test user with subscriber role.
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Test with no roles configured to hide admin bar.
		$options                         = $rsa::get_options( false );
		$options['hide_admin_bar_roles'] = array();
		update_option( 'rsa_options', $options );

		$this->assertTrue( $rsa::hide_admin_bar_for_roles( true ) );
		$this->assertFalse( $rsa::hide_admin_bar_for_roles( false ) );

		// Test with subscriber role configured to hide admin bar.
		$options['hide_admin_bar_roles'] = array( 'subscriber' );
		update_option( 'rsa_options', $options );

		$this->assertFalse( $rsa::hide_admin_bar_for_roles( true ) );
		$this->assertFalse( $rsa::hide_admin_bar_for_roles( false ) );

		// Test with multiple roles, including subscriber.
		$options['hide_admin_bar_roles'] = array( 'subscriber', 'contributor' );
		update_option( 'rsa_options', $options );

		$this->assertFalse( $rsa::hide_admin_bar_for_roles( true ) );

		// Test with different role (should not hide).
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user_id );

		$this->assertTrue( $rsa::hide_admin_bar_for_roles( true ) );
		$this->assertFalse( $rsa::hide_admin_bar_for_roles( false ) );

		// Test in admin area (should not hide).
		set_current_screen( 'edit' );
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$options['hide_admin_bar_roles'] = array( 'subscriber' );
		update_option( 'rsa_options', $options );

		$this->assertTrue( $rsa::hide_admin_bar_for_roles( true ) );
		$this->assertFalse( $rsa::hide_admin_bar_for_roles( false ) );

		// Reset screen.
		set_current_screen( 'front' );
	}

	/**
	 * Test admin bar hiding settings field.
	 */
	public function test_settings_field_hide_admin_bar_roles() {
		$rsa = Restricted_Site_Access::get_instance();

		// Set up options with some roles selected.
		$options                         = $rsa::get_options( false );
		$options['hide_admin_bar_roles'] = array( 'subscriber', 'contributor' );
		update_option( 'rsa_options', $options );

		$rsa::load_options_page();

		ob_start();
		$rsa::settings_field_hide_admin_bar_roles();
		$html = ob_get_clean();

		// Check that the field contains expected elements.
		$this->assertStringContainsString( 'name="rsa_options[hide_admin_bar_roles][]" value="subscriber"  checked=\'checked\'', $html );
		$this->assertStringContainsString( 'name="rsa_options[hide_admin_bar_roles][]" value="contributor"  checked=\'checked\'', $html );
		$this->assertStringContainsString( 'Hide admin bar for roles', $html );
		$this->assertStringContainsString( 'Select user roles for which the WordPress admin bar should be hidden on the frontend', $html );
	}

	/**
	 * Test admin bar hiding with multiple user roles.
	 */
	public function test_hide_admin_bar_for_roles_multiple_roles() {
		$rsa = Restricted_Site_Access::get_instance();

		// Create a user with multiple roles.
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$user    = get_user_by( 'id', $user_id );
		$user->add_role( 'contributor' );
		wp_set_current_user( $user_id );

		// Configure to hide admin bar for subscriber role.
		$options                         = $rsa::get_options( false );
		$options['hide_admin_bar_roles'] = array( 'subscriber' );
		update_option( 'rsa_options', $options );

		// Should hide admin bar because user has subscriber role.
		$this->assertFalse( $rsa::hide_admin_bar_for_roles( true ) );

		// Configure to hide admin bar for contributor role.
		$options['hide_admin_bar_roles'] = array( 'contributor' );
		update_option( 'rsa_options', $options );

		// Should hide admin bar because user has contributor role.
		$this->assertFalse( $rsa::hide_admin_bar_for_roles( true ) );

		// Configure to hide admin bar for author role (user doesn't have this role).
		$options['hide_admin_bar_roles'] = array( 'author' );
		update_option( 'rsa_options', $options );

		// Should not hide admin bar because user doesn't have author role.
		$this->assertTrue( $rsa::hide_admin_bar_for_roles( true ) );
	}

	/**
	 * Test admin bar hiding field in network settings.
	 */
	public function test_network_settings_admin_bar_hiding() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'This test requires multisite.' );
		}

		$rsa = Restricted_Site_Access::get_instance();

		// Set up network options.
		$network_options                         = $rsa::get_options( true );
		$network_options['hide_admin_bar_roles'] = array( 'subscriber' );
		update_site_option( 'rsa_options', $network_options );

		$rsa::load_network_settings_page();

		ob_start();
		$rsa::settings_field_hide_admin_bar_roles();
		$html = ob_get_clean();

		// Check that the field contains expected elements.
		$this->assertStringContainsString( 'name="rsa_options[hide_admin_bar_roles][]"', $html );
		$this->assertStringContainsString( 'value="subscriber"', $html );
		$this->assertStringContainsString( 'checked=\'checked\'', $html );
	}
}
