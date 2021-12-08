describe( 'Show a page to restricted users', () => {
	before( () => {
		cy.visitAdminPage( 'options-reading.php' );
		cy.get( '#rsa-unblocked-page' ).check();
		cy.saveSettings();
		cy.logout();
	} );

	it( 'Show a general restricted message if a page is not selected', () => {
		cy.visit( '/', {
			failOnStatusCode: false
		} );

		cy.get( '#error-page' ).contains( 'This is a restricted site. Please contact the admin.' );
	} );

	it( 'Show the selected page to restricted users', () => {
		cy.visitAdminPage( 'options-reading.php' );
		cy.get( '#rsa_page' ).select( 'Page to redirect' );
		cy.saveSettings();
		cy.logout();
		cy.visit( '/', {
			failOnStatusCode: false,
		} );
		cy.url().should( 'include', `${ Cypress.config().baseUrl }page-to-redirect` );
	} );

	it( 'Unrestricted user should be able to access the site', () => {
		cy.visit( `${ Cypress.config().baseUrl }accessible-page`, {
			failOnStatusCode: false
		} );
		cy.url().should( 'include', `${ Cypress.config().baseUrl }accessible-page` )
	} );
} );
