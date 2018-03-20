<?php
 /*
 Plugin Name: WC Content Freeze
 Plugin URI: https://github.com/Watson-Creative/Content-Freeze
 GitHub Plugin URI: https://github.com/Watson-Creative/Content-Freeze
 description: Add a prominent warning to live sites to prevent users from making content changes that may be overridden by dev-server updates.
 Version: 1.3.1
 Author: Alex Tryon
 Author URI: http://www.alextryonpdx.com
 License: GPL2
 */

/*


This plugin allows admins to create warning for users to be notified of content freeze periods during dev work on another server.



*/



add_action('admin_head', 'freeze_styles');
function freeze_styles() {
	echo '<style> 
		.freeze-options textarea {
	    height: 10em;
		}

		.freeze-options input[type="text"], textarea {
	    width: 100%;
		}

		.notice-watson {
	    border-left-color: #1abcb9;
	    z-index: 9999999;
		}

		.notice-watson a {
			color: #1abcb9 !important;
			text-decoration: none;
		}

		.notice-watson img {
			max-width: 500px;
	    width: 100%;
		}

		#watson-warning {
	    position: fixed;
	    z-index: 999999;
	    left: 0;
	    right: 0;
	    top: 0;
	    bottom: 0;
	    background-color: rgba(0,0,0,.6);
	    margin: 0 0;
		}
		</style>';
}

//////////////////////////////////////////   Set Default Vaules   //////////////////////////////////////////


// Add default vaules on initial load
register_activation_hook(__FILE__,'create_default_values');

function create_default_values() {

	$freeze_delay_default = 60;
	$freeze_heading_default = 'A Temporary Content Freeze is in Effect';
	$freeze_text_default = 'We are currently working to improve your site in a development environment. When we are done, we will need to apply these improvements to this live site, so any changes made in the meantime are likely to be lost. Please hold on changes or updates to your site during this brief maintenance period.';

	$freeze_deactivation_heading_default = 'Content Freeze is Lifted';
	$freeze_deactivation_text_default = 'Thank you for your patience. You are now free to safely update and edit content on your site.';


	if ( get_option( 'freeze_delay' ) == false ) { 
				add_option("freeze_delay", $freeze_delay_default); 
			}
	if ( get_option( 'freeze_heading' ) == false || get_option( 'freeze_heading' ) == '' ) { 
				add_option("freeze_heading", $freeze_heading_default); 
			}
	if ( get_option( 'freeze_text' ) == false || get_option( 'freeze_text' ) == '' ) { 
				add_option("freeze_text", $freeze_text_default ); 
			}
	if ( get_option( 'freeze_deactivation_heading' ) == false || get_option( 'freeze_deactivation_heading' ) == '') { 
				add_option("freeze_deactivation_heading", $freeze_deactivation_heading_default); 
			}
	if ( get_option( 'freeze_deactivation_text' ) == false || get_option( 'freeze_deactivation_text' ) == '' ) { 
				add_option("freeze_deactivation_text", $freeze_deactivation_text_default); 
			}

	add_option('freeze_delay_default', $freeze_delay_default);
	add_option('freeze_heading_default', $freeze_heading_default);
	add_option('freeze_text_default', $freeze_text_default);
	add_option('freeze_deactivation_heading_default', $freeze_deactivation_heading_default);
	add_option('freeze_deactivation_text_default', $freeze_deactivation_text_default);
}



// save start and endtime timestamps on plugin activation
register_activation_hook(__FILE__,'save_timestamps');

function save_timestamps() {
	$init_start = time();
	$init_end = $init_start + (get_option('freeze_delay')*60); // add delay time (in sec) to activation timestamp
	if ( get_option( 'freeze_activation_timestamp' ) !== false ) {
		update_option("freeze_activation_timestamp", $init_start);
	} else {
		add_option("freeze_activation_timestamp", $init_start);
	}

	// if ( get_option( 'freeze_deactivation_timestamp' ) !== false ) {
	// 	update_option("freeze_deactivation_timestamp", $init_end);
	// } else {
	// 	add_option("freeze_deactivation_timestamp", $init_end);
	// }
}


// test current time against endtime timestamp
if ( time() > (get_option('freeze_activation_timestamp') + ( get_option('freeze_delay') *60 ) ) ) {
	     // if ( current_user_can( 'activate_plugins' ) ) {

		     	// deactivate plugin
          add_action( 'admin_init', 'freeze_deactivate' );

          // notify user of deactivation
          add_action( 'admin_notices', 'freeze_deactivation_admin_notice' );

} else {
	add_action('admin_init', 'freeze_active_admin_notice');
}









//////////////////////////////////////////   LOGIC   //////////////////////////////////////////

//deactivate plguin
function freeze_deactivate() {
    deactivate_plugins( plugin_basename( __FILE__ ) );
}

