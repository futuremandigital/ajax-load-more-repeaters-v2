<?php
/*
Plugin Name: Ajax Load More: Custom Repeaters v2
Plugin URI: http://connekthq.com/plugins/ajax-load-more/custom-repeaters/
Description: Ajax Load More extension to allow for unlimited repeater templates.
Author: Darren Cooney
Twitter: @KaptonKaos
Author URI: http://connekthq.com
Version: 2.5.3
License: GPL
Copyright: Darren Cooney & Connekt Media
*/


define('ALM_UNLIMITED_PATH', plugin_dir_path(__FILE__));
define('ALM_UNLIMITED_REPEATER_PATH', plugin_dir_path(__FILE__) . 'repeaters/');
define('ALM_UNLIMITED_URL', plugins_url('', __FILE__));
define('ALM_UNLIMITED_VERSION', '2.5.3');
define('ALM_UNLIMITED_RELEASE', 'November 11, 2018');



/*
 *  alm_unlimited_activation
 *  Core activation hook function
 *
 *  @since 2.0
 *  @updated 2.4
 */
function alm_unlimited_activation($network_wide) {
   if(is_plugin_active('ajax-load-more/ajax-load-more.php')){ // Ajax Load More is activated

		global $wpdb;
	   add_option( "alm_unlimited_version", ALM_UNLIMITED_VERSION ); // Add to WP Options table

	   if ( is_multisite() && $network_wide ) {

	      // Get all blogs in the network and create `alm_unlimited` table for each
	      $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	      foreach ( $blog_ids as $blog_id ) {
	         switch_to_blog( $blog_id );
	         alm_unlimited_create_table();
	         restore_current_blog();
	      }

	   } else {

	      alm_unlimited_create_table();

	   }

	} else {
		die(__('You must install and activate Ajax Load More before installing the Custom Repeaters Add-on.', 'ajax-load-more-repeaters-v2'));
	}
}
register_activation_hook( __FILE__, 'alm_unlimited_activation' );
add_action( 'wpmu_new_blog', 'alm_unlimited_activation' );



/*
 *  alm_unlimited_create_table
 *  Create Table in WP DB
 *
 *  @since 2.0
 *  @updated 2.5.2
 */
function alm_unlimited_create_table(){
	global $wpdb;
	$table_name = $wpdb->prefix . "alm_unlimited";
	//Create table, if it doesn't already exist.
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name text NOT NULL,
			repeaterDefault longtext NOT NULL,
			alias TEXT NOT NULL,
			pluginVersion text NOT NULL,
			UNIQUE KEY id (id)
		);";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
	}
}



/*
 *  alm_unlimited_check_table
 *  Table exist checker function
 *
 *  @since 2.5.2
 */
function alm_unlimited_check_table(){
	global $wpdb;
	$table_name = $wpdb->prefix . "alm_unlimited";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		alm_unlimited_create_table();
	}
}



// Class ALMUnlimitedRepeaters

