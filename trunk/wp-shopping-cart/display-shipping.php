<?php
if (isset($_GET['googlecheckoutshipping'])) {
	include('google_shipping_country.php');
	return;
	exit();
}

// exit("<pre>".print_r($_POST,1)."</pre>");

$curgateway = get_option('shipping_gw');
$changes_made = false;



if(is_numeric($_POST['payment_method']) && (get_option('payment_gateway') != $_POST['payment_method'])) {
	update_option('payment_method', $_POST['payment_method']);
	$changes_made = true;
}

if($_POST['custom_shipping_options'] != null) {
	update_option('custom_shipping_options', $_POST['custom_shipping_options']);
	$changes_made = true;
}

		if($_POST['do_not_use_shipping'] == 1) {
			update_option('do_not_use_shipping', 1);
		} else {
			update_option('do_not_use_shipping', 0);
		}
		
		if(isset($_POST['base_zipcode'])) {
			update_option('base_zipcode', $_POST['base_zipcode']);
		}
		
		if($_POST['shipwire'] == 1) {
			update_option('shipwire', 1);
		} else {
			update_option('shipwire', 0);
		}

		if($_POST['shipwireemail'] != null) {
			update_option('shipwireemail', $_POST['shipwireemail']);
		}
	
		if($_POST['shipwirepassword'] != null) {
			update_option('shipwirepassword', $_POST['shipwirepassword']);
		}

if(isset($_POST['custom_gateway'])) {
  // this particular form field refuses to submit in a way that appears to defy logic if dealt with like the others, hence this overkill
	if($_POST['custom_gateway'] == 1) {
		update_option('custom_gateway', 1);
		$changes_made = true;
	} else if(($_POST['custom_gateway'] == 0) && (get_option('custom_gateway') != 0)) {
		update_option('custom_gateway', 0);
		$changes_made = true;
	}
}

if(($_POST['shipping_gw'] != null) && ($_POST['submit_details'] == null)) {
	update_option('shipping_gw', $_POST['shipping_gw']);
	$curgateway = get_option('shipping_gw');
	$changes_made = true;
}
//exit("<pre>" .print_r($_POST,true). "</pre>");
if(($_POST['shipping_gw'] != null)) {
	foreach($GLOBALS['wpsc_shipping_modules'] as $shipping) {
		if($shipping->internal_name == $_POST['shipping_gw']) {
			$shipping->submit_form();
			$changes_made = true;
		}
	}
}

if($changes_made == true) {
  //echo "<div class='updated'><p align='center'>".TXT_WPSC_THANKSAPPLIED."</p></div>";
}

if (get_option('custom_gateway')) {
	$custom_gateway1 = "checked='checked'";
} else {
	$custom_gateway2 = "checked='checked'";
}

$form = "";
// echo ("<pre>".print_r($GLOBALS['wpsc_shipping_modules'],1)."</pre>");
foreach($GLOBALS['wpsc_shipping_modules'] as $shipping) {
	if($shipping->internal_name == $curgateway ) {
		$selected = " selected='selected'";
		$form = $shipping->getForm();
	} else {
		$selected = '';
	}
	$shippinglist .="<option value='".$shipping->internal_name."' ".$selected." >".$shipping->name."</option>";
}
$shippinglist = "<option value='".$nogw."'>".TXT_WPSC_PLEASESELECTASHIPPINGPROVIDER."</option>" . $shippinglist;



