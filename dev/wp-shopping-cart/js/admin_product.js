/*
 *		Flips the upload buttons between form element and SWFupload
 */
function wpsc_upload_switcher(target_state) {
  switch(target_state) {
    case 'flash':
    jQuery("table.use-browser-uploader").css("display","none");
    jQuery("table.use-flash-uploader").css("display","block");
//    ajax.post("index.php",noresults,"admin=true&ajax=true&save_product_upload_state=true&product_upload_state=1");
	jQuery.post(siteurl+'wp-admin/admin-ajax.php',
		{action: 'wpsc_upload_method'
		,upload_via_flash: '1'
		,cookie: ecnodeURIComponent(document.cookie),
		,"_wpnonce" : wpnonce
		}
	);
//    ajax.post(base_url,noresults,"action=wpsc_upload_method&upload_via_flash=1");
    break;
    
    case 'browser':
    jQuery("table.use-flash-uploader").css("display","none");
    jQuery("table.use-browser-uploader").css("display","block");
	jQuery.post(siteurl+'wp-admin/admin-ajax.php',
		{action: 'wpsc_upload_method'
		,upload_via_flash: '0'
		,cookie: ecnodeURIComponent(document.cookie),
		,"_wpnonce" : wpnonce
		}
	);
    break;
  }
}
jQuery(document).ready(function(){
/*
 *	Manage Downloadable Product Files
 */
	jQuery('.select_product_delete').click(function(){
		var dlfile = jQuery(this).parents('tr:first').attr('id');
		alert("Delete Me "+dlfile);
// 		if(areYouSure('Are you sure you want to delete this product?')){
// 			var which_one = jQuery(this).id().remove('select_product_delete_');
// 		jQuery.post(siteurl+'wp-admin/admin-ajax.php', 
// 		{action:"delete_file"
// 		, 'cookie': encodeURIComponent(document.cookie)
// 		, 'fileid': dlfile
// 		};
// 			productUploadSuccess();
	});
	
	jQuery('.select_product_preview').click(function(){
		var dlfile = jQuery(this).parents('tr:first').attr('id');
		alert("Preview Me "+dlfile);
// 		jQuery.post(siteurl+'wp-admin/admin-ajax.php', 
// 		{action:"preview_file"
// 		, 'cookie': encodeURIComponent(document.cookie)
// 		, 'fileid': dlfile
// 		};
// 			productUploadSuccess();
	});

});

