<?php
define('WPSC_CURR_TOUCHSHOP_URL',WPSC_TOUCHSHOP_URL.'/themes/touchShop/');
/*default theme for iphone module*/

if(get_option('wpsc_selected_theme')) {

// because this file is not shared as in prototype themes  - test if current browsers themes contain these 2 functions

 if (!function_exists('wpsc_grid_title_and_price')) {
	function wpsc_grid_title_and_price(&$product) {
		
		return touch_wpsc_grid_title_and_price($product);
	} 
 }
 if (!function_exists('wpsc_theme_html')) {
	function wpsc_theme_html(&$product) {
		
		return touch_wpsc_theme_html($product);	
	}
 }

// calls to these 2 functions must be added in current browsers themes to be activated if the previous functions are in these themes.

function touch_wpsc_grid_title_and_price(&$product) {
		$output .= "<div class='grid_price'>";
		if ($soldout) {
			$output .= "<span class='soldoutgrid'>Sold out</span>";
		} else {
			if($product['special']==1) {
				$output .= nzshpcrt_currency_display(($product['price'] - $product['special_price']), $product['notax'],false,$product['id']) . "\n\r";
			} else {
				$output .= nzshpcrt_currency_display($product['price'], $product['notax']) . "\n\r";
			}
		}
		$output .= "</div>";
		$output .= "<div class='grid_prodcut_title'><a href='".wpsc_product_url($product['id'])."'>".stripslashes($product['name'])."</a></div>";
	
		return $output;
}
	
function touch_wpsc_theme_html(&$product) {
		
		$siteurl = get_option('siteurl');
		
    $wpsc_theme['html'] .="<div class='navisinglebanner' ><p>";
    $wpsc_theme['html'] .= xili_linkofmain_cat_of_product ($product['id']); //only on single - css
    $wpsc_theme['html'] .="</p><input type='submit'  id='product_".$product['id']."_submit_button' class='wpsc_buy_button' name='Buy' value='".TXT_WPSC_ADDTOCART."' onclick='alert(\"".TXT_WPSC_ITEMHASBEENADDED."\");' /></div>";
  
    $wpsc_theme['html'] .= "<div id='navigeneralbanner'><a href='".get_option('product_list_url')."'>Accueil</a><span class='flexible'>&nbsp</span><a href='".get_option('shopping_cart_url')."'><img src='".WPSC_CURR_TOUCHSHOP_URL."images/panierh40.jpg' alt=''/> Mon panier</a></div>"; 
  
    $wpsc_theme['html'] .= xili_navigation_html($product['id']);
		return $wpsc_theme;
}

	
	function xili_navigation_html($ID,$group_id = 1) {
		        
		return '<!-- add by xili for iphone and ipodtouch : '.$ID.' -->';
		
		}
		
	function xili_linkofmain_cat_of_product ($ID,$group_id = 1,$class="navisingle") {		
		global $wpdb;
		/*to display the mother cat of the products in main group - first release in simple structure*/ 
		$sql = 'SELECT DISTINCT wcats. * '
        . ' FROM '.$wpdb->prefix.'product_categories AS wcats '
        . ' LEFT JOIN '.$wpdb->prefix.'item_category_associations AS cat2pr ON ( wcats.id = cat2pr.category_id ) '
        . ' WHERE cat2pr.product_id = '.$ID.' and wcats.group_id = '.$group_id;
        $the_catsofproduct = $wpdb->get_results($sql);
        //echo $sql;
        //print_r($the_catsofproduct);
        if ($the_catsofproduct)
        	return '<a class="'.$class.'" href="'.get_option('product_list_url').'&category='.$the_catsofproduct[0]->id.'">&lt;&nbsp;&nbsp;'.$the_catsofproduct[0]->name.'</a>';
		}
		
	function xili_WPSC4touchNAVlist($atts) {
	
		if (xili_display4mobile() === true) :

			$arr_result = shortcode_atts(array(
				'class'=>'navishop'
			), $atts); // ready

			$thenavlist = '<ul class='.$arr_result['class'].'>';
			$thenavlist .=	'<li onclick="location.href=\''.get_option('product_list_url').'\'" ><a href="'.get_option('product_list_url').'">La boutique</a></li>';
			/*sub list for main category of the shop : examples*/
			$catslist = xili_WPSC4touch_listcats();
			if ($catslist) :
				$thenavlist .='<ul>';
				foreach ($catslist as $onecat) { 
					$thenavlist .= '<li onclick="location.href=\''.wpsc_category_url($onecat['id']).'\'" ><a href="'.wpsc_category_url($onecat['id']).'">'.$onecat['name'].'</a></li>'; }
				$thenavlist .= '</ul>';
			endif;
			$thenavlist .=	'<li onclick="location.href=\''.get_option('shopping_cart_url').'\'"><a href="'.get_option('shopping_cart_url').'">Votre panier</a></li>';
			$thenavlist .=	'<li onclick="location.href=\''.get_option('user_account_url').'\'"><a href="'.get_option('user_account_url').'">Votre compte</a></li>';
			$thenavlist .=	'<li onclick="location.href=\''.get_bloginfo('url').'\'"><a href="'.get_bloginfo('url').'">Le site</a></li>';	
			$thenavlist .=  '</ul>';
			return $thenavlist;
		endif;
	}

	add_shortcode('xili_wpsc4touchnav', 'xili_WPSC4touchNAVlist');

	function xili_WPSC4touch_listcats($group_id = 1, $id = 0, $level = 0) {
  		global $wpdb ;
  		if(is_numeric($id)) {
   		 $category_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `group_id` IN ('$group_id') AND `active`='1' AND `category_parent` = '".$id."' ORDER BY `id`";
    	$category_list = $wpdb->get_results($category_sql,ARRAY_A);
    	return $category_list;
  		}
	}
	
	function xili_WPSC4touch_catname($category) {
		global $wpdb;
		$category_name=  $wpdb->get_var("SELECT name FROM {$wpdb->prefix}product_categories WHERE id='".$category."'");	
		return $category_name;
	}
	
	function xili_WPSC4touch_pageID($pagename = 'product_list_url') {
	return get_option($pagename);	
	}
	
	function xili_WPSC4touch_showAdditionalDesc($id) {
		// to be more flexible in list of products for displaying or not additional desc.
		// called by product_display_functions - line 372
		$output = "";
		$output .= "<span class='additional_description_span'><a href='";
		$output .= wpsc_product_url($id)."' > + </a>";
		$output .= "</span>";
		return $output;
	}
	
	function xili_WPSC4touch_showVariations($id) {
		// to be more flexible in list of products for displaying or not variations
		// called by product_display_functions - line 410
		$output = "";
		$output .= "<p class='xili_variation_forms'>** <a href='";
		$output .= wpsc_product_url($id)."' >".TXT_WPSC_VARIATIONS."</a> **</p>";
		return $output;	
	}
	
}
?>