describe( 'Admin can login and make sure plugin is activated', () => {
	it( 'Can activate plugin if it is deactivated', () => {
		cy.visitAdminPage( 'plugins.php' );
		cy.get( '#deactivate-restricted-site-access' ).click();
		cy.get( '#activate-restricted-site-access' ).click();
		cy.get( '#deactivate-restricted-site-access' ).should( 'be.visible' );
	} );
} );
