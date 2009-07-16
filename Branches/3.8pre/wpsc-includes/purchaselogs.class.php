<?php

if(isset($_REQUEST['purchaselog_id'])){
$purchlogitem = new wpsc_purchaselogs_items((int)$_REQUEST['purchaselog_id']);
}
function wpsc_purchlogs_has_customfields($id = ''){
	global $purchlogitem;
	//return true;
	if($id == ''){
		foreach($purchlogitem->allcartcontent as $cartitem){
			if($cartitem->files != 'N;' || $cartitem->custom_message != ''){
				return true;
			}
		}
		return false;
	}else{
		$purchlogitem = new wpsc_purchaselogs_items($id);
		foreach($purchlogitem->allcartcontent as $cartitem){
			if($cartitem->files != 'N;' || $cartitem->custom_message != ''){
				return true;
			}
		}
		return false;
	}
	return false;

}
function wpsc_purchlogs_custommessages(){
	global $purchlogitem;
	foreach($purchlogitem->allcartcontent as $cartitem){
		if($cartitem->custom_message != ''){
			//exit('<pre>'.print_r($cartitem,true).'</pre>');
			$messages[] = $cartitem->name.' :<br />'.$cartitem->custom_message;
			//return true;
		}
	}
	return $messages;
}
function wpsc_purchlogs_customfiles(){
	global $purchlogitem;
	foreach($purchlogitem->allcartcontent as $cartitem){
		if($cartitem->files != 'N;'){
			$file = unserialize($cartitem->files);
			//exit('<pre>'.var_dump($file,true).'</pre>');
			if($file["mime_type"] == "image/jpeg" ||$file["mime_type"] == "image/png"||$file["mime_type"] == "image/gif"){
				$image  = "<a href='".WPSC_USER_UPLOADS_URL.$file['file_name']."' >";
				$image .= "<img src='index.php?image_name=".$file['file_name']."' alt='' />";
				$image .="</a>";
				$files[] = $cartitem->name.' :<br />'.$image;
			}else{
				$files[] = $cartitem->name.' :<br />'.$file['file_name'];
			}
			
			//return true;
		}
	}
	return $files;
}
function wpsc_purchlogs_is_google_checkout(){
	global $purchlogs;
	if($purchlogs->purchitem->gateway == 'google'){
		return true;
	}else{
		return false;
	}
}
function wpsc_the_purch_total(){
	global $purchlogs;
	return $purchlogs->totalAmount;
}
function wpsc_have_purch_items(){
	global $purchlogs;
	return $purchlogs->have_purch_items();
}
function wpsc_the_purch_item(){
	global $purchlogs;
	return $purchlogs->the_purch_item();
}

function wpsc_the_purch_item_price(){
	global $purchlogs;
	//	exit('<pre>'.print_r($purchlogs->purchitem, true).'</pre>');
	if($purchlogs->purchitem->processed > 1 && $purchlogs->purchitem->processed != 5){
	$purchlogs->totalAmount += $purchlogs->purchitem->totalprice;
	}
	return $purchlogs->purchitem->totalprice;
}
function wpsc_the_purch_item_id(){
	global $purchlogs;
	//exit('<pre>'.print_r($purchlogs->purchitem,true).'</pre>');
	return $purchlogs->purchitem->id;
}
function wpsc_the_purch_item_date(){
	global $purchlogs;
	return date('M d Y',$purchlogs->purchitem->date);
}
function wpsc_the_purch_item_name(){
	global $purchlogs;
	//exit('<pre>'.print_r($purchlogs, true).'</pre>');
	if(wpsc_purchlogs_has_customfields(wpsc_the_purch_item_id())){
		return $purchlogs->the_purch_item_name().'<img src="'.WPSC_URL.'/images/info_icon.jpg" title="This Purchase has custom user content" alt="exclamation icon" />';
	}else{
		return $purchlogs->the_purch_item_name();
	}
}
function wpsc_the_purch_item_details(){
	global $purchlogs;
	return $purchlogs->the_purch_item_details();
}

