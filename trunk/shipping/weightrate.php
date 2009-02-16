<?php
class weightrate {
	var $internal_name, $name;
	function weightrate () {
		$this->internal_name = "weightrate";
		$this->name="Weight Rate";
		$this->is_external=false;
		return true;
	}
	
	function getId() {
// 		return $this->usps_id;
}
	
	function setId($id) {
// 		$usps_id = $id;
// 		return true;
	}
	
	function getName() {
		return $this->name;
	}
	
	function getInternalName() {
		return $this->internal_name;
	}
	
	function getForm() {
		$output.="<tr><th>".TXT_WPSC_TOTAL_WEIGHT_IN_POUNDS."</th><th>".TXT_WPSC_SHIPPING_PRICE."</th></tr>";
		$layers = get_option("weight_rate_layers");
		if ($layers != '') {
			foreach($layers as $key => $shipping) {
				$output.="<tr><td style='width: 258px;'><div><i style='color: grey;'>".TXT_WPSC_IF_WEIGHT_IS."</i><input type='text' value='$key' name='weight_layer[]'size='10'><i style='color: grey;'>".TXT_WPSC_AND_ABOVE."</i></div></td><td><input type='text' value='{$shipping}' name='weight_shipping[]' size='10'></td></tr>";
			}
		}
		$output.="<input type='hidden' name='checkpage' value='weight'>";
		$output.="<tr class='addlayer'><td colspan='2'>Layers: <a style='cursor:pointer;' onclick='addweightlayer()'>Add Layer</a></td></tr>";
		return $output;
	}
	
	function submit_form() {
		$layers = $_POST['weight_layer'];
		$shippings = $_POST['weight_shipping'];
		if ($shippings != ''){
			foreach($shippings as $key => $price) {
				if ($price == '') {
					unset($shippings[$key]);
					unset($layers[$key]);
				} else {
					$new_layer[$layers[$key]] = $price;
				}
			}
		}
		if ($_POST['checkpage'] == 'weight') {
			update_option('weight_rate_layers',$new_layer);
		}
		return true;
	}
	
	function getQuote() {
		global $wpdb;
		$shopping_cart = $_SESSION['nzshpcrt_cart'];
		$weight = shopping_cart_total_weight();
		$layers = get_option('weight_rate_layers');
		//echo "<pre>".print_r($weight,true)."</pre>";
		if ($layers != '') {
			$layers = array_reverse($layers,true);
			foreach ($layers as $key => $shipping) {
				if ($weight >= (float)$key) {
					return array(array("Weight Rate"=>$shipping));
				}
			}
		
			return array(array("Weight Rate"=>array_shift($layers)));
		}
	}
}
$weightrate = new weightrate();
$wpsc_shipping_modules[$weightrate->getInternalName()] = $weightrate;