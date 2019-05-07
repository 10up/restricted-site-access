<?php
/**
 * Test case class that provides us with some baseline shortcut functionality
 *
 * @package restricted-site-access
 */

/**
 * Class extends \WPAcceptance\PHPUnit\TestCase
 */
class TestCase extends \WPAcceptance\PHPUnit\TestCase {

	/**
	 * Log the current user out.
	 *
	 * @param WPAcceptance\PHPUnit\Actor $I WP Acceptance actor
	 */
	protected function logOut( WPAcceptance\PHPUnit\Actor $I ) {
		$I->moveTo( 'wp-login.php?action=logout' );

		$I->waitUntilElementVisible( '#error-page' );

		$I->click( '#error-page a' );

		$I->waitUntilElementVisible( '#loginform' );
	}

	/**
	 * Network activate the plugin.
	 *
	 * @param WPAcceptance\PHPUnit\Actor $I WP Acceptance actor
	 */
	protected function networkActivate( WPAcceptance\PHPUnit\Actor $I ) {
		$I->moveTo( 'wp-admin/network/plugins.php' );

		$I->waitUntilElementVisible( '#the-list' );

		$I->click( '[data-slug="restricted-site-access"] a' );

		$I->waitUntilElementVisible( '#the-list' );
	}

	/**
	 * Set the proper site visibility settings.
	 *
	 * @param WPAcceptance\PHPUnit\Actor $I WP Acceptance actor
	 * @param array $settings Main settings.
	 * @param array $additional_settings Optional. Additional settings to set.
	 */
	protected function setSiteVisibiltySettings( WPAcceptance\PHPUnit\Actor $I, $settings = [], $additional_settings = [] ) {
		$settings = array_merge( [
			'visibility' => 'blog-public',
			'restricted' => '',
		], $settings );

		$I->moveTo( '/wp-admin/options-reading.php' );
		$I->waitUntilElementVisible( '.option-site-visibility' );

		$I->checkOptions( [ "#{$settings['visibility']}" ] );

		if ( 'blog-public' !== $settings['visibility'] ) {
			$I->seeElement( '.rsa-setting_settings_field_handling' );
		}

		if ( $settings['restricted'] ) {
			$I->checkOptions( [ "#{$settings['restricted']}" ] );
		}

		if ( ! empty( $additional_settings ) ) {
			foreach ( $additional_settings as $additional_setting ) {
				$I->seeElement( "#{$additional_setting['field']}" );

				switch( $additional_setting['type'] ) {
					case 'input':
						$I->fillField( "#{$additional_setting['field']}", $additional_setting['value'] );
						break;
					case 'select':
						$I->selectOptionByValue( "#{$additional_setting['field']}", $additional_setting['value'] );
						break;
				}
			}
		}

		$I->click( '#submit' );
		$I->waitUntilElementVisible( '#wpadminbar' );
	}

	/**
	 * Set the proper multisite visibility settings.
	 *
	 * @param WPAcceptance\PHPUnit\Actor $I WP Acceptance actor
	 * @param array $settings Main settings.
	 * @param array $additional_settings Optional. Additional settings to set.
	 */
	protected function setMultiSiteVisibilitySettings( WPAcceptance\PHPUnit\Actor $I, $settings = [], $additional_settings = [] ) {
		$settings = array_merge( [
			'mode'       => 'rsa-mode-enforce',
			'visibility' => 'blog-public',
			'restricted' => '',
		], $settings );

		$I->moveTo( 'wp-admin/network/settings.php' );
		$I->waitUntilElementVisible( '#restricted-site-access-mode' );

		$I->checkOptions( [ "#{$settings['mode']}" ] );
		$I->checkOptions( [ "#{$settings['visibility']}" ] );

		if ( 'blog-public' !== $settings['visibility'] ) {
			$I->seeElement( '#restricted-site-access' );
		}

		if ( $settings['restricted'] ) {
			$I->checkOptions( [ "#{$settings['restricted']}" ] );
		}

		if ( ! empty( $additional_settings ) ) {
			foreach ( $additional_settings as $additional_setting ) {
				$I->seeElement( "#{$additional_setting['field']}" );

				switch( $additional_setting['type'] ) {
					case 'input':
						$I->fillField( "#{$additional_setting['field']}", $additional_setting['value'] );
						break;
					case 'select':
						$I->selectOptionByValue( "#{$additional_setting['field']}", $additional_setting['value'] );
						break;
				}
			}
		}

		$I->click( '#submit' );
		$I->waitUntilElementVisible( '#wpadminbar' );
	}

}
