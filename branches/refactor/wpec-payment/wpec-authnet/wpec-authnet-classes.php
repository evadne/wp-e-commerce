<?php

class WPEC_Authnet {

	function init() {
		// Add root component
		add_action( 'wpec_setup_root_components', array( 'WPEC_Authnet', 'add_root_component' ) );

		// Setup globals
		add_action( 'wpec_setup_globals', array( 'WPEC_Authnet', 'setup_globals' ) );

		// wp_head
		add_action( 'wp_head', array( 'WPEC_Authnet', 'wp_head' ) );
	}

	/**
	 * activation()
	 *
	 * Placeholder for plugin activation sequence
	 */
	function activation() {	}

	/**
	 * deactivation()
	 *
	 * Placeholder for plugin deactivation sequence
	 */
	function deactivation() { }

	/**
	 * add_root_component()
	 *
	 * Placeholder for BuddyPress plugin root component
	 */
	function add_root_component() { }

	/**
	 * setup_globals()
	 *
	 * Setup all plugin globals
	 *
	 * @global array $wpec
	 * @global object $wpdb
	 */
	function setup_globals() {
		global $wpec, $wpdb;

		// For internal identification
		$wpec->authnet->id			= 'authnet';
		$wpec->authnet->slug		= WPEC_AUTHNET_SLUG;
		$wpec->authnet->settings	= WPEC_Authnet::settings();

		// Register this in the active components array
		$wpec->active_components[$wpec->authnet->slug] = $wpec->authnet->id;

		do_action( 'wpec_authnet_setup_globals' );
	}

	/**
	 * settings()
	 *
	 * Loads up any saved settings and filters each default value
	 *
	 * @return array
	 */
	function settings() {
		$settings = get_site_option( 'wpec_authnet_settings', false );

		// Set default values and allow them to be filtered
		$defaults = array(
			'customer_id'		=> apply_filters( 'wpec_authnet_customer_id', 'test' ),
			'login_id'			=> apply_filters( 'wpec_authnet_login_id', 'test' ),
			'transaction_key'	=> apply_filters( 'wpec_authnet_transaction_key', 'test' ),
		);

		// Allow settings array to be filtered and return
		return apply_filters( 'wpec_authnet_settings', wp_parse_args( $settings, $defaults ) );
	}

	/**
	 * wp_head()
	 *
	 * Hooks into wp_head()
	 *
	 * @return Only return if no data to display
	 */
	function wp_head() {

		// Load up the JS
		wp_enqueue_script( 'jquery' );
	}
}
register_activation_hook( __FILE__, array( 'WPEC_Authnet', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'WPEC_Authnet', 'deactivation' ) );

/**
 * Authorize.net Simple Integration Method
 *
 * This is the simplest of methods to communicate with Authorize.net.
 */
class WPEC_Authnet_SIM {
	// Amount of transaction
	var $amount;

	// Description of transaction
	var $description;

	// Login for merchant
	var $login_id;

	// Merchant additional key
	var $transaction_key;

	// Url to submit form to
	// Be sure to switch from dev to real environment!
	var $url;

	// Invoice number
	var $invoice;

	// Random sequence number
	var $sequence;

	// Timestamp for transaction
	var $timestamp;

	// Unique fingerprint for additional transaction safety
	var $fingerprint;

	// Dev or real environment?
	var $testmode;

	function wpec_authnet_sim( $args = '' ) {

		// Check for debug
		if ( !defined( 'WPEC_AUTHNET_DEBUG' ) )
			$url = 'https://secure.authorize.net/gateway/transact.dll';
		else
			$url = 'https://test.authorize.net/gateway/transact.dll';

		$defaults = array(
			'login_id'			=> wpec_authnet_get_setting( 'login_id' ),
			'transaction_key'	=> wpec_authnet_get_setting( 'transaction_key' ),
			'amount'			=> $_POST['wpec_authnet_amount'],
			'description'		=> $_POST['wpec_authnet_description'],
			'url'				=> $url,
			'invoice'			=> date( YmdHis ),
			'sequence'			=> rand( 1, 1000 ),
			'timestamp'			=> time(),
			'fingerprint'		=> $this->make_fingerprint(),
			'testmode'			=> 'false',
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		$this->login_id			= $login_id;
		$this->transaction_key	= $transaction_key;
		$this->amount			= $amount;
		$this->description		= $description;
		$this->url				= $url;
		$this->invoice			= $invoice;
		$this->sequence			= $sequence;
		$this->timestamp		= $timestamp;
		$this->fingerprint		= $fingerprint;
		$this->testmode			= $testmode;
	}

	/**
	 * make_fingerprint()
	 *
	 * Generate the fingerprint.  PHP versions 5.1.2 and
	 * newer have the necessary hmac function built in.
	 * For older versions, it will try to use the mhash library.
	 *
	 * @return <type>
	 */
	function make_fingerprint() {
		if ( phpversion() >= '5.1.2' )
			return hash_hmac( "md5", $this->login_id . "^" . $this->sequence . "^" . $this->timestamp . "^" . $this->amount . "^", $this->transaction_key );
		else
			return bin2hex( mhash( MHASH_MD5, $this->login_id . "^" . $this->sequence . "^" . $this->timestamp . "^" . $this->amount . "^", $this->transaction_key ) );
	}
}

/* Setup class */
class WPEC_Authnet_AIM {
	// Amount of transaction
	var $amount;

