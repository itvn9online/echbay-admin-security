<?php
/**
 * Plugin Name: EchBay Admin Security
 * Description: Set token key for access to admin page
 * Plugin URI: https://www.facebook.com/webgiare.org/
 * Author: Dao Quoc Dai
 * Author URI: https://www.facebook.com/ech.bay/
 * Version: 1.0.0
 * Text Domain: echbayeas
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

// Exit if accessed directly
if ( ! defined ( 'ABSPATH' ) ) {
	exit ();
}

define ( 'EAS_DF_VERSION', '1.0.0' );
// echo EAS_DF_VERSION . "\n";

define ( 'EAS_DF_DIR', dirname ( __FILE__ ) . '/' );
// echo EAS_DF_DIR . "\n";

define ( 'EAS_THIS_PLUGIN_NAME', 'EchBay Admin Security' );
// echo EAS_THIS_PLUGIN_NAME . "\n";




// global echbay plugins menu name
// check if not exist -> add new
if ( ! defined ( 'EBP_GLOBAL_PLUGINS_SLUG_NAME' ) ) {
	define ( 'EBP_GLOBAL_PLUGINS_SLUG_NAME', 'echbay-plugins-menu' );
	define ( 'EBP_GLOBAL_PLUGINS_MENU_NAME', 'Webgiare Plugins' );
	
	define ( 'EAS_ADD_TO_SUB_MENU', false );
}
// exist -> add sub-menu
else {
	define ( 'EAS_ADD_TO_SUB_MENU', true );
}









/*
* class.php
*/
// check class exist
if (! class_exists ( 'EAS_Actions_Module' )) {
	
	// my class
	class EAS_Actions_Module {
		
		/*
		* config
		*/
		var $eb_plugin_data = '2222';
		
		var $eb_plugin_media_version = EAS_DF_VERSION;
		
		var $eb_plugin_prefix_option = '_eas_token_for_admin_url';
		
		var $eb_plugin_root_dir = '';
		
		var $eb_plugin_url = '';
		
		var $eb_plugin_nonce = '';
		
		var $eb_plugin_en_queue = 'EAS-static-';
		
		var $eb_plugin_cookie_name = 'EAS_cokies_token_for_admin_url';
		
		var $web_link = '';
		
		
		/*
		* begin
		*/
		function load() {
			
			
			/*
			* Check and set config value
			*/
			// root dir
			$this->eb_plugin_root_dir = basename ( EAS_DF_DIR );
			
			// Get version by time file modife
			$this->eb_plugin_media_version = filemtime( EAS_DF_DIR . 'admin.html' );
			
			// URL to this plugin
//			$this->eb_plugin_url = plugins_url () . '/' . EAS_DF_ROOT_DIR . '/';
			$this->eb_plugin_url = plugins_url () . '/' . $this->eb_plugin_root_dir . '/';
			
			// nonce for echbay plugin
//			$this->eb_plugin_nonce = EAS_DF_ROOT_DIR . EAS_DF_VERSION;
			$this->eb_plugin_nonce = $this->eb_plugin_root_dir . EAS_DF_VERSION;
			
			
			/*
			* Load custom value
			*/
			$this->get_op ();
		}
		
		// get options
		function get_op() {
			global $wpdb;
			
			//
			$pref = $this->eb_plugin_prefix_option;
			
			//
			$sql = $wpdb->get_results ( "SELECT option_name, option_value
			FROM
				`" . $wpdb->options . "`
			WHERE
				option_name LIKE '{$pref}%'
			ORDER BY
				option_id DESC
			LIMIT 0, 1", OBJECT );
//			print_r( $sql ); exit();
			
			//
			if ( isset( $sql[0] )
//			&& $sql[0]->option_name == $this->eb_plugin_prefix_option
			&& $sql[0]->option_value != '' ) {
//				$this->eb_plugin_data = esc_textarea( $sql[0]->option_value );
				$this->eb_plugin_data = $sql[0]->option_value;
			}
//			print_r( $this->eb_plugin_data ); exit();
		}
		
		// add checked or selected to input
		function ck($v1, $v2, $e = ' checked') {
			if ($v1 == $v2) {
				return $e;
			}
			return '';
		}
		
		
		
		
		// update custom setting
		function update() {
			if ($_SERVER ['REQUEST_METHOD'] == 'POST' && isset( $_POST['_ebnonce'] )) {
				
				// check nonce
				if( ! wp_verify_nonce( $_POST['_ebnonce'], $this->eb_plugin_nonce ) ) {
					wp_die('404 not found!');
				}

				
				// print_r( $_POST );
				
				
				
				// backup old tags
				$key_bak = $this->eb_plugin_prefix_option . '_' . date( 'ha', time() );
				
				delete_option ( $key_bak );
				
				add_option( $key_bak, $this->eb_plugin_data, '', 'no' );
				
				
				
				// get and update new tags
				$v = $_POST['custom_setting'];
				
				// add prefix key to option key + hour to add
				$key = $this->eb_plugin_prefix_option;
				
				//
				delete_option ( $key );
				
				//
				$v = stripslashes ( stripslashes ( stripslashes ( trim( $v ) ) ) );
				
				//
//				$v = sanitize_text_field( $v );
				
				//
				add_option( $key, $v, '', 'no' );
//				add_option ( $key, $v );
				
				//
				die ( '<script type="text/javascript">
alert("Update done!");
</script>' );
				
			} // end if POST
		}
		
		
		
		
		function check_token_in_admin () {
			
//			print_r( $_COOKIE );
			// if token cookie not found -> exist now
			if ( ! isset( $_COOKIE[ $this->eb_plugin_cookie_name ] ) ) {
				
				// check 
				$act = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
				
				// exit if token false
				if ( $this->check_token_in_string( $act ) == false ) {
					// show 404 page (basic)
					die( file_get_contents( EAS_DF_DIR . '404.html' ) ); exit();
				}
				
			}
			
		}
		
		
		function add_js_check_token () {
			echo '<script type="text/javascript">var EAS_cokies_string_name="' . $this->eb_plugin_cookie_name . '";</script>';
			echo( file_get_contents( EAS_DF_DIR . 'eas_cookie.html' ) );
		}
		
		
		// form admin
		function admin() {
			
			// admin -> used real time version
			$this->eb_plugin_media_version = time();
			
			
			//
			$main = file_get_contents ( EAS_DF_DIR . 'admin.html', 1 );
			
			$main = $this->template ( $main, array (
				'_ebnonce' => wp_create_nonce( $this->eb_plugin_nonce ),
				
				'plugin_name' => EAS_THIS_PLUGIN_NAME,
				'plugin_version' => EAS_DF_VERSION,
				
				'custom_setting' => $this->eb_plugin_data,
			) );
			
			echo $main;
			
		}
		
		
		
		
		function check_token_in_string ( $a ) {
			if ( $a == '' ) {
				return false;
			}
			
			//
			$a = explode( '/', $a );
			foreach ( $a as $v ) {
				// check token
				if ( trim( $v ) == $this->eb_plugin_data ) {
					return true;
				}
			}
			
			return false;
		}
		
		
		
		
		// get html for theme
		function guest() {
			// not 404 page -> return now
			if ( ! is_404() ) {
				return true;
			}
			
			//
			$act = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
			
			// it false -> 404 page true
			if ( $this->check_token_in_string( $act ) == false ) {
				return true;
			}
			
			// it true -> this it token page
			
			// get url
			if ( defined('WP_SITEURL') ) {
				$this->web_link = WP_SITEURL;
			}
			else if ( defined('WP_HOME') ) {
				$this->web_link = WP_HOME;
			}
			else {
				$this->web_link = get_option ( 'siteurl' );
			}
			
			//
			$this->web_link = explode( '/', $this->web_link );
//			print_r( $this->web_link );
			
			$this->web_link[2] = $_SERVER['HTTP_HOST'];
//			print_r( $this->web_link );
			
			// ->
			$this->web_link = implode( '/', $this->web_link );
			
			//
			if ( substr( $this->web_link, -1 ) == '/' ) {
				$this->web_link = substr( $this->web_link, 0, -1 );
			}
//			echo $this->web_link; exit();
			
			// go to admin if login
			if ( is_user_logged_in() ) {
				$url = $this->web_link . '/wp-admin/';
			}
			// go to login page
			else {
				$url = $this->web_link . '/wp-login.php?redirect_to=';
				
				// redirect to
//				$this->web_link .= urlencode( $this->web_link . implode( '/', $act ) );
//				$url .= urlencode( $this->web_link . '/wp-admin/' );
				$url .= urlencode( $this->web_link . '/' . $this->eb_plugin_data . '/' );
			}
//			wp_redirect( $url, 301 );
//			header ( 'Location:' . $url, true, 301 );
			
			//
//			print_r( $act );
//			echo $this->web_link . implode( '/', $act );
			
			//
			die('<script type="text/javascript">
window.location = "' . $url . '";
</script>
<noscript>
<meta http-equiv="refresh" content="0; url=' . $url . '" />
</noscript>
</head>
<body>
</body>
</html>');
			
			// exit now
			exit();
			
		}
		
		
		
		
		// add value to template file
		function template($temp, $val = array(), $tmp = 'tmp') {
			foreach ( $val as $k => $v ) {
				$temp = str_replace ( '{' . $tmp . '.' . $k . '}', $v, $temp );
			}
			
			return $temp;
		}
	} // end my class
} // end check class exist




/*
 * Show in admin
 */
function EAS_show_setting_form_in_admin() {
	global $EAS_func;
	
	$EAS_func->update ();
	
	$EAS_func->admin ();
}

function EAS_add_menu_setting_to_admin_menu() {
	// only show menu if administrator login
	if ( ! current_user_can('manage_options') )  {
		return false;
	}
	
	// menu name
	$a = EAS_THIS_PLUGIN_NAME;
	
	// add main menu
	if ( EAS_ADD_TO_SUB_MENU == false ) {
		add_menu_page( $a, EBP_GLOBAL_PLUGINS_MENU_NAME, 'manage_options', EBP_GLOBAL_PLUGINS_SLUG_NAME, 'EAS_show_setting_form_in_admin', NULL, 99 );
	}
	
	// add sub-menu
	add_submenu_page( EBP_GLOBAL_PLUGINS_SLUG_NAME, $a, trim( str_replace( 'EchBay', '', $a ) ), 'manage_options', strtolower( str_replace( ' ', '-', $a ) ), 'EAS_show_setting_form_in_admin' );
}

function EAS_check_cookie_token_for_admin() {
	global $EAS_func;
	
	$EAS_func->check_token_in_admin ();
}

function EAS_add_js_for_check_token_in_js() {
	global $EAS_func;
	
	$EAS_func->add_js_check_token ();
}




/*
 * Show in theme
 */
function EAS_check_url_in_404_page() {
	global $EAS_func;
	
	$EAS_func->guest ();
}
// end class.php









//
$EAS_func = new EAS_Actions_Module ();

// load custom value in database
$EAS_func->load ();

// check and call function for admin
if (is_admin ()) {
	
	// call to first function in /wp-admin/admin.php file
	add_action ( 'nocache_headers', 'EAS_check_cookie_token_for_admin', 0 );
//	add_action ( 'wp_user_settings', 'EAS_check_cookie_token_for_admin', 0 );
	
	add_action ( 'admin_head', 'EAS_add_js_for_check_token_in_js', 99 );
	
	add_action ( 'admin_menu', 'EAS_add_menu_setting_to_admin_menu' );
	
}
// or guest (public in theme)
else {
	
	add_action ( 'wp_head', 'EAS_check_url_in_404_page', 0 );
	
}




