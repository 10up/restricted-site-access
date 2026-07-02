<?php
/**
 * Tests for IP allowlist management: append_ips().
 *
 * @package Restricted_Site_Access
 */

class Restricted_Site_Access_Test_IP_Management extends WP_UnitTestCase {

	/**
	 * Reset the private static $rsa_options property so append_ips() re-reads
	 * the current option from the database on the next call.
	 */
	private function reset_rsa_options() {
		$reflection = new ReflectionClass( 'Restricted_Site_Access' );
		$prop       = $reflection->getProperty( 'rsa_options' );
		// @todo Remove this once the minimum PHP version is 8.1 or later.
		$prop->setAccessible( true );
		$prop->setValue( null, null );
	}

	/**
	 * append_ips() must update the label for the first IP in the allowlist.
	 *
	 * Before the fix, array_search() returned 0 for a hit at index 0. Because
	 * 0 is falsy, the condition `if ( $found_index && ... )` evaluated to false
	 * and the label update was silently dropped. The elseif branch also did
	 * nothing because `false !== 0` with strict comparison.
	 *
	 * The fix changes the guard to `if ( false !== $found_index && ... )` which
	 * correctly distinguishes "not found" (false) from "found at index 0" (0).
	 *
	 * Delete-the-fix test: revert the guard back to `if ( $found_index && ... )`
	 * and the assertSame( 'updated-label' ) assertion below fails — the DB still holds
	 * 'first-label' because the update branch was never entered.
	 */
	public function test_append_ips_updates_label_for_first_ip_in_allowlist() {
		// Pre-populate the allowlist. '192.168.1.1' lands at index 0 — the
		// exact slot that triggered the silent-drop bug.
		update_option(
			'rsa_options',
			array(
				'allowed' => array( '192.168.1.1', '192.168.1.2' ),
				'comment' => array( 'first-label', 'second-label' ),
			)
		);
		$this->reset_rsa_options();

		// Confirm the precondition: target IP is genuinely at index 0.
		$options_before = get_option( 'rsa_options' );
		$this->assertSame(
			0,
			array_search( '192.168.1.1', $options_before['allowed'], true ),
			'Precondition: 192.168.1.1 must be at index 0 for this test to exercise the bug path.'
		);

		// Call append_ips() asking for a label change on the first IP.
		Restricted_Site_Access::append_ips( array( '192.168.1.1' => 'updated-label' ) );

		$options = get_option( 'rsa_options' );

		$this->assertSame(
			'updated-label',
			$options['comment'][0],
			'Label update for the IP at index 0 must be persisted. ' .
			'array_search() returns 0 for a hit at index 0; the guard must use ' .
			'false !== $found_index, not a plain truthiness check.'
		);

		// The second IP must be untouched.
		$this->assertSame( 'second-label', $options['comment'][1] );

		// The IPs themselves must be unchanged.
		$this->assertSame( array( '192.168.1.1', '192.168.1.2' ), $options['allowed'] );
	}

	/**
	 * append_ips() must also update the label when it is the only IP in the list.
	 *
	 * A single-IP allowlist has exactly one element at index 0, so this is a
	 * focused regression for the same bug path without a second element to mask it.
	 */
	public function test_append_ips_updates_label_for_sole_ip_in_allowlist() {
		update_option(
			'rsa_options',
			array(
				'allowed' => array( '10.0.0.1' ),
				'comment' => array( 'original' ),
			)
		);
		$this->reset_rsa_options();

		Restricted_Site_Access::append_ips( array( '10.0.0.1' => 'renamed' ) );

		$options = get_option( 'rsa_options' );

		$this->assertSame( 'renamed', $options['comment'][0] );
		// The IP itself must not be duplicated.
		$this->assertCount( 1, $options['allowed'] );
	}

	/**
	 * append_ips() must still add a new IP when the address is not in the list.
	 *
	 * Sanity-check that the false !== guard does not regress the "not found" path.
	 */
	public function test_append_ips_adds_new_ip_when_not_in_allowlist() {
		update_option(
			'rsa_options',
			array(
				'allowed' => array( '10.0.0.1' ),
				'comment' => array( 'existing' ),
			)
		);
		$this->reset_rsa_options();

		Restricted_Site_Access::append_ips( array( '10.0.0.2' => 'new-entry' ) );

		$options = get_option( 'rsa_options' );

		$this->assertContains( '10.0.0.2', $options['allowed'] );
		$idx = array_search( '10.0.0.2', $options['allowed'], true );
		$this->assertSame( 'new-entry', $options['comment'][ $idx ] );
	}
}
