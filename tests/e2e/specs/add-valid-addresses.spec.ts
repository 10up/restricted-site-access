/**
 * External dependencies
 */
import type { Page } from '@playwright/test';

/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Internal dependencies
 */
import { restrictNetwork } from '../utils/helpers';

const addresses = [
	{ ip: '172.10.23.4', label: 'IPv4 single address' },
	{ ip: '182.10.*.*', label: 'IPv4 pattern' },
	{ ip: '2001:0db8:3333:4444:0000:0000:0000:0000/64', label: 'IPv6 subnet' },
];

/**
 * Types an address into the last (empty) allow-list row and waits for the
 * plugin to accept it, then adds a new empty row.
 *
 * @param page  Page on the network settings screen.
 * @param ip    Address, range or pattern.
 * @param label Comment for the entry.
 */
async function addValidIp( page: Page, ip: string, label: string ) {
	const row = page.locator( '#ip_list .rsa_unrestricted_ip_row' ).last();
	const check = page.waitForResponse(
		( response ) =>
			response.url().includes( 'admin-ajax.php' ) &&
			response.request().postData()?.includes( 'action=rsa_ip_check' ) ===
				true
	);

	await row.locator( '.ip.code' ).fill( ip );
	// Moving to the comment field triggers the plugin's check of the address.
	await row.locator( '.newipcomment' ).fill( label );
	expect( ( await ( await check ).json() ).success ).toBe( true );

	await page.locator( '#addip' ).click();
}

test.describe( 'Add & save valid IPv4, IPv6 addresses', () => {
	test.beforeAll( async ( { requestUtils } ) => {
		await restrictNetwork( requestUtils );
	} );

	test.afterAll( async ( { requestUtils } ) => {
		await restrictNetwork( requestUtils );
	} );

	test( 'Validate each saved IP addresses', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'network/settings.php' );

		for ( const { ip, label } of addresses ) {
			await addValidIp( page, ip, label );
		}
		await page.locator( '#submit' ).click();
		await page.waitForURL( /updated=true/ );

		await admin.visitAdminPage( 'network/settings.php' );
		const rows = page.locator( '#ip_list .rsa_unrestricted_ip_row' );

		for ( const [ index, { ip, label } ] of addresses.entries() ) {
			await expect(
				rows.nth( index ).locator( 'input' ).nth( 0 )
			).toHaveValue( ip );
			await expect(
				rows.nth( index ).locator( 'input' ).nth( 1 )
			).toHaveValue( label );
		}

		// Saving the screen keeps the network's mode and visibility as they were.
		await expect( page.locator( '#rsa-mode-enforce' ) ).toBeChecked();
		await expect( page.locator( '#blog-restricted' ) ).toBeChecked();
	} );
} );
