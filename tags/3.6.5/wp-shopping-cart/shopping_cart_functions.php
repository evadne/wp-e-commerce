<?php
function nzshpcrt_shopping_basket($input = null, $override_state = null) {
  global $wpdb;
  
  if(is_numeric($override_state)) {
    $state = $override_state;
	} else {
		$state = get_option('cart_location');
	}
  
  if($state == 1) {
    if($input != '') {
      $cart = $_SESSION['nzshpcrt_cart'];
      echo "<div id='sideshoppingcart'><div id='shoppingcartcontents'>";
      echo nzshpcrt_shopping_basket_internals($cart);
      echo "</div></div>";
		}
  } else if(($state == 3) || ($state == 4)) {
		$cart = $_SESSION['nzshpcrt_cart'];
		if($state == 4) {
			#echo $input;
			echo "<div id='widgetshoppingcart'><div id='shoppingcartcontents'>";
			echo nzshpcrt_shopping_basket_internals($cart,false,true);
			echo "</div></div>";
			$dont_add_input = true;
		} else {
			echo "<div id='sideshoppingcart'><div id='shoppingcartcontents'>";
			echo nzshpcrt_shopping_basket_internals($cart);
			echo "</div></div>";
		}
	} else {
		if(($GLOBALS['nzshpcrt_activateshpcrt'] === true)) {
			$cart = $_SESSION['nzshpcrt_cart'];
			echo "<div id='shoppingcart'><div id='shoppingcartcontents'>";
			echo nzshpcrt_shopping_basket_internals($cart);
			echo "</div></div>";
		}
	}
  
  if($dont_add_input !== true) {
    if($input != '') {
      echo $input;
		}
	}
}
  

