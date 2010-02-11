<?php
/**
 Plugin Name: Restricted Site Access
 Plugin URI: http://www.cmurrayconsulting.com/software/wordpress-restricted-site-access/
 Description: <strong>Limit access your site</strong> to visitors who are logged in or accessing the site from a set of specific IP addresses. Send restricted visitors to the log in page, redirect them, or display a message. <strong>Powerful control over redirection</strong>, with option to send to same path and send <strong>SEO friendly redirect headers</strong>. Great solution for Extranets, publicly hosted Intranets, or parallel development sites.
 Version: 2.1
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

/**
 * rsa_activation() handles plugin activation and conversion of pre 1.1 config options
 */
function rsa_activation() 
{
	if (get_option('rsa_restrict_approach')) 
	{
		//convert textarea ips to array
		$allowed = get_option('rsa_allowed_ips');	
		if ($allowed) {
			$arrAllowed = preg_split('/\s+/', $allowed);
			if (empty($arrAllowed)) $arrAllowed = array($allowed);
		}
		
		$rsa_options = array(
			'active' => (get_option('rsa_is_active')),
			'allowed' => $arrAllowed,
			'approach' => (get_option('rsa_restrict_approach')),
			/*
			1: send to login page
			2: redirect to URL
			3: show restricted access message and exit
			*/
			'redirect_path' => (get_option('rsa_redirect_path')),
			'redirect_url' => (get_option('rsa_redirect_url')),
			'head_code' => (get_option('rsa_redirect_head')) 
		);
		update_option('rsa_options',$rsa_options);
		
		delete_option('rsa_is_active');
		delete_option('rsa_allowed_ips');
		delete_option('rsa_restrict_approach');
		delete_option('rsa_redirect_path');
		delete_option('rsa_redirect_url');
		delete_option('rsa_redirect_head');
	}
}

register_activation_hook(__FILE__,'rsa_activation');

/**
 * rsa_admin_init() initializes plugin settings
 */
function rsa_admin_init() {
	register_setting('restricted_site_access_options','rsa_options','rsa_validate'); //array of fundamental options including ID and caching info
}

add_action( 'admin_init', 'rsa_admin_init' );

/**
 * rsa_validate() handles validation of settings
 */
function rsa_validate($input) 
{
	$input['active'] = ($input['active'] == 1) ? 1 : 0;
	$input['approach'] = intval($input['approach']);
	if ($input['approach'] > 3 || $input['approach'] < 0) $input['approach'] = 0;
	$input['redirect_path'] = ($input['redirect_path'] == 1) ? 1 : 0; 
	if ($input['head_code'] != '301' && $input['head_code'] != '302' && $input['head_code'] != '307') $input['head_code'] = '302';
	$input['message'] = trim($input['message']);  
	
	return $input;
}

/**
 * rsa_plugin_actlinks() adds direct settings link to plug-in page
 */
function rsa_plugin_actlinks( $links ) 
{ 
	// Add a link to this plugin's settings page
	$plugin = plugin_basename(__FILE__);
	$settings_link = sprintf( '<a href="options-general.php?page=%s">%s</a>', $plugin, __('Settings') ); 
	array_unshift( $links, $settings_link ); 
	return $links; 
}
if(is_admin()) add_filter("plugin_action_links_".$plugin, 'rsa_plugin_actlinks' );

/**
 * restricted_site_access() is the core function that blocks a page if appropriate
 */
