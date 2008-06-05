<?php
function nzshpcrt_overall_total_price($country_code = null, $for_display = false, $no_discount = false, $total_checkbox=0) {
    /*
     * Determines the total in the shopping cart, adds the tax and shipping if a country code is supplied and adds the discount of a coupon code is present
     * Adds a dollar sign and information if there is no tax and shipping if $for_display is true
     */
    global $wpdb;
    $cart  =& $_SESSION['nzshpcrt_cart'];
    $total_quantity =0;
    $total_weight = 0;
    $all_donations = true;
    $all_no_shipping = true;
    foreach($cart as $cart_item)
      {
      $product_id = $cart_item->product_id;
      $quantity = $cart_item->quantity;
      $product_variations = $cart_item->product_variations;
      $extras = $cart_item->extras;
      $extras_count=count($extras);
      $raw_price = 0;
      $variation_count = count($product_variations);
      if($variation_count > 0)
        {
        foreach($product_variations as $product_variation)
          {
          $value_id = $product_variation;
          $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
          }
        }
	
      //$total_quantity += $quantity;
      $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id` = '$product_id' LIMIT 1";
      $product = $wpdb->get_row($sql,ARRAY_A);
      
      if($product['donation'] == 1)
        {
        $price = $quantity * $cart_item->donation_price;
        }
        else
        {
        $price = $quantity * calculate_product_price($product_id, $product_variations,'stay',$extras);
        if($country_code != null)
          {
          if($product['notax'] != 1)
            {
            $price = nzshpcrt_calculate_tax($price, $_SESSION['selected_country'], $_SESSION['selected_region']);
            }
          $shipping = nzshpcrt_determine_item_shipping($product_id, $quantity, $country_code);
          $price += $shipping;
          }
        $all_donations = false;
        }
      if($product['no_shipping'] != 1) {
        $all_no_shipping = false;
        }
// 	if ($extras_count>0) {
// 		foreach($extras as $extra) {
// 		$price+=$wpdb->get_var("SELECT `price` FROM `".$wpdb->prefix."extras_values_associations` WHERE `product_id` = '".$product_id."' AND `value_id` = '".$extra."' LIMIT 1");
// 		}
// 	}
      $total += $price;
      }

    if(($country_code != null) && ($all_donations == false) && ($all_no_shipping == false)) {
      //echo $_SESSION['selected_country'];
      //exit(nzshpcrt_determine_base_shipping(0, $country_code));
      $total +=  nzshpcrt_determine_base_shipping(0, $_SESSION['delivery_country']);
      }
		if(!empty($_SESSION['coupon_num']) && ($no_discount !== true)){
			$total += nzshpcrt_apply_coupon($total,$_SESSION['coupon_num']) - $total ;
		}

    if($for_display === true) {
      $total = nzshpcrt_currency_display($total,1);
      if(($country_code == null) && (get_option('add_plustax') == 1)) {
        $total .= " + ".TXT_WPSC_POSTAGE_AND_TAX;
			}
		}
    return $total;
    }
	
	//written by allen
	
	function nzshpcrt_overall_total_price_numeric($country_code = null, $for_display = false)
    {
    /*
     * Determines the total in the shopping cart, adds the tax and shipping if a country code is supplied
     * Adds a dollar sign and information if there is no tax and shipping if $for_display is true
     */
    global $wpdb;
    $cart  =& $_SESSION['nzshpcrt_cart'];
    $total_quantity =0;
    $total_weight = 0;
    $all_donations = true;
    $all_no_shipping = true;
    foreach($cart as $cart_item)
      {
      $product_id = $cart_item->product_id;
      $quantity = $cart_item->quantity;
      $product_variations = $cart_item->product_variations;
      $raw_price = 0;
      $variation_count = count($product_variations);
      if($variation_count > 0)
        {
        foreach($product_variations as $product_variation)
          {
          $value_id = $product_variation;
          $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
          }
        }
      //$total_quantity += $quantity;
      $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id` = '$product_id' LIMIT 1";
      $product = $wpdb->get_row($sql,ARRAY_A);
      
      if($product['donation'] == 1)
        {
        $price = $quantity * $cart_item->donation_price;
        }
        else
        {
        $price = $quantity * calculate_product_price($product_id, $product_variations);
        if($country_code != null)
          {
          if($product['notax'] != 1)
            {
            $price = nzshpcrt_calculate_tax($price, $_SESSION['selected_country'], $_SESSION['selected_region']);
            }
          $shipping = nzshpcrt_determine_item_shipping($product_id, $quantity, $country_code);
          $price += $shipping;
          }
        $all_donations = false;
        }
      if($product['no_shipping'] != 1) {
        $all_no_shipping = false;
        }
       

        
      $total += $price;
      }
    
    if(($country_code != null) && ($all_donations == false) && ($all_no_shipping == false)) {
      $total +=  nzshpcrt_determine_base_shipping(0, $country_code);
		}
    return $total;
	}
	
	//end of written by allen
  
  function nzshpcrt_calculate_tax($price, $country, $region)
    {
    global $wpdb;
    $country_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."currency_list` WHERE `isocode` IN('".get_option('base_country')."') LIMIT 1",ARRAY_A);
    if(($country_data['has_regions'] == 1))
      {
      $region_data = $wpdb->get_row("SELECT `".$wpdb->prefix."region_tax`.* FROM `".$wpdb->prefix."region_tax` WHERE `".$wpdb->prefix."region_tax`.`country_id` IN('".$country_data['id']."') AND `".$wpdb->prefix."region_tax`.`id` IN('".$region."') ",ARRAY_A) ;
       $tax_percentage =  $region_data['tax'];
      }
      else
        {
        $tax_percentage =  $country_data['tax'];
        }
    $add_tax = false;
    if($country == get_option('base_country'))
      {
	$add_tax = true;
      }
    if($add_tax === true)
      {
      $price = $price * (1 + ($tax_percentage/100));
      }
    return $price;
    }
  
  function nzshpcrt_find_total_price($purchase_id,$country_code)
    {
    global $wpdb;
    if(is_numeric($purchase_id))
      {
      $purch_sql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `id`='".$purchase_id."'";
      $purch_data = $wpdb->get_row($purch_sql,ARRAY_A) ;

      $cartsql = "SELECT * FROM `".$wpdb->prefix."cart_contents` WHERE `purchaseid`=".$purchase_id."";
      $cart_log = $wpdb->get_results($cartsql,ARRAY_A) ; 
      if($cart_log != null)
        {
        $all_donations = true;
        $all_no_shipping = true;
        foreach($cart_log as $cart_row)
          {
          $productsql= "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`=".$cart_row['prodid']."";
          $product_data = $wpdb->get_results($productsql,ARRAY_A); 
        
          $variation_sql = "SELECT * FROM `".$wpdb->prefix."cart_item_variations` WHERE `cart_id`='".$cart_row['id']."'";
          $variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
          $variation_count = count($variation_data);
          $price = ($cart_row['price'] * $cart_row['quantity']);          
          
          if($purch_data['shipping_country'] != '')
            {
            $country_code = $purch_data['shipping_country'];
            }
            
          if($cart_row['donation'] == 1) {
            $shipping = 0;
            } else {
            $all_donations = false;
            }
          
          if($cart_row['no_shipping'] == 1) {
            $shipping = 0;
            } else {
            $all_no_shipping = false;
            }

          if(($cart_row['donation'] != 1) && ($cart_row['no_shipping'] != 1))
            {
            $shipping = nzshpcrt_determine_item_shipping($cart_row['prodid'], $cart_row['quantity'], $country_code);
            }
          $endtotal += $shipping + $price;
          }
        if(($all_donations == false) && ($all_no_shipping == false))
          {
          $endtotal += nzshpcrt_determine_base_shipping(0, $country_code);
          }
        
        if($purch_data['discount_value'] > 0) {
					$endtotal -= $purch_data['discount_value'];
					if($endtotal < 0) {
						$endtotal = 0;
					}
        }
          
        }
      return $endtotal;
      }
    }
  //written by Allen
  function nzshpcrt_apply_coupon($price,$coupon_num){
	global $wpdb;
	$now = date("Y-m-d H:i:s");
	$now = strtotime($now);
	//echo $now;
	if ($coupon_num!=NULL) {
		$coupon_sql = "SELECT * FROM `".$wpdb->prefix."wpsc_coupon_codes` WHERE coupon_code='".$coupon_num."' LIMIT 1";
		$coupon_data = $wpdb->get_results($coupon_sql,ARRAY_A);
		$coupon_data = $coupon_data[0];
	}
	if ($coupon_data['active']=='1'){
		if ((strtotime($coupon_data['start']) < $now)&&(strtotime($coupon_data['expiry']) > $now)){

			if ($coupon_data['is-percentage']=='1'){
				$price = $price*(1-$coupon_data['value']/100);
			} else {
			  if ($coupon_data['every_product']=='0') {
					$price = $price-$coupon_data['value'];
				} else {
					$cart = $_SESSION['nzshpcrt_cart'];
					$total_quantity=0;
					
					foreach($cart as $product) {
						$total_quantity+=$product->quantity;
					}
					$price = $price-$coupon_data['value']*$total_quantity;
				}

			}
		} else {
			return $price;
		}
	}
	if($price<0){
		$price = 0;
	}
	return $price;
   }
  //End of written by Allen  
  
  function nzshpcrt_determine_base_shipping($per_item_shipping, $country_code) {    
    global $wpdb;
    if(get_option('do_not_use_shipping') != 1) {
      if($country_code == get_option('base_country')) {
        $base_shipping = get_option('base_local_shipping');
      } else {
				$base_shipping = get_option('base_international_shipping');
			}
      $shipping = $base_shipping + $per_item_shipping;
		} else {
      $shipping = 0;
		}
    return $shipping;
	}
    
  function nzshpcrt_determine_item_shipping($product_id, $quantity, $country_code)
    {    
    global $wpdb;
    if(is_numeric($product_id) && (get_option('do_not_use_shipping') != 1)) {
      $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='$product_id' LIMIT 1";
      $product_list = $GLOBALS['wpdb']->get_row($sql,ARRAY_A) ;
      if($product_list['no_shipping'] == 0) {
        //if the item has shipping
        if($country_code == get_option('base_country')) {
          $additional_shipping = $product_list['pnp'];
          } else {
          $additional_shipping = $product_list['international_pnp'];
          }          
        $shipping = $quantity * $additional_shipping;
        } else {
        //if the item does not have shipping
        $shipping = 0;
        }
      } else {
      //if the item is invalid or all items do not have shipping
      $shipping = 0;
      }
    return $shipping;    
    }

