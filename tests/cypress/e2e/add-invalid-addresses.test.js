describe( 'Add invalid IPv4, IPv6 addresses', () => {
	before( () => {
		cy.resetState();
		cy.login();
		cy.visitAdminPage( 'network/settings.php' );
	} );

	it( 'Test error for invalid IPv4 single address', () => {
		cy.addIp( '172.256.23.4', 'Invalid IPv4 single address' );
		cy.get( '#rsa-error-container' ).contains( 'The IP entered is invalid.' );
	} );

	it( 'Test error for invalid IPv4 subnet', () => {
		cy.reload();
		cy.addIp( '172.10.23.4/33', 'Invalid IPv4 subnet' );
		cy.get( '#rsa-error-container' ).contains( 'The IP entered is invalid.' );
	} );

	it( 'Test error for invalid IPv4 pattern', () => {
		cy.reload();
		cy.addIp( '10.256.*', 'Invalid IPv4 pattern' );
		cy.get( '#rsa-error-container' ).contains( 'The IP entered is invalid.' );
	} );

	it( 'Test error for invalid IPv6 single address', () => {
		cy.reload();
		cy.addIp( '2001:db8:3333:4444:5555:6666:7777:888g', 'Invalid IPv6 single address' );
		cy.get( '#rsa-error-container' ).contains( 'The IP entered is invalid.' );
	} );

	it( 'Test error for invalid IPv6 subnet', () => {
		cy.reload();
		cy.addIp( '2001:0db8:3333:4444:0000:0000:0000:0000/129', 'Invalid IPv6 subnet' );
		cy.get( '#rsa-error-container' ).contains( 'The IP entered is invalid.' );
	} );

	it( 'Test error for invalid IPv6 pattern', () => {
		cy.reload();
		cy.addIp( '2001:0db8:3333:4444:0000:0000:0000:::', 'Invalid IPv6 subnet' );
		cy.get( '#rsa-error-container' ).contains( 'The IP entered is invalid.' );
	} );
} );
