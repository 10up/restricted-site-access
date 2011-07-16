<?php
/**
 Plugin Name: Restricted Site Access
 Plugin URI: http://www.get10up.com/plugins/restricted-site-access-wordpress/
 Description: <strong>Limit access your site</strong> to visitors who are logged in or accessing the site from a set of specific IP addresses. Send restricted visitors to the log in page, redirect them, or display a message or page. <strong>Powerful control over redirection</strong>, including <strong>SEO friendly redirect headers</strong>. Great solution for Extranets, publicly hosted Intranets, or parallel development sites.
 Version: 4.0
 Author: Jake Goldman (10up)
 Author URI: http://www.get10up.com

    Plugin: Copyright 2011 10up (email : jake@get10up.com)

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

class restricted_site_access {
	
	private $rsa_options;
	
	public function __construct() {
		$this->rsa_options = get_option('rsa_options');
		
		add_action( 'parse_request', array( $this, 'restrict_access' ), 1 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'init', array( $this, 'init' ) );
		
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );	
	}
	
	function init() {
		load_plugin_textdomain( 'restricted-site-access', false, dirname( plugin_basename( __FILE__ ) ) . '/localization/' );
	}
	
	public function restrict_access( $wp ) {
		remove_action( 'parse_request', array( $this, 'restrict_access' ), 1 );	// only need it the first time
		
		$is_restricted = ( is_admin() || is_user_logged_in() || get_option('blog_public') != 2 ) ? false : true;
		
		if ( apply_filters( 'restricted_site_access_is_restricted', $is_restricted ) === false )
			return;
		
		$rsa_options = $this->rsa_options;
		
		// check for the allow list, if its empty block everything
		if( isset($rsa_options['allowed']) && ( $list = $rsa_options['allowed'] ) ) {
			
			$remote_ip = $_SERVER['REMOTE_ADDR'];  //save the remote ip
			if( strpos($remote_ip, '.') ) $remote_ip = str_replace('::ffff:', '', $remote_ip); //handle dual-stack addresses
			$remote_ip = inet_pton($remote_ip); //parse the remote ip
			
			// iterate through the allow list
			foreach($list as $line) {
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
		
		$rsa_restrict_approach = apply_filters( 'restricted_site_access_approach', $rsa_options['approach'] );
		
		do_action( 'restrict_site_access_handling', $rsa_restrict_approach ); // allow users to hook handling
		
		switch( $rsa_restrict_approach ) {
			case 4:
				if ( ! empty( $rsa_options['page'] ) && ( $page = get_page( $rsa_options['page'] ) ) ) {					
					unset( $wp->query_vars );
					$wp->query_vars['page_id'] = $page->ID;
					return;
				}
			
			case 3:
				$message = empty($rsa_options['message']) ? __( "Access to this site is restricted.", 'restricted-site-access' ) : $rsa_options['message'];
				$message .= "\n<!-- access protected by Restricted Site Access plug-in | http://www.get10up.com/plugins/restricted-site-access-wordpress/ -->";
				$message = apply_filters( 'restricted_site_access_message', $message );
				
				wp_die( $message, get_bloginfo('name') . ' - Site Access Restricted' );
				
			case 2:
				if ( ! empty($rsa_options['redirect_url']) ) {
					if( ! empty($rsa_options['redirect_path']) ) 
						$rsa_options['redirect_url'] .= $_SERVER["REQUEST_URI"]; 	// path
					
					$rsa_redirect_url = $rsa_options['redirect_url'];					
					$rsa_redirect_head = empty($rsa_options['head_code']) ? 302 : (int) $rsa_options['head_code'];	// code
					
					break;
				}
				
			default:
				$rsa_redirect_head = 302;
				$rsa_redirect_url = wp_login_url( empty($_SERVER["REQUEST_URI"]) ? home_url() : $_SERVER["REQUEST_URI"] );		
		}
		
		wp_redirect( apply_filters( 'restricted_site_access_redirect_url', $rsa_redirect_url ), apply_filters( 'restricted_site_access_head', $rsa_redirect_head ) );
		exit;
	}
	
	public function admin_init() {
		// customize privacy message
		add_filter( 'privacy_on_link_text', array( $this, 'privacy_on_link_text' ) );
		add_filter( 'privacy_on_link_title', array( $this, 'privacy_on_link_title' ) );
		
		// customize privacy page
		add_action( 'load-options-privacy.php', array( $this, 'load_options_privacy' ) );
		
		// add new choice for blog privacy
		add_action( 'blog_privacy_selector', array( $this, 'blog_privacy_selector' ) );
		
		// settings for restricted site access
		register_setting( 'privacy', 'rsa_options', array( $this, 'sanitize_options' ) ); //array of fundamental options including ID and caching info
		add_settings_section( 'restricted-site-access', '', '__return_false', 'privacy' );
		add_settings_field( 'approach', __('Handle restricted visitors', 'restricted-site-access'), array( $this, 'settings_field_handling' ), 'privacy', 'restricted-site-access' );
		add_settings_field( 'message', __('Restriction message', 'restricted-site-access'), array( $this, 'settings_field_message' ), 'privacy', 'restricted-site-access' );
		add_settings_field( 'redirect', __('Redirect web address', 'restricted-site-access'), array( $this, 'settings_field_redirect' ), 'privacy', 'restricted-site-access' );
		add_settings_field( 'redirect_path', __('Redirect to same path', 'restricted-site-access'), array( $this, 'settings_field_redirect_path' ), 'privacy', 'restricted-site-access' );
		add_settings_field( 'redirect_code', __('Redirection status code', 'restricted-site-access'), array( $this, 'settings_field_redirect_code' ), 'privacy', 'restricted-site-access' );
		add_settings_field( 'rsa_page', __('Restricted notice page', 'restricted-site-access'), array( $this, 'settings_field_rsa_page' ), 'privacy', 'restricted-site-access' );
		add_settings_field( 'allowed', __('Unrestricted IP addresses', 'restricted-site-access'), array( $this, 'settings_field_allowed' ), 'privacy', 'restricted-site-access' );
		
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'plugin_action_links' ) );
	}
	
	public function privacy_on_link_text( $text ) {
		if ( get_option('blog_public') == 2 ) 
			$text = __('Public access to this site has been restricted.', 'restricted-site-access');
		
		return $text;
	}
	
	public function privacy_on_link_title( $text ) {
		if ( get_option('blog_public') == 2 ) 
			$text = __('Restricted Site Access plug-in is blocking public access to this site.', 'restricted-site-access');
		
		return $text;
	}
	
	public function load_options_privacy() {
		wp_enqueue_script( 'restricted-site-access', plugin_dir_url( __FILE__ ) . 'restricted-site-access.js', array('jquery'), '3.3', true );
		add_filter( 'contextual_help', array( $this, 'contextual_help' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		
		$js_trans = array(
			'SaveChanges' => __('Click "Save Changes" to save this IP address.','restricted-site-access'),
			'Remove' => __('remove','restricted-site-access'),
			'ConfirmRemove' => __('confirm removal','restricted-site-access'),
			'Cancel' => __('cancel','restricted-site-access')
		);
		wp_localize_script( 'restricted-site-access', 'restricted_site_access_l10n', $js_trans );
	}
	
	function admin_notice() {
		if ( empty( $this->rsa_options['approach'] ) )
			return;
		
		if ( $this->rsa_options['approach'] == 4 && empty( $this->rsa_options['page'] ) )
			$message = '<strong>' . __( 'Please select the page you want to show restricted visitors. If no page is selected, WordPress will simply show a general restriction message.', 'restricted-site-access' ) . '</strong>';
		elseif ( $this->rsa_options['approach'] == 2 && empty( $this->rsa_options['redirect_url'] ) )
			$message = '<strong>' . __( 'Please enter the web address you would like to redirect restriced visitors to. If no address is entered, visitors will be redirected to the login screen.', 'restricted-site-access' ) . '</strong>';
		
		if ( ! empty( $message ) )
			echo '<div class="updated settings-error"><p>' . $message . '</p></div>';
	}
	
	public function blog_privacy_selector() {
	?>
		<br />
		<input id="blog-restricted" type="radio" name="blog_public" value="2" <?php checked( '2', get_option('blog_public') ); ?> />
		<label for="blog-restricted"><?php _e('Restrict site access to visitors who are logged in or allowed by IP address', 'restricted-site-access'); ?></label>
	<?php
	}
	
	public function sanitize_options( $input ) {
		$new_input['approach'] = (int) $input['approach'];
		if ( $new_input['approach'] < 1 || $new_input['approach'] > 4 ) 
			$new_input['approach'] = 1;
		
		$new_input['redirect_path'] = empty( $input['redirect_path'] ) ? 0 : 1;
		$new_input['head_code'] = in_array( (int) $input['head_code'], array(301,302,307) ) ? (int) $input['head_code'] : 302;
		$new_input['message'] = trim( $input['message'] );
		$new_input['redirect_url'] = empty( $input['redirect_url'] ) ? '' : esc_url( $input['redirect_url'], array('http','https') );
		$new_input['page'] = empty( $input['page'] ) ? '' : (int) $input['page'];
		
		$new_input['allowed'] = $input['allowed'];   // probably need regex at some point
		
		return $new_input;
	}
	
	public function settings_field_handling( $args ) {
		if ( !isset($this->rsa_options['approach']) )
			$this->rsa_options['approach'] = 1;
	?>
		<fieldset>
			<input id="rsa-send-to-login" name="rsa_options[approach]" type="radio" value="1" <?php checked( $this->rsa_options['approach'], 1 ); ?> />
			<label for="rsa-send-to-login"><?php _e('Send them to the WordPress login screen','restricted-site-access'); ?></label>
			<br />
			<input id="rsa-redirect-visitor" name="rsa_options[approach]" type="radio" value="2" <?php checked( $this->rsa_options['approach'], 2 ); ?> />
			<label for="rsa-redirect-visitor"><?php _e('Redirect them to a specified web address','restricted-site-access'); ?></label>
			<br />
			<input id="rsa-display-message" name="rsa_options[approach]" type="radio" value="3" <?php checked( $this->rsa_options['approach'], 3 ); ?> />
			<label for="rsa-display-message"><?php _e('Show them a simple message','restricted-site-access'); ?></label>
			<br />
			<input id="rsa-unblocked-page" name="rsa_options[approach]" type="radio" value="4" <?php checked( $this->rsa_options['approach'], 4 ); ?> />
			<label for="rsa-unblocked-page"><?php _e('Show them a specific WordPress page I\'ve created','restricted-site-access'); ?></label>
		</fieldset>
	<?php
	}
	
	public function settings_field_allowed( $args ) {
	?>
		<div class="hide-if-no-js">
		<div id="ip_list">
		<?php
			$ips = empty($this->rsa_options['allowed']) ? array() : $this->rsa_options['allowed'];
				
			foreach ($ips as $key => $ip) {
				if ( empty($ip) ) 
					continue;
					
				echo '<span><input type="text" name="rsa_options[allowed][]" value="' . esc_attr($ip) . '" readonly="true" /><input type="button" class="button" onclick="remove_ip(this);" value="' . __('remove','restricted-site-access') . '" /><br /></span>';
			}
		?>
		</div>
		<input type="text" name="newip" id="newip" value="" /><input class="button" type="button" onclick="add_ip(jQuery('#newip').val());" value="<?php _e('add','restricted-site-access'); ?>" />
		<span class="description"><?php _e('Enter a single IP address or a range using a subnet prefix','restricted-site-access'); ?> (<a href="#" onclick="jQuery('#contextual-help-link').click(); return false;"><?php _e('help'); ?></a>)</span>
		<br />
		<?php if ( ! empty($_SERVER['REMOTE_ADDR']) ) { ?>
		<input class="button" type="button" onclick="add_ip('<?php echo $_SERVER['REMOTE_ADDR']; ?>');" value="<?php _e('add my current IP address','restricted-site-access'); ?>" style="margin: 5px 0;" /><br />
		<?php } ?>
		</div>
		<p class="hide-if-js"><strong><?php _e('To manage IP addresses, you must use a JavaScript enabled browser.','restricted-site-access'); ?></strong></p>
	<?php
	}
	
	public function settings_field_message( $args ) {
		if ( empty($this->rsa_options['message']) )
			$this->rsa_options['message'] = __('Access to this site is restricted.','restricted-site-access');
	?>
		<input type="text" name="rsa_options[message]" id="rsa_message" value="<?php echo esc_attr( $this->rsa_options['message'] ); ?>" class="regular-text" />
	<?php
	}
	
	public function settings_field_redirect( $args ) {
	?>
		<input type="text" name="rsa_options[redirect_url]" id="redirect" class="rsa_redirect_field regular-text" value="<?php echo @esc_attr( $this->rsa_options['redirect_url'] ); ?>" />
	<?php
	}
	
	public function settings_field_redirect_path( $args ) {
	?>
		<input type="checkbox" name="rsa_options[redirect_path]" value="1" id="redirect_path" class="rsa_redirect_field" <?php @checked( $this->rsa_options['redirect_path'] ); ?> />
		<?php _e('Send restricted visitor to same path (relative URL) at the new web address','restricted-site-access'); ?> (<a href="#" onclick="jQuery('#contextual-help-link').click(); return false;"><?php _e('help'); ?></a>)
	<?php
	}
	
	public function settings_field_redirect_code( $args ) {
		if ( empty($this->rsa_options['head_code']) )
			$this->rsa_options['head_code'] = 302;
	?>
		<select name="rsa_options[head_code]" id="redirect_code" class="rsa_redirect_field">
			<option value="301" <?php selected( $this->rsa_options['head_code'], 301 ); ?>><?php _e('301 Permanent','restricted-site-access'); ?></option>
			<option value="302" <?php selected( $this->rsa_options['head_code'], 302 ); ?>><?php _e('302 Undefined','restricted-site-access'); ?></option>
			<option value="307" <?php selected( $this->rsa_options['head_code'], 307 ); ?>><?php _e('307 Temporary','restricted-site-access'); ?></option>
		</select>
		<span class="description"><?php _e('HTTP status code sent to browser','restricted-site-access'); ?> (<a href="#" onclick="jQuery('#contextual-help-link').click(); return false;"><?php _e('help'); ?></a>)</span>
	<?php
	}
	
	public function settings_field_rsa_page( $args ) {
		wp_dropdown_pages(array( 
			'selected' => @$this->rsa_options['page'],
			'show_option_none' => 'Select a page',
			'name' => 'rsa_options[page]',
			'id' => 'rsa_page'
		));
	}
	
	/**
	 * special contextual help added to the privacy screen
	 */
	public function contextual_help( $text ) 
	{
		return $text . '
			<h5>Restricted Site Access</h5>
			<p><a href="http://www.get10up.com/plugins/restricted-site-access-wordpress/" target="_blank">Restricted Site Access</a> ' . __('is a plug-in by','restricted-site-access') . ' <a href="http://www.get10up.com" target="_blank">Jake Goldman</a> (<a href="http://www.get10up.com/plugins/restricted-site-access-wordpress/" target="_blank">10up</a>) ' . __('that  allows you to restrict access to logged in users and a set of IP addresses.','restricted-site-access') . '</p>
			
			<p><strong>' . __('Handle restricted visitors','restricted-site-access') . '</strong> - ' . __('choose the method for handling visitors to your site that are restricted.','restricted-site-access') . '</p> 
			<p><strong>' . __('Allowed IP addresses','restricted-site-access') . '</strong> - ' . __('enter a single IP address (for example, 192.168.1.105) or an IP range using a network prefix (for example, 10.0.0.1/24). Enter your addresses carefully! Here\'s a','restricted-site-access') . ' <a href="http://www.csgnetwork.com/ipinfocalc.html" target="_blank">' . __('handy calculator','restricted-site-access') . '</a> ' . __('to check your prefix.','restricted-site-access') . '</p>
			
			<h5>' . __('Redirection Options','restricted-site-access') . '</h5>
			<p>' . __('The redirection fields are only used when "Handle restricted visitors" is set to "Redirect them to a specified web address".','restricted-site-access') . '</p>
			<p><strong>' . __('Redirect web address','restricted-site-access') . '</strong> - ' . __('the web address of the site you want the visitor redirected to.','restricted-site-access') . '</p>
			<p><strong>' . __('Redirect to same path','restricted-site-access') . '</strong> - ' . __('redirect the visitor to the same path (URI) entered at this site. Typically used when there are two, very similar sites at different public web addresses; for instance, a parallel development server accessible on the Internet but not intended for the public.','restricted-site-access') . '</p>
			<p><strong>' . __('Redirection status code','restricted-site-access') . '</strong> - ' . __('redirect status codes can provide certain visitors, particularly search engines, more information about the nature of the redirect. A 301 redirect tells search engines that a page has moved permanently to the new location. 307 indicates a temporary redirect. 302 is an undefined redirect.','restricted-site-access') . '</p>
		';	
	}
	
	/**
	 * add settings link directing user to privacy page on plug-in page
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="options-privacy.php">'.__('Settings').'</a>'; 
		return $links; 
	}
	
	/**
	 * activation of plugin: upgrades old versions, immediately sets privacy
	 */
	public function activation() {
		// if upgrading from pre-3.0, update the blog_public option, otherwise just set to 2
		$blog_public = ( isset($this->rsa_options['active']) && ! $this->rsa_options['active'] ) ? 1 : 2;
	 	update_option( 'blog_public', $blog_public );	// set blog visibility
	}
	
	/**
	 * restore privacy option to default value upon deactivating
	 */
	public function deactivation() {
		if ( get_option('blog_public') == 2 ) 
			update_option( 'blog_public', 1 );
	}
}

$restricted_site_access = new restricted_site_access;

/**
 * uninstall hook - remove options
 */

register_uninstall_hook( __FILE__, 'restricted_site_access_uninstall' );

function restricted_site_access_uninstall() {
	if ( get_option('blog_public') == 2 ) 
		update_option( 'blog_public', 1 );
			
	delete_option('rsa_options');
}