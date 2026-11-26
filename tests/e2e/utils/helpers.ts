/**
 * Helpers shared by the E2E specs.
 */

/**
 * External dependencies
 */
import { execFileSync } from 'child_process';
import type {
	APIRequestContext,
	APIResponse,
	PlaywrightWorkerArgs,
} from '@playwright/test';

/**
 * WordPress dependencies
 */
import type { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

/** The plugin's default options, as the network settings screen saves them. */
export const DEFAULT_OPTIONS = {
	approach: 1,
	message: 'Access to this site is restricted.',
	redirect_path: 0,
	head_code: 302,
	redirect_url: '',
	page: 0,
	allowed: [] as string[],
	comment: [] as string[],
};

/** State the helper plugin's /rsa-e2e/v1/state route accepts. */
type State = {
	rsa_mode?: 'default' | 'enforce';
	blog_public?: number;
	rsa_options?: Partial< typeof DEFAULT_OPTIONS > & {
		hide_admin_bar_roles?: string[];
	};
	user_role?: string;
	remove_user_role?: boolean;
	add_role?: { role: string; name: string };
	deactivate?: boolean;
};

/**
 * Runs WP-CLI in the test environment's cli container and returns its output.
 *
 * Slow (it starts a container per call), so keep it out of per-test hooks.
 * On failure the thrown error includes WP-CLI's error output.
 *
 * @param args WP-CLI arguments, without the leading `wp`.
 * @return Trimmed standard output.
 */
export function wpCli( args: string[] ): string {
	return execFileSync(
		'npm',
		[ 'run', '--silent', 'wp-env:test', '--', 'run', 'cli', 'wp', ...args ],
		{ encoding: 'utf8', stdio: [ 'ignore', 'pipe', 'pipe' ] }
	).trim();
}

/**
 * Sets plugin state through the helper plugin.
 *
 * @param requestUtils Request utils, authenticated as the network admin.
 * @param state        State to set; see the helper plugin for each key.
 */
export async function setState(
	requestUtils: RequestUtils,
	state: State
): Promise< void > {
	await requestUtils.rest( {
		method: 'POST',
		path: '/rsa-e2e/v1/state',
		data: state,
	} );
}

/**
 * Restricts the whole network (enforce mode) with the given plugin options.
 *
 * @param requestUtils Request utils, authenticated as the network admin.
 * @param options      Options to merge onto the plugin defaults.
 */
export async function restrictNetwork(
	requestUtils: RequestUtils,
	options: State[ 'rsa_options' ] = {}
): Promise< void > {
	await setState( requestUtils, {
		rsa_mode: 'enforce',
		blog_public: 2,
		rsa_options: { ...DEFAULT_OPTIONS, ...options },
	} );
}

/**
 * Creates a request context with no cookies, so requests come from a
 * logged-out visitor rather than the admin the other specs act as.
 *
 * @param playwright Playwright worker fixture.
 * @param baseURL    Site URL.
 * @param headers    Extra headers to send with every request.
 * @return The request context; dispose it after use.
 */
export function visitorRequest(
	playwright: PlaywrightWorkerArgs[ 'playwright' ],
	baseURL: string | undefined,
	headers: Record< string, string > = {}
): Promise< APIRequestContext > {
	return playwright.request.newContext( {
		baseURL,
		storageState: { cookies: [], origins: [] },
		extraHTTPHeaders: headers,
	} );
}

/**
 * Returns the absolute URL a redirect response points to.
 *
 * @param response Response of a request sent with maxRedirects: 0.
 * @return Absolute URL from the Location header, or '' when there is none.
 */
export function redirectTarget( response: APIResponse ): string {
	const location = response.headers().location;

	return location ? new URL( location, response.url() ).href : '';
}
