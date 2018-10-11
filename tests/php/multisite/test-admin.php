<?php

class Restricted_Site_Access_Test_Multisite_Admin extends WP_UnitTestCase {

	private $_init_done = false;

	public function run_admin_init() {
		if ( ! $this->_init_done ) {
			$rsa = Restricted_Site_Access::get_instance();
			$rsa::admin_init();
			$this->_init_done = true;
		}
	}

	public function test_multisite_admin_init() {

		$rsa = Restricted_Site_Access::get_instance();
		$this->run_admin_init();

		$this->assertSame( 10, has_action( 'load-settings.php', [ 'Restricted_Site_Access', 'load_network_settings_page' ] ) );
		$this->assertSame( 10, has_action( 'network_admin_notices', [ 'Restricted_Site_Access', 'page_cache_notice' ] ) );
	}

	public function test_multisite_load_network_settings_page() {

		$rsa = Restricted_Site_Access::get_instance();
		$this->run_admin_init();

		update_site_option( 'blog_public', 2 );
		update_site_option( 'rsa_mode', 'enforce' );

		$options = $rsa::get_options( true );

		$options['approach'] = 2;
		$options['message'] = '';
		$options['head_code'] = 0;
		$options['redirect_url'] = 'https://10up.com';
		$options['redirect_path'] = 1;
		$options['allowed'] = [
			'127.0.0.1',
		];

		update_site_option( 'rsa_options', $options );

		$rsa::load_network_settings_page();

		$this->assertSame( 10, has_action( 'wpmu_options', [ 'Restricted_Site_Access', 'show_network_settings' ] ) );
		$this->assertSame( 10, has_action( 'update_wpmu_options', [ 'Restricted_Site_Access', 'save_network_settings' ] ) );

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

		// Run the tests in show_network_settings() since the load_network_settings_page()
		// function is needed to reload settings.
		ob_start();
		$rsa::show_network_settings();
		$html = ob_get_clean();

		$this->assertContains( 'value="default" />', $html );
		$this->assertContains( 'value="enforce" checked=\'checked\' />', $html );

		$this->assertContains( 'name="blog_public" value="1" >', $html );
		$this->assertContains( 'name="blog_public" value="2"  checked=\'checked\'>', $html );

		$this->assertContains( 'id="rsa-send-to-login" name="rsa_options[approach]" type="radio" value="1"  />', $html );
		$this->assertContains( 'id="rsa-redirect-visitor" name="rsa_options[approach]" type="radio" value="2"  checked=\'checked\' />', $html );
		$this->assertContains( 'id="rsa-display-message" name="rsa_options[approach]" type="radio" value="3"  />', $html );

		$this->assertContains( 'name="rsa_options[redirect_url]" id="redirect" class="rsa_redirect_field regular-text" value="https://10up.com" />', $html );

		$this->assertContains( 'value="301" >301 Permanent', $html );
		$this->assertContains( 'value="302"  selected=\'selected\'>302 Undefined', $html );
		$this->assertContains( 'value="307" >307 Temporary', $html );

		$this->assertContains( 'name="rsa_options[redirect_path]" value="1" id="redirect_path" class="rsa_redirect_field"  checked=\'checked\' />', $html );

		$this->assertContains( 'name="rsa_options[message]" id="rsa_message">Access to this site is restricted.', $html );

		$this->assertContains( 'type="text" name="rsa_options[allowed][]" value="127.0.0.1" readonly="true"', $html );
		$this->assertContains( 'id="rsa_myip" value="Add My Current IP Address" style="margin-top: 5px;" data-myip="127.0.0.1" />', $html );

		// Now check for an empty site option.
		delete_site_option( 'blog_public' );

		ob_start();
		$rsa::show_network_settings();
		$html = ob_get_clean();

		$this->assertContains( 'name="blog_public" value="1"  checked=\'checked\'>', $html );
	}

	public function test_multisite_admin_notice() {
		$rsa = Restricted_Site_Access::get_instance();

		update_site_option( 'rsa_mode', 'enforce' );

		ob_start();
		$rsa::admin_notice();
		$html = ob_get_clean();

		$this->assertContains( 'Network visibility settings are currently enforced across all blogs on the network.', $html );
	}

	public function test_multisite_page_cache_notice() {
		$rsa = Restricted_Site_Access::get_instance();

		add_filter( 'restricted_site_access_show_page_cache_notice', '__return_true' );

		update_site_option( 'rsa_hide_page_cache_notice', false );

		ob_start();
		$rsa::page_cache_notice();
		$html = ob_get_clean();

		$this->assertContains( 'Page caching appears to be enabled. Restricted Site Access may not work as expected', $html );

		$rsa::ajax_notice_dismiss();

		ob_start();
		$rsa::page_cache_notice();
		$html = ob_get_clean();

		$this->assertEmpty( $html );

		remove_filter( 'restricted_site_access_show_page_cache_notice', '__return_true' );
	}
}
