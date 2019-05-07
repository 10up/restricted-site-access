<?php
/**
 * Single site test class
 *
 * @package restricted-site-access
 */

/**
 * PHPUnit test class
 */
class SingleSiteTest extends \TestCase {

	/**
	 * Test restricted access, send to the login screen option
	 */
	public function testRestrictLoginScreen() {
		$I = $this->openBrowserPage();

		$I->loginAs( 'admin', 'password' );

		$this->setSiteVisibiltySettings( $I, 'blog-restricted', 'rsa-send-to-login' );

		$this->logOut( $I );

		$I->moveTo( '/' );

		usleep( 500 );

		$url      = $I->getCurrentUrl();
		$contains = false;

		if ( false !== strpos( $url, 'wp-login.php' ) ) {
			$contains = true;
		}

		$this->assertTrue( $contains );
	}

	/**
	 * Test restricted access, send to a specified web address option
	 */
	public function testRestrictWebAddress() {
		$I = $this->openBrowserPage();

		$I->loginAs( 'admin', 'password' );

		$this->setSiteVisibiltySettings( $I, 'blog-restricted', 'rsa-redirect-visitor',
			[
				[
					'field' => 'redirect',
					'value' => 'https://www.google.com/',
					'type'  => 'input',
				],
			]
		);

		$this->logOut( $I );

		$I->moveTo( '/' );

		usleep( 500 );

		$url = $I->getCurrentUrl();

		$this->assertTrue( 'https://www.google.com/' === $I->getCurrentUrl() );
	}

	/**
	 * Test restricted access, show a message option
	 */
	public function testRestrictMessage() {
		$I = $this->openBrowserPage();

		$I->loginAs( 'admin', 'password' );

		$this->setSiteVisibiltySettings( $I, 'blog-restricted', 'rsa-display-message' );

		$this->logOut( $I );

		$I->moveTo( '/' );

		usleep( 500 );

		$url = $I->getCurrentUrl();

		$I->seeText( 'Access to this site is restricted' );
	}

	/**
	 * Test restricted access, show a page option
	 */
	public function testRestrictPage() {
		$I = $this->openBrowserPage();

		$I->loginAs( 'admin', 'password' );

		$this->setSiteVisibiltySettings( $I, 'blog-restricted', 'rsa-unblocked-page',
			[
				[
					'field' => 'rsa_page',
					'value' => '2',
					'type'  => 'select',
				],
			]
		);

		$this->logOut( $I );

		$I->moveTo( '/' );

		usleep( 500 );

		$url      = $I->getCurrentUrl();
		$contains = false;

		if ( false !== strpos( $url, 'sample-page' ) ) {
			$contains = true;
		}

		$this->assertTrue( $contains );
	}

	/**
	 * Test restricted access with an unrestricted IP address
	 */
	public function testRestrictIpAddress() {
		$I = $this->openBrowserPage();

		$I->loginAs( 'admin', 'password' );

		$this->setSiteVisibiltySettings( $I, 'blog-restricted', 'rsa-send-to-login' );

		$I->click( '#rsa_myip' );

		usleep( 100 );

		$I->click( '#addip' );

		usleep( 100 );

		$I->click( '#submit' );
		$I->waitUntilElementVisible( '#wpadminbar' );

		$this->logOut( $I );

		$I->moveTo( '/sample-page/' );

		usleep( 500 );

		$url      = $I->getCurrentUrl();
		$contains = false;

		if ( false !== strpos( $url, 'sample-page' ) ) {
			$contains = true;
		}

		$this->assertTrue( $contains );
	}

}
