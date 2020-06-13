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
		$this->assertStringContainsString( 'Set site IP whitelist to', $cli_result );
		$this->assertStringContainsString( '10.0.0.5', $cli_result );
		$this->assertStringContainsString( '10.0.0.6', $cli_result );

		$cli_result = $this->runCommand( 'rsa ip-list --network' )['stdout'];

		$this->assertStringNotContainsString( '10.0.0.3', $cli_result );
		$this->assertStringContainsString( '10.0.0.5', $cli_result );
		$this->assertStringContainsString( '10.0.0.6', $cli_result );
	}

}