function restricted_site_access() 
{
	$rsa_options = get_option('rsa_options');
	
	//logged in users can stay, can stay if plug-in not active
	if (is_user_logged_in() || !$rsa_options['active']) return false;
	//if we're not on a front end page, stay put
	//if (!is_singular() && !is_archive() && !is_feed() && !is_home()) return false;
	
	// check for the allow list, if its empty block everything
	if(($list = $rsa_options['allowed']) && function_exists('inet_pton'))
	{
		$remote_ip = $_SERVER['REMOTE_ADDR'];  //save the remote ip
		if(strpos($remote_ip, '.')) $remote_ip = str_replace('::ffff:', '', $remote_ip); //handle dual-stack addresses
		$remote_ip = inet_pton($remote_ip); //parse the remote ip
		
		//var_dump($list);
		
		// iterate through the allow list
		foreach($list as $line)
		{
			list($ip, $mask) = explode('/', $line . '/128'); // get the ip and mask from the list
			
			$mask = str_repeat('f', $mask >> 2); //render the mask as bits, similar to info on the php.net man page discussion for inet_pton

			switch($mask % 4){
				case 1:
					$mask .= '8';
					break;
				case 2:
					$mask .= 'c';
					break;
				case 3:
					$mask .= 'e';
					break;
			}
			
			$mask = pack('H*', $mask);

			// check if the masked versions match
			if((inet_pton($ip) & $mask) == ($remote_ip & $mask)) return false;
		}
	}
	
	$rsa_restrict_approach = intval($rsa_options['approach']);
	switch ($rsa_restrict_approach) {
		case 1:
			$new_url = (is_home()) ? get_bloginfo("url") : get_permalink(); 
			wp_redirect(wp_login_url($new_url));
			exit;
		case 2:
			// get base url
			$rsa_redirect_url = $rsa_options['redirect_url'];
			if (!$rsa_redirect_url) return false;
			
			// if redirecting to same path get info
			if($rsa_options['redirect_path']) $rsa_redirect_url .= $_SERVER["REQUEST_URI"];
			
			$rsa_redirect_head = $rsa_options['head_code'];
			$rsa_redirect_head = (!$rsa_redirect_head) ? 302 : intval($rsa_redirect_head);
			
			wp_redirect($rsa_redirect_url, $rsa_redirect_head);
			exit;
		case 3:
			$message = (isset($rsa_options['message']) && $rsa_options['message']) ? $rsa_options['message'] : "Access to this site is restricted.";  
			wp_die($message);
	}
}
if(!is_admin()) add_action('wp','restricted_site_access');

//************************//
//** ADMIN CONTROL PANEL *//
//************************//

