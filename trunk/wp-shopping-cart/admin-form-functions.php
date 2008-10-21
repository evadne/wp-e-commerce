<?php  
function nzshpcrt_getproductform($prodid)
  {
  global $wpdb,$nzshpcrt_imagesize_info;
  $variations_processor = new nzshpcrt_variations;
 /*
  * makes the product form
  * has functions inside a function
  */ 
  function brandslist($current_brand = '') {
    global $wpdb;
    $options = "";
    //$options .= "<option value=''>".TXT_WPSC_SELECTACATEGORY."</option>\r\n";
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_brands` WHERE `active`='1' ORDER BY `id` ASC",ARRAY_A);
    $options .= "<option  $selected value='0'>".TXT_WPSC_SELECTABRAND."</option>\r\n";
    foreach((array)$values as $option) {
      if($current_brand == $option['id']) {
        $selected = "selected='selected'";
			}
      $options .= "<option  $selected value='".$option['id']."'>".$option['name']."</option>\r\n";
      $selected = "";
		}
    $concat .= "<select name='brand'>".$options."</select>\r\n";
    return $concat;
	}
  $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`=$prodid LIMIT 1";
  $product_data = $wpdb->get_results($sql,ARRAY_A);
  $product = $product_data[0];
  $sql = "SELECT * FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `product_id`=$prodid AND meta_key='external_link' LIMIT 1";
  $meta_data = $wpdb->get_results($sql,ARRAY_A);
  $product['external_link'] = $meta_data[0]['meta_value'];
  $sql = "SELECT * FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `product_id`=$prodid AND meta_key='merchant_notes' LIMIT 1";
  $meta_data = $wpdb->get_results($sql,ARRAY_A);
  $product['merchant_notes'] = $meta_data[0]['meta_value'];
  $engrave = get_product_meta($prodid,'engraved',true);
  $can_have_uploaded_image = get_product_meta($prodid,'can_have_uploaded_image',true);
  
   $table_rate_price = get_product_meta($prodid,'table_rate_price',true);
//    exit("<pre>".print_r($table_rate_price,1)."</pre>");
  if(function_exists('wp_insert_term')) {
		$term_relationships = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."term_relationships WHERE object_id = $prodid", ARRAY_A);
		
		foreach ((array)$term_relationships as $term_relationship) {
			$tt_ids[] = $term_relationship['term_taxonomy_id'];
		}
		foreach ((array)$tt_ids as $tt_id) {
			$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."term_taxonomy WHERE term_taxonomy_id = ".$tt_id." AND taxonomy = 'product_tag'", ARRAY_A);
			$term_ids[] = $results[0]['term_id'];
		}
		foreach ((array)$term_ids as $term_id ) {
			if ($term_id != NULL){
			$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."terms WHERE term_id=".$term_id." ",ARRAY_A);
			$tags[] = $results[0]['name'];
			}
		}
		if ($tags != NULL){ 
			$imtags = implode(',', $tags);
		}
  }

  $check_variation_value_count = $wpdb->get_var("SELECT COUNT(*) as `count` FROM `".$wpdb->prefix."variation_values_associations` WHERE `product_id` = '".$product['id']."'");
  
  
	$current_user = wp_get_current_user();
	$closed_postboxes = (array)get_usermeta( $current_user->ID, 'closedpostboxes_editproduct');
  
  $output .= "        <table class='product_editform'>\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td class='itemfirstcol'>\n\r";
  $output .= TXT_WPSC_PRODUCTNAME.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td class='itemformcol'>\n\r";
  
	$output .= "        <div class='admin_product_name'>\n\r";
  $output .= "          <input  size='30' type='text' class='text'  name='title' value='".htmlentities(stripslashes($product['name']), ENT_QUOTES, 'UTF-8')."' />\n\r";
	$output .= "				   <a href='#' class='shorttag_toggle'></a>\n\r";
	$output .= "				   <div class='admin_product_shorttags'>\n\r";
	$output .= "				     <dl>\n\r";
// 	$output .= "				       <dt>Embed Product:</dt><dd>[buy_now_button={$product['id']}]</dd>\n\r";
// 	$output .= "				       <dt>Buy Now Button:</dt><dd></dd>\n\r";
	$output .= "				       <dt>Buy Now Shortcode:</dt><dd>[buy_now_button={$product['id']}]</dd>\n\r";
	$output .= "				       <dt>Buy Now PHP:</dt><dd>&lt;?php echo wpsc_buy_now_button({$product['id']}); ?&gt;</dd>\n\r";
	$output .= "				     </dl>\n\r";
	$output .= "				     <br clear='both' />\n\r";
	
	$output .= "				   </div>\n\r";
	$output .= "        </div>\n\r";
        
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  
  $output .= "          <tr>\n\r";
  $output .= "            <td class='itemfirstcol'>\n\r";
  $output .="<abbr alt='".TXT_WPSC_SKU_FULL."' title='". TXT_WPSC_SKU_FULL."' >".TXT_WPSC_SKU."</abbr>";
  $output .= "            </td>\n\r";
  $output .= "            <td class='itemformcol'>\n\r";
  $sku = get_product_meta($product['id'], 'sku');
  $sku = $sku[0];
  $output .= "<input  size='30' type='text' class='text'  name='productmeta_values[sku]' value='".htmlentities(stripslashes($sku), ENT_QUOTES, 'UTF-8')."' />\n\r";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  $output .= "          <tr>\n\r";
  $output .= "            <td class='itemfirstcol'>\n\r";
  $output .= TXT_WPSC_PRODUCTDESCRIPTION.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td class='itemformcol'>\n\r";
  $output .= "<textarea name='description' cols='40' rows='8' >".stripslashes($product['description'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  $output .= "          <tr>\n\r";
  $output .= "            <td class='itemfirstcol'>\n\r";
  $output .= TXT_WPSC_ADDITIONALDESCRIPTION.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td class='itemformcol'>\n\r";
  $output .= "<textarea name='additional_description' cols='40' rows='8' >".stripslashes($product['additional_description'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
    
  $output .= "          <tr>\n\r";
  $output .= "            <td class='itemfirstcol'>\n\r";
  $output .= TXT_WPSC_PRODUCT_TAGS.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td class='itemformcol'>\n\r";
  $output .= "<input type='text' class='text'  name='product_tags' value='$imtags'><br /><span class='small_italic'>Seperate with commas</span>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

//   $output .="<tr><td>&nbsp;</td></tr>";
  $output .= "          <tr>\n\r";
  $output .= "            <td class='itemfirstcol'>".TXT_WPSC_CATEGORISATION.":</td>\n\r";
  $output .= "            <td>\n\r";
  
    $categorisation_groups =  $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpsc_categorisation_groups` WHERE `active` IN ('1')", ARRAY_A);
					
	foreach((array)$categorisation_groups as $categorisation_group) {
		$category_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}product_categories` WHERE `group_id` IN ('{$categorisation_group['id']}')");
		if($category_count > 0) {
			$output .= "<p>";
			$category_group_name = str_replace("[categorisation]", $categorisation_group['name'], TXT_WPSC_PRODUCT_CATEGORIES);
			$output .= "<strong>".$category_group_name.":</strong><br>";
			$output .= categorylist($categorisation_group['id'], $product['id'], 'edit_');
			$output .= "</p>\n\r";
		}
	} 

  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "<tr><td  colspan='2'><div id='edit_price_and_stock' class='postbox ".((array_search('edit_price_and_stock', $closed_postboxes) !== false) ? 'closed' : '')."'>
	<h3>
		<a class='togbox'>+</a>";
  $output .= "".TXT_WPSC_PRICE_AND_STOCK_CONTROL."";
  $output .= " </h3> <div class='inside'> <table>";
  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_PRICE.": <input type='text' name='price' size='10' value='".number_format($product['price'], 2, '.', '')."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  if($product['notax'] == 1) {
    $checked = "checked='true'";
	} else {
		$checked = "";
	}

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input id='tax' type='checkbox' name='notax' value='yes' $checked />&nbsp;<label for='tax'>".TXT_WPSC_TAXALREADYINCLUDED."</label>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  if($product['donation'] == 1) {
    $checked = "checked='true'";
	} else {
    $checked = "";
	}  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input id='edit_form_donation' type='checkbox' $checked name='donation' value='yes' />&nbsp;<label for='edit_form_donation'>".TXT_WPSC_IS_DONATION."</label>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  if($product['no_shipping'] == 1) {
    $checked = "checked='true'";
	} else {
    $checked = "";
	}  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input id='add_form_no_shipping' type='checkbox' $checked name='no_shipping' value='yes' />&nbsp;<label for='add_form_no_shipping'>".TXT_WPSC_NO_SHIPPING."</label>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  if($product['special'] == 1) {
    $checked = "checked='true'";
	} else {
    $checked = "";
	}  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $disable_form = '';
  if($check_variation_value_count > 0) {
    if($product['special'] != 1) {
      $disable_form = "disabled='true'";
      $disable_form_label = " style='color: #cccccc;'";
		}
	}
  $output .= "<input id='form_special' type='checkbox' $checked name='special' $disable_form value='yes' onclick='hideelement(\"edit_special\")' /> <label for='form_special' $disable_form_label>".TXT_WPSC_SPECIAL."</label>"; 
  if($disable_form != '') {
    $output .="<br /><span class='small'>". TXT_WPSC_VARIATIONS_AND_SPECIALS_DONT_MIX."<span>";
	}
	
	$table_rate_price = get_product_meta($product['id'], 'table_rate_price');
	$table_rate_price = $table_rate_price[0];
	if ($table_rate_price != '') {
		$output .= '<br><input type="checkbox" value="yes" name="table_rate_price" checked="yes" id="table_rate_price"/>
		<label for="table_rate_price">'.TXT_WPSC_TABLE_RATED_PRICE.'</label>';
		
		$output .= '<div id="table_rate">
				<a class="add_level" style="cursor:pointer;">Add level</a><br>
				<table>
				<tr><td>'.TXT_WPSC_QUANTITY.'</td><td>'.TXT_WPSC_PRICE.'</td></tr>';
		foreach($table_rate_price['quantity'] as $key => $qty) {
			$output .= '<tr><td><input type="text" size="10" value="'.$qty.'" name="productmeta_values[table_rate_price][quantity][]"/> and above</td><td><input type="text" size="10" value="'.$table_rate_price['table_price'][$key].'" name="productmeta_values[table_rate_price][table_price][]"/></td><td><img src="'.WPSC_URL.'/images/cross.png" class="remove_line"></td></tr>';
		}
// 		$output .= '<tr><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][quantity][]"/> and above</td><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][table_price][]"/></td><td><img src="'.WPSC_URL.'/images/cross.png" class="remove_line"></td></tr>
		$output .=	'</table>
				</div>';
	}
  if($product['special'] == 1) {
    $output .= "            <div id='edit_special' style='display: block;'>\n\r";
    $output .= "<input type='text' name='special_price' value='".number_format(($product['price']-$product['special_price']), 2, '.', '')."' size='10' />";
	} else {
		$output .= "            <div id='edit_special' style='display: none;'>\n\r";
		$output .= "<input type='text' name='special_price' value='0.00' size='10' />";
	}
  $output .= "              </div>\n\r";

  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r"; 

  if($product['quantity_limited'] == 1) {
    $checked = "checked='true'";
	} else {
		$checked = "";
	}
  $output .= "          <tr>\n\r";
  $output .= "            <td style='width:350px;'>\n\r";
  $output .= "<input id='form_quantity_limited' type='checkbox' $checked name='quantity_limited' value='yes' onclick='hideelement(\"edit_stock\")' /><label for='form_quantity_limited' class='small'>".TXT_WPSC_UNTICKBOX."</label>";
    
  $variations_output = $variations_processor->variations_grid_view($product['id']); 
  if($variations_output != '') {
		//$output .= $variations_output;
		
		$output .= "<div id='edit_stock' style='display: none;'>\n\r";
		$output .= "<input type='hidden' name='quantity' value='".$product['quantity']."' />";
		$output .= "</div>\n\r";
	} else {
		switch($product['quantity_limited']) {
			case 1:
			$output .= "            <div id='edit_stock' style='display: block;'>\n\r";
			break;
			
			default:
			$output .= "            <div id='edit_stock' style='display: none;'>\n\r";
			break;
		}
		$output .= "<input type='text' name='quantity' size='10' value='".$product['quantity']."' />";
		$output .= "              </div>\n\r";
	}
	$output .= "</td></tr>";
	
	
	$output .= "
    </table></div></div></TD></tr>";

  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  
  ob_start();
  do_action('wpsc_product_form', $product['id']);
  $output .= ob_get_contents();
  ob_end_clean();
    
    
    
  $output .= "          <tr>\n\r";
  $output .= "            <td colspan='2'>\n\r";
  $output .= "<div id='edit_variation' class='postbox ".((array_search('edit_variation', $closed_postboxes) !== false) ? 'closed' : '')."'>
        <h3>
		<a class='togbox'>+</a>";
  $output .= "".TXT_WPSC_VARIATION_CONTROL."";
  $output .= " </h3>
	<div class='inside'>
    <table>";  
  
  $output .= "          <tr>\n\r";
  $output .= "            <td colspan='2'>\n\r";
  $output .= TXT_WPSC_ADD_VAR.": <br />";
  $output .= $variations_processor->list_variations($product['id']);
  if($check_variation_value_count < 1) {
		$output .= "            	<div id='edit_variations_container'>\n\r";
		$output .= "            	</div>\n\r";
  }
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
    
  if($check_variation_value_count > 0) {
    $output .= "          <tr>\n\r";
    $output .= "            <td colspan='2'>\n\r";
    $output .= TXT_WPSC_EDIT_VAR.": ";
    $output .= "            </td>\n\r";
    $output .= "          </tr>\n\r";
    
    
    $output .= "          <tr>\n\r";
    $output .= "            <td colspan='2'>\n\r";
		$output .= "            <div id='edit_product_variations'>";
	
		$output .= "            </div>";
    $output .= "            	<div id='edit_variations_container'>\n\r";
    //$variations_processor = new nzshpcrt_variations;
    $output .= $variations_processor->display_attached_variations($product['id']);
    $output .= $variations_output;
    $output .= "            	</div>\n\r";
    $output .= "            </td>\n\r";
    $output .= "          </tr>\n\r";
    }
  $output .="</table></div></div></td></tr>";
  

  $output .= "    <tr>\n\r";
  $output .= "      <td colspan='2'>\n\r";
  $output .= "  <div class='postbox ".((array_search('edit_shipping', $closed_postboxes) !== false) ? 'closed' : '')."' id='edit_shipping'>
	     <h3>
		     <a class='togbox'>+</a>".TXT_WPSC_SHIPPING_DETAILS."";
  $output .= "</h3>
      <div class='inside'>
  <table>";
	if ($product['weight_unit']=='pound') {
		$unit1="selected='selected'";
	} else if ($product['weight_unit']=='once') {
		$unit2="selected='selected'";
	}else if ($product['weight_unit']=='gram') {
		$unit3="selected='selected'";
	}else if ($product['weight_unit']=='kilogram') {
		$unit4="selected='selected'";
	}
	$output .= "<tr>\n\r";
	$output .= "	<td>\n\r";
	$output .= "		". TXT_WPSC_WEIGHT."\n\r";
	$output .= "	</td>\n\r";
	$output .= "	<td>\n\r";
	$output .= "		<input type='text' size='5' name='weight' value='".$product['weight']."'>\n\r";
	$output .= "   <select name='weight_unit'>\n\r";
	$output .= "			<option $unit1 value='pound'>Pounds</option>\n\r";
	$output .= "			<option $unit2 value='once'>Ounces</option>\n\r";
	$output .= "			<option $unit3 value='gram'>Grams</option>\n\r";
	$output .= "			<option $unit4 value='kilogram'>Kilograms</option>\n\r";
	$output .= "		</select>\n\r";
	$output .= "	</td>\n\r";
	$output .= "</tr>";

  $output .= "    <tr>\n\r";
  $output .= "      <td>";
  $output .= TXT_WPSC_LOCAL_PNP;
  $output .= "      </td>\n\r";
  $output .= "      <td>\n\r";
  $output .= "        <input type='text' size='10' name='pnp' value='".$product['pnp']."' />\n\r";
  $output .= "      </td>\n\r";
  $output .= "    </tr>\n\r";
  
  $output .= "    <tr>\n\r";
  $output .= "      <td>";
  $output .= TXT_WPSC_INTERNATIONAL_PNP;
  if($product['international_pnp'] == 0)
    {
    $product['international_pnp'] = "0.00";
    }
  $output .= "      </td>\n\r";
  $output .= "      <td>\n\r";
  $output .= "        <input type='text' size='10' name='international_pnp' value='".$product['international_pnp']."' />\n\r";
  $output .= "      </td>\n\r";
  $output .= "    </tr>\n\r";
  $output .="</table></div></div></td></tr>";
  
  $output .= "<tr><td colspan='2'>";
  $output .="<div id='edit_advanced' class='postbox ".((array_search('edit_advanced', $closed_postboxes) !== false) ? 'closed' : '')."'>
	    <h3>
		    <a class='togbox'>+</a>";
  $output .=TXT_WPSC_ADVANCED_OPTIONS;
  $output .="</h3><div class='inside'>";
  
  $output .='<table>';
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_ADMINNOTES.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<textarea name='merchant_notes' cols='40' rows='3' >".stripslashes($product['merchant_notes'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
	$output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  if($product['display_frontpage'] == 1)
    {
    $output .= "<input type='checkbox' checked='true' value='yes' name='display_frontpage' id='form_display_frontpage'/>\n\r";
    }
    else
      {
      $output .= "<input type='checkbox' value='yes' name='display_frontpage' id='form_display_frontpage'/>\n\r";
      }
      
  $output .= "<label for='form_display_frontpage'>".TXT_WPSC_DISPLAY_FRONT_PAGE."</form>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  
  if ($engrave[0]=='on'){
		$engra="checked='checked'";
	}
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='productmeta_values[engraved]' value='0'>";
  $output .= "<input $engra type='checkbox' name='productmeta_values[engraved]'>". TXT_WPSC_ENGRAVE."<br />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  if ($can_have_uploaded_image[0]=='on'){
		$can_have_uploaded_image_state="checked='checked'";
	}
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='productmeta_values[can_have_uploaded_image]' value='0'>";
  $output .= "<input $can_have_uploaded_image_state type='checkbox' name='productmeta_values[can_have_uploaded_image]'>". TXT_WPSC_ALLOW_UPLOADING_IMAGE."<br />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  if(get_option('payment_gateway') == 'google') {
		$output .= "          <tr>\n\r";
		$output .= "            <td>\n\r";
		$output .= TXT_WPSC_PROHIBITED.": ";
		$output .= "            </td>\n\r";
		$output .= "            <td>\n\r";
		$output .= "<input type='checkbox' name='productmeta_values[\"google_prohibited\"]'/> ";
		$output .= "Prohibited <a href='http://checkout.google.com/support/sell/bin/answer.py?answer=75724'>by Google?</a>";
		$output .= "            </td>\n\r";
		$output .= "          </tr>\n\r";
  }
  
  ob_start();
  do_action('wpsc_add_advanced_options', $product['id']);
  $output .= ob_get_contents();
  ob_end_clean();


  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_PRODUCT_ID.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= $product['id'];
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_EXTERNALLINK.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text' class='text'  value='".$product['external_link']."' name='productmeta_values[external_link]' id='external_link' size='40'> ";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

 $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_USEONLYEXTERNALLINK;
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  $pdf = get_product_meta($product['id'], 'pdf');
  $pdf = $pdf[0];
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_PDF.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='file' name='pdf'>";
  if ($pdf != '') $output .="<font color='red'>to replace $pdf</font>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
 $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_ADD_CUSTOM_FIELD;
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  //foreach
  $output .= "<label></label>
  <div class='product_custom_meta'>
		<label >
		".TXT_WPSC_NAME."
		<input type='text' class='text'  value='' name='new_custom_meta[name][]' >
		</label>
		
		<label >
		".TXT_WPSC_VALUE."
		<input type='text' class='text'  value='' name='new_custom_meta[value][]' > 
		</label>		
		<a href='#' class='add_more_meta' onclick='return add_more_meta(this)'>+</a>
	 <br />
  </div>
   ";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  
  $custom_fields =  $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpsc_productmeta` WHERE `product_id` IN('{$product['id']}') AND `custom` IN('1') ",ARRAY_A);
  if(count($custom_fields) > 0) {
		$output .= "          <tr>\n\r";
		$output .= "            <td>\n\r";
		$output .= TXT_WPSC_EDIT_CUSTOM_FIELDS;
		$output .= "            </td>\n\r";
		$output .= "            <td>\n\r";
		
		//$i = 1;
		foreach((array)$custom_fields as $custom_field) {
			$i = $custom_field['id'];
			// for editing, the container needs an id, I can find no other tidyish method of passing a way to target this object through an ajax request
			$output .= "
			<div class='product_custom_meta'  id='custom_meta_$i'>
				<label for='custom_meta_name_$i'>
				".TXT_WPSC_NAME."
				<input type='text' class='text'  value='{$custom_field['meta_key']}' name='custom_meta[$i][name]' id='custom_meta_name_$i'>
				</label>
				
				<label for='custom_meta_value_$i'>
				".TXT_WPSC_VALUE."
				<input type='text' class='text'  value='{$custom_field['meta_value']}' name='custom_meta[$i][value]' id='custom_meta_value_$i'> 
				</label>
				<a href='#' class='remove_meta' onclick='return remove_meta(this, $i)'>&ndash;</a>
				<br />
			</div>
			";
		}
		$output .= "            </td>\n\r";
		$output .= "          </tr>\n\r";
  }

  
  
  
$output .="</table></div></div></td></tr>";
  
  $output .= "          <tr class='edit_product_image'>\n\r";
  $output .= "            <td colspan='2'>\n\r";
  $output .= "<div id='edit_product_image' class='postbox ".((array_search('edit_product_image', $closed_postboxes) !== false) ? 'closed' : '')."'>
        <h3> 
		<a class='togbox'>+</a>".TXT_WPSC_PRODUCTIMAGE."";
  $output .= "</h3>
		  <div class='inside'>";
// 	<table>";
 $output .= '<button id="add-product-image" name="add-image" class="button-secondary" type="button"><small>Add New Image</small></button>';
  if(function_exists("getimagesize"))
    {
    if($product['image'] != '')
      {
      $imagedir = WPSC_THUMBNAIL_DIR;
      $image_size = @getimagesize(WPSC_THUMBNAIL_DIR.$product['image']);
//       $output .= "          <tr>\n\r";
//       $output .= "            <td>\n\r";
// //       $output .= TXT_WPSC_RESIZEIMAGE.": <br />";      
// //       $output .= "<span class='image_size_text'>".$image_size[0]."&times;".$image_size[1]."px</span>";
//       $output .= "            </td>\n\r";  
//       
//       $output .= "            <td>\n\r";


//       $output .= "            </td>\n\r";
//       $output .= "          </tr>\n\r";
      }
    }
  
//   $output .= "          <tr>\n\r";
//   $output .= "            <td>\n\r";
//   $output .= "            </td>\n\r";
//   $output .= "            <td>\n\r";
//   $output .= TXT_WPSC_UPLOADNEWIMAGE.": <br />";
//   $output .= "<input type='file' name='image' value='' />";
//   $output .= "            </td>\n\r";
//   $output .= "          </tr>\n\r";
  
	if(function_exists("getimagesize")) {
		if($product['image'] == '') {
// 			$output .= "          <tr>\n\r";
// 			$output .= "            <td></td>\n\r";
// 			$output .= "            <td>\n\r";
// 			$output .= "<table>\n\r";
			if(is_numeric(get_option('product_image_height')) && is_numeric(get_option('product_image_width'))) {
// 				$output .= "      <tr>\n\r";
// 				$output .= "        <td>\n\r";
// 				$output .= "      <input type='radio' name='image_resize' value='0' id='image_resize0' class='image_resize' onclick='hideOptionElement(null, \"image_resize0\");' /> <label for='image_resize0'>".TXT_WPSC_DONOTRESIZEIMAGE."</label>\n\r";
// 				$output .= "        </td>\n\r";
// 				$output .= "      </tr>\n\r";
// 				$output .= "      <tr>\n\r";
// 				$output .= "        <td>\n\r";
// 				$output .= "          <input type='radio' checked='true' name='image_resize' value='1' id='image_resize1' class='image_resize' onclick='hideOptionElement(null, \"image_resize1\");' /> <label for='image_resize1'>".TXT_WPSC_USEDEFAULTSIZE." (".get_option('product_image_height') ."x".get_option('product_image_width').")</label>\n\r";
// 				$output .= "        </td>\n\r";
// 				$output .= "      </tr>\n\r";
			}
// 			$output .= "      <tr>\n\r";
// 			$output .= "        <td>\n\r";
// 			$output .= "          <input type='radio' name='image_resize' value='2' id='image_resize2' class='image_resize' onclick='hideOptionElement(\"heightWidth\", \"image_resize2\");' />\n\r";
// 			$output .= "      <label for='image_resize2'>".TXT_WPSC_USESPECIFICSIZE."</label>\n\r";
// 			$output .= "          <div id='heightWidth' style='display: none;'>\n\r";
// 			$output .= "        <input type='text' size='4' name='width' value='' /><label for='image_resize2'>".TXT_WPSC_PXWIDTH."</label>\n\r";
// 			$output .= "        <input type='text' size='4' name='height' value='' /><label for='image_resize2'>".TXT_WPSC_PXHEIGHT."</label>\n\r";
// 			$output .= "      </div>\n\r";
// 			$output .= "        </td>\n\r";
// 			$output .= "      </tr>\n\r";
// 			$output .= "      <tr>\n\r";
// 			$output .= "      <td>\n\r";
// 			$output .= "        <input type='radio' name='image_resize' value='3' id='image_resize3' class='image_resize' onclick='hideOptionElement(\"browseThumb\", \"image_resize3\");' />\n\r";
// 			$output .= "        <label for='image_resize3'>".TXT_WPSC_SEPARATETHUMBNAIL."</label><br />";
// 			$output .= "        <div id='browseThumb' style='display: none;'>\n\r";
// 			$output .= "          <input type='file' name='thumbnailImage' value='' />\n\r";
// 			$output .= "        </div>\n\r";
// 			$output .= "      </td>\n\r";
// 			$output .= "    </tr>\n\r";
// 			$output .= "  </table>\n\r";
// 			$output .= "            </td>\n\r";
// 			$output .= "          </tr>\n\r";
		}
	}
//   $output .= "          <tr>\n\r";
//   $output .= "            <td>\n\r";
//   $output .= "            </td>\n\r";
//   $output .= "            <td>\n\r";
//   $output .= "<input id='delete_image' type='checkbox' name='deleteimage' value='1' /> ";
//   $output .= "<label for='delete_image'>".TXT_WPSC_DELETEIMAGE."</label>";
//   $output .= "            </td>\n\r";
//   $output .= "          </tr>\n\r";
//   $output .= "          <tr>\n\r";
//   $output .= "            <td colspan='2'>\n\r";
  $output .= "<ul id='gallery_list'>";
 
	if(function_exists('edit_multiple_image_form')) {
		$output .= edit_multiple_image_form($product['id']);
	}
	$output .= "</ul>";
	$output .= "<br style='clear:both;'>";
// 	$output .= "            </td>\n\r";
// 	$output .= "          </tr>\n\r";
  $output .="</div></div></td></tr>";


    
  if($product['file'] > 0)
    {
    $output .= "          <tr>\n\r";
    $output .= "            <td colspan='2'>\n\r";
    $output .= "<div id='edit_product_download' class='postbox closed'>
        <h3>
		<a class='togbox'>+</a>".TXT_WPSC_PRODUCTDOWNLOAD."";
    $output .= " </h3>
	<div class='inside'>
	<table>";
    
    $output .= "          <tr>\n\r";
    $output .= "            <td>\n\r";
    $output .= TXT_WPSC_PREVIEW_FILE.": ";
    $output .= "            </td>\n\r";
    $output .= "            <td>\n\r";    
    
    $output .= "<a class='admin_download' href='index.php?admin_preview=true&product_id=".$product['id']."' style='float: left;' ><img align='absmiddle' src='".WPSC_URL."/images/download.gif' alt='' title='' /><span>".TXT_WPSC_CLICKTODOWNLOAD."</span></a>";

    $file_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_files` WHERE `id`='".$product['file']."' LIMIT 1",ARRAY_A);
    if(($file_data != null) && (function_exists('listen_button')))
      {
      $output .= "".listen_button($file_data['idhash'], $file_data['id']);
      }
      
    $output .= "            </td>\n\r";
    $output .= "          </tr>\n\r";
  
              
    $output .= "          <tr>\n\r";
    $output .= "            <td>\n\r";
    $output .= TXT_WPSC_DOWNLOADABLEPRODUCT.": ";
    $output .= "            </td>\n\r";
    $output .= "            <td>\n\r";
    $output .= "<input type='file' name='file' value='' /><br />";    
    $output .= wpsc_select_product_file($product['id']);
    $output .= "            </td>\n\r";
    $output .= "          </tr>\n\r";
    
    if(function_exists("make_mp3_preview") || function_exists("wpsc_media_player")) {
      $output .= "          <tr>\n\r";
      $output .= "            <td>\n\r";
      $output .= TXT_WPSC_NEW_PREVIEW_FILE.": ";
      $output .= "            </td>\n\r";
      $output .= "            <td>\n\r";
      $output .= "<input type='file' name='preview_file' value='' /><br />";
      //$output .= "<span class='admin_product_notes'>".TXT_WPSC_PREVIEW_FILE_NOTE."</span>";
      $output .= "<br /><br />";
      $output .= "            </td>\n\r";
      $output .= "          </tr>\n\r";
      }
    }
    else
      {
      $output .="<tr><td  colspan='2'>";
     $output .= "<div id='edit_product_download' class='postbox closed'>
        <h3>
		<a class='togbox'>+</a>".TXT_WPSC_PRODUCTDOWNLOAD."";
    $output .= " </h3>
	<div class='inside'>
	<table>";
      
      $output .= "       <tr>";
      $output .= "         <td>";
    //  $output .= "".TXT_WPSC_DOWNLOADABLEPRODUCT.":";
      $output .= "        </td>";
      $output .= "        <td>";
      $output .= "          <input type='file' name='file' value='' />";
      $output .= wpsc_select_product_file($product['id']);
      $output .= "        </td>";
      $output .= "      </tr>";
      }
		$output.=" </table></div></div></td></tr>";
		$output .= "          <tr>\n\r";
		$output .= "            <td>\n\r";
		$output .= "            </td>\n\r";
		$output .= "            <td>\n\r";;
		$output .= "            <br />\n\r";
		$output .= "<input type='hidden' name='prodid' id='prodid' value='".$product['id']."' />";
		$output .= "<input type='hidden' name='submit_action' value='edit' />";
		$output .= "<input  class='button' style='float:left;'  type='submit' name='submit' value='".TXT_WPSC_EDIT_PRODUCT."' />";
		$output .= "<a class='button delete_button' ' href='admin.php?page=".WPSC_DIR_NAME."/display-items.php&amp;deleteid=".$product['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE_PRODUCT."</a>";
		$output .= "            <td>\n\r";
		$output .= "          </tr>\n\r";
		
		$output .= "        </table>\n\r";
		return $output;
  }

function nzshpcrt_getcategoryform($catid)
  {
  global $wpdb,$nzshpcrt_imagesize_info;
  $sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `id`=$catid LIMIT 1";
  $product_data = $wpdb->get_results($sql,ARRAY_A) ;
  $product = $product_data[0];
  $output .= "        <table class='category_forms'>\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_NAME.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text' class='text' name='title' value='".htmlentities(stripslashes($product['name']), ENT_QUOTES, 'UTF-8')."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_DESCRIPTION.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<textarea name='description' cols='40' rows='8' >".stripslashes($product['description'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_CATEGORY_PARENT.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= wpsc_parent_category_list($product['group_id'], $product['id'], $product['category_parent']);
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";


	if ($product['display_type'] == 'grid') {
		$display_type1="selected='selected'";
	} else if ($product['display_type'] == 'default') {
		$display_type2="selected='selected'";
	}
	
	switch($product['display_type']) {
		case "grid":
		if(function_exists('product_display_grid')) {
			$product_view3 = "selected ='true'";
			break;
		}
		
		case "list":
		if(function_exists('product_display_list')) {
			$product_view2 = "selected ='true'";
			break;
		}
		
		default:
		$product_view1 = "selected ='true'";
		break;
	}	
	
	$output .= "          <tr>\n\r";
	$output .= "          	<td>\n\r";
	$output .= "          	". TXT_WPSC_DISPLAYTYPE.":\n\r";
	$output .= "          	</td>\n\r";
	$output .= "          	<td>\n\r";
	$output .= "          		<select name='display_type'>\n\r";	
	$output .= "          			<option value='default' $product_view1 >".TXT_WPSC_DEFAULT."</option>\n\r";	
	if(function_exists('product_display_list')) {
		$output .= "          			<option value='list' ". $product_view2.">". TXT_WPSC_LIST."</option>\n\r"; 
	} else {
		$output .= "          			<option value='list' disabled='disabled' ". $product_view2.">". TXT_WPSC_LIST."</option>\n\r";
	}	
	if(function_exists('product_display_grid')) {
		$output .= "          			<option value='grid' ". $product_view3.">". TXT_WPSC_GRID."</option>\n\r";
	} else {
		$output .= "          			<option value='grid' disabled='disabled' ". $product_view3.">". TXT_WPSC_GRID."</option>\n\r";
	}	
	$output .= "          		</select>\n\r";	
	$output .= "          	</td>\n\r";
	$output .= "          </tr>\n\r";
	$output .= "          <tr>\n\r";
	
	
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_CATEGORY_PRODUCT_IMAGE.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_HEIGHT.": <input type='text' value='".$product['image_height']."' name='product_height' size='6'/> ";
  $output .= TXT_WPSC_WIDTH.": <input type='text' value='".$product['image_width']."' name='product_width' size='6'/> <br/>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_IMAGE.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='file' name='image' value='' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  if(function_exists("getimagesize"))
    {
    if($product['image'] != '')
      {
      $imagepath = WPSC_CATEGORY_DIR . $product['image'];
      $imagetype = @getimagesize($imagepath); //previously exif_imagetype()
      $output .= "          <tr>\n\r";
      $output .= "            <td>\n\r";
      $output .= "            </td>\n\r";
      $output .= "            <td>\n\r";
      $output .= TXT_WPSC_HEIGHT.":<input type='text' size='6' name='height' value='".$imagetype[1]."' /> ".TXT_WPSC_WIDTH.":<input type='text' size='6' name='width' value='".$imagetype[0]."' /><br /><span class='small'>$nzshpcrt_imagesize_info</span>";
      $output .= "            </td>\n\r";
      $output .= "          </tr>\n\r";
      }
      else
        {
        $output .= "          <tr>\n\r";
        $output .= "            <td>\n\r";
        $output .= "            </td>\n\r";
        $output .= "            <td>\n\r";
        $output .= TXT_WPSC_HEIGHT.":<input type='text' size='6' name='height' value='".get_option('product_image_height')."' /> ".TXT_WPSC_WIDTH.":<input type='text' size='6' name='width' value='".get_option('product_image_width')."' /><br /><span class='small'>$nzshpcrt_imagesize_info</span>";
        $output .= "            </td>\n\r";
        $output .= "          </tr>\n\r";
        }
    }

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_DELETEIMAGE.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='checkbox' name='deleteimage' value='1' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='prodid' value='".$product['id']."' />";
  $output .= "<input type='hidden' name='submit_action' value='edit' />";
  $output .= "<input class='button' style='float:left;' type='submit' name='submit' value='".TXT_WPSC_EDIT."' />";
  $output .= "<a class='button delete_button' href='admin.php?page=".WPSC_DIR_NAME."/display-category.php&amp;deleteid=".$product['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 $output .= "        </table>\n\r"; 
  return $output;
  }

function nzshpcrt_getbrandsform($catid)
  {
  global $wpdb,$nzshpcrt_imagesize_info;

  $sql = "SELECT * FROM `".$wpdb->prefix."product_brands` WHERE `id`='$catid' LIMIT 1";
  $product_data = $wpdb->get_results($sql,ARRAY_A) ;
  $product = $product_data[0];
  $output .= "        <table>\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_NAME.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text' name='title' value='".stripslashes($product['name'])."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_DESCRIPTION.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<textarea name='description' cols='40' rows='8' >".stripslashes($product['description'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='prodid' value='".$product['id']."' />";
  $output .= "<input type='hidden' name='submit_action' value='edit' />";
  $output .= "<input class='button' style='float:left;' type='submit' name='submit' value='".TXT_WPSC_EDIT."' />";
  $output .= "<a class='button delete_button' href='admin.php?page=".WPSC_DIR_NAME."/display-brands.php&amp;deleteid=".$product['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 $output .= "        </table>\n\r";
  return $output;
  }
  
function nzshpcrt_getvariationform($variation_id)
  {
  global $wpdb,$nzshpcrt_imagesize_info;

  $variation_sql = "SELECT * FROM `".$wpdb->prefix."product_variations` WHERE `id`='$variation_id' LIMIT 1";
  $variation_data = $wpdb->get_results($variation_sql,ARRAY_A) ;
  $variation = $variation_data[0];
  $output .= "        <table class='category_forms' >\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_NAME.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text'  class='text' name='title' value='".htmlentities(stripslashes($variation['name']), ENT_QUOTES, 'UTF-8')."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_VARIATION_VALUES.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $variation_values_sql = "SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `variation_id`='$variation_id' ORDER BY `id` ASC";
  $variation_values = $wpdb->get_results($variation_values_sql,ARRAY_A);
  $variation_value_count = count($variation_values);
  $output .= "<div id='edit_variation_values'>";
  $num = 0;
  foreach($variation_values as $variation_value)
    {
    $output .= "<span id='variation_value_".$num."'>";
    $output .= "<input type='text' class='text' name='variation_values[".$variation_value['id']."]' value='".htmlentities(stripslashes($variation_value['name']), ENT_QUOTES, 'UTF-8')."' />";
    if($variation_value_count > 1)
      {
      $output .= " <a  class='image_link' onclick='remove_variation_value(\"variation_value_".$num."\",".$variation_value['id'].")' href='#'><img src='".WPSC_URL."/images/trash.gif' alt='".TXT_WPSC_DELETE."' title='".TXT_WPSC_DELETE."' /></a>";
      }
    $output .= "<br />";
    $output .= "</span>";
    $num++;
    }
  $output .= "</div>";
  $output .= "<a href='#'  onclick='return add_variation_value(\"edit\")'>".TXT_WPSC_ADD."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='prodid' value='".$variation['id']."' />";
  $output .= "<input type='hidden' name='submit_action' value='edit' />";
  $output .= "<input class='button' style='float:left;'  type='submit' name='submit' value='".TXT_WPSC_EDIT."' />";
  $output .= "<a class='button delete_button' href='admin.php?page=".WPSC_DIR_NAME."/display_variations.php&amp;deleteid=".$variation['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 $output .= "        </table>\n\r";
  return $output;
  } 
      
function coupon_edit_form($coupon) {
  $start_timestamp = strtotime($coupon['start']);
  $end_timestamp = strtotime($coupon['expiry']);
  $id = $coupon['id'];
  $output = '';
  $output .= "<form name='edit_coupon' method='post' action='".get_option('siteurl')."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-coupons.php'>\n\r";
    $output .= "   <input type='hidden' value='true' name='is_edit_coupon' />\n\r";
  $output .= "<table class='add-coupon'>\n\r";
  $output .= " <tr>\n\r";
  $output .= "   <th>".TXT_WPSC_COUPON_CODE."</th>\n\r";
  $output .= "   <th>".TXT_WPSC_DISCOUNT."</th>\n\r";
  $output .= "   <th>".TXT_WPSC_START."</th>\n\r";
  $output .= "   <th>".TXT_WPSC_EXPIRY."</th>\n\r";
  $output .= "   <th>".TXT_WPSC_USE_ONCE."</th>\n\r";
  $output .= "   <th>".TXT_WPSC_ACTIVE."</th>\n\r";
	$output .= "   <th>".TXT_WPSC_PERTICKED."</th>\n\r";
  $output .= "   <th></th>\n\r";
  $output .= " </tr>\n\r";
  $output .= " <tr>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='text' value='".$coupon['coupon_code']."' name='edit_coupon[".$id."][coupon_code]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='text' value='".$coupon['value']."' size='3' name=edit_coupon[".$id."][value]' />\n\r";
  $output .= "   <select name='edit_coupon[".$id."][is-percentage]'>\n\r";
  $output .= "     <option value='0' ".(($coupon['is-percentage'] == 0) ? "selected='true'" : '')." >$</option>\n\r";//
  $output .= "     <option value='1' ".(($coupon['is-percentage'] == 1) ? "selected='true'" : '')." >%</option>\n\r";
  $output .= "   </select>\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $coupon_start = explode(" ",$coupon['start']);
  $output .= "<input type='text' class='pickdate' size='10' name='edit_coupon[".$id."][start]' value='{$coupon_start[0]}'>";
/*  $output .= "   <select name='edit_coupon[".$id."][start][day]'>\n\r";  
   for($i = 1; $i <=31; ++$i) {
     $selected = '';
     if($i == date("d", $start_timestamp)) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>$i</option>";
     }
  $output .= "   </select>\n\r";
  $output .= "   <select name='edit_coupon[".$id."][start][month]'>\n\r";  
   for($i = 1; $i <=12; ++$i) {
     $selected = '';
     if($i == (int)date("m", $start_timestamp)) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>".date("M",mktime(0, 0, 0, $i, 1, date("Y")))."</option>";
     }
  $output .= "   </select>\n\r";
  $output .= "   <select name='edit_coupon[".$id."][start][year]'>\n\r";
   for($i = date("Y"); $i <= (date("Y") +12); ++$i) {
     $selected = '';
     if($i == date("Y", $start_timestamp)) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>".$i."</option>";
     }
  $output .= "   </select>\n\r";*/
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $coupon_expiry = explode(" ",$coupon['expiry']);
  $output .= "<input type='text' class='pickdate' size='10' name='edit_coupon[".$id."][expiry]' value='{$coupon_expiry[0]}'>";
  /*$output .= "   <select name='edit_coupon[".$id."][expiry][day]'>\n\r";
   for($i = 1; $i <=31; ++$i) {
     $selected = '';
     if($i == date("d", $end_timestamp)) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>$i</option>";
     }
  $output .= "   </select>\n\r";
  $output .= "   <select name='edit_coupon[".$id."][expiry][month]'>\n\r";

   for($i = 1; $i <=12; ++$i) {
     $selected = '';
     if($i == (int)date("m", $end_timestamp)) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>".date("M",mktime(0, 0, 0, $i, 1, date("Y")))."</option>";
     }
  $output .= "   </select>\n\r";
  $output .= "   <select name='edit_coupon[".$id."][expiry][year]'>\n\r";
   for($i = date("Y"); $i <= (date("Y") +12); ++$i) {
     $selected = '';
     if($i == (date("Y", $end_timestamp))) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>".$i."</option>\n\r";
     }
  $output .= "   </select>\n\r";*/
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][use-once]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['use-once'] == 1) ? "checked='true'" : '')." name='edit_coupon[".$id."][use-once]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][active]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['active'] == 1) ? "checked='true'" : '')." name='edit_coupon[".$id."][active]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][every_product]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['every_product'] == 1) ? "checked='true'" : '')." name='edit_coupon[".$id."][every_product]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='".$id."' name='edit_coupon[".$id."][id]' />\n\r";
  //$output .= "   <input type='hidden' value='false' name='add_coupon' />\n\r";
  $output .= "   <input type='submit' value='".TXT_WPSC_SUBMIT."' name='edit_coupon[".$id."][submit_coupon]' />\n\r";
  $output .= "   <input type='submit' value='".TXT_WPSC_DELETE."' name='edit_coupon[".$id."][delete_coupon]' />\n\r";
  
  $output .= "  </td>\n\r";
  $output .= " </tr>\n\r";
  $output .= "</table>\n\r";
  $output .= "</form>\n\r";
  return $output;
  }
  
function setting_button(){
	$itemsFeedURL = "http://www.google.com/base/feeds/items";
	$next_url  = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']."?page=wp-shopping-cart/display-items.php";
	$redirect_url = 'https://www.google.com/accounts/AuthSubRequest?session=1';
	$redirect_url .= '&next=';
	$redirect_url .= urlencode($next_url);
	$redirect_url .= "&scope=";
	$redirect_url .= urlencode($itemsFeedURL);
	
// 	$output.="<div><img src='".get_option('siteurl')."/wp-content/plugins/wp-shopping-cart/images/settings_button.jpg' onclick='display_settings_button()'>";
	$output.="<div style='float: right; margin-top: 0px; position: relative;'> | <a href='#' onclick='display_settings_button(); return false;' style='text-decoration: underline;'>Settings &raquo;</a>";
	$output.="<span id='settings_button' style='width:180px;background-color:#f1f1f1;position:absolute; right: 10px; border:1px solid black; display:none;'>";
	$output.="<ul class='settings_button'>";
	
	$output.="<li><a href='?page=wp-shopping-cart/options.php'>".TXT_WPSC_SHOP_SETTINGS."</a></li>";
	$output.="<li><a href='?page=wp-shopping-cart/getwayoptions.php'>".TXT_WPSC_MONEY_AND_PAYMENT."</a></li>";
	$output.="<li><a href='?page=wp-shopping-cart/form_fields.php'>".TXT_WPSC_CHECKOUT_PAGE_SETTINGS."</a></li>";
	//$output.="<li><a href='?page=wp-shopping-cart/instructions.php'>Help/Upgrade</a></li>";
	$output.="<li><a href='{$redirect_url}'>".TXT_WPSC_LOGIN_TO_GOOGLE_BASE."</a></li>";
	$output.="</ul>";
//	$output.="<div>Checkout Settings</div>";
	$output.="</span>&emsp;&emsp;</div>";
	
	return $output;
}

function wpsc_right_now() {
  global $wpdb,$nzshpcrt_imagesize_info;
	$year = date("Y");
	$month = date("m");
	$start_timestamp = mktime(0, 0, 0, $month, 1, $year);
	$end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);

  $replace_values[":productcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."product_list` WHERE `active` IN ('1')");
  $replace_values[":productcount:"] .= " ".(($replace_values[":productcount:"] == 1) ? TXT_WPSC_PRODUCTCOUNT_SINGULAR : TXT_WPSC_PRODUCTCOUNT_PLURAL);
  
  $replace_values[":groupcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."product_categories` WHERE `active` IN ('1')");
  $replace_values[":groupcount:"] .= " ".(($replace_values[":groupcount:"] == 1) ? TXT_WPSC_GROUPCOUNT_SINGULAR : TXT_WPSC_GROUPCOUNT_PLURAL);
  
  $replace_values[":salecount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."purchase_logs` WHERE `date` BETWEEN '".$start_timestamp."' AND '".$end_timestamp."'");
  $replace_values[":salecount:"] .= " ".(($replace_values[":salecount:"] == 1) ? TXT_WPSC_SALECOUNT_SINGULAR : TXT_WPSC_SALECOUNT_PLURAL);
		
  $replace_values[":monthtotal:"] = nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1);
  $replace_values[":overaltotal:"] = nzshpcrt_currency_display(admin_display_total_price(),1);
  
  $replace_values[":pendingcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."purchase_logs` WHERE `processed` IN ('1')");
  $replace_values[":pendingcount:"] .= " " . (($replace_values[":pendingcount:"] == 1) ? TXT_WPSC_PENDINGCOUNT_SINGULAR : TXT_WPSC_PENDINGCOUNT_PLURAL);
  
  $replace_values[":theme:"] = get_option('wpsc_selected_theme');
  $replace_values[":versionnumber:"] = WPSC_PRESENTABLE_VERSION;
  
  
	$output="";	
	$output.="<div id='rightnow'>\n\r";
	$output.="	<h3 class='reallynow'>\n\r";
	$output.="		<span>"._('Right Now')."</span>\n\r";
	$output.="		<a class='rbutton' href='admin.php?page=wp-shopping-cart/display-items.php'><strong>".TXT_WPSC_ADDNEWPRODUCT."</strong></a>\n\r";
	$output.="		<br class='clear'/>\n\r";
	$output.="	</h3>\n\r";
	
	$output.="<p class='youhave'>".TXT_WPSC_SALES_DASHBOARD."</p>\n\r";
	$output.="	<p class='youare'>\n\r";
	$output.="		".TXT_WPSC_YOUAREUSING."\n\r";
	//$output.="		<a class='rbutton' href='themes.php'>Change Theme</a>\n\r";
	//$output.="<span id='wp-version-message'>This is WordPress version 2.6. <a class='rbutton' href='http://wordpress.org/download/'>Update to 2.6.1</a></span>\n\r";
	$output.="		</p>\n\r";
	$output.="</div>\n\r";
	$output.="<br />\n\r";
	
	$output = str_replace(array_keys($replace_values), array_values($replace_values),$output);
	return $output;
}


function wpsc_packing_slip($purchase_id) {
  global $wpdb;
	$purch_sql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `id`='".$purchase_id."'";
		$purch_data = $wpdb->get_row($purch_sql,ARRAY_A) ;
			
			
	  //echo "<p style='padding-left: 5px;'><strong>".TXT_WPSC_DATE."</strong>:".date("jS M Y", $purch_data['date'])."</p>";

		$cartsql = "SELECT * FROM `".$wpdb->prefix."cart_contents` WHERE `purchaseid`=".$purchase_id."";
		$cart_log = $wpdb->get_results($cartsql,ARRAY_A) ; 
		$j = 0;
		if($cart_log != null) {
      echo "<div class='packing_slip'>\n\r";
			echo "<h2>".TXT_WPSC_PACKING_SLIP."</h2>\n\r";
			echo "<strong>".TXT_WPSC_ORDER." #</strong> ".$purchase_id."<br /><br />\n\r";
			
			echo "<table>\n\r";
			
			$form_sql = "SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE  `log_id` = '".(int)$purchase_id."'";
			$input_data = $wpdb->get_results($form_sql,ARRAY_A);
			
			foreach($input_data as $input_row) {
			  $rekeyed_input[$input_row['form_id']] = $input_row;
			}
			
			
			if($input_data != null) {
        $form_data = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}collect_data_forms` WHERE `active` = '1'",ARRAY_A);
        
        foreach($form_data as $form_field) {
          switch($form_field['type']) {
            case 'country':
            if(is_numeric($purch_data['shipping_region'])) {
              echo "  <tr><td>".TXT_WPSC_STATE.":</td><td>".get_region($purch_data['shipping_region'])."</td></tr>\n\r";
            }
            echo "  <tr><td>".$form_field['name'].":</td><td>".get_country($purch_data['billing_country'])."</td></tr>\n\r";
            break;
                
            case 'delivery_country':
            echo "  <tr><td>".$form_field['name'].":</td><td>".get_country($purch_data['shipping_country'])."</td></tr>\n\r";
            break;
                
            case 'heading':
            echo "  <tr><td colspan='2'><strong>".$form_field['name'].":</strong></td></tr>\n\r";
            break;
            
            default:
            echo "  <tr><td>".$form_field['name'].":</td><td>".$rekeyed_input[$form_field['id']]['value']."</td></tr>\n\r";
            break;
          }
        }
				
				
			} else {
					echo "  <tr><td>".TXT_WPSC_NAME.":</td><td>".$purch_data['firstname']." ".$purch_data['lastname']."</td></tr>\n\r";
					echo "  <tr><td>".TXT_WPSC_ADDRESS.":</td><td>".$purch_data['address']."</td></tr>\n\r";
					echo "  <tr><td>".TXT_WPSC_PHONE.":</td><td>".$purch_data['phone']."</td></tr>\n\r";
					echo "  <tr><td>".TXT_WPSC_EMAIL.":</td><td>".$purch_data['email']."</td></tr>\n\r";
			}
			
			if(get_option('payment_method') == 2) {
				$gateway_name = '';
				foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
					if($purch_data['gateway'] != 'testmode') {
						if($gateway['internalname'] == $purch_data['gateway'] ) {
							$gateway_name = $gateway['name'];
						}
					} else {
						$gateway_name = "Manual Payment";
					}
				}
			}
// 			echo "  <tr><td colspan='2'></td></tr>\n\r";
// 			echo "  <tr><td>".TXT_WPSC_PAYMENT_METHOD.":</td><td>".$gateway_name."</td></tr>\n\r";
// 			//echo "  <tr><td>".TXT_WPSC_PURCHASE_NUMBER.":</td><td>".$purch_data['id']."</td></tr>\n\r";
// 			echo "  <tr><td>".TXT_WPSC_HOWCUSTOMERFINDUS.":</td><td>".$purch_data['find_us']."</td></tr>\n\r";
// 			$engrave_line = explode(",",$purch_data['engravetext']);
// 			echo "  <tr><td>".TXT_WPSC_ENGRAVE."</td><td></td></tr>\n\r";
// 			echo "  <tr><td>".TXT_WPSC_ENGRAVE_LINE_ONE.":</td><td>".$engrave_line[0]."</td></tr>\n\r";
// 			echo "  <tr><td>".TXT_WPSC_ENGRAVE_LINE_TWO.":</td><td>".$engrave_line[1]."</td></tr>\n\r";
// 			if($purch_data['transactid'] != '') {
// 				echo "  <tr><td>".TXT_WPSC_TXN_ID.":</td><td>".$purch_data['transactid']."</td></tr>\n\r";
// 			}
			echo "</table>\n\r";
			
			
			
			
      echo "<table class='packing_slip'>";
		
			$endtotal = 0;
			$all_donations = true;
			$all_no_shipping = true;
			$file_link_list = array();
			foreach($cart_log as $cart_row) {
				$alternate = "";
				$j++;
				if(($j % 2) != 0) {
					$alternate = "class='alt'";
        }
				$productsql= "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`=".$cart_row['prodid']."";
				$product_data = $wpdb->get_results($productsql,ARRAY_A); 
			
				$variation_sql = "SELECT * FROM `".$wpdb->prefix."cart_item_variations` WHERE `cart_id`='".$cart_row['id']."'";
				$variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
				$variation_count = count($variation_data);
				if($variation_count > 1) {
					$variation_list = " (";
					$i = 0;
					foreach($variation_data as $variation) {
						if($i > 0) {
							$variation_list .= ", ";
            }
						$value_id = $variation['value_id'];
						$value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
						$variation_list .= $value_data[0]['name'];
						$i++;
          }
					$variation_list .= ")";
        } else if($variation_count == 1) {
          $value_id = $variation_data[0]['value_id'];
          $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
          $variation_list = " (".$value_data[0]['name'].")";
        } else {
							$variation_list = '';
        }

				echo "<tr $alternate>";
		
		
				echo " <td>";
				echo $cart_row['quantity'];
				echo " </td>";
				
				echo " <td>";
				echo $product_data[0]['name'];
				echo $variation_list;
				echo " </td>";
							
				echo '</tr>';
				}
			echo "</table>";
			echo "</div>\n\r";
		} else {
			echo "<br />".TXT_WPSC_USERSCARTWASEMPTY;
		}

}

?>