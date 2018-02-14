<?php

class IpMaskingTests extends \PHPUnit\Framework\TestCase {

	/**
	 * Test that ip addresses pass or fail as expected when compared against an ip with a mask.
	 *
	 * @dataProvider ip_data_provider
	 *
	 * @param {string}   The ip address to test.
	 * @param {string}   The ip plus mask to test against.
	 * @param {@boolean} The expected result, success (true) or failure (false).
	 */
	public function testAdd( $ip_to_test, $ip_plus_mask, $expected )
	{
		$this->assertEquals( $expected, ip_in_range( $ip_to_test, $ip_plus_mask ) );
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
}
?>
