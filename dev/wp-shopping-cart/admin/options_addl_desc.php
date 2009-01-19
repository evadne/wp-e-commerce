<?php
/*
 *		Functions to add multiple description fields to wp-shopping-cart
 *		Created by TRansom (http://1bigidea.com)
 */

DEFINE(WPSC_ADDL_DESC_OPTION,'addl_desc');

// Functions for the Options Page

function wpsc_addl_desc_options_update() {

	if( !isset($_REQUEST['presentation:addl_desc']) ) return;
	$wpsc_site_options = get_option('wpsc_site_options');
	if( !isset($wpsc_site_options) ) $wpsc_site_options = array();
	if( !isset($wpsc_site_options['presentation']) ) $wpsc_site_options['presentation'] = array();
	if( !isset($wpsc_site_options['presentation'][WPSC_ADDL_DESC_OPTION]) ) $wpsc_site_options['presentation'][WPSC_ADDL_DESC_OPTION] = array();

	$descriptions = explode(',', preg_replace('/ *, */',',',$_REQUEST['presentation:addl_desc']));
	$wpsc_site_options['presentation'][WPSC_ADDL_DESC_OPTION] = $descriptions;
	update_option('wpsc_site_options',$wpsc_site_options);
	return;
}
add_action('wpsc_options_update','wpsc_addl_desc_options_update');

function wpsc_addl_desc_options_form() {
	
	$wpsc_site_options = get_option('wpsc_site_options');

	if( !isset($wpsc_site_options['presentation'][WPSC_ADDL_DESC_OPTION]) ) $wpsc_site_options['presentation'][WPSC_ADDL_DESC_OPTION] = array();
	$addl_desc = '';
	if( !wpsc_addl_desc_is_empty($wpsc_site_options['presentation'][WPSC_ADDL_DESC_OPTION]) ) $addl_desc = implode(', ', $wpsc_site_options['presentation'][WPSC_ADDL_DESC_OPTION]);
?>
<th scope="row"><?php _e('Titles for Extra Descriptions','wpsc'); ?></th>
<td>
<label for="presentation:addl_desc"><?php _e('Separated by commas - In addition to Additional Description (default)'); ?></label><br />
<input name="presentation:addl_desc" type="text" size="48" maxlength="2048" value="<?php echo $addl_desc; ?>" />
</td>
<?php
	return;
}
add_action('wpsc_options_present_productpage_end','wpsc_addl_desc_options_form');

function wpsc_addl_desc_is_empty($descriptions) {

	if( is_array($descriptions) ) {
		if( count($descriptions) > 0 || $descriptions[0] != '' ) {
			return false;
		}
	}
	return true;
}

// Functions for the Product Edit Page

function wpsc_addl_desc_product_edit($empty=true,$product_desc_fields='') {

	$wpsc_site_options = get_option('wpsc_site_options');
	$descriptions = $wpsc_site_options['presentation'][WPSC_ADDL_DESC_OPTION];
	if( !wpsc_addl_desc_is_empty($descriptions) ) {
		array_unshift($descriptions, 'addl_desc');
	} else {
		$descriptions = array('addl_desc');
	}

	$the_div_list = '';
	$output = '<div id="tabs" class="addl_desc_admin_tabs">';
	$output .= '<ul>';
	foreach($descriptions as $tab_name) {
		$the_div = '';
		$tab_index = $tab_name;
		$tab_id = preg_replace('/ /','_',$tab_name);
		if( $tab_name == 'addl_desc') {
			$tab_id = $tab_name;
			$tab_name = __('Additional Description','wpsc');
		}
		$output .= '<li><a href="#'.$tab_id.'"><span>'.$tab_name.'</span></a></li>';
		
		$the_div .= '<div id="'.$tab_id.'">';
		$the_div .= '<textarea name="additional_description[]" cols="40" rows="8">';
		if( !$empty && isset($product_desc_fields[$tab_index]) ) {
			$the_div .= stripslashes($product_desc_fields[$tab_index]);
		}
		$the_div .= '</textarea>';
		$the_div .= '</div>';

		$the_div_list .= $the_div;
	}
	
	$output .= '</ul>';
	$output .= $the_div_list;
	$output .= '</div> <!--END wpsc_addl_desc_tab -->';

	return $output;
}

