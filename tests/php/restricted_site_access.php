<?php

class RestrictedSiteAccessTests extends \PHPUnit\Framework\TestCase {
	public function setUp() {
		\WP_Mock::setUp();
	}

	public function tearDown() {
		\WP_Mock::tearDown();
	}

	/**
	 * Test that ip addresses pass or fail as expected when compared against an ip with a mask.
	 *
	 * @dataProvider ip_data_provider
	 *
	 * @param {string}   The ip address to test.
	 * @param {string}   The ip plus mask to test against.
	 * @param {@boolean} The expected result, success (true) or failure (false).
	 */
	public function testIpInRange( $ip_to_test, $ip_plus_mask, $expected )
	{
		$this->assertEquals( $expected, Restricted_Site_Access::ip_in_range( $ip_to_test, $ip_plus_mask ) );
	}

	public function ip_data_provider()
	{
		return [
			[ '192.168.0.1', '192.168.0.1',    true ],
			[ '192.168.0.1', '192.168.0.1',    true ],
			[ '192.168.0.1', '192.168.0.1',    true ],
			[ '192.168.1.1', '192.1.5.2/24',   false ],
			[ '192.168.1.1', '192.168.5.2/24', false ],
			[ '192.168.1.1', '192.168.5.2/16', true ],
			[ '192.168.1.1', '192.168.1.0/24', true ],
			[ '192.168.1.1', '192.168.1.2',    false ],
			[ '192.168.1.1', '192.168.1.2/24', true ],
			[ '192.168.0.1', '192.169.2.2',    false ],
			[ '192.168.0.1', '192.169.2.2/24', false ],
			[ '192.169.0.1', '192.169.2.2/24', false ],
			[ '192.169.2.1', '192.169.2.2/24', true ],
			[ '192.168.0.1', '192.169.2.2/16', false ],
			[ '192.169.0.1', '192.169.2.2/16', true ],
			[ '192.168.0.1', '192.169.2.2/8',  true ]
		];
	}

	/**
	 * Test that the restrict_access function works as expected.
	 */
	public function testRestrictAccess()
	{
		//function get_permalink( $id ) { $mock->get_permalink( $id ); }

		\WP_Mock::userFunction(
			'get_option',
			array(
				'return_in_order' => array(
					array( 'page' => 1, ),
					2
				),
				'times' => 0,
			)
		);
		\WP_Mock::onFilter( 'restricted_site_access_approach' )
			->with( 1 )
			->reply( 4 );
		$this->assertEquals( false, Restricted_Site_Access::restrict_access( array() ) );
		//
		// Expect get_permalink to be called with the value 1.
		//
		// Expect wp_redirect to be called with 'https://wordpress.local/home' and status code 302.
	}

}
?>
