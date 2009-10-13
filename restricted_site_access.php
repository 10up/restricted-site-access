<?php
/**
 Plugin Name: Restricted Site Access
 Plugin URI: http://www.cmurrayconsulting.com/software/wordpress-restricted-site-access/
 Description: <strong>Limit access your site</strong> to visitors who are logged in or accessing the site from a set of specific IP addresses. Send restricted visitors to the log in page, redirect them, or display a message. <strong>Powerful control over redirection</strong>, with option to send to same path and send <strong>SEO friendly redirect headers</strong>. Great solution for Extranets, publicly hosted Intranets, or parallel development sites.
 Version: 1.0.2
 Author: Jacob M Goldman (C. Murray Consulting)
 Author URI: http://www.cmurrayconsulting.com

    Plugin: Copyright 2009 C. Murray Consulting  (email : jake@cmurrayconsulting.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


//*********//
//PLUG INIT//
//*********//
function rsa_admin_init() {
	register_setting('rsa-options', 'rsa_is_active');
	register_setting('rsa-options', 'rsa_allowed_ips');
	register_setting('rsa-options', 'rsa_restrict_approach');
	/*
	1: send to login page
	2: redirect to URL
	3: show restricted access message and exit
	*/
	register_setting('rsa-options', 'rsa_redirect_path');
	register_setting('rsa-options', 'rsa_redirect_url');
	register_setting('rsa-options', 'rsa_redirect_head');
}
add_action( 'admin_init', 'rsa_admin_init' );

function rsa_plugin_actlinks( $links ) { 
	// Add a link to this plugin's settings page
	$plugin = plugin_basename(__FILE__);
	$settings_link = sprintf( '<a href="options-general.php?page=%s">%s</a>', $plugin, __('Settings') ); 
	array_unshift( $links, $settings_link ); 
	return $links; 
}
if(is_admin()) add_filter("plugin_action_links_".$plugin, 'rsa_plugin_actlinks' );

//*******************//
//***CORE FUNCTION***//
//*******************//

function restricted_site_access() {
	//logged in users can stay, can stay if plug-in not active
	if (is_user_logged_in() || get_option('rsa_is_active') != 1) return false;
	//if we're not on a front end page, stay put
	if (!is_singular() && !is_archive() && !is_feed() && !is_home()) return false;
	//check if the IP is allowed
	if (strstr(get_option('rsa_allowed_ips'),$_SERVER['REMOTE_ADDR'])) return false;
	
	$rsa_restrict_approach = intval(get_option('rsa_restrict_approach'));
	switch ($rsa_restrict_approach) {
		case 1:
			$new_url = (is_home()) ? get_bloginfo("url") : get_permalink(); 
			wp_redirect(wp_login_url($new_url));
			exit;
		case 2:
			// get base url
			$rsa_redirect_url = get_option('rsa_redirect_url');
			if (!$rsa_redirect_url) return false;
			
			// if redirecting to same path get info
			if(get_option('rsa_redirect_path') == 1) $rsa_redirect_url = $rsa_redirect_url.$_SERVER["REQUEST_URI"];
			
			$rsa_redirect_head = get_option('rsa_redirect_head');
			
			//backwards compability for WordPress upgrades from 1.0.1 and earlier
			if (strlen($rsa_redirect_head) > 3) {
				$rsa_redirect_head = substr($rsa_redirect_head, 0, 3);
				update_option("rsa_redirect_head",$rsa_redirect_head);
			}
			$rsa_redirect_head = (!$rsa_redirect_head) ? 302 : intval($rsa_redirect_head);
			
			wp_redirect($rsa_redirect_url, $rsa_redirect_head);
			exit;
		case 3:
			exit("Access to this site is restricted.");
	}
}
if(!is_admin()) add_action('wp','restricted_site_access');

//************************//
//** ADMIN CONTROL PANEL *//
//************************//