//status loop functions
function wpsc_have_purch_items_statuses(){
	global $purchlogs;
	return $purchlogs->have_purch_status();
}
function wpsc_the_purch_status(){
	global $purchlogs;

	return $purchlogs->the_purch_status();
}
function wpsc_the_purch_item_statuses(){
	global $purchlogs;
	return $purchlogs->the_purch_item_statuses();
}
function wpsc_the_purch_item_status(){
	global $purchlogs;
	return $purchlogs->the_purch_item_status();
}
function wpsc_the_purch_status_id(){
	global $purchlogs;
//	exit(print_r($purchlogs->purchstatus, true));
	return $purchlogs->purchstatus->id;
}
function wpsc_is_checked_status(){
	global $purchlogs;
	
	return $purchlogs->is_checked_status();
}
function wpsc_the_purch_status_name(){
	global $purchlogs;
	//exit(print_r($purchlogs->purchstatus, true));
	return $purchlogs->purchstatus->name;
}
function wpsc_purchlogs_getfirstdates(){
	global $purchlogs;
	$dates = $purchlogs->getdates();
	foreach($dates as $date){
		$cleanDate = date('M Y', $date['start']);
		$fDate .= '<option value="'.$date['start'].'_'.$date['end'].'">'.$cleanDate.'</option>';
	}
//	exit($i);
	return $fDate;
}
function wpsc_change_purchlog_view($viewby, $status){
	global $purchlogs;
//	exit('<pre>'.print_r($status,true).'</pre>');
	if($viewby == 'all'){
		$dates = $purchlogs->getdates();
		$purchaselogs = $purchlogs->get_purchlogs($dates, $status);
		$_SESSION['newlogs'] = $purchaselogs;
		$purchlogs->allpurchaselogs = $purchaselogs;
	}elseif($viewby == '3mnths'){
		$dates = $purchlogs->getdates();

		$dates = array_slice($dates, 0, 3);
		$purchlogs->current_start_timestamp = $dates[2]['start'];
		$purchlogs->current_end_timestamp = $dates[0]['end'];
	//	exit('<pre>'.print_r($dates,true).'</pre>');		
		$newlogs = $purchlogs->get_purchlogs($dates, $status);
		$_SESSION['newlogs'] = $newlogs;
		//exit('<pre>'.print_r($newlogs, true).'</pre>');
		$purchlogs->allpurchaselogs = $newlogs;
		//exit(print_r($date, true)."".$purchlogs->current_timestamp);
	
	}else{
		
		$dates = explode('_', $viewby);
		$date[0]['start'] = $dates[0];
		$date[0]['end'] = $dates[1];
		$purchlogs->current_start_timestamp = $dates[0];
		$purchlogs->current_end_timestamp =  $dates[1];
		$newlogs = $purchlogs->get_purchlogs($date, $status);
		//exit('<pre>'.print_r($newlogs, true).'</pre>');
		$_SESSION['newlogs'] = $newlogs;
		$purchlogs->allpurchaselogs = $newlogs;
	}

	//exit('View by <pre>'.print_r($purchlogs,true).'</pre>');
}
function wpsc_search_purchlog_view($search){
	global $purchlogs;
	$newlogs = $purchlogs->search_purchlog_view($search);
	$purchlogs->getDates();
	$purchlogs->purch_item_count = count($newlogs);
	$purchlogs->allpurchaselogs = $newlogs;
	
}

function wpsc_have_purchaselog_details(){
	global $purchlogitem;
	//exit('HERe<pre>'.print_r($purchlogitem->allcartcontent,true).'</pre>');
	return $purchlogitem->have_purch_item();
	
	
}