	// Description of transaction
	var $description;

	// Login for merchant
	var $login_id;

	// Merchant additional key
	var $transaction_key;

	// What version of authorize.net AIM are we using?
	var $version;

	// Is data delimited?
	var $delim_data;

	// Data delimiter char
	var $delim_char;

	// Response being relayed
	var $relay_response;

	// Type of transaction
	var $type;

	// Type of payment
	var $method;

	// Credit card number
	var $card_num;

	// Credit card number
	var $exp_date;

	// User first name
	var $first_name;

	// User last name
	var $last_name;

	// User address
	var $address;

	// User state
	var $state;

	// User zip
	var $zip;

	// Url to submit form to
	var $url;

	// String created from vars used to post to authorize.net
	var $post_string;

	function wpec_authnet_aim( $args = '' ) {

		// Check for debug
		if ( !defined( 'WPEC_AUTHNET_DEBUG' ) )
			$url = 'https://secure.authorize.net/gateway/transact.dll';
		else
			$url = 'https://test.authorize.net/gateway/transact.dll';

		$defaults = array(
			// Default transaction info
			'login_id'			=> wpec_authnet_get_setting( 'login_id' ),
			'transaction_key'	=> wpec_authnet_get_setting( 'transaction_key' ),
			'amount'			=> $_POST['wpec_authnet_amount'],
			'description'		=> $_POST['wpec_authnet_description'],
			'url'				=> 'https://secure.authorize.net/gateway/transact.dll',

			'version'			=> '3.1',
			'delim_data'		=> 'TRUE',
			'delim_char'		=> '|',
			'relay_response'	=> 'FALSE',

			// Default card info
			'type'				=> 'AUTH_CAPTURE',
			'method'			=> 'CC',
		);

		// Go Voltron
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		$this->user_meta( $args );

		// Transaction info
		$this->login_id			= $login_id;
		$this->transaction_key	= $transaction_key;
		$this->amount			= $amount;
		$this->description		= $description;
		$this->url				= $url;

		$this->version			= $version;
		$this->delim_data		= $delim_data;
		$this->delim_char		= $delim_char;
		$this->relay_response	= $relay_response;

		// Card info
		$this->type				= $type;
		$this->method			= $method;

		// Post string to communicate with authorize.net
		$this->post_string		= $this->build_uri();
	}