function rsa_options() {
?>
	<script type="text/javascript" language="javascript">
		function add_my_ip() {
			var rsa_allowed_ips = jQuery('#rsa_allowed_ips').val() + ' <?php echo $_SERVER['REMOTE_ADDR']; ?>';
			jQuery('#rsa_allowed_ips').val(jQuery.trim(rsa_allowed_ips));
			return false;
		}
		
		function change_approach(approach_choice) {
			if (approach_choice == 2) jQuery("tr.redirect_field").fadeIn(500);
			else jQuery("tr.redirect_field").fadeOut(500);
		}
		
		function check_redirect() {
			if (jQuery("#rsa_is_active:checked").val() == 1 && jQuery("#rsa_restrict_approach").val() == 0) {
				alert('When restricted access is turned on, restriction handling must be selected.');
				jQuery("#rsa_restrict_approach").focus();
				return false;	
			}
			if (jQuery("#rsa_restrict_approach").val() != 2) return true;
			var redirect_url = jQuery("#rsa_redirect_url").val();
			if (redirect_url.substring(0,7) != "http://" && redirect_url.substring(0,8) != "https://") {
				alert('The redirect location must be a valid URL starting with http:// or https://.');
				jQuery("#rsa_redirect_url").focus();
				return false;
			}
			return true;
		}
	</script>
	<div class="wrap">
		<h2>Restricted Site Access Configuration</h2>
	
		<div id="poststuff" style="margin-top: 20px;">
	
			<div class="postbox" style="width: 215px; min-width: 215px; float: right;">
				<h3 class="hndle">Support us</h3>
				<div class="inside">
					<p>Help support continued development of Restricted Site Access and our other plugins.</p>
					<p>The best thing you can do is <strong>refer someone looking for web development or strategy work <a href="http://www.cmurrayconsulting.com" target="_blank">to our company</a></strong>. Learn more about our <a href="http://www.cmurrayconsulting.com/services/partners/wordpress-developer/" target="_blank">Wordpress experience and services</a>.</p>
					<p>Short of that, please consider a donation. If you cannot afford even a small donation, please consider providing a link to our website, maybe in a blog post acknowledging this plugin.</p>
					<form method="post" action="https://www.paypal.com/cgi-bin/webscr" style="text-align: left;">
					<input type="hidden" value="_s-xclick" name="cmd"/>
					<input type="hidden" value="3377715" name="hosted_button_id"/>
					<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!"/> <img height="1" border="0" width="1" alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif"/><br/>
					</form>
					<p><strong><a href="http://www.cmurrayconsulting.com/software/wordpress-restricted-site-access/">Support page</a></strong></p>
				</div>
			</div>
			
			<form method="post" action="options.php" onsubmit="return check_redirect();">
			<?php settings_fields('rsa-options'); ?>			
				<div class="postbox" style="width: 350px;">
					<h3 class="hndle">Options</h3>
					<div class="inside">
						<table class="form-table" style="clear: none;">
							<tr valign="top">
								<th scope="row" valign="top">Restrict access [<a href="#" onclick="alert('Activates the plug-in and restriction rules.'); return false;" style="cursor: help;">?</a>]</th>
								<td style="padding: 10px;"><input type="checkbox" name="rsa_is_active" value="1" id="rsa_is_active"<?php if (get_option('rsa_is_active')) { echo ' checked="true"'; } ?> /></td>
							</tr>
							<tr valign="top">
								<th scope="row" style="vertical-align: middle;">Restriction Handling [<a href="#" onclick="alert('Choose the method for handling visitors who do not have access. You may send them to the login page for the current site, redirect them, or simply output a message indicating that the site is restricted.'); return false;" style="cursor: help;">?</a>]</th>
								<td style="padding: 10px;">
									<select name="rsa_restrict_approach" id="rsa_restrict_approach" onchange="change_approach(selectedIndex);">
										<?php $rsa_restrict_approach = intval(get_option('rsa_restrict_approach')); ?>
										<option value="0"<?php if (!$rsa_restrict_approach) echo ' selected="selected"'; ?>>Select handling</option>
										<option value="1"<?php if ($rsa_restrict_approach == 1) echo ' selected="selected"'; ?>>Send to login page</option>
										<option value="2"<?php if ($rsa_restrict_approach == 2) echo ' selected="selected"'; ?>>Redirect visitor</option>
										<option value="3"<?php if ($rsa_restrict_approach == 3) echo ' selected="selected"'; ?>>Display message</option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" style="padding-top: 15px;">Allowed IPs [<a href="#" onclick="alert('Enter WAN IP addresses that are allowed access without logging in into this field. Best practice is to seperate IPs with a space, line break, comma, or semicolon. You may leave this field blank to restrict all IPs.'); return false;" style="cursor: help;">?</a>]</th>
								<td style="padding: 10px;">
									<textarea name="rsa_allowed_ips" id="rsa_allowed_ips" rows="5" style="width: 100%;"><?php echo get_option('rsa_allowed_ips'); ?></textarea>
									<small>&nbsp; <a href="#" onclick="return add_my_ip();">add my current IP</a></small>
								</td>
							</tr>
							
							<tr valign="top" class="redirect_field"<?php if ($rsa_restrict_approach != 2) echo ' style="display: none;"'; ?>> 
								<th scope="row" style="vertical-align: middle;">Redirect to [<a href="#" onclick="alert('Enter the URL that the visitor should be redirected to.'); return false;" style="cursor: help;">?</a>]</th>
								<td style="padding: 10px;"><input type="text" name="rsa_redirect_url" id="rsa_redirect_url" value="<?php echo get_option('rsa_redirect_url'); ?>" style="width: 100%;" /></td>
							</tr>
							<tr valign="top" class="redirect_field"<?php if ($rsa_restrict_approach != 2) echo ' style="display: none;"'; ?>>
								<th scope="row" valign="top" style="padding-top: 0;"><em>...with same path</em> [<a href="#" onclick="alert('If you would like to redirect the visitor to the same path (URI) he or she entered this site at (the rest of the URL after the base URL), check this option. This is typically used when there are two, very similar sites at different public web addresses; for instance, a development server open to the Internet but not intended for the public.'); return false;" style="cursor: help;">?</a>]</th>
								<td style="padding-top: 0;"><input type="checkbox" name="rsa_redirect_path" value="1" id="rsa_redirect_path"<?php if (get_option('rsa_redirect_path')) { echo ' checked="true"'; } ?> /></td>
							</tr>
							<tr valign="top" class="redirect_field"<?php if ($rsa_restrict_approach != 2) echo ' style="display: none;"'; ?>>
								<th scope="row" style="vertical-align: middle; padding-top: 0;">Redirect type header [<a href="#" onclick="alert('Redirect type headers can provide certain visitors, particularly search engines, more information about the nature of the redirect. A 301 redirect tells search engines that the page has moved permanently to the new location. 307 indicates a temporary redirect. 302 is an undefined redirect.'); return false;" style="cursor: help;">?</a>]</th>
								<td style="padding-top: 0;">
									<select name="rsa_redirect_head" id="rsa_redirect_head">
										<?php $rsa_redirect_head = get_option('rsa_redirect_head'); ?>
										<option value="301"<?php if ($rsa_redirect_head == "301") echo ' selected="selected"'; ?>>301 Permanent</option>
										<option value="302"<?php if ($rsa_redirect_head == "302" || !$rsa_redirect_head) echo ' selected="selected"'; ?>>302 Undefined</option>
										<option value="307"<?php if ($rsa_redirect_head == "307") echo ' selected="selected"'; ?>>307 Temporary</option>
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
				
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="rsa_is_active,rsa_restrict_approach,rsa_allowed_ips,rsa_redirect_path,rsa_redirect_head" />
				
				<p><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
			
			</form>	
		</div>
	</div>	
<?php 
	}

function rsa_admin_menu() {
	add_options_page('Restricted Site Access Configuration', 'Restricted Access', 8, __FILE__, 'rsa_options');
}
add_action('admin_menu', 'rsa_admin_menu');
?>