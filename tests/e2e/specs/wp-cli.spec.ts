/**
 * The `wp rsa` WP-CLI commands, run in the test environment's cli container.
 */

/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Internal dependencies
 */
import {
	redirectTarget,
	restrictNetwork,
	visitorRequest,
	wpCli,
} from '../utils/helpers';

/**
 * Reads the main site's saved plugin options.
 *
 * @return Saved options.
 */
function siteOptions(): { allowed: string[]; comment: string[] } {
	return JSON.parse(
		wpCli( [ 'option', 'get', 'rsa_options', '--format=json' ] )
	);
}

test.describe( 'WP-CLI', () => {
	test.beforeEach( async ( { requestUtils } ) => {
		await restrictNetwork( requestUtils );
	} );

	test.afterAll( async ( { requestUtils } ) => {
		await restrictNetwork( requestUtils );
	} );

	test( 'ip-add saves an address with its label, refuses an invalid one, and ip-remove removes it', () => {
		// Start from the site's default settings.
		try {
			wpCli( [ 'option', 'delete', 'rsa_options' ] );
		} catch {
			// Nothing saved yet.
		}

		wpCli( [ 'rsa', 'ip-add', '203.0.113.5=Office' ] );

		let options = siteOptions();
		const index = options.allowed.indexOf( '203.0.113.5' );
		expect( index ).toBeGreaterThan( -1 );
		expect( options.comment[ index ] ).toBe( 'Office' );

		expect( () => wpCli( [ 'rsa', 'ip-add', 'not-an-ip' ] ) ).toThrow();
		expect( siteOptions().allowed ).toEqual( options.allowed );

		wpCli( [ 'rsa', 'ip-remove', '203.0.113.5' ] );

		options = siteOptions();
		expect( options.allowed ).not.toContain( '203.0.113.5' );
	} );

	test( 'set-mode changes how visitors are handled, and disable lifts the restriction', async ( {
		baseURL,
		playwright,
	} ) => {
		const visitor = await visitorRequest( playwright, baseURL );

		wpCli( [
			'rsa',
			'set-mode',
			'message',
			'--text=Closed for maintenance',
			'--network',
		] );

		const message = await visitor.get( 'sample-page/', {
			maxRedirects: 0,
		} );
		expect( message.status() ).toBe( 403 );
		expect( await message.text() ).toContain( 'Closed for maintenance' );

		wpCli( [ 'rsa', 'set-mode', 'disable', '--network' ] );

		const open = await visitor.get( 'sample-page/', { maxRedirects: 0 } );
		expect( open.status() ).toBe( 200 );
		expect( redirectTarget( open ) ).toBe( '' );

		await visitor.dispose();
	} );
} );
