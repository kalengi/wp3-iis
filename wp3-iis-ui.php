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
								'Server IP' => 'wp3iis_server_ip',
								'DB version' => 'wp3iis_db_version');
					foreach($wp3iis_options as $option_key => $option_value){
						$delete_setting = delete_site_option($option_value);
                                                //there may be unused local options:
                                                delete_option($option_value);
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

                                        //remove headers table
                                        global  $wpdb;

                                        $table_name = $wpdb->prefix . PENDING_HEADERS_TABLE;
                                        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                                            $wpdb->query("DROP TABLE IF EXISTS $table_name");
                                            ?>
                                                <p class="setting_removed">Table: <?php echo $table_name; ?> => Removed</p>
                                            <?php
                                        }
                                        else{
                                            ?>
                                                <p class="setting_not_removed">Table: <?php echo $table_name; ?> => Not found</p>
                                            <?php
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

function wp3iis_show_settings_page() {
		
    ?>

        <div id="wp3iis-admin" class="wrap">
            <?php screen_icon(); ?>
            <h2>WP3 IIS</h2>
            <ul id="tabs" class="wp3iis_tabs">
                <li><a href="#wp3iis_options">IIS Website Options</a></li>
                <li><a href="#wp3iis_pending_headers">Pending Headers</a></li>
                <li><a href="#wp3iis_uninstall">Uninstall</a></li>
            </ul>

            <!-- Options Form -->
            <div id="wp3iis_options">
                 <h3>IIS Website Options</h3>
                <form method="post" action="options.php">
                    <?php settings_fields( 'wp3iis_settings' ); ?>
                    <?php $wp3iis_website_name = get_site_option('wp3iis_website_name'); ?>
                    <?php $wp3iis_server_ip = get_site_option('wp3iis_server_ip'); ?>

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
                </form>
            </div>

            <!-- Pending Headers List -->
            <div id="wp3iis_pending_headers">
                <h3>Pending Headers</h3>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Website description</th>
                        <td>
                              Dummy
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Server IP</th>
                        <td>
                             Dummy
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Uninstall Plugin -->
            <div id="wp3iis_uninstall">
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>">

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
                </form>
            </div>
        </div>

    <?php
}

?>