	/**
	 * user_meta()
	 *
	 * This function assumes that payment information is saved in user_meta.
	 * This, is obviously not the best thing to do, so most situations will
	 * want to pass their own $args to get user payment info.
	 *
	 * External functions are more than welcome to swoop in and filter this
	 * function to stash payment data someplace considered more safe.
	 *
	 * @global array $wpec
	 * @param array $args
	 * @return 
	 */
	function user_meta( $args = '' ) {
		global $wpec;

		// Assign default settings
		$user_meta['user_id']			= $args['user_id'];
		$user_meta['exp_date_month']	= get_user_meta( $user_meta['user_id'], 'payment_exp_month' );
		$user_meta['exp_date_year']		= get_user_meta( $user_meta['user_id'], 'payment_exp_year' );
		$user_meta['exp_date']			= substr( $user_meta['exp_date_month'], 0, 2 ) . substr( $user_meta['exp_date_year'], 2, 2 );
		$user_meta['card_num']			= get_user_meta( $user_meta['user_id'], 'payment_cc' );
		$user_meta['first_name']		= get_user_meta( $user_meta['user_id'], 'payment_first' );
		$user_meta['last_name']			= get_user_meta( $user_meta['user_id'], 'payment_last' );
		$user_meta['address']			= get_user_meta( $user_meta['user_id'], 'payment_address' );
		$user_meta['state']				= get_user_meta( $user_meta['user_id'], 'payment_state' );
		$user_meta['zip']				= get_user_meta( $user_meta['user_id'], 'payment_zip' );

		$defaults = array(
			'user_id'			=> $user_id,
			'exp_date_month'	=> $user_meta['exp_date_month'],
			'exp_date_year'		=> $user_meta['exp_date_year'],
			'exp_date'			=> $user_meta['exp_date'],
			'card_num'			=> $user_meta['card_num'],
			'first_name'		=> $user_meta['first_name'],
			'last_name'			=> $user_meta['last_name'],
			'address'			=> $user_meta['address'],
			'state'				=> $user_meta['state'],
			'zip'				=> $user_meta['zip'],
		);

		$r = wp_parse_args( $args, $defaults );
		apply_filters( 'wpec_authnet_user_meta', $r );

		extract( $r, EXTR_SKIP );

		// User info
		$this->card_num			= $card_num ? $card_num : $user_meta['card_num'];
		$this->exp_date			= $exp_date ? $exp_date : $user_meta['exp_date'];
		$this->first_name		= $first_name ? $first_name : $user_meta['first_name'];
		$this->last_name		= $last_name ? $last_name : $user_meta['last_name'];
		$this->address			= $address ? $address : $user_meta['address'];
		$this->state			= $state ? $state : $user_meta['state'];
		$this->zip				= $zip ? $zip : $user_meta['zip'];
	}

	function is_user_valid() {
		if (	$this->first_name	== '' ||
				$this->last_name	== '' ||
				$this->address		== '' ||
				$this->state		== '' ||
				$this->zip			== '' ||
				$this->card_num		== '' ||
				$this->exp_date		== '' )
			return false;

		return true;
	}

	function build_uri() {
		$post_values = array(

			// the API Login ID and Transaction Key must be replaced with valid values
			"x_login"			=> $this->login_id,
			"x_tran_key"		=> $this->transaction_key,

			"x_version"			=> $this->version,
			"x_delim_data"		=> $this->delim_data,
			"x_delim_char"		=> $this->delim_char,
			"x_relay_response"	=> $this->relay_response,

			"x_type"			=> $this->type,
			"x_method"			=> $this->method,
			"x_card_num"		=> $this->card_num,
			"x_exp_date"		=> $this->exp_date,

			"x_amount"			=> $this->amount,
			"x_description"		=> $this->description,

			"x_first_name"		=> $this->first_name,
			"x_last_name"		=> $this->last_name,
			"x_address"			=> $this->address,
			"x_state"			=> $this->state,
			"x_zip"				=> $this->zip,
		);

		// Loop through each and build post string
		foreach( $post_values as $key => $value )
			$post_string .= "$key=" . urlencode( $value ) . "&";

		// Trim off trailing &
		$post_string = rtrim( $post_string, "& " );

		return $post_string;
	}

	// This sample code uses the CURL library for php to establish a connection,
	// submit the post, and record the response.
	// If you receive an error, you may want to ensure that you have the curl
	// library enabled in your php configuration
	function response() {

		// initiate curl object
		$request = curl_init( $this->url );

		// set to 0 to eliminate header info from response
		curl_setopt( $request, CURLOPT_HEADER, 0 );

		// Returns response data instead of TRUE(1)
		curl_setopt( $request, CURLOPT_RETURNTRANSFER, 1 );

		// use HTTP POST to send form data
		curl_setopt( $request, CURLOPT_POSTFIELDS, $this->post_string );

		// uncomment this line if you get no gateway response.
		curl_setopt( $request, CURLOPT_SSL_VERIFYPEER, FALSE );

		// execute curl post and store results in $post_response
		$post_response = curl_exec( $request );

		// additional options may be required depending upon your server configuration
		// you can find documentation on curl options at http://www.php.net/curl_setopt
		curl_close ( $request ); // close curl object

		// This line takes the response and breaks it into an array using the specified delimiting character
		$response_array = explode( $this->delim_char, $post_response );

		// We're done, return response
		return $response_array;
	}
}

?>
