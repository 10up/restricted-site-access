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

		// IPv4 tests.
		$this->assertTrue( $rsa::ip_in_range( '127.0.0.1', '127.0.0.0/24' ) );
		$this->assertTrue( $rsa::ip_in_range( '127.0.0.1', '127.0.0.1/32' ) );
		$this->assertTrue( $rsa::ip_in_range( '127.0.0.1', '127.0.0.1' ) );
		$this->assertFalse( $rsa::ip_in_range( '192.168.1.1', '127.0.0.0/24' ) );

		// IPv4 single address match.
		$this->assertTrue( $rsa::ip_in_range( '172.10.23.4', '172.10.23.4' ) );

		// IPv4 single address don't match.
		$this->assertFalse( $rsa::ip_in_range( '172.10.23.4', '172.10.23.5' ) );

		// Invalid IPv4 range.
		$this->assertFalse( $rsa::ip_in_range( '172.10.23.4', '172.10.23.4/33' ) );

		// IPv4 in subnet range.
		$this->assertTrue( $rsa::ip_in_range( '172.10.23.4', '172.10.23.4/3' ) );

		// IPv4 in pattern range.
		$this->assertTrue( $rsa::ip_in_range( '172.10.23.4', '172.10.23.*' ) );
		$this->assertTrue( $rsa::ip_in_range( '172.10.23.4', '172.10.*.*' ) );

		// IPv4 not in pattern range.
		$this->assertFalse( $rsa::ip_in_range( '172.10.23.4', '172.10.*.5' ) );

		// IPv4 not in subnet range.
		$this->assertFalse( $rsa::ip_in_range( '172.10.30.48', '172.10.30.40/28' ) );

		// IPv6 single address match.
		$this->assertTrue( $rsa::ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ) );

		// IPv6 single address don't match.
		$this->assertFalse( $rsa::ip_in_range( '2001:0db8:85a3:0000:0000:8a2e:0370:7334', '2001:0db8:85a3:0000:0000:8a2e:0370:7335' ) );

		// IPv6 in subnet range.
		$this->assertTrue( $rsa::ip_in_range( '2001:db8:3333:4444:5555:6666:7777:8888', '2001:0db8:3333:4444:0000:0000:0000:0000/64' ) );
		$this->assertTrue( $rsa::ip_in_range( '2001:db8:3333:4444:5555:6666:7777:8888', '2001:0db8:3333:4444::0000/32' ) );

		// IPv6 in pattern range.
		$this->assertTrue( $rsa::ip_in_range( '2001:db8:3333:4444:5555:6666:7777:8888', '2001:db8:3333:4444:5555:6666:*:*' ) );

		// IPv6 not in subnet range.
		$this->assertFalse( $rsa::ip_in_range( '2001:db8:3333:4445:5555:6666:7777:8888', '2001:0db8:3333:4444:0000:0000:0000:0000/64' ) );
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

		foreach ( $headers as $header ) {
			$_SERVER[ $header ] = '127.0.0.1';
			$this->assertSame( '127.0.0.1', $rsa::get_client_ip_address() );
			unset( $_SERVER[ $header ] );
		}
	}

	/**
	 * Test trusted proxies.
	 *
	 * @dataProvider trusted_proxy_provider
	 *
	 * @param string $remote_ip Remote IP address.
	 * @param array  $proxies Proxies to trust.
	 */
	public function test_rsa_trusted_proxies( string $remote_ip = '', array $proxies = array() ) {
		$rsa = Restricted_Site_Access::get_instance();

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

		add_filter(
			'rsa_trusted_proxies',
			function() use ( $proxies ) {
				return $proxies;
			}
		);

		$this->assertSame( $remote_ip, $rsa::get_client_ip_address() );

		unset( $_SERVER['REMOTE_ADDR'] );
	}

	public function trusted_proxy_provider() {
		/**
		 * Data to use in our trusted proxy tests
		 *
		 * First key is a string containing our REMOTE_ADDR IP.
		 * Second is an array of proxy IP addresses.
		 */
		return array(
			// Test that if the REMOTE_ADDR matches our proxy, we return a proper IP.
			array( '127.0.0.1', array( '127.0.0.1/24' ) ),
			// Test that if the REMOTE_ADDR doesn't match our proxy, we return an empty string.
			array( '', array( '10.0.0.0/8' ) ),
			// Test if we have multiple proxies and one matches, we return a proper IP.
			array( '127.0.0.1', array( '10.0.0.0/8', '127.0.0.1' ) ),
		);
	}

	/**
	 * Test trusted headers
	 *
	 * @dataProvider trusted_headers_provider
	 *
	 * @param string $remote_ip Remote IP address.
	 * @param array  $headers Headers to set.
	 * @param array  $trusted_headers Headers we want to trust.
	 */
	public function test_rsa_trusted_headers( string $remote_ip = '', array $headers = array(), array $trusted_headers = array() ) {
		$rsa = Restricted_Site_Access::get_instance();

		add_filter(
			'rsa_get_client_ip_address_filter_flags',
			function() {
				return FILTER_FLAG_NO_RES_RANGE;
			}
		);

		add_filter(
			'rsa_trusted_headers',
			function() use ( $trusted_headers ) {
				return $trusted_headers;
			}
		);

		foreach ( $headers as $header => $ip ) {
			$_SERVER[ $header ] = $ip;
		}

		$this->assertSame( $remote_ip, $rsa::get_ip_from_headers() );

		foreach ( $headers as $header ) {
			unset( $_SERVER[ $header ] );
		}
	}

	/**
	 * Test custom trusted headers
	 *
	 * @since x.x.x
	 */
	public function test_rsa_custom_trusted_headers() {
		$this->assertNull( Restricted_Site_Access::has_valid_custom_header() );

		add_filter(
			'rsa_custom_trusted_headers',
			function ( $headers ) {
				return array( 'x-custom-header1' => '1234' );
			}
		);
		$this->assertFalse( Restricted_Site_Access::has_valid_custom_header() );

		$_SERVER['x-custom-header2'] = '';
		add_filter(
			'rsa_custom_trusted_headers',
			function ( $headers ) {
				return array( 'x-custom-header2' => '1234' );
			}
		);
		$this->assertFalse( Restricted_Site_Access::has_valid_custom_header() );

		$_SERVER['x-custom-header3'] = '5678';
		add_filter(
			'rsa_custom_trusted_headers',
			function ( $headers ) {
				return array( 'x-custom-header3' => '1234' );
			}
		);
		$this->assertFalse( Restricted_Site_Access::has_valid_custom_header() );

		$_SERVER['x-custom-header4'] = '1234';
		add_filter(
			'rsa_custom_trusted_headers',
			function ( $headers ) {
				return array( 'x-custom-header4' => '1234' );
			}
		);
		$this->assertTrue( Restricted_Site_Access::has_valid_custom_header() );
	}

	public function trusted_headers_provider() {
		/**
		 * Data to use in our trusted header tests
		 *
		 * First key is a string containing our expected IP.
		 * Second is an array of headers and the IP they are set to.
		 * Third is an array of headers to trust.
		 */
		return array(
			// Test that if we don't trust any headers, we get the REMOTE_ADDR value.
			array(
				'127.0.0.1',
				array(
					'HTTP_CLIENT_IP' => '10.0.0.0',
					'REMOTE_ADDR'    => '127.0.0.1',
				),
				array(),
			),
			// Test if we trust a single header, we get that value back.
			array(
				'10.0.0.0',
				array(
					'HTTP_CLIENT_IP' => '10.0.0.0',
					'REMOTE_ADDR'    => '127.0.0.1',
				),
				array( 'HTTP_CLIENT_IP' ),
			),
			// Test if we trust multiple headers, we get the first matched value back.
			array(
				'10.0.0.8',
				array(
					'HTTP_FORWARDED'   => '10.0.0.0',
					'HTTP_X_FORWARDED' => '10.0.0.8',
					'REMOTE_ADDR'      => '127.0.0.1',
				),
				array(
					'HTTP_X_FORWARDED',
					'HTTP_FORWARDED',
				),
			),
		);
	}

}
