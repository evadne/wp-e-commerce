<?php

/* Make sure slug is healthy */
function wpec_component_sanitize_slug( $slug ) {
	return $slug;
}

/* Check table_name */
function wpec_component_check_table_name( $table_name ) {
	return $table_name;
}

/* Standard component class for all WP e-Commerce components
 *
 * It includes all of the necessary functions and variables to
 * create, initialize, and control all WP e-Commerce components.
 *
 * To be used by core and external plugins
 *
 * @since Refactor
 *
 */
class WPEC_Component {
	/* Set a unique ID for internal reference
	 *	- Will be checked for duplicates
	 */
	var $id;

	/* Unique slug to identify component in URI's
	 *	- Will be checked for duplicates
	 */
	var $slug;

	/* For updating the DB schema */
	var $version;

	/* Set the table name where component data is saved
	 *	- Table will be created/updated by this class
	 */
	var $table_name;

	/* Set object type
	 *	- user/group/blog/custom type
	 *  - @uses wpec_get_registered_objects
	 */
	var $object;

	/* Set if component is on or off
	 *	- Defaults to off
	 */
	var $active;

	/* Best to set this as sanitized component name
	 *	- @uses sanitize_title
	 */
	var $css_class;

	/*
	 * For translations
	 *  - @uses $this->load_textdomain()
	 */
	var $text_domain;

	/* Initialize new component */
	function wpec_component( $args ) {
		$this->setup_globals( $args );
	}

	/**
	 * setup_globals()
	 *
	 * Used initially to setup the component
	 *
	 * @uses this->init Runs the initialization action
	 * @uses this->check_conflict Makes sure no components occupy the space this component wants to take
	 * @uses do_action
	 * @uses wp_parse_args
	 * @global array $wpec
	 * @param array $args
	 */
	function setup_globals( $args ) {
		global $wpec;

		// Allow for pre component initialization
		$this->init();

		// Set default args
		$defaults = array (
			'id' => 'wpec_component',
			'slug' => 'wpec-component',
			'version' => WPEC_CORE_DB_VERSION,
			'table_name' => '',
			'object' => 'user',
			'active' => false,
			'css_class' => 'wpec-component',
			'text_domain' => 'wpec-component'
		);

		// Parse args and extract
		$params = wp_parse_args( $args, $defaults );
		extract( $params, EXTR_SKIP );

		// Assign arguments to class values
		$this->id				= $id;
		$this->slug				= $slug;
		$this->version			= $version;
		$this->table_name		= $wpdb->base_prefix . $table_name;
		$this->object			= $object;
		$this->active			= $active;
		$this->css_class		= $css_class;

		// Check existing components for conflicting values
		if ( $error = $this->check_conflict() ) {
			// Create new error response
			$errors = new WP_Error( printf( __( 'Duplicate WP e-Commerce Component Found! %s', 'wpec-component' ), $error ) );

			// Return error object without running do_action since function
			// was not able to complete without errors.
			return $errors;

		// Register this in the active components array
		} else {
			if ( $this->active ) {
				$wpec->active_components[$this->id] = $this;
			}
		}

		// Top off with the appropriate action
		do_action ( $this->id . '_setup_globals');
	}

	/**
	 * init()
	 *
	 * Run after construct but before setup_globals
	 *
	 * @uses do_action To run the (id)_init action
	 */
	function init() {
		do_action( $this->id . '_init' );
	}

	/**
	 * before_install()
	 *
	 * Run before db schema installation
	 *
	 * @uses do_action To run the (id)_before_install action
	 */
	function before_install() {
		do_action( $this->id . '_before_install' );
	}

	/**
	 * after_install()
	 *
	 * Run after db schema installation
	 *
	 * @uses do_action To call the (id)_after_install action
	 */
	function after_install() {
		do_action( $this->id . '_after_install' );
	}

	/**
	 * check_installed()
	 *
	 * Check if db schema needs to be installed/upgraded
	 *
	 * @uses this->install Runs the installer
	 * @uses get_site_option To set the database scheme version
	 * @global object $wpdb
	 * @global array $wpec
	 */
	function check_installed() {
		global $wpdb, $wpec;

		if ( get_site_option( $this->id . '-db-version') < $this->version )
			$this->install();
	}

	/**
	 * install()
	 *
	 * Run the installation if needed
	 *
	 * @uses update_site_option To set database version
	 * @uses do_action To run (id)_installed action
	 * @uses this->before_install Runs the before install action
	 * @uses this->after_install Runs the after install action
	 * @global object $wpdb
	 * @param string $sql
	 */
	function install( $sql ) {
		global $wpdb;

		// Allow components to perform actions before install
		$this->before_install();

		// Set the charset is one is defined
		if ( !empty( $wpdb->charset ) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

		// Include the WordPress core upgrade file to handle the dirty work
		require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );
		dbDelta( $sql );

		// Allow components to perform action after install
		$this->after_install();

		// Set component version in site meta
		update_site_option( $this->id . '-db-version', $this->version );

		// Top off with the appropriate action
		do_action( $this->id . '_installed' );
	}

