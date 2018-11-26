<?php
   $parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
   require_once( $parse_uri[0] . 'wp-load.php' );
   $alias = $_POST['alias'];
   $id = $_POST['id'];
   $defaultVal = nl2br($_POST['defaultVal']);
?>
<div class="unlimited-wrap">
   <h3 class="heading" data-default="<?php echo $alias; ?>"><?php echo $alias; ?></h3>
	<div class="expand-wrap">
		<div class="wrap repeater-wrap" data-name="<?php echo $id; ?>" data-type="unlimited">

		   <div class="alm-row alm-row--margin-btm">
   		   <div class="column column--half">
   		      <label class="template-title" for="alias-<?php echo $id; ?>">
   		         <?php _e('Template Alias', 'ajax-load-more-repeaters-v2'); ?>:
   		      </label>
   		      <?php echo '<input type="text" id="alias-'.$id.'" class="_alm_repeater_alias" value="'.$alias.'" maxlength="55">'; ?>
		      </div>
   		   <div class="column column--half">
   	         <label class="template-title" for="id-<?php echo $id; ?>">
   	            <?php _e('Template ID', 'ajax-load-more-repeaters-v2'); ?>:
               </label>
               <input type="text" class="disabled-input" id="id-<?php echo $id; ?>" value="<?php echo $id; ?>" readonly="readonly">
   		   </div>
		   </div>

		   <div class="alm-row alm-row--margin-btm">
   		   <div class="column column--two-third">
               <label class="template-title" for="template-<?php echo $id; ?>">
                  <?php _e('Enter the HTML and PHP code for this template', 'ajax-load-more-repeaters-v2'); ?>:
               </label>
   		   </div>
   		   <div class="column column--one-third">
      		   <?php include( ALM_PATH . 'admin/includes/components/layout-list.php'); ?>
   		   </div>
		   </div>

			<div class="alm-row alm-row--margin-btm">
   			<div class="column textarea-wrap">
   				<textarea rows="10" id="<?php echo $id; ?>" class="_alm_repeater"><?php echo $defaultVal; ?></textarea>
   				<script>
                  var editor_<?php echo $id; ?> = CodeMirror.fromTextArea(document.getElementById("<?php echo $id; ?>"), {
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
      			<input type="submit" value="<?php _e('Save Template', 'ajax-load-more-repeaters-v2'); ?>" class="button button-primary save-repeater" data-editor-id="<?php echo $id; ?>">
      			<div class="saved-response">&nbsp;</div>
      			<p class="alm-delete"><a href="javascript:void(0);"><?php _e('Delete', 'ajax-load-more-repeaters-v2'); ?></a></p>
   			</div>
			</div>

		</div>
	</div>
	<div class="clear"></div>
</div>
