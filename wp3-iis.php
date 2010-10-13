<?php
/*
Plugin Name: WP3 IIS
Plugin URI: http://www.dennisonwolfe.com/
Description: The  WP3 IIS plugin adds sub-domains to Microsoft Internet Infaormation Server.
Version: 1.0.1
Author: Dennison+Wolfe Internet Group
Author URI: http://www.dennisonwolfe.com/
*/

/*  Copyright 2009  Dennison+Wolfe Internet Group  (email : tyler@dennisonwolfe.com)

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

define('PENDING_HEADERS_TABLE','wp3iis_pending_headers'); 

if ( is_admin() ) {
	//plugin activation
	add_action('activate_wp3-iis/wp3-iis.php', 'wp3iis_init');
        //register_activation_hook( __FILE__, 'wp3iis_init' );
	//settings menu
	add_action('admin_menu', 'wp3iis_tools_menu');
	//load css
	add_action('admin_print_styles', 'wp3iis_load_stylesheets' );
	//load js
	add_action('admin_print_scripts', 'wp3iis_load_scripts' );
	
}
else{
	//load css
	add_action('wp_head', 'wp3iis_load_stylesheets');
	
}

/* Load js files*/
function wp3iis_load_scripts() {
    wp_enqueue_script('wp3iis', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/wp3-iis.js', array('jquery-ui-tabs'), '1.0');
	
}

/* Load css files*/
/*function wp3iis_load_admin_stylesheets() {
    $style_file = plugins_url('wp3-iis/wp3-iis.css');
    wp_enqueue_style( 'wp3iis-css', $style_file, false, '1.0.0', 'screen' );
}
*/
function wp3iis_load_stylesheets() {
	$style_file = plugins_url('wp3-iis/wp3-iis.css');
	echo '<link rel="stylesheet" type="text/css" href="' . $style_file . '" />' . "\r\n";

}

/* Configuration Screen*/

function wp3iis_tools_menu() {
	add_submenu_page( 'ms-admin.php', 'WP3 IIS UI', 'WP3 IIS', 'manage_options', 'wp3-iis/wp3-iis-ui.php');
	
	//call register settings function
	add_action( 'admin_init', 'register_wp3iis_settings' );
	$plugin = plugin_basename(__FILE__); 
	add_filter( 'plugin_action_links_' . $plugin, 'wp3iis_plugin_actions' );
}


/* Add Settings link to the plugins page*/
function wp3iis_plugin_actions($links) {
    $settings_link = '<a href="ms-admin.php?page=wp3-iis/wp3-iis-ui.php#wp3iis_options">Settings</a>';

    $links = array_merge( array($settings_link), $links);

    return $links;

}

function wp3iis_create_pending_headers_table() {

    global  $wpdb;
    $table_name = $wpdb->prefix . PENDING_HEADERS_TABLE;

	//add the table if its not present (upgrade or reactivation)
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $sql = "CREATE TABLE ".$table_name." (
                header_id bigint(20) NOT NULL AUTO_INCREMENT,
                blog_id bigint(20) unsigned NOT NULL,
                header_name varchar(200) NOT NULL default '',
                required_action varchar(20) NOT NULL,
                last_error longtext NOT NULL,
                PRIMARY KEY  (header_id)
                ) $charset_collate;";
        $result = dbDelta($sql);
     }   
	 
    //populate the table with current hostnames
	$query = "SELECT blog_id, domain FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}' ";
	$query .= " ORDER BY {$wpdb->blogs}.blog_id ";
	$blog_list = $wpdb->get_results( $query, ARRAY_A );

	if ( $blog_list ) {
		foreach ( $blog_list as $blog ) {

			$rows_affected = $wpdb->insert( $table_name,
											array( 'blog_id' => $blog['blog_id'],
												'header_name' => $blog['domain'],
												'required_action' => 'add',
												'last_error' => '' ) );
		}
	}

   
}

/* initialize the plugin settings*/
function wp3iis_init() {
	if(!get_site_option('wp3iis_website_name')){
		add_site_option('wp3iis_website_name', '[IIS Website name]');
		add_site_option('wp3iis_server_ip', '[IIS Website IP address]');
	}
        wp3iis_create_pending_headers_table();
        add_site_option('wp3iis_db_version', '1.0.0');
}

/* register settings*/
function register_wp3iis_settings() {
	register_setting( 'wp3iis_settings', 'wp3iis_website_name', 'wp3iis_update_website_name_option' );
	register_setting( 'wp3iis_settings', 'wp3iis_server_ip', 'wp3iis_update_server_ip_option' );
}

/* Update site option hack since register_setting isn't handling it*/
//global $wp3iis_lock_option;

//$wp3iis_lock_option = false;
function wp3iis_update_website_name_option($option) {
    global $wp3iis_lock_website_name_option;

    if($wp3iis_lock_website_name_option){
        $wp3iis_lock_website_name_option = false;
    }
    else{
        $wp3iis_lock_website_name_option = true;
        update_site_option('wp3iis_website_name', $option);
    }

    return $option;
}

function wp3iis_update_server_ip_option($option) {
    global $wp3iis_lock_server_ip_option;

    if($wp3iis_lock_server_ip_option){
        $wp3iis_lock_server_ip_option = false;
    }
    else{
        $wp3iis_lock_server_ip_option = true;
        update_site_option('wp3iis_server_ip', $option);
    }

    return $option;
}





/* Register new blog*/
function wp3iis_add_domain($blog_id, $user_id, $domain) {
    wp3iis_update_host_header($blog_id, $domain, 'add');
    
}
add_action( 'wpmu_new_blog', 'wp3iis_add_domain', 10, 3 );


/* Remove deleted blog*/
function wp3iis_remove_domain($blog_id) {
    $blog_details = get_blog_details($blog_id);
    wp3iis_update_host_header($blog_id, $blog_details->domain, 'remove');

}
add_action( 'delete_blog', 'wp3iis_remove_domain', 10, 1 );


function wp3iis_update_host_header($blog_id, $domain, $cmd){
    $hostname = $domain ;
    $website = get_site_option('wp3iis_website_name');
    $ip = get_site_option('wp3iis_server_ip');
    $reg_cmd = 'iisbroker.exe' . ' /action:' . $cmd . ' /website:"' . $website . '" /hostname:' . $hostname . ' /ip:' . $ip ;
    $output = array();
    $error = 0;
    $result = '';
    exec($reg_cmd . " 2>&1", $output, $error);
    if (($error != 0) && empty($output)){
            $last_error = error_get_last();
            $error = $last_error['message'];
    }
    else{
            $error = '';
            switch (strtolower($output[0])){
                case 'added':
                    $result = 'The domain ' . $hostname . ' has been added to IIS';
                    break;
                case 'removed':
                    $result = 'The domain ' . $hostname . ' has been removed from IIS';
                    break;
                case 'exists':
                    $result = 'The domain ' . $hostname . ' already exists on IIS';
                    break;
                case 'missing':
                    $result = 'The domain ' . $hostname . ' is missing from IIS';
                    break;
                default:
                    $error = implode(PHP_EOL, $output);
                    break;
            }
    }

    if(!empty($error)){
        global  $wpdb;
        $table_name = $wpdb->prefix . PENDING_HEADERS_TABLE;

        $rows_affected = $wpdb->insert( $table_name,
                                                array( 'blog_id' => $blog_id,
                                                    'header_name' => $domain,
                                                    'required_action' => $cmd,
                                                    'last_error' => $error ) );
        return;
    }
    ?>
        <div id="wp3iis_message" class="updated">
            <?php echo print_r($result, 1); ?>

        </div>

    <?php
    
}

?>