<?php
function wpsc_options_shipping(){
global $wpdb,$external_shipping_modules,$internal_shipping_modules;

// sort into external and internal arrays.
foreach($GLOBALS['wpsc_shipping_modules'] as $key => $module) {
	if($module->is_external == true) {
		$external_shipping_modules[$key] = $module;
	} else {
		$internal_shipping_modules[$key] = $module;
	}
}

//get shipping options that are selected
$selected_shippings = get_option('custom_shipping_options');

?>
<form name='cart_options' id='cart_options' method='post' action=''>
	
	<script language='JavaScript' type='text/javascript'>
function selectgateway() {
	document.forms.shippingopt.submit();
}
</script>
<div class="wrap">
<div class="metabox-holder">
		<form name='shippingopt' method='post' id='shipping_options' action=''>
		<input type='hidden' name='shipping_submits' value='true' />
		<input type='hidden' name='wpsc_admin_action' value='submit_options' />
		
	<?php 
		if (get_option('custom_gateway') == 1){ 
			$custom_gateway_hide="style='display:block;'";
			$custom_gateway1 = 'checked="checked"';
		} else {
			$custom_gateway_hide="style='display:none;'";
			$custom_gateway2 = 'checked="checked"';
		}
	?>
  <h2 class='wpsc_special'><?php echo TXT_WPSC_SHIPPINGOPTIONS;?></h2>
  		<?php 
		/* wpsc_setting_page_update_notification displays the wordpress styled notifications */
		wpsc_settings_page_update_notification(); ?>
			<?php if (IS_WP27) { ?>
				<div class='postbox'>
					<h3 class='hndle'><?php echo TXT_WPSC_OPTIONS_GENERAL_HEADER; ?></h3>
					<div class='inside'>
			
			<?php } else { ?>
			<div class="categorisation_title">
				<strong class="form_group">
				    <?php echo TXT_WPSC_OPTIONS_GENERAL_HEADER; ?>
				</strong>
				<br class="clear"/>
			</div>
			<?php } ?>
			
				<table class='wpsc_options form-table'>
				<tr>
					<th scope="row">
						<?php echo TXT_WPSC_USE_SHIPPING;?>:
					</th>
					<td>
					<?php
					$do_not_use_shipping = get_option('do_not_use_shipping');
				//	exit($do_not_use_shipping);
					$do_not_use_shipping1 = "";
					$do_not_use_shipping2 = "";
					switch($do_not_use_shipping) {    
						case 1:
							$do_not_use_shipping1 = "checked ='checked'";
							break;
													
						case 0:
						default:
							$do_not_use_shipping2 = "checked ='checked'";
							break;
					}
							
					?>
							<input type='radio' value='0' name='wpsc_options[do_not_use_shipping]' id='do_not_use_shipping2' <?php echo $do_not_use_shipping2; ?> /> <label for='do_not_use_shipping2'><?php echo TXT_WPSC_YES;?></label>&nbsp;
					<input type='radio' value='1' name='wpsc_options[do_not_use_shipping]' id='do_not_use_shipping1' <?php echo $do_not_use_shipping1; ?> /> <label for='do_not_use_shipping1'><?php echo TXT_WPSC_NO;?></label><br />
							<?php echo TXT_WPSC_USE_SHIPPING_DESCRIPTION;?>
					</td>
				</tr>
								
				<tr>
					<th>Zipcode:</th>
					<td>
						<input type='text' name='wpsc_options[base_zipcode]' value='<?php echo get_option('base_zipcode'); ?>' />
						<br /><?php echo TXT_WPSC_USPS_DESC; ?>
					</td>
				</tr>
				<?php
					$shipwire1 = "";
					$shipwire2 = "";
					switch(get_option('shipwire')) {    
					case 1:
					$shipwire1 = "checked ='checked'";
					$shipwire_settings = 'style=\'display: block;\'';
					break;
							
					case 0:
					default:
					$shipwire2 = "checked ='checked'";
					$shipwire_settings = '';
					break;
					}
				?>
								
				<tr>
					<th scope="row">
						<?php echo TXT_WPSC_SHIPWIRESETTINGS;?><span style='color: red;'></span> :
					</th>
					<td>
						<input type='radio' onclick='jQuery("#wpsc_shipwire_setting").show()' value='1' name='wpsc_options[shipwire]' id='shipwire1' <?php echo $shipwire1; ?> /> <label for='shipwire1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
						<input type='radio' onclick='jQuery("#wpsc_shipwire_setting").hide()' value='0' name='wpsc_options[shipwire]' id='shipwire2' <?php echo $shipwire2; ?> /> <label for='shipwire2'><?php echo TXT_WPSC_NO;?></label>
						<?php
						$shipwireemail = get_option("shipwireemail");
						$shipwirepassword = get_option("shipwirepassword");
						?>
						<div id='wpsc_shipwire_setting' <?php echo $shipwire_settings; ?>>
						<table>
						<tr><td><?php echo TXT_WPSC_SHIPWIREEMAIL; ?> :</td><td> <input type="text" name='wpsc_options[shipwireemail]' value="<?php echo $shipwireemail;?>" /></td></tr>
						<tr><td><?php echo TXT_WPSC_SHIPWIREPASSWORD; ?> :</td><td><input type="text" name='wpsc_options[shipwirepassword]' value="<?php echo $shipwirepassword;?>" /></td></tr>
						<tr><td><a onclick='shipwire_sync()' style="cursor:pointer;">Sync product</a></td></tr>
						</table>
						</div>
					</td>
			   </tr>
			   </table>
				<?php if (IS_WP27) { ?>
					</div>
					</div>
				<?php }	?>
		<table id='gateway_options' >
			<tr>
				<td class='select_gateway'>
					<?php if (IS_WP27) { ?>
					<div class='postbox'>
						<h3 class='hndle'><?php echo TXT_WPSC_SHIPPING_MODULES ?></h3>
						<div class='inside'>
			
					<?php } else { ?>
					<div class="categorisation_title">
					  <strong class="form_group"><?php echo TXT_WPSC_SHIPPING_MODULES; ?></strong>
					</div>
					
					<?php } ?>
					
					<p>
						<?php echo TXT_WPSC_CHOOSE_SHIPPING; ?>
					</p>
					<br />
					<p>
						<strong><?php echo TXT_WPSC_CHOOSE_INTERNAL_SHIPPING_MODULES; ?></strong>
					</p>
					<?php
					foreach($internal_shipping_modules as $shipping) {
// 						exit("<pre>".print_r($shipping,1)."</pre>");
						if (in_array($shipping->getInternalName(), (array)$selected_shippings)) { ?>
						
							<div class='wpsc_shipping_options'>
							<div class='wpsc-shipping-actions'>
									| <span class="edit">
										<a class='edit-shipping-module' rel="<?php echo $shipping->internal_name; ?>" onclick="event.preventDefault();" title="Edit this Shipping Module" href='<?php echo htmlspecialchars(add_query_arg('shipping_module', $shipping->internal_name)); ?>' style="cursor:pointer;">Edit</a>
									</span> |
						   </div>
						
							<p><input name='custom_shipping_options[]' checked='checked' type='checkbox' value='<?php echo $shipping->internal_name; ?>' id='<?php echo $shipping->internal_name; ?>_id' /><label for='<?php echo $shipping->internal_name; ?>_id'><?php echo $shipping->name; ?></label></p>
   						   </div>
						<?php
						} else { ?>
						
							<div class='wpsc_shipping_options'>
							<div class='wpsc-shipping-actions'>
									| <span class="edit">
										<a class='edit-shippping-module' onclick="event.preventDefault();" rel="<?php echo $shipping->internal_name; ?>"  title="Edit this Shipping Module" href='<?php echo htmlspecialchars(add_query_arg('shipping_module', $shipping->internal_name)); ?>' style="cursor:pointer;">Edit</a>
									</span> |
						   </div>
							<p><input name='custom_shipping_options[]' type='checkbox' value='<?php echo $shipping->internal_name; ?>' id='<?php echo $shipping->internal_name; ?>_id' /><label for='<?php echo $shipping->internal_name; ?>_id'><?php echo $shipping->name; ?></label></p>
						   </div>
							 <?php
						}
					}
					?>
					<br />
					<p>
						<strong><?php echo TXT_WPSC_CHOOSE_EXTERNAL_SHIPPING_MODULES; ?></strong>
						<?php
						if(!function_exists('curl_init')) { ?>
							<br /><span style='color: red; font-size:8pt; line-height:10pt;'><?php echo TXT_WPSC_SHIPPING_BUT_NO_CURL; ?></span>
						<?php 
						} ?>
					</p>
					<?php
					
					// print the internal shipping methods
					foreach($external_shipping_modules as $shipping) {
					  $disabled = '';
					  if(($shipping->requires_curl == true) && !function_exists('curl_init')) {
							$disabled = "disabled='disabled'";
					  }

						if (in_array($shipping->getInternalName(), (array)$selected_shippings)) {
						?>
						<div class='wpsc_shipping_options'>
						<div class="wpsc-shipping-actions">
									| <span class="edit">
										<a class='edit-shippping-module' onclick="event.preventDefault();" rel="<?php echo $shipping->internal_name; ?>"  title="Edit this Shipping Module" href='<?php echo htmlspecialchars(add_query_arg('shipping_module', $shipping->internal_name)); ?>' style="cursor:pointer;">Edit</a>
									</span> |
						</div>
						<p><input <?php echo $disabled; ?> name='custom_shipping_options[]' checked='checked' type='checkbox' value='<?php echo $shipping->internal_name; ?>' id='<?php echo $shipping->internal_name; ?>_id' /><label for='<?php echo $shipping->internal_name; ?>_id'><?php echo $shipping->name; ?></label></p>
   						</div>
						<?php
						} else { ?>
						<div class='wpsc_shipping_options'>
						<div class="wpsc-shipping-actions">
									| <span class="edit">
										<a class='edit-shippping-module' onclick="event.preventDefault();" rel="<?php echo $shipping->internal_name; ?>"  title="Edit this Shipping Module" href='<?php echo htmlspecialchars(add_query_arg('shipping_module', $shipping->internal_name)); ?>' style="cursor:pointer;">Edit</a>
									</span> |
						</div>
						<p><input <?php echo $disabled; ?> name='custom_shipping_options[]' type='checkbox' value='<?php echo $shipping->internal_name; ?>' id='<?php echo $shipping->internal_name; ?>_id' /><label for='<?php echo $shipping->internal_name; ?>_id'><?php echo $shipping->name; ?></label></p>
						</div>
						<?php
						}
					}
					?>
					
					<div class='submit gateway_settings'>
						<input type='hidden' value='true' name='update_gateways'/>
						<input type='submit' value='<?php echo TXT_WPSC_UPDATE_BUTTON;?>' name='updateoption'/>
					</div>
					<?php if (IS_WP27) { ?>
					    </div>
					    </div>
					<?php }	?>
				</td>
				
				<td class='gateway_settings' rowspan='2'>
					<div class='postbox'>
					  <?php
						$shipping_data =	wpsc_get_shipping_form($_SESSION['previous_shipping_name']);
					  ?>
						<h3 class='hndle'><?php echo $shipping_data['name']; ?></h3>
						<div class='inside'>
						<table class='form-table'>
							<?php echo $shipping_data['form_fields']; ?>
						</table>
						<?php if( $shipping_data['has_submit_button'] == 0) {
						 $update_button_css = 'style= "display: none;"';
						 } ?>
							<div class='submit' <?php echo $update_button_css; ?>>
								<input type='submit' value='<?php echo TXT_WPSC_UPDATE_BUTTON;?>' name='updateoption' />
							</div>
					</div>
				</td>
			</tr>
		
		</table>
	</form>
</div>
		

	</div>
</form>
<?php
}
?>