<?php
/**
 * Admin
 *
 * Handles admin interface for license management
 *
 * @package WP_Licensed_Product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class
 */
class WP_Licensed_Product_Admin {

	/**
	 * License manager instance
	 *
	 * @var WP_Licensed_Product_License_Manager
	 */
	private $license_manager;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->license_manager = new WP_Licensed_Product_License_Manager();
	}

	/**
	 * Initialize hooks
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_options_page(
			'WP Licensed Product Settings',
			'Licensed Product',
			'manage_options',
			'wp-licensed-product',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting( 'wp_licensed_product_settings', 'wp_licensed_product_license_key' );
		register_setting( 'wp_licensed_product_settings', 'wp_licensed_product_server_url' );
		register_setting( 'wp_licensed_product_settings', 'wp_licensed_product_id' );

		// Handle license activation/deactivation
		if ( isset( $_POST['wp_licensed_product_activate_license'] ) && check_admin_referer( 'wp_licensed_product_activate' ) ) {
			$this->handle_activate_license();
		}

		if ( isset( $_POST['wp_licensed_product_deactivate_license'] ) && check_admin_referer( 'wp_licensed_product_deactivate' ) ) {
			$this->handle_deactivate_license();
		}
	}

	/**
	 * Handle license activation
	 */
	private function handle_activate_license() {
		// First try to get from POST (if submitted in the same form)
		$license_key = sanitize_text_field( $_POST['wp_licensed_product_license_key'] ?? '' );
		
		// If not in POST, get from saved option
		if ( empty( $license_key ) ) {
			$license_key = get_option( 'wp_licensed_product_license_key', '' );
		}
		
		if ( empty( $license_key ) ) {
			add_settings_error(
				'wp_licensed_product_license_key',
				'empty_license',
				'Please enter a license key in the License Key field above and save settings first, or enter it in the form below.',
				'error'
			);
			return;
		}

		// Update the option if it came from POST
		if ( isset( $_POST['wp_licensed_product_license_key'] ) ) {
			update_option( 'wp_licensed_product_license_key', $license_key );
		}
		
		$result = $this->license_manager->validate_license( $license_key );
		
		if ( isset( $result['valid'] ) && $result['valid'] ) {
			add_settings_error(
				'wp_licensed_product_license_key',
				'license_activated',
				'License activated successfully!',
				'success'
			);
		} else {
			$message = $result['message'] ?? 'License activation failed.';
			add_settings_error(
				'wp_licensed_product_license_key',
				'license_failed',
				$message,
				'error'
			);
		}
	}

	/**
	 * Handle license deactivation
	 */
	private function handle_deactivate_license() {
		$license_key = get_option( 'wp_licensed_product_license_key', '' );
		
		if ( empty( $license_key ) ) {
			return;
		}

		$result = $this->license_manager->deactivate_license( $license_key );
		
		if ( isset( $result['success'] ) && $result['success'] ) {
			delete_option( 'wp_licensed_product_license_key' );
			add_settings_error(
				'wp_licensed_product_license_key',
				'license_deactivated',
				'License deactivated successfully!',
				'success'
			);
		} else {
			$message = $result['message'] ?? 'License deactivation failed.';
			add_settings_error(
				'wp_licensed_product_license_key',
				'license_deactivate_failed',
				$message,
				'error'
			);
		}
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$license_key = get_option( 'wp_licensed_product_license_key', '' );
		$license_status = get_option( 'wp_licensed_product_license_status', 'inactive' );
		$license_message = get_option( 'wp_licensed_product_license_message', '' );
		$license_expires = get_option( 'wp_licensed_product_license_expires', '' );
		$server_url = get_option( 'wp_licensed_product_server_url', home_url() );
		$product_id = get_option( 'wp_licensed_product_id', 1 );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<form method="post" action="options.php">
				<?php settings_fields( 'wp_licensed_product_settings' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="wp_licensed_product_server_url">License Server URL</label>
						</th>
						<td>
							<input 
								type="url" 
								id="wp_licensed_product_server_url" 
								name="wp_licensed_product_server_url" 
								value="<?php echo esc_attr( $server_url ); ?>" 
								class="regular-text"
								placeholder="https://your-license-server.com"
							/>
							<p class="description">The URL of your WordPress site running the WP Licensing plugin.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wp_licensed_product_id">Product ID</label>
						</th>
						<td>
							<input 
								type="number" 
								id="wp_licensed_product_id" 
								name="wp_licensed_product_id" 
								value="<?php echo esc_attr( $product_id ); ?>" 
								class="small-text"
								min="1"
							/>
							<p class="description">The Product ID configured in your WP Licensing system.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wp_licensed_product_license_key">License Key</label>
						</th>
						<td>
							<input 
								type="text" 
								id="wp_licensed_product_license_key" 
								name="wp_licensed_product_license_key" 
								value="<?php echo esc_attr( $license_key ); ?>" 
								class="regular-text code"
								placeholder="XXXX-XXXX-XXXX-XXXX"
							/>
							<p class="description">Enter your license key to activate automatic updates.</p>
						</td>
					</tr>
				</table>

				<?php submit_button( 'Save Settings' ); ?>
			</form>

			<hr>

			<h2>License Status</h2>
			<table class="form-table">
				<tr>
					<th scope="row">Status</th>
					<td>
						<strong>
							<span class="license-status status-<?php echo esc_attr( $license_status ); ?>">
								<?php echo esc_html( ucfirst( $license_status ) ); ?>
							</span>
						</strong>
					</td>
				</tr>
				<?php if ( ! empty( $license_message ) ) : ?>
				<tr>
					<th scope="row">Message</th>
					<td><?php echo esc_html( $license_message ); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( ! empty( $license_expires ) ) : ?>
				<tr>
					<th scope="row">Expires</th>
					<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $license_expires ) ) ); ?></td>
				</tr>
				<?php endif; ?>
			</table>

			<form method="post" action="">
				<?php
				// Include the license key field in this form so it's available when activating
				if ( ! empty( $license_key ) ) {
					?>
					<input type="hidden" name="wp_licensed_product_license_key" value="<?php echo esc_attr( $license_key ); ?>" />
					<?php
				}
				
				if ( 'active' === $license_status && ! empty( $license_key ) ) {
					wp_nonce_field( 'wp_licensed_product_deactivate' );
					submit_button( 'Deactivate License', 'secondary', 'wp_licensed_product_deactivate_license', false );
				} elseif ( ! empty( $license_key ) ) {
					wp_nonce_field( 'wp_licensed_product_activate' );
					submit_button( 'Activate License', 'primary', 'wp_licensed_product_activate_license', false );
				} else {
					?>
					<p class="description">Please enter a license key in the field above and click "Save Settings" first.</p>
					<?php
				}
				?>
			</form>

			<hr>

			<h2>Update Information</h2>
			<div class="update-info-box" style="background: #f0f6fc; border-left: 4px solid #2271b1; padding: 15px; margin: 15px 0;">
				<p style="margin: 0 0 10px 0;"><strong>Current Version:</strong> <span style="font-size: 18px; color: #2271b1; font-weight: bold;"><?php echo esc_html( WP_LICENSED_PRODUCT_VERSION ); ?></span></p>
				<p style="margin: 0;">This plugin checks for updates automatically using the WP Licensing API. Updates will appear in your WordPress Updates page when available.</p>
				<?php if ( '1.0.1' === WP_LICENSED_PRODUCT_VERSION ) : ?>
					<p style="margin: 10px 0 0 0; padding-top: 10px; border-top: 1px solid #c3c4c7;">
						<strong>What's New in v1.0.1:</strong><br>
						✓ Improved version display<br>
						✓ Enhanced update checking<br>
						✓ Better user interface
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Admin notices
	 */
	public function admin_notices() {
		$license_status = get_option( 'wp_licensed_product_license_status', 'inactive' );
		
		if ( 'active' !== $license_status ) {
			$screen = get_current_screen();
			if ( $screen && 'settings_page_wp-licensed-product' !== $screen->id ) {
				?>
				<div class="notice notice-warning is-dismissible">
					<p>
						<strong>WP Licensed Product:</strong> 
						Your license is not active. 
						<a href="<?php echo esc_url( admin_url( 'options-general.php?page=wp-licensed-product' ) ); ?>">
							Activate your license
						</a> to receive automatic updates.
					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'settings_page_wp-licensed-product' !== $hook ) {
			return;
		}

		wp_add_inline_style( 'wp-admin', '
			.license-status {
				padding: 4px 8px;
				border-radius: 3px;
				font-weight: 600;
			}
			.license-status.status-active {
				background: #00a32a;
				color: #fff;
			}
			.license-status.status-inactive {
				background: #dba617;
				color: #fff;
			}
			.license-status.status-error {
				background: #d63638;
				color: #fff;
			}
		' );
	}
}

