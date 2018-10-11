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

	public function test_admin_init() {

		$rsa = Restricted_Site_Access::get_instance();
		$this->run_admin_init();

		$this->assertSame( 10, has_action( 'load-settings.php', [ 'Restricted_Site_Access', 'load_network_settings_page' ] ) );
		$this->assertSame( 10, has_action( 'network_admin_notices', [ 'Restricted_Site_Access', 'page_cache_notice' ] ) );
	}

	public function test_load_network_settings_page() {

		$rsa = Restricted_Site_Access::get_instance();
		$this->run_admin_init();

		$rsa::load_network_settings_page();

		$this->assertSame( 10, has_action( 'wpmu_options', [ 'Restricted_Site_Access', 'show_network_settings' ] ) );
		$this->assertSame( 10, has_action( 'update_wpmu_options', [ 'Restricted_Site_Access', 'save_network_settings' ] ) );
	}

}