function rsa_options() {
?>
	<script type="text/javascript" language="javascript">
		function add_ip(ip) {
			if (!jQuery.trim(ip)) return false;
			
			jQuery('#message').remove();
			
			var ip_used = false;
			jQuery('#ip_list input').each(function(){
				if (jQuery(this).val() == ip) {
					jQuery('h2').after('<div id="message" class="error"><p><strong>IP address '+ip+' already in list.</strong></p></div>');
					scroll(0,0);
					ip_used = true;
					return false; 
				}
			});
			if (ip_used) return false;
			
			jQuery('#ip_list').append('<span><input type="text" name="rsa_options[allowed][]" value="'+ip+'" readonly="true" /><input type="button" class="button" onclick="remove_ip(this);" value="remove" /><br /></span>');
			jQuery('h2').after('<div id="message" class="updated"><p><strong>IP added to exception list.</strong></p></div>');
		}
		
		function remove_ip(btnObj) {
			if (!confirm('Are you certain you want to remove this IP?')) return false;
			jQuery(btnObj).parent().remove();
		}
		
		function change_approach(approach_choice) {
			if (approach_choice == 2) jQuery(".redirect_field").fadeIn(500);
			else jQuery(".redirect_field").fadeOut(500);
			if (approach_choice == 3) jQuery(".message_field").fadeIn(500);
			else jQuery(".message_field").fadeOut(500);
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
		<div class="icon32" id="icon-options-general"><br/></div>
		<h2>Restricted Site Access Configuration</h2>
		
		<?php	
		if (!function_exists('inet_pton')) {
			echo '<p>Version 2.0 of this plug-in requires a server running PHP 5.1 or newer in order to support IPv6 (as well as IPv4) ranges. If you are using an older version of PHP and your host cannot be upgraded, and you do not need IP range support, you can always manually <a href="http://downloads.wordpress.org/plugin/restricted-site-access.1.0.2.zip">download and install version 1.0.2</a>.</p>';
			return false;	
		}
		?>
	
		<div id="poststuff" style="margin-top: 20px;">
	
			<div class="postbox" style="width: 200px; min-width: 200px; float: right;">
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
			<?php 
				settings_fields('restricted_site_access_options');
				$rsa_options = get_option('rsa_options'); 
			?>			
				<h3 class="hndle">Restriction Options</h3>
						
				<table class="form-table" style="clear: none; width: auto;">
					<tr valign="top">
						<th scope="row"><label for="rsa_options[active]">Restrict access</label></th>
						<td>
							<input type="checkbox" name="rsa_options[active]" value="1" id="rsa_is_active"<?php if ($rsa_options['active']) echo ' checked="true"'; ?> />
							Activates the plug-in and restriction rules.
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsa_options[approach]">Handling</label></th>
						<td>
							<select name="rsa_options[approach]" id="rsa_restrict_approach" onchange="change_approach(selectedIndex);">
								<?php 
									$rsa_restrict_approach = intval($rsa_options['approach']);
									$restrict_choices = array('Select handling','Send to login page','Redirect visitor','Display message');
									foreach($restrict_choices as $key=>$value) {
										echo '<option value="'.$key.'"';
										if ($rsa_restrict_approach == $key) echo ' selected="selected"';
										echo '>'.$value."</option>\n";
									} 
								?>
							</select>
							<span class="description">Method for handling visitors who do not have access.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="newip">Allowed IPs</label></th>
						<td>
							<div id="ip_list">
							<?php
								$ips = $rsa_options['allowed'];	
								if (!empty($ips)) {			
									foreach ($ips as $key => $ip) {
										if (empty($ip)) continue;
										echo '<span><input type="text" name="rsa_options[allowed][]" value="'.$ip.'" readonly="true" /><input type="button" class="button" onclick="remove_ip(this);" value="remove" /><br /></span>';
									}
								}
							?>
							</div>
							<input type="text" name="newip" id="newip" value="" /><input class="button" type="button" onclick="add_ip(jQuery('#newip').val());" value="add" /><br />
							<input class="button" type="button" onclick="add_ip('<?php echo $_SERVER['REMOTE_ADDR']; ?>');" value="add my current IP" style="margin: 5px 0;" /><br />
							<span class="description">May enter ranges using subnet prefix or single IPs. Open help tab for details.</span>
						</td>
					</tr>
				</table>
				
				<h3 class="redirect_field"<?php if ($rsa_restrict_approach != 2) echo ' style="display: none;"'; ?>>Redirection Options</h3>
				
				<table class="form-table redirect_field" style="clear: none; width: auto;<?php if ($rsa_restrict_approach != 2) echo ' display: none;'; ?>">	
					<tr valign="top"> 
						<th scope="row"><label for="rsa_options[redirect_url]">Redirect visitor to</label></th>
						<td>
							<input type="text" name="rsa_options[redirect_url]" id="rsa_redirect_url" value="<?php echo $rsa_options['redirect_url']; ?>" class="regular-text" />
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label for="rsa_options[redirect_path]"><em>...with same path</em></label></th>
						<td>
							<input type="checkbox" name="rsa_options[redirect_path]" value="1" id="rsa_redirect_path"<?php if ($rsa_options['redirect_path']) echo ' checked="true"'; ?> />
							Redirect to same path entered at this site (help tab for more)
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row"><label for="rsa_options[head_code]">Redirect type</label></th>
						<td>
							<select name="rsa_options[head_code]" id="rsa_redirect_head">
								<?php $rsa_redirect_head = $rsa_options['head_code']; ?>
								<option value="301"<?php if ($rsa_redirect_head == "301") echo ' selected="selected"'; ?>>301 Permanent</option>
								<option value="302"<?php if ($rsa_redirect_head == "302" || !$rsa_redirect_head) echo ' selected="selected"'; ?>>302 Undefined</option>
								<option value="307"<?php if ($rsa_redirect_head == "307") echo ' selected="selected"'; ?>>307 Temporary</option>
							</select>
							<span class="description">Open help tab for more explanation.</span>
						</td>
					</tr>
				</table>
				
				<h3 class="message_field"<?php if ($rsa_restrict_approach != 3) echo ' style="display: none;"'; ?>>Blocked Access Message</h3>
				
				<table class="form-table message_field" style="clear: none; width: auto;<?php if ($rsa_restrict_approach != 3) echo ' display: none;'; ?>">	
					<tr valign="top"> 
						<th scope="row"><label for="rsa_options[message]">Message</label></th>
						<td>
							<input type="text" name="rsa_options[message]" id="rsa_message" value="<?php echo esc_html($rsa_options['message']); ?>" class="regular-text" /><br />
							<span class="description">Blank = "Access to this site is restricted."</span>
						</td>
					</tr>
				</table>
				
				<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
			</form>	
		</div>
	</div>	
<?php 
	}

function rsa_admin_menu() {
	$plugin_page = add_options_page('Restricted Site Access Configuration', 'Restricted Access', 8, __FILE__, 'rsa_options');
	add_action('admin_head-'.$plugin_page,'rsa_header');
}
add_action('admin_menu', 'rsa_admin_menu');

function rsa_header() {
	add_filter('contextual_help','rsa_context_help');
}

function rsa_context_help() 
{
	echo '
		<h5>Restricted Site Access</h5>
		<p>Restricted Site Access is a plug-in by Jake Goldman (C. Murray Consulting) that  allows you to restrict access to logged in users and a set of IP addresses with flexible restricted access behavior.</p>
		
		<h5>Restriction Options</h5>
		<p><strong>Restrict access</strong> - you can enable and disable restriction at will without deactivating the plug-in.</p>
		<p><strong>Handling</strong> - send the visitor the WordPress login screen, redirect the visitor (choosing this will offer some new options), or display a message indicating that the site is restricted.</p> 
		<p><strong>Allowed IPs</strong> - enter a single IP address (such as 192.168.1.105) or an IP range using a network prefix (such as 10.0.0.1/24). In the current version, no validation is completed on these free form fields intended to hold IP addresses or IP ranges. A future version may check for valid entries. Here\'s a <a href="http://www.csgnetwork.com/ipinfocalc.html" target="_blank">handy calculator</a> to check your prefix.</p>
		
		<h5>Redirection Options</h5>
		<p>This field set will only appear if you are using the "redirect visitor" handler.</p>
		<p><strong>Redirect visitor to</strong> - the web site address of the site you want the visitor redirected to.</p>
		<p><strong>...with same path</strong> - if you would like to redirect the visitor to the same path (URI) he or she entered this site at (the rest of the URL after the base URL), check this option. This is typically used when there are two, very similar sites at different public web addresses; for instance, a parallel development server open to the Internet but not intended for the public.</p>
		<p><strong>Redirect type</strong> - redirect type headers can provide certain visitors, particularly search engines, more information about the nature of the redirect. A 301 redirect tells search engines that the page has moved permanently to the new location. 307 indicates a temporary redirect. 302 is an undefined redirect.</p>
			
		<h5>Support</h5>
		<div class="metabox-prefs">
			<p><a href="http://www.cmurrayconsulting.com/software/wordpress-restricted-site-access/" target="_blank">Restricted Site Access support</a></p>
			<p>This plug-in was developed by <a href="http://www.cmurrayconsulting.com" target="_blank">C. Muray Consulting</a>, Web Development &amp; Strategy Experts located in Providence, Rhode Island in the United States. We develop plug-ins because we love working with WordPress, and to generate interest in our business. If you like our plug-in, and know someone who needs web development work, be in touch!</p>
		</div>
	';	
}
?>