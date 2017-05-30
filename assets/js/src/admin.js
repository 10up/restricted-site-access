/**
 * 10up
 * http://10up.com
 *
 * Copyright (c) 2013 10up, jakemgold
 * Licensed under the GPLv2+ license.
 */
( function ( window, $ ) {

	'use strict';

	$( '.notice' ).on( 'click', '.notice-dismiss', function( event ) {
		var notice = event.delegateTarget.getAttribute( 'data-rsa-notice' );

		if ( ! notice ) {
			return;
		}

		$.ajax( {
			method: 'post',
			data: {
				nonce: rsaAdmin.nonce,
				action: 'rsa_notice_dismiss'
			},
			url: ajaxurl
		} );
	} );
} )( window, jQuery );