if( !class_exists('ALMUnlimitedRepeaters') ):

   class ALMUnlimitedRepeaters{

   	function __construct(){
   		add_action( 'alm_unlimited_repeaters', array(&$this, 'alm_unlimited_add_ons' ));
   		add_action( 'alm_get_unlimited_repeaters', array(&$this, 'alm_get_unlimited_add_ons' ));
   		add_action( 'alm_unlimited_installed', array(&$this, 'alm_is_unlimited_installed' ));
   		add_action( 'plugins_loaded', array(&$this, 'alm_unlimited_update' ));
         add_action( 'alm_unlimited_settings', array(&$this, 'alm_unlimited_settings'));

   		// Ajax actions
         add_action( 'wp_ajax_alm_unlimited_create', array(&$this, 'alm_unlimited_create' )); // Create template
         add_action( 'wp_ajax_alm_unlimited_delete', array(&$this, 'alm_unlimited_delete' )); // Delete template

   		//Load text domain
   		load_plugin_textdomain( 'ajax-load-more-repeaters-v2', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
   	}



   	/*
      *  alm_unlimited_get_repeater_path
      *  Get absolute path to repeater directory base
      *
      *  Multisite installs directories will be `uploads/sites/{id}/alm_templates`
      *
      *  @return $path;
      *  @since 2.5
      */
      public static function alm_unlimited_get_repeater_path(){
         $upload_dir = wp_upload_dir();
         $path = apply_filters( 'alm_repeater_path', $upload_dir['basedir']. '/alm_templates' );
         return $path;
      }



      /*
      *  alm_unlimited_mkdir
      *  Create repeater template directory
      *
      *  @since 2.5
      */
      public static function alm_unlimited_mkdir($dir){

	      // Does $dir exist?
	      if( !is_dir($dir) ) {
	      	wp_mkdir_p($dir);

	      	// Check again after creating it (permission checker)
		      if( !is_dir($dir) ) {
			      echo __('Error creating repeater template directory', 'ajax-load-more-repeaters-v2');
			      echo ' - '. $dir;
			   }
	      }
      }



      /*
      * alm_unlimited_update
      * Update repeaters if the database version of the repeater doesn't match the current plugin version.
      * Check by version numbers
      *
      * @since 2.0
      * @updated 2.4
      */

      function alm_unlimited_update() {

	      if(!get_option( 'alm_unlimited_version')){ // Add to WP options table if it does not exist
				add_option( 'alm_unlimited_version', ALM_UNLIMITED_VERSION );
			}

			$alm_unlimited_installed_ver = get_option( "alm_unlimited_version" ); // Get value from WP Option tbl
			if ( $alm_unlimited_installed_ver != ALM_UNLIMITED_VERSION ) {
				$this->alm_unlimited_run_update();
			}

      }



		/*
		* alm_unlimited_run_update
		* Run the update on all 'blogs'
		*
		* @since 2.4
      * @updated 2.4
		*/

		function alm_unlimited_run_update(){
			global $wpdb;

		   if ( is_multisite()) {
		   	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

		   	// Loop all blogs and run update routine
		      foreach ( $blog_ids as $blog_id ) {
		         switch_to_blog( $blog_id );
		         $this->alm_update_unlimited_template_files();
		         restore_current_blog();
		      }

		   } else {
            $this->alm_update_unlimited_template_files();
		   }

		   update_option( "alm_unlimited_version", ALM_UNLIMITED_VERSION ); // Update the WP Option tbl with the new version num

		}



		/*
		* alm_update_unlimited_template_files
		* Update routine for Custom Repeater templates
		*
		* @since 2.4
		* @updated 2.5
		*/

		function alm_update_unlimited_template_files(){

			global $wpdb;
			$table_name = $wpdb->prefix . "alm_unlimited";

      	$rows = $wpdb->get_results("SELECT * FROM $table_name"); // Get all templates ($rows) from database

      	// Get Base Directory
         $base_dir = ALMUnlimitedRepeaters::alm_unlimited_get_repeater_path();

         // Create Base Repeater Dir (alm_templates) if required
         ALMUnlimitedRepeaters::alm_unlimited_mkdir($base_dir);

         if($rows){
            // Loop $rows
            foreach( $rows as $row ) {

      			$repeater = $row->name;
               $version = $row->pluginVersion;

      		   // Update `pluginVersion` value in database
      		   // ** Note: This might not be necessary since the update in v2.5 **
      		   $data_update = array('pluginVersion' => ALM_UNLIMITED_VERSION);
               $data_where = array('name' => $repeater);
               $wpdb->update($table_name , $data_update, $data_where);

               //Write to repeater file
      		   $data = $wpdb->get_var("SELECT repeaterDefault FROM $table_name WHERE name = '$repeater'");

      			// Current Repeater
               $file = $base_dir .'/'.$repeater .'.php';

					// Wrap is file_exists to avoid updating without cause
      			if( !file_exists($file) ){
         			try {
                     $o = fopen($file, 'w+'); //Open file
                     if ( !$o ) {
                       throw new Exception(__('[Ajax Load More] Unable to open Custom Repeater template - '.$f.'', 'ajax-load-more-repeaters-v2'));
                     }
                     $w = fwrite($o, $data); //Save the file
                     if ( !$w ) {
                       throw new Exception(__('[Ajax Load More] Unable to save Custom Repeater template - '.$f.'', 'ajax-load-more-repeaters-v2'));
                     }
                     fclose($o); //now close it
                  } catch ( Exception $e ) {
                     // Display error message in console.
                     if(!isset($options['_alm_error_notices']) || $options['_alm_error_notices'] == '1'){
                        echo '<script>console.log("' .$e->getMessage(). '");</script>';
                     }
                  }
               }
            }
         }
		}



   	/*
   	*  get_repeater_add_ons
   	*  List our repeaters for selection on shortcode builder page
   	*
   	*  @since 2.0
   	*/

   	function alm_get_unlimited_add_ons(){
   			//Repeater loop
   		   global $wpdb;
         	$table_name = $wpdb->prefix . "alm_unlimited";
         	$rows = $wpdb->get_results("SELECT * FROM $table_name"); // Get all data
            $i = 0;
      		foreach( $rows as $repeater )  {
   			   // Get repeater alias, if avaialble
   			   $i++;
   			   $name = $repeater->name;
            	$repeater_alias = $repeater->alias;
            	if(empty($repeater_alias)){
            	   echo '<option name="'.$name.'" id="chk-'.$name.'" value="'.$name.'">Template #'. $i .'</option>';
            	}else{
            	   echo '<option name="'.$name.'" id="chk-'.$name.'" value="'.$name.'">'.$repeater_alias.'</option>';
            	}
   			}
   	}



   	/*
   	*  alm_is_unlimited_installed
   	*  an empty function to determine if custom repeater is true.
   	*
   	*  @since 2.0
   	*/

   	function alm_is_unlimited_installed(){
   	   // Empty return
   	   // Function called on /ajax-load-more/admin/admin.php
   	}



   	/*
   	*  alm_unlimited_add_ons
   	*  Our front end for the repeaters
   	*
   	*  @since 2.0
   	*/

   	function alm_unlimited_add_ons(){

			// Confirm table exists
	      alm_unlimited_check_table();

   		//Repeater loop
   		global $wpdb;
         $table_name = $wpdb->prefix . "alm_unlimited";
         $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name"); // Count rows
         $rows = $wpdb->get_results("SELECT * FROM $table_name"); // Get all data
         ?>
         <div id="unlmited-container">
         <?php
         if($rowcount > 0){
            $i = 0;

            $base_dir = ALMUnlimitedRepeaters::alm_unlimited_get_repeater_path();
            ALMUnlimitedRepeaters::alm_unlimited_mkdir($base_dir);

      		foreach( $rows as $repeater ) {
      		   $i++;
         		$repeater_file = $repeater->name;
         	   $repeater_name = 'Template #'.$i;
            	$repeater_alias = $repeater->alias;

            	if(!empty($repeater_alias)){ // Set alias
               	$heading = $repeater_alias;
            	}else{
               	$heading = $repeater_name;
            	}
      		?>
      		<div class="row template unlimited">
      		   <div>
         			<h3 class="heading" data-default="<?php echo $repeater_name; ?>"><?php echo $heading; ?></h3>
         			<div class="expand-wrap">
         				<div class="wrap repeater-wrap" data-name="<?php echo $repeater_file; ?>" data-type="unlimited">

            				<div class="alm-row alm-row--margin-btm">
            				   <div class="column column--half">
               			      <label class="template-title" for="alias-<?php echo $repeater_file; ?>">
               			         <?php _e('Template Alias', 'ajax-load-more-repeaters-v2'); ?>:
               			      </label>
               			      <?php
                  			      $alias = (empty($repeater_alias)) ? $repeater_name : $repeater_alias;
                  			      echo '<input type="text" id="alias-'.$repeater_file.'" class="_alm_repeater_alias" value="'.$alias.'" maxlength="55">';
               			      ?>
            				   </div>
            				   <div class="column column--half">
            			         <label class="template-title" for="id-<?php echo $repeater_file; ?>">
            			            <?php _e('Template ID', 'ajax-load-more-repeaters-v2'); ?>:
                              </label>
                              <input type="text" class="disabled-input" id="id-<?php echo $repeater_file; ?>" value="<?php echo $repeater_file; ?>" readonly="readonly">
            				   </div>
            				</div>

         				   <div class="alm-row alm-row--margin-btm">
            				   <div class="column column--two-third">
                              <label class="template-title" for="template-<?php echo $repeater_file; ?>">
                                 <?php _e('Enter the HTML and PHP code for this template', 'ajax-load-more-repeaters-v2'); ?>:
                              </label>
            				   </div>
            				   <div class="column column--one-third">
               				   <?php do_action('alm_get_layouts'); // Layouts - Template Selection ?>
            				   </div>
         				   </div>

            				<?php
									$filename = $base_dir. '/'. $repeater_file. '.php';
               				$handle = fopen ($filename, "r");
               				$content = (filesize ($filename) != 0) ? fread($handle, filesize ($filename)) : '';
               				fclose ($handle);
            				?>
            				<div class="alm-row alm-row--margin-btm">
               				<div class="column textarea-wrap">
               					<textarea rows="10" id="<?php echo $repeater_file; ?>" class="_alm_repeater"><?php if($content) echo $content; ?></textarea>
               					<script>
                                 var editor_<?php echo $repeater_file; ?> = CodeMirror.fromTextArea(document.getElementById("<?php echo $repeater_file; ?>"), {
                                   mode:  "application/x-httpd-php",
                                   lineNumbers: true,
                                   lineWrapping: true,
                                   indentUnit: 0,
                                   matchBrackets: true,
                                   viewportMargin: Infinity,
                                   extraKeys: {"Ctrl-Space": "autocomplete"},
                                 });
                               </script>
               				</div>
            				</div>

            				<div class="alm-row">
   	      			      <div class="column">
                  				<input type="submit" value="<?php _e('Save Template', 'ajax-load-more-repeaters-v2'); ?>" class="button button-primary save-repeater" data-editor-id="<?php echo $repeater_file; ?>">
                  				<div class="saved-response">&nbsp;</div>
                  				<p class="alm-delete"><a href="javascript:void(0);"><?php _e('Delete', 'ajax-load-more-repeaters-v2'); ?></a></p>
         		            	<?php
            		            	$repeater_options = array(
               		            	'path' => $filename,
               		            	'name' => $repeater_file,
               		            	'type' => 'standard'
            		            	);
            		            	include( ALM_PATH . 'admin/includes/components/repeater-options.php');
            		            	unset($repeater_options);
            		            ?>
   	      			      </div>
            				</div>

         				</div>
         			</div>
         			<div class="clear"></div>
      		   </div>
      		</div>
      		<?php
	      		}
      		}
   		?>

         </div>
   		<p class="alm-add-template" id="alm-add-template" style="margin-top:30px"><a href="javascript:void(0);"><i class="fa fa-plus-square"></i> <?php _e('Add New Template', 'ajax-load-more-repeaters-v2'); ?></a></p>

   		<script>
   	   jQuery(document).ready(function($) {

   	      // Check alias'
   		   $(document).on('keyup', '._alm_repeater_alias', function(){
   		      var el = $(this),
   		          heading = el.parent().parent().parent().parent().find('h3.heading');
   		      var val = el.val(),
   		          defaultVal = heading.data('default');
   		      if(val === ''){
      		      heading.text(defaultVal);
   		      }else{
      		      heading.text(val);
   		      }
   		   });

   		   // ADD template
   		   $('#alm-add-template a').on('click', function(){
   		      var el = $(this);
   		      if(!el.hasClass('active')){
   		         el.addClass('active');

   		         // Create div
   		         var container = $('#unlmited-container'),
      				    div = $('<div class="row unlimited new" />');
                  div.appendTo(container);
                  div.fadeIn(250);

                  // Run ajax
         		   $.ajax({
            			type: 'POST',
            			url: alm_admin_localize.ajax_admin_url,
            			data: {
            				action: 'alm_unlimited_create',
            				nonce: alm_admin_localize.alm_admin_nonce,
            			},
            			dataType: "JSON",
            			success: function(data) {
                        div.load("<?php echo ALM_UNLIMITED_URL; ?>/includes/template.php", {
                           id: data.id,
                           alias: data.alias,
                           defaultVal: data.defaultVal
                        }, function(){ // .load() complete
                           div.addClass('done');
                           $('.unlimited-wrap', div).slideDown(350, 'alm_unlimited_ease', function(){
                              div.removeClass('new');
                              div.removeClass('done');
                              el.removeClass('active');
                              $('.CodeMirror').each(function(i, el){
                                  el.CodeMirror.refresh();
                              });
                           });
                        });

            			},
            			error: function(xhr, status, error) {
            				responseText.html('<?php _e('<p>Error - Something went wrong and the template could not be created.</p>', 'ajax-load-more-repeaters-v2'); ?>');
                        div.remove();
            				el.removeClass('active');
            			}
            		});
         		}
   		   });


            // DELETE template
   		   $(document).on('click', '.alm-delete', function(){

   		      var r = confirm("<?php _e('Are you sure you want to delete this template?', 'ajax-load-more-repeaters-v2'); ?>");
               if (r == true && !$(this).hasClass('deleting')) {
      		      var el = $(this),
      		          container = el.closest('.repeater-wrap'),
      		          item = container.parent().parent().parent('.row.unlimited'),
      					 repeater = container.data('name');

            		el.addClass('deleting');
            		item.addClass('deleting');
         		   $.ajax({
            			type: 'POST',
            			url: alm_admin_localize.ajax_admin_url,
            			data: {
            				action: 'alm_unlimited_delete',
            				repeater: repeater,
            				nonce: alm_admin_localize.alm_admin_nonce
            			},
            			dataType: "html",
            			success: function(data) {
            				setTimeout(function() {
            				   item.addClass('deleted');
                           item.slideUp(350, 'alm_unlimited_ease', function(){
                              item.remove();
                           })
                        }, 250);
            				console.log('Template Deleted');
            			},
            			error: function(xhr, status, error) {
            			   item.removeClass('deleting');
            			   el.removeClass('deleting');
            				responseText.html('<?php _e('<p>Error - Something went wrong and the template could not be deleted.</p>', 'ajax-load-more-repeaters-v2'); ?>');
            			}
            		});
         		 }
   		   });
   		   $.easing.alm_unlimited_ease = function (x, t, b, c, d) {
               if ((t /= d / 2) < 1) return c / 2 * t * t + b;
               return -c / 2 * ((--t) * (t - 2) - 1) + b;
            };

   		 });

   		</script>
   		<?php
   	 }



       /*
       *  alm_unlimited_create
       *  Create new repeater template
       *
       *  @since 2.0
       *  @updated 2.5
       */

       function alm_unlimited_create(){

		 	// Confirm table exists
	      alm_unlimited_check_table();

         if (current_user_can( 'edit_theme_options' )){

            global $wpdb;
            $table_name = $wpdb->prefix . "alm_unlimited";
            $blog_id = $wpdb->blogid;

            $nonce = $_POST["nonce"];
            // Check our nonce, if they don't match then bounce!
            if (! wp_verify_nonce( $nonce, 'alm_repeater_nonce' ))
               die(__('Error - unable to verify nonce, please try again.', 'ajax-load-more-repeaters-v2'));

            $count = floatval($wpdb->get_var( "SELECT COUNT(*) FROM $table_name" ));
            $count = $count+1;

            $defaultVal = '<?php // '.__('Enter your template code here', 'ajax-load-more-repeaters-v2').'.  ?>';


            // Insert into DB
            $wpdb->insert($table_name , array(
               'name' => 'temp',
               'repeaterDefault' => $defaultVal,
               'alias' => '',
               'pluginVersion' => ALM_UNLIMITED_VERSION
            ));


            $id = $wpdb->insert_id; // Get new primary key value (id)
            $data_new = array('name' => 'template_'.$id);
            $data_previous = array('name' => 'temp');
            $wpdb->update($table_name , $data_new, $data_previous);


            // Set new template name
            $template = 'template_'.$id;

            $base_dir = ALMUnlimitedRepeaters::alm_unlimited_get_repeater_path();
				ALMUnlimitedRepeaters::alm_unlimited_mkdir($base_dir);

   			$f = $base_dir. '/'.$template .'.php';

            $file = fopen( $f, "w" ) or die ("Error opening file"); // It doesn't exist, so create it.
            $w = fwrite($file, $defaultVal) or die("Error writing file");

            $return = array();
            $return["id"] = $template;
            $return["alias"] = __('Template #', 'ajax-load-more-repeaters-v2') . '' .$count;
            $return["defaultVal"] = $defaultVal;

            wp_send_json($return);

      	}else {
      		echo __('You don\'t belong here.', 'ajax-load-more-repeaters-v2');
      	}

       }


       /*
       *  alm_unlimited_delete
       *  Delete repeater template
       *
       *  @since 2.0
       *  @updated 2.5
       */

       function alm_unlimited_delete(){

		 	// Confirm table exists
	      alm_unlimited_check_table();

         if (current_user_can( 'edit_theme_options' )){

   	      global $wpdb;
            $table_name = $wpdb->prefix . "alm_unlimited";

            $nonce = $_POST["nonce"];
            $template = Trim(stripslashes($_POST["repeater"])); // Repeater name for deletion

            // Nonce verification
            if (! wp_verify_nonce( $nonce, 'alm_repeater_nonce' )){
               die(__('Error - unable to verify nonce, please try again.', 'ajax-load-more-repeaters-v2'));
            }

            $wpdb->delete($table_name, array( 'name' => $template )); // delete from db

            // Get base directory
				$base_dir = ALMUnlimitedRepeaters::alm_unlimited_get_repeater_path();

            // Delete file from server
   			$file_to_delete = $base_dir .'/'.$template .'.php';

            if (file_exists($file_to_delete)) {
               unlink($file_to_delete);
            }

            // See if repeater exists again to be sure it was removed
            if (file_exists($file_to_delete)) {
                echo __('Template could not be deleted.', 'ajax-load-more-repeaters-v2');
            } else {
                echo __('Template deleted successfully.', 'ajax-load-more-repeaters-v2');
            }

            die();

      	} else {

      		echo __('You don\'t belong here.', 'ajax-load-more-repeaters-v2');
      		die();

      	}
      }



      /*
   	*  alm_unlimited_settings
   	*  Create the Custom Repeaters settings panel.
   	*
   	*  @since 2.4
   	*/

   	function alm_unlimited_settings(){
      	register_setting(
      		'alm_unlimited_license',
      		'alm_unlimited_license_key',
      		'alm_unlimited_sanitize_license'
      	);
      }

   }


   /*
   *  alm_unlimited_sanitize_license
   *  Sanitize our license activation
   *
   *  @since 2.4
   */

   function alm_unlimited_sanitize_license( $new ) {
   	$old = get_option( 'alm_unlimited_license_key' );
   	if( $old && $old != $new ) {
   		delete_option( 'alm_unlimited_license_status' ); // new license has been entered, so must reactivate
   	}
   	return $new;
   }



   /*
   *  ALMUnlimitedRepeaters
   *  The main function responsible for returning Ajax Load More Unlimited Repeaters.
   *
   *  @since 2.0
   */

   function ALMUnlimitedRepeaters(){
   	global $alm_unlimited_repeaters;
   	if( !isset($alm_unlimited_repeaters) ){
   		$alm_unlimited_repeaters = new ALMUnlimitedRepeaters();
   	}
   	return $alm_unlimited_repeaters;
   }
   ALMUnlimitedRepeaters(); // initialize

endif; // class_exists check



/* Software Licensing */
function alm_unlimited_plugin_updater() {
	if(!has_action('alm_pro_installed')){ // Don't check for updates if Pro is activated
		$license_key = trim( get_option( 'alm_unlimited_license_key' ) ); // retrieve our license key from the DB
		$edd_updater = new EDD_SL_Plugin_Updater( ALM_STORE_URL, __FILE__, array(
				'version' 	=> ALM_UNLIMITED_VERSION,
				'license' 	=> $license_key,
				'item_id'   => ALM_UNLIMITED_ITEM_NAME,
				'author' 	=> 'Darren Cooney'
			)
		);
	}
}
add_action( 'admin_init', 'alm_unlimited_plugin_updater', 0 );
/* End Software Licensing */