jQuery(document).ready(function(){
/*
 *	Upload Product Images
 */
  	jQuery("#add-product-image").click(function(){
      swfu.selectFiles();
  	});

 	filesizeLimit = 5120000;
	
// 	alert('test 1');
	if (typeof SWFUpload != "undefined") {
//     alert('test 2');
	  var swfu = new SWFUpload({
      flash_url : WPSC_URL+'/js/swfupload.swf',
      upload_url: base_url+'/?action=wpsc_add_image',
      button_placeholder_id : "spanButtonPlaceholder",
      button_width: 103,
      button_height: 24,
      button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
      button_cursor: SWFUpload.CURSOR.HAND,
      post_params: {"prodid" : 0},
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
    });
	}

/**
 * SWFUpload Image Uploading events
 **/

	function imageFileQueued (file) {
	
	}
	
	function imageFileQueueError (file, error, message) {
		if (error == SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("You selected too many files to upload at one time. " + (message === 0 ? "You have reached the upload limit." : "You may upload " + (message > 1 ? "up to " + message + " files." : "only one file.")));
			return;
		}
	
	}
	
	function imageFileDialogComplete (selected, queued) {
		try {
			this.startUpload();
		} catch (ex) {
			this.debug(ex);
		}
	}
	
	function startImageUpload (file) {
	  jQuery("span.swfupload_loadingindicator").css('visibility', 'visible');
		var cell = jQuery('<li></li>').appendTo(jQuery('#gallery_list'));
		var sorting = jQuery('<input type="hidden" name="images[]" value="" />').appendTo(cell);
		var progress = jQuery('<div class="progress"></div>').appendTo(cell);
		var bar = jQuery('<div class="bar"></div>').appendTo(progress);
		var art = jQuery('<div class="gloss"></div>').appendTo(progress);
		this.targetHolder = cell;
		this.progressBar = bar;
		this.sorting = sorting;
		return true;
	}
	
	function imageUploadProgress (file, loaded, total) {
		var progress = Math.ceil((loaded/total)*76);
		jQuery(this.progressBar).animate({'width':progress+'px'},100);
	}
	
	function imageUploadError (file, error, message) {
		console.log(error+": "+message);
	}
	
	function imageUploadSuccess (file, results) {
		//Don't delete, initiate id is neccesary.
		var id = null;
		var pid = null;
		
		jQuery("span.swfupload_loadingindicator").css('visibility', 'hidden');
			eval(results);
	// 		jQuery(this).css('border', '1px solid red');
		  if(pid >= 1) {
			context = jQuery("div#productform");
		  } else {
				context = jQuery("div#additem");
		  }
		
		if (id == null ) {
			if(replacement_src != null) {
				jQuery("li.first div.previewimage a.thickbox", context).attr('href', replacement_src);
				jQuery("li.first div.previewimage a.thickbox img.previewimage", context).attr('src', replacement_src);
			} else {
			if (jQuery('#gold_present', context).val() != '1') {
			  jQuery('#add-product-image', context).remove();
			}
			jQuery(this.sorting).attr({'value':src});
			var img = jQuery('<div class="previewimage" id="'+id+'"><a href="'+WPSC_IMAGE_URL+src+'" rel="product_extra_image_'+id+'" class="thickbox"><img src="'+WPSC_IMAGE_URL+src+'" width="60" height="60" class="previewimage" /></a></div>').appendTo(this.targetHolder).hide();
			set = jQuery("#gallery_list", context).sortable('toArray');
	
			jQuery('#gallery_image_0', context).append("<a class='editButton'>Edit   <img src='"+WPSC_URL+"/images/pencil.png'/></a>");
			jQuery('#gallery_image_0', context).parent('li').addClass('first');
			jQuery('#gallery_image_0', context).parent('li').attr('id', 0);
			jQuery('#gallery_image_0 img.deleteButton', context).remove();
			enablebuttons();
			}
		} else {
			//jQuery(this.targetHolder).attr({'id':'image-'+src});
			jQuery(this.targetHolder).attr({'id':id});
			div_id = 'gallery_image_'+id;
			jQuery(this.targetHolder).html('');
			var img = jQuery('<div class="previewimage" id="'+div_id+'"><input type="hidden" name="images[]" value="'+src+'"><a href="'+WPSC_IMAGE_URL+src+'" rel="product_extra_image_'+id+'" class="thickbox"><img src="'+WPSC_IMAGE_URL+src+'" width="60" height="60" class="previewimage" /></a></div>').appendTo(this.targetHolder).hide();
			
		jQuery('#gallery_image_0', context).append("<a class='editButton'>Edit   <img src='"+WPSC_URL+"/images/pencil.png'/></a>");
		jQuery('#gallery_image_0', context).parent('li').addClass('first');
		jQuery('#gallery_image_0', context).parent('li').attr('id', 0);
		jQuery('#gallery_image_0 img.deleteButton', context).remove();
		
		
		if (jQuery('#gallery_list li', context).size() > 1) {
		  jQuery('#gallery_list', context).sortable('refresh');
		} else {
		  jQuery('#gallery_list', context).sortable();
			}
		set = jQuery("#gallery_list", context).sortable('toArray');
		order = set.join(',');
		prodid = jQuery('#prodid', context).val();
		if(prodid == null) {
		  prodid = 0;
		}
		
		  function imageorderresults(results) {
			eval(results);
			jQuery('#gallery_image_'+ser).append(output);
			enablebuttons();
		  }
		
		ajax.post("index.php",imageorderresults,"admin=true&ajax=true&prodid="+prodid+"&imageorder=true&order="+order+"");
		
		
		enablebuttons();
	
		}
		jQuery(this.progressBar).animate({'width':'76px'},250,function () {
			jQuery(this).parent().fadeOut(500,function() {
				jQuery(this).remove();
				jQuery(img).fadeIn('500');
				jQuery(img).append('<img class="deleteButton" src="'+WPSC_URL+'/images/cross.png" alt="-" style="display: none;"/>');
				enablebuttons()
				//enableDeleteButton(deleteButton);
			});
		});
	}
	
	function imageUploadComplete (file) {
		if (jQuery('#gallery_list li').size() > 1)
			jQuery('#gallery_list').sortable('refresh');
		else
			jQuery('#gallery_list').sortable();
	}
	
	function imageQueueComplete (uploads) {
	
	}

	function enablebuttons(){
		jQuery("img.deleteButton").click(
			function(){
				var r=confirm("Please confirm deletion");
				if (r==true) {
					img_id = jQuery(this).parent().parent('li').attr('id');
					jQuery(this).parent().parent('li').remove();
					ajax.post("index.php",noresults,"admin=true&ajax=true&del_img=true&del_img_id="+img_id);
				}
			 }
		);
		
		jQuery("a.delete_primary_image").click(
			function(){
				var r=confirm("Please confirm deletion");
				if (r==true) {
					img_id = jQuery(this).parents('li.first').attr('id');
					//ajax.post("index.php",noresults,"ajax=true&del_img=true&del_img_id="+img_id);
					jQuery(this).parents('li.first').remove();
					
					
					
					set = jQuery("#gallery_list").sortable('toArray');
					jQuery('#gallery_image_'+set[0]).children('img.deleteButton').remove();
					jQuery('#gallery_image_'+set[0]).append("<a class='editButton'>Edit   <img src='"+WPSC_URL+"/images/pencil.png'/></a>");
					jQuery('#gallery_image_'+set[0]).parent('li').addClass('first');
					jQuery('#gallery_image_'+set[0]).parent('li').attr('id', 0);
					for(i=1;i<set.length;i++) {
						jQuery('#gallery_image_'+set[i]).children('a.editButton').remove();
						jQuery('#gallery_image_'+set[i]).append("<img alt='-' class='deleteButton' src='"+WPSC_URL+"/images/cross.png'/>");
						
			  //alert(jQuery('#gallery_image_'+set[i]).parent('li').attr('id'));
						  //alert(element_id);
						if(element_id == 0) {
				jQuery('#gallery_image_'+set[i]).parent('li').attr('id', img_id);
						}
					}
					order = set.join(',');
					prodid = jQuery('#prodid').val();
					ajax.post("index.php",imageorderresults,"admin=true&ajax=true&prodid="+prodid+"&imageorder=true&order="+order+"&delete_primary=true");
					
					jQuery(this).parents('li.first').attr('id', '0');
				}
		  return false;
			 }
		);
	
		jQuery("div.previewimage").hover(
			function () {
				jQuery(this).children('img.deleteButton').show();
				if(jQuery('#image_settings_box').css('display')!='block')
					jQuery(this).children('a.editButton').show();
				},
			 function () {
				 jQuery(this).children('img.deleteButton').hide();
				 jQuery(this).children('a.editButton').hide();
			}
		);
		
		jQuery("a.editButton").click(
			function(){
				jQuery(this).hide();
				jQuery('#image_settings_box').show('fast');
			}
		);
		
		jQuery("a.closeimagesettings").click(
			function (e) {
				jQuery("div#image_settings_box").hide();
			}
		);
		
	  function imageorderresults(results) {
		eval(results);
		jQuery('#gallery_image_'+ser).append(output);
		enablebuttons();
	  }
	}
});


