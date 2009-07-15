<?php
function widget_specials($args) {
  global $wpdb, $table_prefix;
  extract($args);
  $options = get_option('wpsc-widget_specials');
  $special_count = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `special` = '1'  AND `active` IN ('1')");   
  if($special_count > 0) {
    $title = empty($options['title']) ? __(TXT_WPSC_PRODUCT_SPECIALS) : $options['title'];
    echo $before_widget; 
    $full_title = $before_title . $title . $after_title;
    echo $full_title;
    nzshpcrt_specials();
    echo $after_widget;
	}
}



 function nzshpcrt_specials($input = null) {
   global $wpdb;
   $siteurl = get_option('siteurl');
   $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `special` = '1'  AND `active` IN ('1')  ORDER BY RAND() LIMIT 1";
   $product = $wpdb->get_results($sql,ARRAY_A) ;
		if($product != null) {
			$output = "<div><div>";
			foreach($product as $special) {
				$output .= "<h2><a class='wpsc_product_title' href='".wpsc_product_url($special['id'],$special['category'])."'>".$special['name']."</a></h2><br /> ";
					if(is_numeric($special['image'])){
						$image_file_name = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id`= '".$special['image']."' LIMIT 1");
						if($image_file_name != '') {
							$output .= "<img src='".WPSC_THUMBNAIL_URL.$image_file_name."' title='".$special['name']."' alt='".$special['name']."' /><br />";
						}
					}
				if(get_option('wpsc_special_description') != '1'){
					$output .= $special['description']."<br />";
				}
				$variations_processor = new nzshpcrt_variations;
				$variations_output = $variations_processor->display_product_variations($special['id'],true, false, true);
				$output .= $variations_output[0];
				if($variations_output[1] !== null) {
					$special['price'] = $variations_output[1];
				}
				if($variations_output[1] == null) {
					$output .= "<span class='oldprice'>".nzshpcrt_currency_display($special['price'], $special['notax'],false)."</span><br />";
				}
				
				$output .= "<span id='special_product_price_".$special['id']."'><span class='pricedisplay'>";       
				$output .= nzshpcrt_currency_display(($special['price'] - $special['special_price']), $special['notax'],false,$product['id']);
				$output .= "</span></span><br />";
				
				$output .= "<form id='specials_".$special['id']."' name='' method='post' action='#' onsubmit='submitform(this, null);return false;' >";
				$output .= "<input type='hidden' name='product_id' value='".$special['id']."'/>";
				$output .= "<input type='hidden' name='item' value='".$special['id']."' />";
				$output .= "<input type='hidden' name='wpsc_ajax_action' value='special_widget' />";			
				if(($special['quantity_limited'] == 1) && ($special['quantity'] < 1)) {
					$output .= TXT_WPSC_PRODUCTSOLDOUT."";
				} else {
					//$output .= $variations_processor->display_product_variations($special['id'],true);
					$output .= "<input type='submit' name='".TXT_WPSC_ADDTOCART."' value='".TXT_WPSC_ADDTOCART."'  />";
				}
				$output .= "</form>";
			}
			$output .= "</div></div>";
		} else {
			$output = '';
		}
		echo $input.$output;
	}

function widget_specials_control() {
  $option_name = 'wpsc-widget_specials';  // because I want to only change this to reuse the code.
	$options = $newoptions = get_option($option_name);
	if ( isset($_POST[$option_name]) ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST[$option_name]));
	}
	if(isset($_POST['wpsc_special_description'])){
		update_option('wpsc_special_description', $_POST['wpsc_special_description']);
	}else{
		update_option('wpsc_special_description', '0');
	}
	if(get_option('wpsc_special_description') == '1'){
		$checked = "checked='checked'";
	}else{
		$checked = '';
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option($option_name, $options);
	}
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
	
	echo "<p>\n\r";
	echo "  <label for='{$option_name}'>"._e('Title:')."<input class='widefat' id='{$option_name}' name='{$option_name}' type='text' value='{$title}' /></label>\n\r";
	echo "</p>\n\r";
	echo "<p>\n\r";
	echo "  <label for='{$option_name}'>"._e('Show Description:')."<input $checked id='wpsc_special_description' name='wpsc_special_description' type='checkbox' value='1' /></label>\n\r";
	echo "</p>\n\r";
}

function widget_specials_init() {
  if(function_exists('register_sidebar_widget')) {
    register_sidebar_widget(TXT_WPSC_PRODUCT_SPECIALS, 'widget_specials');
    register_widget_control(TXT_WPSC_PRODUCT_SPECIALS, 'widget_specials_control');
	}
  return;
}
add_action('plugins_loaded', 'widget_specials_init');
?>