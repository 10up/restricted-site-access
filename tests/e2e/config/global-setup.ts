/**
 * Runs once before the E2E suite.
 *
 * Turns the test site into a network with the plugin network-activated, logs
 * in as admin and saves the storage state every spec reuses, then resets the
 * site to a known state.
 */

/**
 * External dependencies
 */
import { execFileSync } from 'child_process';
import { request } from '@playwright/test';
import type { FullConfig } from '@playwright/test';

/**
 * WordPress dependencies
 */
import { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

/**
 * Internal dependencies
 */
import { setState, wpCli } from '../utils/helpers';

/** Constants `wp core multisite-convert` writes to wp-config.php. */
const NETWORK_CONSTANTS = [
	'WP_ALLOW_MULTISITE',
	'MULTISITE',
	'SUBDOMAIN_INSTALL',
	'DOMAIN_CURRENT_SITE',
	'PATH_CURRENT_SITE',
	'SITE_ID_CURRENT_SITE',
	'BLOG_ID_CURRENT_SITE',
];

/** Network option this setup writes once the network is ready for the specs. */
const READY_OPTION = 'rsa_e2e_network_ready';

/**
 * Whether the test site is a network this setup built, for the given URL.
 *
 * PHPUnit's multisite runs leave a network of their own in the same database,
 * so a listed main site alone isn't enough; the marker option is.
 *
 * @param siteUrl Site URL.
 * @return True when WP-CLI lists the main site and the marker is set.
 */
function isNetwork( siteUrl: string ): boolean {
	try {
		return (
			wpCli( [ 'site', 'list', '--field=url' ] )
				.split( '\n' )
				.includes( siteUrl ) &&
			'1' === wpCli( [ 'site', 'option', 'get', READY_OPTION ] )
		);
	} catch {
		return false;
	}
}

/**
 * Makes the test site a network (subdirectory install) with the plugin
 * network-activated, the state the specs expect.
 *
 * A fresh environment is a single site. The site is converted here rather
 * than with wp-env's "multisite" option, because that option writes MULTISITE
 * into the wp-config.php wp-env copies the PHPUnit config from, which would
 * turn the single-site PHPUnit run into a multisite one. PHPUnit reinstalls
 * the database, so a run after PHPUnit finds a broken network, or PHPUnit's own
 * network: reinstall it.
 *
 * Once this has run, re-configuring the environment (a config change or
 * `start --update`) copies MULTISITE into the PHPUnit config as well, and
 * PHPUnit stops with a database error. Recreate the environment then:
 * `npm run wp-env:test -- cleanup --force`, then `npm run wp-env:test start`.
 *
 * @param siteUrl Site URL, with a trailing slash.
 */
function ensureNetwork( siteUrl: string ) {
	if ( ! isNetwork( siteUrl ) ) {
		// Let WP-CLI rewrite wp-config.php and .htaccess whichever user owns the web root.
		execFileSync(
			'npm',
			[
				'run',
				'--silent',
				'wp-env:test',
				'--',
				'run',
				'wordpress',
				'chmod',
				'ugo+w',
				'/var/www/html',
			],
			{ stdio: [ 'ignore', 'pipe', 'pipe' ] }
		);

		for ( const constant of NETWORK_CONSTANTS ) {
			try {
				wpCli( [ 'config', 'delete', constant ] );
			} catch {
				// Not defined.
			}
		}

		wpCli( [ 'db', 'reset', '--yes' ] );
		wpCli( [
			'core',
			'install',
			`--url=${ siteUrl }`,
			'--title=RSA Multisite',
			'--admin_user=admin',
			'--admin_password=password',
			'--admin_email=wordpress@example.com',
			'--skip-email',
		] );
		wpCli( [ 'rewrite', 'structure', '/%postname%/', '--hard' ] );
		wpCli( [ 'core', 'multisite-convert', '--title=RSA Multisite' ] );
		// Converting sets the main site's /blog/ permalink structure; rebuild the rules for it.
		wpCli( [ 'rewrite', 'flush' ] );

		wpCli( [ 'site', 'option', 'update', READY_OPTION, '1' ] );

		if ( ! isNetwork( siteUrl ) ) {
			throw new Error( 'Could not turn the test site into a network.' );
		}
	}

	// The specs cover network-level settings, so the plugin is network-active.
	wpCli( [
		'plugin',
		'activate',
		'restricted-site-access',
		'restricted-site-access-e2e-helpers',
		'--network',
	] );
}

/**
 * Creates the published pages the redirect specs request, unless they exist.
 *
 * @param requestUtils Request utils, authenticated as admin.
 */
async function ensurePages( requestUtils: RequestUtils ) {
	for ( const title of [ 'One', 'Two' ] ) {
		const pages = ( await requestUtils.rest( {
			path: '/wp/v2/pages',
			params: { slug: title.toLowerCase(), status: 'publish' },
		} ) ) as unknown[];

		if ( 0 === pages.length ) {
			await requestUtils.createPage( { title, status: 'publish' } );
		}
	}
}

async function globalSetup( config: FullConfig ) {
	const { storageState, baseURL } = config.projects[ 0 ].use;
	const storageStatePath =
		typeof storageState === 'string' ? storageState : undefined;

	ensureNetwork( new URL( '/', baseURL ).href );

	const requestContext = await request.newContext( { baseURL } );
	const requestUtils = new RequestUtils( requestContext, {
		storageStatePath,
	} );

	// Authenticate and save the storageState to disk.
	await requestUtils.setupRest();

	// Reset the test environment before running the tests.
	await Promise.all( [
		requestUtils.deleteAllPosts(),
		requestUtils.resetPreferences(),
		ensurePages( requestUtils ),
		// The admin bar specs change the admin's role; undo a run that stopped midway.
		setState( requestUtils, { user_role: 'administrator' } ),
	] );

	await requestContext.dispose();
}

export default globalSetup;
