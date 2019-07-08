<?php
/**
 * Test case class that provides us with some baseline shortcut functionality
 *
 * @package restricted-site-access
 */

use WPAcceptance\Log;

/**
 * Class extends \WPAcceptance\PHPUnit\TestCase
 */
class TestCase extends \WPAcceptance\PHPUnit\TestCase {

	/**
	 * Network activate the plugin.
	 *
	 * @param WPAcceptance\PHPUnit\Actor $I WP Acceptance actor
	 */
	protected function networkActivate( WPAcceptance\PHPUnit\Actor $I ) {
		$I->moveTo( 'wp-admin/network/plugins.php' );

		$I->waitUntilElementVisible( '#the-list' );

		$link = $I->getElement( '[data-slug="restricted-site-access"] a' );
		$text = $I->getElementInnerText( $link );

		if ( 'Network Activate' === $text ) {
			$I->click( '[data-slug="restricted-site-access"] a' );

			$I->waitUntilElementVisible( '#the-list' );
		}
	}

	/**
	 * Set the proper site visibility settings.
	 *
	 * @param WPAcceptance\PHPUnit\Actor $I WP Acceptance actor
	 * @param array $settings Main settings.
	 * @param array $additional_settings Optional. Additional settings to set.
	 */
	protected function setSiteVisibilitySettings( WPAcceptance\PHPUnit\Actor $I, $settings = [], $additional_settings = [] ) {
		$settings = array_merge( [
			'visibility' => 'blog-public',
			'restricted' => '',
		], $settings );

		$I->moveTo( '/wp-admin/options-reading.php' );
		$I->waitUntilElementVisible( '.option-site-visibility' );

		if ( 'leave-alone' !== $settings['visibility'] ) {
			$I->checkOptions( [ "#{$settings['visibility']}" ] );
		}

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
					case 'checkbox':
						$I->checkOptions( [ "#{$additional_setting['field']}" ] );
						break;
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

		if ( 'leave-alone' !== $settings['visibility'] ) {
			$I->checkOptions( [ "#{$settings['visibility']}" ] );
		}

		if ( 'blog-public' !== $settings['visibility'] && 'leave-alone' !== $settings['visibility'] ) {
			$I->seeElement( '#restricted-site-access' );
		}

		if ( $settings['restricted'] ) {
			$I->checkOptions( [ "#{$settings['restricted']}" ] );
		}

		if ( ! empty( $additional_settings ) ) {
			foreach ( $additional_settings as $additional_setting ) {
				$I->seeElement( "#{$additional_setting['field']}" );

				switch( $additional_setting['type'] ) {
					case 'checkbox':
						$I->checkOptions( [ "#{$additional_setting['field']}" ] );
						break;
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
	 * Set a constant in the config file.
	 *
	 * @param string $name Constant name.
	 * @param string $value Constant value.
	 * @return void
	 */
	protected function setConstant( $name = '', $value = '' ) {
		if ( ! $name || ! $value ) {
			Log::instance()->write( 'Command not run. Make sure a constant name and value is passed in.' );
			return;
		}

		$command = sprintf( 'config set %s %s --raw', $name, $value );
		$output  = $this->runCommand( $command );

		$this->assertIsArray( $output );
		$this->assertContains( 'Success', $output['stdout'] );
	}

}
