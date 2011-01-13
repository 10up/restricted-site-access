<?php
/**
 Plugin Name: Restricted Site Access
 Plugin URI: http://www.cmurrayconsulting.com/software/wordpress-restricted-site-access/
 Description: <strong>Limit access your site</strong> to visitors who are logged in or accessing the site from a set of specific IP addresses. Send restricted visitors to the log in page, redirect them, or display a message. <strong>Powerful control over redirection</strong>, with option to send to same path and send <strong>SEO friendly redirect headers</strong>. Great solution for Extranets, publicly hosted Intranets, or parallel development sites.
 Version: 3.2.1
 Author: Jake Goldman (Oomph, Inc)
 Author URI: http://www.thinkoomph.com

    Plugin: Copyright 2011 Oomph, Inc (email : jake@thinkoomph.com)

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
 * adds inet_pton support for ranges for servers that don't support (PHP pre 5.1, Windows PHP)
 */
 
if ( !function_exists('inet_pton') ) :

function inet_pton($ip)
{
    # ipv4
    if (strpos($ip, '.') !== FALSE) {
        $ip = pack('N',ip2long($ip));
    }
    # ipv6
    elseif (strpos($ip, ':') !== FALSE) {
        $ip = explode(':', $ip);
        $res = str_pad('', (4*(8-count($ip))), '0000', STR_PAD_LEFT);
        foreach ($ip as $seg) {
            $res .= str_pad($seg, 4, '0', STR_PAD_LEFT);
        }
        $ip = pack('H'.strlen($res), $res);
    }
    return $ip;
} 

endif;

/**
 * class to compartmentalize functionality
 */
 
class restricted_site_access
{
	var $rsa_options;
	
	function restricted_site_access()
	{
		$this->rsa_options = get_option('rsa_options');
		
		add_action( 'wp', array( $this, 'restrict_access' ), 1 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
	}
	
	function restrict_access()
	{
		//logged in users can stay, can stay if plug-in not active
		if ( is_admin() || is_user_logged_in() || get_option('blog_public') != 2 ) 
			return;
		
		$rsa_options = $this->rsa_options;
		
		// check for the allow list, if its empty block everything
		if( isset($rsa_options['allowed']) && ( $list = $rsa_options['allowed'] ) )
		{
			$remote_ip = $_SERVER['REMOTE_ADDR'];  //save the remote ip
			if( strpos($remote_ip, '.') ) $remote_ip = str_replace('::ffff:', '', $remote_ip); //handle dual-stack addresses
			$remote_ip = inet_pton($remote_ip); //parse the remote ip
			
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
				if((inet_pton($ip) & $mask) == ($remote_ip & $mask)) 
					return;
			}
		}
		
		$rsa_restrict_approach = $rsa_options['approach'];
		
		do_action( 'restrict_site_access_handling', $rsa_restrict_approach ); // allow users to hook handling
		
		switch( $rsa_restrict_approach ) 
		{
			case 3:
				if ( !isset($rsa_options['message']) || empty($rsa_options['message']) ) 
					$rsa_options['message'] = "Access to this site is restricted.";
				
				wp_die( $rsa_options['message'], 'Restricted Site Access' );
				
			case 2:
				if ( $rsa_redirect_url = $rsa_options['redirect_url'] ) 
				{
					if( $rsa_options['redirect_path'] ) 
						$rsa_redirect_url .= $_SERVER["REQUEST_URI"]; 	// path
					
					$rsa_redirect_head = ( !$rsa_options['head_code'] ) ? 302 : intval( $rsa_options['head_code'] );	// code
				}
				break;
				
			default:
				$rsa_redirect_head = 302;
				$rsa_redirect_url = wp_login_url( empty($_SERVER["REQUEST_URI"]) ? get_bloginfo('url') : $_SERVER["REQUEST_URI"] );		
		}
		
		wp_redirect( $rsa_redirect_url, $rsa_redirect_head );
		exit;
	}
	
