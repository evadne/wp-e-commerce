<?php
/**
 * WP eCommerce theme functions
 *
 * These are the functions for the wp-eCommerce theme engine
 *
 * @package wp-e-commerce
 * @since 3.7
*/

/**
	*select_wpsc_theme_functions function, provides a place to override the e-commece theme path
  * add to switch "theme's functions file 
  * © with xiligroup dev
  */
function wpsc_select_theme_functions() {
  $theme_dir = WPSC_THEME_DIR; /* done by plugins_loaded */
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',WPSC_FILE_PATH."/themes/".$theme_dir);
	
	if((get_option('wpsc_selected_theme') != '') && (file_exists($cur_wpsc_theme_folder."/".$theme_dir.".php") )) { 
		include_once($cur_wpsc_theme_folder.'/'.$theme_dir.'.php');
	}
  // end add by xiligroup.dev
}
add_action('wp','wpsc_select_theme_functions',10,1);

/**
* wpsc_user_enqueues products function,
* enqueue all javascript and CSS for wp ecommerce
*/
function wpsc_enqueue_user_script_and_css() {
	/**
	* added by xiligroup.dev to be compatible with touchshop
	*/
	if (has_filter('wpsc_enqueue_user_script_and_css') && apply_filters('wpsc_mobile_scripts_css_filters',false)){
	 		do_action('wpsc_enqueue_user_script_and_css'); 
	} else {
		/**
		* end of added by xiligroup.dev to be compatible with touchshop
		*/
		global $wp_styles;
		$version_identifier = WPSC_VERSION.".".WPSC_MINOR_VERSION;
		
		if(is_numeric($_GET['category']) || is_numeric($wp_query->query_vars['product_category']) || is_numeric(get_option('wpsc_default_category'))) {
			if(is_numeric($wp_query->query_vars['product_category'])) {
				$category_id = $wp_query->query_vars['product_category'];
			} else if(is_numeric($_GET['category'])) {
				$category_id = $_GET['category'];
			} else {
				$category_id = get_option('wpsc_default_category');
			}
		}
		
		
		wp_enqueue_script( 'jQuery');
		wp_enqueue_script('wp-e-commerce', WPSC_URL.'/js/wp-e-commerce.js', array('jquery'), WPSC_VERSION.WPSC_MINOR_VERSION);
		wp_enqueue_script('wp-e-commerce-ajax-legacy', WPSC_URL.'/js/ajax.js', false, WPSC_VERSION.WPSC_MINOR_VERSION);
		wp_enqueue_script('wp-e-commerce-dynamic', $siteurl."/index.php?wpsc_user_dynamic_js=true", false, $version_identifier);
		wp_enqueue_script('livequery', WPSC_URL.'/wpsc-admin/js/jquery.livequery.js', array('jquery'), '1.0.3');

		
		wp_enqueue_script('wp-e-commerce-legacy', WPSC_URL.'/js/user.js', array('jquery'), WPSC_VERSION.WPSC_MINOR_VERSION);

		
		wp_enqueue_script('wpsc-thickbox',WPSC_URL.'/js/thickbox.js', array('jquery'), 'Instinct_e-commerce');

		$theme_path = WPSC_FILE_PATH. '/themes/';
		$theme_url = WPSC_URL. '/themes/';
		if(file_exists($theme_path.get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".css")) {
			$theme_url = $theme_url.get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".css";
		} else {
			$theme_url = $theme_url. '/default/default.css';
		}
		
		wp_enqueue_style( 'wpsc-theme-css', $theme_url, false, $version_identifier, 'all');
		wp_enqueue_style( 'wpsc-theme-css-compatibility', WPSC_URL. '/themes/compatibility.css', false, $version_identifier, 'all');
		wp_enqueue_style( 'wpsc-product-rater', WPSC_URL.'/js/product_rater.css', false, $version_identifier, 'all');
		wp_enqueue_style( 'wp-e-commerce-dynamic', $siteurl."/index.php?wpsc_user_dynamic_css=true&category=$category_id" , false, $version_identifier, 'all' );
		wp_enqueue_style( 'wpsc-thickbox', WPSC_URL.'/js/thickbox.css', false, $version_identifier, 'all');
		
		
		wp_enqueue_style( 'wpsc-ie-fixes', WPSC_URL.'/themes/wpsc-ie-fixes.css', false, $version_identifier, 'all');
		$wp_styles->add_data( 'wpsc-ie-fixes', 'conditional', 'lt IE 7' );
	}
}

