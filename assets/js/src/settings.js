import 'jquery-effects-shake';

/**
 * 10up
 * http://10up.com
 *
 * Copyright (c) 2013 10up, jakemgold
 * Licensed under the GPLv2+ license.
 *
 * @param {Object} window Global window object
 * @param {Object} $      The jQuery object.
 */
( function( window, $ ) {
	'use strict';
	const document = window.document;

	const Cache = {
		add_btn: '',
		new_ip: '',
		ip_list_wrap: '',
		empty_ip: '',
		restrict_radio: '',
		table: '',
		header: '',
		redirect_choice: '',
		message_choice: '',
		page_choice: '',
		redirect_fields: '',
		message_field: '',
		page_field: '',
		error_field: '',
		submit_btn: '',
	};

	function init() {
		Cache.add_btn = $( document.getElementById( 'addip' ) );
		Cache.new_ip = document.getElementById( 'newip' );
		Cache.new_ip_comment = document.getElementById( 'newipcomment' );
		Cache.ip_list_wrap = document.getElementById( 'ip_list' );
		Cache.empty_ip = $( document.getElementById( 'ip_list_empty' ) );
		Cache.restrict_radio = document.getElementById( 'blog-restricted' );
		Cache.error_field = document.getElementById( 'rsa-error-container' );
		Cache.table = $(
			document.getElementById( 'rsa-send-to-login' )
		).closest( 'table' );
		Cache.header = Cache.table.prev( 'h2' );
		Cache.redirect_choice = document.getElementById(
			'rsa-redirect-visitor'
		);
		Cache.message_choice = document.getElementById( 'rsa-display-message' );
		Cache.page_choice = document.getElementById( 'rsa-unblocked-page' );
		Cache.redirect_fields = $(
			document.querySelectorAll( '.rsa_redirect_field' )
		).closest( 'tr' );
		Cache.message_field = $(
			document.getElementById( 'rsa_message' )
		).closest( 'tr' );
		Cache.page_field = $( document.getElementById( 'rsa_page' ) ).closest(
			'tr'
		);
		Cache.submit_btn = $( '#submit' );

		if ( Cache.restrict_radio && ! Cache.restrict_radio.checked ) {
			Cache.table.hide();
			Cache.header.hide();
		}

		if ( Cache.redirect_choice && ! Cache.redirect_choice.checked ) {
			Cache.redirect_fields.hide();
		}

		if ( Cache.message_choice && ! Cache.message_choice.checked ) {
			Cache.message_field.hide();
		}

		if ( Cache.page_choice && ! Cache.page_choice.checked ) {
			Cache.page_field.hide();
		}

		$( document.querySelectorAll( '#rsa_handle_fields input' ) ).on(
			'change',
			function() {
				if ( Cache.redirect_choice.checked ) {
					Cache.redirect_fields.show();
				} else {
					Cache.redirect_fields.hide();
				}

				if ( Cache.message_choice.checked ) {
					Cache.message_field.show();
				} else {
					Cache.message_field.hide();
				}

				if ( Cache.page_choice.checked ) {
					Cache.page_field.show();
				} else {
					Cache.page_field.hide();
				}
			}
		);

		$( document.querySelectorAll( '.option-site-visibility input' ) ).on(
			'change',
			function() {
				if ( Cache.restrict_radio.checked ) {
					Cache.header.show();
					Cache.table.show();
				} else {
					Cache.header.hide();
					Cache.table.hide();
				}
			}
		);

		Cache.add_btn.on( 'click', function() {
			const newIp = Cache.empty_ip
				.clone()
				.appendTo( Cache.ip_list_wrap );
			newIp.removeAttr( 'id' ).slideDown( 250 );
		} );

		$( Cache.ip_list_wrap ).on( 'blur', '.ip.code', function() {
			addIp( $( this ).val(), $( this ).next().val(), $( this ) );
		} );

		const myipBtn = document.getElementById( 'rsa_myip' );
		if ( null !== myipBtn ) {
			$( myipBtn ).on( 'click', function() {
				$( '.ip.code:last' ).val( $( this ).data( 'myip' ) ).blur();
			} );
		}

		$( Cache.ip_list_wrap ).on( 'click', '.remove_btn', function() {
			$( this.parentNode ).slideUp( 250, function() {
				$( this ).remove();
			} );
		} );
	}

	function addIp( ip, comment, obj ) {
		const shakeSpeed = 600;
		Cache.submit_btn.prop( 'disabled', true );

		if ( $.trim( ip ) === '' ) {
			Cache.submit_btn.prop( 'disabled', false );
			return;
		}

		const ipList = $( document.querySelectorAll( '#ip_list input' ) );

		for ( let i = 0; i < ipList.length; i++ ) {
			if ( ! obj.is( ipList[ i ] ) && ipList[ i ].value === ip ) {
				$( ipList[ i ] ).parent().effect( 'shake', shakeSpeed );
				$( obj ).focus();
				return;
			}
		}

		jQuery.post(
			ajaxurl,
			{
				action: 'rsa_ip_check',
				ip_address: ip,
				ip_address_comment: comment,
				nonce: rsaSettings.nonce,
			},
			function( response ) {
				if ( ! response.success ) {
					$( obj ).effect( 'shake', shakeSpeed ).focus();
					$( Cache.error_field ).text( response.data );
					return false;
				}

				$( Cache.error_field ).text( '' );

				Cache.submit_btn.prop( 'disabled', false );

				return true;
			}
		);
	}

	$( function() {
		init();
	} );
}( window, jQuery ) );
