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

	test.describe( 'Settings Interface', () => {
		test( 'should display admin bar hiding section', async ( { page } ) => {
			await expect(
				page.getByRole( 'heading', { name: 'Restricted Site Access' } )
			).toBeVisible();
			await expect(
				page.locator( 'th', { hasText: 'Hide admin bar for roles' } )
			).toBeVisible();
		} );

		test( 'should display all user roles as checkboxes', async ( {
			page,
		} ) => {
			expect(
				await page.locator( roleCheckboxes ).count()
			).toBeGreaterThanOrEqual( 5 );

			for ( const role of [
				'administrator',
				'editor',
				'author',
				'contributor',
				'subscriber',
			] ) {
				await expect(
					page.locator( `${ roleCheckboxes }[value="${ role }"]` )
				).toBeVisible();
			}
		} );

		test( 'should display help text', async ( { page } ) => {
			await expect(
				page.locator( 'p.description', {
					hasText:
						'Select user roles for which the WordPress admin bar should be hidden on the frontend',
				} )
			).toBeVisible();
		} );
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

		test( 'should handle multiple role selections', async ( { page } ) => {
			await hideAdminBarForRoles( page, [
				'subscriber',
				'contributor',
				'author',
			] );

			await page.reload();
			await expect(
				page.locator( `${ roleCheckboxes }[value="subscriber"]` )
			).toBeChecked();
			await expect(
				page.locator( `${ roleCheckboxes }[value="contributor"]` )
			).toBeChecked();
			await expect(
				page.locator( `${ roleCheckboxes }[value="author"]` )
			).toBeChecked();
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

		test( 'should show admin bar for other roles on frontend', async ( {
			page,
			requestUtils,
		} ) => {
			await setState( requestUtils, { user_role: 'author' } );

			await page.goto( '/' );

			await expect( page.locator( '#wpadminbar' ) ).toBeVisible();
		} );

		test( 'should show admin bar in admin area for all roles', async ( {
			page,
			requestUtils,
		} ) => {
			await setState( requestUtils, { user_role: 'subscriber' } );

			await page.goto( '/wp-admin/' );

			await expect( page.locator( '#wpadminbar' ) ).toBeVisible();
		} );
	} );

	test.describe( 'Edge Cases', () => {
		test.afterEach( async ( { requestUtils } ) => {
			await setState( requestUtils, { user_role: 'administrator' } );
		} );

		test( 'should handle no roles selected', async ( {
			page,
			requestUtils,
		} ) => {
			await hideAdminBarForRoles( page, [] );
			await setState( requestUtils, { user_role: 'subscriber' } );

			await page.goto( '/' );

			await expect( page.locator( '#wpadminbar' ) ).toBeVisible();
		} );

		test( 'should handle custom user roles', async ( {
			page,
			requestUtils,
		} ) => {
			await setState( requestUtils, {
				add_role: { role: 'custom_role', name: 'Custom Role' },
				user_role: 'custom_role',
			} );
			// Load the screen again so it lists the new role.
			await page.reload();
			await hideAdminBarForRoles( page, [ 'custom_role' ] );

			await page.goto( '/' );

			await expect( page.locator( '#wpadminbar' ) ).toHaveCount( 0 );
		} );

		test( 'should handle users with no roles', async ( {
			page,
			requestUtils,
		} ) => {
			await hideAdminBarForRoles( page, [] );
			await setState( requestUtils, { remove_user_role: true } );

			await page.goto( '/' );

			await expect( page.locator( '#wpadminbar' ) ).toBeVisible();
		} );
	} );

	test.describe( 'Performance', () => {
		test( 'should handle large number of roles efficiently', async ( {
			page,
			requestUtils,
		} ) => {
			for ( let i = 1; i <= 10; i++ ) {
				await setState( requestUtils, {
					add_role: {
						role: `custom_role_${ i }`,
						name: `Custom Role ${ i }`,
					},
				} );
			}
			// Load the screen again so it lists the new roles.
			await page.reload();

			expect(
				await page.locator( roleCheckboxes ).count()
			).toBeGreaterThanOrEqual( 15 );

			await hideAdminBarForRoles( page, [
				'custom_role_1',
				'custom_role_5',
			] );

			await page.reload();
			await expect(
				page.locator( `${ roleCheckboxes }[value="custom_role_1"]` )
			).toBeChecked();
			await expect(
				page.locator( `${ roleCheckboxes }[value="custom_role_5"]` )
			).toBeChecked();
		} );
	} );
} );