if(strpos($_SERVER['SCRIPT_NAME'], "wp-admin") === false) {
	add_action('init', 'wpsc_enqueue_user_script_and_css');
}

function wpsc_user_dynamic_js() { 
 	header('Content-Type: text/javascript');
 	header('Expires: '.gmdate('r',mktime(0,0,0,date('m'),(date('d')+12),date('Y'))).'');
 	header('Cache-Control: public, must-revalidate, max-age=86400');
 	header('Pragma: public');
  $siteurl = get_option('siteurl'); 
  ?>
  jQuery.noConflict();
	/* base url */
	var base_url = "<?php echo $siteurl; ?>";
	var WPSC_URL = "<?php echo WPSC_URL; ?>";
	var WPSC_IMAGE_URL = "<?php echo WPSC_IMAGE_URL; ?>";
	var WPSC_DIR_NAME = "<?php echo WPSC_DIR_NAME; ?>";
	/* LightBox Configuration start*/
	var fileLoadingImage = "<?php echo WPSC_URL; ?>/images/loading.gif";
	var fileBottomNavCloseImage = "<?php echo WPSC_URL; ?>/images/closelabel.gif";
	var fileThickboxLoadingImage = "<?php echo WPSC_URL; ?>/images/loadingAnimation.gif";
	var resizeSpeed = 9;  // controls the speed of the image resizing (1=slowest and 10=fastest)
	var borderSize = 10;  //if you adjust the padding in the CSS, you will need to update this variable
<?php
  exit();
}

if($_GET['wpsc_user_dynamic_js'] == 'true') {
  add_action("init", 'wpsc_user_dynamic_js');  
}



