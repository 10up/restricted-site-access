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
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/?rsa_redirect=yes' );
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
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/?rsa_redirect=yes' );
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
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/?rsa_redirect=yes' );
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

	/** Redirection to internal URL. */
	it( 'Verify - Redirect to internal URL with path', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/redirect-to-internal-url'
		} );
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/?rsa_redirect=yes' );
		} );
	} );

	it( 'Verify - Redirect to internal URL with path to a new path', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/redirect-to-internal-url-with-path-to-a-new-path'
		} );
		cy.request( {
			url: '/main',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/main?rsa_redirect=yes' );
		} );
	} );

	it( 'Verify - Redirect to path', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/redirect-to-path'
		} );
		cy.request( {
			url: '/',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect?rsa_redirect=yes' );
		} );
		cy.request( {
			url: '/main',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect?rsa_redirect=yes' );
		} );
	} );

	it( 'Verify - Redirect to path to a new path', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/restrict-users/redirect-to-path-to-a-new-path'
		} );
		cy.request( {
			url: '/main',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/main?rsa_redirect=yes' );
		} );
		cy.request( {
			url: '/main',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/main?rsa_redirect=yes' );
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
