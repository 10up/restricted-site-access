describe( 'Admin can correctly save redirection settings', () => {
	beforeEach( () => {
		cy.visitAdminPage( 'options-reading.php' );
		cy.get( '#blog-restricted' ).check();
		cy.get( '#rsa-display-message' ).check();
	} );

	it( 'Can edit "Restriction message', () => {
		cy
			.get( '#rsa_message' )
			.clear()
			.type( 'This is a restricted site. Please contact the admin.' );

		cy
			.get( '#rsa_message' )
			.type( '{selectall}' );

		cy
			.get( '#qt_rsa_message_strong' )
			.click();

		cy
			.get( '#rsa_message' )
			.should( 'have.value', '<strong>This is a restricted site. Please contact the admin.</strong>' );

		cy.saveSettings();
	} );

	it( 'Verify all settings are correctly saved', () => {
		cy
			.get( '#rsa_message' )
			.should( 'have.value', '<strong>This is a restricted site. Please contact the admin.</strong>' );
	} );
} );
