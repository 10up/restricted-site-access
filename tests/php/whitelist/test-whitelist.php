<?php

class Restricted_Site_Access_Test_Whitelist_Settings extends WP_UnitTestCase {

	public function test_get_config_ips() {

		$rsa = Restricted_Site_Access::get_instance();

		$this->assertTrue( defined( 'RSA_IP_WHITELIST' ) );

		$ips = $rsa::get_config_ips();

		$this->assertCount( 2, $ips );
		$this->assertSame( '192.168.1.50', $ips[0] );
		$this->assertSame( '192.168.1.51', $ips[1] );
	}
}
