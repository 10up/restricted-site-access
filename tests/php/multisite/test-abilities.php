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
		$result = $this->run_ability( 'set-network-mode', array( 'mode' => 'enforce' ) );

		$this->assertSame( 'enforce', $result['network_mode'] );
		$this->assertTrue( $result['changed'] );
		$this->assertSame( 'enforce', get_site_option( 'rsa_mode' ) );

		$result = $this->run_ability( 'set-network-mode', array( 'mode' => 'enforce' ) );
		$this->assertFalse( $result['changed'] );
	}

	public function test_site_admin_cannot_change_network() {
		wp_set_current_user( self::$site_admin_id );

		$result = $this->run_ability( 'set-network-mode', array( 'mode' => 'enforce' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'ability_invalid_permissions', $result->get_error_code() );

		$result = $this->run_ability(
			'update-settings',
			array(
				'network' => true,
				'mode'    => 'message',
			)
		);
		$this->assertWPError( $result );
		$this->assertSame( 'ability_invalid_permissions', $result->get_error_code() );
	}

	public function test_network_settings_are_separate_from_site_settings() {
		$result = $this->run_ability(
			'update-settings',
			array(
				'network' => true,
				'mode'    => 'message',
				'message' => 'Network message',
			)
		);

		$this->assertSame( 'network', $result['scope'] );
		$this->assertSame( 'message', $result['mode'] );
		$this->assertSame( 'Network message', get_site_option( 'rsa_options' )['message'] );
		$this->assertFalse( get_option( 'rsa_options' ) );

		$this->run_ability(
			'add-ips',
			array(
				'network' => true,
				'ips'     => array( array( 'ip' => '10.0.0.1' ) ),
			)
		);
		$this->assertSame( array( '10.0.0.1' ), get_site_option( 'rsa_options' )['allowed'] );
	}

	public function test_enforced_network_blocks_site_changes() {
		update_site_option( 'rsa_mode', 'enforce' );

		$settings = $this->run_ability( 'get-settings' );
		$this->assertTrue( $settings['overridden_by_network'] );
		$this->assertSame( 'enforce', $settings['network_mode'] );

		$result = $this->run_ability( 'update-settings', array( 'mode' => 'message' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_network_enforced', $result->get_error_code() );

		$result = $this->run_ability( 'add-ips', array( 'ips' => array( array( 'ip' => '10.0.0.1' ) ) ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_network_enforced', $result->get_error_code() );

		// The network settings can still change.
		$result = $this->run_ability(
			'update-settings',
			array(
				'network' => true,
				'mode'    => 'login',
			)
		);
		$this->assertSame( 'login', $result['mode'] );
	}
}
