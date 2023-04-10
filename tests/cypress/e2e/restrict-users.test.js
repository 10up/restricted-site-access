describe( 'Handle restricted visitors - Send to login screen', () => {
	it( 'Verify - Visitor is redirected to login screen', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/send-to-login-screen'
		} );
		cy.visit( `sample-page`, {
			failOnStatusCode: false,
		} );

		cy.contains( 'Username or Email Address' );
	} );
} );

describe( 'Handle restricted visitors - Redirect to web address', () => {
	it( 'Verify - Redirection with status code 301', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/redirect-to-web-address/with-301'
		} );
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.status ).to.eq( 301 );
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/' );
		} );
	} );

	it( 'Verify - Redirection with status code 302', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/redirect-to-web-address/with-302'
		} );
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.status ).to.eq( 302 );
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/' );
		} );
	} );

	it( 'Verify - Redirection with status code 307', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/redirect-to-web-address/with-307'
		} );
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.status ).to.eq( 307 );
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/' );
		} );
	} );


	/** Redirection to external URL. */
	it( 'Verify - Redirect to external URL', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/redirect-to-external-url'
		} );
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'https://www.google.com/' );
		} );
	} );

	it( 'Verify - Redirect to external URL with path', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/redirect-to-external-url-with-path'
		} );
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'https://www.google.com/main' );
		} );
	} );

	it( 'Verify - Redirect to external URL to a new path', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/redirect-to-external-url-to-new-path'
		} );
		cy.request( {
			url: '/main',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'https://www.google.com/main' );
		} );
	} );

	it( 'Verify - Redirect to external URL with path to a new path', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/redirect-to-external-with-path-to-aurl-to-new-path'
		} );
		cy.request( {
			url: '/main',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'https://www.google.com/cool/main' );
		} );
	} );
} );

describe( 'Handle restricted visitors - Show simple message', () => {
	it( 'Verify - Show simple message', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/show-simple-message'
		} );
		cy.visit( `sample-page`, {
			failOnStatusCode: false,
		} );

		cy.contains( 'Access to this site is restricted.' );
	} );
} );

describe( 'Cases 1 - 3', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/case-1-to-3'
		} );
	} );

	it( 'Case 1: / to /', () => {
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.status ).to.eq( 200 );
		} );
	} );

	it( 'Case 2: /one to /', () => {
		cy.request( {
			url: '/one',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/' );
		} );
	} );

	it( 'Cases 3: /abcd to /', () => {
		cy.request( {
			url: '/abcd',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/' );
		} );
	} );
} );

describe( 'Cases 4 - 6', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/case-4-to-6'
		} );
	} );

	it( 'Case 4: / to /one', () => {
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one' );
		} );
	} );

	it( 'Case 5: /two to /one', () => {
		cy.request( {
			url: '/two',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one' );
		} );
	} );

	it( 'Case 6: /abcd to /one', () => {
		cy.request( {
			url: '/abcd',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one' );
		} );
	} );
} );

describe( 'Cases 7 - 9', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/case-7-to-9'
		} );
	} );

	it( 'Case 7: / to /blog/category/now', () => {
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/blog/category/now' );
		} );
	} );

	it( 'Case 8: /two to /blog/category/now', () => {
		cy.request( {
			url: '/two',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/blog/category/now' );
		} );
	} );

	it( 'Case 9: /abcd to /blog/category/now', () => {
		cy.request( {
			url: '/abcd',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/blog/category/now' );
		} );
	} );
} );

describe( 'Cases 10 - 12', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/case-10-to-12'
		} );
	} );

	it( 'Case 10: / to /', () => {
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.status ).to.eq( 200 );
		} );
	} );

	it( 'Case 11: /one to /', () => {
		cy.request( {
			url: '/one',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/' );
		} );
	} );

	it( 'Case 12: /abcd to /', () => {
		cy.request( {
			url: '/abcd',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/' );
		} );
	} );
} );

