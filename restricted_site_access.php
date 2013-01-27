<?php
/**
 Plugin Name: Restricted Site Access
 Plugin URI: http://10up.com/plugins/restricted-site-access-wordpress/
 Description: <strong>Limit access your site</strong> to visitors who are logged in or accessing the site from a set of specific IP addresses. Send restricted visitors to the log in page, redirect them, or display a message or page. <strong>Powerful control over redirection</strong>, including <strong>SEO friendly redirect headers</strong>. Great solution for Extranets, publicly hosted Intranets, or parallel development sites.
 Version: 5.0.1
 Author: Jake Goldman, 10up, Oomph
 Author URI: http://10up.com
 License: GPLv2 or later
*/

class Restricted_Site_Access {
	
	private $rsa_options;
	private $basename;
	private $settings_page = 'privacy';
	private $fields = array(
		'approach'		=> array(
			'default' 	=> 1,
			'label' 	=> 'Handle restricted visitors',
			'field' 	=> 'settings_field_handling',
		),
		'message' 		=> array(
			'default' 	=> 'Access to this site is restricted.',
			'label' 	=> 'Restriction message',
			'field' 	=> 'settings_field_message',
		),
		'redirect_url' 	=> array(
			'default' 	=> '',
			'label' 	=> 'Redirect web address',
			'field' 	=> 'settings_field_redirect',
		),
		'redirect_path'	=> array(
			'default' 	=> 0,
			'label' 	=> 'Redirect to same path',
			'field' 	=> 'settings_field_redirect_path',
		),
		'head_code'		=> array(
			'default' 	=> 302,
			'label' 	=> 'Redirection status code',
			'field' 	=> 'settings_field_redirect_code',
		),
		'page' 			=> array(
			'default' 	=> 0,
			'label' 	=> 'Restricted notice page',
			'field' 	=> 'settings_field_rsa_page',
		),
		'allowed' 		=> array(
			'default' 	=> array(),
			'label'		=> 'Unrestricted IP addresses',
			'field' 	=> 'settings_field_allowed',
		),
	);

	public function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		
		add_action( 'parse_request', array( $this, 'restrict_access' ), 1 );
		add_action( 'admin_init', array( $this, 'admin_init' ), 1 );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_rsa_ip_check', array( $this, 'ajax_rsa_ip_check' ) );