function wpsc_purchaselog_details_name(){
	global $purchlogitem;
	return $purchlogitem->purchitem->name;
}
function wpsc_the_purchaselog_item(){
	global $purchlogitem;
	return $purchlogitem->the_purch_item();
}
function wpsc_purchaselog_details_SKU(){
	global $purchlogitem;
	if($purchlogitem->purchitem->meta_value ==''){
		return 'N/A';
	}else{
		return $purchlogitem->purchitem->meta_value;
	}

}
function wpsc_purchaselog_details_quantity(){
	global $purchlogitem;
//	exit('<pre>'.print_r($purchlogitem->purchitem, true).'</pre>');
	return $purchlogitem->purchitem->quantity;
}
function wpsc_purchaselog_details_price(){
	global $purchlogitem;
//	exit('<pre>'.print_r($purchlogitem->purchitem, true).'</pre>');
	return $purchlogitem->purchitem->price;
}
function wpsc_purchaselog_details_tax(){
	global $purchlogitem;
//	exit('<pre>'.print_r($purchlogitem->purchitem, true).'</pre>');
	return $purchlogitem->purchitem->tax_charged;
}
function wpsc_purchaselog_details_discount(){
	global $purchlogitem;
//	exit('<pre>'.print_r($purchlogitem->extrainfo, true).'</pre>');
	return $purchlogitem->extrainfo->discount_value;
}
function wpsc_purchaselog_details_date(){
	global $purchlogitem;
//	exit('<pre>'.print_r($purchlogitem->extrainfo, true).'</pre>');
	return date('jS M Y',$purchlogitem->extrainfo->date);
	
}
function wpsc_purchaselog_details_total(){
	global $purchlogitem;
	$total = 0;
  $total += ($purchlogitem->purchitem->price*$purchlogitem->purchitem->quantity);
  $total += ($purchlogitem->purchitem->tax_charged*$purchlogitem->purchitem->quantity);
  $total += $purchlogitem->extrainfo->discount_value;
	$purchlogitem->totalAmount += $total;
	return $total;
}
function wpsc_purchaselog_details_purchnumber(){
	global $purchlogitem;
		//exit('<pre>'.print_r($purchlogitem->extrainfo, true).'</pre>');
	return $purchlogitem->extrainfo->id;
}
/*
 *Returns base shipping should make a function to calculate items shipping as well
 */
function wpsc_display_purchlog_shipping(){
	global $purchlogitem;
//	exit('<pre>'.print_r($purchlogitem, true).'</pre>');
	return nzshpcrt_currency_display($purchlogitem->extrainfo->base_shipping, true);
}
function wpsc_display_purchlog_totalprice(){
	global $purchlogitem;
	$purchlogitem->totalAmount += $purchlogitem->extrainfo->base_shipping;
	return nzshpcrt_currency_display($purchlogitem->totalAmount, true);
}
function wpsc_display_purchlog_buyers_name(){
	global $purchlogitem;
	return $purchlogitem->userinfo['billingfirstname']['value'].' '.$purchlogitem->userinfo['billinglastname']['value'];
}
function wpsc_display_purchlog_buyers_email(){
	global $purchlogitem;
	//exit('<pre>'.print_r($purchlogitem->userinfo,true).'</pre>');
	return $purchlogitem->userinfo['billingemail']['value'];
}
function wpsc_display_purchlog_buyers_phone(){
	global $purchlogitem;
	//exit('<pre>'.print_r($purchlogitem->userinfo,true).'</pre>');
	return $purchlogitem->userinfo['billingphone']['value'];
}
function wpsc_display_purchlog_shipping_name(){
	global $purchlogitem;

	return $purchlogitem->shippinginfo['shippingfirstname']['value'].' '.$purchlogitem->shippinginfo['shippinglastname']['value'];
}
function wpsc_display_purchlog_shipping_address(){
	global $purchlogitem;
//	exit('<pre>'.print_r($purchlogitem->shippinginfo,true).'</pre>');
	return $purchlogitem->shippinginfo['shippingaddress']['value'];
}
function wpsc_display_purchlog_shipping_city(){
	global $purchlogitem;
//	exit('<pre>'.print_r($purchlogitem->shippinginfo,true).'</pre>');
	return $purchlogitem->shippinginfo['shippingcity']['value'];
}
function wpsc_display_purchlog_shipping_state_and_postcode(){
	global $purchlogitem;
	//exit('<pre>'.print_r($purchlogitem->shippinginfo,true).'</pre>');
	return $purchlogitem->shippinginfo['shippingstate']['value'].', '.$purchlogitem->shippinginfo['shippingpostcode']['value'];
}
function wpsc_display_purchlog_shipping_country(){
	global $purchlogitem;
	return $purchlogitem->shippinginfo['shippingcountry']['value'];
}
function wpsc_display_purchlog_shipping_method(){
	global $purchlogitem;
//	exit('<pre>'.print_r($purchlogitem->extrainfo, true).'</pre>');
	return $purchlogitem->extrainfo->shipping_method;
}
function wpsc_display_purchlog_shipping_option(){
	global $purchlogitem;
//	exit('<pre>'.print_r($purchlogitem->extrainfo, true).'</pre>');
	return $purchlogitem->extrainfo->shipping_option;
}	
function wpsc_display_purchlog_paymentmethod(){
	global $purchlogitem;
//	exit('<pre>'.print_r($purchlogitem->extrainfo, true).'</pre>');
	if($purchlogitem->extrainfo->gateway =='testmode'){
		return 'Manual Payment';
	}else{
		return $purchlogitem->extrainfo->gateway;
	}
}
function wpsc_has_purchlog_shipping(){
	global $purchlogitem;
	//exit('<pre>'.print_r($purchlogitem, true).'</pre>');
	if($purchlogitem->shippinginfo['shippingfirstname']['value'] != ''){
	 return true;
	}else{
	 return false;
	}

}
function wpsc_purchlog_is_checked_status(){
	global $purchlogitem, $purchlogs;
	//exit('<pre>'.print_r($purchlogs,true).'</pre>');
	if($purchlogs->purchstatus->id == $purchlogitem->extrainfo->processed){
			return 'selected="selected"';
		}else{
			return '';
		}
//	exit('<pre>'.print_r($purchlogitem,true).'</pre>');
}

