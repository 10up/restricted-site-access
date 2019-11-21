/**
 * 10up
 * http://10up.com
 *
 * Copyright (c) 2013 10up, jakemgold
 * Licensed under the GPLv2+ license.
 */
( function( window, $ ) {

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

( function( window, $ ) {
	'use strict';

	var RSADisablePlugin = {

		els: {
			dialog: document.getElementById( 'rsa-disable-dialog' ),
			userMessage: document.getElementById( 'rsa-user-message' )
		},

		variables: {
			expectedAnswer: rsaAdmin.strings.message.toLowerCase(),
			disablingURL: null
		},

		openDialog: function( event ) {
			event.preventDefault();
			$( this.els.dialog ).dialog( 'open' );
		},

		isExpectedAnswer: function() {
			var userMessage = this.els.userMessage.value.toLowerCase();

			if ( userMessage === this.variables.expectedAnswer ) {
				return true;
			}

			return false;
		},
		dialogSettings: function() {
			var self = this;

			self.close = function() {
				$( self.els.dialog ).dialog( 'close' );
				self.els.userMessage.style.border = '1px solid #ddd';
				self.els.userMessage.value = '';
			}

			$( this.els.dialog ).dialog( {
				dialogClass: 'wp-dialog',
				autoOpen: false,
				draggable: false,
				width: 'auto',
				modal: true,
				resizable: false,
				buttons: [
					{
						text: rsaAdmin.strings.confirm,
						click: function() {
							if ( self.isExpectedAnswer() ) {
								window.location.href = self.variables.disablingURL;
							} else {
								self.els.userMessage.style.border = '1px solid red';
							}
						}
					},
					{
						text: rsaAdmin.strings.cancel,
						click: function() {
							self.close();
						},
						'class': 'button-primary'
					}
				],
				open: function() {
					$( '.ui-widget-overlay' ).bind( 'click', function() {
						self.close();
					});
				},
				create: function() {
					$( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
					$( this ).siblings( '.ui-dialog-titlebar' ).hide();
				}
			} );

			this.els.buttons = $( this.els.dialog ).dialog( 'option', 'buttons' );
		},

		maybeSubmit: function( event ) {
			switch ( event.key ) {
				case 'Enter':
					this.els.buttons[0].click();
					break;
			}
		},

		bindEvents: function() {
			$( '[data-slug="restricted-site-access"]' ).on( 'click', '.deactivate a', this.openDialog.bind( this ) );
			this.els.userMessage.addEventListener( 'keyup', this.maybeSubmit.bind( this ) );
		},

		init: function() {
			if ( ! rsaAdmin.isNetworkWidePluginsPage ) {
				return;
			}

			this.variables.disablingURL = document.getElementById( 'the-list' ).querySelector( '[data-slug="restricted-site-access"] .deactivate a' ).href;
			this.bindEvents();
			this.dialogSettings();
		}
	};

	RSADisablePlugin.init();
}( window, jQuery ) );