function nzshpcrt_shopping_basket_internals($cart,$quantity_limit = false, $no_title=false)
  {
  global $wpdb;
  
  if(get_option('permalink_structure') != '') {
    $seperator ="?";
    } else {
    $seperator ="&amp;";
    }    
  
  if(get_option('show_sliding_cart') == 1) {
    if(is_numeric($_SESSION['slider_state'])) {
      if($_SESSION['slider_state'] == 0) { $collapser_image = 'plus.png'; } else { $collapser_image = 'minus.png'; }
      $fancy_collapser = "<a href='#' onclick='return shopping_cart_collapser()' id='fancy_collapser_link'><img src='".WPSC_URL."/images/$collapser_image' title='' alt='' id='fancy_collapser' /></a>";
		} else {
      if($_SESSION['nzshpcrt_cart'] == null) { $collapser_image = 'plus.png'; } else { $collapser_image = 'minus.png'; }
      $fancy_collapser = "<a href='#' onclick='return shopping_cart_collapser()' id='fancy_collapser_link'><img src='".WPSC_URL."/images/$collapser_image' title='' alt='' id='fancy_collapser' /></a>";
		}
	} else { $fancy_collapser = ""; }
  
  $current_url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
 if(get_option('cart_location') == 4) {
   $no_title = true;
 }
 switch(get_option('cart_location'))  {
    case 1:
    if($no_title !== true) {
			$output .= "<h2>".TXT_WPSC_SHOPPINGCART." $fancy_collapser</h2>";
			$output .="<span id='alt_loadingindicator'><img id='alt_loadingimage' src='".WPSC_URL."/images/indicator.gif' alt='Loading' title='Loading' /> ".TXT_WPSC_UDPATING."...</span></strong><br />";
    }
    $spacing = "";
    break;
    
    case 3:
    if($no_title !== true) {
      $output .= "<strong class='cart_title'>".TXT_WPSC_SHOPPINGCART." $fancy_collapser</strong>";
    }
    //$output .= "<a href='#' onclick='return shopping_cart_collapser()' class='cart_title' id='fancy_collapser_link'>".TXT_WPSC_SHOPPINGCART." </a>";
    break;
    
    case 4:
    if($no_title !== true) {
			if(is_array($GLOBALS['registered_sidebars'])) {
				$sidebar_args = end($GLOBALS['registered_sidebars']);
			}	else{
				$sidebar_args['before_title'] = "<h2>";
				$sidebar_args['after_title'] = "</h2>";
			}
			$output .= $sidebar_args['before_title'] . TXT_WPSC_SHOPPINGCART." $fancy_collapser" . $sidebar_args['after_title'];
    }
    break;
    
    default:
    
    if($no_title !== true) {
			//$output .= "<strong class='cart_title'>".TXT_WPSC_SHOPPINGCART." $fancy_collapser</strong>";
    }
    break;
	}
 $cart_count = 0;
 foreach((array)$cart as $item) {
   $cart_count += $item->quantity;
	}
 
 $output .= "<div id='sliding_cart'>";
  if($cart != null) {
    if(($quantity_limit == true) || ($_SESSION['out_of_stock'] == true)) {
      $output .= TXT_WPSC_NUMBEROFITEMS.": &nbsp;&nbsp;".$cart_count."<br /><br />";
      $output .= TXT_WPSC_NOMOREAVAILABLE."<br /><br />";
      $_SESSION['out_of_stock'] = false;
		} else {
			$output .= TXT_WPSC_NUMBEROFITEMS.": &nbsp;&nbsp;".$cart_count."<br /><br />";
		}
    

    $output .= "<table class='shoppingcart'>\n\r";
    $output .= "<tr><th>".TXT_WPSC_PRODUCT."</th><th>".TXT_WPSC_QUANTITY_SHORT."</th><th>".TXT_WPSC_PRICE."</th></tr>\n\r";
    $all_donations = true;
    $all_no_shipping = true;
    $tax = 0;

//written by allen
	$merchant_id = get_option('google_id');  // Your Merchant ID
	$merchant_key = get_option('google_key');  // Your Merchant Key
	$server_type = get_option('google_server_type');
	$currency = get_option('google_cur');
	if (get_option('payment_gateway') == 'google') {
		$google_cart = new GoogleCart($merchant_id, $merchant_key, $server_type, $currency);
	}
    foreach($cart as $cart_item) {
      $product_id = $cart_item->product_id;
      $quantity = $cart_item->quantity;
	
      //echo("<pre>".print_r($cart_item->product_variations,true)."</pre>");
      $product = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
      if($product['donation'] == 1) {
        if (get_option('payment_gateway') == 'google') {
					$google_unit_price = $cart_item->donation_price;
	      }
        $price = $quantity * $cart_item->donation_price;
			} else {
        if (get_option('payment_gateway') == 'google') {
					$google_unit_price = calculate_product_price($product_id, $cart_item->product_variations,'stay',$cart_item->extras);
				}
        $price = $quantity * calculate_product_price($product_id, $cart_item->product_variations,'stay',$cart_item->extras);
				if($product['notax'] != 1) {
					$tax += nzshpcrt_calculate_tax($price, $_SESSION['selected_country'], $_SESSION['selected_region']) - $price;
				}
        $all_donations = false;
			}
        
      if($product['no_shipping'] != 1) {
        $all_no_shipping = false;
			}
        
      if($_SESSION['delivery_country'] != null) {
        $total_shipping += nzshpcrt_determine_item_shipping($product['id'], $quantity, $_SESSION['delivery_country']);
			}

	$total += $price;
	//exit(utf8_encode('&trade;'));
	$product['name'] = str_replace("™","&trade;",$product['name']);
	$product['description'] = str_replace("™","&trade;",$product['description']);
	
	
			if (get_option('payment_gateway') == 'google') {
				$google_item = new GoogleItem(utf8_decode($product['name']),utf8_decode($product['description']), $quantity, $google_unit_price);
				$google_item->SetMerchantItemId($product['id']);
				
				$google_cart->SetMerchantCalculations(get_option('siteurl'),"false","false","false");
				//echo serialize($cart_item->product_variations);
				$google_item->SetMerchantPrivateItemData("some variations");
				$google_cart->AddItem($google_item);
			}
      $output .= "<tr>";
      if (get_option("hide_name_link")=='1') {
	      $output .= "<td>".$product['name']."</td>";
      } else {
        $output .= "<td><a href='".wpsc_product_url($product['id'])."' >".$product['name']."</a></td>";
      }
      $output .= "<td>".$quantity."</td>";
      $output .= "<td>".nzshpcrt_currency_display($price, 1)."</td>";
      $output .= "</tr>\n\r";
      }
	//google checkout stuff.
	// 	if (get_option('payment_gateway') == 'google') {
	// 		$google_shipping = new GoogleFlatRateShipping("Flat Rate Shipping", $total_shipping);
	// 		$Gfilter = new GoogleShippingFilters();
	// 		$google_checkout_shipping=get_option("google_shipping_country");
	// 		$google_shipping_country_ids = implode(",",(array)$google_checkout_shipping);
	// 		if($google_shipping_country_ids != null) {
	// 			$google_shipping_country = $wpdb->get_var("SELECT isocode FROM ".$wpdb->prefix."currency_list WHERE id IN (".$google_shipping_country_ids.")");
	// 		}
	// 		$Gfilter->AddAllowedPostalArea($google_shipping_country);
	// 		$google_shipping->AddShippingRestrictions($Gfilter);
	// 		$google_cart->AddShipping($google_shipping);
	// 
	// 		if ($_SESSION['selected_country']=='US'){
	// 			$tax_rule = new GoogleDefaultTaxRule(0.05);
	// 			$state_name = $wpdb->get_var("SELECT name FROM ".$wpdb->prefix."region_tax WHERE id='".$_SESSION['selected_region']."'");
	// 			$tax_rule->SetStateAreas(array($state_name));
	// 			$tax_rule->AddPostalArea($google_shipping_country);
	// 			$google_cart->AddDefaultTaxRules($tax_rule);
	// 		}
	// 	}
	//end of google checkout.
    $output .= "</table>";
    if($_SESSION['delivery_country'] != null)
      {
      $total_shipping = nzshpcrt_determine_base_shipping($total_shipping, $_SESSION['delivery_country']);
      $output .= "<strong>".TXT_WPSC_SUBTOTAL.":</strong> &nbsp;&nbsp;".nzshpcrt_currency_display(($total), 1)."<br />";
      if((get_option('do_not_use_shipping') != 1) && ($all_donations == false) && ($all_no_shipping == false))
        {
        $output .= "<strong>".TXT_WPSC_POSTAGE.":</strong> &nbsp;&nbsp;".nzshpcrt_currency_display($total_shipping, 1)."<br />";
        }
      if($tax > 0)
        {
        $output .= "<strong>".TXT_WPSC_TAX.":</strong> &nbsp;&nbsp;".nzshpcrt_currency_display($tax, 1)."<br />";
        }
      if($_SESSION['coupon_num']){
		$overall_total = nzshpcrt_overall_total_price_numeric($_SESSION['selected_country'],true);
		$discount = $overall_total - nzshpcrt_apply_coupon($overall_total,$_SESSION['coupon_num']);
		$total_after_discount = $overall_total-$discount;
		$_SESSION['wpsc_discount']= $discount;
		} else {
		$_SESSION['wpsc_discount']= 0;
		}
	if($discount > 0) {
		$output .= "<strong>".TXT_WPSC_DISCOUNT.":</strong> &nbsp;&nbsp;".nzshpcrt_currency_display($discount, 1)."<br />";
	}
      $output .= "<strong>".TXT_WPSC_TOTAL.":</strong> &nbsp;&nbsp;".nzshpcrt_overall_total_price($_SESSION['delivery_country'],true)."<br /><br />";
      }
      else
        {
		if($discount > 0) {
			$output .= "<strong>".TXT_WPSC_DISCOUNT.":</strong> &nbsp;&nbsp;".nzshpcrt_currency_display($discount, 1)."<br />";
		}
        $output .= "<strong>".TXT_WPSC_TOTAL.":</strong> &nbsp;&nbsp;".nzshpcrt_overall_total_price($_SESSION['selected_country'],true)."<br /><br />";
	//$output .= "<strong>".TXT_WPSC_PRICEAFTERDISCOUNT.":</strong> &nbsp;&nbsp;".nzshpcrt_currency_display($total_after_discount, 1)."<br /><br />";
        }
    if(get_option('permalink_structure') != '')
      {
      $seperator ="?";
      }
      else
         {
         $seperator ="&amp;";
         }
         
         
    if ($discount > 0) {
			if (get_option('payment_gateway') == 'google') {
				$google_item = new GoogleItem(utf8_decode("Coupon Code: '".$_SESSION['coupon_num']."'"), utf8_decode("A coupon redeem"),1,	-$discount); 
				$google_item->SetMerchantPrivateItemData("Coupon Deduction");
				$google_cart->AddItem($google_item);
			}
		}
	 if (get_option('payment_gateway') == 'google') {
		 if (!$total_shipping) $total_shipping = 0;
		 $local_shipping_price= nzshpcrt_determine_base_shipping($total_shipping, get_option('base_country'));
		 $google_local_shipping = new GoogleFlatRateShipping("Local Shipping", $local_shipping_price);
		 $international_shipping_price= nzshpcrt_determine_base_shipping($total_shipping, get_option('base_country')."-");
		 //$international_shipping_price=(int)$international_shipping_price-(int)$google_local_shipping;
		 $google_international_shipping = new GoogleFlatRateShipping("International Shipping", $international_shipping_price);
		 $Gfilter2 = new GoogleShippingFilters();
		 $Gfilter = new GoogleShippingFilters();
		 $google_checkout_shipping=get_option("google_shipping_country");
		 $google_shipping_country_ids = implode(",",(array)$google_checkout_shipping);
		 $google_shipping_country = $wpdb->get_results("SELECT isocode FROM ".$wpdb->prefix."currency_list WHERE id IN (".$google_shipping_country_ids.")", ARRAY_A);
		 //exit(print_r($google_shipping_country,1));
		foreach ((array)$google_shipping_country as $country) {
			$Gfilter->AddAllowedPostalArea($country['isocode']);
			$Gfilter2->AddAllowedPostalArea($country['isocode']);
			$Gfilter2->AddExcludedPostalArea(get_option('base_country'));
			if ($country['isocode'] != get_option('base_country')) {
				 $Gfilter->AddExcludedPostalArea($country['isocode']);
			 }
		 }
		 $google_local_shipping->AddShippingRestrictions($Gfilter);
		 $google_international_shipping->AddShippingRestrictions($Gfilter2);
		 $google_cart->AddShipping($google_local_shipping);
		 $google_cart->AddShipping($google_international_shipping);
		
		 $local_tax = $wpdb->get_var("SELECT tax from ".$wpdb->prefix."currency_list WHERE isocode='".get_option('base_country')."'");
		 //exit($local_tax);
		 $tax_rule = new GoogleDefaultTaxRule($local_tax/100);
		 
		 if (($_SESSION['selected_country']=='US') && (get_option('base_country')=='US')){
			 $state_name = $wpdb->get_var("SELECT name FROM ".$wpdb->prefix."region_tax WHERE id='".$_SESSION['selected_region']."'");
			 //foreach ($state_name as $state)
			 $tax_rule->SetStateAreas(array($state_name));
		 } else {
			 $tax_rule->AddPostalArea(get_option('base_country'));
		 }
		$google_cart->AddDefaultTaxRules($tax_rule);
		
		$alter_tax_rule = new GoogleDefaultTaxRule(0.00);
		foreach ((array)$google_shipping_country as $country) {
			if (get_option('base_country') != $country['isocode'] )
				$alter_tax_rule->AddPostalArea($country['isocode']);
		}
		if ($alter_tax_rule != '')
			$google_cart->AddDefaultTaxRules($alter_tax_rule);
	 }

    $output .= "<a href='".get_option('product_list_url').$seperator."category=".$_GET['category']."&amp;cart=empty' onclick='emptycart();return false;'>".TXT_WPSC_EMPTYYOURCART."</a><br />";
    $output .= "<a href='".get_option('shopping_cart_url')."'>".TXT_WPSC_GOTOCHECKOUT."</a><br />";
	if (get_option('payment_gateway') == 'google') {
		if (get_option('google_button_size') == '0'){
			$google_button_size = 'BIG';
		} elseif(get_option('google_button_size') == '1') {
			$google_button_size = 'MEDIUM';
		} elseif(get_option('google_button_size') == '2') {
			$google_button_size = 'SMALL';
		}
		$google_cart->SetMerchantCalculations(get_option('siteurl'),"false","false");
		$google_cart->SetRequestBuyerPhone("true");
		$google_session = md5(time());
		$_SESSION['google_session']=$google_session;
		if(!preg_match("/\?/",get_option('product_list_url'))) {
			$seperator ="?";
		} else {
			$seperator ="&";
		}
		$continueshoppingurl = get_option('product_list_url').$seperator."action=bfg&session=".$google_session;
		

		$google_cart->SetContinueShoppingUrl($continueshoppingurl);
		$google_cart->SetEditCartUrl(get_option('shopping_cart_url'));
		$_SESSION['google_shopping_cart']=serialize($google_cart);
		$output .= "<br>".$google_cart->CheckoutButtonCode($google_button_size);
	}
    //$output .= "<a href='".get_option('product_list_url')."'>".TXT_WPSC_CONTINUESHOPPING."</a>";
    }
    else
      {
      $output .= $spacing;
      $output .= TXT_WPSC_YOURSHOPPINGCARTISEMPTY.".<br />";
      $output .= "<a href='".get_option('product_list_url')."'>".TXT_WPSC_VISITTHESHOP."</a>";
      }
  
 $output .= "</div>";
  return $output;
  }
  
