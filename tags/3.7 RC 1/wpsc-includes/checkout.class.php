<?php
/**
 * WP eCommerce checkout class
 *
 * These are the class for the WP eCommerce checkout
 * The checkout class handles dispaying the checkout form fields
 *
 * @package wp-e-commerce
 * @subpackage wpsc-checkout-classes 
*/
function wpsc_google_checkout_submit(){
	global $wpdb,  $wpsc_cart, $current_user;
	$wpsc_checkout = new wpsc_checkout();
	$purchase_log_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid` IN('".$_SESSION['wpsc_sessionid']."') LIMIT 1") ;
	//$purchase_log_id = 1;
	get_currentuserinfo();
	//	exit('<pre>'.print_r($current_user, true).'</pre>');
	if($current_user->display_name != ''){
		foreach($wpsc_checkout->checkout_items as $checkoutfield){
		//	exit(print_r($checkoutfield,true));
			if($checkoutfield->unique_name == 'billingfirstname'){
				$checkoutfield->value = $current_user->display_name;
			}
		}	
	}
	if($current_user->user_email != ''){
		foreach($wpsc_checkout->checkout_items as $checkoutfield){
		//	exit(print_r($checkoutfield,true));
			if($checkoutfield->unique_name == 'billingemail'){
				$checkoutfield->value = $current_user->user_email;
			}
		}	
	}

	$wpsc_checkout->save_forms_to_db($purchase_log_id);
	$wpsc_cart->save_to_db($purchase_log_id);
	$wpsc_cart->submit_stock_claims($purchase_log_id);

}
function wpsc_have_checkout_items() {
	global $wpsc_checkout;
	return $wpsc_checkout->have_checkout_items();
}

function wpsc_the_checkout_item() {
	global $wpsc_checkout;
	return $wpsc_checkout->the_checkout_item();
}

function wpsc_the_checkout_item_error_class($as_attribute = true) {
	global $wpsc_checkout;
	if($_SESSION['wpsc_checkout_error_messages'][$wpsc_checkout->checkout_item->id] != '') {
	  $class_name = 'validation-error';
	}
	if(($as_attribute == true)){
	 $output = "class='$class_name'";
	} else {
		$output = $class_name;
	}
	return $output;
}

function wpsc_the_checkout_item_error() {
	global $wpsc_checkout;
	$output = false;
	if($_SESSION['wpsc_checkout_error_messages'][$wpsc_checkout->checkout_item->id] != '') {
	  $output = $_SESSION['wpsc_checkout_error_messages'][$wpsc_checkout->checkout_item->id];
	}
	
	return $output;
}


function wpsc_checkout_form_is_header() {
	global $wpsc_checkout;
	if($wpsc_checkout->checkout_item->type == 'heading') {
	  $output = true;
	} else {
	  $output = false;
	}
	return $output;
}


function wpsc_checkout_form_name() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_name();
}
function wpsc_checkout_form_element_id() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_element_id();
}

function wpsc_checkout_form_field() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_field();
}


function wpsc_shipping_region_list($selected_country, $selected_region){
global $wpdb;

	if ($selected_country == 'US') {
		$region_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_REGION_TAX."` WHERE country_id='136'",ARRAY_A);
		$output .= "<select name='region'  id='region' onchange='submit_change_country();' >";
		foreach ($region_data as $region) {
			$selected ='';
			if($selected_region == $region['id']) {
				$selected = "selected='selected'";
			}
			$output .= "<option $selected value='{$region['id']}'>".htmlspecialchars($region['name'])."</option>";
		}
		$output .= "";
		
		$output .= "</select>";
	} else {
		$output .= " ";
	}
	return $output;
}

