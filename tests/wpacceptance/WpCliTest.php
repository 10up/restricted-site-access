<?php
/**
 * Single site test class
 *
 * @package restricted-site-access
 */

/**
 * PHPUnit test class
 */
class WpCliTest extends \TestCase {

	/**
	 * @testdox Test IP list, add, remove, set for single site.
	 */
	public function testIpManipulationsSingleSite() {
		$cli_result = $this->runCommand( 'rsa ip-list' )['stdout'];

		$this->assertStringContainsString( 'No IP addresses configured', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-add 10.0.0.3' )['stdout'];
		$cli_result = $this->runCommand( 'rsa ip-add 10.0.0.4' )['stdout'];

		$this->assertStringContainsString( 'Added 10.0.0.4 to site whitelist', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-list' )['stdout'];

		$this->assertStringContainsString( '10.0.0.3', $cli_result );
		$this->assertStringContainsString( '10.0.0.4', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-remove 10.0.0.4' )['stdout'];

		$this->assertStringContainsString( 'Removed IPs 10.0.0.4', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-list' )['stdout'];

		$this->assertStringContainsString( '10.0.0.3', $cli_result );
		$this->assertStringNotContainsString( '10.0.0.4', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-set 10.0.0.5 10.0.0.6' )['stdout'];
		$this->assertStringContainsString( 'Set site IP whitelist to', $cli_result );
		$this->assertStringContainsString( '10.0.0.5', $cli_result );
		$this->assertStringContainsString( '10.0.0.6', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-list' )['stdout'];

		$this->assertStringNotContainsString( '10.0.0.3', $cli_result );
		$this->assertStringContainsString( '10.0.0.5', $cli_result );
		$this->assertStringContainsString( '10.0.0.6', $cli_result );
	}

	/**
	 * @testdox Test IP list, add, remove for network.
	 */
	public function testIpManipulationsNetwork() {
		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->networkActivate( $I );

		$cli_result = $this->runCommand( 'rsa ip-list --network' )['stdout'];

		$this->assertStringContainsString( 'No IP addresses configured', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-add 10.0.0.3 --network' )['stdout'];
		$cli_result = $this->runCommand( 'rsa ip-add 10.0.0.4 --network' )['stdout'];

		$this->assertStringContainsString( 'Added 10.0.0.4 to network whitelist', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-list --network' )['stdout'];

		$this->assertStringContainsString( '10.0.0.3', $cli_result );
		$this->assertStringContainsString( '10.0.0.4', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-remove 10.0.0.4 --network' )['stdout'];

		$this->assertStringContainsString( 'Removed IPs 10.0.0.4', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-list --network' )['stdout'];

		$this->assertStringContainsString( '10.0.0.3', $cli_result );
		$this->assertStringNotContainsString( '10.0.0.4', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-set 10.0.0.5 10.0.0.6 --network' )['stdout'];
		$this->assertStringContainsString( 'Set network IP whitelist to', $cli_result );
		$this->assertStringContainsString( '10.0.0.5', $cli_result );
		$this->assertStringContainsString( '10.0.0.6', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-list --network' )['stdout'];

		$this->assertStringNotContainsString( '10.0.0.3', $cli_result );
		$this->assertStringContainsString( '10.0.0.5', $cli_result );
		$this->assertStringContainsString( '10.0.0.6', $cli_result );
	}

	public function testSetMode() {
		$cli_result = $this->runCommand( 'rsa set-mode login' )['stdout'];
		$this->assertStringContainsString( 'Site redirecting visitors to login', $cli_result );

		$I = $this->openBrowserPage();

		$I->moveTo( '/' );
		usleep( 500 );
		$this->assertStringContainsString( 'wp-login.php', $I->getcurrentUrl() );

		$cli_result = $this->runCommand( 'rsa set-mode disable' )['stdout'];
		$this->assertStringContainsString( 'Site restrictions disabled.', $cli_result );

		$I->moveTo( '/sample-page' );
		usleep( 500 );
		$this->assertStringContainsString( 'sample-page', $I->getcurrentUrl() );

		$cli_result = $this->runCommand( 'rsa set-mode redirect --redirect=http://example.com' )['stdout'];
		$this->assertStringContainsString( 'example.com', $cli_result );

		$I->moveTo( '/sample-page' );
		usleep( 500 );
		$this->assertStringContainsString( 'example.com', $I->getcurrentUrl() );

		$cli_result = $this->runCommand( 'rsa set-mode message --text="None shall pass!"' )['stdout'];
		$this->assertStringContainsString( 'Site showing message to visitors', $cli_result );

		$I->moveTo( '/sample-page' );
		usleep( 500 );
		$I->seeText( 'None shall pass' );

		$cli_result = $this->runCommand( 'rsa set-mode page --page=2' )['stdout'];
		$this->assertStringContainsString( 'Sample Page', $cli_result );

		$I->moveTo( '/' );
		usleep( 500 );
		$this->assertStringContainsString( 'sample-page', $I->getcurrentUrl() );
	}

	public function testSetNetworkMode() {
		$cli_result = $this->runCommand( 'rsa set-network-mode default' )['stdout'];
		$this->assertStringContainsString( 'Cannot set network mode', $cli_result );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->networkActivate( $I );

		$cli_result = $this->runCommand( 'rsa set-network-mode default' )['stdout'];
		$this->assertStringContainsString( 'Mode is already set', $cli_result );

		$cli_result = $this->runCommand( 'rsa set-network-mode enforce' )['stdout'];
		$this->assertStringContainsString( 'Set network mode to', $cli_result );

		$I->moveTo( '/wp-admin/network/settings.php' );

		$this->assertEquals( 'enforce', $I->executeJavaScript( 'return document.querySelector(\'input[name="rsa_mode"]:checked\').value;' ));
		
	}
}
