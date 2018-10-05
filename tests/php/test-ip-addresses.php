<?php

class Restricted_Site_Access_Test_IP_Addresses extends WP_UnitTestCase {

	public function test_is_ip() {

		$rsa = Restricted_Site_Access::get_instance();

		$valid_ips = array(
			'127.0.0.1',
			'192.168.0.1',
			'192.168.5.2/16',
		);

		$invalid_ips = array(
			'bad',
			'0.42.42.42',
			'999.888.777.666',
			'198.51.100.0/24',
			'203.0.113.0/24',
		);

		foreach ( $valid_ips as $valid ) {
			$this->assertTrue( $rsa::is_ip( $valid ), $valid . ' was not valid.' );
		}

		foreach ( $invalid_ips as $invalid ) {

			try {
				$this->assertFalse( $rsa::is_ip( $invalid ), $invalid . ' was valid.' );
			} catch ( \Exception $e ) {
				$this->assertNotEmpty( $e );
			}
		}
	}
}
