describe( 'Admin can add/remove IP addresses', () => {
	const expectedTuples = [
		{
			ip: '10.2.4.23',
			label: '',
		},
		{
			ip: '123.5.60.3',
			label: 'Google',
		},
		{
			ip: '2a06:98c0::/29',
			label: 'Cloudflare',
		}
	];

	beforeEach( () => {
		cy.visitAdminPage( 'options-reading.php' );
	} );

	it( 'Is able to add IPv4 and IPv6 address without and without label', () => {
		cy
			.get( '#newip' )
			.clear()
			.type( '10.2.4.23' )
			.should( 'have.value', '10.2.4.23' );

		cy
			.get( '#addip' )
			.click();

		cy
			.get( '#newip' )
			.clear()
			.type( '123.5.60.3' )
			.should( 'have.value', '123.5.60.3' );

		cy
			.get( '#newipcomment' )
			.clear()
			.type( 'Google' )
			.should( 'have.value', 'Google' );

		cy
			.get( '#addip' )
			.click();

		cy
			.get( '#newip' )
			.clear()
			.type( '2a06:98c0::/29' )
			.should( 'have.value', '2a06:98c0::/29' );

		cy
			.get( '#newipcomment' )
			.clear()
			.type( 'Cloudflare' )
			.should( 'have.value', 'Cloudflare' );

		cy
			.get( '#addip' )
			.click();


		cy
			.get( '#submit' )
			.click();

		cy.get( '.rsa_unrestricted_ip_row' ).each( ( $el, index ) => {
			const ipEl    = $el.find( 'input[name="rsa_options[allowed][]"]' );
			const labelEl = $el.find( 'input[name="rsa_options[comment][]"]' );

			expect( ipEl ).to.have.value( expectedTuples[ index ].ip );
			expect( labelEl ).to.have.value( expectedTuples[ index ].label );
		} );
	} );

	it( 'Is able to remove IP address', () => {
		cy
			.get( '.rsa_unrestricted_ip_row' )
			.eq( 1 )
			.then( $el => {
				const removeBtn = $el.find( '.remove_btn' );
				cy.wrap( removeBtn ).click();
			} ).then( () => {
				cy
					.wait( 800 )
					.get( '#submit' )
					.click();
			} )


		cy.get( '.rsa_unrestricted_ip_row' ).each( ( $el, index ) => {
			const ipEl    = $el.find( 'input[name="rsa_options[allowed][]"]' );
			const labelEl = $el.find( 'input[name="rsa_options[comment][]"]' );

			if ( 1 === index ) {
				expect( ipEl ).to.have.value( '2a06:98c0::/29' );
				expect( labelEl ).to.have.value( 'Cloudflare' );
			}
		} );
	} );

	it( 'Adding invalid IPv4 address throws error', function() {
		cy
			.get( '#newip' )
			.clear()
			.type( '123.5.60.3/33' );

		cy
			.get( '#addip' )
			.click();

		/**
		 * Wait for the Ajax response.
		 */
		cy.wait( 1000 );

		cy
			.get( '#rsa-error-container' )
			.contains( 'The IP entered is invalid.' );

		cy
			.get( '#newip' )
			.clear()
			.type( '123.5.60.3/32' );

		cy
			.get( '#addip' )
			.click();

		/**
		 * Wait for the Ajax response.
		 */
		cy.wait( 1000 );

		cy
			.get( '#rsa-error-container' )
			.should( 'contain', '' );
	} );

	it( 'Adding invalid IPv6 address throws error', function() {
		cy
			.get( '#newip' )
			.clear()
			.type( '2a06:98c0::/129' );

		cy
			.get( '#addip' )
			.click();

		/**
		 * Wait for the Ajax response.
		 */
		cy.wait( 1000 );

		cy
			.get( '#rsa-error-container' )
			.contains( 'The IP entered is invalid.' );

		cy
			.get( '#newip' )
			.clear()
			.type( '2a06:98c0::/128' );

		cy
			.get( '#addip' )
			.click();
	} );

	after( () => {
		cy.get( '.rsa_unrestricted_ip_row' ).each( ( $el, index ) => {
			const removeBtn = $el.find( '.remove_btn' );

			if ( removeBtn ) {
				cy.wrap( removeBtn ).click();
			}
		} );
	} );
} );
