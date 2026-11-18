<?php

class Restricted_Site_Access_Test_Singlesite_Abilities extends WP_UnitTestCase {

	/**
	 * Administrator user ID.
	 *
	 * @var int
	 */
	private static $admin_id;

	/**
	 * Editor user ID.
	 *
	 * @var int
	 */
	private static $editor_id;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$admin_id  = $factory->user->create( array( 'role' => 'administrator' ) );
		self::$editor_id = $factory->user->create( array( 'role' => 'editor' ) );
	}

	public function set_up() {
		parent::set_up();

		wp_set_current_user( self::$admin_id );
		update_option( 'blog_public', 1 );
		delete_option( 'rsa_options' );
	}

	/**
	 * Run an ability and return its result.
	 *
	 * @param string $name  Ability name without the prefix.
	 * @param mixed  $input Ability input.
	 * @return mixed
	 */
	private function run_ability( $name, $input = null ) {
		$ability = wp_get_ability( 'restricted-site-access/' . $name );
		$this->assertNotNull( $ability, $name . ' is not registered.' );

		return $ability->execute( $input );
	}

	public function test_abilities_are_registered() {
		foreach ( array( 'get-settings', 'update-settings', 'list-ips', 'add-ips', 'update-ip', 'remove-ips' ) as $name ) {
			$this->assertTrue( wp_has_ability( 'restricted-site-access/' . $name ), $name . ' is not registered.' );
		}

		// Network mode is only available when the plugin is network activated.
		$this->assertFalse( wp_has_ability( 'restricted-site-access/set-network-mode' ) );
	}

	public function test_editor_cannot_use_abilities() {
		wp_set_current_user( self::$editor_id );

		$result = $this->run_ability( 'get-settings' );

		$this->assertWPError( $result );
		$this->assertSame( 'ability_invalid_permissions', $result->get_error_code() );
	}

	public function test_get_settings_defaults() {
		$result = $this->run_ability( 'get-settings' );

		$this->assertSame( 'site', $result['scope'] );
		$this->assertFalse( $result['restricted'] );
		$this->assertSame( 'login', $result['mode'] );
		$this->assertSame( 302, $result['redirect_status_code'] );
		$this->assertNull( $result['network_mode'] );
		$this->assertFalse( $result['overridden_by_network'] );
	}

	public function test_network_scope_is_rejected_on_single_site() {
		$result = $this->run_ability( 'get-settings', array( 'network' => true ) );

		$this->assertWPError( $result );
		$this->assertSame( 'rsa_network_unavailable', $result->get_error_code() );
	}

	public function test_update_settings_redirect_mode() {
		$result = $this->run_ability( 'update-settings', array( 'mode' => 'redirect' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_redirect_url_required', $result->get_error_code() );

		$result = $this->run_ability( 'update-settings', array( 'redirect_url' => 'javascript:alert(1)' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_invalid_redirect_url', $result->get_error_code() );

		$result = $this->run_ability(
			'update-settings',
			array(
				'restricted'           => true,
				'mode'                 => 'redirect',
				'redirect_url'         => 'https://example.com',
				'redirect_status_code' => 307,
				'redirect_same_path'   => true,
			)
		);

		$this->assertTrue( $result['restricted'] );
		$this->assertSame( 'redirect', $result['mode'] );
		$this->assertSame( 'https://example.com', $result['redirect_url'] );
		$this->assertSame( 307, $result['redirect_status_code'] );
		$this->assertTrue( $result['redirect_same_path'] );
		$this->assertSame( 2, (int) get_option( 'blog_public' ) );

		$options = get_option( 'rsa_options' );
		$this->assertSame( 2, $options['approach'] );
		$this->assertSame( 307, $options['head_code'] );
	}

	public function test_update_settings_page_mode() {
		$result = $this->run_ability(
			'update-settings',
			array(
				'mode'    => 'page',
				'page_id' => self::factory()->post->create(),
			)
		);
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_invalid_page', $result->get_error_code() );

		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$result  = $this->run_ability(
			'update-settings',
			array(
				'mode'    => 'page',
				'page_id' => $page_id,
			)
		);

		$this->assertSame( 'page', $result['mode'] );
		$this->assertSame( $page_id, $result['page_id'] );
	}

	public function test_update_settings_roles() {
		$result = $this->run_ability( 'update-settings', array( 'hide_admin_bar_roles' => array( 'subscriber', 'not-a-role' ) ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_invalid_roles', $result->get_error_code() );

		$result = $this->run_ability( 'update-settings', array( 'hide_admin_bar_roles' => array( 'subscriber' ) ) );
		$this->assertSame( array( 'subscriber' ), $result['hide_admin_bar_roles'] );
	}

	public function test_disabling_restriction_makes_site_public() {
		update_option( 'blog_public', 2 );

		$result = $this->run_ability( 'update-settings', array( 'restricted' => false ) );

		$this->assertFalse( $result['restricted'] );
		$this->assertSame( 1, (int) get_option( 'blog_public' ) );

		// Disabling again keeps "discourage search engines".
		update_option( 'blog_public', 0 );
		$this->run_ability( 'update-settings', array( 'restricted' => false ) );
		$this->assertSame( 0, (int) get_option( 'blog_public' ) );
	}

	public function test_add_update_and_remove_ips() {
		$result = $this->run_ability(
			'add-ips',
			array(
				'ips' => array(
					array(
						'ip'    => '10.0.0.1',
						'label' => 'Office',
					),
					array( 'ip' => '192.168.0.0/24' ),
				),
			)
		);
		$this->assertSame( array( '10.0.0.1', '192.168.0.0/24' ), $result['added'] );

		// A missing label keeps the saved label. A new label replaces it.
		$result = $this->run_ability(
			'add-ips',
			array(
				'ips' => array(
					array( 'ip' => '10.0.0.1' ),
					array(
						'ip'    => '192.168.0.0/24',
						'label' => 'LAN',
					),
				),
			)
		);
		$this->assertSame( array( '10.0.0.1' ), $result['unchanged'] );
		$this->assertSame( array( '192.168.0.0/24' ), $result['updated'] );

		$options = get_option( 'rsa_options' );
		$this->assertSame( array( '10.0.0.1', '192.168.0.0/24' ), $options['allowed'] );
		$this->assertSame( array( 'Office', 'LAN' ), $options['comment'] );

		// One invalid address rejects the whole call.
		$result = $this->run_ability( 'add-ips', array( 'ips' => array( array( 'ip' => '10.0.0.2' ), array( 'ip' => 'bad' ) ) ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_invalid_ip', $result->get_error_code() );
		$this->assertNotContains( '10.0.0.2', get_option( 'rsa_options' )['allowed'] );

		$result = $this->run_ability(
			'update-ip',
			array(
				'ip'     => '10.0.0.1',
				'new_ip' => '10.0.0.9',
				'label'  => 'Office 2',
			)
		);
		$this->assertSame( '10.0.0.9', $result['ip'] );
		$this->assertSame( 'Office 2', $result['label'] );

		$result = $this->run_ability(
			'update-ip',
			array(
				'ip'     => '10.0.0.9',
				'new_ip' => '192.168.0.0/24',
			)
		);
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_ip_exists', $result->get_error_code() );

		$result = $this->run_ability( 'remove-ips', array( 'ips' => array( '10.0.0.9', '8.8.8.8' ) ) );
		$this->assertSame( array( '10.0.0.9' ), $result['removed'] );
		$this->assertSame( array( '8.8.8.8' ), $result['not_found'] );
		$this->assertSame( array( '192.168.0.0/24' ), get_option( 'rsa_options' )['allowed'] );
	}

	public function test_static_ip_methods_see_ability_changes() {
		// Load the static options cache before the ability writes.
		Restricted_Site_Access::get_ips( false );

		$this->run_ability( 'add-ips', array( 'ips' => array( array( 'ip' => '10.0.0.1' ) ) ) );
		Restricted_Site_Access::append_ips( array( '10.0.0.2' ) );

		$this->assertSame( array( '10.0.0.1', '10.0.0.2' ), get_option( 'rsa_options' )['allowed'] );
	}
}