function wpsc_purchlogs_have_downloads_locked(){
	global $purchlogitem;
	$ip = $purchlogitem->have_downloads_locked();
	if($ip != ''){
		return sprintf(TXT_WPSC_CLEAR_IP_LOCKS, $ip);
	}else{
		return false;
	}
	 
}
/**
 * WP eCommerce purchaselogs AND purchaselogs_items class
 *
 * These is the classes for the WP eCommerce purchase logs,
 * The purchaselogs class handles adding, removing and adjusting details in the purchaselogs,
 *The purchaselogs_items class handles adding, removing and adjusting individual item details in the purchaselogs,
 *
 * @package wp-e-commerce
 * @since 3.7
 * @subpackage wpsc-cart-classes 
*/

class wpsc_purchaselogs{
	
	var $earliest_timestamp;
	var $current_timestamp;
	var $earliest_year;
	var $current_year;
	
	var $form_data;
	
	var $purch_item_count;
	//individual purch log variables
	var $allpurchaselogs;
	var $currentitem = -1;
	var $purchitem;
	
	//used for purchase options
	var $currentstatus = -1;
	var $purch_status_count;
	var $allpurchaselogstatuses;
	
	//calculation of totals
	var $totalAmount;
	
	//used for csv 
	var $current_start_timestamp;
	var $current_end_timestamp;
	
	/* Constructor function*/
	function wpsc_purchaselogs(){

		$this->getall_formdata();
		if(!isset($_POST['view_purchlogs_by']) || !isset($_POST['purchlogs_searchbox'])){
			$dates = $this->getdates();
			if(isset($_SESSION['newlogs'])){
				$purchaselogs = $_SESSION['newlogs'];
				unset($_SESSION['newlogs']);
			}else{
				$firstDates[] = $dates[0]; 
				$purchaselogs = $this->get_purchlogs($firstDates);
			}
		
			$this->allpurchaselogs = $purchaselogs;
		}else{
			$this->getdates();
		}
		$this->purch_item_count = count($this->allpurchaselogs);
		$statuses = $this->the_purch_item_statuses();

	}
	
	function get_purchlogs($dates, $status=''){
		global $wpdb;
		$purchlog2 = array();
		if($status=='' || $status=='-1'){
		   foreach((array)$dates as $date_pair){
		        if(($date_pair['end'] >= $this->earliest_timestamp) && ($date_pair['start'] <= $this->current_timestamp)) {   
		          $sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '".$date_pair['start']."' AND '".$date_pair['end']."' ORDER BY `date` DESC";
		          $purchase_logs = $wpdb->get_results($sql) ;
				  array_push($purchlog2, $purchase_logs);
				}
			}
		}else{
		   foreach((array)$dates as $date_pair){
			        if(($date_pair['end'] >= $this->earliest_timestamp) && ($date_pair['start'] <= $this->current_timestamp)) {   
			          $sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '".$date_pair['start']."' AND '".$date_pair['end']."' AND `processed`='".$status."' ORDER BY `date` DESC";
			        // exit($sql);
			          $purchase_logs = $wpdb->get_results($sql) ;
			          $purchlog2 = $purchase_logs;
	          	  	  $this->allpurchaselogs = $purchlog2;
	 				  $this->purch_item_count = count($this->allpurchaselogs);
					  return $purchlog2;
			        //  array_push($purchlog2, $purchase_logs);

					}
				}

	  	}
	  	
	  	foreach($purchlog2 as $purch){
	  		if(is_array($purch)){
		  		foreach($purch as $log){
		  			$newarray[] = $log;
		  		}
	  		}else{
	  			exit('Else :'.print_r($purch));
	  		}	  		
	  	}
	//  	exit('<pre>'.print_r($newarray,true).'<pre>');
	   	$this->allpurchaselogs = $newarray;
	   	$this->purch_item_count = count($this->allpurchaselogs);
	  return $newarray;
	}
	