	function admin_init()
	{
		// customize privacy message
		add_filter( 'privacy_on_link_text', array( $this, 'privacy_on_link_text' ) );
		add_filter( 'privacy_on_link_title', array( $this, 'privacy_on_link_title' ) );
		
		// customize privacy page
		add_action( 'load-options-privacy.php', array( $this, 'load_options_privacy' ) );
		
		// add new choice for blog privacy
		add_action( 'blog_privacy_selector', array( $this, 'blog_privacy_selector' ) );
		
		// settings for restricted site access
		register_setting( 'privacy', 'rsa_options', array( $this, 'sanitize_options' ) ); //array of fundamental options including ID and caching info
		add_settings_section( 'restricted-site-access', __('Restricted Site Access'), array( $this, 'settings_section' ), 'privacy' );
		add_settings_field( 'approach', __('Handling'), array( $this, 'settings_field_handling' ), 'privacy', 'restricted-site-access' );
		add_settings_field( 'allowed', __('Allowed IPs'), array( $this, 'settings_field_allowed' ), 'privacy', 'restricted-site-access' );
		add_settings_field( 'message', __('Message'), array( $this, 'settings_field_message' ), 'privacy', 'restricted-site-access' );
		add_settings_field( 'redirect', __('Redirect visitor to'), array( $this, 'settings_field_redirect' ), 'privacy', 'restricted-site-access' );
		add_settings_field( 'redirect_path', __('Redirect to same path'), array( $this, 'settings_field_redirect_path' ), 'privacy', 'restricted-site-access' );
		add_settings_field( 'redirect_code', __('Redirection type'), array( $this, 'settings_field_redirect_code' ), 'privacy', 'restricted-site-access' );
		
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'plugin_action_links' ) );
	}
	
	function settings_section() { }
	
	function privacy_on_link_text( $text )
	{
		if ( get_option('blog_public') == 2 ) 
			$text = __('Site Access Restricted');
		
		return $text;
	}
	
	function privacy_on_link_title( $text )
	{
		if ( get_option('blog_public') == 2 ) 
			$text = __('Restricted Site Access plug-in is blocking public access to this site.');
		
		return $text;
	}
	
	function load_options_privacy()
	{
		wp_enqueue_script( 'restricted-site-access', plugin_dir_url( __FILE__ ) . 'restricted-site-access.js', array('jquery'), '3.2', true );
		add_filter( 'contextual_help', array( $this, 'contextual_help' ) );
	}
	
	function blog_privacy_selector() {
	?>
		<br />
		<input id="blog-restricted" type="radio" name="blog_public" value="2" <?php checked( '2', get_option('blog_public') ); ?> />
		<label for="blog-restricted"><?php _e('I would like to block all visitors who are not logged in or allowed by IP address'); ?> (<strong><?php _e('Restricted Site Access'); ?></strong>)</label>
	<?php
	}
	
	function sanitize_options( $input ) 
	{
		$new_input['approach'] = intval( $input['approach'] );
		
		if ( !in_array( $new_input['approach'], array(1,2,3) ) ) 
			$new_input['approach'] = 1;
		
		$new_input['redirect_path'] = ( isset($input['redirect_path']) && $input['redirect_path'] == 1 ) ? 1 : 0;
		$new_input['head_code'] = !in_array( $input['head_code'], array('301','302','307') ) ? $new_input['head_code'] = '302' : $input['head_code'] ;
		$new_input['message'] = trim( $input['message'] );
		$new_input['redirect_url'] = esc_url( $input['redirect_url'], array('http','https') );
		
		$new_input['allowed'] = $input['allowed'];   // probably need regex at some point
		
		return $new_input;
	}
	
	function settings_field_handling( $value ) 
	{
		if ( !isset($this->rsa_options['approach']) )
			$this->rsa_options['approach'] = '1';
	?>
		<select name="rsa_options[approach]" id="allowed">
			<option value="1" <?php selected( $this->rsa_options['approach'], '1' ); ?>><?php _e('Send to login page'); ?></option>
			<option value="2" <?php selected( $this->rsa_options['approach'], '2' ); ?>><?php _e('Redirect visitor'); ?></option>
			<option value="3" <?php selected( $this->rsa_options['approach'], '3' ); ?>><?php _e('Display message'); ?></option>
		</select>
		<span class="description"><?php _e('Method for handling visitors who do not have access.'); ?></span>
	<?php
	}
	
	function settings_field_allowed( $value ) {
	?>
		<div id="ip_list">
		<?php
			if ( !isset($this->rsa_options['allowed']) )
				$this->rsa_options['allowed'] = false;
		
			$ips = $this->rsa_options['allowed'];
				
			if ( !empty($ips) ) 
			{			
				foreach ($ips as $key => $ip) 
				{
					if ( empty($ip) ) 
						continue;
					
					echo '<span><input type="text" name="rsa_options[allowed][]" value="' . esc_attr($ip) . '" readonly="true" /><input type="button" class="button" onclick="remove_ip(this);" value="remove" /><br /></span>';
				}
			}
		?>
		</div>
		<input type="text" name="newip" id="newip" value="" /><input class="button" type="button" onclick="add_ip(jQuery('#newip').val());" value="add" />
		<span class="description"><?php _e('Enter a single IP or a range using a subnet prefix.'); ?> (<a href="#" onclick="jQuery('#contextual-help-link').click(); return false;"><?php _e( 'more help' ); ?></a>)</span>
		<br />
		<input class="button" type="button" onclick="add_ip('<?php echo $_SERVER['REMOTE_ADDR']; ?>');" value="add my current IP" style="margin: 5px 0;" /><br />
	<?php
	}
	
	function settings_field_message( $value ) 
	{
	?>
		<input type="text" name="rsa_options[message]" id="rsa_message" value="<?php echo @esc_attr( $this->rsa_options['message'] ); ?>" class="regular-text" />
		<span class="description"><?php _e('Default (blank): "Access to this site is restricted."'); ?></span>
	<?php
	}
	
	function settings_field_redirect( $value ) 
	{
	?>
		<input type="text" name="rsa_options[redirect_url]" id="redirect" value="<?php echo @esc_attr( $this->rsa_options['redirect_url'] ); ?>" class="regular-text" />
	<?php
	}
	
	function settings_field_redirect_path( $value ) 
	{
	?>
		<input type="checkbox" name="rsa_options[redirect_path]" value="1" id="redirect_path" <?php @checked( $this->rsa_options['redirect_path'] ); ?> />
		<?php _e('Send visitor to same relative URL at redirection site'); ?> (<a href="#" onclick="jQuery('#contextual-help-link').click(); return false;"><?php _e('more help'); ?></a>)
	<?php
	}
	
	function settings_field_redirect_code( $value ) 
	{
	?>
		<select name="rsa_options[head_code]" id="redirect_code">
			<option value="301" <?php @selected( $this->rsa_options['head_code'], '301' ); ?>><?php _e('301 Permanent'); ?></option>
			<option value="302" <?php @selected( $this->rsa_options['head_code'], '302' ); ?>><?php _e('302 Undefined'); ?></option>
			<option value="307" <?php @selected( $this->rsa_options['head_code'], '307' ); ?>><?php _e('307 Temporary'); ?></option>
		</select>
		<span class="description"><?php _e('Redirect HTTP status code'); ?> (<a href="#" onclick="jQuery('#contextual-help-link').click(); return false;"><?php _e('more help'); ?></a>)</span>
	<?php
	}
	
	/**
	 * special contextual help added to the privacy screen
	 */
	function contextual_help( $text ) 
	{
		return $text . '
			<h5>' . __('Restricted Site Access') . '</h5>
			<p><a href="http://www.thinkoomph.com/plugins-modules/wordpress-restricted-site-access/" target="_blank">' . __('Restricted Site Access') . '</a> ' . __('is a plug-in by') . ' <a href="http://www.jakegoldman.net" target="_blank">Jake Goldman</a> (<a href="http://www.thinkoomph.com/plugins-modules/wordpress-restricted-site-access/" target="_blank">Oomph, Inc</a>) ' . __('that  allows you to restrict access to logged in users and a set of IP addresses.') . '</p>
			
			<p><strong>' . __('Handling') . '</strong> - ' . __('send the visitor the WordPress login screen, redirect the visitor, or display a message indicating that the site is restricted.') . '</p> 
			<p><strong>' . __('Allowed IPs') . '</strong> - ' . __('enter a single IP address (i.e. 192.168.1.105) or an IP range using a network prefix (i.e. 10.0.0.1/24). In the current version, validation is not performed on the IP addresses or IP ranges, so enter your addresses carefully! Here\'s a') . ' <a href="http://www.csgnetwork.com/ipinfocalc.html" target="_blank">' . __('handy calculator') . '</a> ' . __('to check your prefix.') . '</p>
			
			<h5>' . __('Redirection Options') . '</h5>
			<p>' . __('The redirection fields are only used when "Handling" is set to "Redirect visitor".') . '</p>
			<p><strong>' . __('Redirect visitor to') . '</strong> - ' . __('the web address of the site you want the visitor redirected to.') . '</p>
			<p><strong>' . __('Redirect to same path') . '</strong> - ' . __('redirect the visitor to the same path (URI) entered at this site. Typically used when there are two, very similar sites at different public web addresses; for instance, a parallel development server accessible on the Internet but not intended for the public.') . '</p>
			<p><strong>' . __('Redirection type') . '</strong> - ' . __('redirect status codes can provide certain visitors, particularly search engines, more information about the nature of the redirect. A 301 redirect tells search engines that a page has moved permanently to the new location. 307 indicates a temporary redirect. 302 is an undefined redirect.') . '</p>
				
			<h5><a href="http://www.thinkoomph.com/plugins-modules/wordpress-restricted-site-access/" target="_blank">' . __('Restricted Site Access support') . '</a></h5>
		';	
	}
	
	/**
	 * add settings link directing user to privacy page on plug-in page
	 */
	function plugin_action_links( $links ) 
	{ 
		array_unshift( $links, '<a href="options-privacy.php">'.__('Settings').'</a>' ); 
		return $links; 
	}
	
	/**
	 * activation of plugin: upgrades old versions, immediately sets privacy
	 */
	function activation() 
	{
		$blog_public = 2; //default new blog public option
		
		// if upgrading from pre-3.0, update the blog_public option
		if ( $rsa_options = $this->rsa_options ) 
		{
			if ( isset($rsa_options['active']) && !$rsa_options['active'] ) 
				$blog_public = 1;
		}
		
		// upgrading pre 1.1
		if ( get_option('rsa_restrict_approach') ) 
		{
			//visibility
			if ( !get_option('rsa_is_active') ) 
				$blog_public = 1;
			
			//convert textarea ips to array
			$allowed = get_option('rsa_allowed_ips');	
			if ($allowed) 
			{
				$arrAllowed = preg_split('/\s+/', $allowed);
				
				if ( empty($arrAllowed) ) 
					$arrAllowed = array($allowed);
			}
			
			$rsa_options = array(
				'allowed' => $arrAllowed,
				'approach' => (get_option('rsa_restrict_approach')),
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
		
	 	update_option( 'blog_public', $blog_public );	// set blog visibility
	}
	
	/**
	 * restore privacy option to default value upon deactivating
	 */
	function deactivation() 
	{
		if ( get_option('blog_public') == 2 ) 
			update_option( 'blog_public', 1 );
	}
}

$restricted_site_access = new restricted_site_access;

/**
 * uninstall hook - remove options
 */

register_uninstall_hook( __FILE__, 'restricted_site_access_uninstall' );

function restricted_site_access_uninstall()
{
	if ( get_option('blog_public') == 2 ) 
		update_option( 'blog_public', 1 );
			
	delete_option('rsa_options');
}