$selected[get_option('payment_method')] = "checked='true'";
if ($_GET['shipping_options']=='true') {
?>
<script language='JavaScript' type='text/javascript'>
function selectgateway() {
	document.forms.shippingopt.submit();
}
</script>
<div class="wrap">
		<form name='shippingopt' method='POST' id='shipping_options' action='admin.php?page=<?php echo WPSC_DIR_NAME; ?>/options.php'>
		<input type='hidden' name='shipping_submits' value='true'>
	<?php 
		if (get_option('custom_gateway') == 1){ 
			$custom_gateway_hide="style='display:block;'";
			$custom_gateway1 = 'checked="true"';
		} else {
			$custom_gateway_hide="style='display:none;'";
			$custom_gateway2 = 'checked="true"';
		}
	?>
  <h2><?php echo TXT_WPSC_SHIPPINGOPTIONS;?></h2>

		<div class="tablenav wpsc_admin_nav">
			<div class="alignright">
<!-- 				<a class="about_this_page" href="http://www.instinct.co.nz/e-commerce/payment-options/" target="_blank"><span>About This Page</span> </a> -->
			</div>
			<br class="clear"/>
		</div>

				<table class='wpsc_options form-table'>
				<tr>
				<th scope="row">
									<?php echo TXT_WPSC_USE_SHIPPING;?>:
				</th>
						<td>
						<?php
						$do_not_use_shipping = get_option('do_not_use_shipping');
				$do_not_use_shipping1 = "";
				$do_not_use_shipping2 = "";
				switch($do_not_use_shipping) {    
					case 1:
						$do_not_use_shipping1 = "checked ='true'";
						break;
												
					case 0:
					default:
						$do_not_use_shipping2 = "checked ='true'";
						break;
				}
						
				?>
						<input type='radio' value='0' name='do_not_use_shipping' id='do_not_use_shipping2' <?php echo $do_not_use_shipping2; ?> /> <label for='do_not_use_shipping2'><?php echo TXT_WPSC_YES;?></label>&nbsp;
				<input type='radio' value='1' name='do_not_use_shipping' id='do_not_use_shipping1' <?php echo $do_not_use_shipping1; ?> /> <label for='do_not_use_shipping1'><?php echo TXT_WPSC_NO;?></label><br />
						<?php echo TXT_WPSC_USE_SHIPPING_DESCRIPTION;?>
								</td>
										</tr>
										<?php
										if (get_option('base_country') == 'US') {
									echo "<tr>";
									echo "<th>";
									echo "Zipcode:";
									echo "</th>";
									echo "<td>";
									echo "<input type='text' name='base_zipcode' value='".get_option('base_zipcode')."'>";
									echo "</td>";
									echo "</tr>";
										}
										?>
								
								
										<?php
										$shipwire1 = "";
										$shipwire2 = "";
										switch(get_option('shipwire')) {    
										case 1:
										$shipwire1 = "checked ='true'";
										$shipwire_settings = 'style=\'display: block;\'';
										break;
												
										case 0:
										default:
										$shipwire2 = "checked ='true'";
										$shipwire_settings = '';
										break;
										}
						
										?>
								
										<tr>
										<th scope="row">
										<?php echo TXT_WPSC_SHIPWIRESETTINGS;?><span style='color: red;'></span> :
										</th>
										<td>
										<input type='radio' onclick='jQuery("#wpsc_shipwire_setting").show()' value='1' name='shipwire' id='shipwire1' <?php echo $shipwire1; ?> /> <label for='shipwire1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
										<input type='radio' onclick='jQuery("#wpsc_shipwire_setting").hide()' value='0' name='shipwire' id='shipwire2' <?php echo $shipwire2; ?> /> <label for='shipwire2'><?php echo TXT_WPSC_NO;?></label>
										<?php
										$shipwrieemail = get_option("shipwireemail");
										$shipwriepassword = get_option("shipwirepassword");
										?>
										<div id='wpsc_shipwire_setting' <?php echo $shipwire_settings; ?>>
										<table>
										<tr><td><?=TXT_WPSC_SHIPWIREEMAIL;?> :</td><td> <input type="text" name="shipwireemail" value="<?=$shipwrieemail;?>"></td></tr>
										<tr><td><?=TXT_WPSC_SHIPWIREPASSWORD;?> :</td><td><input type="text" name="shipwirepassword" value="<?=$shipwriepassword;?>"></td></tr>
										<tr><td><a onclick='shipwire_sync()' style="cursor:pointer;">Sync product</a></td></tr>
										</table>
										</div>
										</td>
										</tr>
										</table>
		<table id='gateway_options' >
			<tr>
				<td class='select_gateway'>
					<div class="categorisation_title">
					  <strong class="form_group"><?php echo TXT_WPSC_SHIPPING_MODULES; ?></strong>
					</div>
					<p>
						<?php echo TXT_WPSC_CHOOSE_SHIPPING_MODULES; ?>
					</p>
					<?php
					$selected_shippings = get_option('custom_shipping_options');
					foreach($GLOBALS['wpsc_shipping_modules'] as $shipping) {
// 						exit("<pre>".print_r($shipping,1)."</pre>");
						if (in_array($shipping->getInternalName(), (array)$selected_shippings)) {
							echo "						";// add the whitespace to the html
							echo "<p><input name='custom_shipping_options[]' checked='checked' type='checkbox' value='{$shipping->internal_name}' id='{$shipping->internal_name}_id'><label for='{$shipping->internal_name}_id'>{$shipping->name}</label></p>\n\r";
						} else {
							echo "						";
							echo "<p><input name='custom_shipping_options[]' type='checkbox' value='{$shipping->internal_name}' id='{$shipping->internal_name}_id'><label for='{$shipping->internal_name}_id'>{$shipping->name}</label></p>\n\r";
						}
					}
					?>
					<div class='submit gateway_settings'>
						<input type='hidden' value='true' name='update_gateways'/>
						<input type='submit' value='Update &raquo;' name='updateoption'/>
					</div>
				</td>
				
				<td class='gateway_settings' rowspan='2'>
					<table class='form-table'>
						<tr class="firstrowth">
							<td colspan='2' style='border-bottom: none;'>
								<strong class="form_group"><?php echo TXT_WPSC_CONFIGURE_SHIPPING_MODULES;?></strong>
							</td>
						</tr>
						<tr>
							<td style='border-top: none;'>
								<h4><?php echo TXT_WPSC_SHIPPING_MODULES;?></h4>
							</td>
							<td style='border-top: none;'>
								<select name='shipping_gw' onChange='selectgateway();'>
									<?php echo $shippinglist; ?>
								</select>
							</td>
						</tr>
						<?php echo $form; ?>
					
						<tr class='update_gateway' >
							<td colspan='2'>
								<div class='submit'>
									<input type='submit' value='Update &raquo;' name='updateoption'/>
									
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<!--<tr>
				<td>
					<h4><?php echo TXT_WPSC_WE_RECOMMEND; ?></h4>
					<a style="border-bottom:none;" href="https://www.paypal.com/nz/mrb/pal=LENKCHY6CU2VY" target="_blank"><img src="<?php echo WPSC_URL; ?>/images/paypal-referal.gif" border="0" alt="Sign up for PayPal and start accepting credit card payments instantly."></a> <br /><br />
					<a style="border-bottom:none;" href="http://checkout.google.com/sell/?promo=seinstinct" target="_blank"><img src="https://checkout.google.com/buyer/images/google_checkout.gif" border="0" alt="Sign up for Google Checkout"></a>
				</td>
			</tr>-->
		</table>
	</form>
</div>
				
<?php
}
?>