describe( 'Cases 13 - 15', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/case-13-to-15'
		} );
	} );

	it( 'Cases 13: / to /one', () => {
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one' );
		} );
	} );

	it( 'Cases 14: /two to /one', () => {
		cy.request( {
			url: '/two',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one' );
		} );
	} );

	it( 'Cases 15: /abcd to /one', () => {
		cy.request( {
			url: '/abcd',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one' );
		} );
	} );
} );

describe( 'Cases 16 - 19', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/case-16-to-19'
		} );
	} );

	it( 'Case 16: / to /blog/category/now', () => {
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/blog/category/now' );
		} );
	} );

	it( 'Case 17: /one to /blog/category/now', () => {
		cy.request( {
			url: '/one',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/blog/category/now' );
		} );
	} );

	it( 'Case 18: /abcd to /blog/category/now', () => {
		cy.request( {
			url: '/abcd',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/blog/category/now' );
		} );
	} );

	it( 'Case 19: /blog/category/now to /blog/category/now', () => {
		cy.request( {
			url: '/blog/category/now',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/blog/category/now/' );
		} );
	} );
} );

describe( 'Cases 20 - 22 (Redirect to same path enabled)', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/case-20-to-22'
		} );
	} );

	it( 'Case 20: / to /', () => {
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.status ).to.eq( 200 );
		} );
	} );

	it( 'Case 21: /one to /one', () => {
		cy.request( {
			url: '/one',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one/' );
		} );
	} );

	it( 'Case 22: /abcd to /', () => {
		cy.request( {
			url: '/abcd',
			followRedirect: false,
			failOnStatusCode: false,
		} ).then( ( resp ) => {
			expect( resp.status ).to.eq( 404 );
		} );
	} );
} );

describe( 'Cases 23 - 25 (Redirect to same path enabled)', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/case-23-to-25'
		} );
	} );

	it( 'Case 23: / to /one', () => {
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one/' );
		} );
	} );

	it( 'Case 24: /one to /one/one', () => {
		cy.request( {
			url: '/one',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one/one' );
		} );

	} );

	it( 'Case 25: /abcd to /one/abcd', () => {
		cy.request( {
			url: '/abcd',
			followRedirect: false,
			failOnStatusCode: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one/abcd' );
		} );
	} );
} );

describe( 'Cases 26 - 28 (Redirect to same path enabled)', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/case-26-to-28'
		} );
	} );

	it( 'Case 26: / to /blog/category/now', () => {
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/blog/category/now/' );
		} );
	} );

	it( 'Case 27: /one to /blog/category/now/one', () => {
		cy.request( {
			url: '/one',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/blog/category/now/one' );
		} );

	} );

	it( 'Case 28: /abcd to /blog/category/now/abcd', () => {
		cy.request( {
			url: '/abcd',
			followRedirect: false,
			failOnStatusCode: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/blog/category/now/abcd' );
		} );
	} );
} );

describe( 'Cases 29 - 31 (Redirect to same path enabled)', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/case-29-to-31'
		} );
	} );

	it( 'Case 29: / to /', () => {
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.status ).to.eq( 200 );
		} );
	} );

	it( 'Case 30: /one to /one', () => {
		cy.request( {
			url: '/one',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one/' );
		} );

	} );

	it( 'Case 31: /abcd to /abcd', () => {
		cy.visit( '/abcd', { failOnStatusCode: false } )
		cy.url().should( 'contain', '/abcd' );
	} );
} );

describe( 'Cases 32 - 34 (Redirect to same path enabled)', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/case-32-to-34'
		} );
	} );

	it( 'Case 32: / to /one', () => {
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one/' );
		} );
	} );

	it( 'Case 33: /two to /one/two', () => {
		cy.request( {
			url: '/two',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one/two' );
		} );

	} );

	it( 'Case 34: /abcd to /one/abcd', () => {
		cy.request( {
			url: '/abcd',
			followRedirect: false,
			failOnStatusCode: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/one/abcd' );
		} );
	} );
} );

