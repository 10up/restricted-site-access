<?php
/**
 * Multisite constant test class
 *
 * @package restricted-site-access
 */

/**
 * PHPUnit test class
 */
class MultiSiteConstantTest extends \TestCase {

	/**
	 * Test the Forbid Restriction constant
	 */
	public function testForbidRestriction() {
		$this->setConstant( 'RSA_FORBID_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->networkActivate( $I );

		$I->moveTo( 'wp-admin/network/settings.php' );
		$I->waitUntilElementVisible( '.notice-warning' );

		$I->seeText( 'Site visibility settings are currently enforced by code configuration', '.notice-warning' );

		$I->cannotInteractWithField( '#blog-restricted' );
	}

	/**
	 * Test the Forbid Restriction constant, enforced across the network
	 */
	public function testForbidRestrictionEnforced() {
		$this->setConstant( 'RSA_FORBID_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->networkActivate( $I );

		$this->setMultiSiteVisibilitySettings( $I,
			[
				'mode'       => 'rsa-mode-enforce',
				'visibility' => 'leave-alone',
			]
		);

		$I->moveTo( '/wp-admin/options-reading.php' );

		$I->seeText( 'Site visibility settings are currently enforced across all sites on the network', '.notice-warning' );
		$I->cannotInteractWithField( '#blog-restricted' );
	}

	/**
	 * Test the Force Restriction constant
	 */
	public function testForceRestriction() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->networkActivate( $I );

		$I->moveTo( 'wp-admin/network/settings.php' );
		$I->waitUntilElementVisible( '.notice-warning' );

		$I->seeText( 'Site visibility settings are currently enforced by code configuration', '.notice-warning' );

		$I->seeCheckboxIsChecked( '#blog-restricted' );
		$I->cannotInteractWithField( '#blog-restricted' );
	}

	/**
	 * Test a single site inherits multisite settings.
	 */
	public function testSingleSiteEnforced() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->networkActivate( $I );

		$this->setMultiSiteVisibilitySettings( $I,
			[
				'mode'       => 'rsa-mode-enforce',
				'visibility' => 'leave-alone',
				'restricted' => 'rsa-send-to-login',
			]
		);

		$I->moveTo( '/wp-admin/options-reading.php' );

		$I->seeText( 'Site visibility settings are currently enforced across all sites on the network', '.rsa-network-enforced-warning .notice-warning' );
		$I->seeCheckboxIsChecked( '#blog-restricted' );
		$I->cannotInteractWithField( '#blog-restricted' );
	}

	/**
	 * Test restricted access, send to the login screen option
	 */
	public function testRestrictLoginScreen() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->networkActivate( $I );

		$this->setMultiSiteVisibilitySettings( $I,
			[
				'mode'       => 'rsa-mode-enforce',
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
	 * Test restricted access, send to a specified web address option
	 */
	public function testRestrictWebAddress() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->networkActivate( $I );

		$this->setMultiSiteVisibilitySettings( $I,
			[
				'mode'       => 'rsa-mode-enforce',
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

		$this->setMultiSiteVisibilitySettings( $I,
			[
				'mode'       => 'rsa-mode-enforce',
				'visibility' => 'blog-restricted',
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
	 * Test restricted access, show a message option
	 */
	public function testRestrictMessage() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->networkActivate( $I );

		$this->setMultiSiteVisibilitySettings( $I,
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
	 * Test restricted access with an unrestricted IP address
	 */
	public function testRestrictIpAddress() {
		$this->setConstant( 'RSA_FORCE_RESTRICTION', 'true' );

		$I = $this->openBrowserPage();

		$I->loginAs( 'wpsnapshots' );

		$this->networkActivate( $I );

		$this->setMultiSiteVisibilitySettings( $I,
			[
				'visibility' => 'leave-alone',
				'restricted' => 'rsa-send-to-login',
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
