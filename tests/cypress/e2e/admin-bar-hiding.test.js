describe( 'Admin Bar Hiding Feature', () => {
	beforeEach( () => {
		cy.visitAdminPage( 'options-reading.php' );
	} );

	describe( 'Settings Interface', () => {
		it( 'should display admin bar hiding section', () => {
			cy.get( 'h2' ).contains( 'Restricted Site Access' ).should( 'be.visible' );
			cy.get( 'label' ).contains( 'Hide admin bar for user roles on frontend' ).should( 'be.visible' );
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
			cy.get( 'input[value="subscriber"]' ).check();
			cy.get( 'input[value="contributor"]' ).check();

			// Save settings
			cy.saveSettings();

			// Reload page and verify selections are saved
			cy.reload();
			cy.get( 'input[value="subscriber"]' ).should( 'be.checked' );
			cy.get( 'input[value="contributor"]' ).should( 'be.checked' );
			cy.get( 'input[value="author"]' ).should( 'not.be.checked' );
		} );

		it( 'should clear unselected roles', () => {
			// First, select some roles
			cy.get( 'input[value="subscriber"]' ).check();
			cy.get( 'input[value="contributor"]' ).check();
			cy.saveSettings();

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
			cy.get( 'input[value="subscriber"]' ).check();
			cy.get( 'input[value="contributor"]' ).check();
			cy.get( 'input[value="author"]' ).check();
			cy.saveSettings();

			// Verify all are selected
			cy.reload();
			cy.get( 'input[value="subscriber"]' ).should( 'be.checked' );
			cy.get( 'input[value="contributor"]' ).should( 'be.checked' );
			cy.get( 'input[value="author"]' ).should( 'be.checked' );
		} );
	} );

	describe( 'Frontend Behavior', () => {
		beforeEach( () => {
			// Configure admin bar hiding for subscriber role via REST API
			cy.request( 'POST', '/wp-json/rsa/v1/seed/admin-bar-hiding/configure-settings', {
				roles_to_hide: [ 'subscriber' ],
			} );
		} );

		it( 'should hide admin bar for subscriber on frontend', () => {
			// Create subscriber user via REST API
			cy.request( 'POST', '/wp-json/rsa/v1/seed/admin-bar-hiding/create-user', {
				username: 'subscriber_test',
				email: 'subscriber@test.com',
				role: 'subscriber',
				password: 'password123',
			} ).then( ( response ) => {
				expect( response.body.success ).to.be.true;
			} );

			// Login as subscriber
			cy.visit( '/wp-login.php' );
			cy.get( '#user_login' ).type( 'subscriber_test' );
			cy.get( '#user_pass' ).type( 'password123' );
			cy.get( '#wp-submit' ).click();

			// Visit frontend
			cy.visit( '/' );

			// Check that admin bar is hidden
			cy.get( '#wpadminbar' ).should( 'not.exist' );
		} );

		it( 'should show admin bar for other roles on frontend', () => {
			// Create author user via REST API
			cy.request( 'POST', '/wp-json/rsa/v1/seed/admin-bar-hiding/create-user', {
				username: 'author_test',
				email: 'author@test.com',
				role: 'author',
				password: 'password123',
			} ).then( ( response ) => {
				expect( response.body.success ).to.be.true;
			} );

			// Login as author
			cy.visit( '/wp-login.php' );
			cy.get( '#user_login' ).type( 'author_test' );
			cy.get( '#user_pass' ).type( 'password123' );
			cy.get( '#wp-submit' ).click();

			// Visit frontend
			cy.visit( '/' );

			// Check that admin bar is visible
			cy.get( '#wpadminbar' ).should( 'be.visible' );
		} );

		it( 'should show admin bar in admin area for all roles', () => {
			// Create subscriber user via REST API
			cy.request( 'POST', '/wp-json/rsa/v1/seed/admin-bar-hiding/create-user', {
				username: 'subscriber_admin',
				email: 'subscriber_admin@test.com',
				role: 'subscriber',
				password: 'password123',
			} ).then( ( response ) => {
				expect( response.body.success ).to.be.true;
			} );

			// Login as subscriber
			cy.visit( '/wp-login.php' );
			cy.get( '#user_login' ).type( 'subscriber_admin' );
			cy.get( '#user_pass' ).type( 'password123' );
			cy.get( '#wp-submit' ).click();

			// Visit admin area
			cy.visit( '/wp-admin/' );

			// Check that admin bar is visible in admin
			cy.get( '#wpadminbar' ).should( 'be.visible' );
		} );

		it( 'should handle multiple roles per user', () => {
			// Create user with subscriber role via REST API
			cy.request( 'POST', '/wp-json/rsa/v1/seed/admin-bar-hiding/create-user', {
				username: 'multirole_test',
				email: 'multirole@test.com',
				role: 'subscriber',
				password: 'password123',
			} ).then( ( response ) => {
				expect( response.body.success ).to.be.true;
			} );

			// Add contributor role via WP-CLI (since REST API doesn't support multiple roles)
			cy.wpCli( 'user set-role multirole_test contributor' );

			// Login as multi-role user
			cy.visit( '/wp-login.php' );
			cy.get( '#user_login' ).type( 'multirole_test' );
			cy.get( '#user_pass' ).type( 'password123' );
			cy.get( '#wp-submit' ).click();

			// Visit frontend
			cy.visit( '/' );

			// Should hide admin bar because user has subscriber role
			cy.get( '#wpadminbar' ).should( 'not.exist' );
		} );
	} );

	describe( 'Edge Cases', () => {
		it( 'should handle no roles selected', () => {
			// Reset admin bar hiding settings via REST API
			cy.request( 'GET', '/wp-json/rsa/v1/seed/admin-bar-hiding/reset-settings' ).then( ( response ) => {
				expect( response.body.success ).to.be.true;
			} );

			// Create subscriber user via REST API
			cy.request( 'POST', '/wp-json/rsa/v1/seed/admin-bar-hiding/create-user', {
				username: 'edge_test',
				email: 'edge@test.com',
				role: 'subscriber',
				password: 'password123',
			} ).then( ( response ) => {
				expect( response.body.success ).to.be.true;
			} );

			cy.visit( '/wp-login.php' );
			cy.get( '#user_login' ).type( 'edge_test' );
			cy.get( '#user_pass' ).type( 'password123' );
			cy.get( '#wp-submit' ).click();

			cy.visit( '/' );

			// Admin bar should be visible when no roles are selected
			cy.get( '#wpadminbar' ).should( 'be.visible' );
		} );

		it( 'should handle custom user roles', () => {
			// Create a custom role
			cy.wpCli( 'eval "add_role( \'custom_role\', \'Custom Role\', array( \'read\' => true ) );"' );

			// Create user with custom role via REST API
			cy.request( 'POST', '/wp-json/rsa/v1/seed/admin-bar-hiding/create-user', {
				username: 'custom_test',
				email: 'custom@test.com',
				role: 'custom_role',
				password: 'password123',
			} ).then( ( response ) => {
				expect( response.body.success ).to.be.true;
			} );

			// Configure admin bar hiding for custom role via REST API
			cy.request( 'POST', '/wp-json/rsa/v1/seed/admin-bar-hiding/configure-settings', {
				roles_to_hide: [ 'custom_role' ],
			} );

			// Login as custom role user
			cy.visit( '/wp-login.php' );
			cy.get( '#user_login' ).type( 'custom_test' );
			cy.get( '#user_pass' ).type( 'password123' );
			cy.get( '#wp-submit' ).click();

			cy.visit( '/' );

			// Admin bar should be hidden for custom role
			cy.get( '#wpadminbar' ).should( 'not.exist' );
		} );

		it( 'should handle users with no roles', () => {
			// Create user with no specific role via REST API
			cy.request( 'POST', '/wp-json/rsa/v1/seed/admin-bar-hiding/create-user', {
				username: 'norole_test',
				email: 'norole@test.com',
				role: '',
				password: 'password123',
			} ).then( ( response ) => {
				expect( response.body.success ).to.be.true;
			} );

			cy.visit( '/wp-login.php' );
			cy.get( '#user_login' ).type( 'norole_test' );
			cy.get( '#user_pass' ).type( 'password123' );
			cy.get( '#wp-submit' ).click();

			cy.visit( '/' );

			// Admin bar should be visible for users with no roles
			cy.get( '#wpadminbar' ).should( 'be.visible' );
		} );
	} );

	describe( 'Performance', () => {
		it( 'should handle large number of roles efficiently', () => {
			// Create many custom roles
			for ( let i = 1; i <= 10; i++ ) {
				cy.wpCli( `eval "add_role( 'custom_role_${ i }', 'Custom Role ${ i }', array( 'read' => true ) );"` );
			}

			// Reload settings page
			cy.visitAdminPage( 'options-reading.php' );

			// Should still load quickly
			cy.get( 'input[name="rsa_options[hide_admin_bar_roles][]"]' ).should( 'have.length.at.least', 15 );

			// Should be able to select roles
			cy.get( 'input[value="custom_role_1"]' ).check();
			cy.get( 'input[value="custom_role_5"]' ).check();
			cy.saveSettings();

			cy.reload();
			cy.get( 'input[value="custom_role_1"]' ).should( 'be.checked' );
			cy.get( 'input[value="custom_role_5"]' ).should( 'be.checked' );
		} );
	} );
} );
