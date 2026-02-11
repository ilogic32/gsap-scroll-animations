# GSAP Scroll Animations - Update System Setup Guide

## Overview

The plugin now includes professional automatic update functionality using the Plugin Update Checker library. This guide walks you through configuring the server-side update system on blu8print.com.

## What's Been Done (Local)

✅ **Completed locally:**
- Downloaded and integrated Plugin Update Checker library v5.6
- Added update checker initialization to `gsap-scroll-animations.php`
- Created `update-info.json` with version 1.0.0 metadata
- Created `gsap-scroll-animations-1.0.0.zip` plugin package

**Files to upload to blu8print.com:**
```
/plugins/gsap-animations/
├── update-info.json                      (from plugin root)
├── gsap-scroll-animations-1.0.0.zip      (from plugins directory)
└── .htaccess                             (create on server)
```

---

## Step 1: Create Directory Structure on blu8print.com

### Via cPanel File Manager:

1. **Log in to cPanel** at `https://www.blu8print.com/cpanel`
2. **Open File Manager**
3. **Navigate to** `/public_html/`
4. **Right-click → Create Folder** → Name: `plugins`
5. **Open `plugins` folder**
6. **Right-click → Create Folder** → Name: `gsap-animations`
7. **Open the new `gsap-animations` folder**

### Verify Permissions:

1. **Right-click on `plugins` folder → Change Permissions**
2. **Set to:** `755`
3. **Apply recursively:** ✓ Yes
4. **Click Change Permissions**

---

## Step 2: Upload Files to Server

### File 1: update-info.json

**Location:** `/Users/sebastiaan/Development/plugins/GSAP-Animations-COMPLETE/update-info.json`

1. **In cPanel File Manager**, navigate to `/public_html/plugins/gsap-animations/`
2. **Click Upload**
3. **Select file:** `update-info.json`
4. **Upload**
5. **Set permissions:** Right-click → Change Permissions → `644`

**Verify:** Visit `https://www.blu8print.com/plugins/gsap-animations/update-info.json` in browser
- Should display JSON content (not download)
- Content should be readable

### File 2: Plugin ZIP Package

**Location:** `/Users/sebastiaan/Development/plugins/gsap-scroll-animations-1.0.0.zip`

1. **In cPanel File Manager**, navigate to `/public_html/plugins/gsap-animations/`
2. **Click Upload**
3. **Select file:** `gsap-scroll-animations-1.0.0.zip`
4. **Upload**
5. **Set permissions:** Right-click → Change Permissions → `644`

**Verify:** Visit `https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.0.0.zip` in browser
- Should trigger file download

---

## Step 3: Create .htaccess File (Optional but Recommended)

This prevents directory browsing and ensures proper MIME types.

1. **In cPanel File Manager**, navigate to `/public_html/plugins/gsap-animations/`
2. **Right-click → Create New File**
3. **Name:** `.htaccess`
4. **Right-click → Edit**
5. **Copy and paste the following content:**

```apache
# Prevent directory listing
Options -Indexes

# Set proper MIME type for JSON files
<Files "*.json">
    Header set Content-Type "application/json; charset=utf-8"
</Files>

# Ensure ZIP downloads properly
<Files "*.zip">
    Header set Content-Type "application/zip"
</Files>

# Prevent access to sensitive files
<Files "update-info.json">
    Allow from all
</Files>
```

6. **Save and close**
7. **Set permissions:** `644`

---

## Step 4: Verify Server Setup

### Test JSON Endpoint:

Open browser and visit:
```
https://www.blu8print.com/plugins/gsap-animations/update-info.json
```

**Expected result:**
- Page displays JSON text (formatted or minified)
- Content-Type header is `application/json`
- All fields from `update-info.json` are visible

**Test commands (via terminal):**
```bash
# Check JSON content
curl https://www.blu8print.com/plugins/gsap-animations/update-info.json

# Check headers
curl -I https://www.blu8print.com/plugins/gsap-animations/update-info.json
```

