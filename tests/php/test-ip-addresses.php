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

	public function test_rsa_trusted_proxies() {
		$rsa = Restricted_Site_Access::get_instance();

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

		// Test that if the REMOTE_ADDR matches our proxy, we return a proper IP.
		add_filter( 'rsa_trusted_proxies', function() {
			return array( '127.0.0.1/24' );
		} );

		$this->assertSame( '127.0.0.1', $rsa::get_client_ip_address() );

		// Test that if the REMOTE_ADDR doesn't match our proxy, we return an empty string.
		add_filter( 'rsa_trusted_proxies', function() {
			return array( '10.0.0.0/8' );
		} );

		$this->assertSame( '', $rsa::get_client_ip_address() );

		// Test if we have multiple proxies and one matches, we return a proper IP.
		add_filter( 'rsa_trusted_proxies', function() {
			return array( '10.0.0.0/8', '127.0.0.1' );
		} );

		$this->assertSame( '127.0.0.1', $rsa::get_client_ip_address() );

		// Reset the filter.
		add_filter( 'rsa_trusted_proxies', '__return_empty_array' );
		unset( $_SERVER['REMOTE_ADDR'] );
	}

	public function test_rsa_trusted_headers() {
		$rsa = Restricted_Site_Access::get_instance();

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

		add_filter( 'rsa_get_client_ip_address_filter_flags', function() {
			return FILTER_FLAG_NO_RES_RANGE;
		} );

		$headers = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
		);

		// Test that each header returns the value we expect.
		foreach( $headers as $header ) {
			$_SERVER[ $header ] = '127.0.0.1';
			$this->assertSame( '127.0.0.1', $rsa::get_ip_from_headers() );
			unset( $_SERVER[ $header ] );
		}

		// Test that if we don't trust any headers, we get the REMOTE_ADDR value.
		$_SERVER['HTTP_CLIENT_IP'] = '10.0.0.0';
		add_filter( 'rsa_trusted_headers', '__return_empty_array' );
		$this->assertSame( '127.0.0.1', $rsa::get_ip_from_headers() );
		unset( $_SERVER['HTTP_CLIENT_IP'] );

		// Test if we trust a single header, we get that value back.
		$_SERVER['HTTP_CLIENT_IP'] = '10.0.0.0';
		add_filter( 'rsa_trusted_headers', function() {
			return array( 'HTTP_CLIENT_IP' );
		} );
		$this->assertSame( '10.0.0.0', $rsa::get_ip_from_headers() );
		unset( $_SERVER['HTTP_CLIENT_IP'] );

		// Test if we trust multiple headers, we get the first matched value back.
		$_SERVER['HTTP_X_FORWARDED'] = '10.0.0.8';
		$_SERVER['HTTP_FORWARDED']   = '10.0.0.0';
		add_filter( 'rsa_trusted_headers', function() use ( $headers ) {
			return $headers;
		} );
		$this->assertSame( '10.0.0.8', $rsa::get_ip_from_headers() );
		unset( $_SERVER['HTTP_X_FORWARDED'] );
		unset( $_SERVER['HTTP_FORWARDED'] );

		// Reset things.
		add_filter( 'rsa_get_client_ip_address_filter_flags', function() {
			return FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
		} );
		unset( $_SERVER['REMOTE_ADDR'] );
	}

}
