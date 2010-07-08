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
					$wp3iis_options = array('Website description' => 'wp3iis_website_name',
											'Server IP' => 'wp3iis_server_ip');
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
					<a href="<?php echo $deactivate_url; ?>">Click Here</a> to deactivate the WP3 IIS plugin automatically
				</p>
			</div>
		<?php
	}
}

function wp3iis_register_domain($domain) {
	$cmd = 'remove' ;
	$website = 'DW Dining';
	$hostname = $domain ;
	$ip = '192.168.182.129' ;
	$reg_cmd = 'iisbroker.exe' . ' /action:' . $cmd . ' /website:"' . $website . '" /hostname:' . $hostname . ' /ip:' . $ip ;
	$output = array();
	$error = 0;
	exec($reg_cmd . " 2>&1", $output, $error);
	if (($error != 0) && empty($output)){
		$last_error = error_get_last();
		$error = ': ERROR - ' . $last_error['message'];
	}
	else{
		$error = '';
	}
	return print_r($output, 1) .  $error ;
}

function wp3iis_show_settings_page() {
		$domain = "papalennons.dwdining.nerdonia";
		$domain_result = wp3iis_register_domain($domain);
		xdebug_break();
	?>
		<!-- Options Form -->
		<form method="post" action="options.php">
			<?php settings_fields( 'wp3iis_settings' ); ?>
			<?php $wp3iis_website_name = get_option('wp3iis_website_name'); ?>
			<?php $wp3iis_server_ip = get_option('wp3iis_server_ip'); ?>
			<div class="wrap">
				<?php screen_icon(); ?>
				<h2>WP3 IIS Options</h2>
				<p> Doamin registration result: <?php echo $domain; ?> : <?php echo $domain_result; ?></p>
				<h3>IIS Website Details</h3>
				
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Website description</th>
						<td>
							<input name="wp3iis_website_name" type="text" value="<?php echo $wp3iis_website_name; ?>"  />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Server IP</th>
						<td>
							<input name="wp3iis_server_ip" type="text" value="<?php echo $wp3iis_server_ip; ?>"  />
						</td>
					</tr>
				</table>
				
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</div>
		</form>
		
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