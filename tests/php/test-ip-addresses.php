<?php

class Restricted_Site_Access_Test_IP_Addresses extends WP_UnitTestCase {

	public function test_is_ip() {

		$rsa = Restricted_Site_Access::get_instance();

		$valid_ips = array(
			'127.0.0.1',
			'192.168.0.1',
			'192.168.5.2/16',
			'435:23f::45:23/101',
			'2a02:6b8::/32',
		);

		$invalid_ips = array(
			'',
			'bad',
			'/',
			'0.42.42.42',
			'999.888.777.666',
			'198.51.100.0/24',
			'203.0.113.0/24',
			'203.0.113.0/ten',
			'192.0.2.0/33',
			'2001:800::/129',
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

	public function test_ip_in_range() {

		$rsa = Restricted_Site_Access::get_instance();

		$this->assertTrue( $rsa::ip_in_range( '127.0.0.1', '127.0.0.0/24' ) );
		$this->assertTrue( $rsa::ip_in_range( '127.0.0.1', '127.0.0.1/32' ) );
		$this->assertTrue( $rsa::ip_in_range( '127.0.0.1', '127.0.0.1' ) );
		$this->assertFalse( $rsa::ip_in_range( '192.168.1.1', '127.0.0.0/24' ) );
	}

	public function test_get_client_ip_address() {

		$rsa = Restricted_Site_Access::get_instance();

		$headers = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach( $headers as $header ) {
			$_SERVER[ $header ] = '127.0.0.1';
			$this->assertSame( '127.0.0.1', $rsa::get_client_ip_address() );
			unset( $_SERVER[ $header ] );
		}
	}
}