	function  getall_formdata(){
		global $wpdb;
		$form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' AND `display_log` = '1';";
    	$form_data = $wpdb->get_results($form_sql,ARRAY_A);
    	$this->form_data = $form_data;
    	return $form_data;
    }

	/*
	 * This finds the earliest time in the shopping cart and sorts out the timestamp system for the month by month display
	 * or if there was a filter applied use the filter to sort the dates.
	 */  
	function getdates(){
		global $wpdb;
		$earliest_record_sql = "SELECT MIN(`date`) AS `date` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date`!=''";
		$earliest_record = $wpdb->get_results($earliest_record_sql,ARRAY_A) ;
		
		$this->current_timestamp = time();
		$this->earliest_timestamp = $earliest_record[0]['date'];
		
		$this->current_year = date("Y");
		$this->earliest_year = date("Y",$this->earliest_timestamp);
		
		$j = 0;
		for($year = $this->current_year; $year >= $this->earliest_year; $year--) {
		  for($month = 12; $month >= 1; $month--) {          
		    $this->start_timestamp = mktime(0, 0, 0, $month, 1, $year);
		    $this->end_timestamp = mktime(0, 0, 0, ($month+1), 1, $year);
		    if(($this->end_timestamp >= $this->earliest_timestamp) && ($this->start_timestamp <= $this->current_timestamp)) {
		      $date_list[$j]['start'] = $this->start_timestamp;
		      $date_list[$j]['end'] = $this->end_timestamp;
		      $j++;
				}
			}
		}
		$purchlogs->current_start_timestamp = $purchlogs->earliest_timestamp;
		$purchlogs->current_end_timestamp = $purchlogs->current_timestamp;
		//exit('<pre>'.print_r($date_list, true).'<pre>');
		
		return $date_list;
	}
	
	function deletelog($deleteid){
	//change $_GET[deleteid] to $deleteid
		global $wpdb;
		if(is_numeric($deleteid)) {
		  
		  $delete_log_form_sql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='$deleteid'";
		  $cart_content = $wpdb->get_results($delete_log_form_sql,ARRAY_A);
		  foreach((array)$cart_content as $cart_item) {
		    $cart_item_variations = $wpdb->query("DELETE FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id` = '".$cart_item['id']."'", ARRAY_A);
			}
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='$deleteid'");
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` IN ('$deleteid')");
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`='$deleteid' LIMIT 1");
		  return '<div id="message" class="updated fade"><p>'.TXT_WPSC_THANKS_DELETED.'</p></div>';
		}
		
	}
	//individual purchase log functions
	function next_purch_item(){
		$this->currentitem++;
		
		$this->purchitem = $this->allpurchaselogs[$this->currentitem];
		return $this->purchitem ;
	}
	
	function the_purch_item() {
		$this->purchitem = $this->next_purch_item();
		//if ( $this->currentitem == 0 ) // loop has just started

	}
	
	function have_purch_items() {	
		//exit($this->currentitem.' '.$this->purch_item_count);
		if ($this->currentitem + 1 < $this->purch_item_count) {
			return true;
		} else if ($this->currentitem + 1 == $this->purch_item_count && $this->purch_item_count > 0) {
			// Do some cleaning up after the loop,
			$this->rewind_purch_items();
		}
		return false;
	}
	
	function rewind_purch_items() {
		$this->currentitem = -1;
		if ($this->purch_item_count > 0) {
			$this->purchitem = $this->allpurchaselogs[0];
		}
	}
	
