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

(function (window, $) {
	'use strict';

	var RSADisablePlugin = {

		els: {
			dialog: document.getElementById( 'rsa-disable-dialog' ),
			placeholderA: document.getElementById( 'rsa-operator-a' ),
			placeholderB: document.getElementById( 'rsa-operator-b' )
		},

		variables: {
			operatorA: 0,
			operatorB: 0,
			expectedAnswer: 0,
			disablingURL: null
		},

		openDialog: function ( event ) {
			event.preventDefault();
			$( this.els.dialog ).dialog( 'open' );
		},

		getRandomNum: function () {
			return Math.floor( Math.random() * 10 );
		},

		isExpectedAnswer: function () {
			var userResult = parseInt( document.getElementById( 'rsa-user-result' ).value, 10 );

			if ( userResult === this.variables.expectedAnswer ) {
				return true;
			}

			return false;
		},
		dialogSettings: function () {
			var self = this;

			$( this.els.dialog ).dialog({
				title: '⚠️ ' + rsaAdmin.strings.warning,
				dialogClass: 'wp-dialog',
				autoOpen: false,
				draggable: false,
				width: 'auto',
				modal: true,
				resizable: false,
				buttons: [
					{
						text: rsaAdmin.strings.confirm,
						click: function () {
							$( this ).dialog( 'close' );

							if ( self.isExpectedAnswer() ) {
								$.ajax({
									method: 'post',
									data: {
										nonce: rsaAdmin.nonce,
										user: rsaAdmin.user,
										action: 'rsa_network_disable_log'
									},
									url: ajaxurl
								}).always(function() {
									window.location.href = self.variables.disablingURL;
								});
							}
						}
					},
					{
						text: rsaAdmin.strings.cancel,
						click: function () {
							$( this ).dialog( 'close' );
						}
					}
				],
				open: function () {
					self.refreshValues();
					$( '.ui-widget-overlay' ).bind( 'click', function () {
						$(self.els.dialog).dialog( 'close' );
					});
				},
				create: function () {
					$( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
				},
			})
		},

		refreshValues: function () {
			var vars = this.variables;

			vars.operatorA = this.getRandomNum();
			vars.operatorB = this.getRandomNum();
			vars.expectedAnswer = vars.operatorA + vars.operatorB;

			this.els.placeholderA.innerText = vars.operatorA;
			this.els.placeholderB.innerText = vars.operatorB;
		},

		bindEvents: function () {
			$( '[data-slug="restricted-site-access"]' ).on( 'click', '.deactivate a', this.openDialog.bind( this ) );
		},

		init: function () {
			if ( ! Boolean( rsaAdmin.isNetworkWidePluginsPage ) ) {
				return;
			}

			this.variables.disablingURL = document.getElementById( 'the-list' ).querySelector( '[data-slug="restricted-site-access"] .deactivate a' ).href;
			this.bindEvents();
			this.refreshValues();
			this.dialogSettings();
		}
	};

	RSADisablePlugin.init();
})(window, jQuery);