// FREEZE ACTIVE admin warning
function freeze_active_admin_notice(){
	// echo '<div id="watson-warning">';
	echo '<div class="notice notice-watson">';
	echo '<a href="https://www.watsoncreative.com/"><img src="'. plugins_url('img/WC_Brand_Signature.png', __FILE__) . '"  /></a>';
	echo '<h1>' . get_option('freeze_heading') . '</h1>';
	echo '<h4>' . get_option('freeze_text') . '</h4>';
	echo '<h4>The content freeze is in effect for approximately ' . round( ( ( intval( get_option('freeze_activation_timestamp') ) + intval( get_option('freeze_delay')*60 ) ) - intval( time() ) ) / 60 ) . ' more minutes.</h4>';
	echo '<h4>Please email the <a href="mailto:dev@watsoncreative.com">Watson Dev Team</a> with any questions or concerns.</h4>';
	echo '<h4>Thanks,<br/>Watson Creative</h4>';
	echo '</div>';
	// echo "</div>";
}

// ONE TIME DEACTIVATION admin warning
function freeze_deactivation_admin_notice(){
	echo '<div class="notice notice-watson">';

	echo '<img src="'. plugins_url('img/WC_Brand_Signature.png', __FILE__) . '" style="width:100%;" />';
	echo '<h1>' . get_option('freeze_deactivation_heading') . '</h1>';
	echo '<h4>' . get_option('freeze_deactivation_text') . '</h4>';
	echo '<h4>Please email the <a href="mailto:dev@watsoncreative.com">Watson Dev Team</a> with any questions or concerns.</h4>';
	echo '<h4>Thanks,<br/>Watson Creative</h4>';
		// echo '<h1>FREEZE ACTIVE - end:' . get_option('freeze_deactivation_timestamp') . ' - start:' . get_option('freeze_activation_timestamp') . ' - current:'.time().'</h1>';
	echo '</div>';
}


////////////////////////////////////////   SETTINGS   ////////////////////////////////////////

if ( is_admin() ){ // admin actions
  add_action( 'admin_menu', 'freeze_create_menu' );
  add_action( 'admin_init', 'register_freeze_settings' );
}


function freeze_create_menu() {

	//create new top-level menu
	add_menu_page('WC Content Freeze Settings', 'Freeze Settings', 'administrator', __FILE__, 'freeze_settings_page', plugins_url('img/WC_Brand-20.png', __FILE__ ) );

	//call register settings function
	add_action( 'admin_init', 'register_freeze_settings' );
}


function register_freeze_settings() { // whitelist options
	register_setting( 'freeze_option-group', 'freeze_activation_timestamp' );
  register_setting( 'freeze_option-group', 'freeze_delay' );
  register_setting( 'freeze_option-group', 'freeze_heading' );
  register_setting( 'freeze_option-group', 'freeze_text' );
  register_setting( 'freeze_option-group', 'freeze_deactivation_text' );
}

function freeze_settings_page() {
?>

<div class="wrap">
	<h1>Watson Creative Content Freeze</h1>
	<form method="post" action="options.php"> 
		<?php 
		settings_fields( 'freeze_option-group' );
		do_settings_sections( 'freeze_option-group' ); ?>

		<table class="form-table freeze-options">

				<input type="hidden" name="freeze_activation_timestamp" value="<?php echo time(); ?>">

				<tr valign="top">
	        <th scope="row">Freeze Duration in minutes</th>
	        <td><input type="number" step="1" name="freeze_delay" value="<?php echo esc_attr( get_option('freeze_delay') ); ?>" /></td>
        </tr>

        <tr valign="top">
	        <th scope="row">Heading Content</th>
	        <td><input type="text" name="freeze_heading" value="<?php echo esc_attr( get_option('freeze_heading') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
	        <th scope="row">Warning Content</th>
	        <td><textarea name="freeze_text"><?php echo esc_attr( get_option('freeze_text') ); ?></textarea></td>
        </tr>
        
        <tr valign="top">
	        <th scope="row">Freeze Deactivation Heading</th>
	        <td><input type="text" name="freeze_deactivation_heading" value="<?php echo esc_attr( get_option('freeze_deactivation_heading') ); ?>" /></td>
        </tr>

        <tr valign="top">
	        <th scope="row">Freeze Deactivation Text</th>
	        <td><textarea name="freeze_deactivation_text"><?php echo esc_attr( get_option('freeze_deactivation_text') ); ?></textarea></td>
        </tr>

    </table>

    <?php
		submit_button('Save Changes and Reset Timer');
		?>
	</form>


	<form method="post" action="options.php">
			<?php 
			settings_fields( 'freeze_option-group' );
			do_settings_sections( 'freeze_option-group' ); ?>
			<input type="hidden" name="freeze_activation_timestamp" value="<?php echo time(); ?>">
			<input class="hidden" type="number" step="1" name="freeze_delay" value="<?php echo get_option('freeze_delay_default'); ?>" />
			<input type="hidden" name="freeze_heading" value="<?php echo get_option('freeze_heading_default'); ?>" />
			<textarea class="hidden" name="freeze_text"><?php echo get_option('freeze_text_default'); ?></textarea>
			<input class="hidden" type="text" name="freeze_deactivation_heading" value="<?php echo get_option('freeze_deactivation_heading_default'); ?>" />
			<textarea class="hidden" name="freeze_deactivation_text"><?php echo get_option('freeze_deactivation_text_default'); ?></textarea>
			<?php
			submit_button('Restore Default Settings and Reset Timer');
			?>
	</form>
</div>







<?php } ?>