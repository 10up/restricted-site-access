<?php

// Any common helper functions to configure RSA will go here.

function rsa_tests_update_options( $options ) {
	if ( is_multisite() ) {
		update_site_option( 'rsa_options', $options );
	} else {
		update_option( 'rsa_options', $options );
	}
}
