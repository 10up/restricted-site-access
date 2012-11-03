function add_ip( ip ) {
	var shake_speed = 600;
	if ( restricted_site_access_l10n.wp_version < 3.5 )
		shake_speed = 60;

	if ( jQuery.trim(ip) == '' )
		return false;

	add_btn.attr('disabled', 'disabled');

	var ips = jQuery('#ip_list input');
	for ( var i = 0, l = ips.length; i < ips.length; i++ ) {
		if( ips[i].value == ip ) {
			jQuery(ips[i]).parent().effect('shake',shake_speed);
			add_btn.removeAttr('disabled');
			return false;
		}
	}

	jQuery.post( ajaxurl, { action: 'rsa_ip_check', 'ip_address': ip }, function(response) {
		if ( response ) {
			jQuery('#newip').parent().effect('shake',shake_speed);
			add_btn.removeAttr('disabled');
			return false;
		} else {
			jQuery('<div style="display: none;"><input type="text" name="rsa_options[allowed][]" value="' + ip + '" readonly="true" /> <a href="#remove" onclick="remove_ip(this);">' + restricted_site_access_l10n.Remove + '</a></div>').appendTo('#ip_list').slideDown(250);
			if ( ip == jQuery('#newip').val() )
				jQuery('#newip').val('');
			jQuery('#addip').removeAttr('disabled');
			return true;
		}
	} );
}

function remove_ip( btnObj ) {
	jQuery(btnObj).parent().slideUp(250,function(){ jQuery(this).remove(); });
}

var add_btn;

jQuery(document).ready(function($){
	// hide and show relevant pieces
	add_btn = $('#addip');
	var rsa_table = $('#rsa-send-to-login').closest('table');
	var rsa_redirect_fields = $('.rsa_redirect_field').closest('tr');
	var rsa_messsage_field = $('#rsa_message').closest('tr');
	var rsa_page_field = $('#rsa_page').closest('tr');

	if ( ! $('#blog-restricted').is(':checked') )
		rsa_table.hide();
	if ( ! $('#rsa-redirect-visitor').is(':checked') )
		rsa_redirect_fields.hide();
	if ( ! $('#rsa-display-message').is(':checked') )
		rsa_messsage_field.hide();
	if ( ! $('#rsa-unblocked-page').is(':checked') )
		rsa_page_field.hide();

	$('input[name="rsa_options[approach]"]').change(function(){
		if( $('#rsa-redirect-visitor').is(':checked') )
			rsa_redirect_fields.show();
		else
			rsa_redirect_fields.hide();

		if( $('#rsa-display-message').is(':checked') )
			rsa_messsage_field.show();
		else
			rsa_messsage_field.hide();

		if( $('#rsa-unblocked-page').is(':checked') )
			rsa_page_field.show();
		else
			rsa_page_field.hide();
	});

	$('input[name="blog_public"]').change(function(){
		if( $('#blog-restricted').is(':checked') )
			rsa_table.show();
		else
			rsa_table.hide();
	});
});