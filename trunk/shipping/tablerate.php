<?php

class tablerate {
	var $internal_name, $name;

	function tablerate() {
		$this->internal_name = "tablerate";
		$this->name="Table Rate";
		$this->is_external=false;
		return true;
	}

	function getName() {
		return $this->name;
	}

	function getInternalName() {
		return $this->internal_name;
	}

	function getForm() {
		$output.="<tr><th>".__('Total Price', 'wpsc')."</th><th>".__('Shipping Price', 'wpsc')."</th></tr>";
		$layers = get_option("table_rate_layers");
		if ($layers != '') {
			foreach ($layers as $key => $shipping) {
				$output.="<tr class='rate_row'>
							<td>
								<i style='color: grey;'>".__('If price is ', 'wpsc')."</i>
								<input type='text' name='layer[]' value='$key' size='4' />
								<i style='color: grey;'> ".__(' and above', 'wpsc')."</i>
							</td>
							<td>
								".wpsc_get_currency_symbol()."
								<input type='text' value='{$shipping}' name='shipping[]'  size='4'>
								&nbsp;&nbsp;<a href='#' class='delete_button' >".__('Delete', 'wpsc')."</a>

							</td>
						</tr>";
			}
		}
		$output.="<input type='hidden' name='checkpage' value='table'>";
		$output.="<tr class='addlayer'><td colspan='2'>Layers: <a href='' style='cursor:pointer;' id='addlayer' >Add Layer</a></td></tr>";
		return $output;
	}

	function submit_form() {

		$layers = (array)$_POST['layer'];
		$shippings = (array)$_POST['shipping'];

		if ($shippings != '') {
			foreach ($shippings as $key => $price) {
				if ($price == '') {
					unset($shippings[$key]);
					unset($layers[$key]);
				} else {
					$new_layer[$layers[$key]] = $price;
				}
			}
		}

		// Sort the data before it goes into the database. Makes the UI make more sense
		if (isset($new_layer)) {
			krsort($new_layer);
		}

		if ($_POST['checkpage'] == 'table') {
			update_option('table_rate_layers', $new_layer);
		}
		return true;
	}

	function getQuote() {

		global $wpdb, $wpsc_cart;

		$shopping_cart = $_SESSION['nzshpcrt_cart'];

		if (is_object($wpsc_cart)) {
			$price = $wpsc_cart->calculate_subtotal(true);
		}

		$layers = get_option('table_rate_layers');

		if ($layers != '') {

			// At some point we should probably remove this as the sorting should be 
			// done when we save the data to the database. But need to leave it here
			// for people who have non-sorted settings in their database
			krsort($layers);

			foreach ($layers as $key => $shipping) {
				if ($price >= (float)$key) {
					if (stristr($shipping, '%')) {
						// Shipping should be a % of the cart total
						$shipping = str_replace('%','',$shipping);
						$shipping_amount = $price * ( $shipping / 100 );
					} else {
						// Shipping is an absolute value
						$shipping_amount = $shipping;
					}
					return array("Table Rate"=>$shipping_amount);
				}
			}

			$shipping = array_shift($layers);

			if (stristr($shipping, '%')) {
				$shipping = str_replace('%','',$shipping);
				$shipping_amount = $price * ( $shipping / 100 );
			} else {
				$shipping_amount = $shipping;
			}

			return array("Table Rate"=>$shipping_amount);
		}
	}

	function get_item_shipping( &$cart_item ) {

		global $wpdb, $wpsc_cart;

		$unit_price = $cart_item->unit_price;
		$quantity = $cart_item->quantity;
		$weight = $cart_item->weight;
		$product_id = $cart_item->product_id;

		if (is_numeric($product_id) && (get_option('do_not_use_shipping') != 1) && ($_SESSION['quote_shipping_method'] == 'flatrate')) {
			$sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='$product_id' LIMIT 1";
			$product_list = $wpdb->get_row($sql, ARRAY_A) ;

			if ($product_list['no_shipping'] == 0) {

				//if the item has shipping
				if ($country_code == get_option('base_country')) {
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
	}

}

$tablerate = new tablerate();
$wpsc_shipping_modules[$tablerate->getInternalName()] = $tablerate;
?>