	/**
	 * setup_nav()
	 *
	 * Responsible for running the component setup_navigation action
	 *
	 * @uses do_action To run(id)_setup_nav action
	 */
	function setup_nav() {
		do_action( $this->id . '_setup_nav' );
	}

	/**
	 * admin_menu()
	 *
	 * Calls the component action to add navigation to the WordPress admin area
	 *
	 * @uses is_site_admin Check if the current user is a super admin
	 * @global object $wpdb
	 * @global array $wpec
	 */
	function admin_menu() {

		// Quick permission check
		// @todo Remove this and setup with proper permissions function
		if ( !is_site_admin() )
			return false;

		do_action( $this->id . '_admin_menu' );
	}

	/**
	 * screen()
	 *
	 * Template files are stored like $template/$slug/$screen
	 *
	 * Examples:	/wpec-default/members/index
	 *				/wpec-default/members/my-friends
	 *
	 *				/wpec-default/groups/index
	 *				/wpec-default/groups/my-groups
	 *
	 *				/wpec-default/blogs/index
	 *				/wpec-default/blogs/create
	 *
	 *				/wpec-default/activity/index
	 *
	 * @uses wpec_core_load_template Loads the appropriate template file
	 * @uses do_action Calls (id)_screen_$screen action
	 * @param string $screen
	 * @param bool $single
	 */
	function screen( $screen, $single = false ) {

		// Call the action
		do_action( $this->id . '_screen_' . $screen );

		// If item is single, adjust template directory
		$is_single = $single ? '/single/' : '/';

		// Load it up
		//wpec_core_load_template( apply_filters( $this->id . '_template_' . $screen, $this->slug . $is_single . $screen ) );

	}

	/**
	 * single_screen()
	 *
	 * Template files are stored like $template/$slug/single/$screen
	 *
	 * Examples:	/wpec-default/members/single/my-friends
	 *				/wpec-default/members/single/friends-activity
	 *
	 *				/wpec-default/groups/single/my-groups
	 *				/wpec-default/groups/single/groups-activity
	 *
	 *				/wpec-default/forums/single/edit
	 *				/wpec-default/forums/single/topic
	 *
	 *				/wpec-default/activity/single/my-activity
	 *				/wpec-default/activity/single/friends-activity
	 *				/wpec-default/activity/single/blogs-activity
	 *
	 * @uses this->screen Loads template for single item
	 * @param bool $screen
	 */
	function single_screen( $screen ) {
		$this->screen( $screen, true );
	}

	/**
	 * single_screen_permalink()
	 *
	 * Default action for when $single is not set
	 *
	 * @uses do_action Calls (id)_screen_single_permalink action
	 */
	function single_screen_permalink() {
		do_action( $this->id . '_screen_single_permalink' );
	}

	/**
	 * load_textdomain()
	 *
	 * Custom textdomain loader. Based heavily off of component ID.
	 *
	 * @uses get_locale() Get current website locale
	 * @uses load_textdomain() Load up proper translation file
	 */
	function load_textdomain() {
		do_action( $this->id . '_load_text_domain' );

		$locale = apply_filters( $this->id . '_load_text_domain', get_locale() );

		$mofile = WP_PLUGIN_DIR . '/' . $this->id . '/languages/' . $this->id . '-' . $locale . '.mo';

		load_textdomain( $this->id, apply_filters( $this->id . '_load_text_domain_file', $mofile ) );
	}

	/**
	 * remove_data()
	 *
	 * Handle removing of data
	 *
	 * @uses do_action Calls (id)_remove_data action and passes current component object
	 */
	function remove_data() {
		do_action( $this->id . '_remove_data', $this );
	}

	/**
	 * check_conflict()
	 *
	 * Checks to make sure no active WP e-Commerce components exist that would
	 * potentially conflict with this component. Mostly used internally but
	 * could be used externally.
	 *
	 * @global array $wpec
	 * @return bool
	 */
	function check_conflict() {
		global $wpec;

		// If first item, no dupe
		if ( !is_array( $wpec->active_components ) )
			return false;

		// Loop through components and look for dupes
		foreach ( $wpec->active_components as $component ) {
			if ( $this->id == $component->id )
				return true;

			if ( $this->slug == $component->slug )
				return true;

			if ( $this->slug == $component->slug )
				return true;

			if ( ( $this->table_name && $component->table_name) && $this->table_name == $component->table_name )
				return true;

			if ( $this->css_class == $component->css_class )
				return true;
		}

		// No dupes, return false
		return false;
	}
}

?>