describe( 'Verify network settings', () => {
	before( () => {
		cy.visitAdminPage( 'network/plugins.php' );
		cy.get( '#activate-restricted-site-access' ).click();
		cy.visitAdminPage( 'network/settings.php' );
	} );

	it( 'Renders "Mode" settings correctly', () => {
		cy
			.get( '#rsa-mode-default' )
			.should( 'be.visible' )
			.and( 'have.value', 'default' );

		cy
			.get( '#rsa-mode-enforce' )
			.should( 'be.visible' )
			.and( 'have.value', 'enforce' );
	} );

	it( 'Set mode to enforce', () => {
		cy.visitAdminPage( 'network/settings.php' );

		cy
			.get( '#blog-restricted' )
			.check();

		cy
			.get( '#rsa-mode-enforce' )
			.check();

		cy
			.get( '#rsa-display-message' )
			.check();

		cy
			.get( '#rsa_message' )
			.clear()
			.type( 'Access to this site is restricted. - From the network' )

		cy.wait(1000)

		cy.get( '#submit' ).click();
	} );

	it( 'Verify sites inheriting network settings', () => {
		cy.visitAdminPage( 'options-reading.php' );
		cy.contains( 'Site visibility settings are currently enforced across all sites on the network.' );
		cy.logout();
		cy.visit( Cypress.config().baseUrl, {
			failOnStatusCode: false
		} );
		cy.get( '.wp-die-message' ).contains( 'Access to this site is restricted. - From the network' );
	} );
} );
