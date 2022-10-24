describe( 'Add & save valid IPv4, IPv6 addresses', () => {
	before( () => {
		cy.resetState();
		cy.login();
		cy.visitAdminPage( 'network/settings.php' );
	} );

	it( 'Add IPv4 single address', () => {
		cy.addIp( '172.10.23.4', 'IPv4 single address' );
	} );

	it( 'Add IPv4 range', () => {
		cy.addIp( '172.10.23.4/3', 'IPv4 range' );
	} );

	it( 'Add IPv4 pattern', () => {
		cy.addIp( '182.10.*.*', 'IPv4 pattern' );
	} );

	it( 'Add IPv6 single address', () => {
		cy.addIp( '2001:0db8:85a3:0000:0000:8a2e:0370:7334', 'IPv6 single address' );
	} );

	it( 'Add IPv6 subnet', () => {
		cy.addIp( '2001:0db8:3333:4444:0000:0000:0000:0000/64', 'IPv6 subnet' );
	} );

	it( 'Add IPv6 pattern', () => {
		cy.addIp( '2001:db8:3333:4444:5555:6666:*:*', 'IPv6 pattern' );
	} );

	after( () => {
		cy.saveSettings();
	} )
} );

describe( 'Validate save operation', () => {
	it( 'Validate each saved IP addresses', () => {
		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 0 ).find( 'input' ).eq( 0 ).should( 'have.value', '172.10.23.4' );
		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 0 ).find( 'input' ).eq( 1 ).should( 'have.value', 'IPv4 single address' );

		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 1 ).find( 'input' ).eq( 0 ).should( 'have.value', '172.10.23.4/3' );
		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 1 ).find( 'input' ).eq( 1 ).should( 'have.value', 'IPv4 range' );

		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 2 ).find( 'input' ).eq( 0 ).should( 'have.value', '182.10.*.*' );
		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 2 ).find( 'input' ).eq( 1 ).should( 'have.value', 'IPv4 pattern' );

		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 3 ).find( 'input' ).eq( 0 ).should( 'have.value', '2001:0db8:85a3:0000:0000:8a2e:0370:7334' );
		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 3 ).find( 'input' ).eq( 1 ).should( 'have.value', 'IPv6 single address' );

		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 4 ).find( 'input' ).eq( 0 ).should( 'have.value', '2001:0db8:3333:4444:0000:0000:0000:0000/64' );
		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 4 ).find( 'input' ).eq( 1 ).should( 'have.value', 'IPv6 subnet' );

		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 5 ).find( 'input' ).eq( 0 ).should( 'have.value', '2001:db8:3333:4444:5555:6666:*:*' );
		cy.get( '#ip_list .rsa_unrestricted_ip_row' ).eq( 5 ).find( 'input' ).eq( 1 ).should( 'have.value', 'IPv6 pattern' );
	} );
} );
