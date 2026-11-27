<?php
/**
 * Tests for what saving the plugin settings keeps and discards.
 *
 * @package Restricted_Site_Access
 */

/**
 * Saving `rsa_options` runs the registered sanitize callback; these tests save
 * through update_option() the way the Reading settings screen does.
 */
class Restricted_Site_Access_Test_Settings_Sanitize extends WP_UnitTestCase {

	/**
	 * Register the setting and its sanitize callback, as the admin does on admin_init.
	 */
	public function set_up() {
		parent::set_up();
		Restricted_Site_Access::admin_init();
	}

	/**
	 * Save settings over the defaults and return what was stored.
	 *
	 * @param array $input Settings to save.
	 * @return array Stored settings.
	 */
	private function save( array $input ) {
		update_option( 'rsa_options', array_merge( Restricted_Site_Access::get_options( false ), $input ) );

		return get_option( 'rsa_options' );
	}

	/**
	 * Only the four known approaches are kept; anything else is saved as "send to login".
	 */
	public function test_unknown_approach_is_saved_as_login() {
		$this->assertSame( 4, $this->save( array( 'approach' => 4 ) )['approach'] );

		foreach ( array( 0, 5, 'other' ) as $approach ) {
			$this->assertSame( 1, $this->save( array( 'approach' => $approach ) )['approach'], (string) $approach );
		}
	}

	/**
	 * Only the redirect status codes 301, 302 and 307 are kept; anything else is saved as 302.
	 */
	public function test_unknown_redirect_status_is_saved_as_302() {
		$this->assertSame( 307, $this->save( array( 'head_code' => 307 ) )['head_code'] );
		$this->assertSame( 302, $this->save( array( 'head_code' => 404 ) )['head_code'] );
	}

	/**
	 * The redirect address keeps only http and https addresses.
	 */
	public function test_redirect_address_keeps_only_web_addresses() {
		$this->assertSame( 'https://example.com/landing', $this->save( array( 'redirect_url' => 'https://example.com/landing' ) )['redirect_url'] );

		foreach ( array( 'javascript:void(0)', 'ftp://example.com/', 'data:text/plain,hello' ) as $address ) {
			$this->assertSame( '', $this->save( array( 'redirect_url' => $address ) )['redirect_url'], $address );
		}
	}

	/**
	 * The restriction message keeps the HTML allowed in post content and drops script elements.
	 */
	public function test_message_keeps_only_post_html() {
		$message = $this->save( array( 'message' => '<p>Members <strong>only</strong>.</p><script>document.title</script>' ) )['message'];

		$this->assertStringContainsString( '<p>Members <strong>only</strong>.</p>', $message );
		$this->assertStringNotContainsString( '<script', $message );
	}

	/**
	 * The allow list keeps valid addresses, ranges and patterns, drops invalid
	 * entries, and keeps each comment with its own address.
	 */
	public function test_allow_list_drops_invalid_entries_and_keeps_comments_aligned() {
		$options = $this->save(
			array(
				'allowed' => array( '172.10.23.4', 'not-an-ip', '172.10.23.4/3', '182.10.*.*', '2001:0db8:3333:4444:0000:0000:0000:0000/64', '172.256.23.4' ),
				'comment' => array( 'single', 'invalid', 'range', 'pattern', 'subnet', 'invalid too' ),
			)
		);

		$this->assertSame( array( '172.10.23.4', '172.10.23.4/3', '182.10.*.*', '2001:0db8:3333:4444:0000:0000:0000:0000/64' ), $options['allowed'] );
		$this->assertSame( array( 'single', 'range', 'pattern', 'subnet' ), $options['comment'] );
	}

	/**
	 * Only roles that exist are kept in the list of roles whose admin bar is hidden.
	 */
	public function test_hidden_admin_bar_roles_keep_only_existing_roles() {
		add_role( 'rsa_custom_role', 'RSA Custom Role', array( 'read' => true ) );

		$options = $this->save( array( 'hide_admin_bar_roles' => array( 'subscriber', 'rsa_custom_role', 'no_such_role' ) ) );

		remove_role( 'rsa_custom_role' );

		$this->assertSame( array( 'subscriber', 'rsa_custom_role' ), $options['hide_admin_bar_roles'] );
	}
}
