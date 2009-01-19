<?php
if( $_REQUEST['page'] == WPSC_DIR_NAME.'/display-items.php' ) {   // Only load on the Display-Items page
?>
window.onload = function() {
// Create swf object

	filesizeLimit = 5120000;
	
	swfu_settings = {
	  flash_url : WPSC_URL+'/js/swfupload.swf',
	  upload_url : WPSC_URL+'/wpsc_upload_files.php',
	  file_post_name : "Filedata",
	  button_placeholder_id : "spanButtonPlaceholder",
	  button_width: 103,
	  button_height: 24,
	  post_params: {
	  	'action' : 'wpsc_add_image'
	  	,'auth_cookie' : '<?php if ( is_ssl() ) echo $_COOKIE[SECURE_AUTH_COOKIE]; else echo $_COOKIE[AUTH_COOKIE]; ?>'
	  	,'prodid' : '<?php if( isset($_REQUEST['prodid']) ) { echo $_REQUEST['prodid']; } else { echo '0';} ?>'
	  	,'_wpnonce' : '<?php echo wp_create_nonce('wp-shopping-cart'); ?>'
	  },
	  button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
	  button_cursor: SWFUpload.CURSOR.HAND,
	  file_queue_limit : 1,
	  file_size_limit : filesizeLimit+'b',
	  file_types : "*.jpg;*.jpeg;*.png;*.gif",
	  file_types_description : "Web-compatible Image Files",
	  file_upload_limit : filesizeLimit,
		custom_settings : {
			targetHolder : false,
			progressBar : false,
			sorting : false
	  },
	  debug: false,
	  
	  file_queued_handler : imageFileQueued,
	  file_queue_error_handler : imageFileQueueError,
	  file_dialog_complete_handler : imageFileDialogComplete,
	  upload_start_handler : startImageUpload,
	  upload_progress_handler : imageUploadProgress,
	  upload_error_handler : imageUploadError,
	  upload_success_handler : imageUploadSuccess,
	  upload_complete_handler : imageUploadComplete,
	  queue_complete_handler : imageQueueComplete
	};
	
	swfdl_settings = {
	  flash_url : WPSC_URL+'/js/swfupload.swf',
	  upload_url : WPSC_URL+'/wpsc_upload_files.php',
	  file_post_name : "file",
	  button_placeholder_id : "spanButtonPlaceholderProduct",
	  button_width: 103,
	  button_height: 24,
	  post_params: {
	  	'action' : 'wpsc_add_product'
	  	,'auth_cookie' : '<?php if ( is_ssl() ) echo $_COOKIE[SECURE_AUTH_COOKIE]; else echo $_COOKIE[AUTH_COOKIE]; ?>'
	  	,'prodid' : '<?php if( isset($_REQUEST['prodid']) ) { echo $_REQUEST['prodid']; } else { echo '0';} ?>'
	  	,'_wpnonce' : '<?php echo wp_create_nonce('wp-shopping-cart'); ?>'
	  	,'subaction' : 'add'
	  },
	  button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
	  button_cursor: SWFUpload.CURSOR.HAND,
	  file_queue_limit : 1,
	  file_size_limit : filesizeLimit+'b',
	  file_types : "*.mp3;*.avi;*.pdf;*.*",
	  file_types_description : "Web-compatible Image Files",
	  file_upload_limit : filesizeLimit,
		custom_settings : {
			targetHolder : false,
			progressBar : false,
			sorting : false
	  },
	  debug: false,
	  
	  file_queued_handler : productFileQueued,
	  file_queue_error_handler : productFileQueueError,
	  file_dialog_complete_handler : productFileDialogComplete,
	  upload_start_handler : startProductUpload,
	  upload_progress_handler : productUploadProgress,
	  upload_error_handler : productUploadError,
	  upload_success_handler : productUploadSuccess,
	  upload_complete_handler : productUploadComplete,
	  queue_complete_handler : productQueueComplete
	};
	
	jQuery(document).ready(function() {
		jQuery("#add-product-image").click(function(){
			swfu.selectFile();
		});

		jQuery("#add-product-files").click(function(){
			swfdl.selectFile();
		});
	});
	swfu = new SWFUpload(swfu_settings);
	swfdl = new SWFUpload(swfdl_settings);
};
?>
}