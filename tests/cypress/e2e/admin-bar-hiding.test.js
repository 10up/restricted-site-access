describe( 'Admin Bar Hiding Feature', () => {
	before( () => {
		cy.visitAdminPage( 'network/settings.php' );
		cy.get( '#rsa-mode-default' ).check();
		cy.saveSettings();
	} );

	beforeEach( () => {
		cy.visitAdminPage( 'options-reading.php' );
	} );

	describe( 'Settings Interface', () => {
		it( 'should display admin bar hiding section', () => {
			cy.get( 'h2' ).contains( 'Restricted Site Access' ).should( 'be.visible' );
			cy.get( 'th' ).contains( 'Hide admin bar for roles' ).should( 'be.visible' );
		} );

		it( 'should display all user roles as checkboxes', () => {
			// Check for common WordPress roles
			cy.get( 'input[name="rsa_options[hide_admin_bar_roles][]"]' ).should( 'have.length.at.least', 5 );

			// Verify specific roles are present
			cy.get( 'input[value="administrator"]' ).should( 'be.visible' );
			cy.get( 'input[value="editor"]' ).should( 'be.visible' );
			cy.get( 'input[value="author"]' ).should( 'be.visible' );
			cy.get( 'input[value="contributor"]' ).should( 'be.visible' );
			cy.get( 'input[value="subscriber"]' ).should( 'be.visible' );
		} );

		it( 'should display help text', () => {
			cy.get( 'p.description' ).contains( 'Select user roles for which the WordPress admin bar should be hidden on the frontend' ).should( 'be.visible' );
		} );
	} );

	describe( 'Settings Persistence', () => {
		it( 'should save selected roles', () => {
			// Select subscriber and contributor roles
			cy.hideAdminBarUserRole( [ 'subscriber', 'contributor' ] );

			// Reload page and verify selections are saved
			cy.reload();
			cy.get( 'input[value="subscriber"]' ).should( 'be.checked' );
			cy.get( 'input[value="contributor"]' ).should( 'be.checked' );
			cy.get( 'input[value="author"]' ).should( 'not.be.checked' );
		} );

		it( 'should clear unselected roles', () => {
			// First, select some roles
			cy.hideAdminBarUserRole( [ 'subscriber', 'contributor' ] );

			// Then unselect them
			cy.get( 'input[value="subscriber"]' ).uncheck();
			cy.get( 'input[value="contributor"]' ).uncheck();
			cy.saveSettings();

			// Verify they are unchecked
			cy.reload();
			cy.get( 'input[value="subscriber"]' ).should( 'not.be.checked' );
			cy.get( 'input[value="contributor"]' ).should( 'not.be.checked' );
		} );

		it( 'should handle multiple role selections', () => {
			// Select multiple roles
			cy.hideAdminBarUserRole( [ 'subscriber', 'contributor', 'author' ] );

			// Verify all are selected
			cy.reload();
			cy.get( 'input[value="subscriber"]' ).should( 'be.checked' );
			cy.get( 'input[value="contributor"]' ).should( 'be.checked' );
			cy.get( 'input[value="author"]' ).should( 'be.checked' );
		} );
	} );

	describe( 'Frontend Behavior', () => {
		beforeEach( () => {
			cy.hideAdminBarUserRole( [ 'subscriber' ] );
		} );

		afterEach( () => {
			cy.updateUserRole( 'administrator' );
		} );

		it( 'should hide admin bar for subscriber on frontend', () => {
			// Update role to subscriber
			cy.updateUserRole( 'subscriber' );

			// Visit frontend
			cy.visit( '/' );

			// Check that admin bar is hidden
			cy.get( '#wpadminbar' ).should( 'not.exist' );
		} );

		it( 'should show admin bar for other roles on frontend', () => {
			// Update role to author
			cy.updateUserRole( 'author' );

			// Visit frontend
			cy.visit( '/' );

			// Check that admin bar is visible
			cy.get( '#wpadminbar' ).should( 'be.visible' );
		} );

		it( 'should show admin bar in admin area for all roles', () => {
			// Update role to subscriber
			cy.updateUserRole( 'subscriber' );

			// Visit admin area
			cy.visit( '/wp-admin/' );

			// Check that admin bar is visible in admin
			cy.get( '#wpadminbar' ).should( 'be.visible' );
		} );
	} );

	describe( 'Edge Cases', () => {
		afterEach( () => {
			cy.updateUserRole( 'administrator' );
		} );

		it( 'should handle no roles selected', () => {
			// Reset admin bar hiding settings via REST API
			cy.resetAdminBarHiding();

			// Update role to subscriber
			cy.updateUserRole( 'subscriber' );

			// Visit frontend
			cy.visit( '/' );

			// Admin bar should be visible when no roles are selected
			cy.get( '#wpadminbar' ).should( 'be.visible' );
		} );

		it( 'should handle custom user roles', () => {
			// Create a custom role
			cy.createCustomUserRole();

			// Update role to custom role
			cy.updateUserRole( 'custom_role' );

			// Hide admin bar for custom role
			cy.hideAdminBarUserRole( [ 'custom_role' ] );

			// Visit frontend
			cy.visit( '/' );

			// Admin bar should be hidden for custom role
			cy.get( '#wpadminbar' ).should( 'not.exist' );
		} );

		it( 'should handle users with no roles', () => {
			// Reset admin bar hiding settings
			cy.resetAdminBarHiding();

			// Remove user role
			cy.removeUserRole();

			// Visit frontend
			cy.visit( '/' );

			// Admin bar should be visible for users with no roles
			cy.get( '#wpadminbar' ).should( 'be.visible' );
		} );
	} );

	describe( 'Performance', () => {
		it( 'should handle large number of roles efficiently', () => {
			// Create many custom roles
			for ( let i = 1; i <= 10; i++ ) {
				// Create a custom role
				cy.createCustomUserRole( `custom_role_${ i }`, `Custom Role ${ i }`, { read: true } );
			}

			// Should still load quickly
			cy.get( 'input[name="rsa_options[hide_admin_bar_roles][]"]' ).should( 'have.length.at.least', 15 );

			// Should be able to select roles
			cy.hideAdminBarUserRole( [ 'custom_role_1', 'custom_role_5' ] );

			cy.reload();
			cy.get( 'input[value="custom_role_1"]' ).should( 'be.checked' );
			cy.get( 'input[value="custom_role_5"]' ).should( 'be.checked' );
		} );
	} );
} );