function nzshpcrt_currency_display($price_in, $tax_status, $nohtml = false, $id = false, $no_dollar_sign = false)
  {
  /*
   * This now ignores tax status, but removing it entirely will probably have to wait for the inevitable yet indefinately delayed total rewrite, woot
   */
  global $wpdb;
  $currency_sign_location = get_option('currency_sign_location');
  $currency_type = get_option('currency_type');
  $currency_data = $wpdb->get_results("SELECT `symbol`,`symbol_html`,`code` FROM `".$wpdb->prefix."currency_list` WHERE `id`='".$currency_type."' LIMIT 1",ARRAY_A) ;
  $price_out = null;
  $currency_sign_location = get_option('currency_sign_location');
  $currency_type = get_option('currency_type');
  $currency_data = $wpdb->get_results("SELECT `symbol`,`symbol_html`,`code` FROM `".$wpdb->prefix."currency_list` WHERE `id`='".$currency_type."' LIMIT 1",ARRAY_A) ;
  $price_out = null;
  if(is_numeric($id))
    {
    
    }

  $price_out =  number_format($price_in, 2, '.', '');

  if($currency_data[0]['symbol'] != '')
    {    
    if($nohtml == false)
      {
      $currency_sign = $currency_data[0]['symbol_html'];
      }
      else
        {
        $currency_sign = $currency_data[0]['symbol'];
        }
    }
    else
      {
      $currency_sign = $currency_data[0]['code'];
      }

  switch($currency_sign_location)
    {
    case 1:
    $output = $price_out.$currency_sign;
    break;

    case 2:
    $output = $price_out.' '.$currency_sign;
    break;

    case 3:
    $output = $currency_sign.$price_out;
    break;

    case 4:
    $output = $currency_sign.'  '.$price_out;
    break;
    }

  if($nohtml == true)
    {
    $output = "".$output."";
    }
    else
      {
      $output = "<span class='pricedisplay'>".$output."</span>";
      }
      
  if($no_dollar_sign == true)
    {
    return $price_out;
    }
  return $output;
  }
  
