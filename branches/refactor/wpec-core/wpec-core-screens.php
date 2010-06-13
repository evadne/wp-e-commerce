<?php

/**
 * wpec_screens()
 *
 * Remap template files if WP e-Commerce is active.
 * Call this only when WPEC is an active plugin.
 *
 * @global array $wpec
 */
function wpec_screens() {
	global $wpec;

	if ( defined( 'WPEC_VERSION' ) )
		$wpec->screens = new WPEC_Screens;
}
add_action( 'init', 'wpec_screens' );

/**
 * This file is responsible for defining the locations that
 * WP e-Commerce wants to look for to find template files.
 *
 * By default this is set as a globally available array of key/value pairs.
 *
 * Example:
 *
 *	$wpec->screens->filters = array(
 *		'wpec_products' => '/products/products',
 *		'wpec_products_loop' => '/products/products-loop'
 *	);
 */
class WPEC_Screens {

	// Contains filters and template locations
	var $filters = array();

	/**
	 * wpec_screens()
	 *
	 * Called when class is created
	 */
	function wpec_screens( $no_init = false ) {
		do_action( 'wpec_screens' );

		// Create class without initializing
		if ( true == $no_init )
			$this->init();
	}

	/**
	 * init()
	 *
	 * Initializes the screens class
	 */
	function init() {
		// Hook default template filters
		$this->default_filters();

		// Add any additional filters (from modules)
		$this->add_filters();

		do_action( 'wpec_screens_init' );
	}

	/**
	 * add_screen()
	 *
	 * Adds a filter/file to the screens array
	 *
	 * @param array $filters
	 * @return bool True if successful, false if not
	 */
	function add_screen( $filters ) {
		$filters = array( $filters );

		if ( $this->filters = array_merge( $this->filters, $filters ) )
			return true;
		else
			return false;
	}

	/**
	 * update_screen()
	 *
	 * Updates an existing filter to a new file
	 *
	 * @param string $filter Name of filter
	 * @param string $file Location of template file
	 * @return bool True if successful, false if not
	 */
	function update_screen( $filter, $file ) {
		if ( !array_key_exists( $this->filters[$filter] ) )
			return false;

		$this->filters[$filter] = $file;

		return true;
	}

	/**
	 * remove_screen()
	 *
	 * Removes a filter/file from the screens array
	 *
	 * @param string $filter The filter to remove
	 */
	function remove_screen( $filter ) {
		unset( $this->filters[$filter] );
	}

	/**
	 * default_filters()
	 *
	 * Creates the default filters array, which is the key/value pair that tells
	 * WPEC which filters to look for when loading template files.
	 *
	 * These will most likely be added dynamically later with code as new
	 * product types, products, variations, and modules are created and turned on.
	 *
	 * Modules such as shopping cart, checkout, payment, and shipping will want
	 * to assign their own template files and screen filters to use.
	 */
	function default_filters() {
		$this->filters = array(

			// Product Loops
			'wpec_products_index' =>	'products/index',
			'wpec_products_loop' =>		'products/products-loop',

			// Individual Product Pages
			'wpec_products_single_index' =>	'products/single/index',
			'wpec_products_single_loop' =>	'products/single/products-single-loop',

		);

		do_action( 'wpec_screens_create_filters' );
	}

	/**
	 * add_filters()
	 *
	 * Adds filters for all screens and templates.
	 */
	function add_filters() {
		foreach ( $this->filters as $filter => $file ) {
			add_filter( $filter, array( 'WPEC_Screens', 'template' ) );
		}
	}

	/**
	 * template()
	 *
	 * Returns the template location.
	 *
	 * @param string $screen The screen/filter to look for
	 * @uses current_filter() Gets the current WordPress filter
	 * @return string
	 */
	function template() {
		global $wpec;

		// What is the current WP filter?
		$screen = current_filter();

		if ( isset( $wpec->screens->filters[$screen] ) )
			return $wpec->screens->filters[$screen];
		else
			return false;
	}
}

/**
 * wpec_screens_add()
 *
 * Add your components theme files to the internal array
 *
 * @global array $wpec
 * @param array $screens filter => template file
 */
function wpec_screens_add( $screens ) {
	global $wpec;
	$wpec->screens->add_screen( $screens );
}

/**
 * wpec_screens_update()
 *
 * Update an existing filter/file in the screens array
 *
 * @global array $wpec
 * @param string $filter Existing filter/screen in array
 * @param string $file New template file location
 */
function wpec_screens_update( $filter, $file ) {
	global $wpec;
	$wpec->screens->update_screen( $filter, $file );
}

/**
 * wpec_screens_remove()
 *
 * Used to totally unset a theme file from the array. This will also
 * totally remove any previous access to this theme file from the site.
 *
 * @global array $wpec
 * @param string $filter Name of filter/screen to remove
 */
function wpec_screens_remove( $filter ) {
	global $wpec;
	$wpec->screens->remove_screen( $filter );
}

/**
 * wpec_screens_template()
 *
 * Returns a template file from the provided filter/screen. If nothing is
 * passed, the current_filter() is used.
 *
 * @global array $wpec
 * @param string $filter Name of filter/screen to look for
 * @return mixed Template file if exists, or else false
 */
function wpec_screens_template( $filter = null ) {
	global $wpec;
	return $wpec->screens->template( $filter );
}

?>