<?php

/**
 * WPEC_Authnet_Loader
 *
 * Loads plugin
 */
class WPEC_Authnet_Loader {
	/**
	 * constants()
	 *
	 * Default component constants that can be overridden or filtered
	 */
	function constants() {

		// Default slug for component
		if ( !defined( 'WPEC_AUTHNET_SLUG' ) )
			define( 'WPEC_AUTHNET_SLUG', apply_filters( 'wpec_authnet_slug', 'authnet' ) );

		// Response codes
		define( 'WPEC_AUTHNET_STATUS_SUCCESS', 1 );
		define( 'WPEC_AUTHNET_STATUS_DECLINE', 2 );
		define( 'WPEC_AUTHNET_STATUS_REFERRAL', 3 );
		define( 'WPEC_AUTHNET_STATUS_KEEPCARD', 4 );

		// More response codes
		define( 'WPEC_AUTHNET_RESPONSE_STATUS', 0 );
		define( 'WPEC_AUTHNET_RESPONSE_MESSAGE', 3 );
		define( 'WPEC_AUTHNET_RESPONSE_TOTAL', 9 );

		// Debug class (sends inquiries to authorize.net test server)
		//define( 'WPEC_AUTHNET_DEBUG', true );

	}

	/**
	 * includes()
	 *
	 * Load required files
	 *
	 * @uses is_admin If in WordPress admin, load additional file
	 */
	function includes() {
		// Load the files
		require_once( WP_PLUGIN_DIR . '/wp-e-commerce/wpec-payment/wpec-authnet/wpec-authnet-classes.php' );
		require_once( WP_PLUGIN_DIR . '/wp-e-commerce/wpec-payment/wpec-authnet/wpec-authnet-templatetags.php' );

		// Quick admin check
		if ( is_admin() )
			require_once( WP_PLUGIN_DIR . '/wp-e-commerce/wpec-payment/wpec-authnet/wpec-authnet-admin.php' );
	}

	/**
	 * init()
	 *
	 * Initialize plugin
	 *
	 * @uses WPEC_Authnet_Loader::constants()
	 * @uses WPEC_Authnet_Loader::includes()
	 * @uses WPEC_Authnet::init()
	 * @uses WPEC_Authnet_User::init()
	 * @uses WPEC_Authnet_Admin::init()
	 * @uses is_admin()
	 * @uses do_action Calls custom action to allow external enhancement
	 */
	function init() {

		// Define all the constants
		WPEC_Authnet_Loader::constants();

		// Include required files
		WPEC_Authnet_Loader::includes();

		// Initialize site action hooks
		WPEC_Authnet::init();

		// Initialize user action hooks
		//WPEC_Authnet_User::init();

		// Admin initialize
		if ( is_admin() )
			WPEC_Authnet_Admin::init();

		/**
		 * For developers:
		 * ---------------------
		 * If you want to make sure your code is loaded after this plugin
		 * have your code load on this action
		 */
		do_action ( 'wpec_authnet_init' );
	}
}

// Do the ditty
if ( defined( 'WPEC_VERSION' ) )
	WPEC_Authnet_Loader::init();
else
	add_action( 'wpec_init', array( 'WPEC_Authnet_Loader', 'init' ) );

?>
