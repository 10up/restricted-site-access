/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Internal dependencies
 */
import { restrictNetwork } from '../utils/helpers';

const addresses = [ { name: 'IPv4 single address', ip: '172.256.23.4' } ];

test.describe( 'Add invalid IPv4, IPv6 addresses', () => {
	test.beforeAll( async ( { requestUtils } ) => {
		await restrictNetwork( requestUtils );
	} );

	test.beforeEach( async ( { admin } ) => {
		await admin.visitAdminPage( 'network/settings.php' );
	} );

	for ( const { name, ip } of addresses ) {
		test( `Test error for invalid ${ name }`, async ( { page } ) => {
			const row = page
				.locator( '#ip_list .rsa_unrestricted_ip_row' )
				.last();

			await row.locator( '.ip.code' ).fill( ip );
			// Moving to the comment field triggers the plugin's check of the address.
			await row.locator( '.newipcomment' ).fill( `Invalid ${ name }` );
			await page.locator( '#addip' ).click();

			await expect(
				page.locator( '#rsa-error-container' )
			).toContainText( 'The IP entered is invalid.' );
		} );
	}
} );
