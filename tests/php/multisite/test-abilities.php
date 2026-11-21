<?php

class Restricted_Site_Access_Test_Multisite_Abilities extends WP_UnitTestCase {

	/**
	 * Super admin user ID.
	 *
	 * @var int
	 */
	private static $super_admin_id;

	/**
	 * Site administrator user ID.
	 *
	 * @var int
	 */
	private static $site_admin_id;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$super_admin_id = $factory->user->create( array( 'role' => 'administrator' ) );
		grant_super_admin( self::$super_admin_id );

		self::$site_admin_id = $factory->user->create( array( 'role' => 'administrator' ) );
	}

	public function set_up() {
		parent::set_up();

		wp_set_current_user( self::$super_admin_id );
		update_site_option( 'rsa_mode', 'default' );
		delete_site_option( 'rsa_options' );
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

	public function test_set_network_mode() {
		$result = $this->run_ability(
			'settings-update',
			array(
				'network'      => true,
				'network_mode' => 'enforce',
			)
		);

		$this->assertSame( 'network', $result['scope'] );
		$this->assertSame( 'enforce', $result['network_mode'] );
		$this->assertSame( 'enforce', get_site_option( 'rsa_mode' ) );

		// Changing only the network mode does not create network options.
		$this->assertFalse( get_site_option( 'rsa_options' ) );
	}

	public function test_network_mode_needs_network_scope() {
		$result = $this->run_ability( 'settings-update', array( 'network_mode' => 'enforce' ) );

		$this->assertWPError( $result );
		$this->assertSame( 'rsa_network_scope_required', $result->get_error_code() );
		$this->assertSame( 'default', get_site_option( 'rsa_mode' ) );
	}

	public function test_site_admin_cannot_change_network() {
		wp_set_current_user( self::$site_admin_id );

		$result = $this->run_ability( 'settings-update', array( 'network_mode' => 'enforce' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'ability_invalid_permissions', $result->get_error_code() );

		$result = $this->run_ability(
			'settings-update',
			array(
				'network' => true,
				'mode'    => 'message',
			)
		);
		$this->assertWPError( $result );
		$this->assertSame( 'ability_invalid_permissions', $result->get_error_code() );

		$result = $this->run_ability( 'settings-query', array( 'network' => true ) );
		$this->assertWPError( $result );
		$this->assertSame( 'ability_invalid_permissions', $result->get_error_code() );
	}

	public function test_network_settings_are_separate_from_site_settings() {
		$result = $this->run_ability(
			'settings-update',
			array(
				'network' => true,
				'mode'    => 'message',
				'message' => 'Network message',
				'ips'     => array( array( 'ip' => '10.0.0.1' ) ),
			)
		);

		$this->assertSame( 'network', $result['scope'] );
		$this->assertSame( 'message', $result['mode'] );
		$this->assertSame( array( '10.0.0.1' ), array_column( $result['ips'], 'ip' ) );

		$network_options = get_site_option( 'rsa_options' );
		$this->assertSame( 'Network message', $network_options['message'] );
		$this->assertSame( array( '10.0.0.1' ), $network_options['allowed'] );
		$this->assertFalse( get_option( 'rsa_options' ) );

		$site = $this->run_ability( 'settings-query' );
		$this->assertSame( 'site', $site['scope'] );
		$this->assertSame( array(), $site['ips'] );
	}

	public function test_enforced_network_blocks_site_changes() {
		update_site_option( 'rsa_mode', 'enforce' );

		$settings = $this->run_ability( 'settings-query' );
		$this->assertTrue( $settings['overridden_by_network'] );
		$this->assertSame( 'enforce', $settings['network_mode'] );

		$result = $this->run_ability( 'settings-update', array( 'mode' => 'message' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_network_enforced', $result->get_error_code() );

		$result = $this->run_ability( 'settings-update', array( 'ips' => array( array( 'ip' => '10.0.0.1' ) ) ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_network_enforced', $result->get_error_code() );

		// The network settings can still change, and the network mode can be reset.
		$result = $this->run_ability(
			'settings-update',
			array(
				'network'      => true,
				'mode'         => 'login',
				'network_mode' => 'default',
			)
		);
		$this->assertSame( 'login', $result['mode'] );
		$this->assertSame( 'default', $result['network_mode'] );
	}
}
