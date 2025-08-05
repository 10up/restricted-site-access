describe( 'Admin Bar Hiding Feature', () => {
	beforeEach( () => {
		cy.visitAdminPage( 'options-reading.php' );
	} );

	describe( 'Settings Interface', () => {
		it( 'should display admin bar hiding section', () => {
			cy.get( 'h2' ).contains( 'Restricted Site Access Settings' ).should( 'be.visible' );
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
			cy.get( 'p.description' ).contains( 'Select user roles for which the admin bar should be hidden on the frontend' ).should( 'be.visible' );
		} );

		it( 'should have proper fieldset structure', () => {
			cy.get( 'fieldset' ).within( () => {
				cy.get( 'legend.screen-reader-text' ).should( 'be.visible' );
				cy.get( 'input[type="checkbox"]' ).should( 'have.length.at.least', 5 );
			} );
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
			// Set up admin bar hiding for subscriber role
			cy.get( 'input[value="subscriber"]' ).check();
			cy.saveSettings();
		} );

		it( 'should hide admin bar for subscriber on frontend', () => {
			// Create and login as subscriber
			cy.wpCli( 'user create subscriber_test subscriber@test.com --role=subscriber --user_pass=password123' );

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
			// Create and login as author
			cy.wpCli( 'user create author_test author@test.com --role=author --user_pass=password123' );

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
			// Create and login as subscriber
			cy.wpCli( 'user create subscriber_admin subscriber_admin@test.com --role=subscriber --user_pass=password123' );

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
			// Create user with multiple roles
			cy.wpCli( 'user create multirole_test multirole@test.com --role=subscriber --user_pass=password123' );
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

	describe( 'Network Settings (Multisite)', () => {
		it( 'should display admin bar hiding in network settings', () => {
			// Only run this test in multisite
			cy.wpCli( 'core is-installed --network' ).then( ( result ) => {
				if ( result.code === 0 ) {
					cy.visitAdminPage( 'settings.php' );

					cy.get( 'h2' ).contains( 'Restricted Site Access Settings' ).should( 'be.visible' );
					cy.get( 'label' ).contains( 'Hide admin bar for user roles on frontend' ).should( 'be.visible' );

					// Test network settings persistence
					cy.get( 'input[value="subscriber"]' ).check();
					cy.saveSettings();

					cy.reload();
					cy.get( 'input[value="subscriber"]' ).should( 'be.checked' );
				} else {
					cy.log( 'Skipping multisite test - not a multisite installation' );
				}
			} );
		} );
	} );

	describe( 'Edge Cases', () => {
		it( 'should handle no roles selected', () => {
			// Ensure no roles are selected
			cy.get( 'input[name="rsa_options[hide_admin_bar_roles][]"]' ).uncheck();
			cy.saveSettings();

			// Create and login as subscriber
			cy.wpCli( 'user create edge_test edge@test.com --role=subscriber --user_pass=password123' );

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

			// Create user with custom role
			cy.wpCli( 'user create custom_test custom@test.com --role=custom_role --user_pass=password123' );

			// Select custom role for admin bar hiding
			cy.visitAdminPage( 'options-reading.php' );
			cy.get( 'input[value="custom_role"]' ).check();
			cy.saveSettings();

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
			// Create user with no specific role
			cy.wpCli( 'user create norole_test norole@test.com --user_pass=password123' );

			cy.visit( '/wp-login.php' );
			cy.get( '#user_login' ).type( 'norole_test' );
			cy.get( '#user_pass' ).type( 'password123' );
			cy.get( '#wp-submit' ).click();

			cy.visit( '/' );

			// Admin bar should be visible for users with no roles
			cy.get( '#wpadminbar' ).should( 'be.visible' );
		} );
	} );

	describe( 'Performance and Responsiveness', () => {
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