function wpsc_addl_desc_product_form_submit() {
	global $wpdb;
	$default_tab = WPSC_ADDL_DESC_OPTION;
	
	if( is_array($_POST['additional_description']) ) {
		$wpsc_site_options = get_option('wpsc_site_options');
		$descriptions = $wpsc_site_options['presentation'][$default_tab];
		array_unshift($descriptions, $default_tab);
		$addl_descriptions = array();
		$input = $_POST['additional_description'];
		$i=0;
		for($i;$i<count($_POST['additional_description']);$i++) {
			$addl_descriptions[trim($descriptions[$i])] = stripslashes($input[$i]);
		}
	} else {
		$addl_descriptions = array( WPSC_ADDL_DESC_OPTION => stripslashes($_POST['additional_description']));
	}

	return $wpdb->escape( maybe_serialize($addl_descriptions) );
}

// Display functions for catalog
function wpsc_addl_desc_show($addl_descriptions) {

	if( is_array($addl_descriptions) && isset($addl_descriptions['addl_desc']) ) return wpsc_addl_desc_make_tabs($addl_descriptions);
	if( is_array($addl_descriptions) ) return $addl_descriptions[0];
	return $addl_descriptions;
}
function wpsc_addl_desc_make_tabs($addl_descriptions) {

	$wpsc_site_options = get_option('wpsc_site_options');
	$descriptions = $wpsc_site_options['presentation'][WPSC_ADDL_DESC_OPTION];
	if( !wpsc_addl_desc_is_empty($descriptions) ) {
		array_unshift($descriptions, 'addl_desc');
	} else {
		$descriptions = array('addl_desc');
	}

	$the_div_list = '';
	$output = '<div id="tabs" class=".ui-helper-reset ui-tabs ui-widget ui-widget-content ui-corner-all">';
	$output .= '<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">';
	foreach($descriptions as $tab_name) {
		$the_div = '';
		$tab_index = $tab_name;
		$tab_id = preg_replace('/ /','_',$tab_name);
		if( $tab_name == 'addl_desc') {
			$tab_id = $tab_name;
			$tab_name = __('Additional Description','wpsc');
		}
		$output .= '<li class="ui-state-default ui-corner-top"><a href="#'.$tab_id.'"><span>'.$tab_name.'</span></a></li>';
		
		$the_div .= '<div class="ui-tabs-panel ui-widget-content ui-corner-bottom" id="'.$tab_id.'">';
		if( !$empty && isset($addl_descriptions[$tab_index]) ) {
			$the_div .= wpautop( stripslashes($addl_descriptions[$tab_index]) );
		}
		$the_div .= '</div>';

		$the_div_list .= $the_div;
	}
	
	$output .= '</ul>';
	$output .= $the_div_list;
	$output .= '</div> <!--END wpsc_addl_desc_tab -->';

	add_action('wp_footer','wpsc_addl_desc_footer_js');

	return $output;	
}

// Setup functions

function wpsc_addl_desc_init() {

	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-tabs');	
}
add_action('load-'.WPSC_DIR_NAME.'/display-items.php','wpsc_addl_desc_init');

function wpsc_addl_desc_header_style() {
	$myTabsStyleSheet = WPSC_URL . '/css/addl_desc.admin.tabs.css';
//	if( file_exists($myTabsStyleSheet) ) {
		wp_register_style('wpsc_addl_desc_style_tabs', $myTabsStyleSheet);
		wp_enqueue_style('wpsc_addl_desc_style_tabs');
//	}
}
add_action('admin_print_styles-'.WPSC_DIR_NAME.'/display-items.php','wpsc_addl_desc_header_style');

function wpsc_addl_desc_footer() {

	add_action('admin_footer','wpsc_addl_desc_footer_js',5);
}
add_action('load-'.WPSC_DIR_NAME.'/display-items.php','wpsc_addl_desc_footer');

function wpsc_addl_desc_footer_js() {
?>
<script>
	jQuery(document).ready(function() {
		jQuery("#tabs > ul").tabs();
	});
</script>
<?php
}