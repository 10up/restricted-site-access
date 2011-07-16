// functions for dynamic IP address boxes

function add_ip(ip) {
	if (!jQuery.trim(ip)) return false;
	
	jQuery('#message').remove();
	
	var ip_used = false;
	jQuery('#ip_list input').each(function(){
		if (jQuery(this).val() == ip) {
			jQuery(this).animate( { marginLeft: '-45px' }, 250, 'swing', function(){ jQuery(this).animate( { marginLeft: '0px' }, 250, 'swing' ); } );
			ip_used = true;
			return false; 
		}
	});
	if (ip_used) return false;
	
	jQuery('<span style="display: none;"><input type="text" name="rsa_options[allowed][]" value="'+ip+'" readonly="true" /><input type="button" class="button" onclick="remove_ip(this);" value="' + restricted_site_access_l10n.Remove + '" /> <span class="description">' + restricted_site_access_l10n.SaveChanges + '</span><br /></span>').appendTo('#ip_list').slideDown();
}

function cancel_remove_ip(btnObj) {
	jQuery(btnObj).siblings('.button-primary').removeClass('button-primary').val(restricted_site_access_l10n.Remove);
	jQuery(btnObj).remove();
}

function remove_ip(btnObj) {
	var jbtnObj = jQuery(btnObj);
	if ( jbtnObj.hasClass('button-primary') ) jbtnObj.parent().slideUp(250,function(){ jQuery(this).remove() });
	else jbtnObj.val(restricted_site_access_l10n.ConfirmRemove).addClass('button-primary').after('<input type="button" value="' + restricted_site_access_l10n.Cancel + '" class="button" onclick="cancel_remove_ip(this);" />');
}

// hide and show relevant pieces
var rsa_table = jQuery('#rsa-send-to-login').closest('table');
var rsa_redirect_fields = jQuery('.rsa_redirect_field').closest('tr');
var rsa_messsage_field = jQuery('#rsa_message').closest('tr');
var rsa_page_field = jQuery('#rsa_page').closest('tr');

if ( jQuery('#blog-restricted:checked').length <= 0 ) rsa_table.hide();
if ( jQuery('#rsa-redirect-visitor:checked').length <= 0 ) rsa_redirect_fields.hide();
if ( jQuery('#rsa-display-message:checked').length <= 0 ) rsa_messsage_field.hide();
if ( jQuery('#rsa-unblocked-page:checked').length <= 0 ) rsa_page_field.hide();

jQuery('input[name="rsa_options[approach]"]').change(function(){
	if( jQuery('#rsa-redirect-visitor').is(':checked') ) rsa_redirect_fields.show();
	else rsa_redirect_fields.hide();
	
	if( jQuery('#rsa-display-message').is(':checked') ) rsa_messsage_field.show();
	else rsa_messsage_field.hide();
	
	if( jQuery('#rsa-unblocked-page').is(':checked') ) rsa_page_field.show();
	else rsa_page_field.hide();
});

jQuery('input[name="blog_public"]').change(function(){
	if( jQuery('#blog-restricted').is(':checked') ) rsa_table.show();
	else rsa_table.hide();	
});