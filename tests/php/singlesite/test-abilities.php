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
		$this->assertTrue( wp_has_ability( 'restricted-site-access/settings-query' ) );
		$this->assertTrue( wp_has_ability( 'restricted-site-access/settings-update' ) );
	}

	public function test_editor_cannot_use_abilities() {
		wp_set_current_user( self::$editor_id );

		$result = $this->run_ability( 'settings-query' );
		$this->assertWPError( $result );
		$this->assertSame( 'ability_invalid_permissions', $result->get_error_code() );

		$result = $this->run_ability( 'settings-update', array( 'mode' => 'message' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'ability_invalid_permissions', $result->get_error_code() );
	}

	public function test_settings_query_defaults() {
		$result = $this->run_ability( 'settings-query' );

		$this->assertSame( 'site', $result['scope'] );
		$this->assertFalse( $result['restricted'] );
		$this->assertSame( 'login', $result['mode'] );
		$this->assertSame( 302, $result['redirect_status_code'] );
		$this->assertSame( array(), $result['ips'] );
		$this->assertSame( array(), $result['config_ips'] );
		$this->assertNull( $result['network_mode'] );
		$this->assertFalse( $result['network_mode_locked'] );
		$this->assertFalse( $result['overridden_by_network'] );
	}

	public function test_network_scope_is_rejected_on_single_site() {
		$result = $this->run_ability( 'settings-query', array( 'network' => true ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_network_unavailable', $result->get_error_code() );

		$result = $this->run_ability( 'settings-update', array( 'network_mode' => 'enforce' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_network_unavailable', $result->get_error_code() );
	}

	public function test_settings_update_needs_a_field() {
		$result = $this->run_ability( 'settings-update', array() );

		$this->assertWPError( $result );
		$this->assertSame( 'rsa_no_changes', $result->get_error_code() );
	}

	public function test_settings_update_redirect_mode() {
		$result = $this->run_ability( 'settings-update', array( 'mode' => 'redirect' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_redirect_url_required', $result->get_error_code() );

		$result = $this->run_ability( 'settings-update', array( 'redirect_url' => 'javascript:alert(1)' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_invalid_redirect_url', $result->get_error_code() );

		$result = $this->run_ability(
			'settings-update',
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

	public function test_settings_update_page_mode() {
		$result = $this->run_ability(
			'settings-update',
			array(
				'mode'    => 'page',
				'page_id' => self::factory()->post->create(),
			)
		);
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_invalid_page', $result->get_error_code() );

		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$result  = $this->run_ability(
			'settings-update',
			array(
				'mode'    => 'page',
				'page_id' => $page_id,
			)
		);

		$this->assertSame( 'page', $result['mode'] );
		$this->assertSame( $page_id, $result['page_id'] );
	}

	public function test_settings_update_roles() {
		$result = $this->run_ability( 'settings-update', array( 'hide_admin_bar_roles' => array( 'subscriber', 'not-a-role' ) ) );
		$this->assertWPError( $result );
		$this->assertSame( 'rsa_invalid_roles', $result->get_error_code() );

		$result = $this->run_ability( 'settings-update', array( 'hide_admin_bar_roles' => array( 'subscriber' ) ) );
		$this->assertSame( array( 'subscriber' ), $result['hide_admin_bar_roles'] );
	}

	public function test_disabling_restriction_makes_site_public() {
		update_option( 'blog_public', 2 );

		$result = $this->run_ability( 'settings-update', array( 'restricted' => false ) );

		$this->assertFalse( $result['restricted'] );
		$this->assertSame( 1, (int) get_option( 'blog_public' ) );

		// Disabling again keeps "discourage search engines".
		update_option( 'blog_public', 0 );
		$this->run_ability( 'settings-update', array( 'restricted' => false ) );
		$this->assertSame( 0, (int) get_option( 'blog_public' ) );
	}

	public function test_restriction_only_update_does_not_create_options() {
		$this->run_ability( 'settings-update', array( 'restricted' => true ) );

		$this->assertSame( 2, (int) get_option( 'blog_public' ) );
		$this->assertFalse( get_option( 'rsa_options' ) );
	}

	public function test_settings_update_replaces_ips() {
		$result = $this->run_ability(
			'settings-update',
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

		$this->assertSame(
			array(
				array(
					'ip'    => '10.0.0.1',
					'label' => 'Office',
				),
				array(
					'ip'    => '192.168.0.0/24',
					'label' => '',
				),
			),
			$result['ips']
		);

		// The query result can be sent back with one address added.
		$ips   = $this->run_ability( 'settings-query' )['ips'];
		$ips[] = array(
			'ip'    => '10.0.0.2',
			'label' => 'VPN',
		);
		$result = $this->run_ability( 'settings-update', array( 'ips' => $ips ) );
		$this->assertSame( array( '10.0.0.1', '192.168.0.0/24', '10.0.0.2' ), array_column( $result['ips'], 'ip' ) );

		$options = get_option( 'rsa_options' );
		$this->assertSame( array( '10.0.0.1', '192.168.0.0/24', '10.0.0.2' ), $options['allowed'] );
		$this->assertSame( array( 'Office', '', 'VPN' ), $options['comment'] );

		// An empty list removes every saved address.
		$result = $this->run_ability( 'settings-update', array( 'ips' => array() ) );
		$this->assertSame( array(), $result['ips'] );
		$this->assertSame( array(), get_option( 'rsa_options' )['allowed'] );
	}

	public function test_invalid_ip_rejects_the_whole_update() {
		$result = $this->run_ability(
			'settings-update',
			array(
				'mode' => 'message',
				'ips'  => array( array( 'ip' => '10.0.0.1' ), array( 'ip' => 'bad' ) ),
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'rsa_invalid_ip', $result->get_error_code() );
		$this->assertFalse( get_option( 'rsa_options' ) );
	}

	public function test_duplicate_ips_keep_the_last_label() {
		$result = $this->run_ability(
			'settings-update',
			array(
				'ips' => array(
					array(
						'ip'    => '10.0.0.1',
						'label' => 'Old',
					),
					array(
						'ip'    => '10.0.0.1',
						'label' => 'New',
					),
				),
			)
		);

		$this->assertSame(
			array(
				array(
					'ip'    => '10.0.0.1',
					'label' => 'New',
				),
			),
			$result['ips']
		);
	}

	public function test_static_ip_methods_see_ability_changes() {
		// Load the static options cache before the ability writes.
		Restricted_Site_Access::get_ips( false );

		$this->run_ability( 'settings-update', array( 'ips' => array( array( 'ip' => '10.0.0.1' ) ) ) );
		Restricted_Site_Access::append_ips( array( '10.0.0.2' ) );

		$this->assertSame( array( '10.0.0.1', '10.0.0.2' ), get_option( 'rsa_options' )['allowed'] );
	}
}