function wpsc_user_dynamic_css() {  
  global $wpdb;  
  header('Content-Type: text/css');
 	header('Expires: '.gmdate('r',mktime(0,0,0,date('m'),(date('d')+12),date('Y'))).'');
 	header('Cache-Control: public, must-revalidate, max-age=86400');
 	header('Pragma: public'); 	
 	
  $category_id = absint($_GET['category']);

	$category_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id`='{$category_id}' LIMIT 1",ARRAY_A);
	
	
	if($category_data['display_type'] != '') {
		$display_type = $category_data['display_type'];
	} else {
		$display_type = get_option('product_view');
	}
	
	
	if(!defined('WPSC_DISABLE_IMAGE_SIZE_FIXES') || (constant('WPSC_DISABLE_IMAGE_SIZE_FIXES') != true)) {
		$thumbnail_width = get_option('product_image_width');
		if($thumbnail_width <= 0) {
			$thumbnail_width = 96;
		}
		$thumbnail_height = get_option('product_image_height'); 
		if($thumbnail_height <= 0) { 
			$thumbnail_height = 96; 
		}
		
    $single_thumbnail_width = get_option('single_view_image_width');
    $single_thumbnail_height = get_option('single_view_image_height');
    if($single_thumbnail_width <= 0) {
      $single_thumbnail_width = 128;
    }
		
		?>
		
    /*
     * Default View Styling
     */
		div.default_product_display div.textcol{
			margin-left: <?php echo $thumbnail_width + 10; ?>px !important;
			<?php /*_margin-left: <?php echo ($thumbnail_width/2) + 5; ?>px !important;  */ ?>
			min-height: <?php echo $thumbnail_height;?>px;
			_height: <?php echo $thumbnail_height;?>px;
		}
			
		div.default_product_display  div.textcol div.imagecol{
			position:absolute;
			top:0px;
			left: 0px;
			margin-left: -<?php echo $thumbnail_width + 10; ?>px !important;
		}
		
		div.default_product_display  div.textcol div.imagecol a img {
			width: <?php echo $thumbnail_width; ?>px;
			height: <?php echo $thumbnail_height; ?>px;
		}
		
		div.default_product_display div.item_no_image  {
			width: <?php echo $thumbnail_width-2; ?>px;
			height: <?php echo $thumbnail_height-2; ?>px;
		}
		div.default_product_display div.item_no_image a  {
			width: <?php echo $thumbnail_width-2; ?>px;
		}


    /*
     * Grid View Styling
     */
		div.product_grid_display div.item_no_image  {
			width: <?php echo $thumbnail_width-2; ?>px;
			height: <?php echo $thumbnail_height-2; ?>px;
		}
		div.product_grid_display div.item_no_image a  {
			width: <?php echo $thumbnail_width-2; ?>px;
		}
		
		
		
    /*
     * Single View Styling
     */
     
		div.single_product_display div.item_no_image  {
			width: <?php echo $single_thumbnail_width-2; ?>px;
			height: <?php echo $single_thumbnail_height-2; ?>px;
		}
		div.single_product_display div.item_no_image a  {
			width: <?php echo $single_thumbnail_width-2; ?>px;
		}
		
		div.single_product_display div.textcol{
			margin-left: <?php echo $single_thumbnail_width + 10; ?>px !important;
			<?php /* _margin-left: <?php echo ($single_thumbnail_width/2) + 5; ?>px !important; */ ?>
			min-height: <?php echo $single_thumbnail_height;?>px;
			_height: <?php echo $single_thumbnail_height;?>px;
		}
			
			
		div.single_product_display  div.textcol div.imagecol{
			position:absolute;
			top:0px;
			left: 0px;
			margin-left: -<?php echo $single_thumbnail_width + 10; ?>px !important;
		}
		
		div.single_product_display  div.textcol div.imagecol a img {
			width: <?php echo $single_thumbnail_width; ?>px;
			height: <?php echo $single_thumbnail_height; ?>px;
		}
      
    <?php
    $product_image_size_list = $wpdb->get_results("SELECT `products`.`id`, `meta1`.`meta_value` AS `height`, `meta2`.`meta_value` AS `width` FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products` INNER JOIN `".WPSC_TABLE_PRODUCTMETA."` AS `meta1` INNER JOIN `".WPSC_TABLE_PRODUCTMETA."` AS `meta2` ON `products`.`id` = `meta1`.`product_id` = `meta2`.`product_id`  WHERE `products`.`thumbnail_state` IN(0,2,3) AND `meta1`.`meta_key` IN ('thumbnail_height') AND `meta2`.`meta_key` IN ('thumbnail_width')", ARRAY_A); 
    foreach($product_image_size_list as $product_image_sizes) {
      $individual_thumbnail_height = $product_image_sizes['height']; 
      $individual_thumbnail_width = $product_image_sizes['width'];     
      if($individual_thumbnail_height> $thumbnail_height) { 
        echo "    div.default_product_display.product_view_$product_id div.textcol{\n\r"; 
        echo "            min-height: ".($individual_thumbnail_height + 10)."px !important;\n\r"; 
        echo "            _height: ".($individual_thumbnail_height + 10)."px !important;\n\r"; 
        echo "      }\n\r";
      } 
      if($individual_thumbnail_width> $thumbnail_width) {
          echo "      div.default_product_display.product_view_$product_id div.textcol{\n\r";
          echo "            margin-left: ".($individual_thumbnail_width + 10)."px !important;\n\r";
          echo "            _margin-left: ".(($individual_thumbnail_width/2) + 5)."px !important;\n\r";
          echo "      }\n\r";
  
          echo "      div.default_product_display.product_view_$product_id  div.textcol div.imagecol{\n\r";
          echo "            position:absolute;\n\r";
          echo "            top:0px;\n\r";
          echo "            left: 0px;\n\r";
          echo "            margin-left: -".($individual_thumbnail_width + 10)."px !important;\n\r";
          echo "      }\n\r";
  
          echo "      div.default_product_display.product_view_$product_id  div.textcol div.imagecol a img{\n\r";
          echo "            width: ".$individual_thumbnail_width."px;\n\r";
          echo "            height: ".$individual_thumbnail_height."px;\n\r";
          echo "      }\n\r";
        }
      }	
    }
    
  if(is_numeric($_GET['brand']) || (get_option('show_categorybrands') == 3)) {
    $brandstate = 'block';
    $categorystate = 'none';
  } else {
    $brandstate = 'none';
    $categorystate = 'block';
  }
      
    ?>
    div#categorydisplay{
    display: <?php echo $categorystate; ?>;
    }
    
    div#branddisplay{
    display: <?php echo $brandstate; ?>;
    }
    <?php
	exit();
}

