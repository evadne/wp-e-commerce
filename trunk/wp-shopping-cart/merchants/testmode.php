<?php
$nzshpcrt_gateways[$num]['name'] = 'Manual Payment';
$nzshpcrt_gateways[$num]['internalname'] = 'testmode';
$nzshpcrt_gateways[$num]['function'] = 'gateway_testmode';
$nzshpcrt_gateways[$num]['form'] = "form_testmode";
$nzshpcrt_gateways[$num]['submit_function'] = "submit_testmode";

function gateway_testmode($seperator, $sessionid) {
  $transact_url = get_option('transact_url');
  // exit("Location: ".$transact_url.$seperator."sessionid=".$sessionid);
  //$_SESSION['nzshpcrt_cart'] = null;
  //$_SESSION['nzshpcrt_serialized_cart'] = null;
  header("Location: ".$transact_url.$seperator."sessionid=".$sessionid);
  exit();
}

function submit_testmode() {
  return true;
}

function form_testmode() {
  return '';
}
?>
