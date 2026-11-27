/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Internal dependencies
 */
import { wpCli } from '../utils/helpers';

test.describe( 'Activation/Deactivation', () => {
	test.afterAll( () => {
		wpCli( [
			'plugin',
			'activate',
			'restricted-site-access',
			'--network',
		] );
	} );

	test( 'Plugin deactivation', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'network/plugins.php' );
		const row = page.locator( 'tr[data-slug="restricted-site-access"]' );

		await row.locator( '.deactivate > a' ).click();
		await page.locator( '#rsa-user-message' ).fill( 'I understand' );
		await page
			.getByRole( 'button', { name: 'Network Disable Plugin' } )
			.click();

		await expect( row.locator( '.activate > a' ) ).toBeVisible();
	} );
} );
