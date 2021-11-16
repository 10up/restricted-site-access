describe( 'Plugin admin settings are properly rendered.', () => {
	before( () => {
		cy.visitAdminPage( 'post-new.php?post_type=page' );
		cy.get( 'button[aria-label="Close dialog"]' ).click();
		cy.get( '.editor-post-title__input' ).click().type( 'Accessible page' );
		cy.get( '.editor-post-publish-panel__toggle' ).click();
		cy.get( '.editor-post-publish-button' ).click();
		cy.get( '.components-snackbar', { timeout: 10000 } ).should(
			'be.visible'
		);

		cy.visitAdminPage( 'post-new.php?post_type=page' );
		cy.get( '.editor-post-title__input' ).click().type( 'Page to redirect' );
		cy.get( '.editor-post-publish-panel__toggle' ).click();
		cy.get( '.editor-post-publish-button' ).click();
		cy.get( '.components-snackbar', { timeout: 10000 } ).should(
			'be.visible'
		);
	} );

	it( 'Visit plugin settings.', () => {
		cy.visitAdminPage( 'options-reading.php' );
	} );

	it( 'Site visibility: "public" radio button', () => {
		cy
			.get( '#blog-public' )
			.should( 'be.visible' )
			.and( 'have.value', '1' );

		cy
			.get( 'label[for="blog-public"]' )
			.should( 'be.visible' )
			.and( 'have.text', 'Allow search engines to index this site' );
	} );

	it( 'Site visibility: "no robots" radio button', () => {
		cy
			.get( '#blog-norobots' )
			.should( 'be.visible' )
			.and( 'have.value', '0' );

		cy
			.get( 'label[for="blog-norobots"]' )
			.should( 'be.visible' )
			.and( 'have.text', 'Discourage search engines from indexing this site' );
	} );

	it( 'Site visibility: "restricted" radio button', () => {
		cy
			.get( '#blog-restricted' )
			.should( 'be.visible' )
			.and( 'have.value', '2' )
			.and( 'be.checked' );

		cy
			.get( 'label[for="blog-restricted"]' )
			.should( 'be.visible' )
			.and( 'have.text', 'Restrict site access to visitors who are logged in or allowed by IP address' )
	} );

	it( 'Handle restricted visitors: "send to login" radio button', () => {
		cy
			.get( '#rsa-send-to-login' )
			.should( 'be.visible' )
			.and( 'have.value', '1' );

		cy
			.get( 'label[for="rsa-send-to-login"]' )
			.should( 'be.visible' )
			.and( 'have.text', 'Send them to the WordPress login screen' );
	} );

	it( 'Handle restricted visitors: "redirect visitor" radio button', () => {
		cy
			.get( '#rsa-redirect-visitor' )
			.should( 'be.visible' )
			.and( 'have.value', '2' );

		cy
			.get( 'label[for="rsa-redirect-visitor"]' )
			.should( 'be.visible' )
			.and( 'have.text', 'Redirect them to a specified web address' );
	} );

	it( 'Handle restricted visitors: "display message" radio button', () => {
		cy
			.get( '#rsa-display-message' )
			.should( 'be.visible' )
			.and( 'have.value', '3' );

		cy
			.get( 'label[for="rsa-display-message"]' )
			.should( 'be.visible' )
			.and( 'have.text', 'Show them a simple message' );
	} );

	it( 'Handle restricted visitors: "unnblocked page" radio button', () => {
		cy
			.get( '#rsa-unblocked-page' )
			.should( 'be.visible' )
			.and( 'have.value', '4' );

		cy
			.get( 'label[for="rsa-unblocked-page"]' )
			.should( 'be.visible' )
			.and( 'have.text', 'Show them a page' );
	} );

	it( '"IP Address or Range" text input field', () => {
		cy
			.get( '#newip' )
			.should( 'be.visible' )
			.and( ( $el ) => expect( $el ).to.have.attr( 'placeholder', 'IP Address or Range' ) );
	} );

	it( '"Identify this entry" text input field', () => {
		cy
			.get( '#newipcomment' )
			.should( 'be.visible' )
			.and( ( $el ) => expect( $el ).to.have.attr( 'placeholder', 'Identify this entry' ) );
	} );
	
	it( '"Add" button', () => {
		cy
			.get( '#addip' )
			.should( 'be.visible' )
			.and( 'have.value', 'Add' );
	} )

	it( '"Add My Current IP Address" button', () => {
		cy
			.get( '#rsa_myip' )
			.should( 'be.visible' )
			.and( 'have.value', 'Add My Current IP Address' );
	} )

	it( '"Redirect web address" input field should be hidden', () => {
		cy
			.get( 'input[name="rsa_options[redirect_url]"]' )
			.should( 'not.be.visible' );
	} );

	it( '"Redirect to same path" checkbox should be hidden', () => {
		cy
			.get( 'input[name="rsa_options[redirect_path]"]' )
			.should( 'not.be.visible' );
	} );

	it( '"Redirection status code" select dropdown should be hidden', () => {
		cy
			.get( 'select[name="rsa_options[head_code]"]' )
			.should( 'not.be.visible' );
	} );

	it( '"Restriction message" textarea should be hidden', () => {
		cy
			.get( 'textarea[name="rsa_options[message]"]' )
			.should( 'not.be.visible' );
	} );

	it( '"Restriction notice page" select dropdown should be hidden', () => {
		cy
			.get( 'select[name="rsa_options[page]"]' )
			.should( 'not.be.visible' );
	} );
} )
