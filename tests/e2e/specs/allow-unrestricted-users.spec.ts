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

// How a visitor without the allowed IP is turned away, for each approach.
const approaches = [
	{
		title: 'Show simple message',
		options: { approach: 3 },
		blocked: { status: 403, to: '' },
	},
	{
		title: 'Redirect to web address',
		options: { approach: 2, redirect_url: '{site}page-to-redirect/' },
		blocked: { status: 302, to: '{site}page-to-redirect/' },
	},
	{
		title: 'Send to login screen',
		options: { approach: 1, redirect_url: '{site}page-to-redirect/' },
		blocked: {
			status: 302,
			to: '{site}wp-login.php?redirect_to=%2Fsample-page',
		},
	},
];

test.describe( 'Handle restricted visitors - Show simple message', () => {
	for ( const { title, options, blocked } of approaches ) {
		test( `${ title } - Visitor can still access requested page `, async ( {
			baseURL,
			playwright,
			requestUtils,
		} ) => {
			const site = new URL( '/', baseURL ).href;
			await restrictNetwork( requestUtils, {
				...options,
				redirect_url: ( options.redirect_url ?? '' ).replace(
					'{site}',
					site
				),
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
				expect( blockedResponse.status() ).toBe( blocked.status );
				expect( redirectTarget( blockedResponse ) ).toBe(
					blocked.to.replace( '{site}', site )
				);
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
	}
} );
