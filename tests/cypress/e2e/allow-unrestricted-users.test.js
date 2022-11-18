describe( 'Handle restricted visitors - Show simple message', () => {
	before( () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/allow-unrestricted-users'
		} )
	} );
	
	it( 'Show simple message - Visitor can still access requested page ', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/allow-unrestricted-users/show-simple-message'
		} )
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
			headers: {
				'X-Forwarded': '172.13.24.5',
			}
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/sample-page/' );
		} );
	} );

	it( 'Redirect to web address - Visitor can still access requested page ', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/allow-unrestricted-users/redirect-to-web-address'
		} )
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
			headers: {
				'X-Forwarded': '172.13.24.5',
			}
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/sample-page/' );
		} );
	} );

	it( 'Send to login screen - Visitor can still access requested page ', () => {
		cy.request( {
			url: '/wp-json/rsa/v1/seed/allow-unrestricted-users/send-to-login-screen'
		} );
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
			headers: {
				'X-Forwarded': '172.13.24.5',
			}
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/sample-page/' );
		} );
	} );
} );
