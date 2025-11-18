# WP Licensed Product - Example Plugin

A complete example WordPress plugin that demonstrates how to integrate WP Licensing API for license validation and automatic updates. This plugin serves as a reference implementation that you can copy and customize for your own plugins.

## 📋 Table of Contents

- [Overview](#overview)
- [How It Works](#how-it-works)
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [File Structure](#file-structure)
- [Usage as Template](#usage-as-template)
- [API Integration](#api-integration)
- [Troubleshooting](#troubleshooting)

## 🎯 Overview

WP Licensed Product is a **reference implementation** that shows developers exactly how to integrate the WP Licensing system into their WordPress plugins. It demonstrates:

- ✅ License key validation
- ✅ Automatic plugin updates
- ✅ Admin settings interface
- ✅ Scheduled license checks
- ✅ Update notifications
- ✅ Error handling

This plugin connects to a WP Licensing server (where the main WP Licensing plugin is installed) to validate licenses and receive automatic updates.

## 🔧 How It Works

### System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│         WP Licensing Server (Your Main Site)                │
│  • WP Licensing plugin installed                           │
│  • Products database                                        │
│  • Licenses database                                        │
│  • REST API endpoints                                       │
└─────────────────────────────────────────────────────────────┘
                            ▲
                            │ HTTP Requests
                            │
┌─────────────────────────────────────────────────────────────┐
│      WP Licensed Product (Customer Site)                    │
│  • This plugin installed                                    │
│  • License Manager (validates license)                     │
│  • Update Checker (checks for updates)                     │
│  • Admin Interface (settings page)                         │
└─────────────────────────────────────────────────────────────┘
```

### Complete Workflow

#### 1. Initial Setup

1. **Server Side (WP Licensing)**:
   - Admin creates a product in WP Licensing dashboard
   - Admin creates a license key for a customer
   - Admin uploads plugin ZIP file or sets download URL
   - Product version is set (e.g., 1.0.0)

2. **Client Side (This Plugin)**:
   - Customer installs this plugin on their WordPress site
   - Customer goes to Settings → Licensed Product
   - Customer enters:
     - License Server URL (where WP Licensing is installed)
     - Product ID (from WP Licensing admin)
     - License Key (received from admin)

#### 2. License Activation Flow

```
Customer enters license key
         ↓
Plugin sends POST request to:
/wp-json/wp-licensing/v1/validate
         ↓
Server validates:
• License key exists?
• License is active?
• Not expired?
• Has activation slots available?
• Product ID matches?
         ↓
Server responds:
• If valid: Activates license, tracks site URL
• If invalid: Returns error message
         ↓
Plugin stores:
• License status (active/inactive)
• License expiry date
• Last check timestamp
```

#### 3. Automatic Update Flow

```
WordPress checks for updates (periodically)
         ↓
Plugin intercepts update check
         ↓
Plugin sends GET request to:
/wp-json/wp-licensing/v1/update?license_key=XXX&version=1.0.0&product_id=1
         ↓
Server checks:
• License is valid?
• Current version < server version?
• Update available?
         ↓
Server responds:
• If update available: Returns new version, download URL, changelog
• If no update: Returns update_available: false
         ↓
WordPress shows update notification
         ↓
User clicks "Update Now"
         ↓
WordPress downloads ZIP from server
         ↓
WordPress installs update
```

#### 4. Scheduled License Validation

```
WordPress Cron runs daily
         ↓
Plugin's scheduled event triggers
         ↓
Plugin validates stored license key
         ↓
Updates license status if changed
         ↓
Shows admin notice if license expired/invalid
```

## ✨ Features

### License Management

- **License Validation**: Validates license keys against WP Licensing server
- **Activation Tracking**: Tracks which sites/licenses are activated
- **Status Monitoring**: Monitors license status (active, inactive, expired)
- **Scheduled Checks**: Automatically validates license daily via WordPress cron
- **Deactivation**: Allows deactivating license to free up activation slot

### Automatic Updates

- **Update Detection**: Automatically checks for new versions
- **Version Comparison**: Compares current version with server version
- **Secure Downloads**: Downloads updates only if license is valid
- **Changelog Display**: Shows changelog in WordPress update screen
- **One-Click Updates**: Updates appear in WordPress Updates page

### Admin Interface

- **Settings Page**: Easy-to-use settings page under Settings → Licensed Product
- **License Status Display**: Shows current license status with color coding
- **Expiry Information**: Displays license expiry date if applicable
- **Update Information**: Shows current version and update availability
- **Admin Notices**: Warns users if license is not active

### Security

- **License Verification**: Only valid licenses can activate
- **Domain Tracking**: Tracks which domains/licenses are activated
- **Secure API Calls**: Uses WordPress HTTP API with proper sanitization
- **Nonce Verification**: All form submissions use WordPress nonces

## 📦 Installation

### Step 1: Install on License Server

1. Install **WP Licensing** plugin on your main WordPress site
2. Create a product in WP Licensing admin
3. Create a license key for testing
4. Note the Product ID

### Step 2: Install This Plugin

1. **Upload the plugin** to your test WordPress site's `wp-content/plugins/` directory
2. **Activate the plugin** from WordPress admin → Plugins
3. **Go to Settings → Licensed Product**

### Step 3: Configure

1. **Enter License Server URL**: The URL where WP Licensing is installed
   - Example: `https://your-license-server.com`
   
2. **Enter Product ID**: The Product ID from WP Licensing admin
   - Example: `1`

3. **Enter License Key**: The license key you created
   - Example: `XXXX-XXXX-XXXX-XXXX`

4. **Click "Save Settings"**

5. **Click "Activate License"**

### Step 4: Verify

- License status should show "Active" (green)
- License expiry date should display (if set)
- No error messages should appear

## ⚙️ Configuration

### Settings Explained

#### License Server URL

The full URL of your WordPress site where the WP Licensing plugin is installed. This is where all API requests will be sent.

**Example:**
```
https://your-license-server.com
```

**Important:** 
- Must include `https://` or `http://`
- Should not include trailing slash
- Must be accessible from the customer's site

#### Product ID

The numeric ID of the product in your WP Licensing system. This must match the Product ID used when creating the license.

**How to find it:**
1. Go to WP Licensing admin → Products
2. Find your product in the list
3. Note the ID number in the first column

**Example:**
```
1
```

#### License Key

The license key generated by WP Licensing. This is a unique identifier that links the customer to their license.

**Format:**
```
XXXX-XXXX-XXXX-XXXX
```

**Where to get it:**
1. Go to WP Licensing admin → Licenses
2. Find the license you created
3. Copy the license key from the table

## 🏗️ Architecture

### Class Structure

The plugin consists of three main classes:

#### 1. `WP_Licensed_Product_License_Manager`

**Purpose:** Handles all license-related operations

**Key Methods:**
- `validate_license()` - Validates license key with server
- `deactivate_license()` - Deactivates license on current site
- `get_license_status()` - Gets current license status
- `is_license_valid()` - Checks if license is currently valid

**How it works:**
1. Sends POST request to `/wp-json/wp-licensing/v1/validate`
2. Receives response with license status
3. Stores status in WordPress options
4. Schedules daily validation via WordPress cron

#### 2. `WP_Licensed_Product_Update_Checker`

**Purpose:** Handles automatic update checking

**Key Methods:**
- `check_for_updates()` - Hooks into WordPress update system
- `get_update_info()` - Fetches update info from server
- `plugin_info()` - Provides plugin info for update screen

**How it works:**
1. Hooks into `pre_set_site_transient_update_plugins` filter
2. When WordPress checks for updates, sends GET request to server
3. Compares versions using `version_compare()`
4. If update available, adds to WordPress update transient
5. WordPress automatically shows update notification

#### 3. `WP_Licensed_Product_Admin`

**Purpose:** Creates and manages admin interface

**Key Methods:**
- `add_admin_menu()` - Adds settings page to WordPress admin
- `register_settings()` - Registers WordPress settings
- `render_settings_page()` - Renders the settings page HTML
- `handle_activate_license()` - Processes license activation
- `handle_deactivate_license()` - Processes license deactivation

**How it works:**
1. Creates settings page under Settings menu
2. Registers three settings: server_url, product_id, license_key
3. Handles form submissions for activation/deactivation
4. Displays license status and update information

### Data Flow

```
User Action → Admin Class → License Manager → HTTP Request → WP Licensing Server
                                                                    ↓
User Sees Result ← Admin Class ← License Manager ← HTTP Response ←─┘
```

### WordPress Hooks Used

**License Manager:**
- `wp_licensed_product_validate_license` - Custom cron event (daily)
- `wp_schedule_event` - Schedules daily validation

**Update Checker:**
- `pre_set_site_transient_update_plugins` - Intercepts update checks
- `plugins_api` - Provides plugin information for update screen

**Admin:**
- `admin_menu` - Adds settings page
- `admin_init` - Registers settings
- `admin_notices` - Shows license warnings
- `admin_enqueue_scripts` - Adds custom styles

## 📁 File Structure

```
wp-licensed-product/
├── wp-licensed-product.php          # Main plugin file
├── index.php                         # Security file
├── README.md                         # This file
├── CHANGELOG.md                      # Version history
└── includes/
    ├── class-license-manager.php    # License validation & management
    ├── class-update-checker.php     # Automatic update checking
    └── class-admin.php              # Admin settings interface
```

### File Descriptions

#### `wp-licensed-product.php`

Main plugin file that:
- Defines plugin constants (version, paths, slug)
- Includes required class files
- Initializes all classes
- Registers activation/deactivation hooks

**Key Constants:**
- `WP_LICENSED_PRODUCT_VERSION` - Current plugin version
- `WP_LICENSED_PRODUCT_PLUGIN_DIR` - Plugin directory path
- `WP_LICENSED_PRODUCT_PLUGIN_URL` - Plugin URL
- `WP_LICENSED_PRODUCT_PLUGIN_BASENAME` - Plugin basename for updates
- `WP_LICENSED_PRODUCT_SLUG` - Plugin slug (must match folder name)

#### `includes/class-license-manager.php`

Handles all license operations:

**Properties:**
- `$license_server_url` - URL of license server
- `$product_id` - Product ID from WP Licensing

**Key Features:**
- Validates license keys via API
- Deactivates licenses
- Stores license status in WordPress options
- Schedules daily validation checks
- Provides helper methods to check license status

**WordPress Options Used:**
- `wp_licensed_product_license_key` - Stored license key
- `wp_licensed_product_license_status` - Current status (active/inactive/error)
- `wp_licensed_product_license_message` - Status message
- `wp_licensed_product_license_expires` - Expiry date
- `wp_licensed_product_license_last_check` - Last validation timestamp
- `wp_licensed_product_server_url` - License server URL
- `wp_licensed_product_id` - Product ID

#### `includes/class-update-checker.php`

Handles automatic updates:

**Properties:**
- `$license_server_url` - URL of license server
- `$product_id` - Product ID
- `$version` - Current plugin version
- `$slug` - Plugin slug (must match folder name)

**Key Features:**
- Integrates with WordPress update system
- Checks for updates only if license is valid
- Compares versions using `version_compare()`
- Provides plugin information for update screen
- Shows changelog in update details

**How Version Comparison Works:**
```php
// Server has version 1.0.1
// Plugin has version 1.0.0
// Result: Update available ✓

// Server has version 1.0.0
// Plugin has version 1.0.0
// Result: No update needed ✗

// Server has version 1.0.1
// Plugin has version 1.0.2 (development)
// Result: No update needed ✗
```

#### `includes/class-admin.php`

Creates admin interface:

**Key Features:**
- Creates settings page under Settings menu
- Registers WordPress settings API
- Handles license activation/deactivation
- Displays license status with color coding
- Shows update information
- Displays admin notices for inactive licenses

**Settings Page Sections:**
1. **Configuration**: Server URL, Product ID, License Key
2. **License Status**: Current status, message, expiry date
3. **Actions**: Activate/Deactivate license buttons
4. **Update Information**: Current version and update availability

## 🔄 Usage as Template

This plugin is designed to be **copied and customized** for your own plugins. Here's how:

### Step 1: Copy Files

Copy the entire `includes/` directory to your plugin:
```
your-plugin/
└── includes/
    ├── class-license-manager.php
    ├── class-update-checker.php
    └── class-admin.php
```

### Step 2: Rename Classes

**In `class-license-manager.php`:**
```php
// Change:
class WP_Licensed_Product_License_Manager

// To:
class Your_Plugin_License_Manager
```

**In `class-update-checker.php`:**
```php
// Change:
class WP_Licensed_Product_Update_Checker

// To:
class Your_Plugin_Update_Checker
```

**In `class-admin.php`:**
```php
// Change:
class WP_Licensed_Product_Admin

// To:
class Your_Plugin_Admin
```

### Step 3: Update Option Names

Replace all occurrences of `wp_licensed_product_` with `your_plugin_`:

**Find and Replace:**
- `wp_licensed_product_license_key` → `your_plugin_license_key`
- `wp_licensed_product_server_url` → `your_plugin_server_url`
- `wp_licensed_product_id` → `your_plugin_id`
- `wp_licensed_product_license_status` → `your_plugin_license_status`
- etc.

### Step 4: Update Constants

In your main plugin file, define:
```php
define( 'YOUR_PLUGIN_VERSION', '1.0.0' );
define( 'YOUR_PLUGIN_SLUG', 'your-plugin-slug' ); // Must match folder name!
```

In `class-update-checker.php`, update:
```php
$this->version = YOUR_PLUGIN_VERSION;
$this->slug = YOUR_PLUGIN_SLUG;
```

### Step 5: Initialize in Main Plugin File

```php
function your_plugin_init() {
	require_once YOUR_PLUGIN_DIR . 'includes/class-license-manager.php';
	require_once YOUR_PLUGIN_DIR . 'includes/class-update-checker.php';
	require_once YOUR_PLUGIN_DIR . 'includes/class-admin.php';

	$license_manager = new Your_Plugin_License_Manager();
	$license_manager->init();

	$update_checker = new Your_Plugin_Update_Checker();
	$update_checker->init();

	$admin = new Your_Plugin_Admin();
	$admin->init();
}
add_action( 'plugins_loaded', 'your_plugin_init' );
```

### Step 6: Update Plugin Slug

**Critical:** The plugin slug in `class-update-checker.php` must match your plugin's folder name exactly!

If your plugin folder is `my-awesome-plugin`, then:
```php
$this->slug = 'my-awesome-plugin';
```

## 📡 API Integration

### Endpoints Used

#### 1. Validate License

**Endpoint:** `POST /wp-json/wp-licensing/v1/validate`

**Request:**
```php
wp_remote_post( $url, array(
	'body' => array(
		'license_key' => 'XXXX-XXXX-XXXX-XXXX',
		'site_url'    => 'https://example.com',
		'product_id'  => 1,
	),
) );
```

**Response:**
```json
{
	"valid": true,
	"status": "active",
	"expires_at": "2024-12-31 23:59:59",
	"activations_left": 4,
	"message": "License activated successfully"
}
```

#### 2. Deactivate License

**Endpoint:** `POST /wp-json/wp-licensing/v1/deactivate`

**Request:**
```php
wp_remote_post( $url, array(
	'body' => array(
		'license_key' => 'XXXX-XXXX-XXXX-XXXX',
		'site_url'    => 'https://example.com',
	),
) );
```

**Response:**
```json
{
	"success": true,
	"message": "License deactivated successfully"
}
```

#### 3. Check for Updates

**Endpoint:** `GET /wp-json/wp-licensing/v1/update`

**Request:**
```php
$url = add_query_arg( array(
	'license_key' => 'XXXX-XXXX-XXXX-XXXX',
	'version'     => '1.0.0',
	'product_id'  => 1,
), $server_url . '/wp-json/wp-licensing/v1/update' );

wp_remote_get( $url );
```

**Response:**
```json
{
	"update_available": true,
	"version": "1.0.1",
	"download_url": "https://server.com/wp-content/uploads/wp-licensing/product-1.0.1.zip",
	"changelog": "Fixed bugs and improved performance",
	"description": "New version with improvements"
}
```

### Error Handling

All API calls include error handling:

```php
$response = wp_remote_post( $url, $args );

if ( is_wp_error( $response ) ) {
	// Handle network error
	return array(
		'valid' => false,
		'message' => $response->get_error_message(),
	);
}

$code = wp_remote_retrieve_response_code( $response );
$body = wp_remote_retrieve_body( $response );
$data = json_decode( $body, true );

if ( 200 === $code && isset( $data['valid'] ) && $data['valid'] ) {
	// Success
} else {
	// Error
	$message = $data['message'] ?? 'Request failed.';
}
```

## 🔍 How Each Component Works

### License Manager - Detailed Flow

1. **Initialization:**
   ```php
   $license_manager = new WP_Licensed_Product_License_Manager();
   $license_manager->init();
   ```
   - Reads server URL and Product ID from options
   - Schedules daily validation cron event

2. **License Validation:**
   ```php
   $result = $license_manager->validate_license( $license_key );
   ```
   - Sends POST request to validation endpoint
   - Server checks license validity
   - Server activates license if valid
   - Plugin stores status in WordPress options
   - Returns result array

3. **Scheduled Validation:**
   - WordPress cron triggers daily
   - Validates stored license key
   - Updates status if changed
   - Shows notice if license expired

### Update Checker - Detailed Flow

1. **Initialization:**
   ```php
   $update_checker = new WP_Licensed_Product_Update_Checker();
   $update_checker->init();
   ```
   - Sets up WordPress update hooks
   - Configures version and slug

2. **Update Check Trigger:**
   - WordPress checks for updates (periodic or manual)
   - `pre_set_site_transient_update_plugins` filter fires
   - Plugin intercepts the check

3. **Update Detection:**
   ```php
   public function check_for_updates( $transient ) {
       // Only check if license is valid
       if ( 'active' !== $license_status ) {
           return $transient;
       }
       
       // Get update info from server
       $update_info = $this->get_update_info( $license_key );
       
       // Compare versions
       if ( $update_info->update_available ) {
           // Add to WordPress update system
           $transient->response[ $plugin_file ] = (object) array(
               'new_version' => $update_info->version,
               'package'     => $update_info->download_url,
           );
       }
   }
   ```

4. **Update Installation:**
   - User sees update notification
   - User clicks "Update Now"
   - WordPress downloads ZIP from `download_url`
   - WordPress installs update automatically

### Admin Interface - Detailed Flow

1. **Settings Page Creation:**
   ```php
   add_options_page(
       'WP Licensed Product Settings',
       'Licensed Product',
       'manage_options',
       'wp-licensed-product',
       array( $this, 'render_settings_page' )
   );
   ```
   - Creates page under Settings menu
   - Requires `manage_options` capability

2. **Settings Registration:**
   ```php
   register_setting( 'wp_licensed_product_settings', 'wp_licensed_product_license_key' );
   register_setting( 'wp_licensed_product_settings', 'wp_licensed_product_server_url' );
   register_setting( 'wp_licensed_product_settings', 'wp_licensed_product_id' );
   ```
   - Registers three settings
   - WordPress handles saving automatically

3. **License Activation:**
   - User enters license key
   - User clicks "Activate License"
   - Form submits with nonce
   - `handle_activate_license()` processes
   - Calls `$license_manager->validate_license()`
   - Shows success/error message

4. **Status Display:**
   - Reads status from options
   - Displays with color coding:
     - Green: Active
     - Yellow: Inactive
     - Red: Error
   - Shows expiry date if available

## 🧪 Testing Updates

### Complete Testing Workflow

1. **Setup License Server:**
   - Install WP Licensing plugin
   - Create product with version 1.0.0
   - Upload plugin ZIP file
   - Create license key

2. **Install Plugin (Version 1.0.0):**
   - Install this plugin on test site
   - Configure settings
   - Activate license
   - Verify license is active

3. **Create Update:**
   - Update plugin version to 1.0.1
   - Make some changes to plugin
   - Create new ZIP file
   - In WP Licensing admin:
     - Go to Products
     - Click Edit on your product
     - Update version to 1.0.1
     - Upload new ZIP file
     - Update changelog
     - Click Update

4. **Test Update:**
   - Go to WordPress admin → Updates
   - Click "Check Again" if needed
   - Update should appear
   - Click "Update Now"
   - Verify update installs successfully
   - Check version number

### Testing Checklist

- [ ] License activation works
- [ ] License deactivation works
- [ ] Invalid license shows error
- [ ] Expired license shows error
- [ ] Update appears when available
- [ ] Update installs successfully
- [ ] Changelog displays correctly
- [ ] Version number updates
- [ ] Scheduled validation runs
- [ ] Admin notices display correctly

## 🐛 Troubleshooting

### License Won't Activate

**Problem:** License activation fails with error message

**Solutions:**
1. **Check License Server URL:**
   - Must be correct and accessible
   - Try opening in browser: `https://your-server.com/wp-json/wp-licensing/v1/validate`
   - Should return JSON (may show error, but should be accessible)

2. **Verify Product ID:**
   - Must match Product ID in WP Licensing admin
   - Check Products table in WP Licensing

3. **Check License Key:**
   - Copy exactly from WP Licensing admin
   - No extra spaces
   - Correct format: `XXXX-XXXX-XXXX-XXXX`

4. **Check License Status:**
   - License must be active in WP Licensing
   - License must not be expired
   - License must have activation slots available

5. **Check Server Logs:**
   - Look for PHP errors
   - Check WordPress debug log
   - Check server error logs

### Updates Not Showing

**Problem:** Update available on server but not showing in WordPress

**Solutions:**
1. **Verify Plugin Slug:**
   - Must match plugin folder name exactly
   - Check `WP_LICENSED_PRODUCT_SLUG` constant
   - Check `$this->slug` in `class-update-checker.php`

2. **Check Version Numbers:**
   - Server version must be higher than plugin version
   - Use semantic versioning (1.0.0, 1.0.1, etc.)
   - Check `version_compare()` result

3. **Verify License Status:**
   - License must be active
   - Update checker only works with active license
   - Check `wp_licensed_product_license_status` option

4. **Clear WordPress Transients:**
   ```php
   delete_transient( 'update_plugins' );
   delete_site_transient( 'update_plugins' );
   ```
   - Or use plugin like "Transients Manager"

5. **Check Update Check Interval:**
   - WordPress checks updates periodically
   - Click "Check Again" in Updates page
   - Or wait for next automatic check

6. **Verify Download URL:**
   - Check product download URL in WP Licensing
   - URL must be accessible
   - ZIP file must be valid

### Scheduled Validation Not Running

**Problem:** Daily license validation not happening

**Solutions:**
1. **Check WordPress Cron:**
   - WordPress cron requires site visits
   - Use plugin like "WP Crontrol" to check
   - Manually trigger: `wp_licensed_product_validate_license`

2. **Verify Cron Event:**
   ```php
   $next_run = wp_next_scheduled( 'wp_licensed_product_validate_license' );
   if ( ! $next_run ) {
       // Event not scheduled, reschedule it
   }
   ```

3. **Check License Key:**
   - Must have license key saved
   - Validation only runs if key exists

### Update Downloads But Fails to Install

**Problem:** Update downloads but installation fails

**Solutions:**
1. **Check ZIP File:**
   - ZIP must be valid
   - Must contain plugin files
   - Main plugin file must be in root

2. **Check File Permissions:**
   - WordPress must be able to write to plugins directory
   - Check `wp-content/plugins/` permissions

3. **Check PHP Memory:**
   - Increase PHP memory limit
   - Check for memory errors in logs

4. **Verify Plugin Structure:**
   ```
   plugin-name/
   ├── plugin-name.php    # Main file (required)
   ├── index.php          # Security file
   └── includes/          # Other files
   ```

## 📝 WordPress Options Reference

The plugin stores the following options in WordPress:

| Option Name | Description | Example Value |
|------------|------------|--------------|
| `wp_licensed_product_license_key` | Stored license key | `XXXX-XXXX-XXXX-XXXX` |
| `wp_licensed_product_license_status` | Current license status | `active`, `inactive`, `error` |
| `wp_licensed_product_license_message` | Status message | `License activated successfully` |
| `wp_licensed_product_license_expires` | Expiry date | `2024-12-31 23:59:59` |
| `wp_licensed_product_license_last_check` | Last validation timestamp | `2024-01-15 10:30:00` |
| `wp_licensed_product_server_url` | License server URL | `https://your-server.com` |
| `wp_licensed_product_id` | Product ID | `1` |

## 🔐 Security Considerations

### Input Sanitization

All user inputs are sanitized:
```php
sanitize_text_field( $license_key )
esc_url_raw( $site_url )
absint( $product_id )
```

### Nonce Verification

All form submissions use WordPress nonces:
```php
check_admin_referer( 'wp_licensed_product_activate' );
wp_nonce_field( 'wp_licensed_product_activate' );
```

### Capability Checks

Admin functions check user capabilities:
```php
if ( ! current_user_can( 'manage_options' ) ) {
    return;
}
```

### Secure API Calls

Uses WordPress HTTP API which handles:
- SSL verification
- Timeout handling
- Error handling
- Response validation

## 📚 Code Examples

### Check License Status in Your Code

```php
$license_status = get_option( 'wp_licensed_product_license_status', 'inactive' );

if ( 'active' === $license_status ) {
    // License is valid, enable features
    add_action( 'init', 'your_premium_feature' );
} else {
    // License invalid, show notice
    add_action( 'admin_notices', function() {
        echo '<div class="notice notice-error">Please activate your license.</div>';
    });
}
```

### Programmatically Activate License

```php
require_once WP_LICENSED_PRODUCT_PLUGIN_DIR . 'includes/class-license-manager.php';

$license_manager = new WP_Licensed_Product_License_Manager();
$result = $license_manager->validate_license( 'YOUR-LICENSE-KEY' );

if ( isset( $result['valid'] ) && $result['valid'] ) {
    echo 'License activated!';
} else {
    echo 'Activation failed: ' . $result['message'];
}
```

### Check for Updates Manually

```php
require_once WP_LICENSED_PRODUCT_PLUGIN_DIR . 'includes/class-update-checker.php';

$update_checker = new WP_Licensed_Product_Update_Checker();
$update_checker->init();

// Force update check
delete_transient( 'update_plugins' );
wp_update_plugins();
```

## 🎓 Best Practices

### Version Management

1. **Use Semantic Versioning:**
   - Format: `MAJOR.MINOR.PATCH`
   - Example: `1.0.0`, `1.0.1`, `1.1.0`, `2.0.0`

2. **Update Version in Multiple Places:**
   - Plugin header: `Version: 1.0.0`
   - Constant: `define( 'WP_LICENSED_PRODUCT_VERSION', '1.0.0' );`
   - WP Licensing admin: Product version

3. **Always Increment Version:**
   - Even small changes should increment version
   - Server version must be higher than client version

### Error Handling

Always handle API errors gracefully:

```php
$response = wp_remote_post( $url, $args );

if ( is_wp_error( $response ) ) {
    // Log error
    error_log( 'License validation error: ' . $response->get_error_message() );
    
    // Show user-friendly message
    return array(
        'valid' => false,
        'message' => 'Unable to connect to license server. Please try again later.',
    );
}
```

### User Experience

1. **Clear Error Messages:**
   - Don't show technical errors to users
   - Provide actionable guidance
   - Link to settings page when needed

2. **Status Indicators:**
   - Use color coding (green/yellow/red)
   - Show expiry warnings
   - Display last check time

3. **Update Notifications:**
   - Show changelog in update screen
   - Make update process clear
   - Provide rollback instructions if needed

## 📞 Support

For issues, questions, or contributions:

- **Plugin URI**: https://wpcorex.com/products/wp-licensed-product
- **Author**: Bilal Mahmood
- **Author URI**: https://wpcorex.com

## 📄 License

GPL-2.0+

## 🙏 Credits

This plugin is an example implementation for the WP Licensing system, developed to help WordPress plugin developers integrate licensing and automatic updates into their products.

---

**Ready to use?** Copy the `includes/` directory to your plugin, rename the classes, update the options, and you're ready to go! See the [Usage as Template](#usage-as-template) section for detailed instructions.