	function the_purch_item_statuses(){
		global $wpdb;
		$sql = "SELECT name,id FROM ".WPSC_TABLE_PURCHASE_STATUSES;
		$statuses = $wpdb->get_results($sql);
		$this->purch_status_count = count($statuses);
		$this->allpurchaselogstatuses = $statuses;
		return $statuses;
	
	}
	// purchase status loop functions
	function next_purch_status(){
		$this->currentstatus++;
		
		$this->purchstatus = $this->allpurchaselogstatuses[$this->currentstatus];
		return $this->purchstatus ;
	}
	
	function the_purch_status() {
		$this->purchstatus = $this->next_purch_status();
		//if ( $this->currentitem == 0 ) // loop has just started

	}
	
	function have_purch_status() {	
		
		if ($this->currentstatus + 1 < $this->purch_status_count) {
			return true;
		} else if ($this->currentstatus + 1 == $this->purch_status_count && $this->purch_status_count > 0) {
			// Do some cleaning up after the loop,
			$this->rewind_purch_status();
		}
		return false;
	}
	
	function rewind_purch_status() {
		$this->currentstatus = -1;
		if ($this->purch_status_count > 0) {
			$this->purchstatus = $this->allpurchaselogstatuses[0];
		}
	}
	function is_checked_status(){
		
		if($this->purchstatus->id == $this->purchitem->processed){
			return 'selected="selected"';
		}else{
			return '';
		}
	}
/*
	function the_purch_item_status(){
		//exit('Purchlog status'.$this->purchitem->processed);
		return $this->purchitem->processed;
	}
	
*/
	function the_purch_item_name(){
		global $wpdb;
		$i=0;
		//exit('<pre>'.print_r($this->form_data, true).'</pre>');
		foreach((array)$this->form_data as $formdata){
		if(in_array('billingemail', $formdata)){
				$emailformid = $formdata['id'];
			}elseif(in_array('email', $formdata)){
				$emailformid = $formdata['id'];
			}
			if(in_array('billingfirstname', $formdata)){
				$fNameformid = $formdata['id'];
			}elseif(in_array('First Name', $formdata)){
				$fNameformid = $formdata['id'];
			}
			if(in_array('billinglastname', $formdata)){
				$lNameformid = $formdata['id'];
			}elseif(in_array('Last Name', $formdata)){
				$lNameformid = $formdata['id'];
			}
			$i++;
		}
		
		//$values = array();
		$sql = "SELECT value FROM ".WPSC_TABLE_SUBMITED_FORM_DATA." WHERE log_id=".$this->purchitem->id." AND form_id=".$emailformid;
		$email = $wpdb->get_var($sql);
		$sql = "SELECT value FROM ".WPSC_TABLE_SUBMITED_FORM_DATA." WHERE log_id=".$this->purchitem->id." AND form_id=".$fNameformid;
		$fname = $wpdb->get_var($sql);
		if(!$fname){
			//exit($sql);
		}
		$sql = "SELECT value FROM ".WPSC_TABLE_SUBMITED_FORM_DATA." WHERE log_id=".$this->purchitem->id." AND form_id=".$lNameformid;
		$lname = $wpdb->get_var($sql);
		$namestring = $fname.' '.$lname.' (<a href="mailto:'.$email.'?subject=Message From '.get_option('siteurl').'">'.$email.'</a>) ';
		if($fname == '' && $lname == '' && $email == ''){
			$namestring = 'N/A';
		}
		//exit($fname.' '.$lname.' ('.$email.') ');
		return $namestring;
		/*
	
		exit('<pre>'.print_r($this->form_data, true).'</pre>');
		*/
	
	}
	
	function the_purch_item_details(){
		global $wpdb;
		$sql="SELECT SUM(quantity) FROM ".WPSC_TABLE_CART_CONTENTS." WHERE purchaseid=".$this->purchitem->id;
		$sum = $wpdb->get_var($sql);
		return $sum;
	
	}
	function search_purchlog_view($searchterm){
		global $wpdb;
		$sql = "SELECT DISTINCT `".WPSC_TABLE_PURCHASE_LOGS."` . * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` LEFT JOIN `".WPSC_TABLE_PURCHASE_LOGS."` ON `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id` = `".WPSC_TABLE_PURCHASE_LOGS."`.`id` WHERE `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`value` LIKE '%".$wpdb->escape($searchterm)."%' ";
		$newlogs = $wpdb->get_results($sql);
	//	exit('<pre>'.print_r($newlogs,true).'</pre>');
		return $newlogs;
	}
}