function wpsc_shipping_country_list() {
	global $wpdb, $wpsc_shipping_modules;
	$output = "<input type='hidden' name='wpsc_ajax_actions' value='update_location' />";
	$selected_country = $_SESSION['wpsc_delivery_country'];
	$selected_region = $_SESSION['wpsc_delivery_region'];
	if($selected_country == null) {
		$selected_country = get_option('base_country');
	}
	if($selected_region == null) {
		$selected_region = get_option('base_region');
	}
	$country_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
	$output .= "<select name='country' id='current_country' onchange='submit_change_country();' >";
	foreach ($country_data as $country) {
	// 23-02-09 fix for custom target market by jeffry
	// recon this should be taken out and put into a function somewhere maybe,,,
	 if($country['visible'] == '1'){
		$selected ='';
		if($selected_country == $country['isocode']) {
			$selected = "selected='selected'";
		}
		$output .= "<option value='".$country['isocode']."' $selected>".htmlspecialchars($country['country'])."</option>";
	 }
	}

	$output .= "</select>";
	
	$output .= wpsc_shipping_region_list($selected_country, $selected_region);
	
// 	$output .= "ZipCode:";
if(isset($_POST['zipcode'])) {
		if ($_POST['zipcode']=='') {
			$zipvalue = 'Your Zipcode';
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
			$color = '#999';
		} else {
			$zipvalue = $_POST['zipcode'];
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
			$color = '#000';
		}
	} else if(isset($_SESSION['wpsc_zipcode']) && ($_SESSION['wpsc_zipcode'] != '')) {
			$zipvalue = $_SESSION['wpsc_zipcode'];
			$color = '#000';
	} else {
		$zipvalue = 'Your Zipcode';
		$_SESSION['wpsc_zipcode'] = '';
		$color = '#999';
	}
	
		$uses_zipcode = false;
		$custom_shipping = get_option('custom_shipping_options');
		foreach((array)$custom_shipping as $shipping) {
		  if($wpsc_shipping_modules[$shipping]->needs_zipcode == true) {
		    $uses_zipcode = true;
		  }
		}
	
	if($uses_zipcode == true) {
		$output .= " <input type='text' style='color:".$color.";' onclick='if (this.value==\"Your Zipcode\") {this.value=\"\";this.style.color=\"#000\";}' onblur='if (this.value==\"\") {this.style.color=\"#999\"; this.value=\"Your Zipcode\"; }' value='".$zipvalue."' size='10' name='zipcode' id='zipcode'>";
	}
	return $output;
}









/**
 * The WPSC Checkout class
 */
class wpsc_checkout {
	// The checkout loop variables
	var $checkout_items = array();
	var $checkout_item;
	var $checkout_item_count = 0;
	var $current_checkout_item = -1;
	var $in_the_loop = false;
   
	/**
	* wpsc_checkout method, gets the tax rate as a percentage, based on the selected country and region
	* @access public
	*/
  function wpsc_checkout() {
    global $wpdb;
    $this->checkout_items = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' ORDER BY `order`;");
    $this->checkout_item_count = count($this->checkout_items);
  }
  
  function form_name() {
		if($this->form_name_is_required() && ($this->checkout_item->type != 'heading')){
			return $this->checkout_item->name.' * ';
		}else{
			return $this->checkout_item->name;
		}
  }  
   
	function form_name_is_required(){
		if($this->checkout_item->mandatory == 0){
			return false;
		}else{
			return true;
		}
	}
	/**
	* form_element_id method, returns the form html ID
	* @access public
	*/
  function form_element_id() {
		return 'wpsc_checkout_form_'.$this->checkout_item->id;
	}  
	
	/**
	* form_field method, returns the form html
	* @access public
	*/
  function form_field() {
		global $wpdb;
		switch($this->checkout_item->type) {
			case "address":
			case "delivery_address":
			case "textarea":
			$output = "<textarea class='text' id='".$this->form_element_id()."' name='collected_data[{$this->checkout_item->id}]' rows='3' cols='40' >".$_SESSION['wpsc_checkout_saved_values'][$this->checkout_item->id]."</textarea>";
			break;
			
			case "country":
			$output = wpsc_country_region_list($this->checkout_item->id , false, $_SESSION['wpsc_selected_country'], $_SESSION['wpsc_selected_region'], $this->form_element_id());
			break;

			case "delivery_country":
			$country_name = $wpdb->get_var("SELECT `country` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode`='".$_SESSION['wpsc_delivery_country']."' LIMIT 1");
			$output = "<input type='hidden' id='".$this->form_element_id()."' class='shipping_country' name='collected_data[{$this->checkout_item->id}]' value='".$_SESSION['wpsc_delivery_country']."' size='4' /><span class='shipping_country_name'>".$country_name."</span> ";
			break;
			
			case "text":
			case "city":
			case "delivery_city":
			case "email":
			case "coupon":
			default:
			$output = "<input type='text' id='".$this->form_element_id()."' class='text' value='".$_SESSION['wpsc_checkout_saved_values'][$this->checkout_item->id]."' name='collected_data[{$this->checkout_item->id}]' />";
			break;
		}
		return $output;
	}
  
