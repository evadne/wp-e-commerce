<?php
/**
 * WP eCommerce display functions
 *
 * These are functions for the wp-eCommerce theme engine, template tags and shortcodes
 *
 * @package wp-e-commerce
 * @since 3.7
*/




/**
* wpsc buy now button code products function
* Sorry about the ugly code, this is just to get the functionality back, buy now will soon be overhauled, and this function will then be completely different
* @return string - html displaying one or more products
*/
function wpsc_buy_now_button($product_id, $replaced_shortcode = false) {
  global $wpdb, $wpsc_query;
  $temp_wpsc_query = new WPSC_query(array('product_id' =>$product_id));
  list($wpsc_query, $temp_wpsc_query) = array($temp_wpsc_query, $wpsc_query); // swap the wpsc_query objects
  
  $selected_gateways = get_option('custom_gateway_options');
  if (in_array('google', (array)$selected_gateways)) {
		$output .= google_buynow($product['id']);
	} else if (in_array('paypal_multiple', (array)$selected_gateways)) {
		if ($product_id > 0){
				//$output .= "<pre>".print_r($wpsc_query,true)."</pre>";
			while (wpsc_have_products()) :
				wpsc_the_product();
				$price =  calculate_product_price($wpsc_query->product['id'], $wpsc_query->first_variations); 
				$output .= "<form onsubmit='log_paypal_buynow(this)' target='paypal' action='".get_option('paypal_multiple_url')."' method='post' />
					<input type='hidden' name='business' value='".get_option('paypal_multiple_business')."' />
					<input type='hidden' name='cmd' value='_xclick' />
					<input type='hidden' name='item_name' value='".wpsc_the_product_title()."' />
					<input type='hidden' id='item_number' name='item_number' value='".wpsc_the_product_id()."' />
					<input type='hidden' id='amount' name='amount' value='".$price."' />
					<input type='hidden' id='unit' name='unit' value='".$price."' />
					<input type='hidden' id='shipping' name='ship11' value='".$shipping."' />
					<input type='hidden' name='handling' value='".get_option('base_local_shipping')."' />
					<input type='hidden' name='currency_code' value='".get_option('paypal_curcode')."' />
					<input type='hidden' name='undefined_quantity' value='0' />
					<input type='image' name='submit' border='0' src='https://www.paypal.com/en_US/i/btn/btn_buynow_LG.gif' alt='PayPal - The safer, easier way to pay online' />
					<img alt='' border='0' width='1' height='1' src='https://www.paypal.com/en_US/i/scr/pixel.gif' />
				</form>\n\r";
			endwhile;
		}
	}
	
	list($temp_wpsc_query, $wpsc_query) = array($wpsc_query, $temp_wpsc_query); // swap the wpsc_query objects back
	if($replaced_shortcode == true) {
		return $output;
	} else {
		echo $output;
  }
}





