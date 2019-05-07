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
	 * @param \WPAcceptance\PHPUnit\Actor $I WP Acceptance actor
	 */
	protected function logOut( \WPAcceptance\PHPUnit\Actor $I ) {
		$I->hover( '#wp-admin-bar-my-account a' );

		usleep( 500 );

		$I->waitUntilElementVisible( '#wp-admin-bar-logout' );

		$I->click( '#wp-admin-bar-logout a' );

		$I->waitUntilElementVisible( '#loginform' );
	}

	/**
	 * Set the proper site visibility settings.
	 *
	 * @param \WPAcceptance\PHPUnit\Actor $I WP Acceptance actor
	 * @param string $visibility_setting Visibility setting to set
	 * @param string $restricted_setting Restricted setting to set
	 * @param array $additional_settings Optional. Additional settings to set.
	 */
	protected function setSiteVisibiltySettings( \WPAcceptance\PHPUnit\Actor $I, $visibility_setting = 'blog-public', $restricted_setting = '', $additional_settings = [] ) {
		$I->moveTo( '/wp-admin/options-reading.php' );
		$I->waitUntilElementVisible( '.option-site-visibility' );

		$I->checkOptions( [ "#{$visibility_setting}" ] );

		if ( 'blog-public' !== $visibility_setting ) {
			$I->seeElement( '.rsa-setting_settings_field_handling' );
		}

		if ( $restricted_setting ) {
			$I->checkOptions( [ "#{$restricted_setting}" ] );
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
