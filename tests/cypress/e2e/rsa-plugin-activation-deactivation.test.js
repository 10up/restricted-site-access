describe( 'Admin can login and make sure plugin is network activated/deactivated', () => {
	it( 'Can network activate plugin', () => {
		cy.visitAdminPage( 'network/plugins.php' );
		cy.contains( 'strong', 'Restricted Site Access' ).closest( 'td' ).find( '.activate > a' ).click();
		cy.contains( 'strong', 'Restricted Site Access' ).closest( 'td' ).find( '.deactivate > a' ).click();
	} );
} );

describe( 'Admin can login and make sure plugin is activated', () => {
	it( 'Can activate plugin if it is deactivated', () => {
		cy.visitAdminPage( 'plugins.php' );
		cy.contains( 'strong', 'Restricted Site Access' ).closest( 'td' ).find( '.deactivate > a' ).click();
		cy.contains( 'strong', 'Restricted Site Access' ).closest( 'td' ).find( '.activate > a' ).click();
	} );
} );

