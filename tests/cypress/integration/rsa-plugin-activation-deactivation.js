describe( 'Admin can login and make sure plugin is network activated/deactivated', () => {
	it( 'Can network activate plugin', () => {
		cy.visitAdminPage( 'network/plugins.php' );
		cy.get( '#activate-restricted-site-access' ).click();
		cy.get( '#deactivate-restricted-site-access' ).click();
		cy.get( '#rsa-user-message' ).type( 'I understand' );
		cy.contains( 'button', 'Network Disable Plugin' ).click();
		cy.get( '#activate-restricted-site-access' ).should( 'be.visible' );
	} );
} );

describe( 'Admin can login and make sure plugin is activated', () => {
	it( 'Can activate plugin if it is deactivated', () => {
		cy.visitAdminPage( 'plugins.php' );
		cy.get( '#deactivate-restricted-site-access' ).click();
		cy.get( '#activate-restricted-site-access' ).click();
		cy.get( '#deactivate-restricted-site-access' ).should( 'be.visible' );
	} );
} );
