<?php

class IpMaskingTests extends \PHPUnit\Framework\TestCase {
	public function setUp() {
		\WP_Mock::setUp();
	}

	public function tearDown() {
		\WP_Mock::tearDown();
	}

	/**
	 * Test that ip adcdresses pass or fail as expected when compared against an ip with a mask.
	 *
	 * @dataProvider ip_data_provider
	 *
	 * @param {string}   The ip address to test.
	 * @param {string}   The ip plus mask to test against.
	 * @param {@boolean} The expected result, success (true) or failure (false).
	 */
	public function testAdd( $ip_to_test, $ip_plus_mask, $expected )
	{
		$this->assertEquals( $expected, Restricted_Site_Access::ip_in_mask( $ip_to_test, $ip_plus_mask ) );
	}

	public function ip_data_provider()
	{
		return [
			[ '192.168.0.1', '192.168.0.1', true ],
			[ '192.168.0.1', '192.168.0.1', true ],
			[ '192.168.0.1', '192.168.0.1', true ],
			[ '192.168.1.1', '102.1.5.2/24', true ],
			[ '192.168.1.1', '192.168.1.0/24', true ],
			[ '192.168.1.1', '192.168.1.1', true ]

		];
	}
}
?>