function admin_display_total_price($start_timestamp = '', $end_timestamp = '')
  {
  global $wpdb;
  if(($start_timestamp != '') && ($end_timestamp != ''))
    {
    $sql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `processed` > '1' AND `date` BETWEEN '$start_timestamp' AND '$end_timestamp' ORDER BY `date` DESC";
    }
    else
      {
      $sql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `processed` > '1' AND `date` != ''";
      }
  $purchase_log = $wpdb->get_results($sql,ARRAY_A) ;
  $total = 0;
  if($purchase_log != null)
    {
    foreach($purchase_log as $purchase)
      {
      $country_sql = "SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".get_option('country_form_field')."' LIMIT 1";
      $country_data = $wpdb->get_results($country_sql,ARRAY_A);
      $country = $country_data[0]['value'];
      $total += nzshpcrt_find_total_price($purchase['id'],$country);
      }
    }
  return $total;
  }
  


function calculate_product_price($product_id, $variations = false, $pm='',$extras=false) {
  global $wpdb;
  $pm = '';  // PM override code lies here
  if(is_numeric($product_id)) {
    if(is_array($variations) && ((count($variations) >= 1) && (count($variations) <= 2))) {
      $variation_count = count($variations);
      $variations = array_values($variations);
		}
		if ($pm!='') {
			$checkb_sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id` = '".(int)$product_id."' LIMIT 1";
			$product_data = $wpdb->get_results($checkb_sql,ARRAY_A);
			if ($product_data[0]['special']=='1') {
				$std_price = $product_data[0]['price'] - $product_data[0]['special_price'];
			} else {
				$std_price = $product_data[0]['price'];
			}
			if ($pm=='stay') {
				if ((count($extras)>0)&&($extras!=null)) {
					foreach ($extras as $extra) {
						$price+=$wpdb->get_var("SELECT `price` FROM `".$wpdb->prefix."extras_values_associations` WHERE `product_id` = '".$product_id."' AND `value_id` = '".$extra."' LIMIT 1");
					}
				}
				return $std_price+$price;
			}
			$sql = "SELECT `price` FROM `".$wpdb->prefix."extras_values_associations` WHERE `product_id` = '".$product_id."' AND `extras_id` = '".$extras[0]."' LIMIT 1";
			
			if ($pm=='plus') {
				if ((count($extras)>0)&&($extras!=null)) {
					foreach ($extras as $extra) {
						$price+=$wpdb->get_var("SELECT `price` FROM `".$wpdb->prefix."extras_values_associations` WHERE `product_id` = '".$product_id."' AND `extras_id` = '".$extra."' LIMIT 1");
					}
				}
				return $std_price+$price;
			} elseif ($pm=='minus') {
				if ((count($extras)>0)&&($extras!=null)) {
					foreach ($extras as $extra) {
						$price+=$wpdb->get_var("SELECT `price` FROM `".$wpdb->prefix."extras_values_associations` WHERE `product_id` = '".$product_id."' AND `extras_id` = '".$extra."' LIMIT 1");
					}
				}
				return $std_price+$price;
			}			
			return $price;
		} else {
			if(($variation_count >= 1) && ($variation_count <= 2)) {
				switch($variation_count) {
					case 1:
					$sql = "SELECT `price` FROM `".$wpdb->prefix."variation_priceandstock` WHERE `product_id` IN ('".$product_id."') AND `variation_id_1` = '".$variations[0]."' AND `variation_id_2` = '0' LIMIT 1";
					break;
		
					case 2:
					$sql = "SELECT `price` FROM `".$wpdb->prefix."variation_priceandstock` WHERE `product_id` IN ('".$product_id."') AND ((`variation_id_1` = '".$variations[0]."' AND `variation_id_2` = '".$variations[1]."') OR (`variation_id_1` = '".$variations[1]."' AND `variation_id_2` = '".$variations[0]."')) LIMIT 1";
					break;
				}
				$price = $wpdb->get_var($sql);
				//exit("// $price $sql");
			} else {
				$sql = "SELECT `price`,`special`,`special_price` FROM `".$wpdb->prefix."product_list` WHERE `id`='".$product_id."' LIMIT 1";
				$product_data = $wpdb->get_row($sql,ARRAY_A);
				if($product_data['special_price'] > 0) {
					$price = $product_data['price'] - $product_data['special_price'];
				} else {
					$price = $product_data['price'];
				}
			}
		}
	} else {
		$price = false;
	}
  return $price;
}
  
function check_in_stock($id, $variations, $item_quantity = 1)
  {
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='".$id."' LIMIT 1";
  $item_data = $wpdb->get_row($sql,ARRAY_A);
  
  $item_stock = null;
  $variation_count = count($variations);
  if(($variation_count >= 1) && ($variation_count <= 2))
    {
    foreach($variations as $variation_id)
      {
      if(is_numeric($variation_id))
        {
        $variation_ids[] = $variation_id;
        }
      }
    if(count($variation_ids) == 2)
      {
      $variation_stock_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."variation_priceandstock` WHERE `product_id` = '".$id."' AND (`variation_id_1` = '".$variation_ids[0]."' AND `variation_id_2` = '".$variation_ids[1]."') OR (`variation_id_1` = '".$variation_ids[1]."' AND `variation_id_2` = '".$variation_ids[0]."') LIMIT 1",ARRAY_A);
      $item_stock = $variation_stock_data['stock'];
      }
      else if(count($variation_ids) == 1)
        {
        $variation_stock_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."variation_priceandstock` WHERE `product_id` = '".$id."' AND (`variation_id_1` = '".$variation_ids[0]."' AND `variation_id_2` = '0') LIMIT 1",ARRAY_A);

        $item_stock = $variation_stock_data['stock'];
        }
    }
    
  if($item_stock === null)
    {
    $item_stock = $item_data['quantity'];
    }
  
  if((($item_data['quantity_limited'] == 1) && ($item_stock > 0) && ($item_stock >= $item_quantity)) || ($item_data['quantity_limited'] == 0)) 
    {
    $output = true;
    }
    else
      {
      $output = false;
      }
  return $output;
  }
 
  
  
