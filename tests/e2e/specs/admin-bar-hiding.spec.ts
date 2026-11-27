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
import { setState } from '../utils/helpers';

const roleCheckboxes = 'input[name="rsa_options[hide_admin_bar_roles][]"]';

/**
 * Saves the Reading settings screen.
 *
 * @param page Page on the Reading settings screen.
 */
async function saveSettings( page: Page ) {
	await page.locator( '#submit' ).click();
	await expect( page.getByText( 'Settings saved.' ) ).toBeVisible();
}

/**
 * Selects exactly the given roles for hiding the admin bar, and saves.
 *
 * @param page  Page on the Reading settings screen.
 * @param roles Role slugs.
 */
async function hideAdminBarForRoles( page: Page, roles: string[] ) {
	for ( const checkbox of await page.locator( roleCheckboxes ).all() ) {
		await checkbox.uncheck();
	}
	for ( const role of roles ) {
		await page.locator( `${ roleCheckboxes }[value="${ role }"]` ).check();
	}
	await saveSettings( page );
}

test.describe( 'Admin Bar Hiding Feature', () => {
	test.beforeAll( async ( { requestUtils } ) => {
		// Per-site settings, so the Reading screen shows the plugin's fields.
		await setState( requestUtils, { rsa_mode: 'default' } );
	} );

	test.beforeEach( async ( { admin } ) => {
		await admin.visitAdminPage( 'options-reading.php' );
	} );

	test.describe( 'Settings Persistence', () => {
		test( 'should save selected roles', async ( { page } ) => {
			await hideAdminBarForRoles( page, [ 'subscriber', 'contributor' ] );

			await page.reload();
			await expect(
				page.locator( `${ roleCheckboxes }[value="subscriber"]` )
			).toBeChecked();
			await expect(
				page.locator( `${ roleCheckboxes }[value="contributor"]` )
			).toBeChecked();
			await expect(
				page.locator( `${ roleCheckboxes }[value="author"]` )
			).not.toBeChecked();
		} );

		test( 'should clear unselected roles', async ( { page } ) => {
			await hideAdminBarForRoles( page, [ 'subscriber', 'contributor' ] );

			await page
				.locator( `${ roleCheckboxes }[value="subscriber"]` )
				.uncheck();
			await page
				.locator( `${ roleCheckboxes }[value="contributor"]` )
				.uncheck();
			await saveSettings( page );

			await page.reload();
			await expect(
				page.locator( `${ roleCheckboxes }[value="subscriber"]` )
			).not.toBeChecked();
			await expect(
				page.locator( `${ roleCheckboxes }[value="contributor"]` )
			).not.toBeChecked();
		} );
	} );

	test.describe( 'Frontend Behavior', () => {
		test.beforeEach( async ( { page } ) => {
			await hideAdminBarForRoles( page, [ 'subscriber' ] );
		} );

		test.afterEach( async ( { requestUtils } ) => {
			await setState( requestUtils, { user_role: 'administrator' } );
		} );

		test( 'should hide admin bar for subscriber on frontend', async ( {
			page,
			requestUtils,
		} ) => {
			await setState( requestUtils, { user_role: 'subscriber' } );

			await page.goto( '/' );

			await expect( page.locator( '#wpadminbar' ) ).toHaveCount( 0 );
		} );
	} );
} );
