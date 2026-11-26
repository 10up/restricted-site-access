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

// Every request in this file comes from a logged-out visitor.
test.use( { storageState: { cookies: [], origins: [] } } );

test.describe( 'Handle restricted visitors - Send to login screen', () => {
	test( 'Verify - Visitor is redirected to login screen', async ( {
		page,
		requestUtils,
	} ) => {
		await restrictNetwork( requestUtils, { approach: 1 } );

		await page.goto( 'sample-page' );

		await expect(
			page.getByText( 'Username or Email Address' )
		).toBeVisible();
	} );
} );

test.describe( 'Handle restricted visitors - Redirect to web address', () => {
	for ( const code of [ 301, 302, 307 ] ) {
		test( `Verify - Redirection with status code ${ code }`, async ( {
			baseURL,
			playwright,
			requestUtils,
		} ) => {
			const target = new URL( 'page-to-redirect/', baseURL ).href;
			await restrictNetwork( requestUtils, {
				approach: 2,
				head_code: code,
				redirect_url: target,
			} );
			const visitor = await visitorRequest( playwright, baseURL );

			const response = await visitor.get( 'sample-page', {
				maxRedirects: 0,
			} );

			expect( response.status() ).toBe( code );
			expect( redirectTarget( response ) ).toBe( target );
			await visitor.dispose();
		} );
	}

	const external = [
		{
			title: 'Redirect to external URL',
			options: { redirect_url: 'https://www.google.com/' },
			path: '/',
			expected: 'https://www.google.com/',
		},
		{
			title: 'Redirect to external URL with path',
			options: { redirect_url: 'https://www.google.com/main' },
			path: '/',
			expected: 'https://www.google.com/main',
		},
		{
			title: 'Redirect to external URL to a new path',
			options: {
				redirect_url: 'https://www.google.com/',
				redirect_path: 1,
			},
			path: '/main',
			expected: 'https://www.google.com/main',
		},
		{
			title: 'Redirect to external URL with path to a new path',
			options: {
				redirect_url: 'https://www.google.com/cool',
				redirect_path: 1,
			},
			path: '/main',
			expected: 'https://www.google.com/cool/main',
		},
	];

	for ( const { title, options, path, expected } of external ) {
		test( `Verify - ${ title }`, async ( {
			baseURL,
			playwright,
			requestUtils,
		} ) => {
			await restrictNetwork( requestUtils, {
				approach: 2,
				head_code: 307,
				...options,
			} );
			const visitor = await visitorRequest( playwright, baseURL );

			const response = await visitor.get( path, { maxRedirects: 0 } );

			expect( redirectTarget( response ) ).toBe( expected );
			await visitor.dispose();
		} );
	}
} );

test.describe( 'Handle restricted visitors - Show simple message', () => {
	test( 'Verify - Show simple message', async ( { page, requestUtils } ) => {
		await restrictNetwork( requestUtils, {
			approach: 3,
			allowed: [ '172.13.24.5' ],
		} );

		await page.goto( 'sample-page' );

		await expect(
			page.getByText( 'Access to this site is restricted.' )
		).toBeVisible();
	} );
} );

/*
 * Redirect to a web address: where each request ends up, for each redirect URL
 * (absolute or relative to the site) with "Redirect to the same path" off and on.
 * Without `to`, the response `status` is checked instead; `browserUrl` cases
 * load the path in the browser and check the URL it ends on.
 */