function wpsc_item_process_image($id='') {
  global $wpdb;
  if ($id=='') {
	  $id=$_POST['prodid'];
	}
	
  if(($_FILES['image'] != null) && preg_match("/\.(gif|jp(e)*g|png){1}$/i",$_FILES['image']['name']) && apply_filters( 'wpsc_filter_file', $_FILES['image']['tmp_name'] )) {
		//$active_signup = apply_filters( 'wpsc_filter_file', $_FILES['image']['tmp_name'] );
    if(function_exists("getimagesize")) {
			$image_name = basename($_FILES['image']['name']);
			if(is_file((WPSC_IMAGE_DIR.$image_name))) {
				$name_parts = explode('.',basename($image_name));
				$extension = array_pop($name_parts);
				$name_base = implode('.',$name_parts);
				$dir = glob(WPSC_IMAGE_DIR."$name_base*");
				
				foreach($dir as $file) {
					$matching_files[] = basename($file);
				}
				$image_name = null;
				$num = 2;
				//  loop till we find a free file name, first time I get to do a do loop in yonks
				do {
					$test_name = "{$name_base}-{$num}.{$extension}";
					if(!file_exists(WPSC_IMAGE_DIR.$test_name)) {
						$image_name = $test_name;
					} 						
					$num++;
				} while ($image_name == null);
			}			
			
			//exit("<pre>".print_r($image_name,true)."</pre>");
			
			$new_image_path = WPSC_IMAGE_DIR.$image_name;
			move_uploaded_file($_FILES['image']['tmp_name'], $new_image_path);
			$stat = stat( dirname( $new_image_path ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( $new_image_path, $perms );	
			
      switch($_POST['image_resize']) {
        case 2:
        $height = $_POST['height'];
        $width  = $_POST['width'];
        break;
        
        case 1:
        default:
        $height = get_option('product_image_height');
        $width  = get_option('product_image_width');
        break;
			}
			if(($_POST['image_resize'] == 3) && ($_FILES['thumbnailImage'] != null) && file_exists($_FILES['thumbnailImage']['tmp_name'])) {
				$imagefield='thumbnailImage';
				$image= image_processing($_FILES['thumbnailImage']['tmp_name'], (WPSC_THUMBNAIL_DIR.$image_name),null,null,$imagefield);
				$thumbnail_image = $image;
			} else {
				image_processing($new_image_path, (WPSC_THUMBNAIL_DIR.$image_name), $width, $height);
			}
			
			$updatelink_sql = "UPDATE `".$wpdb->prefix."product_list` SET `image` = '".$image_name."', `thumbnail_image` = '".$thumbnail_image."'  WHERE `id` = '$id'";
			$wpdb->query($updatelink_sql);

			$image = $wpdb->escape($image_name);
    } else {
			$image_name = basename($_FILES['image']['name']);
			if(is_file((WPSC_IMAGE_DIR.$image_name))) {
				$name_parts = explode('.',basename($image_name));
				$extension = array_pop($name_parts);
				$name_base = implode('.',$name_parts);
				$dir = glob(WPSC_IMAGE_DIR."$name_base*");
				
				foreach($dir as $file) {
					$matching_files[] = basename($file);
				}
				$image_name = null;
				$num = 2;
				//  loop till we find a free file name
				do {
					$test_name = "{$name_base}-{$num}.{$extension}";
					if(!file_exists(WPSC_IMAGE_DIR.$test_name)) {
						$image_name = $test_name;
					} 						
					$num++;
				} while ($image_name == null);
			}			
			$new_image_path = WPSC_IMAGE_DIR.$image_name;
			move_uploaded_file($_FILES['image']['tmp_name'], $new_image_path);
			$stat = stat( dirname( $new_image_path ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( $new_image_path, $perms );	
			$image = $wpdb->escape($image_name);
    }
  } else {
			$image_data = $wpdb->get_row("SELECT `id`,`image` FROM `".$wpdb->prefix."product_list` WHERE `id`='".(int)$_POST['prodid']."' LIMIT 1",ARRAY_A);
			//exit("<pre>".print_r($image_data,true)."</pre>");
			
		if(($_POST['image_resize'] == 3) && ($_FILES['thumbnailImage'] != null) && file_exists($_FILES['thumbnailImage']['tmp_name'])) {
			$imagefield='thumbnailImage';
			$image=image_processing($_FILES['thumbnailImage']['tmp_name'], WPSC_THUMBNAIL_DIR.$_FILES['thumbnailImage']['name'],null,null,$imagefield);
			$thumbnail_image = $image;
			$wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `thumbnail_image` = '".$thumbnail_image."' WHERE `id` = '".$image_data['id']."'");
			$stat = stat( dirname( (WPSC_THUMBNAIL_DIR.$image_data['image']) ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( (WPSC_THUMBNAIL_DIR.$image_data['image']), $perms );	
		}
		$image = false;
	}
  return $image;
}

function wpsc_item_process_file($mode = 'add') {
  global $wpdb;
	if(apply_filters( 'wpsc_filter_file', $_FILES['file']['tmp_name'] )) {
	  // initialise $idhash to null to prevent issues with undefined variables and error logs
	  $idhash = null;
		switch($mode) {
			case 'edit':
	    /* if we are editing, grab the current file and ID hash */ 
			$product_id = $_POST['prodid'];
			$fileid_data = $wpdb->get_results("SELECT `file` FROM `".$wpdb->prefix."product_list` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
			
			case 'add':
			default:
			/* if we are adding, make a new file row and get the ID of it */
			$timestamp = time();
			$query_results = $wpdb->query("INSERT INTO `".$wpdb->prefix."product_files` ( `filename`  , `mimetype` , `idhash` , `date` ) VALUES ( '', '', '', '$timestamp');");
			$fileid = $wpdb->get_var("SELECT LAST_INSERT_ID() FROM `".$wpdb->prefix."product_files`");
			break;
		}
	
		/* if there is no idhash, generate it */
		if($idhash == null) {
			$idhash = sha1($fileid);
			if($idhash == '') {
			  // if sha1 doesnt spit an error, but doesnt return anything either (it has done so on some servers)
				$idhash = md5($fileid);
			}
		}
		// if needed, we can add code here to stop hash doubleups in the unlikely event that they shoud occur
	
		$mimetype = wpsc_get_mimetype($_FILES['file']['tmp_name']);
		
		$filename = basename($_FILES['file']['name']);
	
		if(move_uploaded_file($_FILES['file']['tmp_name'],(WPSC_FILE_DIR.$idhash)))	{
			$stat = stat( dirname( (WPSC_FILE_DIR.$idhash) ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( (WPSC_FILE_DIR.$idhash), $perms );	
			if(function_exists("make_mp3_preview"))	{
				if($mimetype == "audio/mpeg" && (!isset($_FILES['preview_file']['tmp_name']))) {
				  // if we can generate a preview file, generate it (most can't due to sox being rare on servers and sox with MP3 support being even rarer), thus this needs to be enabled by editing code
					make_mp3_preview((WPSC_FILE_DIR.$idhash), (WPSC_PREVIEW_DIR.$idhash.".mp3"));
					$preview_filepath = (WPSC_PREVIEW_DIR.$idhash.".mp3");
				} else if(file_exists($_FILES['preview_file']['tmp_name'])) {    
					$preview_filename = basename($_FILES['preview_file']['name']);
					$preview_mimetype = wpsc_get_mimetype($_FILES['preview_file']['tmp_name']);
					copy($_FILES['preview_file']['tmp_name'], (WPSC_PREVIEW_DIR.$preview_filename));
					$preview_filepath = (WPSC_PREVIEW_DIR.$preview_filename);
					$wpdb->query("UPDATE `".$wpdb->prefix."product_files` SET `preview` = '".$wpdb->escape($preview_filename)."', `preview_mimetype` = '".$preview_mimetype."' WHERE `id` = '$fileid' LIMIT 1");
				}
				$stat = stat( dirname($preview_filepath));
				$perms = $stat['mode'] & 0000666;
				@ chmod( $preview_filepath, $perms );	
			}
			$wpdb->query("UPDATE `".$wpdb->prefix."product_files` SET `filename` = '".$wpdb->escape($filename)."', `mimetype` = '$mimetype', `idhash` = '$idhash' WHERE `id` = '$fileid' LIMIT 1");
		}
		if($mode == 'edit') {			
      //if we are editing, update the file ID in the product row, this cannot be done for add because the row does not exist yet.
      $wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `file` = '$fileid' WHERE `id` = '$product_id' LIMIT 1");
		}
		return $fileid;
  } else {
		return false;
  }
}

function wpsc_item_reassign_file($selected_product_file, $mode = 'add') {
  global $wpdb;
	// initialise $idhash to null to prevent issues with undefined variables and error logs
	$idhash = null;
	if($mode == 'edit') {
		/* if we are editing, grab the current file and ID hash */ 
		$product_id = (int)$_POST['prodid'];
		if($selected_product_file == '.none.') {
			// unlikely that anyone will ever upload a file called .none., so its the value used to signify clearing the product association
			$wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `file` = '0' WHERE `id` = '$product_id' LIMIT 1");
			return null;
		}
		
		// if we already use this file, there is no point doing anything more.
		$current_fileid = $wpdb->get_var("SELECT `file` FROM `".$wpdb->prefix."product_list` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
		if($current_fileid > 0) {
			$current_file_data = $wpdb->get_row("SELECT `id`,`idhash` FROM `".$wpdb->prefix."product_files` WHERE `id` = '$current_fileid' LIMIT 1",ARRAY_A);
			if(basename($selected_product_file) == $file_data['idhash']) {
				return $current_fileid;
			}
		}
	}

	
	$selected_product_file = basename($selected_product_file);
	if(file_exists(WPSC_FILE_DIR.$selected_product_file)) {
		$timestamp = time();
		$file_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_files` WHERE `idhash` IN('".$wpdb->escape($selected_product_file)."') LIMIT 1", ARRAY_A);
		$fileid = (int)$file_data['id'];
		if($fileid < 1) { // if the file does not have a database row, add one.
		  $mimetype = wpsc_get_mimetype(WPSC_FILE_DIR.$selected_product_file);
		  $filename = $idhash = $selected_product_file;
			$timestamp = time();
			$wpdb->query("INSERT INTO `{$wpdb->prefix}product_files` ( `filename`  , `mimetype` , `idhash` , `date` ) VALUES ( '{$filename}', '{$mimetype}', '{$idhash}', '{$timestamp}');");
			$fileid = $wpdb->get_var("SELECT `id` FROM `".$wpdb->prefix."product_files` WHERE `date` = '{$timestamp}' AND `filename` IN ('{$filename}')");
		}
		if($mode == 'edit') {
      //if we are editing, update the file ID in the product row, this cannot be done for add because the row does not exist yet.
      $wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `file` = '$fileid' WHERE `id` = '$product_id' LIMIT 1");
		}
	}	
	return $fileid;
}

function wpsc_get_mimetype($file) {
	if(file_exists($file)) {
		if(function_exists('finfo_open') && function_exists('finfo_file')) { 
			// fileinfo apparently works best, wish it was included with PHP by default
			$finfo_handle = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo_handle,$file);
		} else if(function_exists('mime_content_type')) {
			//obsolete, but probably second best due to completeness
			$mimetype = mime_content_type($file); 
		} else {
			//included with plugin, uses the extention, limited and odd list, last option
			$mimetype_class = new mimetype();
			$mimetype = $mimetype_class->getType($file);
		}
	} else {
		$mimetype = false;
	}
	return $mimetype;
}

?>