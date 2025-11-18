<?php
/**
 * Update Checker
 *
 * Handles automatic updates using WP Licensing API
 *
 * @package WP_Licensed_Product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update Checker class
 */
class WP_Licensed_Product_Update_Checker {

	/**
	 * License server URL
	 *
	 * @var string
	 */
	private $license_server_url;

	/**
	 * Product ID
	 *
	 * @var int
	 */
	private $product_id;

	/**
	 * Current version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->license_server_url = get_option( 'wp_licensed_product_server_url', home_url() );
		$this->product_id = (int) get_option( 'wp_licensed_product_id', 1 );
		$this->version = WP_LICENSED_PRODUCT_VERSION;
		$this->slug = WP_LICENSED_PRODUCT_SLUG;
	}

	/**
	 * Initialize hooks
	 */
	public function init() {
		// Hook into WordPress update system
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
	}

	/**
	 * Get license key from options
	 *
	 * @return string
	 */
	private function get_license_key() {
		return get_option( 'wp_licensed_product_license_key', '' );
	}

	/**
	 * Check for updates
	 *
	 * @param object $transient Update transient.
	 * @return object
	 */
	public function check_for_updates( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$license_key = $this->get_license_key();
		if ( empty( $license_key ) ) {
			return $transient;
		}

		// Only check if license is valid
		$license_status = get_option( 'wp_licensed_product_license_status', 'inactive' );
		if ( 'active' !== $license_status ) {
			return $transient;
		}

		$update_info = $this->get_update_info( $license_key );

		if ( $update_info && isset( $update_info->update ) && $update_info->update ) {
			$plugin_file = WP_LICENSED_PRODUCT_PLUGIN_BASENAME;
			$transient->response[ $plugin_file ] = (object) array(
				'slug'        => $this->slug,
				'new_version' => $update_info->version,
				'package'     => $update_info->download_url,
				'url'         => '',
			);
		}

		return $transient;
	}

	/**
	 * Get update info from license server
	 *
	 * @param string $license_key License key.
	 * @return object|null
	 */
	private function get_update_info( $license_key ) {
		$url = add_query_arg(
			array(
				'license_key' => $license_key,
				'version'     => $this->version,
				'product_id'  => $this->product_id,
			),
			trailingslashit( $this->license_server_url ) . 'wp-json/wp-licensing/v1/update'
		);

		$response = wp_remote_get( $url, array( 'timeout' => 15 ) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		return $data;
	}

	/**
	 * Plugin info for update screen
	 *
	 * @param false|object|array $result Result.
	 * @param string             $action Action.
	 * @param object             $args Arguments.
	 * @return false|object|array
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || $args->slug !== $this->slug ) {
			return $result;
		}

		$license_key = $this->get_license_key();
		if ( empty( $license_key ) ) {
			return $result;
		}

		$update_info = $this->get_update_info( $license_key );

		if ( $update_info && isset( $update_info->update ) && $update_info->update ) {
			$result = (object) array(
				'name'          => 'WP Licensed Product',
				'slug'          => $this->slug,
				'version'       => $update_info->version,
				'download_link' => $update_info->download_url,
				'sections'      => array(
					'changelog' => isset( $update_info->changelog ) ? $update_info->changelog : 'No changelog available.',
				),
			);
		}

		return $result;
	}
}

