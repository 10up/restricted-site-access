/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Internal dependencies
 */
import { restrictNetwork } from '../utils/helpers';

const addresses = [
	{ name: 'IPv4 single address', ip: '172.256.23.4' },
	{ name: 'IPv4 subnet', ip: '172.10.23.4/33' },
	{ name: 'IPv4 pattern', ip: '10.256.*' },
	{
		name: 'IPv6 single address',
		ip: '2001:db8:3333:4444:5555:6666:7777:888g',
	},
	{ name: 'IPv6 subnet', ip: '2001:0db8:3333:4444:0000:0000:0000:0000/129' },
	{ name: 'IPv6 pattern', ip: '2001:0db8:3333:4444:0000:0000:0000:::' },
];

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
