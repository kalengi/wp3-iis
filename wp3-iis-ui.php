<?php

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


/* 
	File Information: WP3 IIS ui page
*/

wp3iis_settings_page();

function wp3iis_settings_page() {
	
	if(!empty($_POST['uninstall'])) {
		wp3iis_remove_settings();
		return;
	}
	
	wp3iis_show_settings_page();
}

function wp3iis_remove_settings(){
	if($_POST['uninstall'] == 'UNINSTALL WP3 IIS'){
		?> 
			<div id="message" class="updated fade">
				<?php 
					$wp3iis_options = array('Show widget' => 'wp3iis_show_widget',
											'Default Related Links Category' => 'wp3iis_default_category',
											'Related Links Categories' => 'wp3iis_selected_categories',
											'Show Category Title' => 'wp3iis_show_category_title',
											'Category Link Order' => 'wp3iis_category_link_order');
					foreach($wp3iis_options as $option_key => $option_value){
						$delete_setting = delete_option($option_value);
						if($delete_setting) {
							?> 
							<p class="setting_removed">Setting: <?php echo $option_key; ?> => Removed</p>
							<?php
						} 
						else {
							?> 
							<p class="setting_not_removed">Setting: <?php echo $option_key; ?> => Not Removed </p>
							<?php
						}
					}
				?>
			</div>
		<?php
		
		$deactivate_url = 'plugins.php?action=deactivate&amp;plugin=wp3-iis%2Fwp3-iis.php';
		if(function_exists('wp_nonce_url')) { 
			$deactivate_url = wp_nonce_url($deactivate_url, 'deactivate-plugin_wp3-iis/wp3-iis.php');
		}
		
		?>
			<div class="wrap">
				<h2>Deactivate WP3 IIS</h2>
				<p class="deactivation_message">
					<a href="<?php echo $deactivate_url; ?>">Click Here</a> to deactivate the plugin automatically
				</p>
			</div>
		<?php
	}
}


