describe( 'Handle restricted visitors - Show simple message', () => {
	before( () => {
		cy.wpCli( `network meta set 1 blog_public 2` );
		cy.wpCli( `network meta set 1 rsa_mode enforce` );
	} );
	
	it( 'Show simple message - Visitor can still access requested page ', () => {
		cy.wpCli( `network meta set 1 rsa_options '{"approach":3,"message":"Access to this site is restricted.","redirect_path":0,"head_code":302,"redirect_url":"","page":0,"allowed":["172.13.24.5"],"comment":[""]}' --format=json` );
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
			headers: {
				'X-Forwarded': '172.13.24.5',
			}
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/sample-page/' );
		} );
	} );

	it( 'Redirect to web address - Visitor can still access requested page ', () => {
		cy.wpCli( `network meta set 1 rsa_options '{"approach":2,"message":"Access to this site is restricted.","redirect_path":0,"head_code":301,"redirect_url":"http:\/\/localhost:8889\/page-to-redirect\/","page":0,"allowed":["172.13.24.5"],"comment":[]}' --format=json` );
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
			headers: {
				'X-Forwarded': '172.13.24.5',
			}
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/sample-page/' );
		} );
	} );

	it( 'Send to login screen - Visitor can still access requested page ', () => {
		cy.wpCli( `network meta set 1 rsa_options '{"approach":2,"message":"Access to this site is restricted.","redirect_path":0,"head_code":301,"redirect_url":"http:\/\/localhost:8889\/page-to-redirect\/","page":0,"allowed":["172.13.24.5"],"comment":[]}' --format=json` );
		cy.request( {
			url: 'sample-page',
			followRedirect: false,
			headers: {
				'X-Forwarded': '172.13.24.5',
			}
		} ).then( ( resp ) => {
			expect( resp.redirectedToUrl ).to.eq( 'http://localhost:8889/sample-page/' );
		} );
	} );
} );
