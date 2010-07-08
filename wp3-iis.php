<?php
/*
Plugin Name: WP3 IIS
Plugin URI: http://www.dennisonwolfe.com/
Description: The  WP3 IIS plugin adds sub-domains to Microsoft Internet Infaormation Server.
Version: 1.0.0
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


if ( is_admin() ) {
	//plugin activation
	add_action('activate_wp3-iis/wp3-iis.php', 'wp3iis_init');
	//settings menu
	add_action('admin_menu', 'wp3iis_tools_menu');
	//load css
	add_action('admin_head', 'wp3iis_load_stylesheets');
	//load js
	add_action('wp_print_scripts', 'wp3iis_load_scripts' );
	
}
else{
	//load css
	add_action('wp_head', 'wp3iis_load_stylesheets');
	
}


/* Configuration Screen*/

function wp3iis_tools_menu() {
	add_submenu_page( 'tools.php', 'WP3 IIS UI', 'WP3 IIS', 'manage_options', 'wp3-iis/wp3-iis-ui.php');
	
	//call register settings function
	add_action( 'admin_init', 'register_wp3iis_settings' );
	$plugin = plugin_basename(__FILE__); 
	add_filter( 'plugin_action_links_' . $plugin, 'wp3iis_plugin_actions' );
}


/* initialize the plugin settings*/
function wp3iis_init() {
	add_option('wp3iis_website_name', '[IIS Website name]');
	add_option('wp3iis_server_ip', '[IIS Website IP address]'); 
}

/* register settings*/
function register_wp3iis_settings() {
	register_setting( 'wp3iis_settings', 'wp3iis_website_name' );
	register_setting( 'wp3iis_settings', 'wp3iis_server_ip' );
}


/* Add Settings link to the plugins page*/
function wp3iis_plugin_actions($links) {
    $settings_link = '<a href="tools.php?page=wp3-iis/wp3-iis-ui.php">Settings</a>';

    $links = array_merge( array($settings_link), $links);

    return $links;

}

/* Load js files*/
function wp3iis_load_scripts() {
}

/* Load css files*/
function wp3iis_load_stylesheets() {
	$style_file = plugins_url('wp3-iis/wp3-iis.css');
	
	echo '<link rel="stylesheet" type="text/css" href="' . $style_file . '" />' . "\r\n";
	
}

?>