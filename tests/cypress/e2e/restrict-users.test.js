describe( 'Handle restricted visitors - Send to login screen', () => {
	before( () => {
		cy.wpCli( `network meta set 1 blog_public 2` );
		cy.wpCli( `network meta set 1 rsa_mode enforce` );
		cy.wpCli( `network meta set 1 rsa_options '{"approach":1,"message":"Access to this site is restricted.","redirect_path":0,"head_code":302,"redirect_url":"","page":0,"allowed":["172.13.24.5"],"comment":[""]}' --format=json` );
	} );

	it( 'Verify - Visitor is redirected to login screen', () => {
		cy.visit( `sample-page`, {
			failOnStatusCode: false,
		} );

		cy.contains( 'Username or Email Address' );
	} );
} );

describe( 'Handle restricted visitors - Redirect to web address', () => {
	it( 'Verify - Redirection with status code 301', () => {
		cy.wpCli( `network meta set 1 rsa_options '{"approach":2,"message":"Access to this site is restricted.","redirect_path":0,"head_code":301,"redirect_url":"http:\/\/localhost:8889\/page-to-redirect\/","page":0,"allowed":["172.13.24.5"],"comment":[]}' --format=json` );
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.status ).to.eq( 301 );
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/' );
		} );
	} );

	it( 'Verify - Redirection with status code 302', () => {
		cy.wpCli( `network meta set 1 rsa_options '{"approach":2,"message":"Access to this site is restricted.","redirect_path":0,"head_code":302,"redirect_url":"http:\/\/localhost:8889\/page-to-redirect\/","page":0,"allowed":["172.13.24.5"],"comment":[]}' --format=json` );
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.status ).to.eq( 302 );
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/' );
		} );
	} );

	it( 'Verify - Redirection with status code 307', () => {
		cy.wpCli( `network meta set 1 rsa_options '{"approach":2,"message":"Access to this site is restricted.","redirect_path":0,"head_code":307,"redirect_url":"http:\/\/localhost:8889\/page-to-redirect\/","page":0,"allowed":["172.13.24.5"],"comment":[]}' --format=json` );
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
		} ).then( ( resp ) => {
			expect( resp.status ).to.eq( 307 );
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/page-to-redirect/' );
		} );
	} );
} );

describe( 'Handle restricted visitors - Show simple message', () => {
	before( () => {
		cy.wpCli( `network meta set 1 rsa_options '{"approach":3,"message":"Access to this site is restricted.","redirect_path":0,"head_code":302,"redirect_url":"","page":0,"allowed":["172.13.24.5"],"comment":[""]}' --format=json` );
	} );

	it( 'Verify - Show simple message', () => {
		cy.visit( `sample-page`, {
			failOnStatusCode: false,
		} );

		cy.contains( 'Access to this site is restricted.' );
	} );
} );
