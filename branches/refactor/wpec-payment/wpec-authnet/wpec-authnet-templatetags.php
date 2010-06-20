<?php

/**
 * wpec_authnet_has_access()
 *
 * Make sure user can perform special tasks
 *
 * @return bool $can_do
 */
function wpec_authnet_has_access() {

	if ( is_super_admin() )
		$has_access = true;
	else
		$has_access = false;

	return apply_filters( 'wpec_authnet_has_access', $has_access );
}

/**
 * wpec_authnet_setting()
 *
 * Outputs the requested setting
 *
 * @param string $setting
 */
function wpec_authnet_setting( $setting ) {
	echo wpec_authnet_get_setting( $setting );
}
	/**
	 * wpec_authnet_get_setting()
	 *
	 * Get a global authnet setting
	 *
	 * @global array $wpec
	 * @param string $setting Setting to get
	 * @return string
	 */
	function wpec_authnet_get_setting( $setting ) {
		global $wpec;

		return $wpec->authnet->settings[$setting];
	}

/**
 * wpec_authnet_set_setting()
 *
 * Sets a global authnet setting
 *
 * @global array $wpec
 * @param string $setting Setting to set
 * @param string $value  Value to assign
 */
function wpec_authnet_set_setting( $setting, $value ) {
	global $wpec;

	$wpec->authnet->settings[$setting] = $value;
}

/* Template tags */
function wpec_authnet_new( $args = '' ) {
	global $wpec;

	$defaults = array(
		// Default transaction info
			'login_id'				=> wpec_authnet_get_setting( 'login_id' ),
			'transaction_key'		=> wpec_authnet_get_setting( 'transaction_key' ),
			'amount'				=> $_POST['wpec_authnet_amount'],
			'description'			=> $_POST['wpec_authnet_description'],
			'url'					=> 'https://secure.authorize.net/gateway/transact.dll',
			'integration_method'	=> wpec_authnet_get_setting( 'integration_method' ),

		// Advanced integration method params
			'version'				=> '3.1',
			'delim_data'			=> 'TRUE',
			'delim_char'			=> '|',
			'relay_response'		=> 'FALSE',

			// Default card info
			'type'					=> 'AUTH_CAPTURE',
			'method'				=> 'CC',

		// Simple integration method params
			'invoice'				=> date( 'YmdHis' ),
			'sequence'				=> rand( 1, 1000 ),
			'timestamp'				=> time(),
			'fingerprint'			=> '',
			'testmode'				=> 'false',

	);

	// Go Voltron
	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( 'aim' == $integration_method )
		$wpec->authnet->method = new WPEC_Authnet_AIM( $r );
	else
		$wpec->authnet->method = new WPEC_Authnet_SIM( $r );
}

function wpec_authnet_seal() { ?>
		<script type="text/javascript" language="javascript">
			var ANS_customer_id = "<?php wpec_authnet_setting( 'customer_id' ); ?>";
		</script>
		<script type="text/javascript" language="javascript" src="https://verify.authorize.net/anetseal/seal.js" ></script>
<?php
}


function wpec_authnet_login_id() {
	echo wpec_authnet_get_login_id();
}
	function wpec_authnet_get_login_id() {
		global $wpec;
		return $wpec->authnet->settings['login_id'];
	}

function wpec_authnet_transaction_key() {
	echo wpec_authnet_get_transaction_key();
}
	function wpec_authnet_get_transaction_key() {
		global $wpec;
		return $wpec->authnet->settings['transaction_key'];
	}

function wpec_authnet_amount() {
	echo wpec_authnet_get_amount();
}
	function wpec_authnet_get_amount() {
		global $wpec;
		return apply_filters( 'wpec_authnet_get_amount', $wpec->authnet->method->amount );
	}

function wpec_authnet_description() {
	echo wpec_authnet_get_description();
}
	function wpec_authnet_get_description() {
		global $wpec;
		return apply_filters( 'wpec_authnet_get_description', $wpec->authnet->method->description );
	}

function wpec_authnet_url() {
	echo wpec_authnet_get_url();
}
	function wpec_authnet_get_url() {
		global $wpec;
		return apply_filters( 'wpec_authnet_get_url', $wpec->authnet->method->url );
	}

function wpec_authnet_invoice() {
	echo wpec_authnet_get_invoice();
}
	function wpec_authnet_get_invoice() {
		global $wpec;
		return apply_filters( 'wpec_authnet_get_invoice', $wpec->authnet->method->invoice );
	}

function wpec_authnet_sequence() {
	echo wpec_authnet_get_sequence();
}
	function wpec_authnet_get_sequence() {
		global $wpec;
		return apply_filters( 'wpec_authnet_get_sequence', $wpec->authnet->method->sequence );
	}

function wpec_authnet_timestamp() {
	echo wpec_authnet_get_timestamp();
}
	function wpec_authnet_get_timestamp() {
		global $wpec;
		return apply_filters( 'wpec_authnet_get_timestamp', $wpec->authnet->method->timestamp );
	}

function wpec_authnet_fingerprint() {
	echo wpec_authnet_get_fingerprint();
}
	function wpec_authnet_get_fingerprint() {
		global $wpec;
		return apply_filters( 'wpec_authnet_get_fingerprint', $wpec->authnet->method->fingerprint );
	}

function wpec_authnet_testmode() {
	echo wpec_authnet_get_testmode();
}
	function wpec_authnet_get_testmode() {
		global $wpec;
		return apply_filters( 'wpec_authnet_get_testmode', $wpec->authnet->method->testmode );
	}

/**
 * wpec_authnet_response()
 *
 * Populate response into global and return it
 *
 * @return object
 */
function wpec_authnet_response() {
	return wpec_authnet_get_response();
}
	function wpec_authnet_get_response() {
		global $wpec;

		$wpec->authnet->response = $wpec->authnet->method->response();

		return apply_filters( 'wpec_authnet_get_response', $wpec->authnet->response );
	}

/**
 * wpec_authnet_response_status()
 *
 *
 */
function wpec_authnet_response_status() {
	echo wpec_authnet_get_response_status();
}
	/**
	 * wpec_authnet_get_response_status()()
	 *
	 * Return status response from authorize.net
	 *
	 * @global array $wpec
	 * @return string
	 */
	function wpec_authnet_get_response_status() {
		global $wpec;
		return apply_filters( 'wpec_authnet_get_response_status', $wpec->authnet->response[WPEC_AUTHNET_RESPONSE_STATUS] );
	}

/**
 * wpec_authnet_response_message()
 *
 * Echo wpec_authnet_get_response_message()
 */
function wpec_authnet_response_message() {
	echo wpec_authnet_get_response_message();
}
	/**
	 * wpec_authnet_get_response_message()()
	 *
	 * Return response message from authorize.net
	 *
	 * @global array $wpec
	 * @return string
	 */
	function wpec_authnet_get_response_message() {
		global $wpec;
		return apply_filters( 'wpec_authnet_get_response_message', $wpec->authnet->response[WPEC_AUTHNET_RESPONSE_MESSAGE] );
	}

/**
 * wpec_authnet_response_total()
 *
 * Echo wpec_authnet_get_response_total()
 */
function wpec_authnet_response_total() {
	echo wpec_authnet_get_response_total();
}
	/**
	 * wpec_authnet_get_response_total()()
	 *
	 * Return response total from authorize.net
	 *
	 * @global array $wpec
	 * @return string
	 */
	function wpec_authnet_get_response_total() {
		global $wpec;
		return apply_filters( 'wpec_authnet_get_response_total', $wpec->authnet->response[WPEC_AUTHNET_RESPONSE_TOTAL] );
	}

?>