	/**
	* validate_forms method, validates the input from the checkout page
	* @access public
	*/
  function validate_forms() {
   global $wpdb;
   $any_bad_inputs = false;
  		foreach($this->checkout_items as $form_data) {
			$value = $_POST['collected_data'][$form_data->id];
		  	$value_id = (int)$value_id;
			$_SESSION['wpsc_checkout_saved_values'][$form_data->id] = $value;
			$bad_input = false;
			if(($form_data->mandatory == 1) || ($form_data->type == "coupon")) {
				switch($form_data->type) {
					case "email":
					if(!preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-.]+\.[a-zA-Z]{2,5}$/",$value)) {
						$any_bad_inputs = true;
						$bad_input = true;
					}
					break;

					case "delivery_country":
					case "country":
					case "heading":
					break;
					
					default:
					if($value == null) {
						$any_bad_inputs = true;
						$bad_input = true;
					}
					break;
				}
				if($bad_input === true) {
					$_SESSION['wpsc_checkout_error_messages'][$form_data->id] = TXT_WPSC_PLEASEENTERAVALID . " " . strtolower($form_data->name) . ".";
					$_SESSION['wpsc_checkout_saved_values'][$form_data->id] = '';
				}
			}
		}
		return array('is_valid' => !$any_bad_inputs, 'error_messages' => $bad_input_message);
  }
  
	/**
	* validate_forms method, validates the input from the checkout page
	* @access public
	*/
  function save_forms_to_db($purchase_id) {
   global $wpdb;
   
		foreach($this->checkout_items as $form_data) {
		
		  $value = $_POST['collected_data'][$form_data->id];
		  if($value == ''){
		  	$value = $form_data->value;
		  }	
		 // echo '<pre>'.print_r($form_data,true).'</pre>';
		  if(is_array($value)){
		  	$value = $value[0];
		  }	  
		  if($form_data->type != 'heading') {
				//echo "INSERT INTO `".WPSC_TABLE_SUBMITED_FORM_DATA."` ( `log_id` , `form_id` , `value` ) VALUES ( '{$purchase_id}', '".(int)$form_data->id."', '".$value."');<br />";
				
				$prepared_query = $wpdb->query($wpdb->prepare("INSERT INTO `".WPSC_TABLE_SUBMITED_FORM_DATA."` ( `log_id` , `form_id` , `value` ) VALUES ( %d, %d, %s)", $purchase_id, $form_data->id, $value));
				
 			}
		}
  }
  
  /**
	 * checkout loop methods
	*/ 
  
  function next_checkout_item() {
		$this->current_checkout_item++;
		$this->checkout_item = $this->checkout_items[$this->current_checkout_item];
		return $this->checkout_item;
	}

  
  function the_checkout_item() {
		$this->in_the_loop = true;
		$this->checkout_item = $this->next_checkout_item();
		if ( $this->current_checkout_item == 0 ) // loop has just started
			do_action('wpsc_checkout_loop_start');
	}

	function have_checkout_items() {
		if ($this->current_checkout_item + 1 < $this->checkout_item_count) {
			return true;
		} else if ($this->current_checkout_item + 1 == $this->checkout_item_count && $this->checkout_item_count > 0) {
			do_action('wpsc_checkout_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_checkout_items();
		}

		$this->in_the_loop = false;
		return false;
	}

	function rewind_checkout_items() {
	  $_SESSION['wpsc_checkout_error_messages'] = array();
		$this->current_checkout_item = -1;
		if ($this->checkout_item_count > 0) {
			$this->checkout_item = $this->checkout_items[0];
		}
	}    
  
}