if($_GET['wpsc_user_dynamic_css'] == 'true') {
  add_action("init", 'wpsc_user_dynamic_css');  
}




// Template tags
/**
* wpsc display products function
* @return string - html displaying one or more products
*/
function wpsc_display_products($query) {
  global $wpdb, $wpsc_query;
	
	/// added by xiligroup.dev to be compatible with touchshop
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',WPSC_FILE_PATH."/themes/".WPSC_THEME_DIR);
	/// end of added by xiligroup.dev to be compatible with touchshop
  
  $temp_wpsc_query = new WPSC_query($query);
  list($wpsc_query, $temp_wpsc_query) = array($temp_wpsc_query, $wpsc_query); // swap the wpsc_query objects
  
	$GLOBALS['nzshpcrt_activateshpcrt'] = true;
	ob_start();
	if(wpsc_is_single_product()) {
		include_once($cur_wpsc_theme_folder."/single_product.php");
	} else {
		// get the display type for the selected category
		if(is_numeric($wpsc_query->query_vars['category_id'])) {
			$category_id =(int) $wpsc_query->query_vars['category_id'];
			$display_type = $wpdb->get_var("SELECT `display_type` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id`='{$category_id}' LIMIT 1");
		}
		
		if($display_type == '') {
			$display_type = get_option('product_view');
		}

		if(isset($_SESSION['wpsc_display_type'])) {
		  $display_type = $_SESSION['wpsc_display_type'];
		}

		if(isset($_GET['view_type'])) {
			switch($_GET['view_type']) {
				case 'grid':
				$display_type = 'grid';
				$_SESSION['wpsc_display_type'] = $display_type;
				break;
				
				case 'default':
				$display_type = 'default';
				$_SESSION['wpsc_display_type'] = $display_type;
				break;

			  default:
			  break;
			}
		}

		
		//exit($display_type);
		// switch the display type, based on the display type variable...
		switch($display_type) {
			case "grid":
			if(file_exists($cur_wpsc_theme_folder."/grid_view.php")) {
				include_once($cur_wpsc_theme_folder."/grid_view.php");
				break; // only break if we have the function;
			}
			
			/*
			case "list":
			if(function_exists('product_display_list')) {
				echo product_display_list($product_list, $group_type, $group_sql, $search_sql);
				break; // only break if we have the function;
			}
			*/
			
			case "default":  // this may be redundant :D
			default:
				include_once($cur_wpsc_theme_folder."/products_page.php");
			break;
		}
	}
	$output = ob_get_contents();
	ob_end_clean();
	$output = str_replace('$','\$', $output);

	list($temp_wpsc_query, $wpsc_query) = array($wpsc_query, $temp_wpsc_query); // swap the wpsc_query objects back
	return $output;
}

//handles replacing the tags in the pages
  
