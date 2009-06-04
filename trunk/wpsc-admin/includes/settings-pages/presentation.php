<?php
function options_categorylist() {
	global $wpdb;
	$current_default = get_option('wpsc_default_category');
	$group_sql = "SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active`='1'";
	$group_data = $wpdb->get_results($group_sql,ARRAY_A);
	$categorylist .= "<select name='wpsc_options[wpsc_default_category]'>";
	$categorylist .= "<option value='none' ".$selected." >".TXT_WPSC_SELECTACATEGORY."</option>";
	
		if(get_option('wpsc_default_category') == 'all')  {
				$selected = "selected='selected'";
			}
	
	$categorylist .= "<option value='all' ".$selected." >".TXT_WPSC_SELECTALLCATEGORIES."</option>";
	foreach($group_data as $group) {
			$cat_sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ({$group['id']}) AND `active`='1'";
			$category_data = $wpdb->get_results($cat_sql,ARRAY_A);
			if($category_data != null) {
		    	  			
				
				$categorylist .= "<optgroup label='{$group['name']}'>";;
				foreach((array)$category_data as $category)  {
					if(get_option('wpsc_default_category') == $category['id'])  {
						$selected = "selected='selected'";
					} else {
						$selected = "";
					}
					$categorylist .= "<option value='".$category['id']."' ".$selected." >".$category['name']."</option>";
				}
				$categorylist .= "</optgroup>";
			}
		}
	
	$categorylist .= "</select>";
	return $categorylist;
}
function wpsc_options_presentation(){
global $wpdb;
?>
<form name='cart_options' id='cart_options' method='post' action=''>
	<div id="options_presentation">
		<h2><?php echo TXT_WPSC_OPTIONS_PRESENTATION_HEADER; ?></h2>
		<?php 
		/* wpsc_setting_page_update_notification displays the wordpress styled notifications */
		wpsc_settings_page_update_notification(); ?>
		<h3 class="form_group"><?php echo TXT_WPSC_PRODUCTS_PAGE_SETTINGS;?></h3>
		<table class='wpsc_options form-table'>		
		<tr>
			<th scope="row"><?php echo TXT_WPSC_PRODUCT_DISPLAY;?>:</th>
			<td>
			<?php
			$display_pnp = get_option('product_view');
			$product_view1 = null;
			$product_view2 = null;
			$product_view3 = null;
			switch($display_pnp) {
				case "grid":
				if(function_exists('product_display_grid')) {
					$product_view3 = "selected ='selected'";
					break;
				}
				
				case "list":
				if(function_exists('product_display_list')) {
					$product_view2 = "selected ='selected'";
					break;
				}
				
				default:
				$product_view1 = "selected ='selected'";
				break;
			}
			
			if(get_option('list_view_quantity') == 1) {
				$list_view_quantity_value = "checked='checked'";
			} else {
				$list_view_quantity_value = '';
			}

			if(get_option('show_images_only') == 1) {
				$show_images_only_value = "checked='checked'";
			} else {
				$show_images_only_value = '';
			}
			if(get_option('display_variations') == 1) {
				$display_variations = "checked='checked'";
			} else {
				$display_variations = '';
			}
			if(get_option('display_description') == 1) {
				$display_description = "checked='checked'";
			} else {
				$display_description = '';
			}
			if(get_option('display_addtocart') == 1) {
				$display_addtocart = "checked='checked'";
			} else {
				$display_addtocart = '';
			}
			if(get_option('display_moredetails') == 1) {
				$display_moredetails= "checked='checked'";
			} else {
				$display_moredetails = '';
			}
			?>
			<select name='wpsc_options[product_view]' onchange="toggle_display_options(this.options[this.selectedIndex].value)">
				<option value='default' <?php echo $product_view1; ?>><?php echo TXT_WPSC_DEFAULT;?></option>
				<?php
				if(function_exists('product_display_list')) {
					?>
					<option value='list' <?php echo $product_view2; ?>><?php echo TXT_WPSC_LIST;?></option>
					<?php      
				}  else {
					?>
					<option value='list' disabled='disabled' <?php echo $product_view2; ?>><?php echo TXT_WPSC_LIST;?></option>
					<?php      
				  
				}
				
				if(function_exists('product_display_grid')) {
					?>
				<option value='grid' <?php echo $product_view3; ?>><?php echo TXT_WPSC_GRID;?></option>
					<?php   
				} else {
					?>
				<option value='grid' disabled='disabled' <?php echo $product_view3; ?>><?php echo TXT_WPSC_GRID;?></option>
					<?php 
				}
				?>
				</select>
				<?php 
				if(!function_exists('product_display_grid')) {
				?><a href='http://www.instinct.co.nz/e-commerce/shop/'><?php echo TXT_WPSC_PURCHASE_UNAVAILABLE; ?></a> <?php 
				}
				?>
					<div id='list_view_options' <?php if(is_null($product_view2)) { echo "style='display:none;'";} ?> >
						<input type='checkbox' value='1' name='wpsc_options[list_view_quantity]' id='list_view_quantity' <?php echo $list_view_quantity_value;?> />
						<label for='list_view_options'><?php echo TXT_WPSC_ADJUSTABLE_QUANTITY;?></label>
					</div>
					<div id='grid_view_options' <?php echo $list_view_quantity_style;?> <?php if(is_null($product_view3)) { echo "style='display:none;'";} ?>>
					
						<input type='text'  name='wpsc_options[grid_number_per_row]' id='grid_number_per_row' size='1' value='<?php echo get_option('grid_number_per_row');?>' />
						<label for='grid_number_per_row'><?php echo TXT_SHOW_GRID_PER_ROW;?></label><br />
						
						
						<input type='hidden' value='0' name='wpsc_options[show_images_only]' />
						<input type='checkbox' value='1' name='wpsc_options[show_images_only]' id='show_images_only' <?php echo $show_images_only_value;?> />
						<label for='show_images_only'><?php echo TXT_SHOW_IMAGES_ONLY;?></label><br />
						
						
						<input type='hidden' value='0' name='wpsc_options[display_variations]' />
						<input type='checkbox' value='1' name='wpsc_options[display_variations]' id='display_variations' <?php echo $display_variations;?> />
						<label for='display_variations'><?php echo TXT_DISPLAY_VARIATIONS;?></label><br />
						
						
						<input type='hidden' value='0' name='wpsc_options[display_description]' />
						<input type='checkbox' value='1' name='wpsc_options[display_description]' id='display_description' <?php echo $display_description;?> />
						<label for='display_description'><?php echo TXT_DISPLAY_DESCRIPTION;?></label><br />
						
						
						<input type='hidden' value='0' name='wpsc_options[display_addtocart]' />
						<input type='checkbox' value='1' name='wpsc_options[display_addtocart]' id='display_addtocart' <?php echo $display_addtocart;?> />
						<label for='display_addtocart'><?php echo TXT_DISPLAY_ADDTOCART;?></label><br />
						
						<input type='hidden' value='0' name='wpsc_options[display_moredetails]' />
						<input type='checkbox' value='1' name='wpsc_options[display_moredetails]' id='display_moredetails' <?php echo $display_moredetails;?> />
						<label for='display_moredetails'><?php echo TXT_DISPLAY_MOREDETAILS;?></label>

					</div>
				</td>
			</tr>
			<?php
			//  }
			?>			
								
			<tr>
				<th scope="row"><?php echo TXT_WPSC_SELECT_THEME;?>:</th>
				<td>
				<?php
				echo wpsc_list_product_themes();
				?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php echo TXT_WPSC_DEFAULTCATEGORY; ?>:</th>
				<td>
				<?php echo options_categorylist(); ?>
				</td>
			</tr>
			<?php
				$wpsc_sort_by = get_option('wpsc_sort_by');
				switch($wpsc_sort_by) {    
					case 'name':
					$wpsc_sort_by1 = "selected ='selected'";
					break;
					
					case 'price':
					$wpsc_sort_by2 = "selected ='selected'";
					break;
					
					case 'id':
					default:
					$wpsc_sort_by3 = "selected ='selected'";
					break;
				}
			?>
			<tr>
				<th scope="row">
					<?php echo TXT_WPSC_SORT_PRODUCT_BY;?>:
				</th>
				<td>
					<select name='wpsc_options[wpsc_sort_by]'>
					<option <?php echo $wpsc_sort_by1; ?> value='name'><?php echo TXT_WPSC_NAME;?></option>
					<option <?php echo $wpsc_sort_by2; ?> value='price'><?php echo TXT_WPSC_PRICE;?></option>
					<option <?php echo $wpsc_sort_by3; ?> value='id'><?php echo TXT_WPSC_TIME_UPLOADED;?></option>
					</select>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php echo TXT_WPSC_SHOWPOSTAGEANDPACKAGING;?>:</th>
				<td>
				<?php
				$display_pnp = get_option('display_pnp');
				$display_pnp1 = "";
				$display_pnp2 = "";
				switch($display_pnp) {
					case 0:
					$display_pnp2 = "checked ='checked'";
					break;
					
					case 1:
					$display_pnp1 = "checked ='checked'";
					break;
				}
	
				?>
				<input type='radio' value='1' name='wpsc_options[display_pnp]' id='display_pnp1' <?php echo $display_pnp1; ?> /> <label for='display_pnp1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
				<input type='radio' value='0' name='wpsc_options[display_pnp]' id='display_pnp2' <?php echo $display_pnp2; ?> /> <label for='display_pnp2'><?php echo TXT_WPSC_NO;?></label>
				</td>
			</tr>
				
			<tr>
				<th scope="row"><?php echo TXT_WPSC_SHOW_BREADCRUMBS;?>:</th>
				<td>
				<?php
				$show_breadcrumbs = get_option('show_breadcrumbs');
				$show_breadcrumbs1 = "";
				$show_breadcrumbs2 = "";
				switch($show_breadcrumbs) {
					case 0:
					$show_breadcrumbs2 = "checked ='checked'";
					break;
					
					case 1:
					$show_breadcrumbs1 = "checked ='checked'";
					break;
				}
	
				?>
				<input type='radio' value='1' name='wpsc_options[show_breadcrumbs]' id='show_breadcrumbs1' <?php echo $show_breadcrumbs1; ?> /> <label for='show_breadcrumbs1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
				<input type='radio' value='0' name='wpsc_options[show_breadcrumbs]' id='show_breadcrumbs2' <?php echo $show_breadcrumbs2; ?> /> <label for='show_breadcrumbs2'><?php echo TXT_WPSC_NO;?></label>
				</td>
			</tr>							
				
			<tr>
				<th scope="row"><?php echo TXT_WPSC_SHOWPRODUCTRATINGS;?>:</th>
				<td>
				<?php
				$display_pnp = get_option('product_ratings');
				$product_ratings1 = "";
				$product_ratings2 = "";
				switch($display_pnp) {
					case 0:
					$product_ratings2 = "checked ='checked'";
					break;
					
					case 1:
					$product_ratings1 = "checked ='checked'";
					break;
				}
	
				?>
				<input type='radio' value='1' name='wpsc_options[product_ratings]' id='product_ratings1' <?php echo $product_ratings1; ?> /> <label for='product_ratings1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
				<input type='radio' value='0' name='wpsc_options[product_ratings]' id='product_ratings2' <?php echo $product_ratings2; ?> /> <label for='product_ratings2'><?php echo TXT_WPSC_NO;?></label>
				</td>
			</tr>

	<!-- // Adrian - options for displaying number of products per category -->      
				
			<tr>
				<th scope="row">
				<?php echo TXT_WPSC_SHOW_CATEGORY_COUNT;?>:
				</th>
				<td>
				<?php
				$display_pnp = get_option('show_category_count');
				$show_category_count1 = "";
				$show_category_count2 = "";
				switch($display_pnp) {
					case 0:
					$show_category_count2 = "checked ='checked'";
					break;
					
					case 1:
					$show_category_count1 = "checked ='checked'";
					break;
				}
	
				?>
				<input type='radio' value='1' name='wpsc_options[show_category_count]' id='show_category_count1' <?php echo $show_category_count1; ?> /> <label for='show_category_count1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
				<input type='radio' value='0' name='wpsc_options[show_category_count]' id='show_category_count2' <?php echo $show_category_count2; ?> /> <label for='show_category_count2'><?php echo TXT_WPSC_NO;?></label>
				</td>
			</tr>
				
	<!-- // Adrian - options for displaying category display type -->      
				
			<tr>
				<th scope="row">
				<?php echo TXT_WPSC_CATSPRODS_DISPLAY_TYPE;?>:
				</th>
				<td>
				<?php
				$display_pnp = get_option('catsprods_display_type');
				$catsprods_display_type1 = "";
				$catsprods_display_type2 = "";
				switch($display_pnp) {
					case 0:
					$catsprods_display_type1 = "checked ='checked'";
					break;
					
					case 1:
					$catsprods_display_type2 = "checked ='checked'";
					break;
				}
	
				?>
				<input type='radio' value='0' name='wpsc_options[catsprods_display_type]' id='catsprods_display_type1' <?php echo $catsprods_display_type1; ?> /> <label for='catsprods_display_type1'><?php echo TXT_WPSC_CATSPRODS_TYPE_CATONLY;?></label> &nbsp;
				<input type='radio' value='1' name='wpsc_options[catsprods_display_type]' id='catsprods_display_type2' <?php echo $catsprods_display_type2; ?> /> <label for='catsprods_display_type2'><?php echo TXT_WPSC_CATSPRODS_TYPE_SLIDEPRODS;?></label>
				</td>
			</tr>
				
	<?php
	if(function_exists('gold_shpcrt_search_form')) {
		?>
			<tr>
				<th scope="row"><?php echo TXT_WPSC_SHOW_SEARCH;?>:</th>
				<td>
				<?php
				$display_pnp = get_option('show_search');
				$show_search1 = "";
				$show_search2 = "";
				switch($display_pnp) {
					case 0:
					$show_search2 = "checked ='checked'";
					break;
					
					case 1:
					$show_search1 = "checked ='checked'";
					break;
				}
			
				$display_ad_pnp = get_option('show_advanced_search');
				$show_advanced_search = "";
				if($display_ad_pnp == 1) {
					$show_advanced_search = "checked ='checked'";
				}
			
				$display_live_pnp = get_option('show_live_search');
				if($display_ad_pnp == 1) {
					$show_live_search = "checked ='checked'";
				}
			
				if ($show_search1 != "checked ='checked'") {
					$dis = "style='display:none;'";
				}
				?>
				<input type='radio' onclick='jQuery("#wpsc_advanced_search").show()' value='1' name='wpsc_options[show_search]' id='show_search1' <?php echo $show_search1; ?> /> <label for='show_search1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
				<input type='radio' onclick='jQuery("#wpsc_advanced_search").hide()' value='0' name='wpsc_options[show_search]' id='show_search2' <?php echo $show_search2; ?> /> <label for='show_search2'><?php echo TXT_WPSC_NO;?></label>
				
			<div <?php echo $dis;?> id='wpsc_advanced_search'>
				<input  type='checkbox' name='wpsc_options[show_advanced_search]' id='show_advanced_search' <?php echo $show_advanced_search; ?> />
				<?php echo TXT_WPSC_SHOWADVANCEDSEARCH;?><br />
				<input type='checkbox' name='wpsc_options[show_live_search]' id='show_live_search' <?php echo $show_live_search; ?> />
				<?php echo TXT_WPSC_SHOWLIVESEARCH;?>
			</div>
			
				</td>
			</tr>
		<?php
		}
	?>
			<tr>
				<th scope="row">
				<?php echo TXT_WPSC_DISPLAY_FANCY_NOTIFICATIONS;?>:
				</th>
				<td>
				<?php
				$fancy_notifications = get_option('fancy_notifications');
				$fancy_notifications1 = "";
				$fancy_notifications2 = "";
				switch($fancy_notifications)
					{
					case 0:
					$fancy_notifications2 = "checked ='checked'";
					break;
					
					case 1:
					$fancy_notifications1 = "checked ='checked'";
					break;
					}
				?>
				<input type='radio' value='1' name='wpsc_options[fancy_notifications]' id='fancy_notifications1' <?php echo $fancy_notifications1; ?> /> <label for='fancy_notifications1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
				<input type='radio' value='0' name='wpsc_options[fancy_notifications]' id='fancy_notifications2' <?php echo $fancy_notifications2; ?> /> <label for='fancy_notifications2'><?php echo TXT_WPSC_NO;?></label>
				</td>
			</tr>  
	
			<tr>
				<th scope="row"><?php echo TXT_WPSC_REPLACE_PAGE_TITLE;?>:</th>
			<td>
			<?php
			$wpsc_replace_page_title = get_option('wpsc_replace_page_title');
			$wpsc_replace_page_title1 = "";
			$wpsc_replace_page_title2 = "";
			switch($wpsc_replace_page_title) {
			case 0:
			$wpsc_replace_page_title2 = "checked ='checked'";
			break;
			
			case 1:
			$wpsc_replace_page_title1 = "checked ='checked'";
			break;
			}
			?>
			<input type='radio' value='1' name='wpsc_options[wpsc_replace_page_title]' id='wpsc_replace_page_title1' <?php echo $wpsc_replace_page_title1; ?> /> <label for='wpsc_replace_page_title1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
			<input type='radio' value='0' name='wpsc_options[wpsc_replace_page_title]' id='wpsc_replace_page_title2' <?php echo $wpsc_replace_page_title2; ?> /> <label for='wpsc_replace_page_title2'><?php echo TXT_WPSC_NO;?></label>
			</td>
			</tr>
	
			<tr>
				<th scope="row"><?php echo TXT_WPSC_USE_THEME_ENGINE;?>:</th>
				<td>
				<?php
				$wpsc_use_theme_engine = get_option('wpsc_use_theme_engine');
				$wpsc_use_theme_engine1 = "";
				$wpsc_use_theme_engine2 = "";
				switch($wpsc_use_theme_engine) {
					case 0:
					$wpsc_use_theme_engine2 = "checked ='checked'";
					break;
					
					case 1:
					$wpsc_use_theme_engine1 = "checked ='checked'";
					break;
				}
				?>
				<input type='radio' value='1' name='wpsc_options[wpsc_use_theme_engine]' id='wpsc_use_theme_engine1' <?php echo $wpsc_use_theme_engine1; ?> /> <label for='wpsc_use_theme_engine1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
				<input type='radio' value='0' name='wpsc_options[wpsc_use_theme_engine]' id='wpsc_use_theme_engine2' <?php echo $wpsc_use_theme_engine2; ?> /> <label for='wpsc_use_theme_engine2'><?php echo TXT_WPSC_NO;?></label>
				</td>
			</tr>
	
		</table> 
		
		<h3 class="form_group"><?php echo TXT_WPSC_CARTSETTINGS;?></h3>
		<table class='wpsc_options form-table'>
			<tr>
				<th scope="row"><?php echo TXT_WPSC_CARTLOCATION;?>:</th>
				<td>
				<?php
				$cart_location = get_option('cart_location');
				$cart1 = "";
				$cart2 = "";
				switch($cart_location) {
					case 1:
					$cart1 = "checked ='checked'";
					break;
					
					case 2:
					$cart2 = "checked ='checked'";
					break;
					
					case 3:
					$cart3 = "checked ='checked'";
					break;
					
					case 4:
					$cart4 = "checked ='checked'";
					break;
					
					case 5:
					$cart5 = "checked ='checked'";
					break;
				} 
				if(function_exists('register_sidebar_widget')) {
					?>
					<input type='radio' value='1' onclick='hideelement1("dropshop_option", this.value)' disabled='disabled'  name='wpsc_options[cart_location]' id='cart1' <?php echo $cart1; ?> /> <label style='color: #666666;' for='cart1'><?php echo TXT_WPSC_SIDEBAR;?></label> &nbsp;
					<?php
				} else {
					?>
					<input type='radio' value='1' name='wpsc_options[cart_location]' id='cart1' <?php echo $cart1; ?> /> <label for='cart1'><?php echo TXT_WPSC_SIDEBAR;?></label> &nbsp;
					<?php
				}
				?>
				<input type='radio' onclick='hideelement1("dropshop_option", this.value)' value='2' name='wpsc_options[cart_location]' id='cart2' <?php echo $cart2; ?> /> <label for='cart2'><?php echo TXT_WPSC_PAGE;?></label> &nbsp;
				<?php
				if(function_exists('register_sidebar_widget')) {
					?>
					<input type='radio' value='4' onclick='hideelement1("dropshop_option", this.value)' name='wpsc_options[cart_location]' id='cart4' <?php echo $cart4; ?> /> <label for='cart4'><?php echo TXT_WPSC_WIDGET;?></label> &nbsp;
					<?php
				} else {
					?>
					<input type='radio'  disabled='disabled' value='4' name='wpsc_options[cart_location]' id='cart4' alt='<?php echo TXT_WPSC_NEEDTOENABLEWIDGET;?>' title='<?php echo TXT_WPSC_NEEDTOENABLEWIDGET;?>' <?php echo $cart4; ?> /> <label style='color: #666666;' for='cart4' title='<?php echo TXT_WPSC_NEEDTOENABLEWIDGET;?>'><?php echo TXT_WPSC_WIDGET;?></label> &nbsp;
					<?php
				}
				
				if(function_exists('drag_and_drop_cart')) {
					?>
					<input type='radio' onclick='hideelement1("dropshop_option", this.value)' value='5' name='wpsc_options[cart_location]' id='cart5' <?php echo $cart5; ?> /> <label for='cart5'><?php echo TXT_WPSC_GOLD_DROPSHOP;?></label> &nbsp;
					<?php
				} else {
					?>
					<input type='radio' disabled='disabled' value='5' name='wpsc_options[cart_location]' id='cart5' alt='<?php echo TXT_WPSC_NEEDTOENABLEWIDGET;?>' title='<?php echo TXT_WPSC_NEEDTOENABLEDROPSHOP;?>' <?php echo $cart5; ?> /> <label style='color: #666666;' for='cart5' title='<?php echo TXT_WPSC_NEEDTOENABLEDROPSHOP;?>'><?php echo TXT_WPSC_GOLD_DROPSHOP;?></label> &nbsp;
					<?php
				}
					?>
				<input type='radio' onclick='hideelement1("dropshop_option", this.value)' value='3' name='wpsc_options[cart_location]' id='cart3' <?php echo $cart3; ?> /> <label for='cart3'><?php echo TXT_WPSC_MANUAL;?> <span style='font-size: 7pt;'>(PHP code: &lt;?php echo nzshpcrt_shopping_basket(); ?&gt; )</span></label>
		<div  style='display: <?php if (isset($cart5)) { echo "block"; } else { echo "none"; } ?>;'  id='dropshop_option'>
		<p>
		<input type="radio" id="drop1" value="all" <?php if (get_option('dropshop_display') == 'all') { echo "checked='checked'"; } ?> name="wpsc_options[dropshop_display]" /><label for="drop1"><?php echo TXT_WPSC_SHOW_DROPSHOP_ALL;?></label>
		<input type="radio" id="drop2" value="product" <?php if (get_option('dropshop_display') == 'product') { echo "checked='checked'"; } ?> name="wpsc_options[dropshop_display]"/><label for="drop2"><?php echo TXT_WPSC_SHOW_DROPSHOP_PRODUCT;?></label>
		</p>
		<p>
		<input type="radio" id="wpsc_dropshop_theme1" value="light" <?php if (get_option('wpsc_dropshop_theme') != 'dark') { echo "checked='checked'"; } ?> name="wpsc_options[wpsc_dropshop_theme]" /><label for="wpsc_dropshop_theme1"><?php echo TXT_WPSC_DROPSHOP_LIGHT;?></label>
		<input type="radio" id="wpsc_dropshop_theme2" value="dark" <?php if (get_option('wpsc_dropshop_theme') == 'dark') { echo "checked='checked'"; } ?> name="wpsc_options[wpsc_dropshop_theme]"/><label for="wpsc_dropshop_theme2"><?php echo TXT_WPSC_DROPSHOP_DARK;?></label>
		
		</p>
		</div>
				</td>
			</tr>
			
			
<tr>
					<th scope="row">
					<?php echo TXT_WPSC_SHOW_SLIDING_CART;?>:
					</th>
					<td>
					<?php
					$display_pnp = get_option('show_sliding_cart');
					$show_sliding_cart1 = "";
					$show_sliding_cart2 = "";
					switch($display_pnp) {
						case 0:
						$show_sliding_cart2 = "checked ='checked'";
						break;
						
						case 1:
						$show_sliding_cart1 = "checked ='checked'";
						break;
					}
		
					?>
					<input type='radio' value='1' name='wpsc_options[show_sliding_cart]' id='show_sliding_cart1' <?php echo $show_sliding_cart1; ?> /> <label for='show_sliding_cart1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
					<input type='radio' value='0' name='wpsc_options[show_sliding_cart]' id='show_sliding_cart2' <?php echo $show_sliding_cart2; ?> /> <label for='show_sliding_cart2'><?php echo TXT_WPSC_NO;?></label>
					</td>
				</tr>
				
			<tr>
				<th scope="row">
				<?php echo TXT_WPSC_DISPLAY_PLUSTAX;?>:
				</th>
				<td>
				<?php
				$add_plustax = get_option('add_plustax');
				$add_plustax1 = "";
				$add_plustax2 = "";
				switch($add_plustax) {
					case 0:
					$add_plustax2 = "checked ='checked'";
					break;
					
					case 1:
					$add_plustax1 = "checked ='checked'";
					break;
				}
				?>
				<input type='radio' value='1' name='wpsc_options[add_plustax]' id='add_plustax1' <?php echo $add_plustax1; ?> /> <label for='add_plustax1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
				<input type='radio' value='0' name='wpsc_options[add_plustax]' id='add_plustax2' <?php echo $add_plustax2; ?> /> <label for='add_plustax2'><?php echo TXT_WPSC_NO;?></label>
				</td>
			</tr>
			
			</table> 
			
			
			
			<h3 class="form_group"><?php echo TXT_WPSC_GROUP_SETTINGS;?></h3>
			<table class='wpsc_options form-table'>
				
				<tr>
					<th scope="row"><?php echo TXT_WPSC_SHOW_CATEGORY_DESCRIPTION;?>:</th>
					<td>
					<?php
					$wpsc_category_description = get_option('wpsc_category_description');
					$wpsc_category_description1 = "";
					$wpsc_category_description2 = "";
					switch($wpsc_category_description) {    
						case '1':
						$wpsc_category_description1 = "checked ='checked'";
						break;
						
						case '0':
						default:
						$wpsc_category_description2 = "checked ='checked'";
						break;
					}
		
					?>
					<input type='radio' value='1' name='wpsc_options[wpsc_category_description]' id='wpsc_category_description1' <?php echo $wpsc_category_description1; ?> /> <label for='wpsc_category_description1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
					<input type='radio' value='0' name='wpsc_options[wpsc_category_description]' id='wpsc_category_description2' <?php echo $wpsc_category_description2; ?> /> <label for='wpsc_category_description2'><?php echo TXT_WPSC_NO;?></label>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
					<?php echo TXT_WPSC_SHOWCATEGORYTHUMBNAILS;?>:
					</th>
					<td>
					<?php
					$show_category_thumbnails = get_option('show_category_thumbnails');
					$show_category_thumbnails1 = "";
					$show_category_thumbnails2 = "";
					switch($show_category_thumbnails) {
						case 0:
						$show_category_thumbnails2 = "checked ='checked'";
						break;
						
						case 1:
						$show_category_thumbnails1 = "checked ='checked'";
						break;
					}
		
					?>
					<input type='radio' value='1' name='wpsc_options[show_category_thumbnails]' id='show_category_thumbnails1' <?php echo $show_category_thumbnails1; ?> /> <label for='show_category_thumbnails1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
					<input type='radio' value='0' name='wpsc_options[show_category_thumbnails]' id='show_category_thumbnails2' <?php echo $show_category_thumbnails2; ?> /> <label for='show_category_thumbnails2'><?php echo TXT_WPSC_NO;?></label>
					</td>
				</tr>
			</table> 
			
			
			<h3 class="form_group"><?php echo TXT_WPSC_THUMBNAILSETTINGS;?></h3>
			<table class='wpsc_options form-table'>
			<?php
				if(function_exists("getimagesize")) {
				?>
				<tr>
					<th scope="row"><?php echo TXT_WPSC_PRODUCTTHUMBNAILSIZE;?>:</th>
					<td>
					<?php echo TXT_WPSC_HEIGHT;?>:<input type='text' size='6' name='wpsc_options[product_image_height]' value='<?php echo get_option('product_image_height'); ?>' /> <?php echo TXT_WPSC_WIDTH;?>:<input type='text' size='6' name='wpsc_options[product_image_width]' value='<?php echo get_option('product_image_width'); ?>' /> <br /><span class='small'></span>	</td>
				</tr>
				<tr>
					<th scope="row">
					<?php echo TXT_WPSC_CATEGORYTHUMBNAILSIZE;?>:
					</th>
					<td>
					<?php echo TXT_WPSC_HEIGHT;?>:<input type='text' size='6' name='wpsc_options[category_image_height]' value='<?php echo get_option('category_image_height'); ?>' /> <?php echo TXT_WPSC_WIDTH;?>:<input type='text' size='6' name='wpsc_options[category_image_width]' value='<?php echo get_option('category_image_width'); ?>' /> <span class='small'></span>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
					<?php echo TXT_WPSC_SINGLE_PRODUCTTHUMBNAILSIZE;?>:
					</th>
					<td>
					<?php echo TXT_WPSC_HEIGHT;?>:<input type='text' size='6' name='wpsc_options[single_view_image_height]' value='<?php echo get_option('single_view_image_height'); ?>' /> <?php echo TXT_WPSC_WIDTH;?>:<input type='text' size='6' name='wpsc_options[single_view_image_width]' value='<?php echo get_option('single_view_image_width'); ?>' /> <span class='small'></span>
					</td>
				</tr>
		
			<?php
				}
			?>            
		
				<tr>
					<th scope="row"><?php echo TXT_WPSC_SHOWTHUMBNAILS;?>:</th>
					<td>
					<?php
					$show_thumbnails = get_option('show_thumbnails');
					$show_thumbnails1 = "";
					$show_thumbnails2 = "";
					switch($show_thumbnails) {
						case 0:
						$show_thumbnails2 = "checked ='checked'";
						break;
						
						case 1:
						$show_thumbnails1 = "checked ='checked'";
						break;
					}
		
					?>
					<input type='radio' value='1' name='wpsc_options[show_thumbnails]' id='show_thumbnails1' <?php echo $show_thumbnails1; ?> /> <label for='show_thumbnails1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
					<input type='radio' value='0' name='wpsc_options[show_thumbnails]' id='show_thumbnails2' <?php echo $show_thumbnails2; ?> /> <label for='show_thumbnails2'><?php echo TXT_WPSC_NO;?></label>
					</td>
				</tr>
				<?php
	if(function_exists('gold_shpcrt_display_gallery')) {
	?>
	<tr>
	  <th scope="row">
	  <?php echo TXT_WPSC_SHOW_GALLERY;?>:
	  </th>
	  <td>
	  <?php
	  $display_pnp = get_option('show_gallery');
	  $show_gallery1 = "";
	  $show_gallery2 = "";
	  switch($display_pnp) {
	    case 0:
	    $show_gallery2 = "checked ='checked'";
	    break;
	    
	    case 1:
	    $show_gallery1 = "checked ='checked'";
	    break;
	  }
	  ?>
	  <input type='radio' value='1' name='wpsc_options[show_gallery]' id='show_gallery1' <?php echo $show_gallery1; ?> /> <label for='show_gallery1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
	  <input type='radio' value='0' name='wpsc_options[show_gallery]' id='show_gallery2' <?php echo $show_gallery2; ?> /> <label for='show_gallery2'><?php echo TXT_WPSC_NO;?></label>
	  </td>
	</tr>
	<?php
	}
				?>
			</table>
			
					
			<h3 class="form_group"><?php echo TXT_WPSC_PAGESETTINGS;?></h3>
			<table class='wpsc_options form-table'>
				<tr>
					<th scope="row">
					<?php echo TXT_WPSC_USE_PAGINATION;?>:
					</th>
					<td>
					<?php
					$use_pagination = get_option('use_pagination');
					$use_pagination1 = "";
					$use_pagination2 = "";
					switch($use_pagination) {
						case 0:
						$use_pagination2 = "checked ='checked'";
						$page_count_display_state = 'style=\'display: none;\'';
						break;
						
						case 1:
						$use_pagination1 = "checked ='checked'";
						$page_count_display_state = '';
						break;
					}
					?>
					<input onclick='jQuery("#wpsc_products_per_page").show()'  type='radio' value='1' name='wpsc_options[use_pagination]' id='use_pagination1' <?php echo $use_pagination1; ?> /> <label for='use_pagination1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
					<input onclick='jQuery("#wpsc_products_per_page").hide()' type='radio' value='0' name='wpsc_options[use_pagination]' id='use_pagination2' <?php echo $use_pagination2; ?> /> <label for='use_pagination2'><?php echo TXT_WPSC_NO;?></label><br />
					<div id='wpsc_products_per_page' <?php echo $page_count_display_state; ?> >
					<input type='text' size='6' name='wpsc_options[wpsc_products_per_page]' value='<?php echo get_option('wpsc_products_per_page'); ?>' /> <?php echo TXT_WPSC_OPTION_PRODUCTS_PER_PAGE; ?>
					</div>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<?php echo TXT_WPSC_PAGE_NUMBER_POSITION;?>:
					</th>
					<td>
						<input type='radio' value='1' name='wpsc_options[wpsc_page_number_position]' id='wpsc_page_number_position1' <?php if (get_option('wpsc_page_number_position') == 1) { echo "checked='checked'"; } ?> /><label for='wpsc_page_number_position1'><?php echo TXT_WPSC_PAGENUMBER_POSITION_TOP; ?></label>&nbsp;
						<input type='radio' value='2' name='wpsc_options[wpsc_page_number_position]' id='wpsc_page_number_position2' <?php if (get_option('wpsc_page_number_position') == 2) { echo "checked='checked'"; } ?> /><label for='wpsc_page_number_position2'><?php echo TXT_WPSC_PAGENUMBER_POSITION_BOTTOM; ?></label>&nbsp;
						<input type='radio' value='3' name='wpsc_options[wpsc_page_number_position]' id='wpsc_page_number_position3' <?php if (get_option('wpsc_page_number_position') == 3) { echo "checked='checked'"; } ?> /><label for='wpsc_page_number_position3'><?php echo TXT_WPSC_PAGENUMBER_POSITION_BOTH; ?></label>
						<br />
					</td>
				</tr>    
			</table>
	
				
			<h3 class="form_group"><?php echo TXT_WPSC_COMMENTSETTINGS;?></h3>
			<table class='wpsc_options form-table'>
				<tr>
					<th scope="row">
					<?php echo TXT_WPSC_ENABLE_COMMENTS;?>:
					<a href="http://intensedebate.com/" title="IntenseDebate comments enhance and encourage conversation on your blog or website" target="_blank"><img src="<?php echo WPSC_URL; ?>/images/intensedebate-logo.png" alt="intensedebate-logo" title="IntenseDebate"/></a>
					</th>
					<td>
					<?php
					$enable_comments = get_option('wpsc_enable_comments');
					$enable_comments1 = "";
					$enable_comments2 = "";
					switch($enable_comments) {
						case 1:
						$enable_comments1 = "checked ='checked'";
						$intense_debate_account_id_display_state = '';
						break;
						
						default:
						case 0:
						$enable_comments2 = "checked ='checked'";
						$intense_debate_account_id_display_state = 'style=\'display: none;\'';
						break;
					}
					?>
					<input onclick='jQuery("#wpsc_enable_comments,.wpsc_comments_details").show()'  type='radio' value='1' name='wpsc_options[wpsc_enable_comments]' id='wpsc_enable_comments1' <?php echo $enable_comments1; ?> /> <label for='wpsc_enable_comments1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
					<input onclick='jQuery("#wpsc_enable_comments,.wpsc_comments_details").hide()' type='radio' value='0' name='wpsc_options[wpsc_enable_comments]' id='wpsc_enable_comments2' <?php echo $enable_comments2; ?> /> <label for='wpsc_enable_comments1'><?php echo TXT_WPSC_NO;?></label><br />
					<div id='wpsc_enable_comments' <?php echo $intense_debate_account_id_display_state; ?> >
					<?php echo TXT_WPSC_INTENSE_DEBATE_ACCOUNT_ID; ?>:<br/>
					<input type='text' size='30' name='wpsc_options[wpsc_intense_debate_account_id]' value='<?php echo get_option('wpsc_intense_debate_account_id'); ?>' /><br/>
					<small><a href='http://intensedebate.com/sitekey/' title='Help finding the Account ID'><?php _e('Help on finding the Account ID'); ?></a></small>
					</div>
					</td>
				</tr>
		
				<tr>
					
					<th scope="row">
					<div class='wpsc_comments_details' <?php echo $intense_debate_account_id_display_state ?> >
						<?php echo TXT_WPSC_COMMENTS_WHICH_PRODUCTS;?>:
					</div>
					</th>
					<td>
					<div class='wpsc_comments_details' <?php echo $intense_debate_account_id_display_state ?> >
						<input type='radio' value='1' name='wpsc_options[wpsc_comments_which_products]' id='wpsc_comments_which_products1' <?php if (get_option('wpsc_comments_which_products') == 1 || !get_option('wpsc_comments_which_products')) { echo "checked='checked'"; } ?> /><label for='wpsc_comments_which_products1'>All Products</label>&nbsp;
						<input type='radio' value='2' name='wpsc_options[wpsc_comments_which_products]' id='wpsc_comments_which_products2' <?php if (get_option('wpsc_comments_which_products') == 2) { echo "checked='checked'"; } ?> /><label for='wpsc_comments_which_products2'>Per Product</label>&nbsp;
						<br />
					</div>
					</td>
					
				</tr>
			</table> 
			
			<?php
		  /* here end the presentation options */						  
		  ?>
			<div class="submit">
				<input type='hidden' name='wpsc_admin_action' value='submit_options' />
				<input type="submit" value="<?php echo TXT_WPSC_UPDATE_BUTTON;?>" name="updateoption" />
			</div>
		</div>
		</form>				
<?php						
}					
						
						


?>