function wpsc_country_region_list($form_id = null, $ajax = false , $selected_country = null, $selected_region = null )
  {
  global $wpdb;
  if($selected_country == null)
    {
    $selected_country = get_option('base_country');
    }
  if($selected_region == null)
    {
    $selected_region = get_option('base_region');
    }
  if($form_id != null)
    {
    $html_form_id = "region_country_form_$form_id";
    }
    else
      {
      $html_form_id = 'region_country_form';
      }
  $country_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."currency_list` ORDER BY `country` ASC",ARRAY_A);
  $output .= "<div id='$html_form_id'>\n\r";
  $output .= "<select name='collected_data[".$form_id."][0]' class='current_country' onchange='set_billing_country(\"$html_form_id\", \"$form_id\");' >\n\r";
  foreach ($country_data as $country)
    {
    $selected ='';
    if($selected_country == $country['isocode'])
      {
      $selected = "selected='true'";
      }
    $output .= "<option value='".$country['isocode']."' $selected>".$country['country']."</option>\n\r";
    }  
  $output .= "</select>\n\r";
  
  
  $region_list = $wpdb->get_results("SELECT `".$wpdb->prefix."region_tax`.* FROM `".$wpdb->prefix."region_tax`, `".$wpdb->prefix."currency_list`  WHERE `".$wpdb->prefix."currency_list`.`isocode` IN('".$selected_country."') AND `".$wpdb->prefix."currency_list`.`id` = `".$wpdb->prefix."region_tax`.`country_id`",ARRAY_A) ;
    $output .= "<div id='region_select_$form_id'>";
    if($region_list != null)
      {
      $output .= "<select name='collected_data[".$form_id."][1]' class='current_region' onchange='set_billing_country(\"$html_form_id\", \"$form_id\");'>\n\r";
      //$output .= "<option value=''>None</option>";
      foreach($region_list as $region)
        {
        if($selected_region == $region['id'])
          {
          $selected = "selected='true'";
          }
          else
            {
            $selected = "";
            }
        $output .= "<option value='".$region['id']."' $selected>".$region['name']."</option>\n\r";
        }
      $output .= "</select>\n\r";
      }
  $output .= "</div>";
  $output .= "</div>\n\r";
  return $output;
  }
?>