class wpsc_purchaselogs_items{

	var $purchlogid;
	var $extrainfo;
	//the loop
	var $currentitem = -1;
	var $purchitem;
	var $allcartcontent;
	var $purch_item_count;
	//grand total
	var $totalAmount;
	//usersinfo
	var $userinfo;
	var $shippinginfo;
	
	function wpsc_purchaselogs_items($id){
		$this->purchlogid = $id;
		$this->get_purchlog_details();
	}
	
	function get_purchlog_details(){
		global $wpdb;
		
		$cartsql = "SELECT DISTINCT `".WPSC_TABLE_CART_CONTENTS."`.*, `".WPSC_TABLE_PRODUCTMETA."`.`meta_value`  FROM `".WPSC_TABLE_CART_CONTENTS."` LEFT JOIN `".WPSC_TABLE_PRODUCTMETA."` ON `".WPSC_TABLE_CART_CONTENTS."`.`prodid` = `".WPSC_TABLE_PRODUCTMETA."`.`product_id` WHERE `".WPSC_TABLE_CART_CONTENTS."`.`purchaseid`=".$this->purchlogid." AND `".WPSC_TABLE_PRODUCTMETA."`.`meta_key` = 'sku'";
		$cartcontent = $wpdb->get_results($cartsql);
		$this->allcartcontent = $cartcontent;
		//exit('<pre>'.print_r($cartcontent, true).'</pre>');
		$sql = "SELECT DISTINCT `".WPSC_TABLE_PURCHASE_LOGS."` . * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` LEFT JOIN `".WPSC_TABLE_PURCHASE_LOGS."` ON `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id` = `".WPSC_TABLE_PURCHASE_LOGS."`.`id` WHERE `".WPSC_TABLE_PURCHASE_LOGS."`.`id`=".$this->purchlogid;
		$extrainfo = $wpdb->get_results($sql);
		$this->extrainfo = $extrainfo[0];

		$usersql = "SELECT `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`value`, `".WPSC_TABLE_CHECKOUT_FORMS."`.`name`, `".WPSC_TABLE_CHECKOUT_FORMS."`.`unique_name` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` LEFT JOIN `".WPSC_TABLE_SUBMITED_FORM_DATA."` ON `".WPSC_TABLE_CHECKOUT_FORMS."`.id = `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`form_id` WHERE `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id`=".$this->purchlogid." ORDER BY `".WPSC_TABLE_CHECKOUT_FORMS."`.`order`";
		//exit($usersql);
		$userinfo = $wpdb->get_results($usersql, ARRAY_A);
		//exit('<pre>'.print_r($userinfo, true).'</pre>');
		foreach((array)$userinfo as $input_row) {
			if(stristr($input_row['unique_name'],'shipping')){
			 	 $shippinginfo[$input_row['unique_name']] = $input_row;
			}else{
				 $billingdetails[$input_row['unique_name']] = $input_row;
			}
		}		
		$this->userinfo = $billingdetails;
		$this->shippinginfo= $shippinginfo;
		$this->purch_item_count = count($cartcontent);
//		exit('<pre>'.print_r($cartcontent, true).'</pre>');
	}

	function next_purch_item(){
		$this->currentitem++;
		$this->purchitem = $this->allcartcontent[$this->currentitem];
		return $this->purchitem ;
	}
	
	function the_purch_item() {
		$this->purchitem = $this->next_purch_item();
		//if ( $this->currentitem == 0 ) // loop has just started

	}
	
	function have_purch_item() {	
		
		if ($this->currentitem + 1 < $this->purch_item_count) {
			return true;
		} else if ($this->currentitem + 1 == $this->purch_item_count && $this->purch_item_count > 0) {
			// Do some cleaning up after the loop,
			$this->rewind_purch_item();
		}
		return false;
	}
	
	function rewind_purch_item() {
		$this->currentitem = -1;
		if ($this->purch_item_count > 0) {
			$this->purchitem = $this->allcartcontent[0];
		}
	}
	function have_downloads_locked(){
		global $wpdb;
		$sql = "SELECT `ip_number` FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE purchid=".$this->purchlogid;
		$ip_number = $wpdb->get_var($sql);
		return $ip_number;
	}

}

?>