### Test ZIP Download:

Open browser and visit:
```
https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.0.0.zip
```

**Expected result:**
- File downloads to your computer
- File size: ~94 KB
- Content-Type header is `application/zip`

**Test commands (via terminal):**
```bash
# Download file
curl -O https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.0.0.zip

# Verify ZIP integrity
unzip -t gsap-scroll-animations-1.0.0.zip
```

---

## Step 5: Test Update Detection in WordPress

### Initial Test (No Update):

1. **Install the plugin** in your WordPress test site
2. **Activate** the plugin
3. **Go to:** WordPress Admin → Plugins
4. **Find:** "GSAP Scroll Animations"
5. **Expected:** No update notification (versions match: 1.0.0 = 1.0.0)

### Test Update Detection:

1. **Edit `update-info.json`** on server
2. **Change version** from `1.0.0` to `1.0.1`
3. **Save file**
4. **In WordPress:** Dashboard → Updates → **"Check Again"** button
5. **Expected result:**
   - Update notification appears on Plugins page
   - Shows: "There is a new version of GSAP Scroll Animations available"
   - Version shows: "1.0.1"
   - "View version 1.0.1 details" link available

### View Update Details:

1. **Click** "View version 1.0.1 details"
2. **Expected popup shows:**
   - Plugin name: "GSAP Scroll Animations"
   - Description section
   - Changelog section (shows HTML from JSON)
   - "Install Update Now" button

### Revert for Production:

**Important:** Revert `update-info.json` back to version `1.0.0` after testing:

1. **Edit `update-info.json`** on server
2. **Change version** from `1.0.1` back to `1.0.0`
3. **Save file**

---

## Release Workflow for Future Versions

When you're ready to release version 1.1.0 (or any new version):

### Step 1: Update Plugin Version

**Edit** `/Users/sebastiaan/Development/plugins/GSAP-Animations-COMPLETE/gsap-scroll-animations.php`

Find line 6 and update:
```php
* Version: 1.1.0
```

### Step 2: Create New ZIP Package

**Run these commands:**

```bash
cd /Users/sebastiaan/Development/plugins

# Rename directory
mv GSAP-Animations-COMPLETE gsap-scroll-animations

# Create ZIP (excluding docs)
zip -r gsap-scroll-animations-1.1.0.zip gsap-scroll-animations/ \
    -x "*.DS_Store" "__MACOSX/*" "*.md" "ANIMATION-*.html" "UPLOAD_INSTRUCTIONS.txt"

# Rename back
mv gsap-scroll-animations GSAP-Animations-COMPLETE

# Verify
ls -lh gsap-scroll-animations-1.1.0.zip
```

### Step 3: Upload New ZIP to Server

1. **Navigate to:** `/public_html/plugins/gsap-animations/` on cPanel
2. **Upload:** `gsap-scroll-animations-1.1.0.zip`
3. **Set permissions:** `644`

### Step 4: Update JSON Metadata

**Edit** `update-info.json` on server with new version info:

```json
{
  "name": "GSAP Scroll Animations",
  "slug": "gsap-scroll-animations",
  "version": "1.1.0",
  "download_url": "https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.1.0.zip",
  "homepage": "https://www.blu8print.com",
  "requires": "5.0",
  "tested": "6.6",
  "requires_php": "7.4",
  "last_updated": "2026-03-15 14:30:00",
  "author": "Blueprint 8",
  "author_homepage": "https://www.blu8print.com",
  "sections": {
    "description": "Two powerful animation systems: ID-based admin UI for non-technical users (13 animation types), and class-based system for developers with 20+ advanced animation effects. Includes accessibility support with prefers-reduced-motion detection.",
    "installation": "Upload the plugin folder to /wp-content/plugins/ and activate through the WordPress Plugins menu.",
    "changelog": "<h4>1.1.0</h4><ul><li>New feature: X</li><li>Bug fix: Y</li></ul><h4>1.0.0</h4><ul><li>Initial release</li></ul>"
  },
  "upgrade_notice": "Update to 1.1.0 for new features and improvements."
}
```