jQuery(document).ready(function(){
/*
 *	Upload Downloadable Products
 */
//window.onload = function () {

// When Add Downloadable Product Button is clicked - launch SWFupload
	jQuery("#add-product-files").click(function(){
		swfdl.selectFiles();
	});


//SWFUpload - for product files
	filesizeLimit = 5120000;
	prodid = jQuery('#prodid').val();
	if( typeof(prodid) == "undefined") {
		prodid = 0;
	}
	
	if (typeof SWFUpload != "undefined") {
		var swfdl = new SWFUpload({
		  flash_url : WPSC_URL+'/js/swfupload.swf',
		  button_placeholder_id : "spanButtonPlaceholderProduct",
		  swfupload_element_id: "flashui2",
		  degraded_element_id: "degradedui2",
		  button_width: 103,
		  button_height: 24,
		  button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		  button_cursor: SWFUpload.CURSOR.HAND,
		  upload_url: WPSC_URL+'/wpsc_upload_files.php',
		  post_params: {
				action  : 'wpsc_add_product'
				,"prodid" : prodid
				,"auth_cookie" : auth_cookie
				,"_wpnonce" : wpnonce
				},
		  file_queue_limit : 1,
		  file_size_limit : filesizeLimit+'b',
		  file_types : "*.mp3;*.avi;*.pdf;*.*",
		  file_types_description : "Files to be delivered to customer",
		  file_post_name: "file",
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
		});
	
 	}
	function productFileQueued (file) {
	
	}
	
	function productFileQueueError (file, error, message) {
		if (error == SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("You selected too many files to upload at one time. " + (message === 0 ? "You have reached the upload limit." : "You may upload " + (message > 1 ? "up to " + message + " files." : "only one file.")));
			return;
		}	
	}
	
	function productFileDialogComplete (selected, queued) {
		try {
			this.startUpload();
		} catch (ex) {
			this.debug(ex);
		}
	}

	function startProductUpload (file) {
		return true;
	}
	
	function productUploadProgress (file, loaded, total) {
		var progress = Math.ceil((loaded/total)*76);
		jQuery(this.progressBar).animate({'width':progress+'px'},100);
	}
	
	function productUploadError (file, error, message) {
		console.log(error+": "+message);
	}
	
	function productUploadSuccess (file, results) {
		jQuery('#flash-product-uploader-status').html('Product Added').addClass('updated');

	}

	function productUploadComplete (file) {
	}
	
	function productQueueComplete (uploads) {
	}
//}
});