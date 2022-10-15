<?php

class Restricted_Site_Access_Test_Actions extends WP_UnitTestCase {

	public function test_actions() {

		$rsa = Restricted_Site_Access::get_instance();
		$rsa::add_actions();

		$this->assertSame( 1, has_action( 'parse_request', array( 'Restricted_Site_Access', 'restrict_access' ) ) );
		$this->assertSame( 1, has_action( 'admin_init', array( 'Restricted_Site_Access', 'admin_init' ) ) );
		$this->assertSame( 10, has_action( 'wp_ajax_rsa_ip_check', array( 'Restricted_Site_Access', 'ajax_rsa_ip_check' ) ) );
		$this->assertSame( 10, has_action( 'activate_' . RSA_TEST_PLUGIN_BASENAME, array( 'Restricted_Site_Access', 'activation' ) ) );
		$this->assertSame( 10, has_action( 'deactivate_' . RSA_TEST_PLUGIN_BASENAME, array( 'Restricted_Site_Access', 'deactivation' ) ) );
		$this->assertSame( 10, has_action( 'wpmu_new_blog', array( 'Restricted_Site_Access', 'set_defaults' ) ) );
		$this->assertSame( 10, has_action( 'admin_enqueue_scripts', array( 'Restricted_Site_Access', 'enqueue_admin_script' ) ) );
		$this->assertSame( 10, has_action( 'wp_ajax_rsa_notice_dismiss', array( 'Restricted_Site_Access', 'ajax_notice_dismiss' ) ) );
	}
}
