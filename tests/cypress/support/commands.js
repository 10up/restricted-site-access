// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })
Cypress.Commands.add(
	'login',
	( username = 'admin', password = 'password' ) => {
		cy.visit( `/wp-admin` );
		cy.get( 'body' ).then( ( $body ) => {
			if ( $body.find( '#wpwrap' ).length == 0 ) {
				cy.get( 'input#user_login' ).clear();
				cy.get( 'input#user_login' ).click().type( username );
				cy.get( 'input#user_pass' ).type( `${ password }{enter}` );
			}
		} );
	}
);

Cypress.Commands.add( 'visitAdminPage', ( page = 'index.php' ) => {
	cy.login();
	if ( page.includes( 'http' ) ) {
		cy.visit( page );
	} else {
		cy.visit( `/wp-admin/${ page.replace( /^\/|\/$/g, '' ) }` );
	}
} );

Cypress.Commands.add(
	'createTaxonomy',
	( name = 'Test taxonomy', taxonomy = 'category' ) => {
		cy.visitAdminPage( `edit-tags.php?taxonomy=${ taxonomy }` );
		cy.get( '#tag-name' ).click().type( `${ name }{enter}` );
	}
);

Cypress.Commands.add( 'openDocumentSettingsSidebar', () => {
	const button =
		'.edit-post-header__settings button[aria-label="Settings"][aria-expanded="false"]';
	cy.get( 'body' ).then( ( $body ) => {
		if ( $body.find( button ).length > 0 ) {
			cy.get( button ).click();
		}
	} );
	cy.get( '.edit-post-sidebar__panel-tab' ).contains( 'Post' ).click();
} );

Cypress.Commands.add( 'openDocumentSettingsPanel', ( name ) => {
	cy.openDocumentSettingsSidebar();
	cy.get( '.components-panel__body .components-panel__body-title button' )
		.contains( name )
		.then( ( panel ) => {
			if ( ! panel.hasClass( '.is-opened' ) ) {
				cy.get( panel ).click();
				cy.get( panel )
					.parents( '.components-panel__body' )
					.should( 'have.class', 'is-opened' );
			}
		} );
} );

Cypress.Commands.add( 'saveSettings', () => {
	cy
		.get( '#submit' )
		.click();
} );

Cypress.Commands.add( 'logout', () => {
	cy
		.get( '#wp-admin-bar-logout > a' )
		.invoke( 'attr', 'href' )
		.then( href => {
			cy.visit( href );
		} );
} );

Cypress.Commands.add( 'setPermalink', () => {
	cy.visitAdminPage( 'options-permalink.php' );
	cy
		.get( 'form[action="options-permalink.php"] input[type="radio"]' )
		.eq(4)
		.check();
} );

Cypress.Commands.add( 'resetState', () => {
	cy.wpCli( `network meta set 1 blog_public 2` );
	cy.wpCli( `network meta set 1 rsa_options '{"approach":1,"message":"Access to this site is restricted.","redirect_path":0,"head_code":302,"redirect_url":"","page":0,"allowed":[],"comment":[""]}' --format=json` );
} );

Cypress.Commands.add( 'addIp', ( ip = '', label = '' ) => {
	cy.get( '#rsa_add_new_ip_fields input[name="newip"]' ).type( ip );
	cy.get( '#rsa_add_new_ip_fields input[name="newipcomment"]' ).type( label );
	cy.get( '#addip' ).click();
	cy.wait( 600 );
} );