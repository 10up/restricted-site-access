describe( 'Plugin admin settings are properly interactive.', () => {
	it( 'Visit plugin settings.', () => {
		cy.visitAdminPage( 'options-reading.php' );
	} );

	it ( 'Select "Site visibility: public" radio button', function() {
		cy
			.get( '#blog-public' )
			.check();

		cy
			.get( '.rsa-setting_settings_field_handling' )
			.should( 'not.be.visible' );
	} );

	it ( 'Select "Site visibility: no robots" radio button', function() {
		cy
			.get( '#blog-norobots' )
			.check();

		cy
			.get( '.rsa-setting_settings_field_handling' )
			.should( 'not.be.visible' );
	} );

	it ( 'Select "Site visibility: no robots" radio button', function() {
		cy
			.get( '#blog-restricted' )
			.check();

		cy
			.get( '.rsa-setting_settings_field_handling' )
			.should( 'be.visible' );
	} );

	it ( 'Select "Handle restricted visitors: redirect visitors" radio button', function() {
		cy
			.get( '#rsa-redirect-visitor' )
			.check();

		cy
			.get( 'input[name="rsa_options[redirect_url]"]' )
			.should( 'be.visible' );

		cy
			.get( 'input[name="rsa_options[redirect_path]"]' )
			.should( 'be.visible' );

		cy
			.get( 'select[name="rsa_options[head_code]"]' )
			.should( 'be.visible' );
	} );

	it ( 'Select "Handle restricted visitors: display message" radio button', function() {
		cy
			.get( '#rsa-display-message' )
			.check();

		cy
			.get( 'input[name="rsa_options[redirect_url]"], input[name="rsa_options[redirect_path]"], input[name="rsa_options[head_code]"]' )
			.should( 'not.be.visible' );

		cy
			.get( 'textarea[name="rsa_options[message]"]' )
			.should( 'be.visible' );
	} );

	it ( 'Select "Handle restricted visitors: unblocked page" radio button', function() {
		cy
			.get( '#rsa-unblocked-page' )
			.check();

		cy
			.get( 'textarea[name="rsa_options[message]"]' )
			.should( 'not.be.visible' );

		cy
			.get( 'select[name="rsa_options[page]"]' )
			.should( 'be.visible' );
	} );

	it ( 'Select "Handle restricted visitors: send to login" radio button', function() {
		cy
			.get( '#rsa-send-to-login' )
			.check();

		cy
			.get( 'select[name="rsa_options[page]"]' )
			.should( 'not.be.visible' );
	} );
} );
