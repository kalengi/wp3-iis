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
	//ajax handling
	add_action('wp_ajax_wp3iis_action', 'wp3iis_ajax_callback');
	
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
	add_option('wp3iis_show_widget', '1');
	add_option('wp3iis_default_category', '2'); //ID of blogroll
	$wp3iis_selected_categories = array('blogroll' => '2'); //assumption is this category is typically availa ble at this ID
	add_option('wp3iis_selected_categories', $wp3iis_selected_categories);
	add_option('wp3iis_show_category_title', '1');
}

/* register settings*/
function register_wp3iis_settings() {
	register_setting( 'wp3iis_settings', 'wp3iis_show_widget' );
	register_setting( 'wp3iis_settings', 'wp3iis_default_category' );
	register_setting( 'wp3iis_settings', 'wp3iis_selected_categories' );
	register_setting( 'wp3iis_settings', 'wp3iis_show_category_title' );
	register_setting( 'wp3iis_settings', 'wp3iis_category_link_order' );
}


/* Add Settings link to the plugins page*/
function wp3iis_plugin_actions($links) {
    $settings_link = '<a href="tools.php?page=wp3-iis/wp3-iis-ui.php">Settings</a>';

    $links = array_merge( array($settings_link), $links);

    return $links;

}

/* Load js files*/
function wp3iis_load_scripts() {
	wp_enqueue_script('inline-links-list', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/inline-links-list.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), '1.0');
	wp_enqueue_script('wp3-iis', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/wp3-iis.js', array('jquery'), '1.0');
}

/* Load css files*/
function wp3iis_load_stylesheets() {
	$style_file = plugins_url('wp3-iis/wp3-iis.css');
	
	echo '<link rel="stylesheet" type="text/css" href="' . $style_file . '" />' . "\r\n";
	
}


/* Add a custom box to the Page Edit admin screen*/
function wp3iis_add_custom_box() {
	 add_meta_box( 'wp3iis_category_list', 'Related Link Categories', 'wp3iis_custom_box_html', 'page', 'side', 'low' );
	// add_meta_box( 'wp3iis_category_list', 'Related Link Categories', 'wp3iis_custom_box_html', 'post', 'side', 'low' );
	 
}


/* Ordering meta box*/
function wp3iis_order_meta_boxes($page, $context, $object) {
    
    if (($context == 'side') && (($page == 'page') || ($page == 'post')) ) {
        // Place meta box  as the  second in order
		wp3iis_position_metabox('wp3iis_category_list', $page, $context, 1);
    }
    
}


/* Sort meta boxes */
function wp3iis_position_metabox($id, $page = 'page', $context = 'side', $position = 1) {
	//handle the recursion
	static $been_here = false;
	
	$metabox_sort_order = get_user_option( "meta-box-order_$page", 0, false );
	
	if(!empty($metabox_sort_order) && (is_array($metabox_sort_order)) && (!empty($metabox_sort_order[$context]))){
		$metaboxes = $metabox_sort_order[$context];
		$metaboxes = explode(',', $metaboxes);
		
		$been_here = false;
		
		if($metaboxes[$position] == $id){
			return;
		}
		
		$flipped = array_flip($metaboxes);
		
		$new_metaboxes = array();
		$old = 0;
		$new = 0;
		$metabox_placed = false;
		
		if(!empty($flipped[$id])){
			if($position >= count($metaboxes)){
				return;
			}
			
			foreach($metaboxes as $metabox){
				if($metaboxes[$old] == $id){
					$old += 1;
				}
				
				if($new == $position){
					$new_metaboxes[$new] = $id;
					$new += 1;
					$metabox_placed = true;
				}
				
				if(!empty($metaboxes[$old])){
					$new_metaboxes[$new] = $metaboxes[$old];
				}
				$old += 1;
				$new += 1;
			}
		}
		else{
			if($position > count($metaboxes)){
				return;
			}
			
			while(1 == 1){
				if($new == $position){
					$new_metaboxes[$new] = $id;
					$new += 1;
					$metabox_placed = true;
				}
				
				if(empty($metaboxes[$old])){
					if($metabox_placed){
						break;
					}
				}
				
				$new_metaboxes[$new] = $metaboxes[$old];
				
				$old += 1;
				$new += 1;
			}
		}
		
		$metabox_sort_order[$context] = implode(',', $new_metaboxes);
		
		$user = wp_get_current_user();
		update_user_option($user->ID, "meta-box-order_$page", $metabox_sort_order);
		
		
		
	}
	else{
		if($been_here){
			return;
		}
		
		global $wp_meta_boxes;
		
		$metaboxes = $wp_meta_boxes[$page][$context];
		
		if(empty($metaboxes)){
			return;
		}
		
		$priorities = array('high', 'core', 'low');
		$sort_list = array();
		foreach($priorities as $priority){
			if(!empty($metaboxes[$priority])){
				$sort_list = array_merge($sort_list, array_keys($metaboxes[$priority]));
			}
		}
		
		if(!isset($metabox_sort_order)){
			$metabox_sort_order = array();
		}
		
		$metabox_sort_order[$context] = implode(',', $sort_list);
		
		$user = wp_get_current_user();
		update_user_option($user->ID, "meta-box-order_$page", $metabox_sort_order);
		
		//for some reason there was an infinite recursion scenario at some point. This static variable is in place to stop that from happening in future
		$been_here = true;
			wp3iis_position_metabox($id, $page, $context, $position);
		$been_here = false;
		
	
	}
}