function wpsc_post_title_seo($title) {
	global $wpdb, $page_id, $wp_query;
	if($wp_query->query_vars['product_name'] != '') {
		$product_id = $wpdb->get_var("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '".$wpdb->escape($wp_query->query_vars['product_name'])."' ) LIMIT 1");			
    $title = $wpdb->get_var("SELECT `name` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` IN('".(int)$product_id."') LIMIT 1");
	} else if(is_numeric($_GET['product_id'])) {
		$title=$wpdb->get_var("SELECT `name` FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id IN ('".(int)$_GET['product_id']."') LIMIT 1" );
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
  
  $also_bought = $wpdb->get_results("SELECT `".WPSC_TABLE_PRODUCT_LIST."`.* FROM `".WPSC_TABLE_ALSO_BOUGHT."`, `".WPSC_TABLE_PRODUCT_LIST."` WHERE `selected_product`='".$product_id."' AND `".WPSC_TABLE_ALSO_BOUGHT."`.`associated_product` = `".WPSC_TABLE_PRODUCT_LIST."`.`id` AND `".WPSC_TABLE_PRODUCT_LIST."`.`active` IN('1') ORDER BY `".WPSC_TABLE_ALSO_BOUGHT."`.`quantity` DESC LIMIT $also_bought_limit",ARRAY_A);
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
            $output .= "<img src='".WPSC_URL."/images/no-image-uploaded.gif' title='".$also_bought_data['name']."' alt='".$also_bought_data['name']."' width='$image_display_height' height='$image_display_height' id='product_image_".$also_bought_data['id']."' class='product_image' />";
					} else {
            $output .= "<img src='".WPSC_URL."/images/no-image-uploaded.gif' title='".$also_bought_data['name']."' alt='".htmlentities(stripslashes($product['name']), ENT_QUOTES)."' id='product_image_".$also_bought_data['id']."' class='product_image' />";
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

function fancy_notification_content($cart_messages) {
  global $wpdb;
  $siteurl = get_option('siteurl');
	foreach((array)$cart_messages as $cart_message) {
		$output .= "<span>".$cart_message."</span><br />";
	}
	$output .= "<a href='".get_option('shopping_cart_url')."' class='go_to_checkout'>".TXT_WPSC_GOTOCHECKOUT."</a>";
	$output .= "<a href='#' onclick='jQuery(\"#fancy_notification\").css(\"display\", \"none\"); return false;' class='continue_shopping'>".TXT_WPSC_CONTINUE_SHOPPING."</a>";
  return $output;
}


function wpsc_product_url($product_id, $category_id = null, $escape = true) {
  global $wpdb, $wp_rewrite, $wp_query;
  
  if(!is_numeric($category_id) || ($category_id < 1)) {
		if(is_numeric($wp_query->query_vars['product_category'])) {
		  $category_id = $wp_query->query_vars['product_category'];
		} else {
			$category_list = $wpdb->get_row("SELECT `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id`, IF((`".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id` = '".get_option('wpsc_default_category')."'), 0, 1) AS `order_state` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` , `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` IN ('".$product_id."') AND `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` = `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id` AND `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`active` IN('1') LIMIT 1",ARRAY_A);
			$category_id = $category_list['id'];		
		}
  }
  

  
  if((($wp_rewrite->rules != null) && ($wp_rewrite != null)) || (get_option('rewrite_rules') != null)) {
    $url_name = get_product_meta($product_id, 'url_name', true);	
		$product_url =wpsc_category_url($category_id).$url_name."/";
  } else {    
    if(!stristr(get_option('product_list_url'), "?")) {
      $initial_seperator = "?";
    } else {
      $initial_seperator = ($escape) ? "&amp;" : "&";
    }
    if(is_numeric($category_id) && ($category_id > 0)) {
      $product_url = get_option('product_list_url').$initial_seperator."category=".$category_id.(($escape) ? "&amp;" : "&")."product_id=".$product_id;
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
		$product_sql = "SELECT * FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id = ".$product_id." LIMIT 1";
		$product_info = $wpdb->get_results($product_sql, ARRAY_A);
		$variation_sql = "SELECT * FROM ".WPSC_TABLE_VARIATION_PROPERTIES." WHERE product_id = ".$product_id;
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
	if (!stristr($link,'http://')) {
		$link = 'http://'.$link;
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
	 
	$category = $wpdb->get_row("SELECT `image_height` AS `height`, `image_width` AS `width` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` IN ('{$category_id}')", ARRAY_A);
	// if there is a height, width, and imagePNG function
	if(($category['height'] != null) && ($category['width'] != null) && (function_exists('ImagePNG'))) {
		$image_path = "index.php?productid=".$product_id."&amp;thumbnail=".$use_thumbnail_image."&amp;width=".$category['width']."&amp;height=".$category['height']."";
	} else {
	  $image_path = WPSC_THUMBNAIL_URL.$image_name;
	}
  return $image_path;
}


/* 19-02-09
 * add cart button function used for php template tags and shortcodes
*/
function wpsc_add_to_cart_button($product_id, $replaced_shortcode = false) {
	global $wpdb;
	if ($product_id > 0){
		if(function_exists('wpsc_theme_html')) {
			$product = $wpdb->get_row("SELECT * FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id = ".$product_id." LIMIT 1", ARRAY_A);
			//this needs the results from the product_list table passed to it, does not take just an ID
			$wpsc_theme = wpsc_theme_html($product);
		}
		
		// grab the variation form fields here
		$variations_processor = new nzshpcrt_variations;         
		$variations_output = $variations_processor->display_product_variations($product_id,false, false, false);

		$output .= "<form onsubmit='submitform(this);return false;'  action='' method='post'>";
		if($variations_output != '') { //will always be set, may sometimes be an empty string 
			$output .= "           <p>".$variations_output."</p>";
		}
		$output .= "<input type='hidden' name='prodid' value='".$product_id."' />";
		$output .= "<input type='hidden' name='item' value='".$product_id."' />";
		if(isset($wpsc_theme) && is_array($wpsc_theme) && ($wpsc_theme['html'] !='')) {
				$output .= $wpsc_theme['html'];
		} else {
			$output .= "<input type='submit' id='product_".$product['id']."_submit_button' class='wpsc_buy_button' name='Buy' value='".TXT_WPSC_ADDTOCART."'  />";
		}
		if($replaced_shortcode == true) {
			return $output;
		} else {
			echo $output;
	 	}
	} 
}

/**
* wpsc_obtain_the_title function, for replaacing the page title with the category or product
* @return string - the new page title
*/
function wpsc_obtain_the_title() {
  global $wpdb, $wp_query, $wpsc_title_data;
  $output = null;
  //exit("<pre>".print_r($wp_query,true)."</pre>");
  
	if(is_numeric($wp_query->query_vars['category_id'])) {
	  $category_id = $wp_query->query_vars['category_id'];
	  if(isset($wpsc_title_data['category'][$category_id])) {
			$output = $wpsc_title_data['category'][$category_id];
	  } else {
			$output = $wpdb->get_var("SELECT `name` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id`='{$category_id}' LIMIT 1");
			$wpsc_title_data['category'][$category_id] = $output;
		}
		
	}
	if(isset($wp_query->query_vars['product_url_name'])) {
	  $product_name = $wp_query->query_vars['product_url_name'];
	  if(isset($wpsc_title_data['product'][$product_name])) {
	    $product_list = array();
	    $product_list['name'] = $wpsc_title_data['product'][$product_name];
	  } else {
			$product_id = $wpdb->get_var("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '{$wp_query->query_vars['product_url_name']}' ) ORDER BY `id` DESC LIMIT 1");
			$product_list = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1",ARRAY_A);
			$wpsc_title_data['product'][$product_name] = $product_list['name'];
		}
  }
  if(isset($product_list ) && ($product_list != null)) {
  	$output = htmlentities(stripslashes($product_list['name']), ENT_QUOTES);
  }
	return $output;
}
 

function wpsc_replace_the_title($input) {
  global $wpdb, $wp_query;
	$output = wpsc_obtain_the_title();
	if($output != null) {
		$backtrace = debug_backtrace();
		if($backtrace[3]['function'] == 'get_the_title') {
			return $output;
		}
	}
	return $input;
}

function wpsc_replace_wp_title($input) {
  global $wpdb, $wp_query;
	$output = wpsc_obtain_the_title();
	if($output != null) {
		return $output;
	}
	return $input;
}

function wpsc_replace_bloginfo_title($input, $show) {
  global $wpdb, $wp_query;
  if($show == 'description') {
		$output = wpsc_obtain_the_title();
		if($output != null) {
			return $output;
		}
	}
	return $input;
}
 
if(get_option('wpsc_replace_page_title') == 1) {
  add_filter('the_title', 'wpsc_replace_the_title', 10, 2);
  add_filter('wp_title', 'wpsc_replace_wp_title', 10, 2);
  add_filter('bloginfo', 'wpsc_replace_bloginfo_title', 10, 2);
}


?>