<?php
class ups {
    var $internal_name, $name;
        public $service_url = "";
        private $Services = "";

    function ups() {
            $this->internal_name = "ups";
            $this->name="UPS";
            $this->is_external=true;
            $this->requires_curl=true;
            $this->requires_weight=true;
            $this->needs_zipcode=true;
            $this->_includeUPSData();
            $this->_setServiceURL();
            return true;
    }

    function getId() {
//         return $this->usps_id;
    }

    function setId($id) {
//         $usps_id = $id;
//         return true;
    }

    private function _setServiceURL(){
        global $wpdb;
        $wpsc_ups_settings = get_option("wpsc_ups_settings");
        $wpsc_ups_environment = $wpsc_ups_settings["upsenvironment"];
        if ($wpsc_ups_environment == "1"){
            $this->service_url = "https://wwwcie.ups.com/ups.app/xml/Rate";
        }else{
            $this->service_url = "https://www.ups.com/ups.app/xml/Rate";
        }
    }

    function getName() {
            return $this->name;
    }

    function getInternalName() {
            return $this->internal_name;
    }

    private function _includeUPSData(){
        $this->drop_types = array(
                            "1"=>"Daily Pickup",
                            "3"=>"Customer Counter",
                            "6"=>"One Time Pickup",
                            "7"=>"On Call Air",
                            "11"=>"Suggested Retail Rates",
                            "19"=>"Letter Center",
                            "20"=>"Air Service Center"
                            );

        $this->Services = array(
            "14" => "Next Day Air Early AM",
            "01" => "Next Day Air",
            "13" => "Next Day Air Saver",
            "59" => "2nd Day Air AM",
            "02" => "2nd Day Air",
            "12" => "3 Day Select",
            "03" => "Ground",
            "11" => "Standard",
            "07" => "Worldwide Express",
            "54" => "Worldwide Express Plus",
            "08" => "Worldwide Expedited",
            "65" => "Saver",
            "82" => "UPS Today Standard",
            "83" => "UPS Today Dedicated Courier",
            "84" => "UPS Today Intercity",
            "85" => "UPS Today Express",
            "86" => "UPS Today Express Saver"
        );
    }

