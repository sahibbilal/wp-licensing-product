<?php
/**
 * License Manager
 *
 * Handles license validation and deactivation using WP Licensing API
 *
 * @package WP_Licensed_Product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * License Manager class
 */
class WP_Licensed_Product_License_Manager {

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
	 * Constructor
	 */
	public function __construct() {
		// Configure these values for your setup
		// You can also make these configurable via settings
		$this->license_server_url = get_option( 'wp_licensed_product_server_url', home_url() );
		$this->product_id = (int) get_option( 'wp_licensed_product_id', 1 );
	}

	/**
	 * Initialize hooks
	 */
	public function init() {
		// Schedule periodic license validation
		add_action( 'wp_licensed_product_validate_license', array( $this, 'validate_stored_license' ) );
		
		// Schedule the event if not already scheduled
		if ( ! wp_next_scheduled( 'wp_licensed_product_validate_license' ) ) {
			wp_schedule_event( time(), 'daily', 'wp_licensed_product_validate_license' );
		}
	}

	/**
	 * Validate license key
	 *
	 * @param string $license_key License key.
	 * @param string $site_url Site URL (optional).
	 * @return array Validation result.
	 */
	public function validate_license( $license_key, $site_url = null ) {
		if ( null === $site_url ) {
			$site_url = home_url();
		}

		$url = trailingslashit( $this->license_server_url ) . 'wp-json/wp-licensing/v1/validate';

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 15,
				'body'    => array(
					'license_key' => sanitize_text_field( $license_key ),
					'site_url'    => esc_url_raw( $site_url ),
					'product_id'  => $this->product_id,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			$result = array(
				'valid'   => false,
				'message' => $response->get_error_message(),
			);
			$this->update_license_status( 'error', $result['message'] );
			return $result;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $code && isset( $data['valid'] ) && $data['valid'] ) {
			$this->update_license_status( 'active', $data['message'] ?? 'License is valid.' );
			if ( isset( $data['expires_at'] ) ) {
				update_option( 'wp_licensed_product_license_expires', $data['expires_at'] );
			}
		} else {
			$message = $data['message'] ?? 'License validation failed.';
			$this->update_license_status( 'inactive', $message );
		}

		return $data;
	}

	/**
	 * Deactivate license
	 *
	 * @param string $license_key License key.
	 * @param string $site_url Site URL (optional).
	 * @return array Deactivation result.
	 */
	public function deactivate_license( $license_key, $site_url = null ) {
		if ( null === $site_url ) {
			$site_url = home_url();
		}

		$url = trailingslashit( $this->license_server_url ) . 'wp-json/wp-licensing/v1/deactivate';

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 15,
				'body'    => array(
					'license_key' => sanitize_text_field( $license_key ),
					'site_url'    => esc_url_raw( $site_url ),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['success'] ) && $data['success'] ) {
			$this->update_license_status( 'inactive', 'License deactivated.' );
		}

		return $data;
	}

	/**
	 * Validate stored license (for scheduled checks)
	 */
	public function validate_stored_license() {
		$license_key = get_option( 'wp_licensed_product_license_key', '' );
		if ( ! empty( $license_key ) ) {
			$this->validate_license( $license_key );
		}
	}

	/**
	 * Update license status
	 *
	 * @param string $status Status.
	 * @param string $message Message.
	 */
	private function update_license_status( $status, $message ) {
		update_option( 'wp_licensed_product_license_status', $status );
		update_option( 'wp_licensed_product_license_message', $message );
		update_option( 'wp_licensed_product_license_last_check', current_time( 'mysql' ) );
	}

	/**
	 * Get license status
	 *
	 * @return string
	 */
	public function get_license_status() {
		return get_option( 'wp_licensed_product_license_status', 'inactive' );
	}

	/**
	 * Check if license is valid
	 *
	 * @return bool
	 */
	public function is_license_valid() {
		$status = $this->get_license_status();
		return 'active' === $status;
	}
}

