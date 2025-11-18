<?php
/**
 * Plugin Name: WP Licensed Product
 * Plugin URI: https://wpcorex.com/products/wp-licensed-product
 * Description: Sample plugin that uses WP Licensing API for license validation and automatic updates
 * Version: 1.0.0
 * Author: Bilal Mahmood
 * Author URI: https://wpcorex.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-licensed-product
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'WP_LICENSED_PRODUCT_VERSION', '1.0.0' );
define( 'WP_LICENSED_PRODUCT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_LICENSED_PRODUCT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_LICENSED_PRODUCT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WP_LICENSED_PRODUCT_SLUG', 'wp-licensed-product' );

/**
 * Initialize the plugin
 */
function wp_licensed_product_init() {
	require_once WP_LICENSED_PRODUCT_PLUGIN_DIR . 'includes/class-license-manager.php';
	require_once WP_LICENSED_PRODUCT_PLUGIN_DIR . 'includes/class-update-checker.php';
	require_once WP_LICENSED_PRODUCT_PLUGIN_DIR . 'includes/class-admin.php';

	// Initialize license manager
	$license_manager = new WP_Licensed_Product_License_Manager();
	$license_manager->init();

	// Initialize update checker
	$update_checker = new WP_Licensed_Product_Update_Checker();
	$update_checker->init();

	// Initialize admin
	$admin = new WP_Licensed_Product_Admin();
	$admin->init();
}
add_action( 'plugins_loaded', 'wp_licensed_product_init' );

/**
 * Activation hook
 */
register_activation_hook( __FILE__, 'wp_licensed_product_activate' );
function wp_licensed_product_activate() {
	// Optionally validate license on activation
	$license_key = get_option( 'wp_licensed_product_license_key', '' );
	if ( ! empty( $license_key ) ) {
		require_once WP_LICENSED_PRODUCT_PLUGIN_DIR . 'includes/class-license-manager.php';
		$license_manager = new WP_Licensed_Product_License_Manager();
		$license_manager->validate_license( $license_key );
	}
}

/**
 * Deactivation hook
 */
register_deactivation_hook( __FILE__, 'wp_licensed_product_deactivate' );
function wp_licensed_product_deactivate() {
	// Optionally deactivate license on deactivation
	$license_key = get_option( 'wp_licensed_product_license_key', '' );
	if ( ! empty( $license_key ) ) {
		require_once WP_LICENSED_PRODUCT_PLUGIN_DIR . 'includes/class-license-manager.php';
		$license_manager = new WP_Licensed_Product_License_Manager();
		$license_manager->deactivate_license( $license_key );
	}
}