const redirectCases: {
	title: string;
	redirectUrl: string;
	redirectPath: 0 | 1;
	cases: {
		title: string;
		path: string;
		to?: string;
		status?: number;
		browserUrl?: string;
	}[];
}[] = [
	{
		title: 'Cases 1 - 3',
		redirectUrl: '{site}',
		redirectPath: 0,
		cases: [
			{ title: 'Case 1: / to /', path: '/', status: 200 },
			{ title: 'Case 2: /one to /', path: '/one', to: '/' },
			{ title: 'Cases 3: /abcd to /', path: '/abcd', to: '/' },
		],
	},
	{
		title: 'Cases 4 - 6',
		redirectUrl: '{site}one',
		redirectPath: 0,
		cases: [
			{ title: 'Case 4: / to /one', path: '/', to: '/one' },
			{ title: 'Case 5: /two to /one', path: '/two', to: '/one' },
			{ title: 'Case 6: /abcd to /one', path: '/abcd', to: '/one' },
		],
	},
	{
		title: 'Cases 7 - 9',
		redirectUrl: '{site}blog/category/now',
		redirectPath: 0,
		cases: [
			{
				title: 'Case 7: / to /blog/category/now',
				path: '/',
				to: '/blog/category/now',
			},
			{
				title: 'Case 8: /two to /blog/category/now',
				path: '/two',
				to: '/blog/category/now',
			},
			{
				title: 'Case 9: /abcd to /blog/category/now',
				path: '/abcd',
				to: '/blog/category/now',
			},
		],
	},
	{
		title: 'Cases 10 - 12',
		redirectUrl: '/',
		redirectPath: 0,
		cases: [
			{ title: 'Case 10: / to /', path: '/', status: 200 },
			{ title: 'Case 11: /one to /', path: '/one', to: '/' },
			{ title: 'Case 12: /abcd to /', path: '/abcd', to: '/' },
		],
	},
	{
		title: 'Cases 13 - 15',
		redirectUrl: '/one',
		redirectPath: 0,
		cases: [
			{ title: 'Cases 13: / to /one', path: '/', to: '/one' },
			{ title: 'Cases 14: /two to /one', path: '/two', to: '/one' },
			{ title: 'Cases 15: /abcd to /one', path: '/abcd', to: '/one' },
		],
	},
	{
		title: 'Cases 16 - 19',
		redirectUrl: '/blog/category/now',
		redirectPath: 0,
		cases: [
			{
				title: 'Case 16: / to /blog/category/now',
				path: '/',
				to: '/blog/category/now',
			},
			{
				title: 'Case 17: /one to /blog/category/now',
				path: '/one',
				to: '/blog/category/now',
			},
			{
				title: 'Case 18: /abcd to /blog/category/now',
				path: '/abcd',
				to: '/blog/category/now',
			},
			{
				title: 'Case 19: /blog/category/now to /blog/category/now',
				path: '/blog/category/now',
				to: '/blog/category/now/',
			},
		],
	},
	{
		title: 'Cases 20 - 22 (Redirect to same path enabled)',
		redirectUrl: '{site}',
		redirectPath: 1,
		cases: [
			{ title: 'Case 20: / to /', path: '/', status: 200 },
			{ title: 'Case 21: /one to /one', path: '/one', to: '/one/' },
			{ title: 'Case 22: /abcd to /', path: '/abcd', status: 404 },
		],
	},
	{
		title: 'Cases 23 - 25 (Redirect to same path enabled)',
		redirectUrl: '{site}one',
		redirectPath: 1,
		cases: [
			{ title: 'Case 23: / to /one', path: '/', to: '/one/' },
			{
				title: 'Case 24: /one to /one/one',
				path: '/one',
				to: '/one/one',
			},
			{
				title: 'Case 25: /abcd to /one/abcd',
				path: '/abcd',
				to: '/one/abcd',
			},
		],
	},
	{
		title: 'Cases 26 - 28 (Redirect to same path enabled)',
		redirectUrl: '/blog/category/now',
		redirectPath: 1,
		cases: [
			{
				title: 'Case 26: / to /blog/category/now',
				path: '/',
				to: '/blog/category/now/',
			},
			{
				title: 'Case 27: /one to /blog/category/now/one',
				path: '/one',
				to: '/blog/category/now/one',
			},
			{
				title: 'Case 28: /abcd to /blog/category/now/abcd',
				path: '/abcd',
				to: '/blog/category/now/abcd',
			},
		],
	},
	{
		title: 'Cases 29 - 31 (Redirect to same path enabled)',
		redirectUrl: '/',
		redirectPath: 1,
		cases: [
			{ title: 'Case 29: / to /', path: '/', status: 200 },
			{ title: 'Case 30: /one to /one', path: '/one', to: '/one/' },
			{
				title: 'Case 31: /abcd to /abcd',
				path: '/abcd',
				browserUrl: '/abcd',
			},
		],
	},
	{
		title: 'Cases 32 - 34 (Redirect to same path enabled)',
		redirectUrl: '/one',
		redirectPath: 1,
		cases: [
			{ title: 'Case 32: / to /one', path: '/', to: '/one/' },
			{
				title: 'Case 33: /two to /one/two',
				path: '/two',
				to: '/one/two',
			},
			{
				title: 'Case 34: /abcd to /one/abcd',
				path: '/abcd',
				to: '/one/abcd',
			},
		],
	},
];

for ( const group of redirectCases ) {
	test.describe( group.title, () => {
		test.beforeAll( async ( { baseURL, requestUtils } ) => {
			await requestUtils
				.rest( {
					method: 'POST',
					path: '/wp/v2/categories',
					data: { name: 'Now' },
				} )
				.catch( () => {
					// The category already exists.
				} );

			await restrictNetwork( requestUtils, {
				approach: 2,
				head_code: 307,
				redirect_url: group.redirectUrl.replace(
					'{site}',
					new URL( '/', baseURL ).href
				),
				redirect_path: group.redirectPath,
			} );
		} );

		for ( const { title, path, to, status, browserUrl } of group.cases ) {
			if ( undefined !== browserUrl ) {
				test( title, async ( { page } ) => {
					await page.goto( path );

					expect( page.url() ).toContain( browserUrl );
				} );
				continue;
			}

			test( title, async ( { baseURL, playwright } ) => {
				const visitor = await visitorRequest( playwright, baseURL );

				const response = await visitor.get( path, { maxRedirects: 0 } );

				if ( undefined !== to ) {
					expect( redirectTarget( response ) ).toBe(
						new URL( to, baseURL ).href
					);
				} else {
					expect( response.status() ).toBe( status );
				}
				await visitor.dispose();
			} );
		}
	} );
}

test.describe( 'Handle restricted visitors - REST API requests', () => {
	test.beforeAll( async ( { requestUtils } ) => {
		await restrictNetwork( requestUtils, { approach: 1 } );
	} );

	test( 'Verify - REST API request receives a 401 JSON error', async ( {
		baseURL,
		playwright,
	} ) => {
		const visitor = await visitorRequest( playwright, baseURL );

		const response = await visitor.get( '/wp-json/wp/v2/posts', {
			maxRedirects: 0,
		} );

		expect( response.status() ).toBe( 401 );
		expect( response.headers()[ 'content-type' ] ).toContain(
			'application/json'
		);
		expect( response.headers()[ 'cache-control' ] ).toContain( 'no-store' );
		const body = await response.json();
		expect( body.code ).toBe( 'rest_not_logged_in' );
		expect( body.data.status ).toBe( 401 );
		await visitor.dispose();
	} );

	test( 'Verify - Plain permalink REST API request receives a 401 JSON error', async ( {
		baseURL,
		playwright,
	} ) => {
		const visitor = await visitorRequest( playwright, baseURL );

		const response = await visitor.get( '/?rest_route=/wp/v2/posts', {
			maxRedirects: 0,
		} );

		expect( response.status() ).toBe( 401 );
		expect( ( await response.json() ).code ).toBe( 'rest_not_logged_in' );
		await visitor.dispose();
	} );
} );