/* Add code for the Page Edit custom box*/
function wp3iis_custom_box_html() {
	 //get previously selected categories
     global $post;
     $selected_categories = get_post_meta($post->ID,'wp3iis_categories',true);
	
	if(empty($selected_categories)){
		 $selected_categories = array();
	}
     // use nonce for verification
     echo '<input type="hidden" name="wp3iis_noncename" id="wp3iis_noncename" value="'.wp_create_nonce(plugin_basename(__FILE__)).'" />' . "\r\n";

     // The list of categories for selection
	 $available_categories = get_option('wp3iis_selected_categories');
	 if(!empty($available_categories)){
     ?>
		 <ul id="categorychecklist" class="list:category categorychecklist form-no-clear">
	<?php
		foreach ( $available_categories as $slug => $id ) {
			$category =  get_term($id, 'link_category');
			if(is_null($category)){
				continue;
			}
		?>
			<li id='link-category-<?php echo $id; ?>' ><label class="selectit" for="in-link-category-<?php echo $id; ?>"><input value="<?php echo $id; ?>" type="checkbox" name="link_category[]" id="in-link-category-<?php echo $id; ?>" <?php checked("$id", $selected_categories[$slug]);?>/> <?php echo $category->name; ?></label></li>
		<?php
			if(isset($selected_categories[$slug])){
				unset($selected_categories[$slug]);
			}
		}
		
		if(isset($selected_categories) && is_array($selected_categories)){
			foreach ( $selected_categories as $slug => $id ) {
				?>
					<li id='link-category-<?php echo $id; ?>' class="category-missing" ><label class="selectit" "category-missing" for="in-link-category-<?php echo $id; ?>"><?php echo $slug; ?> missing. <a href="link-manager.php?page=wp3-iis/wp3-iis-ui.php">View Options</a></label></li>
				<?php
			}
		}
	?>
		</ul>
	<?php
	 }
}

/* Save Related Link Categories*/
function wp3iis_save_postdata( $post_id ) {

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times

	if ( !wp_verify_nonce( $_POST['wp3iis_noncename'], plugin_basename(__FILE__) )) {
		return $post_id;
	}

	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
	// to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
		return $post_id;
	}


	// Check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) ){
		  return $post_id;
		}
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) ){
		  return $post_id;
		}
	}

	// OK, we're authenticated: we need to find and save the data

	$selected_categories = $_POST['link_category'];
	$update_categories = array();
	
	if(isset($selected_categories) && is_array($selected_categories)){
		foreach ($selected_categories as $id ) {
			$category =  get_term($id, 'link_category');
			if(is_null($category)){
				continue;
			}
			
			$update_categories[$category->slug] = $category->term_id;
		}
		
	}
	update_post_meta($post_id, 'wp3iis_categories', $update_categories);
	
	return $selected_categories;
}


/* Related Link widget*/
class wp3iis_Widget extends WP_Widget {
	function wp3iis_Widget() {
		parent::WP_Widget(false, $name = 'Related Links');
	}

