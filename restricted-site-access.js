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
	
	jQuery('<span style="display: none;"><input type="text" name="rsa_options[allowed][]" value="'+ip+'" readonly="true" /><input type="button" class="button" onclick="remove_ip(this);" value="remove" /> <span class="description">Click "Save Changes" to save this IP.</span><br /></span>').appendTo('#ip_list').slideDown();
}

function remove_ip(btnObj) {
	if (confirm('Are you certain you want to remove this IP?')) jQuery(btnObj).parent().slideUp(250,function(){ jQuery(this).remove() });
}