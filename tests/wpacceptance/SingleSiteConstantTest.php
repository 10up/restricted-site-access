<?php
/**
 * Single site constant test class
 *
 * @package restricted-site-access
 */

/**
 * PHPUnit test class
 */
class SingleSiteConstantTest extends \TestCase {

	/**
	 * Test the Forbid Restriction constant
	 */
	public function testForbidRestriction() {
		$this->setConstant( 'RSA_FORBID_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$I->moveTo( '/wp-admin/options-reading.php' );
		$I->waitUntilElementVisible( '.rsa-network-enforced-warning' );

		$I->seeText( 'Site visibility settings are currently enforced by code configuration', '.rsa-network-enforced-warning .notice-warning' );

		$I->cannotInteractWithField( '#blog-restricted' );
	}

	/**
	 * Test the Force Restriction constant
	 */
	public function testForceRestriction() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$I->moveTo( '/wp-admin/options-reading.php' );
		$I->waitUntilElementVisible( '.rsa-setting_settings_field_handling' );

		$I->seeText( 'Site visibility settings are currently enforced by code configuration', '.rsa-network-enforced-warning .notice-warning' );

		$I->cannotInteractWithField( '#blog-restricted' );
	}

	/**
	 * Test the Force Restriction constant, send to the login screen option
	 */
	public function testForceRestrictionLoginScreen() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->setSiteVisibilitySettings( $I,
			[
				'visibility' => 'leave-alone',
				'restricted' => 'rsa-send-to-login',
			]
		);

		$I->logout();

		$I->moveTo( '/' );

		usleep( 500 );

		$contains = false;

		if ( false !== strpos( $I->getCurrentUrl(), 'wp-login.php' ) ) {
			$contains = true;
		}

		$this->assertTrue( $contains );
	}

	/**
	 * Test the Force Restriction constant, send to a specified web address option
	 */
	public function testForceRestrictionWebAddress() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->setSiteVisibilitySettings( $I,
			[
				'visibility' => 'leave-alone',
				'restricted' => 'rsa-redirect-visitor',
			],
			[
				[
					'field' => 'redirect',
					'value' => 'https://www.google.com/',
					'type'  => 'input',
				],
			]
		);

		$I->logout();

		$I->moveTo( '/' );

		usleep( 500 );

		$this->assertTrue( 'https://www.google.com/' === $I->getCurrentUrl() );

		$I->loginAs( 'wpsnapshots' );

		$this->setSiteVisibilitySettings( $I,
			[
				'visibility' => 'leave-alone',
				'restricted' => 'rsa-redirect-visitor',
			],
			[
				[
					'field' => 'redirect',
					'value' => 'https://www.google.com/',
					'type'  => 'input',
				],
				[
					'field' => 'redirect_path',
					'value' => true,
					'type'  => 'checkbox',
				],
			]
		);

		$I->logout();

		$I->moveTo( '/some-post/' );

		usleep( 500 );

		$this->assertTrue( 'https://www.google.com/some-post/' === $I->getCurrentUrl() );
	}

	/**
	 * Test the Force Restriction constant, show a message option
	 */
	public function testForceRestrictionShowMessage() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->setSiteVisibilitySettings( $I,
			[
				'visibility' => 'leave-alone',
				'restricted' => 'rsa-display-message',
			]
		);

		$I->logout();

		$I->moveTo( '/' );

		usleep( 500 );

		$I->seeText( 'Access to this site is restricted' );
	}

	/**
	 * Test the Force Restriction constant, show a page option
	 */
	public function testForceRestrictionPage() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->setSiteVisibilitySettings( $I,
			[
				'visibility' => 'leave-alone',
				'restricted' => 'rsa-unblocked-page',
			],
			[
				[
					'field' => 'rsa_page',
					'value' => '2',
					'type'  => 'select',
				],
			]
		);

		$I->logout();

		$I->moveTo( '/' );

		usleep( 500 );

		$contains = false;

		if ( false !== strpos( $I->getCurrentUrl(), 'sample-page' ) ) {
			$contains = true;
		}

		$this->assertTrue( $contains );
	}

	/**
	 * Test the Force Restriction constant, with the unrestricted IP address option
	 */
	public function testForceRestrictionIpAddress() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->setSiteVisibilitySettings( $I,
			[
				'visibility' => 'leave-alone',
				'restricted' => 'rsa-send-to-login',
			],
			[
				[
					'field' => 'rsa_page',
					'value' => '2',
					'type'  => 'select',
				],
			]
		);

		$I->click( '#rsa_myip' );

		usleep( 100 );

		$I->click( '#addip' );

		usleep( 100 );

		$I->click( '#submit' );
		$I->waitUntilElementVisible( '#wpadminbar' );

		$I->logout();

		$I->moveTo( '/sample-page/' );

		usleep( 500 );

		$contains = false;

		if ( false !== strpos( $I->getCurrentUrl(), 'sample-page' ) ) {
			$contains = true;
		}

		$this->assertTrue( $contains );
	}

}
