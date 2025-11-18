# Testing License Updates

This guide will help you test the automatic update functionality of the WP Licensed Product plugin.

## Prerequisites

1. **License Server Setup:**
   - WP Licensing plugin installed and activated
   - At least one Product created
   - At least one License created for that product

2. **Test Site Setup:**
   - WordPress installation (can be the same site or different)
   - WP Licensed Product plugin installed (version 1.0.0)

## Step-by-Step Testing Process

### Step 1: Create the Plugin ZIP

Choose one of these methods:

**Method A: Using PHP Script (Recommended)**
1. Visit: `http://yoursite.com/wp-content/plugins/wp-licensed-product/create-zip.php`
2. Download the generated ZIP file

**Method B: Using Command Line**
- Windows: Double-click `create-zip.bat`
- Linux/Mac: Run `chmod +x create-zip.sh && ./create-zip.sh`

**Method C: Manual ZIP**
- Create a ZIP containing all plugin files
- Name it: `wp-licensed-product-1.0.1.zip`

### Step 2: Upload to WP Licensing

1. Go to **Licensing → Products** in your WordPress admin
2. Either:
   - **Create a new product** (if testing from scratch)
   - **Edit existing product** (if updating)
3. Fill in the product details:
   - **Name**: WP Licensed Product
   - **Slug**: wp-licensed-product (auto-generated)
   - **Version**: 1.0.1
   - **Download URL**: Either upload the ZIP file OR enter a URL
4. Click **Create** or **Update**

### Step 3: Create/Verify License

1. Go to **Licensing → Licenses**
2. Create a license for the product (or use existing)
3. Note the **License Key**

### Step 4: Configure Test Site

1. On your test WordPress site, go to **Settings → Licensed Product**
2. Enter:
   - **License Server URL**: Your license server URL (e.g., `https://your-license-server.com`)
   - **Product ID**: The ID of the product you created (usually 1)
   - **License Key**: The license key from Step 3
3. Click **Save Settings**
4. Click **Activate License**
5. Verify license status shows as **Active**

### Step 5: Test the Update

1. **Initial State Check:**
   - Go to **Settings → Licensed Product** on test site
   - Verify it shows **Current Version: 1.0.0**

2. **Trigger Update Check:**
   - Go to **Dashboard → Updates** (or **Plugins → Installed Plugins**)
   - Click **Check Again** if available
   - OR wait for WordPress to automatically check (usually within 12 hours)

3. **Verify Update Appears:**
   - You should see "WP Licensed Product" in the updates list
   - It should show version 1.0.1 available

4. **Install the Update:**
   - Select the plugin update
   - Click **Update Plugins**
   - Wait for the update to complete

5. **Verify Update Success:**
   - Go to **Settings → Licensed Product**
   - Verify it now shows **Current Version: 1.0.1**
   - Check that the "What's New" section appears

## Troubleshooting

### Update Not Appearing

1. **Check License Status:**
   - Ensure license is active on test site
   - Go to Settings → Licensed Product and verify status

2. **Check Product Version:**
   - Ensure product version in WP Licensing matches the ZIP version (1.0.1)
   - Product version must be HIGHER than installed version (1.0.0)

3. **Check Product ID:**
   - Ensure Product ID in test site settings matches the product ID in WP Licensing

4. **Check Server URL:**
   - Verify the license server URL is correct and accessible
   - Test the API endpoint: `https://your-server.com/wp-json/wp-licensing/v1/update?license_key=YOUR_KEY&version=1.0.0&product_id=1`

5. **Force Update Check:**
   - Add this to `wp-config.php` temporarily:
     ```php
     define('WP_AUTO_UPDATE_CORE', true);
     ```
   - Or use a plugin like "WP Updates Notifier"

### License Validation Fails

1. Check that the license key is correct
2. Verify the license is active in WP Licensing admin
3. Check that the site URL matches (some servers are strict about this)
4. Verify the Product ID matches

### ZIP Upload Fails

1. Check file size (should be under 50MB)
2. Verify it's a valid ZIP file
3. Check WordPress uploads directory permissions
4. Try using a download URL instead

## Testing Different Scenarios

### Scenario 1: New Installation
- Install plugin version 1.0.0
- Activate license
- Update should appear for 1.0.1

### Scenario 2: License Expired
- Create license with expiration date in the past
- Update should NOT appear
- License status should show as expired

### Scenario 3: Invalid License
- Use an invalid license key
- Update should NOT appear
- License status should show as inactive

### Scenario 4: Multiple Sites
- Activate same license on multiple sites
- All sites should receive updates
- Check activation limit in license settings

## Next Steps

After successful testing:

1. **Create New Versions:**
   - Update version number in `wp-licensed-product.php`
   - Make changes to the plugin
   - Create new ZIP file
   - Update product version in WP Licensing

2. **Production Deployment:**
   - Use the same process for your actual commercial plugins
   - Set up proper versioning strategy
   - Document your update process

3. **Monitor:**
   - Check license activations
   - Monitor update requests
   - Review API logs in WP Licensing

## Support

If you encounter issues:
1. Check the WP Licensing API & Plugins tab for API documentation
2. Review the README.md for configuration details
3. Check WordPress debug log for errors
4. Verify all settings match between license server and client site

