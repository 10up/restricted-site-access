describe( 'Activation/Deactivation', () => {
	before( () => {
		cy.resetState();
		cy.wpCli( 'plugin deactivate restricted-site-access --network' );
		cy.login();
	} );

	it( 'Plugin activation', () => {
		cy.visitAdminPage( 'network/plugins.php' );
		cy.contains( 'strong', 'Restricted Site Access' ).closest( 'td' ).find( '.activate > a' ).click();
	} );

	it( 'Plugin deactivation', () => {
		cy.visitAdminPage( 'network/plugins.php' );
		cy.contains( 'strong', 'Restricted Site Access' ).closest( 'td' ).find( '.deactivate > a' ).click();
		cy.get( '#rsa-user-message' ).type( 'I understand' );
		cy.get( 'button' ).contains( 'Network Disable Plugin' ).click();
	} );

	after( () => {
		cy.contains( 'strong', 'Restricted Site Access' ).closest( 'td' ).find( '.activate > a' ).click();
	} );
} );