	function form($instance) {
		$title = esc_attr($instance['title']);
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</label></p>
        <?php
	}

	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	function widget($args, $instance) {
		extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		echo $before_widget; 
			if ( $title ){
				echo $before_title . $title . $after_title; 
			}
			echo $this->generate_menu_items(); 
		echo $after_widget; 
	}
	
	function generate_menu_items(){
		global $post;
		$selected_categories = get_post_meta($post->ID,'wp3iis_categories',true);
	
		$category_found = true;
		if(!empty($selected_categories)){
			foreach ( $selected_categories as $slug => $id ) {
				$category =  get_term($id, 'link_category');
				if(is_null($category)){
					$category_found = false;
					unset($selected_categories[$slug]);
					continue;
				}
				$category_found = true;
				break;
			}
		}
					
					
		if(empty($selected_categories) || !$category_found){
			$selected_categories = array();
			$default_category = get_option('wp3iis_default_category');
			
			if(!empty($default_category)){
				$category =  get_term($default_category, 'link_category');
				if(is_null($category)){
					$available_categories = get_option('wp3iis_selected_categories');
					if(!empty($available_categories)){
						foreach ( $available_categories as $slug => $id ) {
							if($id == $default_category){
								continue;
							}
							
							$category =  get_term($id, 'link_category');
							if(is_null($category)){
								continue;
							}
							break;
						}
						
						if(!is_null($category)){
							$selected_categories = array("$category->slug" => "$category->term_id");
						}
					}
				}
				else{
					$selected_categories = array("$category->slug" => "$category->term_id");
				}
			}
		}
		
		
		$output = '';
		if(!empty($selected_categories)){
			$show_category_title = get_option('wp3iis_show_category_title');
			$category_link_order = get_option('wp3iis_category_link_order');
			$args = array('category' => 0, 'hide_invisible' => 0, 'orderby' => 'name', 'hide_empty' => 0);
			$displayed = array();
			foreach ( $selected_categories as $slug => $id ) {
				$args['category'] = $id;
				$links = get_bookmarks( $args );
				
				if(!empty($links)){
					if($show_category_title){
						$category_title = $links[0]->description;
						$output .= "<h2 class='related_links_menu_title' id='related_links_menu_title-$id'>$category_title</h2>" . "\r\n";
					}
					else{
						if(!empty($displayed)){
							$output .= '<br class="category_separator" />' . "\r\n";
						}
					}
					$output .= "<ul class='related_links_menu' id='related_links_menu-$id'>" . "\r\n";
					
					$links = wp3iis_sort_category_links($links, $category_link_order[$slug]);
					
					foreach ( $links as $link ) {
						if(!isset($displayed[$link->link_name])){
							$displayed[$link->link_name] = $link->link_id ;
							$current = '';
							if(preg_replace('/\//', '', $_SERVER['REQUEST_URI']) == preg_replace('/\//', '', $link->link_url)){
								$current = 'current';
							}
							$output .= '<li class="related_link related_link_' . $link->link_id . ' ' . $current . '"><a href="' . $link->link_url . '">' . $link->link_name . '</a></li>' . "\r\n";
						}
					}
					
					$output .= '</ul>' . "\r\n";
				}
			}
		}
		
		return $output;
	}

}

add_action('widgets_init', create_function('', 'return register_widget("wp3iis_Widget");'));

function wp3iis_sort_category_links($links, $link_order = ''){
	if(!isset($links) || !is_array($links)){
		return array();
	}
	
	if(empty($link_order)){
		return $links;
	}
	
	$loadedLinks = array();
	foreach ( $links as $link ) {
		$loadedLinks[$link->link_id] = $link;
	}
	
	$sortedLinks = array();
	if(isset($link_order)){
		$seralized_sort = '&' . $link_order;
		$sortedLinks = explode('&category_link[]=', $seralized_sort);
		array_shift($sortedLinks);
	}
	
	$links = array();
	while(!empty($sortedLinks)){
		$link_id = array_shift($sortedLinks);
		if(isset($loadedLinks[$link_id])){
			$links[$link_id] = $loadedLinks[$link_id];
			unset($loadedLinks[$link_id]);
			if(empty($loadedLinks)){
				break;
			}
		}
	}
	
	foreach ( $loadedLinks as $link ) {
		$links[$link->link_id] = $link;
	}
	
	return $links;
}
?>