		add_action( 'activate_' . $this->basename, array( $this, 'activation' ) );
		add_action( 'deactivate_' . $this->basename, array( $this, 'deactivation' ) );
	}
	
	public function init() {
		load_plugin_textdomain( 'restricted-site-access', false, dirname( $this->basename ) . '/localization/' );
	}

	/**
	 * populate the option with defaults
	 */
	private function set_option_defaults() {
		if ( ! empty( $this->rsa_options ) )
			return;

		// set default options
		$this->rsa_options = (array) get_option( 'rsa_options' );
		foreach( $this->fields as $field_name => $field_details ) {
			if ( ! isset( $this->rsa_options[$field_name] ) )
				$this->rsa_options[$field_name] = $field_details['default'];
		}
	}
	
	public function restrict_access( $wp ) {
		remove_action( 'parse_request', array( $this, 'restrict_access' ), 1 );	// only need it the first time
		
		$is_restricted = ( is_admin() || is_user_logged_in() || get_option( 'blog_public' ) != 2 || ( defined( 'WP_INSTALLING' ) && isset( $_GET['key'] ) ) ) ? false : true;
		if ( apply_filters( 'restricted_site_access_is_restricted', $is_restricted, $wp ) === false )
			return;

		$this->set_option_defaults();
		
		// check for the allow list, if its empty block everything
		if ( $list = $this->rsa_options['allowed'] ) {
			$remote_ip = $_SERVER['REMOTE_ADDR'];  //save the remote ip
			if ( strpos( $remote_ip, '.' ) )
				$remote_ip = str_replace( '::ffff:', '', $remote_ip ); //handle dual-stack addresses
			$remote_ip = inet_pton( $remote_ip ); //parse the remote ip
			
			// iterate through the allow list
			foreach( $list as $line ) {
				list( $ip, $mask ) = explode( '/', $line . '/128' ); // get the ip and mask from the list
				
				$mask = str_repeat( 'f', $mask >> 2 ); //render the mask as bits, similar to info on the php.net man page discussion for inet_pton
	
				switch( $mask % 4 ) {
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
				
				$mask = pack( 'H*', $mask );
	
				// check if the masked versions match
				if ( ( inet_pton( $ip ) & $mask ) == ( $remote_ip & $mask ) )
					return;
			}
		}
		
		$rsa_restrict_approach = apply_filters( 'restricted_site_access_approach', $this->rsa_options['approach'] );
		do_action( 'restrict_site_access_handling', $rsa_restrict_approach ); // allow users to hook handling
		
		switch( $rsa_restrict_approach ) {
			case 4:
				if ( $this->rsa_options['page'] && ( $page_id = get_post_field( 'ID', $this->rsa_options['page'] ) ) ) {
					unset( $wp->query_vars );
					$wp->query_vars['page_id'] = $page_id;
					return;
				}
			
			case 3:
				$message = __( $this->rsa_options['message'], 'restricted-site-access' );
				$message .= "\n<!-- access protected by Restricted Site Access plug-in | http://10up.com/plugins/restricted-site-access-wordpress/ -->";
				$message = apply_filters( 'restricted_site_access_message', $message );
				
				wp_die( $message, get_bloginfo( 'name' ) . ' - Site Access Restricted' );
				
			case 2:
				if ( $this->rsa_options['redirect_url'] ) {
					if( ! empty( $this->rsa_options['redirect_path'] ) )
						$this->rsa_options['redirect_url'] = untrailingslashit( $this->rsa_options['redirect_url'] ) . $_SERVER["REQUEST_URI"]; 	// path
					break;
				}
				
			default:
				$this->rsa_options['redirect_path'] = 302;
				$current_path = empty( $_SERVER["REQUEST_URI"] ) ? home_url() : $_SERVER["REQUEST_URI"];
				$this->rsa_options['redirect_url'] = wp_login_url( $current_path );
		}

		$redirect_url = apply_filters( 'restricted_site_access_redirect_url', $this->rsa_options['redirect_url'] );
		$redirect_code = apply_filters( 'restricted_site_access_head', $this->rsa_options['redirect_path'] );
		wp_redirect( $redirect_url, $redirect_code );
		die;
	}
	
	public function admin_init() {
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '3.5', '>=' ) )
			$this->settings_page = 'reading';

		// customize privacy message
		add_filter( 'privacy_on_link_text', array( $this, 'privacy_on_link_text' ) );
		add_filter( 'privacy_on_link_title', array( $this, 'privacy_on_link_title' ) );
		
		// customize privacy page
		add_action( 'load-options-' . $this->settings_page . '.php', array( $this, 'load_options_page' ) );
		
		// add new choice for blog privacy
		add_action( 'blog_privacy_selector', array( $this, 'blog_privacy_selector' ) );
		
		// settings for restricted site access
		register_setting( $this->settings_page, 'rsa_options', array( $this, 'sanitize_options' ) ); // array of fundamental options including ID and caching info
		add_settings_section( 'restricted-site-access', '', '__return_false', $this->settings_page );
		foreach ( $this->fields as $field_name => $field_data ) {
			add_settings_field( $field_name, __( $field_data['label'], 'restricted-site-access' ), array( $this, $field_data['field'] ), $this->settings_page, 'restricted-site-access' );
		}
		
		add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'plugin_action_links' ) );
	}
	
	public function privacy_on_link_text( $text ) {
		if ( get_option( 'blog_public' ) == 2 )
			$text = __( 'Public access to this site has been restricted.', 'restricted-site-access' );
		
		return $text;
	}
	
	public function privacy_on_link_title( $text ) {
		if ( get_option( 'blog_public' ) == 2 )
			$text = __( 'Restricted Site Access plug-in is blocking public access to this site.', 'restricted-site-access' );
		
		return $text;
	}
	
	public function load_options_page() {
		$dev = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.dev' : '';
		wp_enqueue_script( 'restricted-site-access', plugin_dir_url( __FILE__ ) . 'restricted-site-access'.$dev.'.js', array('jquery-effects-shake'), '5.0', true );
		wp_localize_script( 'restricted-site-access', 'restricted_site_access_l10n', array(
			'Remove' => __('Remove','restricted-site-access'),
			'wp_version' => floatval( get_bloginfo( 'version' ) ),
		) );

		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ) );

		$this->set_option_defaults();
	}
	
	public function admin_notice() {
		if ( empty( $this->rsa_options['approach'] ) )
			return;
		
		if ( $this->rsa_options['approach'] == 4 && empty( $this->rsa_options['page'] ) )
			$message = '<strong>' . __( 'Please select the page you want to show restricted visitors. If no page is selected, WordPress will simply show a general restriction message.', 'restricted-site-access' ) . '</strong>';
		elseif ( $this->rsa_options['approach'] == 2 && empty( $this->rsa_options['redirect_url'] ) )
			$message = '<strong>' . __( 'Please enter the web address you would like to redirect restriced visitors to. If no address is entered, visitors will be redirected to the login screen.', 'restricted-site-access' ) . '</strong>';
		
		if ( ! empty( $message ) )
			echo '<div class="updated settings-error"><p>' . $message . '</p></div>';
	}

	public function admin_head() {
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id'      => 'restricted-site-access',
			'title'   => __('Restricted Site Acccess'),
			'content' => '
				<p><strong>' . __('Handle restricted visitors','restricted-site-access') . '</strong> - ' . __('choose the method for handling visitors to your site that are restricted.','restricted-site-access') . '</p>
				<p><strong>' . __('Allowed IP addresses','restricted-site-access') . '</strong> - ' . __('enter a single IP address (for example, 192.168.1.105) or an IP range using a network prefix (for example, 10.0.0.1/24). Enter your addresses carefully! Here\'s a','restricted-site-access') . ' <a href="http://www.csgnetwork.com/ipinfocalc.html" target="_blank">' . __('handy calculator','restricted-site-access') . '</a> ' . __('to check your prefix.','restricted-site-access') . '</p>
				<p>' . __('The redirection fields are only used when "Handle restricted visitors" is set to "Redirect them to a specified web address".','restricted-site-access') . '</p>
				<p><strong>' . __('Redirect web address','restricted-site-access') . '</strong> - ' . __('the web address of the site you want the visitor redirected to.','restricted-site-access') . '</p>
				<p><strong>' . __('Redirect to same path','restricted-site-access') . '</strong> - ' . __('redirect the visitor to the same path (URI) entered at this site. Typically used when there are two, very similar sites at different public web addresses; for instance, a parallel development server accessible on the Internet but not intended for the public.','restricted-site-access') . '</p>
				<p><strong>' . __('Redirection status code','restricted-site-access') . '</strong> - ' . __('redirect status codes can provide certain visitors, particularly search engines, more information about the nature of the redirect. A 301 redirect tells search engines that a page has moved permanently to the new location. 307 indicates a temporary redirect. 302 is an undefined redirect.','restricted-site-access') . '</p>
			',
		) );
	}
	
	public function blog_privacy_selector() {
	?>
		<input id="blog-restricted" type="radio" name="blog_public" value="2" <?php checked( get_option( 'blog_public' ), 2 ); ?> />
		<label for="blog-restricted"><?php _e( 'Restrict site access to visitors who are logged in or allowed by IP address', 'restricted-site-access' ); ?></label>
	<?php
	}
	
	public function sanitize_options( $input ) {
		$new_input['approach'] = (int) $input['approach'];
		if ( $new_input['approach'] < 1 || $new_input['approach'] > 4 ) 
			$new_input['approach'] = $this->fields['approach']['default'];

		global $allowedtags;
		$new_input['message'] = wp_kses( $input['message'], $allowedtags );

		$new_input['redirect_path'] = empty( $input['redirect_path'] ) ? 0 : 1;
		$new_input['head_code'] = in_array( (int) $input['head_code'], array( 301, 302, 307 ) ) ? (int) $input['head_code'] : $this->fields['head_code']['default'];
		$new_input['redirect_url'] = empty( $input['redirect_url'] ) ? '' : esc_url_raw( $input['redirect_url'], array('http','https') );
		$new_input['page'] = empty( $input['page'] ) ? 0 : (int) $input['page'];

		$new_input['allowed'] = array();
		if ( !empty( $input['allowed'] ) && is_array( $input['allowed'] ) ) {
			foreach( $input['allowed'] as $ip_address ) {
				if ( $this->is_ip( $ip_address ) )
					$new_input['allowed'][] = $ip_address;
			}
		}
		
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
				foreach ( (array) $this->rsa_options['allowed'] as $ip) {
					if ( empty( $ip ) )
						continue;

					echo '<div><input type="text" name="rsa_options[allowed][]" value="' . esc_attr( $ip ) . '" readonly="true" /> <a href="#remove" onclick="remove_ip(this);">' . __( 'Remove' ) . '</a></div>';
				}
			?>
			</div>
			<div>
				<input type="text" name="newip" id="newip" /> <input class="button" type="button" id="addip" onclick="add_ip(jQuery('#newip').val());" value="<?php _e( 'Add' ); ?>" />
				<label for="newip"><span class="description"><?php _e('Enter a single IP address or a range using a subnet prefix','restricted-site-access'); ?></span></label>
            </div>
			<?php if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) { ?><input class="button" type="button" onclick="add_ip('<?php echo esc_attr( $_SERVER['REMOTE_ADDR'] ); ?>');" value="<?php _e( 'Add My Current IP Address', 'restricted-site-access' ); ?>" style="margin-top: 5px;" /><br /><?php } ?>
		</div>
		<p class="hide-if-js"><strong><?php _e('To manage IP addresses, you must use a JavaScript enabled browser.','restricted-site-access'); ?></strong></p>
	<?php
	}
	
	public function settings_field_message( $args ) {
		if ( empty($this->rsa_options['message']) )
			$this->rsa_options['message'] = __('Access to this site is restricted.','restricted-site-access');

		wp_editor( $this->rsa_options['message'], 'rsa_message', array(
			'media_buttons' => false,
			'textarea_name' => 'rsa_options[message]',
			'textarea_rows' => 4,
			'tinymce'		=> false,
		) );
	}
	
	public function settings_field_redirect( $args ) {
	?>
		<input type="text" name="rsa_options[redirect_url]" id="redirect" class="rsa_redirect_field regular-text" value="<?php echo @esc_attr( $this->rsa_options['redirect_url'] ); ?>" />
	<?php
	}
	
	public function settings_field_redirect_path( $args ) {
	?>
		<input type="checkbox" name="rsa_options[redirect_path]" value="1" id="redirect_path" class="rsa_redirect_field" <?php @checked( $this->rsa_options['redirect_path'] ); ?> />
		<?php _e( 'Send restricted visitor to same path (relative URL) at the new web address', 'restricted-site-access' ); ?>
	<?php
	}
	
	public function settings_field_redirect_code( $args ) {
		if ( empty($this->rsa_options['head_code']) )
			$this->rsa_options['head_code'] = 302;
	?>
		<select name="rsa_options[head_code]" id="redirect_code" class="rsa_redirect_field">
			<option value="301" <?php selected( $this->rsa_options['head_code'], 301 ); ?>><?php _e( '301 Permanent', 'restricted-site-access' ); ?></option>
			<option value="302" <?php selected( $this->rsa_options['head_code'], 302 ); ?>><?php _e( '302 Undefined', 'restricted-site-access' ); ?></option>
			<option value="307" <?php selected( $this->rsa_options['head_code'], 307 ); ?>><?php _e( '307 Temporary', 'restricted-site-access' ); ?></option>
		</select>
		<span class="description"><?php _e( 'HTTP status code sent to browser', 'restricted-site-access' ); ?></span>
	<?php
	}
	
	public function settings_field_rsa_page( $args ) {
		wp_dropdown_pages(array( 
			'selected' => $this->rsa_options['page'],
			'show_option_none' => 'Select a page',
			'name' => 'rsa_options[page]',
			'id' => 'rsa_page'
		));
	}

	/**
	 * validate IP address entry on demand (AJAX)
	 */
	public function ajax_rsa_ip_check() {
		if ( empty( $_POST['ip_address'] ) )
			die('1');

		if ( $this->is_ip( stripslashes( $_POST['ip_address'] ) ) )
			die;
		else
			die('1');
	}

	/**
	 * is it a valid IP address? v4/v6 with subnet range
	 */
	public function is_ip( $ip_address ) {
		// very basic validation of ranges
		if ( strpos( $ip_address, '/' ) ) {
			$ip_parts = explode( '/', $ip_address );
			if ( empty( $ip_parts[1] ) || !is_numeric( $ip_parts[1] ) || strlen( $ip_parts[1] ) > 3 )
				return false;
			$ip_address = $ip_parts[0];
		}

		// confirm IP part is a valid IPv6 or IPv4 IP
		if ( empty( $ip_address ) || !inet_pton( stripslashes( $ip_address ) ) )
			return false;

		return true;
	}

	/**
	 * add settings link directing user to privacy page on plug-in page
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="options-' . $this->settings_page . '.php">' . __('Settings') . '</a>';
		return $links; 
	}
	
	/**
	 * activation of plugin: upgrades old versions, immediately sets privacy
	 */
	public function activation() {
		update_option( 'blog_public', 2 );
	}
	
	/**
	 * restore privacy option to default value upon deactivating
	 */
	public function deactivation() {
		if ( get_option( 'blog_public' ) == 2 )
			update_option( 'blog_public', 1 );
	}
}

$restricted_site_access = new Restricted_Site_Access;

/**
 * uninstall hook - remove options
 */

register_uninstall_hook( __FILE__, 'restricted_site_access_uninstall' );

function restricted_site_access_uninstall() {
	if ( get_option('blog_public') == 2 ) 
		update_option( 'blog_public', 1 );
			
	delete_option('rsa_options');
}

/**
 * inet_pton is not included in PHP < 5.3 on Windows (WP requires PHP 5.2)
 */

if ( ! function_exists( 'inet_pton' ) ) :

	function inet_pton($ip) {
		if (strpos($ip, '.') !== false) {
			// ipv4
			$ip = pack('N',ip2long($ip));
		} elseif (strpos($ip, ':') !== false) {
			// ipv6
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