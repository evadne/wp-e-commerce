<?php
//handles replacing the tags in the pages
  
function nzshpcrt_products_page($content = '') {
  global $wpsc_query;
  if(preg_match("/\[productspage\]/",$content)) {
    
    $theme_path = WPSC_FILE_PATH. '/themes/';
    if(!file_exists($theme_path.get_option('wpsc_selected_theme'))) {
			$theme_dir = 'default';
    } else {
			$theme_dir = get_option('wpsc_selected_theme');
    }
    
		$wpsc_query->get_products();
    $GLOBALS['nzshpcrt_activateshpcrt'] = true;
    ob_start();
    if(wpsc_is_single_product()) {
			include_once(WPSC_FILE_PATH . "/themes/$theme_dir/single_product.php");
    } else {
			include_once(WPSC_FILE_PATH . "/themes/$theme_dir/products_page.php");
    }
    $output = ob_get_contents();
    ob_end_clean();
    $output = str_replace('$','\$', $output);
    return preg_replace("/(<p>)*\[productspage\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}


function wpsc_post_title_seo($title) {
	global $wpdb, $page_id, $wp_query;
	if($wp_query->query_vars['product_name'] != '') {
		$product_id = $wpdb->get_var("SELECT `product_id` FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '".$wpdb->escape($wp_query->query_vars['product_name'])."' ) LIMIT 1");			
    $title = $wpdb->get_var("SELECT `name` FROM `".$wpdb->prefix."product_list` WHERE `id` IN('".(int)$product_id."') LIMIT 1");
	} else if(is_numeric($_GET['product_id'])) {
		$title=$wpdb->get_var("SELECT `name` FROM ".$wpdb->prefix."product_list WHERE id IN ('".(int)$_GET['product_id']."') LIMIT 1" );
	}
	return stripslashes($title);
}



function wpsc_also_bought($product_id) {
  /*
   * Displays products that were bought aling with the product defined by $product_id
   * most of it scarcely needs describing
   */
  global $wpdb;
  $siteurl = get_option('siteurl');
  
  if(get_option('wpsc_also_bought') == 0) {
    //returns nothing if this is off
    return '';
	}
  
  // to be made customiseable in a future release
  $also_bought_limit = 3;
  $element_widths = 96; 
  $image_display_height = 96; 
  $image_display_width = 96; 
  
  $also_bought = $wpdb->get_results("SELECT `".$wpdb->prefix."product_list`.* FROM `".$wpdb->prefix."also_bought_product`, `".$wpdb->prefix."product_list` WHERE `selected_product`='".$product_id."' AND `".$wpdb->prefix."also_bought_product`.`associated_product` = `".$wpdb->prefix."product_list`.`id` AND `".$wpdb->prefix."product_list`.`active` IN('1') ORDER BY `".$wpdb->prefix."also_bought_product`.`quantity` DESC LIMIT $also_bought_limit",ARRAY_A);
  if(count($also_bought) > 0) {
    $output = "<p class='wpsc_also_bought_header'>".TXT_WPSC_ALSO_BOUGHT."</p>";
    $output .= "<div class='wpsc_also_bought'>";
    foreach((array)$also_bought as $also_bought_data) {
      $output .= "<p class='wpsc_also_bought' style='width: ".$element_widths."px;'>";
      if(get_option('show_thumbnails') == 1) {
        if($also_bought_data['image'] !=null) {
          $image_size = @getimagesize(WPSC_THUMBNAIL_DIR.$also_bought_data['image']);
          $largest_dimension  = ($image_size[1] >= $image_size[0]) ? $image_size[1] : $image_size[0];
          $size_multiplier = ($image_display_height / $largest_dimension);
          // to only make images smaller, scaling up is ugly, also, if one is scaled, so must the other be scaled
          if(($image_size[0] >= $image_display_width) || ($image_size[1] >= $image_display_height)) {
            $resized_width  = $image_size[0]*$size_multiplier;
            $resized_height =$image_size[1]*$size_multiplier;
					} else {
            $resized_width  = $image_size[0];
            $resized_height =$image_size[1];
					}            
          $margin_top = floor((96 - $resized_height) / 2);
          $margin_top = 0;
          
          $image_link = WPSC_IMAGE_URL.$also_bought_data['image'];          
          if($also_bought_data['thumbnail_image'] != null) {
            $image_file_name = $also_bought_data['thumbnail_image'];
					} else {
            $image_file_name = $also_bought_data['image'];
					}           
          
          $output .= "<a href='".wpsc_product_url($also_bought_data['id'])."' class='preview_link'  rel='".str_replace(" ", "_",$also_bought_data['name'])."'>";          
          $image_url = "index.php?productid=".$also_bought_data['id']."&amp;thumbnail=true&amp;width=".$resized_width."&amp;height=".$resized_height."";        
          $output .= "<img src='$siteurl/$image_url' id='product_image_".$also_bought_data['id']."' class='product_image' style='margin-top: ".$margin_top."px'/>";
          $output .= "</a>";
				} else {
          if(get_option('product_image_width') != '') {
            $output .= "<img src='".WPSC_URL."/no-image-uploaded.gif' title='".$also_bought_data['name']."' alt='".$also_bought_data['name']."' width='$image_display_height' height='$image_display_height' id='product_image_".$also_bought_data['id']."' class='product_image' />";
					} else {
            $output .= "<img src='".WPSC_URL."/no-image-uploaded.gif' title='".$also_bought_data['name']."' alt='".$product['name']."' id='product_image_".$also_bought_data['id']."' class='product_image' />";
					}
				}
			}
      $output .= "<a class='wpsc_product_name' href='".wpsc_product_url($also_bought_data['id'])."'>".$also_bought_data['name']."</a>";
      //$output .= "<a href='".wpsc_product_url($also_bought_data['id'])."'>".$also_bought_data['name']."</a>";
      $output .= "</p>";
		}
    $output .= "</div>";
    $output .= "<br clear='all' />";
	}
  return $output;
}  


function fancy_notifications() {
  global $wpdb;
  if(get_option('fancy_notifications') == 1) {
    $output = "";
    $output .= "<div id='fancy_notification'>\n\r";
    $output .= "  <div id='loading_animation'>\n\r";
    $output .= '<img id="fancy_notificationimage" title="Loading" alt="Loading" src="'.WPSC_URL.'/images/indicator.gif" />'.TXT_WPSC_UPDATING."...\n\r";
    $output .= "  </div>\n\r";
    $output .= "  <div id='fancy_notification_content'>\n\r";
    $output .= "  </div>\n\r";
    $output .= "</div>\n\r";
	}
  return $output;
}

function fancy_notification_content($product_id, $quantity_limit = false) {
  global $wpdb;
  $siteurl = get_option('siteurl');
  $instock = true;
  if(is_numeric($product_id)) {
    $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='".$product_id."' LIMIT 1";
    $product = $wpdb->get_row($sql,ARRAY_A);
    //if($product['quantity_limited'] == 1) { }
    $output = "";
    if($quantity_limit == false) {
      $output .= "<span>".str_replace("[product_name]", stripslashes($product['name']), TXT_WPSC_YOU_JUST_ADDED)."</span>";
		} else {
			$output .= "<span>".str_replace("[product_name]", $product['name'], TXT_WPSC_SORRY_NONE_LEFT)."</span>";
		}
    $output .= "<a href='".get_option('shopping_cart_url')."' class='go_to_checkout'>".TXT_WPSC_GOTOCHECKOUT."</a>";
    $output .= "<a href='#' onclick='jQuery(\"#fancy_notification\").css(\"display\", \"none\"); return false;' class='continue_shopping'>".TXT_WPSC_CONTINUE_SHOPPING."</a>";
	}
  return $output;
}


function wpsc_product_url($product_id, $category_id = null) {
  global $wpdb, $wp_rewrite, $wp_query;
  
  if(!is_numeric($category_id) || ($category_id < 1)) {
		if(is_numeric($wp_query->query_vars['product_category'])) {
		  $category_id = $wp_query->query_vars['product_category'];
		} else {
			$category_list = $wpdb->get_row("SELECT `".$wpdb->prefix."product_categories`.`id`, IF((`".$wpdb->prefix."product_categories`.`id` = '".get_option('wpsc_default_category')."'), 0, 1) AS `order_state` FROM `".$wpdb->prefix."item_category_associations` , `".$wpdb->prefix."product_categories` WHERE `".$wpdb->prefix."item_category_associations`.`product_id` IN ('".$product_id."') AND `".$wpdb->prefix."item_category_associations`.`category_id` = `".$wpdb->prefix."product_categories`.`id` AND `".$wpdb->prefix."product_categories`.`active` IN('1') LIMIT 1",ARRAY_A);
			$category_id = $category_list['id'];		
		}
  }
  

  
  if((($wp_rewrite->rules != null) && ($wp_rewrite != null)) || (get_option('rewrite_rules') != null)) {
    $url_name = get_product_meta($product_id, 'url_name', true);	
		$product_url =wpsc_category_url($category_id).$url_name[0]."/";
  } else {    
    if(!stristr(get_option('product_list_url'), "?")) {
      $initial_seperator = "?";
    } else {
      $initial_seperator = "&amp;";
    }
    if(is_numeric($category_id) && ($category_id > 0)) {
      $product_url = get_option('product_list_url').$initial_seperator."category=".$category_id."&amp;product_id=".$product_id;
    } else {
      $product_url = get_option('product_list_url').$initial_seperator."product_id=".$product_id;
    }
  }
  return $product_url;
}

function google_buynow($product_id) {
	global $wpdb;
	$output = "";
	if ($product_id > 0){
		$product_sql = "SELECT * FROM ".$wpdb->prefix."product_list WHERE id = ".$product_id." LIMIT 1";
		$product_info = $wpdb->get_results($product_sql, ARRAY_A);
		$variation_sql = "SELECT * FROM ".$wpdb->prefix."variation_priceandstock WHERE product_id = ".$product_id;
		$variation_info = $wpdb->get_results($variation_sql, ARRAY_A);
		if (count($variation_info) > 0) {
			$variation = 1;
			$price = $variation_info[0]['price'];
		}
		if (get_option('google_server_type')=='production') {
			$action_target = "https://checkout.google.com/cws/v2/Merchant/".get_option('google_id')."/checkoutForm";
		} else {
			$action_target = "https://sandbox.google.com/checkout/cws/v2/Merchant/".get_option('google_id')."/checkoutForm";
		}

	
		$product_info = $product_info[0];
		$output .= "<form id='BB_BuyButtonForm".$product_id."' onsubmit='log_buynow(this);return true;' action= '".$action_target."' method='post' name='BB_BuyButtonForm".$product_id."'>";
		$output .= "<input name='product_id' type='hidden' value='".$product_id."'>";
		$output .= "<input name='item_name_1' type='hidden' value='".$product_info['name']."'>";
		$output .= "<input name='item_description_1' type='hidden' value='".$product_info['description']."'>";
		$output .= "<input name='item_quantity_1' type='hidden' value='1'>";
		if ($variation == 1) {
			$output .= "<input id='item_price' name='item_price_1' type='hidden' value='".$price."'>";
		} else {
			if ($product_info['special']=='0') {
				$output .= "<input id='item_price' name='item_price_1' type='hidden' value='".$product_info['price']."'>";
			} else {
				$output .= "<input name='item_price_1' type='hidden' value='".$product_info['special_price']."'>";
			}
		}
		$output .= "<input name='item_currency_1' type='hidden' value='".get_option('google_cur')."'>";
		$output .= "<input type='hidden' name='checkout-flow-support.merchant-checkout-flow-support.continue-shopping-url' value='".get_option('product_list_url')."'>";
		$output .= "<input type='hidden' name='checkout-flow-support.merchant-checkout-flow-support.edit-cart-url' value='".get_option('shopping_cart_url')."'>";
		$output .= "<input alt='' src=' https://checkout.google.com/buttons/buy.gif?merchant_id=".get_option('google_id')."&w=117&h=48&style=trans&variant=text&loc=en_US' type='image'/>";
		$output .="</form>";
	}
	return $output;
}

function external_link($product_id) { 
	global $wpdb;
	$link = get_product_meta($product_id,'external_link',true);
	if (!stristr($link[0],'http://')) {
		$link = 'http://'.$link[0];
	}
	$output .= "<input type='button' value='".TXT_WPSC_BUYNOW."' onclick='gotoexternallink(\"$link\")'>";
	return $output;
}


// displays error messages if the category setup is odd in some way
// needs to be in a function because there are at least three places where this code must be used.
function wpsc_odd_category_setup() {
	get_currentuserinfo();
  global $userdata;  
  $output = '';
  if(($userdata->wp_capabilities['administrator'] ==1) || ($userdata->user_level >=9)) {
    if(get_option('wpsc_default_category') == 1) {
			$output = "<p>".TXT_WPSC_USING_EXAMPLE_CATEGORY."</p>";
		} else {
		  $output = "<p>".TXT_WPSC_ADMIN_EMPTY_CATEGORY."</p>";
		}
  }
  return $output;
}


function wpsc_product_image_html($image_name, $product_id) {
  global $wpdb, $wp_query;
	if(is_numeric($wp_query->query_vars['product_category'])) {
    $category_id = (int)$wp_query->query_vars['product_category'];
	} else if (is_numeric($_GET['category'])) {
    $category_id = (int)$_GET['category'];
	} else {
    $category_id = (int)get_option('wpsc_default_category');
	}
	// 	$options['height'] = get_option('product_image_height');
	// 	$options['width']  = get_option('product_image_width');
	// 
	$product['height'] = get_product_meta($id, 'thumbnail_height');	
	$product['width']  = get_product_meta($id, 'thumbnail_width');
	
	
	$use_thumbnail_image = 'false';
	if(($product['height'] > $category['height']) || ($product['width'] > $category['width'])) {
		$use_thumbnail_image = 'true';
	}
	
	//list($category['height'], $category['width']) =
	 
	$category = $wpdb->get_row("SELECT `image_height` AS `height`, `image_width` AS `width` FROM `".$wpdb->prefix."product_categories` WHERE `id` IN ('{$category_id}')", ARRAY_A);
	// if there is a height, width, and imagePNG function
	if(($category['height'] != null) && ($category['width'] != null) && (function_exists('ImagePNG'))) {
		$image_path = "index.php?productid=".$product_id."&amp;thumbnail=".$use_thumbnail_image."&amp;width=".$category['width']."&amp;height=".$category['height']."";
	} else {
	  $image_path = WPSC_THUMBNAIL_URL.$image_name;
	}
  return $image_path;
}

function wpsc_buy_now_button($product_id, $replaced_shortcode = false) {
  global $wpdb;
  $selected_gateways = get_option('custom_gateway_options');
  if (in_array('google', (array)$selected_gateways)) {
		$output .= google_buynow($product['id']);
	} else if (in_array('paypal_multiple', (array)$selected_gateways)) {
		if ($product_id > 0){
			$product_sql = "SELECT * FROM ".$wpdb->prefix."product_list WHERE id = ".$product_id." LIMIT 1";
			$product = $wpdb->get_row($product_sql, ARRAY_A);
			$output .= "<form onsubmit='log_paypal_buynow(this)' target='paypal' action='".get_option('paypal_multiple_url')."' method='post'>
				<input type='hidden' name='business' value='".get_option('paypal_multiple_business')."'>
				<input type='hidden' name='cmd' value='_xclick'>
				<input type='hidden' name='item_name' value='".$product['name']."'>
				<input type='hidden' id='item_number' name='item_number' value='".$product['id']."'>
				<input type='hidden' id='amount' name='amount' value='".$product['price']."'>
				<input type='hidden' id='unit' name='unit' value='".$product['price']."'>
				<input type='hidden' id='shipping' name='ship11' value='".$shipping."'>
				<input type='hidden' name='handling' value='".get_option('base_local_shipping')."'>
				<input type='hidden' name='currency_code' value='".get_option('paypal_curcode')."'>
				<input type='hidden' name='undefined_quantity' value='0'>
				<input type='image' name='submit' border='0' src='https://www.paypal.com/en_US/i/btn/btn_buynow_LG.gif' alt='PayPal - The safer, easier way to pay online'>
				<img alt='' border='0' width='1' height='1' src='https://www.paypal.com/en_US/i/scr/pixel.gif' >
			</form>
		";
		}
	}
	if($replaced_shortcode == true) {
		return $output;
	} else {
		echo $output;
  }
}
?>