jQuery(document).ready(function(){
/*
 *		Flips the upload buttons between form element and SWFupload
 */
	function wpsc_upload_switcher(target_state) {
	  switch(target_state) {
		case 'flash':
		var upload_flash = '1';
		break;
		
		case 'browser':
		var upload_flash = '0';
		break;
	  }

	wpsc_upload_flip(upload_flash);
	
	jQuery.post(ajax_url,
		{action: 'wpsc_upload_method'
		,upload_via_flash: upload_flash
		,cookie: encodeURIComponent(document.cookie)
		,"_wpnonce" : wpnonce
		}
	);
	}

	jQuery('.wpsc_upload_switcher').click(function(){
	/* Extracts the contents of the link and uses that to make the switch */
		var method=jQuery(this).text();
		method=method.substring(0,method.indexOf(' ')).toLowerCase();
		wpsc_upload_switcher(method);
	});
});

var upload_flash;
function wpsc_upload_flip(upload_method) {
	switch(upload_method) {
		case '1':
		jQuery("table.use-browser-uploader").css("display","none");
		jQuery("table.use-flash-uploader").css("display","block");
		break;
		
		case '0':
		jQuery("table.use-flash-uploader").css("display","none");
		jQuery("table.use-browser-uploader").css("display","block");
		break;
	}
}

/*
 *	Manage Downloadable Product Files
 */

function attach_actions() {
	jQuery('.select_product_delete').click(prod_delete);
}

function prod_delete(){

	if(areYouSure('Are you sure you want to delete this product?')) {
		var which_one = jQuery(this).attr('id').replace(/select_product_delete_/,'');
		jQuery.post(ajax_url, 
		{action:"delete_product_file"
		, 'auth_cookie': encodeURIComponent(document.cookie)
		, 'fileid': which_one
		, 'prodid' : '0'
		,"_wpnonce" : wpnonce
		},
		function(results) {
			jQuery('#wpsc_dl_product_list').html(results);
			attach_actions();
		});
	}
	return false;
}

jQuery(document).ready(function(){
	var toss = attach_actions();

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


// Used on admin/display-items.php - toggles the publish status of a product (dims background for unpublished)
// click logic from http://xplus3.net/2008/10/16/jquery-and-ajax-in-wordpress-plugins-administration-pages/
	jQuery("span.publish_toggle a").click(function() {
		var that = this;
		var theRow = jQuery(this).parents('tr:first');
		jQuery.post(jQuery(this).attr("href"), {
			   action: "wpsc_toggle_publish"
			, 'cookie': encodeURIComponent(document.cookie)
			, 'productid': jQuery(theRow).attr('id')
			}
			, function(newstatus){
				if (newstatus == 'true') {
					jQuery(that).text('Unpublish');
					jQuery(theRow).removeClass('wpsc_not_published').addClass('wpsc_published')
				} else {
					jQuery(that).text('Publish');
					jQuery(theRow).removeClass('wpsc_published').addClass('wpsc_not_published');
				}	
			}
		);
		return false; // The click never happened - defeat the a tag
	});
});

