import 'jquery-ui-dialog';

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

	$( '.notice' ).on( 'click', '.notice-dismiss', function( event ) {
		const notice = event.delegateTarget.getAttribute( 'data-rsa-notice' );

		if ( ! notice ) {
			return;
		}

		$.ajax( {
			method: 'post',
			data: {
				nonce: rsaAdmin.nonce,
				action: 'rsa_notice_dismiss',
			},
			url: ajaxurl,
		} );
	} );
}( window, jQuery ) );

( function( window, $ ) {
	'use strict';
	$( () => {
		const RSADisablePlugin = {
			els: {
				dialog: document.getElementById( 'rsa-disable-dialog' ),
				userMessage: document.getElementById( 'rsa-user-message' ),
			},

			variables: {
				expectedAnswer: rsaAdmin.strings.message.toLowerCase(),
				disablingURL: null,
			},

			openDialog( event ) {
				event.preventDefault();
				$( this.els.dialog ).dialog( 'open' );
			},

			isExpectedAnswer() {
				const userMessage = this.els.userMessage.value.toLowerCase();

				if ( userMessage === this.variables.expectedAnswer ) {
					return true;
				}

				return false;
			},
			dialogSettings() {
				const self = this;

				self.close = function() {
					$( self.els.dialog ).dialog( 'close' );
					self.els.userMessage.style.border = '1px solid #ddd';
					self.els.userMessage.value = '';
				};

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
							click() {
								if ( self.isExpectedAnswer() ) {
									window.location.href =
										self.variables.disablingURL;
								} else {
									self.els.userMessage.style.border =
										'1px solid red';
								}
							},
						},
						{
							text: rsaAdmin.strings.cancel,
							click() {
								self.close();
							},
							class: 'button-primary',
						},
					],
					open() {
						$( '.ui-widget-overlay' ).bind( 'click', function() {
							self.close();
						} );
					},
					create() {
						$( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
						$( this ).siblings( '.ui-dialog-titlebar' ).hide();
					},
				} );

				this.els.buttons = $( this.els.dialog ).dialog(
					'option',
					'buttons'
				);
			},

			maybeSubmit( event ) {
				switch ( event.key ) {
					case 'Enter':
						this.els.buttons[ 0 ].click();
						break;
				}
			},

			bindEvents() {
				$( '[data-slug="restricted-site-access"]' ).on(
					'click',
					'.deactivate a',
					this.openDialog.bind( this )
				);
				if ( this.els.userMessage ) {
					this.els.userMessage.addEventListener(
						'keyup',
						this.maybeSubmit.bind( this )
					);
				}
			},

			init() {
				const list = document.getElementById( 'the-list' );
				if ( list ) {
					this.variables.disablingURL = list.querySelector(
						'[data-slug="restricted-site-access"] .deactivate a'
					).href;
				}
				this.bindEvents();
				this.dialogSettings();
			},
		};

		RSADisablePlugin.init();
	} );
}( window, jQuery ) );
