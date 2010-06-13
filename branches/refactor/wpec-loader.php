<?php
/*
Plugin Name: WP e-Commerce
Plugin URI: http://www.instinct.co.nz
Description: A plugin that provides a WordPress Shopping Cart. Contact <a href='http://www.instinct.co.nz/?p=16#support'>Instinct Entertainment</a> for support. <br />Click here to to <a href='?wpsc_uninstall=ask'>Uninstall</a>.
Version: Refactor
Author: Instinct Entertainment
Author URI: http://www.instinct.co.nz/e-commerce/
*/

/**
 * wpec_ready()
 *
 * Action to call when WP e-Commerce is ready to extend. External dependent
 * want to set themselves to load here rather than on plugins loaded.
 *
 */
function wpec_ready() {
	do_action( 'wpec_ready' );
}
add_action( 'plugins_loaded', 'wpec_ready' );

?>