function wp3iis_list_link_categories($default_category = '0', $category_link_order = array()) {
	
	$args = array('hide_empty' => 0);
	$categories = get_terms( 'link_category', $args );
	$wp3iis_selected_categories = get_option('wp3iis_selected_categories');
	
	if ( $categories ) {
		$output = '';
		$row_class = '';
		foreach ( $categories as $category ) {
			$args = array('category' => $category->term_id, 'hide_invisible' => 0, 'orderby' => 'name', 'hide_empty' => 0);
			$links = get_bookmarks( $args );
			
			$output .= "<tr id='wp3iis-cat-$category->term_id' class='list_item $row_class'>" . "\r\n";
			//checkbox
			$output .= "<th scope='row' class='check-column'>" . "\r\n";
			$output .= "<input type='checkbox' class='category_check' name='wp3iis_selected_categories[$category->slug]' ";
			$output .= "value='$category->term_id' ";
			$output .= checked("$category->term_id", $wp3iis_selected_categories[$category->slug], false) . ' />' . "\r\n";
			$output .= "</th>" . "\r\n";
			
			//Category name
			$output .= '<td class="name column-name">' . "\r\n";
			$output .= $category->name;
			$output .= '<br />' . "\r\n";
			$output .= '<div class="row-actions">' . "\r\n";
			$output .= "<span class='inline hide-if-no-js'>" . "\r\n";
			$output .= '<a href="#" class="listinline">Show&nbsp;Links</a>' . "\r\n"; 
			$output .= '</span>' . "\r\n";
			$output .= '</div>' . "\r\n";
			
			if(isset($category_link_order[$category->slug])){
				$output .= '<input type="hidden" id="wp3iis_category_link_order_' . $category->term_id . '" name="wp3iis_category_link_order[' . $category->slug . ']" value="' . $category_link_order[$category->slug] . '" />' . "\r\n";
			}
			
			$output .= '<div class="hidden" id="inline_list_' . $category->term_id . '">' . "\r\n";
			$output .= '<ul class="category_links_list " id="category_links_list_' . $category->term_id . '">' . "\r\n";
			
			$links = wp3iis_sort_category_links($links, $category_link_order[$category->slug]);
			
			foreach ( $links as $link ) {
				$output .= '<li class="link_title " id="category_link_' . $link->link_id . '">' . $link->link_name . '</li>' . "\r\n";
			}
			
			$output .= '</ul>' . "\r\n";
			$output .= '</div>' . "\r\n";
			$output .= '</td>' . "\r\n";
			
			//Category Description
			$output .= '<td class="description column-description">' . "\r\n";
			$output .= "<p>$category->description</p>" . "\r\n";
			$output .= '</td>' . "\r\n";
			
			//Default selector
			$output .= '<td class="default_list column-default">' . "\r\n";
			$output .= '<input type="radio" name="default_category" ';
			$output .= 'class="default_category_radio default_category_radio-' . $category->term_id . '" ';
			$output .= 'value="' . $category->term_id . '" '; 
			$output .= (checked("$category->term_id", $wp3iis_selected_categories[$category->slug], false) == '') ? 'disabled' : ''; 
			$output .= ' ' . checked($default_category, $wp3iis_selected_categories[$category->slug], false) . ' />' . "\r\n";
			$output .= '</td>' . "\r\n";
			
			$output .= "</tr>" . "\r\n";
			
			$row_class = ($row_class == 'alternate') ? '' : 'alternate';
			
			if(isset($wp3iis_selected_categories[$category->slug])){
				unset($wp3iis_selected_categories[$category->slug]);
			}
		}
		
		if(isset($wp3iis_selected_categories) && is_array($wp3iis_selected_categories)){
			$transfer_default = false;
			foreach ( $wp3iis_selected_categories as $slug => $term_id ) {
				$output .= "<tr id='wp3iis-cat-missing-$term_id' class='list_item_missing $row_class'>" . "\r\n";
				
				$output .= "<th scope='row' class='check-column'>&nbsp;" . "\r\n";
				$output .= "</th>" . "\r\n";
				$output .= '<td class="name column-name">' . "\r\n";
				$output .= $slug  . "\r\n";
				$output .= '</td>' . "\r\n";
				$output .= '<td class="description column-description">' . "\r\n";
				$output .= "<p>Missing</p>" . "\r\n";
				$output .= '</td>' . "\r\n";
				$output .= '<td class="default_list column-default">&nbsp;' . "\r\n";
				$output .= '</td>' . "\r\n";
				
				$output .= "</tr>" . "\r\n";
				
				$row_class = ($row_class == 'alternate') ? '' : 'alternate';
				
				if($default_category == $term_id){
					$transfer_default = true;
				}
			}
			
			if($transfer_default){
				$output .= '<script type="text/javascript" defer="defer">' . "\r\n";
				$output .= '	(function(rlmc){rlmc(document).ready(function(){wp3iisConfig.findNewDefault();})})(jQuery);' . "\r\n";
				$output .= '</script>' . "\r\n";
			}
		}
		
		return $output;
	}
	else{
		return '';
	}
	
}

error_reporting(E_ALL); 
ini_set("display_errors", 1);
function wp3iis_register_domain($domain) {
	xdebug_break();
	$reg_cmd = 'iisbroker /action:add /website:"DW Dining" /hostname:papalennons.dwdining.nerdonia /ip:192.168.182.129';
	$reg_cmd = 'ipconfig';
	$output = array();
	$error = exec($reg_cmd . " 2>&1", $output);
	return print_r($output, 1) . ': ERROR - ' . $error ;
}

