describe( 'Send restricted visitors to a web address', () => {
	before( () => {
		cy.setPermalink();
		cy.saveSettings();
		cy.visitAdminPage( 'options-reading.php' );
		cy.get( '#blog-restricted' ).check();
		cy.get( '#rsa-redirect-visitor' ).check();
		cy.get( '#redirect_path' ).uncheck();
		cy
			.get( 'input[name="rsa_options[redirect_url]"]' )
			.clear()
			.type( `${ Cypress.config().baseUrl }non-existent-page` )
		cy.saveSettings();

		cy.logout();
	} );

	it( `Visiting front page redirects to https://${ Cypress.config().baseUrl }non-existent-page`, () => {
		cy.visit( '/', {
			failOnStatusCode: false
		} );
		cy.url().should( 'include', `${ Cypress.config().baseUrl }non-existent-page` )
	} );

	it( 'HTTP status code: 301 Permanent works correctly', () => {
		testRedirectStatusCode( 301 )
	} );

	it( 'HTTP status code: 302 Undefined works correctly', () => {
		testRedirectStatusCode( 302 )
	} );

	it( 'HTTP status code: 307 Temporary works correctly', () => {
		testRedirectStatusCode( 307 )
	} );
} );

/**
 * A generic function to test all the HTTP status codes
 * that can be set for redirection.
 *
 * @param {Number} $status_code HTTP status code.
 */
function testRedirectStatusCode( $status_code = 301 ) {
	cy.visitAdminPage( 'options-reading.php' );
	cy
		.get( '.rsa-setting_settings_field_redirect_code #redirect_code' )
		.select( `${ $status_code }` );
	cy.saveSettings();
	cy.logout();

	cy.request({
		method: 'GET',
		url: Cypress.config().baseUrl,
		failOnStatusCode: false,
		followRedirect: false
	} ).then( ( response ) => {
		expect( response.status ).to.eq( $status_code )
		expect( response.redirectedToUrl ).to.eq( `${ Cypress.config().baseUrl }non-existent-page` )
	} )
}