### Step 5: Trigger Update Check

**In WordPress:**
1. Go to: Dashboard → Updates
2. Click: "Check Again" button
3. Update should appear immediately for all installations

---

## Troubleshooting

### Issue: "Update not showing up"

**Checklist:**
- [ ] JSON file is accessible at `https://www.blu8print.com/plugins/gsap-animations/update-info.json`
- [ ] Version in JSON is **higher** than installed plugin version
- [ ] `slug` in JSON is exactly `gsap-scroll-animations`
- [ ] No syntax errors in JSON (validate at https://jsonlint.com)
- [ ] `download_url` points to valid ZIP file
- [ ] Cleared WordPress transients (Dashboard → Updates → "Check Again")

### Issue: "JSON file returns 404"

**Check:**
1. File uploaded to correct path: `/public_html/plugins/gsap-animations/`
2. File named exactly: `update-info.json` (case-sensitive)
3. File permissions are `644`
4. URL uses HTTPS (not HTTP)
5. No .htaccess blocking access

### Issue: "Update fails to install"

**Check:**
1. ZIP file is accessible and downloads correctly
2. ZIP structure is correct (has `gsap-scroll-animations/` folder inside)
3. Plugin directory has write permissions (`755`)
4. Sufficient disk space available
5. PHP memory limit is adequate (check wp-config.php)

### Issue: "Plugin Update Checker library not found"

**Check:**
1. Files exist in: `/lib/plugin-update-checker/` directory
2. Main file exists: `lib/plugin-update-checker/plugin-update-checker.php`
3. Path in plugin file is correct: `require __DIR__ . '/lib/plugin-update-checker/plugin-update-checker.php';`

---

## Security Best Practices

### HTTPS Required
- All URLs must use HTTPS
- WordPress enforces secure connections for plugin updates
- Verify SSL certificate is valid on blu8print.com

### File Permissions
```
Directories: 755 (owner can write, others can read)
Files: 644 (owner can write, others can read)
```

### JSON Validation
- Always validate JSON syntax before uploading
- Use https://jsonlint.com to check
- Invalid JSON breaks the update checker

### Bandwidth Monitoring
- Each update check downloads ~5 KB of JSON
- Full updates download ~94 KB per ZIP
- Monitor server bandwidth if you have many users

### Backup Strategy
- Keep all previous ZIP versions on server
- Allows quick rollback if new version has issues
- Example: Keep last 3 versions (1.0.0, 1.1.0, 1.2.0)

---

## FAQ

**Q: Do users need to do anything?**
A: No. Updates are checked automatically every 12 hours. Users see a notification in WordPress admin and can click "Update Now".

**Q: Can I push updates instantly?**
A: Within 12 hours, yes. To force immediate check: WordPress admin → Dashboard → Updates → "Check Again"

**Q: What if I need to roll back?**
A: Update the JSON file to point to previous ZIP version. All installations will revert on next update check.

**Q: Does this require WordPress.org approval?**
A: No. This is a custom update system. You have full control and instant releases.

**Q: Can I require a license key for updates?**
A: Yes. Advanced feature. See "Advanced Enhancements" section in main plan document.

---

## Summary

**You've implemented:**
✅ Plugin Update Checker library (v5.6)
✅ Update checker initialization code
✅ JSON metadata file
✅ Initial plugin ZIP package

**Now you need to:**
1. Create `/public_html/plugins/gsap-animations/` directories on blu8print.com
2. Upload `update-info.json` and `gsap-scroll-animations-1.0.0.zip`
3. Verify both files are accessible via HTTPS
4. Test update detection in WordPress

**After server setup:**
- Plugin will check for updates automatically every 12 hours
- Users see update notifications in WordPress admin
- One-click updates from WordPress interface
- Professional update experience matching WordPress.org

Questions? Refer to troubleshooting section above.
