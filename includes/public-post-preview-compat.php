<?php
/**
 * Enable Public Post Preview through Restricted Site Access
 */

namespace TenUp\PPPRSACompat;

/**
 * Verifies that correct nonce was used with time limit. Without an UID.
 *
 * Reimplements https://github.com/ocean90/public-post-preview/blob/45f2f277801cdd79996fcd63df7c7147079f5283/public-post-preview.php#L650-L676
 *
 * @see wp_verify_nonce()
 *
 * @param string     $nonce  Nonce that was used in the form to verify.
 * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
 * @return bool               Whether the nonce check passed or failed.
 */
function verify_nonce( $nonce, $action = -1 ) {
	// Reimplement nonce_tick() https://github.com/ocean90/public-post-preview/blob/45f2f277801cdd79996fcd63df7c7147079f5283/public-post-preview.php#L619-L632
	$nonce_life = apply_filters( 'ppp_nonce_life', 2 * DAY_IN_SECONDS ); // 2 days.

	$i = ceil( time() / ( $nonce_life / 2 ) );

	// Nonce generated 0-12 hours ago.
	if ( substr( wp_hash( $i . $action, 'nonce' ), -12, 10 ) === $nonce ) {
		return 1;
	}

	// Nonce generated 12-24 hours ago.
	if ( substr( wp_hash( ( $i - 1 ) . $action, 'nonce' ), -12, 10 ) === $nonce ) {
		return 2;
	}

	// Invalid nonce.
	return false;
}

/**
 * Allow requests past RSA if the request is for a Public Post Preview link
 *
 * This significantly reimplements PPP's private method DS_Public_Post_Preview::is_public_preview_available
 * https://github.com/ocean90/public-post-preview/blob/master/public-post-preview.php#L520-L529
 *
 * @param bool $is_restricted Whether RSA should restrict this request
 * @param WP $wp the WordPress Object
 * @return bool whether RSA should restrict this request
 */
function connector( $is_restricted, $wp ) {
	// convert WP_Query to post ID
	$query_vars = get_object_vars( $wp )['query_vars'];
	$post_id    = $query_vars['p'] ?? 0;
	$preview    = $query_vars['preview'] ?? 0;

	if ( empty( $post_id ) ) {
		return $is_restricted;
	}


	$_ppp = null;

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $_REQUEST['preview'] ) || 1 !== (int) $_REQUEST['preview'] ) {
		return $is_restricted;
	}

	// The 'nonce' here comes from wp_hash:
	// https://github.com/ocean90/public-post-preview/blob/45f2f277801cdd79996fcd63df7c7147079f5283/public-post-preview.php#L634-L648
	// wp_hash, unless replaced by a plugin, uses hash_hmac:
	// https://developer.wordpress.org/reference/functions/wp_hash/
	// hash_hmac returns hexits (0-9a-f):
	// https://www.php.net/manual/en/function.hash-hmac.php
	//
	// Therefore an appropriate sanitizing function is https://developer.wordpress.org/reference/functions/sanitize_key/

	$_ppp = null;

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_REQUEST['_ppp'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_ppp = sanitize_key( $_REQUEST['_ppp'] );
	}

	if ( ! verify_nonce( $_ppp, 'public_post_preview_' . $post_id ) ) {
		return false;
	}

	// reimplement get_preview_post_ids() https://github.com/ocean90/public-post-preview/blob/45f2f277801cdd79996fcd63df7c7147079f5283/public-post-preview.php#L678-L690
	$post_ids = get_option( 'public_post_preview', [] );
	$post_ids = array_map( 'intval', $post_ids );

	if ( ! in_array( $post_id, $post_ids, true ) ) {
		return false;
	}

	return true;
}

/**
 * Check if RSA is active, add our filters.
 *
 * @return void
 */
function init() {
	if ( class_exists( 'DS_Public_Post_Preview' ) ) {
		add_action( 'restricted_site_access_is_restricted', __NAMESPACE__ . '\connector', 10, 2 );
	}
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );
