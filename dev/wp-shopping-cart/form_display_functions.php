<?php
function categorylist($group_id, $product_id = '', $unique_id = '', $category_id = null, $iteration = 0) {
  /* Displays the category forms for adding and editing products
   * Recurses to generate the branched view for subcategories
	*/
  global $wpdb;
  if(is_numeric($category_id)) {
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `group_id` IN ('$group_id') AND  `active`='1' AND `category_parent` = '$category_id'  ORDER BY `id` ASC",ARRAY_A);
  } else {
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `group_id` IN ('$group_id') AND  `active`='1' AND `category_parent` = '0'  ORDER BY `id` ASC",ARRAY_A);
	}
  foreach((array)$values as $option) {
    if(is_numeric($product_id) && ($product_id > 0)) {
      $category_assoc = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."item_category_associations` WHERE `product_id` IN('".$product_id."') AND `category_id` IN('".$option['id']."')  LIMIT 1",ARRAY_A); 
      //echo "<pre>".print_r($category_assoc,true)."</pre>";
      if(is_numeric($category_assoc['id']) && ($category_assoc['id'] > 0)) {
        $selected = "checked='true'";
			}
		}
    if(is_numeric($category_id) && ($iteration > 0)) {
      if($iteration > 1) {
        if($iteration > 3) {
          $output .= str_repeat("&nbsp;", $iteration);
				}
        $output .= str_repeat("&nbsp;", $iteration);
			}
      $output .=   "-&nbsp;";
		}
    $output .= "<input id='".$unique_id."category_form_".$option['id']."' type='checkbox' $selected name='category[]' value='".$option['id']."'><label for='".$unique_id."category_form_".$option['id']."' >".stripslashes($option['name'])."</label><br />";
    $output .= categorylist($group_id, $product_id, $unique_id, $option['id'], $iteration+1);
    $selected = "";
	}
  return $output;
}
  
function nzshpcrt_country_list($selected_country = null) {
  global $wpdb;
  $output = "<option value=''></option>";
  if($selected_country == null) {
    $output = "<option value=''>".TXT_WPSC_PLEASE_SELECT."</option>";
	}
  $country_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."currency_list` ORDER BY `country` ASC",ARRAY_A);
  foreach ($country_data as $country) {
    $selected ='';
    if($selected_country == $country['isocode']) {
      $selected = "selected='true'";
		}
    $output .= "<option value='".$country['isocode']."' $selected>".$country['country']."</option>";
	}
  return $output;
}

function nzshpcrt_region_list($selected_country = null, $selected_region = null) {
  global $wpdb;
  if($selected_region == null) {
    $selected_region = get_option('base_region');
	}
  $output = "";
  $region_list = $wpdb->get_results("SELECT `".$wpdb->prefix."region_tax`.* FROM `".$wpdb->prefix."region_tax`, `".$wpdb->prefix."currency_list`  WHERE `".$wpdb->prefix."currency_list`.`isocode` IN('".$selected_country."') AND `".$wpdb->prefix."currency_list`.`id` = `".$wpdb->prefix."region_tax`.`country_id`",ARRAY_A) ;
  if($region_list != null) {
    $output .= "<select name='base_region'>\n\r";
    $output .= "<option value=''>None</option>";
    foreach($region_list as $region) {
      if($selected_region == $region['id']) {
        $selected = "selected='true'";
			} else {
				$selected = "";
			}
      $output .= "<option value='".$region['id']."' $selected>".$region['name']."</option>\n\r";
		}
    $output .= "</select>\n\r";    
	} else {
		$output .= "<select name='base_region' disabled='true'><option value=''>None</option></select>\n\r";
	}
  return $output;
}
  
function nzshpcrt_form_field_list($selected_field = null)
  {
  global $wpdb;
  $output = "";
  $output .= "<option value=''>Please choose</option>";
  $form_sql = "SELECT * FROM `".$wpdb->prefix."collect_data_forms` WHERE `active` = '1';";
  $form_data = $wpdb->get_results($form_sql,ARRAY_A);
  foreach ((array)$form_data as $form) {
    $selected ='';
    if($selected_field == $form['id']) {
      $selected = "selected='true'";
		}
    $output .= "<option value='".$form['id']."' $selected>".$form['name']."</option>";
	}
  return $output;
}
  
  
function wpsc_parent_category_list($group_id, $category_id, $category_parent_id) {
  global $wpdb,$category_data;
  $options = "";
  $options .= "<option value='0'>".TXT_WPSC_SELECT_PARENT."</option>\r\n";
  $options .= wpsc_category_options((int)$group_id, (int)$category_id, null, 0, (int)$category_parent_id);   
  $concat .= "<select name='category_parent'>".$options."</select>\r\n";    
  return $concat;
}

function wpsc_category_options($group_id, $this_category = null, $category_id = null, $iteration = 0, $selected_id = null) {
  /*
   * Displays the category forms for adding and editing products
   * Recurses to generate the branched view for subcategories
   */
  global $wpdb;
  $siteurl = get_option('siteurl'); 
  if(is_numeric($category_id)) {
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `group_id` = '$group_id' AND `active`='1' AND `id` != '$this_category' AND `category_parent` = '$category_id'  ORDER BY `id` ASC",ARRAY_A);
	} else {
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `group_id` = '$group_id' AND `active`='1' AND `id` != '$this_category' AND `category_parent` = '0'  ORDER BY `id` ASC",ARRAY_A);
	}
  foreach((array)$values as $option) {
    if($selected_id == $option['id']) {
      $selected = "selected='selected'";
		}
    
    $output .= "<option $selected value='".$option['id']."'>".str_repeat("-", $iteration).stripslashes($option['name'])."</option>\r\n";
    $output .= wpsc_category_options($group_id, $this_category, $option['id'], $iteration+1, $selected_id);
    $selected = "";
	}
  return $output;
}
  

function wpsc_uploaded_files()
  {
  global $wpdb;
  /*
$product_files = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}product_list", ARRAY_A);
  foreach($product_files as $product_file){
  	$filelist[] = $product_file['file'];
  }
  $filelist = implode(',', $filelist);
  $files = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}product_files WHERE id IN ($filelist)", ARRAY_A);
  $num = 0;
  foreach ($files as $file_data) {
  	if ($file_data != null){
  		$dirlist[$num]['display_filename'] = $file_data['filename'];
        $dirlist[$num]['file_id'] = $file_data['id'];
		$dirlist[$num]['real_filename'] = $file_data['idhash'];
  	}
  	$num++;
  }
*/
  
  $dir = @opendir(WPSC_FILE_DIR);
  $num = 0;
  while(($file = @readdir($dir)) !== false)
    {
    //filter out the dots, macintosh hidden files and any backup files
    if(($file != "..") && ($file != ".") && ($file != "product_files")  && ($file != "preview_clips") && !stristr($file, "~") && !( strpos($file, ".") === 0 ) && !strpos($file, ".old"))
      {
      $file_data = $wpdb->get_row("SELECT `id`,`filename` FROM `".$wpdb->prefix."product_files` WHERE `idhash` LIKE '".$file."' LIMIT 1",ARRAY_A);
      if($file_data != null)
        {
        $dirlist[$num]['display_filename'] = $file_data['filename'];
        $dirlist[$num]['file_id'] = $file_data['id'];
        }
        else
        {
        $dirlist[$num]['display_filename'] = $file;
        $dirlist[$num]['file_id'] = null;
        }        
      $dirlist[$num]['real_filename'] = $file;
      $num++;
      }
    }
  return $dirlist;
  }
  
  
function wpsc_select_product_file($product_id = null) {
  global $wpdb;

// Added by TRansom - put this somewhere else eventually
	$mime_icons = array(
			0 	=> 'image-x-generic.png'
		,'exe' 	=> 'application-x-executable.png'
		,'mp3' 	=> 'audio-x-generic.png'
		,'zip' 	=> 'package-x-generic.png'
		,'htm' 	=> 'text-html.png'
		,'txt' 	=> 'text-x-generic.png'
		,'php' 	=> 'text-x-script.png'
		,'avi' 	=> 'video-x-generic.png'
		,'doc' 	=> 'x-office-document.png'
		,'ppt' 	=> 'x-office-presentation.png'
		,'xls' 	=> 'x-office-spreadsheet.png'
		,'pdf' 	=> 'x-pdf.png'
		,'null'	=> 'null.png'
	);
// END - MimeIcons
  //return false;
  
  $file_list = wpsc_uploaded_files();
  $file_id = $wpdb->get_var("SELECT `file` FROM `".$wpdb->prefix."product_list` WHERE `id` = '".$product_id."' LIMIT 1");
  // Table Heading
  $output = "<span class='admin_product_notes select_product_note '>".TXT_WPSC_CHOOSE_DOWNLOADABLE_PRODUCT."</span>";
  $output .= "<div class='".((is_numeric($product_id)) ? "edit_" : "")."select_product_file'>";
  //$output .= "<div class='select_product_file'>";
  $output .= "<table id='select_product_table'>";
  $num = 0;
  // First row - No Product
  $output .= "<tr ".((($num % 2) > 0) ? '' : "class='alt'  id='product-uploader-first' ").">";
  $output .= "<td><img src='".WPSC_URL."/images/mimetypes/".$mime_icons['null']."' /></td>";
  $output .= "<td><input type='radio' name='select_product_file' value='.none.' id='select_product_file_$num' ".((!is_numeric($file_id) || ($file_id < 1)) ? "checked='checked'" : "")." /></td>";
  $output .= "<td><label for='select_product_file_$num'>".TXT_WPSC_SHOW_NO_PRODUCT."</label></td>";
  $output .= '<td></td><td></td><td></td>';
  $output .= "</tr>";
  
  $wpnonce = wp_create_nonce('wp-shopping-cart');
  if ( is_ssl() ) $auth_cookie = $_COOKIE[SECURE_AUTH_COOKIE]; else $auth_cookie = $_COOKIE[AUTH_COOKIE];
  foreach((array)$file_list as $file) {
    $num++;
    $output .= "<tr id='select_product_row_".$file['file_id']."' ".((($num % 2) > 0) ? '' : "class='alt'").">";
    
// Insert Mime-type   added by TRansom
	// determine extension
	$extension = substr(strrchr($file['display_filename'], "."), 1);
	// set icon to null
	$ext_icon = $mime_icons[0];
	// do we have an icon for this extension - insert it
	if( array_key_exists($extension,$mime_icons) ) $ext_icon = $mime_icons[$extension];
	$output .= "<td class='product_file_icon' ><img src='".WPSC_URL."/images/mimetypes/".$ext_icon."' /></td>";

//	Radio Button and File Name
    $output .="<td><input type='radio' name='select_product_file' value='".$file['real_filename']."' id='select_product_file_$num' ".((is_numeric($file_id) && ($file_id == $file['file_id'])) ? "checked='checked'" : "")." /></td>";
    $output .="<td class='ellipsis product_file_name' ><label class='ellipsis' for='select_product_file_$num'>".$file['display_filename']."</label></td>";

// Download - Added by transom
    $output .= "<td class='product_file_actions' >";
	$output .= "<a href='?page=".WPSC_DIR_NAME."/display-items.php&action=admin_preview&file_id=".$file['file_id']."&_wpnonce=".$wpnonce."' >";
	$output .= "<img src='".WPSC_URL."/images/download.gif' alt='' title='".TXT_WPSC_CLICKTODOWNLOAD."' />";
	$output .= "</a></td>";

// Delete File
    $output .= "<td class='product_file_actions' >";
    // Handled via jQuery and
    $output .= "<img id='select_product_delete_".$file['file_id']."' class='select_product_delete' src='".WPSC_URL."/images/trash.gif' title='".TXT_WPSC_DELETE_PRODUCT."' >";
    $output .= "</td>";

// Edit File
    $output .= "<td class='product_file_actions' >";
    $output .= "<a id='select_product_edit_".$file['file_id']."' class='select_product_edit' href='#' >";
    $output .= __('Edit','wp-shopping-cart');
    $output .= '</a>';
    $output .= "</td>";

// End of Row
	$output .= "</tr>";

// Preview (listen button)
    if( ($extension == 'mp3' || $extension == 'avi') && 
    	( ($file != null) && (function_exists('listen_button')) )) {
		$output .= "<tr".((($num % 2) > 0) ? '' : "class='alt'").">";
		$output .= "<td colspan='2'>";
		$output .= '<td class="product_file_actions" >';
		$output .= listen_button($file['idhash'], $file['file_id']);
		$output .= '</td>';
		$output .= '<td></td><td></td><td></td>';
		$output .= "</tr>";
	}

  }
// End of Table
  $output .= '<tr id="product-uploader-last"><td></td></tr>';
  $output .= "</table></div>";

  return $output;
}
  
  
function wpsc_select_variation_file($variation_ids, $variation_combination_id = null) {
  global $wpdb;
  //return false;
  $file_list = wpsc_uploaded_files();
  $file_id = 0;
  if((int)$variation_combination_id > 0) {
    $file_id = $wpdb->get_var("SELECT `file` FROM `{$wpdb->prefix}variation_priceandstock` WHERE `id` = '".(int)$variation_combination_id."' LIMIT 1");
  } else {
    $variation_combination_id = 0;
  }
  
  $unique_id_component = $variation_combination_id."_".str_replace(",","_",$variation_ids);
  
  
  $output = "<div class='variation_settings_contents'>\n\r";
  $output .= "<span class='admin_product_notes select_product_note '>".TXT_WPSC_CHOOSE_DOWNLOADABLE_VARIATIONS."</span>\n\r";
  $output .= "<div class='select_variation_file'>\n\r";
  
  $num = 0;
  $output .= "  <p>\n\r";
  $output .= "    <input type='radio' name='variation_priceandstock[{$variation_ids}][file]' value='0' id='select_variation_file{$unique_id_component}_{$num}' ".((!is_numeric($file_id) || ($file_id < 1)) ? "checked='checked'" : "")." />\n\r";
  $output .= "    <label for='select_variation_file{$unique_id_component}_{$num}'>".TXT_WPSC_SHOW_NO_PRODUCT."</label>\n\r";
  $output .= "  </p>\n\r";
  foreach((array)$file_list as $file) {
    $num++;
    $output .= "  <p>\n\r";
    $output .= "    <input type='radio' name='variation_priceandstock[{$variation_ids}][file]' value='".$file['file_id']."' id='select_variation_file{$unique_id_component}_{$num}' ".((is_numeric($file_id) && ($file_id == $file['file_id'])) ? "checked='checked'" : "")." />\n\r";
    $output .= "    <label for='select_variation_file{$unique_id_component}_{$num}'>".$file['display_filename']."</label>\n\r";
    $output .= "  </p>\n\r";
  }
  $output .= "</div>\n\r";
  $output .= "</div>\n\r";
  return $output;
}
  
  
function wpsc_list_product_themes($theme_name = null) {
  global $wpdb;
  $selected_theme = get_option('wpsc_selected_theme');
  if($selected_theme == '') {
    $selected_theme = 'default';
	}
    
  $theme_path = WPSC_FILE_PATH.'/themes/';
  $theme_list = nzshpcrt_listdir($theme_path);
  foreach($theme_list as $theme_file) {
    if(is_dir($theme_path.$theme_file) && is_file($theme_path.$theme_file."/".$theme_file.".css")) {
      $theme[$theme_file] = get_theme_data($theme_path.$theme_file."/".$theme_file.".css");
		}
	}  
  $output .= "<select name='wpsc_theme_list'>\n\r";
  foreach((array)$theme as $theme_file=>$theme_data) {
    if(stristr($theme_file, $selected_theme)) {
      $selected = "selected='true'";
		} else {
			$selected = "";
		}
    $output .= "<option value='$theme_file' $selected>".$theme_data['Name']."</option>\n\r";
	}
  $output .= "</select>\n\r";    
  return $output;
}



?>