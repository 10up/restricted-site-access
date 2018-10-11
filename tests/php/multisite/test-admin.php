<?php

class Restricted_Site_Access_Test_Multisite_Admin extends WP_UnitTestCase {

	public function test_admin_init() {

		$rsa = Restricted_Site_Access::get_instance();
		$rsa::admin_init();

		$this->assertSame( 10, has_action( 'load-settings.php', [ 'Restricted_Site_Access', 'load_network_settings_page' ] ) );
		$this->assertSame( 10, has_action( 'network_admin_notices', [ 'Restricted_Site_Access', 'page_cache_notice' ] ) );
	}
}