function wpsc_products_page($content = '') {
  global $wpdb, $wp_query, $wpsc_query;
	/// added by xiligroup.dev to be compatible with touchshop
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',WPSC_FILE_PATH."/themes/".WPSC_THEME_DIR);
	/// end of added by xiligroup.dev to be compatible with touchshop
  
	$output = '';
  if(preg_match("/\[productspage\]/",$content)) {
  
      if(!(is_numeric(get_option('wpsc_default_category')) || (get_option('wpsc_default_category') == 'all'))) {
        if(is_numeric($wp_query->query_vars['category_id'])) {
					$category_id = $wp_query->query_vars['category_id'];
				} else if(is_numeric($_GET['category'])) {
					$category_id = $_GET['category'];
				}
				
				//echo "<pre>".print_r($wp_query,true)."</pre>";
				// if we have no categories, and no search, show the group list
				// this does not use the theme engine because categories uses a recursive function, I have not yet thought of a way of making this work in a theme engine
				if(!(is_numeric($category_id) || is_numeric(get_option('wpsc_default_category')) || (is_numeric($product_id)) || (get_option('wpsc_default_category') == 'all') || ($_GET['product_search'] != ''))) {
				  if(get_option('wpsc_default_category') == 'all+list') {
						$output = nzshpcrt_display_categories_groups();
				  } else { 
						return nzshpcrt_display_categories_groups();
						exit();
					}
				}
      
      }
//     if(get_option('wpsc_use_theme_engine') == TRUE) {
			$wpsc_query->get_products();
  
			$GLOBALS['nzshpcrt_activateshpcrt'] = true;
			ob_start();
			
			if(wpsc_is_single_product()) {
				include_once($cur_wpsc_theme_folder."/single_product.php");
			} else {
			  // get the display type for the selected category
				if(is_numeric($_GET['category']) || is_numeric($wp_query->query_vars['category_id']) || is_numeric(get_option('wpsc_default_category'))) {
					if(is_numeric($wp_query->query_vars['category_id'])) {
						$category_id =(int) $wp_query->query_vars['category_id'];
					} else if(is_numeric($_GET['category'])) {
						$category_id = (int)$_GET['category'];
					} else { 
						$category_id = (int)get_option('wpsc_default_category');
					}
				}			
				$display_type = $wpdb->get_var("SELECT `display_type` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id`='{$category_id}' LIMIT 1");
			
				if($display_type == '') {
					$display_type = get_option('product_view');
				}

				if(isset($_SESSION['wpsc_display_type'])) {
					$display_type = $_SESSION['wpsc_display_type'];
				}

				if(isset($_GET['view_type'])) {
					switch($_GET['view_type']) {
						case 'grid':
						$display_type = 'grid';
						$_SESSION['wpsc_display_type'] = $display_type;
						break;
						
						case 'default':
						$display_type = 'default';
						$_SESSION['wpsc_display_type'] = $display_type;
						break;

						default:
						break;
					}
				}

				
				// switch the display type, based on the display type variable...
				switch($display_type) {
					case "grid":
					if(file_exists($cur_wpsc_theme_folder."/grid_view.php")) {
						include_once($cur_wpsc_theme_folder."/grid_view.php");
						break; // only break if we have the function;
					}
					/*
					case "list":
					if(function_exists('product_display_list')) {
						echo product_display_list($product_list, $group_type, $group_sql, $search_sql);
						break; // only break if we have the function;
					}
					*/
				  case "default":  // this may be redundant :D
				  default:
				    include_once($cur_wpsc_theme_folder."/products_page.php");
				  break;
				}
			}
			$output .= ob_get_contents();
			ob_end_clean();
			$output = str_replace('$','\$', $output);
//     } else {
// 			$GLOBALS['nzshpcrt_activateshpcrt'] = true;
// 			ob_start();
// 			include_once(WPSC_FILE_PATH . "/products_page.php");
// 			$output = ob_get_contents();
// 			ob_end_clean();
//     }
    return preg_replace("/(<p>)*\[productspage\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}

function wpsc_place_shopping_cart($content = '') {
	/// added by xiligroup.dev to be compatible with touchshop
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',WPSC_FILE_PATH."/themes/".WPSC_THEME_DIR);
	/// end of added by xiligroup.dev to be compatible with touchshop
	
  if(preg_match("/\[shoppingcart\]/",$content)) {
		$GLOBALS['nzshpcrt_activateshpcrt'] = true;
		ob_start();
		include_once($cur_wpsc_theme_folder."/shopping_cart_page.php");
		$output = ob_get_contents();
		ob_end_clean();
		$output = str_replace('$','\$', $output);
    return preg_replace("/(<p>)*\[shoppingcart\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}
  
/// wpsc_checkout is an obsolete function
// function wpsc_checkout($content = '') {
//   if(preg_match("/\[checkout\]/",$content)) {
//     ob_start();
//     include_once(WPSC_FILE_PATH . "/checkout.php");
//     $output = ob_get_contents();
//     ob_end_clean();
//     return preg_replace("/(<p>)*\[checkout\](<\/p>)*/",$output, $content);
// 	} else {
//     return $content;
// 	}
// }

function wpsc_transaction_results($content = '') {
  if(preg_match("/\[transactionresults\]/",$content)) {
    ob_start();
    include_once(WPSC_FILE_PATH . "/transaction_results.php");
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/(<p>)*\[transactionresults\](<\/p>)*/",$output, $content);
	} else { 
    return $content;
	}
}
  
function wpsc_user_log($content = '') {
  if(preg_match("/\[userlog\]/",$content)) {
    ob_start();
    include_once(WPSC_FILE_PATH . '/user-log.php');
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/(<p>)*\[userlog\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}
  
  
//displays a list of categories when the code [showcategories] is present in a post or page.
function wpsc_show_categories($content = '') {
  if(preg_match("/\[showcategories\]/",$content)) {
    $GLOBALS['nzshpcrt_activateshpcrt'] = true;
    $output = nzshpcrt_display_categories_groups();
    return preg_replace("/(<p>)*\[showcategories\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}

// substitutes in the buy now buttons where the shortcode is in a post.
function wpsc_substitute_buy_now_button($content = '') {
  if(preg_match_all("/\[buy_now_button=([\d]+)\]/", $content, $matches)) {
  //echo "<pre>".print_r($matches,true)."</pre>";
    foreach($matches[1] as $key => $product_id) {
      $original_string = $matches[0][$key];
      //print_r($matches);
      $output = wpsc_buy_now_button($product_id, true);  
			$content = str_replace($original_string, $output, $content);
    }
	}	
	return $content;
}

/* 19-02-09
 * add to cart shortcode function used for shortcodes calls the function in
 * product_display_functions.php
 */
function add_to_cart_shortcode($content = '') {
		//exit($content);
  if(preg_match_all("/\[add_to_cart=([\d]+)\]/",$content, $matches)) {
    	foreach($matches[1] as $key => $product_id){
  			$original_string = $matches[0][$key];
  			$output = wpsc_add_to_cart_button($product_id, true);  
			$content = str_replace($original_string, $output, $content);
  	}
  }
    return $content;	
}


function wpsc_enable_page_filters($excerpt = ''){
  global $wp_query;
  add_filter('the_content', 'add_to_cart_shortcode', 12);//Used for add_to_cart_button shortcode
  add_filter('the_content', 'wpsc_products_page', 12);
  add_filter('the_content', 'wpsc_place_shopping_cart', 12);
  add_filter('the_content', 'wpsc_transaction_results', 12);
  //add_filter('the_content', 'wpsc_checkout', 12);
  add_filter('the_content', 'nszhpcrt_homepage_products', 12);
  add_filter('the_content', 'wpsc_user_log', 12);
  add_filter('the_content', 'nszhpcrt_category_tag', 12);
  add_filter('the_content', 'wpsc_show_categories', 12);
  add_filter('the_content', 'wpsc_substitute_buy_now_button', 12);
  return $excerpt;
}

function wpsc_disable_page_filters($excerpt = '') {
	remove_filter('the_content', 'add_to_cart_shortcode');//Used for add_to_cart_button shortcode
  remove_filter('the_content', 'wpsc_products_page');
  remove_filter('the_content', 'wpsc_place_shopping_cart');
  remove_filter('the_content', 'wpsc_transaction_results');
  //remove_filter('the_content', 'wpsc_checkout');
  remove_filter('the_content', 'nszhpcrt_homepage_products');
  remove_filter('the_content', 'wpsc_user_log');
  remove_filter('the_content', 'wpsc_category_tag');
  remove_filter('the_content', 'wpsc_show_categories');
  remove_filter('the_content', 'wpsc_substitute_buy_now_button');
  return $excerpt;
}

wpsc_enable_page_filters();

add_filter('get_the_excerpt', 'wpsc_disable_page_filters', -1000000);
add_filter('get_the_excerpt', 'wpsc_enable_page_filters', 1000000);


?>