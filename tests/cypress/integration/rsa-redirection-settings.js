describe( 'Admin can correctly save redirection settings', () => {
	beforeEach( () => {
		cy.visitAdminPage( 'options-reading.php' );
		cy.get( '#blog-restricted' ).check();
		cy.get( '#rsa-redirect-visitor' ).check();
	} );

	it( 'Can fill "Redirect web address" input field', () => {
		cy
			.get( '.rsa-setting_settings_field_redirect #redirect' )
			.clear()
			.type( `${ Cypress.config().baseUrl }non-existent-page` )
			.should( 'have.value', `${ Cypress.config().baseUrl }non-existent-page` );

		cy.saveRsaSettings();
	} );

	it( 'Can check "Redirect to same path" checkbox', () => {
		cy
			.get( '.rsa-setting_settings_field_redirect_path #redirect_path' )
			.check()
			.should( 'be.checked' );

		cy.saveRsaSettings();
	} );

	it( 'Can select any of the "Redirection status code" options', () => {
		cy
			.get( '.rsa-setting_settings_field_redirect_code #redirect_code' )
			.select( '301' )
			.should( 'have.value', '301' )
			.select( '302' )
			.should( 'have.value', '302' )
			.select( '307' )
			.should( 'have.value', '307' );

		cy.saveRsaSettings();
	} );

	it( 'Verify all settings are correctly saved', () => {
		cy
			.get( '.rsa-setting_settings_field_redirect #redirect' )
			.should( 'have.value', `${ Cypress.config().baseUrl }non-existent-page` );

		cy
			.get( '.rsa-setting_settings_field_redirect_path #redirect_path' )
			.should( 'be.checked' );

		cy
			.get( '.rsa-setting_settings_field_redirect_code #redirect_code' )
			.should( 'have.value', '307' );
	} )
} );