function wp3iis_show_settings_page() {
		$domain = "papalennons.dwdining.nerdonia";
		$domain_result = wp3iis_register_domain($domain);
	?>
		<!-- Options Form -->
		<form method="post" action="options.php">
			<?php settings_fields( 'wp3iis_settings' ); ?>
			<?php $wp3iis_show_widget = get_option('wp3iis_show_widget'); ?>
			<?php $wp3iis_show_category_title = get_option('wp3iis_show_category_title'); ?>
			<?php $wp3iis_default_category = get_option('wp3iis_default_category'); ?>
			<?php $wp3iis_category_link_order = get_option('wp3iis_category_link_order'); ?>
			<?php $link_categories = wp3iis_list_link_categories($wp3iis_default_category, $wp3iis_category_link_order);?>
			<div class="wrap">
				<?php screen_icon(); ?>
				<h2>WP3 IIS Options</h2>
				<p> Doamin registration: <?php echo $domain; ?> : <?php echo $domain_result; ?></p>
				<h3>Select Link Categories</h3>
				
				<?php if(!empty($link_categories)){?>
						<table class="widefat fixed" cellspacing="0">
							<thead>
								<tr>
									<th scope="col" id="chk" class="manage-column column-chk check-column" style=""><input type="checkbox" disabled /></th>
									<th scope="col" id="name" class="manage-column column-name" style="">Link Category</th>
									<th scope="col" id="description" class="manage-column column-description" style="">Description</th>
									<th scope="col" id="default_list" class="manage-column column-default" style="">Default</th>
								</tr>
							</thead>

							<tfoot>
								<tr>
									<th scope="col" id="chk" class="manage-column column-chk check-column" style=""><input type="checkbox" disabled /></th>
									<th scope="col" id="name" class="manage-column column-name" style="">Link Category</th>
									<th scope="col" id="description" class="manage-column column-description" style="">Description</th>
									<th scope="col" id="default_list" class="manage-column column-default" style="">Default</th>
								</tr>
							</tfoot>

							<tbody id="wp3iis-list" class="list:wp3iis-cat">
								<?php echo $link_categories; ?>	
							</tbody>
						</table>

						<table class="form-table">
							<input type="hidden" id="wp3iis_category_link_order" name="wp3iis_category_link_order[_dummy_slug]" value="<?php echo $wp3iis_category_link_order['_dummy_slug']; ?>" />
							<input type="hidden" id="wp3iis_default_category" name="wp3iis_default_category" value="<?php echo ($wp3iis_default_category == '') ? '0' : $wp3iis_default_category; ?>" />
							<tr valign="top">
								<th scope="row">Show Widget</th>
								<td>
									<input name="wp3iis_show_widget" type="checkbox" value="1" <?php checked('1', $wp3iis_show_widget); ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">Show Category Title</th>
								<td>
									<input name="wp3iis_show_category_title" type="checkbox" value="1" <?php checked('1', $wp3iis_show_category_title); ?> />
								</td>
							</tr>
						</table>
						
						<p class="submit">
							<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
						</p>
				<?php 
				} 
				else{
				?>
						<div id="message" class="updated fade">
							<p class="settings_update_message">
								There are no Link Categories to select from
							</p>
						</div>
				<?php
				}
				?>
			</div>
		</form>
		
		<!-- Inline link listing -->
		<table style="display: none">
			<tbody id="inlinelist">
				<tr id="inline-list" class="inline-list-row" style="display: none">
					<td colspan="1">
						<fieldset>
							<span class="error_message" id="error_message">Error Message</span>
							<div class="inline-list-col">
								<h4><?php _e( 'Links' ); ?></h4>
								<span class="info_message" >Click and drag links to reorder, then Save Changes below</span>
								<div id="link_container">
									<span class="link_placeholder">Links List</span>
								</div>

							</div>
						</fieldset>
						<p class="inline-list-close submit">
							<a accesskey="c" href="#inline-list" title="Close" class="close button-secondary alignleft">Close</a>
							<br class="clear" />
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		
		
		<!-- Uninstall Plugin -->
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>">
			<div id="wp3iis_uninstall" class="wrap"> 
				<h3>Uninstall WP3 IIS plugin</h3>
				<p>
					The uninstall action removes all WP3 IIS plugin settings that have been saved in your WordPress database. Use this prior to deactivating the plugin.
				</p>
				<p class="warning">
					Please note that the deleted settings cannot be recovered. Proceed only if you do not wish to use these settings any more.
				</p>
				<p class="uninstall_confirmation">
					<input type="submit" name="uninstall" value="UNINSTALL WP3 IIS" class="button" onclick="return confirm('You Are About To Uninstall WP3 IIS From WordPress.\n\n Choose [Cancel] To Stop, [OK] To Uninstall.')" />
				</p>
			</div> 
		</form>
	<?php
}

?>