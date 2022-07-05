describe( 'Site is unblocked for unrestricted users', () => {
	before( () => {
		cy.visitAdminPage( 'options-reading.php' );
		cy.get( '#blog-restricted' ).check();
		cy.get( '#rsa-send-to-login' ).check();
		cy.saveSettings();
	} );

	it( 'Add IP 193.168.20.30 as an unrestricted user', () => {
		cy.visitAdminPage( 'options-reading.php' );
		cy
			.get( '#newip' )
			.clear()
			.type( '193.168.20.30' )

		cy
			.get( '#addip' )
			.click();

		cy.wait( 800 )

		cy.saveSettings();
		cy.logout();
	} );

	it( 'Verify restricted users are sent to the login page', () => {
		cy.visit( '/' );
		cy.url().should( 'include', `${ Cypress.config().baseUrl }wp-login.php` )
	} );

	it( 'Verify unrestricted users can access the home page', () => {
		cy.request({
			method: 'GET',
			url: `${ Cypress.config().baseUrl }`,
			failOnStatusCode: false,
			headers: {
				'X-Forwarded': '193.168.20.30',
			}
		} ).then( ( response ) => {
			expect( response.status ).to.eq( 200 );
			expect( response.body ).to.contain( 'home blog' );
		} );
	} );
} );
