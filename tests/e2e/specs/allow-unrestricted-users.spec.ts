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
} from '../utils/helpers';

const allowedIp = '172.13.24.5';
const otherIp = '172.13.24.6';

test.describe( 'Handle restricted visitors - Show simple message', () => {
	test( 'Show simple message - Visitor can still access requested page ', async ( {
		baseURL,
		playwright,
		requestUtils,
	} ) => {
		await restrictNetwork( requestUtils, {
			approach: 3,
			allowed: [ allowedIp ],
		} );
		// The page's canonical URL, which WordPress redirects to once the visitor is let in.
		const pageUrl = new URL( 'sample-page/', baseURL ).href;

		// Controls: without an IP, or from an IP not on the list, the visitor is turned away.
		for ( const headers of [ {}, { 'X-Forwarded': otherIp } ] ) {
			const visitor = await visitorRequest(
				playwright,
				baseURL,
				headers
			);
			const blockedResponse = await visitor.get( 'sample-page', {
				maxRedirects: 0,
			} );
			expect( blockedResponse.status() ).toBe( 403 );
			expect( redirectTarget( blockedResponse ) ).toBe( '' );
			await visitor.dispose();
		}

		const allowed = await visitorRequest( playwright, baseURL, {
			'X-Forwarded': allowedIp,
		} );
		const response = await allowed.get( 'sample-page', {
			maxRedirects: 0,
		} );

		expect( redirectTarget( response ) ).toBe( pageUrl );
		await allowed.dispose();
	} );
} );
