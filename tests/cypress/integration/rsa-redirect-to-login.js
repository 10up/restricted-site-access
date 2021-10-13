describe( 'Send restricted visitors to login page', () => {
	before( () => {
		cy.visitAdminPage( 'options-reading.php' );
		cy.get( '#blog-restricted' ).check();
		cy.get( '#rsa-send-to-login' ).check();
		cy.saveSettings();
		cy.logout();
	} );

	it( 'Visiting front page redirects to login page', () => {
		cy.visit( '/' );
		cy.url().should( 'include', `${ Cypress.config().baseUrl }wp-login.php` )
	} );
} );