/**
 * The WPSC Gateway functions
 */


function wpsc_gateway_count() {
	global $wpsc_gateway;
	return $wpsc_gateway->gateway_count;
}

function wpsc_have_gateways() {
	global $wpsc_gateway;
	return $wpsc_gateway->have_gateways();
}

function wpsc_the_gateway() {
	global $wpsc_gateway;
	return $wpsc_gateway->the_gateway();
}

function wpsc_gateway_name() {
	global $wpsc_gateway;
	$payment_gateway_names = get_option('payment_gateway_names');
	if($payment_gateway_names[$wpsc_gateway->gateway['internalname']] != '') {
		$display_name = $payment_gateway_names[$wpsc_gateway->gateway['internalname']];					    
	} else {
		switch($selected_gateway_data['payment_type']) {
			case "paypal";
				$display_name = "PayPal";
			break;
			
			case "manual_payment":
				$display_name = "Manual Payment";
			break;
			
			case "google_checkout":
				$display_name = "Google Checkout";
			break;
			
			case "credit_card":
			default:
				$display_name = "Credit Card";
			break;
		}
	}
	return $display_name;
}

function wpsc_gateway_internal_name() {
	global $wpsc_gateway;
	return $wpsc_gateway->gateway['internalname'];
}

function wpsc_gateway_is_checked() {
	global $wpsc_gateway;
	$is_checked = false;
	if(isset($_SESSION['wpsc_previous_selected_gateway'])) {
	  if($wpsc_gateway->gateway['internalname'] == $_SESSION['wpsc_previous_selected_gateway']) {
	    $is_checked = true;	  
	  }
	} else {
	  if($wpsc_gateway->current_gateway == 0) {
	    $is_checked = true;
	  }
	}
	if($is_checked == true) {
	  $output = 'checked="checked"';
	} else {
		$output = '';
	}
	return $output;
}

function wpsc_gateway_form_fields() {
	global $wpsc_gateway, $gateway_checkout_form_fields;
	return $gateway_checkout_form_fields[$wpsc_gateway->gateway['internalname']];

}

function wpsc_gateway_form_field_style() {
 return "checkout_forms_hidden";
}

/**
 * The WPSC Gateway class
 */

class wpsc_gateways {
  var $wpsc_gateways;
	var $gateway;
	var $gateway_count = 0;
	var $current_gateway = -1;
	var $in_the_loop = false;
  
  function wpsc_gateways() {
		global $nzshpcrt_gateways;
		
		$gateway_options = get_option('custom_gateway_options');
		foreach($nzshpcrt_gateways as $gateway) {
			if(array_search($gateway['internalname'], (array)$gateway_options) !== false) {
				$this->wpsc_gateways[] = $gateway;
			}		
		}
		$this->gateway_count = count($this->wpsc_gateways);
  }

  /**
	 * checkout loop methods
	*/ 
  
  function next_gateway() {
		$this->current_gateway++;
		$this->gateway = $this->wpsc_gateways[$this->current_gateway];
		return $this->gateway;
	}

  
  function the_gateway() {
		$this->in_the_loop = true;
		$this->gateway = $this->next_gateway();
		if ( $this->current_gateway == 0 ) // loop has just started
			do_action('wpsc_checkout_loop_start');
	}

	function have_gateways() {
		if ($this->current_gateway + 1 < $this->gateway_count) {
			return true;
		} else if ($this->current_gateway + 1 == $this->gateway_count && $this->gateway_count > 0) {
			do_action('wpsc_checkout_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_gateways();
		}

		$this->in_the_loop = false;
		return false;
	}

	function rewind_gateways() {
		$this->current_gateway = -1;
		if ($this->gateway_count > 0) {
			$this->gateway = $this->wpsc_gateways[0];
		}
	}    

}


?>