    function getForm(){
            if (!isset($this->Services)){
                $this->_includeUPSData();
            }

            //__('Your Packaging', 'wpsc');  <-- use to translate
            $wpsc_ups_settings = get_option("wpsc_ups_settings");
            $wpsc_ups_services = get_option("wpsc_ups_services");
            // Defined on page 41 in UPS API documentation RSS_Tool_06_10_09.pdf
            /*$packaging_options['00'] = __('**UNKNOWN**', 'wpsc');*/
            $packaging_options['01'] = __('UPS Letter', 'wpsc');
            $packaging_options['02'] = __('Your Packaging', 'wpsc');
            $packaging_options['03'] = __('UPS Tube', 'wpsc');
            $packaging_options['04'] = __('UPS Pak', 'wpsc');
            $packaging_options['21'] = __('UPS Express Box', 'wpsc');
            $packaging_options['2a'] = __('UPS Express Box - Small', 'wpsc');
            $packaging_options['2b'] = __('UPS Express Box - Medium', 'wpsc');
            $packaging_options['2c'] = __('UPS Express Box - Large', 'wpsc');

            $output = "<tr>\n\r";
            $output .= "    <td>".__('Destination Type', 'wpsc')."</td>\n\r";
            $output .= "    <td>\n\r";

            // Default is Residential
            $checked[0] = "checked='checked'";
            $checked[1] = "";
            if ($wpsc_ups_settings['49_residential'] == "2"){
                $checked[0] = "";
                $checked[1] = "checked='checked'";
            }

            $output .= "        <label><input type='radio' {$checked[0]} value='1' name='wpsc_ups_settings[49_residential]'/>".__('Residential Address', 'wpsc')."</label><br />\n\r";
            $output .= "        <label><input type='radio' {$checked[1]} value='2' name='wpsc_ups_settings[49_residential]'/>".__('Commercial Address', 'wpsc')."</label>\n\r";
            $output .= "    </td>\n\r";
            $output .= "</tr>\n\r";
            $output .= "<tr>\n\r";

            // Dropoff Type
            $output .= "    <td>".__('Dropoff Type', 'wpsc')."</td>\n\r";
            $output .= "    <td>\n\r";
            $output .= "        <select name='wpsc_ups_settings[DropoffType]'>\n\r";
            
            $sel2_drop = "";
            if (empty($wpsc_ups_settings['DropoffType'])){
                $sel2_drop = "1";
            }else{ $sel2_drop = $wpsc_ups_settings['DropoffType']; }

            foreach(array_keys($this->drop_types) as $dkey){
                $sel = "";
                if ($sel2_drop == $dkey){
                    $sel = 'selected="selected"';
                }
                $output .= "            <option value=\"".$dkey."\" ".$sel." >".$this->drop_types[$dkey]."</option>\n\r";
            }
            $output .= "        </select>\n\r";
            $output .= "    </td>\n\r";
            $output .= "</tr>\n\r";

            // Packaging Config
            $output .= "<tr>\n\r";
            $output .= "    <td>".__('Packaging', 'wpsc')."</td>\n\r";
            $output .= "    <td>\n\r";
            $output .= "        <select name='wpsc_ups_settings[48_container]'>\n\r";
            foreach($packaging_options as $key => $name) {
              $selected = '';
                    if($key == $wpsc_ups_settings['48_container']) {
                            $selected = "selected='true' ";
                    }
                    $output .= "            <option value='{$key}' {$selected}>{$name}</option>\n\r";
            }
            $output .= "        </select>\n\r";
            $output .= "    </td>\n\r";
            $output .= "</tr>\n\r";

            $selected_env = $wpsc_ups_settings['upsenvironment'];
            if ($selected_env == "1"){
                $env_test = "checked=\"checked\"";
            }
            $output .= ("
                        <tr>
                            <td><label for=\"ups_env_test\" >".__('Use Testing Environment', 'wpsc')."</label></td>
                            <td>
                                <input type=\"checkbox\" id=\"ups_env_test\" name=\"wpsc_ups_settings[upsenvironment]\" value=\"1\" ".$env_test." /><br />
                            </td>
                        </tr>
                        ");
            // Added by Greg Gullett --
            $selected_negotiated_rate = $wpsc_ups_settings['ups_negotiated_rates'];
            $negotiated_rates = "";
            if ($selected_negotiated_rate == "1"){
                $negotiated_rates = "checked=\"checked\"";
            }
            $output .= ("
                        <tr>
                            <td><label for=\"ups_negotiated_rates\" >".__('Show UPS negotiated rates', 'wpsc')." *</label></td>
                            <td>
                                <input type=\"checkbox\" id=\"ups_negotiated_rates\" name=\"wpsc_ups_settings[ups_negotiated_rates]\" value=\"1\" ".$negotiated_rates." /><br />
                            </td>
                        </tr>
                        ");
            $output .= ("
                        <tr>
                            <td>
                                ".__('UPS Preferred Services', 'wpsc')."
                            </td>
                            <td>
                                <div id=\"resizeable\" class=\"ui-widget-content multiple-select\">");

            ksort($this->Services);
            $first=false;
            foreach(array_keys($this->Services) as $service){
                $checked = "";
                if(is_array($wpsc_ups_services)){
                    if ((array_search($service,$wpsc_ups_services) !== false)){
                        $checked = "checked=\"checked\"";
                    }
                }
                $output .= ("<input type=\"checkbox\" id=\"wps_ups_srv_$service\" name=\"wpsc_ups_services[]\" value=\"$service\" $checked />
                             <label for=\"wps_ups_srv_$service\">".$this->Services[$service]."</label>
                             <br />");
            }

            $output .= ("       </div>
                                <br />
                                -Note: ".__('All services used if no services selected','wpsc')."
                            </td>
                        </tr>");
            $output .= ("<tr>
                             <td>".__('UPS Account #', 'wpsc')." *:</td>
                             <td>
                                 <input type=\"text\" name='wpsc_ups_settings[upsaccount]' value=\"".$wpsc_ups_settings['upsaccount']."\" />
                             </td>
                         </tr>");
            $output .= ("<tr>
                             <td>".__('UPS Username', 'wpsc')." :</td>
                             <td>
                                 <input type=\"text\" name='wpsc_ups_settings[upsusername]' value=\"".base64_decode($wpsc_ups_settings['upsusername'])."\" />
                             </td>
                         </tr>");
            $output .= ("<tr>
                            <td>".__('UPS Password', 'wpsc')." :</td>
                            <td>
                                <input type=\"password\" name='wpsc_ups_settings[upspassword]' value=\"".base64_decode($wpsc_ups_settings['upspassword'])."\" />
                            </td>
                        </tr>");
            $output .= ("<tr>
                            <td>".__('UPS XML API Key', 'wpsc')." :</td>
                            <td>
                                <input type=\"text\" name='wpsc_ups_settings[upsid]' value=\"".base64_decode($wpsc_ups_settings['upsid'])."\" />
                                <br />
                                ".__('Don\'t have an API login/ID ?', 'wpsc')."
                                    <a href=\"https://www.ups.com/upsdeveloperkit?loc=en_US\" target=\"_blank\">".__('Click Here','wpsc')."</a>.
                                        <br />
                                ".__('* For Negotiated rates, you must enter a UPS account number and select "Show UPS negotiated rates" ', 'wpsc')."
                            </td>
                        </tr>");
            // End new Code
            return $output;
    }

    function submit_form() {
            /* This function is called when the user hit "submit" in the
             * UPS settings area under Shipping to update the setttings.
             */
            $wpsc_ups_services = $_POST['wpsc_ups_services'];
            update_option('wpsc_ups_services',$wpsc_ups_services);
            if ($_POST['wpsc_ups_settings'] != '') {
                $temp = $_POST['wpsc_ups_settings'];
                // base64_encode the information so it isnt stored as plaintext.
                // base64 is by no means secure but without knowing if the server
                // has mcrypt installed what can you do really?
                $temp['upsusername'] = base64_encode($temp['upsusername']);
                $temp['upspassword'] = base64_encode($temp['upspassword']);
                $temp['upsid'] = base64_encode($temp['upsid']);

                update_option('wpsc_ups_settings', $temp);
            }
            return true;
    }

    function array2xml($data){
        $xml = "";
        if (is_array($data)){
            foreach($data as $key=>$value){
                //if(empty($value)){
                //    $xml .= "<".trim($key)." />\n";
                //}else{
                    $xml .= "<".trim($key).">\n";
                    $xml .= $this->array2xml($value);
                    $xml .= "</".trim($key).">\n";
               // }
            }
        }else if(is_bool($data)){
            if($data){$xml = "true\n";}
            else { $xml = "false\n"; }
        }else{
            $xml = trim($data)."\n";
        }
        return $xml;
    }


    private function _buildRateRequest($args){
        // Vars is an array
        // $RateRequest, $RatePackage, $RateCustomPackage, $RateRequestEnd
        // Are defined in ups_data.php that is included below if not
        // done so by instantiating class ... shouldnt ever need to
        if (!isset($this->MessageStart)){
            $this->_includeUPSData();
        }
        // Always start of with this, it includes the auth block//
        $REQUEST = "<?xml version=\"1.0\"?>\n
        <AccessRequest xml:lang=\"en-US\">\n";

        $access = array(
            "AccessLicenseNumber"=>base64_decode($args['api_id']),   // UPS API ID#
            "UserId" =>base64_decode($args['username']), // UPS API Username
            "Password" =>base64_decode($args['password'])  // UPS API Password
        );

        $REQUEST .= $this->array2xml($access);
        $REQUEST .= "</AccessRequest>\n";
        $REQUEST .= "<RatingServiceSelectionRequest xml:lang=\"en-US\">\n";

        // By Default we will shop. Shop when you do not have a service type
        // and you want to get a set of services and rates back!
        $RequestType = "Shop";
        // If service type is set we cannot shop so instead we Rate!
        if (isset($args["service"])){
            $RequestType = "Rate";
        }

        $RatingServiceRequest = array(
            "Request"=>array(
                "TransactionReference"=>array(
                    "CustomerContext"=>"Rate Request",
                    "XpciVersion"=>"1.0001"
                ),
                "RequestAction"=>"Rate",
                "RequestOption"=>$RequestType
            )
        );

        // Set up Shipment Node
        $Shipment = array();

        // Set the dropoff code
        $dropCode = (array_key_exists('DropoffType',$args)) ? $args['DropoffType'] : '01';
        $Shipment["PickupType"] = array(
                "Code"=>$dropCode
            );

        // Shipper Address (billing)
        $Shipment["Shipper"]=array(
            "Address"=>array(
                "StateProvinceCode"=>$args['shipr_state'],
                "PostalCode"=>$args['shipr_pcode'], // The shipper Postal Code
                "CountryCode"=>$args['shipr_ccode']
            ));

        // Negotiated Rates
        if (array_key_exists('negotiated_rates', $args) ){
            if ($args['negotiated_rates'] == '1' && !empty($args['account_number'])){
                $Shipment["Shipper"]["ShipperNumber"] = $args['account_number'];
            }
        }

        // If the city is configured use it
        if (array_key_exists('shipr_city', $args)){
            if (!empty($args['shipr_city'])){
                $Shipment["Shipper"]["Address"]["City"] = $args["shipr_city"];
            }
        }
        // The physical address the shipment is from (normally the same as billing)
        $Shipment["ShipFrom"]=array(
            "Address"=>array(
                "StateProvinceCode"=>$args['shipf_state'],
                "PostalCode"=>$args['shipf_pcode'], // The shipper Postal Code
                "CountryCode"=>$args['shipf_ccode']
            ));

        // If the city is configured use it
        if (array_key_exists('shipf_city', $args)){
            if (!empty($args['shipf_city'])){
                $Shipment["ShipFrom"]["Address"]["City"] = $args["shipf_city"];
            }
        }

        $Shipment["ShipTo"]= array(
            "Address"=>array(
                "StateProvinceCode"=>$args['dest_state'], // The Destination State
                "PostalCode"=>$args['dest_pcode'], // The Destination Postal Code
                "CountryCode"=>$args['dest_ccode'], // The Destination Country
                //"ResidentialAddress"=>"1"
            ));

        if ($args['residential'] == '1'){
            $Shipment["ShipTo"]["Address"]["ResidentialAddress"] = "1";
        }

        // If there is a specific service being requested then
        // we want to pass the service into the XML
        if (isset($args["service"])){
           $Shipment["Service"] = array("Code" =>$args['service']);
        }

        // Include this only if you want negotiated rates
        if (array_key_exists('negotiated_rates', $args) ){
            if ($args['negotiated_rates'] == "1"){
                $Shipment["RateInformation"]=array("NegotiatedRatesIndicator" => "");
            }
        }

        $Shipment["Package"] = array(
            "PackagingType"=>array("Code"=>$args['packaging']),
            "PackageWeight"=>array(
                "UnitOfMeasurement"=>array("Code"=>$args['units']),
                "Weight" => $args["weight"]
            )
        );

        // If the packaging type is "Your Own aka Package" then UPS needs
        // the dimensions of the box you are going to use to accurately rate
        /*
        if ($args['packaging'] == "02"){
            $Shipment["Package"]["Dimensions"] = array(
                "UnitOfMeasurement"=>$args['units'],
                "Length"=>$args['length'],
                "Width"=>$args['width'],
                "Height"=>$args['height'],
            );
        }*/


        // Set the structure for the Shipment Node
        $RatingServiceRequest["Shipment"] = $Shipment;

        $REQUEST .= $this->array2xml($RatingServiceRequest);
        $REQUEST .= "</RatingServiceSelectionRequest>";

        // Return the final XML document as a string to be used by _makeRateRequest
        //$file = fopen('request-enhanced.'.time().'.log', 'w');
        //fwrite($file, $REQUEST."\n");
        return $REQUEST;
    }

    private function _makeRateRequest($message){
        // Make the XML request to the server and retrieve the response
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$this->service_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    $message);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function futureDate($interval){
        //Wed Apr 7
        date_default_timezone_set('America/Los_Angeles');
        $timestamp = date('c');
        $hour = date("G");
        if ((int)$hour >= 3){
            $interval += 1;
        }

        $date = date("Y-m-d");
        $interval = " +$interval day";
        $final = date("D M j",strtotime(date("Y-m-d", strtotime($date)).$interval));
        $test = explode(" ",$final);

        if ($test[0] == "Sat"){
            return $this->futureDate($interval+2);
        }else if($test[0] == "Sun"){
            return $this->futureDate($interval+1);
        }
        return $final;
    }

    private function _parseQuote($raw){
        global $wpdb;

        $config = get_option('wpsc_ups_settings');
        $debug  = (array_key_exists('upsenvironment', $config)) ? $config['upsenvironment'] : "";

        $rate_table = array();
        $wpsc_ups_services = get_option("wpsc_ups_services");
        // Initialize a DOM using the XML passed in!
        $objDOM = new DOMDocument();
        if($raw != '') {
            $objDOM->loadXML($raw);

            // Get the <ResponseStatusCode> from the UPS XML
            $getStatusNode = $objDOM->getElementsByTagName("ResponseStatusCode");
            // Get the value of the error code, 1 == No Error, 0 == Error !!!
            $statusCode = $getStatusNode->item(0)->nodeValue;

            if ($statusCode == "0"){
                // Usually I dont leave debug stuff in but this is handy stuff!!
                // it will print out the error message returned by UPS!
                if ($debug == "1"){
                    $getErrorDescNode = $objDOM->getElementsByTagName("ErrorDescription");
                    $ErrorDesc = $getErrorDescNode->item(0)->nodeValue;
                    echo "<br />Error : ".$ErrorDesc."<br />";
                }
                return false;
            }else{
                $RateBlocks = $objDOM->getElementsByTagName("RatedShipment");
                foreach($RateBlocks as $rate_block){
                    // Get the <Service> Node from the XML chunk
                    $getServiceNode = $rate_block->getElementsByTagName("Service");
                    $serviceNode = $getServiceNode->item(0);

                    // Get the <Code> Node from the <Service> chunk
                    $getServiceCodeNode = $serviceNode->getElementsByTagName("Code");
                    // Get the value from <Code>
                    $serviceCode = $getServiceCodeNode->item(0)->nodeValue;
                    $go = true;
                    $price = "";
                    $time = "";

                    //if (array_key_exists('ups_negotiated_rates', $config)){
                    $getNegotiatedRateNode = $rate_block->getElementsByTagName("NegotiatedRates");
                    if ($getNegotiatedRateNode){
                        $negotiatedRateNode = $getNegotiatedRateNode->item(0);
                        if ($negotiatedRateNode){
                            $getNetSummaryNode = $negotiatedRateNode->getElementsByTagName("NetSummaryCharges");
                            $netSummaryNode = $getNetSummaryNode->item(0);

                            $getGrandTotalNode = $netSummaryNode->getElementsByTagName("GrandTotal");
                            $grandTotalNode = $getGrandTotalNode->item(0);

                            $getMonetaryNode = $grandTotalNode->getElementsByTagName("MonetaryValue");
                            $monetaryNode = $getMonetaryNode->item(0)->nodeValue;
                            if (!empty($monetaryNode)){
                                $go = false;
                                $price = $monetaryNode;
                            }
                        }
                    }

                    // Get the <TotalCharges> Node from the XML chunk
                    $getChargeNodes = $rate_block->getElementsByTagName("TotalCharges");
                    $chargeNode = $getChargeNodes->item(0);
/*
                    $getDeliveryNode = $rate_block->getElementsByTagName("GuaranteedDaysToDelivery");
                    $deliveryDays = $getDeliveryNode->item(0)->nodeValue;
                    if ($deliveryDays){
                        $time = $this->futureDate($deliveryDays);
                    }else{
                        $time = $this->futureDate(6);
                    }
*/
                    // Get the <CurrencyCode> from the <TotalCharge> chunk
                    $getCurrNode= $chargeNode->getElementsByTagName("CurrencyCode");
                    // Get the value of <CurrencyCode>
                    $currCode = $getCurrNode->item(0)->nodeValue;

                    if ($go == true){
                        // Get the <MonetaryValue> from the <TotalCharge> chunk
                        $getMonetaryNode= $chargeNode->getElementsByTagName("MonetaryValue");
                        // Get the value of <MonetaryValue>
                        $price = $getMonetaryNode->item(0)->nodeValue;
                    }
                    // If there are any services specified in the admin area
                    // this will check that list and pass on adding any services that
                    // are not explicitly defined.
                    if (!empty($wpsc_ups_services)){
                        if (is_array($wpsc_ups_services)){
                            if (array_search($serviceCode, $wpsc_ups_services) === false){
                                continue;
                            }
                        }else if ($wpsc_ups_services != $serviceCode){
                            continue;
                        }
                    }
                    if(array_key_exists($serviceCode,$this->Services)){
                        $rate_table[$this->Services[$serviceCode]] = array($currCode,$price);
                    }

                } // End foreach rated shipment block
            }
        }
        // Revers sort the rate selection so it is cheapest First!
        asort($rate_table);
        return $rate_table;
    }

    private function _formatTable($services, $currency=false){
        /* The checkout template expects the array to be in a certain
         * format. This function will iterate through the provided
         * services array and format it for use. During the loop
         * we take advantage of the loop and translate the currency
         * if necessary based off of what UPS tells us they are giving us
         * for currency and what is set for the main currency in the settings
         * area
         */
        $converter = null;
        if ($currency){
            $converter = new CURRENCYCONVERTER();
        }
        $finalTable = array();
        foreach(array_keys($services) as $service){
            if ($currency != false && $currency != $services[$service][0]){
                $temp =$services[$service][1];
                $services[$service][1] = $converter->convert($services[$service][1],
                                                             $currency,
                                                             $services[$service][0]);
            }
            $finalTable[$service] = $services[$service][1];
        }
        return $finalTable;
    }

    function getQuote(){
        global $wpdb;

        // Arguments array for various functions to use
        $args = array();
        // Final rate table
        $rate_table = array();
        // Get the ups settings from the ups account info page (Shipping tab)
        $wpsc_ups_settings = get_option("wpsc_ups_settings");
        // Get the wordpress shopping cart options
        $wpsc_options = get_option("wpsc_options");

        // API Auth settings //
        $args['username'] = (array_key_exists('upsaccount',$wpsc_ups_settings)) ? $wpsc_ups_settings['upsusername'] : "";
        $args['password'] = (array_key_exists('upspassword',$wpsc_ups_settings)) ? $wpsc_ups_settings['upspassword'] : "";
        $args['api_id']   = (array_key_exists('upsid',$wpsc_ups_settings)) ? $wpsc_ups_settings['upsid'] : "";
        $args['account_number'] = (array_key_exists('upsaccount',$wpsc_ups_settings)) ? $wpsc_ups_settings['upsaccount'] : "";
        $args['negotiated_rates'] = (array_key_exists('ups_negotiated_rates',$wpsc_ups_settings)) ? 
                                                    $wpsc_ups_settings['ups_negotiated_rates'] : "";
        $args['residential'] = $wpsc_ups_settings['49_residential'];

        // What kind of pickup service do you use ?
        $args['DropoffType'] = $wpsc_ups_settings['DropoffType'];
        $args['packaging'] = $wpsc_ups_settings['48_container'];
        // Preferred Currency to display
        $currency_data = $wpdb->get_row("SELECT `code`
                                         FROM `".WPSC_TABLE_CURRENCY_LIST."`
                                         WHERE `isocode`='".get_option("currency_type")."'
                                         LIMIT 1", ARRAY_A) ;
        if ($currency_data){
            $args['currency'] = $currency_data['code'];
        }else{
            $args['currency'] = "USD";
        }
        // Shipping billing / account address
        $origin_region_data = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`
                                WHERE `".WPSC_TABLE_REGION_TAX."`.`id` = '".get_option('base_region')."' ",ARRAY_A);
        $args['shipr_state']= (is_array($origin_region_data)) ? $origin_region_data[0]['code'] : "";
        $args['shipr_city'] = get_option('base_city');
        $args['shipr_ccode'] = get_option('base_country');
        $args['shipr_pcode'] = get_option('base_zipcode');
        // Physical Shipping address being shipped from
        $args['shipf_state'] = $args['shipr_state'];
        $args['shipf_city'] = $args['shipr_city'];
        $args['shipf_ccode'] = $args['shipr_ccode'];
        $args['shipf_pcode'] = $args['shipr_pcode'];
        // Get the total weight from the shopping cart
        $args['units'] = "LBS";
        $args['weight'] = wpsc_cart_weight_total();
        // Destination zip code
        $args['dest_ccode'] = $_SESSION['wpsc_delivery_country'];
        if ($args['dest_ccode'] == "UK"){
            // So, UPS is a little off the times
            $args['dest_ccode'] = "GB";
        }

        // If ths zip code is provided via a form post use it!
        if(isset($_POST['zipcode']) && ($_POST['zipcode'] != "Your Zipcode" && $_POST['zipcode'] != "YOURZIPCODE")) {
          $args['dest_pcode'] = $_POST['zipcode'];
          $_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
        } else if(isset($_SESSION['wpsc_zipcode']) && ($_POST['zipcode'] != "Your Zipcode" && $_POST['zipcode'] != "YOURZIPCODE")) {
          // Well, we have a zip code in the session and no new one provided
          $args['dest_pcode'] = $_SESSION['wpsc_zipcode'];
        }else{
            // We cannot get a quote without a zip code so might as well return!
            return array();
        }
        
        // If the region code is provided via a form post use it!
        if(isset($_POST['region']) && !empty($_POST['region'])) {
            $query ="SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`
                                WHERE `".WPSC_TABLE_REGION_TAX."`.`id` = '".$_POST['region']."'";
            $dest_region_data = $wpdb->get_results($query, ARRAY_A);      
            $args['dest_state'] = (is_array($dest_region_data)) ? $dest_region_data[0]['code'] : "";
            $_SESSION['wpsc_state'] = $args['dest_state'];
        } else if(isset($_SESSION['wpsc_state'])) {
            // Well, we have a zip code in the session and no new one provided
            $args['dest_state'] = $_SESSION['wpsc_state'];
        } else{
            $args['dest_state'] = "";
        }
        
        $shipping_cache_check['state'] = $args['dest_state'];
        $shipping_cache_check['zipcode'] = $args['dest_pcode'];
        $shipping_cache_check['weight'] = $args['weight'];
        
        // This is where shipping breaks out of UPS if weight is higher than 150 LBS
        if($weight > 150){
                unset($_SESSION['quote_shipping_method']);
                $shipping_quotes[TXT_WPSC_OVER_UPS_WEIGHT] = 0;
                $_SESSION['wpsc_shipping_cache_check']['weight'] = $args['weight'];
                $_SESSION['wpsc_shipping_cache'][$this->internal_name] = $shipping_quotes;
                $_SESSION['quote_shipping_method'] = $this->internal_name;
                return array($shipping_quotes);
        }
        
        // We do not want to spam UPS (and slow down our process) if we already
        // have a shipping quote!
        if(($_SESSION['wpsc_shipping_cache_check'] === $shipping_cache_check)
                && ($_SESSION['wpsc_shipping_cache'][$this->internal_name] != null)) {
            
            $rate_table = $_SESSION['wpsc_shipping_cache'][$this->internal_name];
            return $rate_table;
        }else{
            // Build the XML request
            $request = $this->_buildRateRequest($args);
            //$file = fopen('logs/ups.request.'.time().'.log', 'w');
            //fwrite($file, $request."\n");
            // Now that we have the message to send ... Send it!
            $raw_quote = $this->_makeRateRequest($request);
            // Now we have the UPS response .. unfortunately its not ready
            // to be viewed by normal humans ...
            $quotes = $this->_parseQuote($raw_quote);
//                $file = fopen('logs/ups.response.'.time().'.log', 'w');
//                fwrite($file, $raw_quote."\n");
            // If we actually have rates back from UPS we can use em!
            if ($quotes != false){
                $rate_table = $this->_formatTable($quotes,$args['currency']);
            }else{
                if ($wpsc_ups_settings['upsenvironment'] == '1'){
                    echo "<strong>:: GetQuote ::DEBUG OUTPUT::</strong><br />";
                    echo "Arguments sent to UPS";
                    print_r($args);
                    echo "Response from UPS";
                    echo $raw_quote;
                    echo "</strong>:: GetQuote ::End DEBUG OUTPUT::";
                }
            }
        }
        
        $_SESSION['wpsc_shipping_cache_check']['state'] = $args['dest_state'];
        $_SESSION['wpsc_shipping_cache_check']['zipcode'] = $args['dest_pcode'];
        $_SESSION['wpsc_shipping_cache_check']['weight'] = $args['weight'];
        $_SESSION['wpsc_shipping_cache'][$this->internal_name] = $rate_table;
        // return the final formatted array !
        return $rate_table;
    }

    // Empty Function, this exists just b/c it is prototyped elsewhere
    function get_item_shipping(){
    }
}
$ups = new ups();
$wpsc_shipping_modules[$ups->getInternalName()] = $ups;
?>
