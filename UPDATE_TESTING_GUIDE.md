# GSAP Scroll Animations - Update System Testing Guide

## Pre-Flight Checks

Before testing, verify everything is in place:

### Local Files
```bash
# Verify library is installed
ls -la /Users/sebastiaan/Development/plugins/GSAP-Animations-COMPLETE/lib/plugin-update-checker/plugin-update-checker.php

# Verify plugin file has update checker code
grep -n "PucFactory" /Users/sebastiaan/Development/plugins/GSAP-Animations-COMPLETE/gsap-scroll-animations.php

# Verify JSON file exists
cat /Users/sebastiaan/Development/plugins/GSAP-Animations-COMPLETE/update-info.json

# Verify ZIP package exists
ls -lh /Users/sebastiaan/Development/plugins/gsap-scroll-animations-1.0.0.zip
```

### Expected Output:
- `plugin-update-checker.php` exists and is readable
- Lines 21-29 in plugin file contain update checker code
- `update-info.json` contains valid JSON with version "1.0.0"
- ZIP file is ~94 KB

---

## Server-Side Tests

### Test 1: Verify Directory Structure

**Command (via SSH or cPanel terminal):**
```bash
ls -la /public_html/plugins/gsap-animations/
```

**Expected output:**
```
drwxr-xr-x  2 user  group  4096 Feb 11 12:30 .
drwxr-xr-x  3 user  group  4096 Feb 11 12:30 ..
-rw-r--r--  1 user  group  1405 Feb 11 12:30 update-info.json
-rw-r--r--  1 user  group 94000 Feb 11 12:30 gsap-scroll-animations-1.0.0.zip
```

### Test 2: JSON File Accessibility

**Command:**
```bash
curl -v https://www.blu8print.com/plugins/gsap-animations/update-info.json
```

**Expected output:**
- Status: `200 OK`
- Content-Type: `application/json` (or plain text)
- JSON content displays completely
- No 404 or 403 errors

**In browser:**
Visit: `https://www.blu8print.com/plugins/gsap-animations/update-info.json`
- Should see JSON text
- Should NOT download the file

### Test 3: ZIP File Accessibility

**Command:**
```bash
curl -I https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.0.0.zip
```

**Expected output:**
```
HTTP/2 200
Content-Type: application/zip
Content-Length: 96256
```

**In browser:**
Visit: `https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.0.0.zip`
- Should trigger download
- File size should be ~94-96 KB

### Test 4: JSON Syntax Validation

**Command:**
```bash
curl -s https://www.blu8print.com/plugins/gsap-animations/update-info.json | python3 -m json.tool > /dev/null && echo "JSON is valid" || echo "JSON is invalid"
```

**Expected output:**
```
JSON is valid
```

**Or use online validator:**
1. Visit https://jsonlint.com
2. Paste content from: https://www.blu8print.com/plugins/gsap-animations/update-info.json
3. Should show "Valid JSON"

### Test 5: ZIP Structure Verification

**Command (download and verify):**
```bash
# Download the ZIP
wget -q https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.0.0.zip

# List contents
unzip -l gsap-scroll-animations-1.0.0.zip | head -20

# Verify structure (should have gsap-scroll-animations/ folder inside)
unzip -l gsap-scroll-animations-1.0.0.zip | grep "gsap-scroll-animations.php"
```

**Expected output:**
```
gsap-scroll-animations-1.0.0.zip
├── gsap-scroll-animations/
│   ├── gsap-scroll-animations.php
│   ├── lib/
│   ├── assets/
│   └── readme.txt
```

**Critical:** The PHP file must be INSIDE a folder with the plugin slug name.

---

## WordPress Plugin Tests

### Test 6: Plugin Installation (No Errors)

**Prerequisites:**
- WordPress site set up and running
- Admin access to WordPress dashboard

**Steps:**
1. **Deactivate any existing version** of the plugin
2. **Upload new version** via Plugins → Add New → Upload Plugin
3. **Select:** `/Users/sebastiaan/Development/plugins/GSAP-Animations-COMPLETE/` (or your dev copy)
4. **Activate plugin**

**Expected result:**
- Plugin activates without PHP errors
- No errors in `/wp-content/debug.log`
- Plugin listed on Plugins page

**Verify in debug log:**
```bash
tail -20 /path/to/wp-content/debug.log
```

### Test 7: Initial Update Detection (No Update)

**Scenario:** Version 1.0.0 installed, JSON shows 1.0.0 (should show NO update)

**Steps:**
1. **Ensure plugin version** in PHP file is `1.0.0`
2. **Ensure JSON version** is `1.0.0`
3. **Go to:** WordPress Admin → Plugins
4. **Find:** "GSAP Scroll Animations"

**Expected result:**
- No "Update available" text
- No version number indicator
- Plugin appears normal

### Test 8: Update Detection (Update Available)

**Scenario:** Version 1.0.0 installed, JSON shows 1.0.1 (should show update)

**Steps:**
1. **Keep plugin at** `1.0.0`
2. **Edit JSON on server:** Change `"version": "1.0.0"` to `"version": "1.0.1"`
3. **In WordPress:** Go to Dashboard → Updates
4. **Click:** "Check Again" button
5. **Wait:** 5-10 seconds for transients to clear
6. **Go to:** Plugins page

**Expected result:**
- Update notification appears in red/yellow
- Shows: "There is a new version of GSAP Scroll Animations available"
- Update link shows version "1.0.1"
- "View version 1.0.1 details" link available

**If not showing:**
1. **Clear transients:**
   ```php
   // Add to wp-config.php or use a plugin like "WP Control"
   delete_transients_by_prefix('puc_');
   delete_site_transients_by_prefix('puc_');
   ```
2. **Try Again:** Dashboard → Updates → "Check Again"

### Test 9: View Update Details Popup

**Steps:**
1. **On Plugins page**, find "GSAP Scroll Animations"
2. **Click:** "View version 1.0.1 details"

**Expected popup shows:**
- **Name:** "GSAP Scroll Animations"
- **Description:** From JSON `sections.description`
- **Changelog:** From JSON `sections.changelog`
- **Buttons:** "Install Update Now" and "Close"
- **Author:** "Blueprint 8"

**Verify content:**
- Changelog should be readable HTML
- Version "1.0.1" mentioned somewhere
- No JSON syntax visible (properly formatted)

### Test 10: View Installation Instructions

**In the same popup:**

**Steps:**
1. **Click tab or section** for "Installation"

**Expected result:**
- Shows installation instructions from JSON
- Readable and properly formatted
- No errors or broken HTML

---

## Advanced Testing

### Test 11: Debug Mode Verification

**Enable debug mode temporarily:**

**Edit:** `/Users/sebastiaan/Development/plugins/GSAP-Animations-COMPLETE/gsap-scroll-animations.php`

**Uncomment line 32:**
```php
$gsapUpdateChecker->debugMode = true;
```

**Steps:**
1. **Update plugin** in WordPress
2. **Go to:** Plugins page
3. **View page source** (Ctrl+U or Right-click → View Page Source)
4. **Search for:** "PUC Debug Info"

**Expected result:**
- Debug section appears in HTML comments
- Shows last check timestamp
- Shows update checker state
- Shows remote URL being checked
- No fatal errors

**After testing:** Re-comment line 32

### Test 12: Update Cache Behavior

**Steps:**
1. **Note current time** on Updates page
2. **Refresh page** (Ctrl+R) 5 times
3. **Check:** "Last checked" time
4. **Wait:** 1 minute
5. **Refresh** again

**Expected behavior:**
- Time stays same after multiple refreshes (cached)
- Transients working properly
- Cache expires appropriately

### Test 13: HTTPS/SSL Verification

**Command:**
```bash
# Test SSL certificate validity
echo | openssl s_client -servername www.blu8print.com -connect www.blu8print.com:443 2>&1 | grep -A 10 "Verify return code"
```

**Expected output:**
```
Verify return code: 0 (ok)
```

**If error:**
- SSL certificate may be invalid
- Check SSL in cPanel
- Ensure HTTPS works in browser

---

## Rollback Testing

### Test 14: Version Rollback

**Scenario:** Version 1.0.1 has issues, need to rollback to 1.0.0

**Steps:**
1. **Edit JSON on server**
2. **Change:** `"version": "1.0.1"` to `"version": "1.0.0"`
3. **Change:** `"download_url"` to point to 1.0.0 ZIP
4. **Update:** `"last_updated"` to current date
5. **In WordPress:** Dashboard → Updates → "Check Again"

**Expected result:**
- Installed version 1.0.1 shows as outdated
- Update to 1.0.0 appears available
- Users can click "Update" to downgrade
- Process completes successfully

---

## Performance Testing

### Test 15: Update Check Performance

**Command (measure check time):**
```bash
# Check how long update check takes
time curl -s https://www.blu8print.com/plugins/gsap-animations/update-info.json > /dev/null
```

**Expected result:**
- Response time: < 1 second
- File size: ~1.4 KB (minimal bandwidth)

### Test 16: Update Download Speed

**Command:**
```bash
# Measure download speed
time curl -O https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.0.0.zip
```

**Expected result:**
- Download time: < 5 seconds (on typical internet)
- File size: ~94 KB

---

## Error Handling Tests

### Test 17: Invalid JSON Response

**Simulate error:**
1. **On server**, temporarily break JSON syntax
   - Add extra comma, remove bracket, etc.
2. **In WordPress:** Dashboard → Updates → "Check Again"

**Expected behavior:**
- Update checker gracefully handles error
- No crash or fatal error
- Falls back to "no update available"
- Error logged in debug mode (if enabled)

**After testing:** Restore JSON to valid state

### Test 18: Missing ZIP File

**Simulate error:**
1. **On server**, temporarily rename ZIP file
   - `gsap-scroll-animations-1.0.0.zip` → `gsap-scroll-animations-1.0.0.zip.bak`
2. **In WordPress:** Show update available
3. **Click:** "Update Now"

**Expected behavior:**
- Update fails gracefully
- Helpful error message shown
- Plugin remains active at current version
- No broken installation

**After testing:** Restore ZIP file

### Test 19: Network Timeout

**Simulate slow network:**
1. **Enable slow 3G** in browser DevTools
2. **In WordPress:** Dashboard → Updates → "Check Again"

**Expected behavior:**
- Waits for response (up to 30 seconds)
- Successfully completes or times out gracefully
- No hanging or frozen interface
- Error messages clear (if timeout occurs)

---

## Cross-Browser Testing

### Test 20: Browser Compatibility

**Test update notifications in:**
- [ ] Chrome/Edge (Latest)
- [ ] Firefox (Latest)
- [ ] Safari (Latest)

**Steps:**
1. **Install plugin** in WordPress
2. **Go to:** Dashboard → Updates
3. **Click:** "Check Again"
4. **Observe:** Update notification (if available)

**Expected result:**
- Update appears consistently
- Styling correct in all browsers
- "Install Update Now" button works
- No JavaScript errors in console

---

## Cleanup & Reset

### After Testing

**Revert changes:**

1. **On server**, ensure JSON shows version `1.0.0`
2. **Uncomment debug mode** back in plugin file (if you enabled it)
3. **Verify** both files on server are still accessible
4. **Test** one final update check shows no updates available

**Final verification command:**
```bash
# Verify JSON is back to 1.0.0
curl -s https://www.blu8print.com/plugins/gsap-animations/update-info.json | grep '"version"'
```

**Expected output:**
```
"version": "1.0.0",
```

---

## Summary Checklist

**Server-side tests:**
- [ ] Directory exists: `/public_html/plugins/gsap-animations/`
- [ ] JSON file accessible via HTTPS
- [ ] ZIP file accessible and downloads correctly
- [ ] JSON syntax is valid
- [ ] ZIP structure is correct (has folder inside)

**WordPress tests:**
- [ ] Plugin installs without errors
- [ ] No update shows when versions match
- [ ] Update appears when JSON version is higher
- [ ] Update details popup displays correctly
- [ ] Changelog renders properly

**Advanced tests:**
- [ ] Debug mode shows info (optional)
- [ ] Caching works properly
- [ ] SSL/HTTPS certificate valid
- [ ] Error handling is graceful
- [ ] Works across browsers

**All tests passed?** ✅ Your update system is production-ready!

---

## Troubleshooting Test Failures

### Update never appears
1. Check JSON version is higher than plugin version
2. Verify `slug` in JSON matches exactly
3. Clear WordPress transients
4. Check /wp-content/debug.log for errors
5. Verify server URL is correct in plugin file

### ZIP won't download
1. Check ZIP file exists on server
2. Verify file permissions are 644
3. Test URL directly in browser
4. Check file isn't corrupted (run `unzip -t`)

### Plugin won't activate
1. Check PHP error log in cPanel
2. Verify library files are in correct directory
3. Check PHP version is 7.4+
4. Look for typos in require statement

### Update fails during install
1. Check WordPress has write permissions to plugins folder
2. Verify ZIP structure (must have folder inside)
3. Check server bandwidth/timeouts
4. Check WordPress memory limit in wp-config.php

---

## Support Resources

- **Plugin Update Checker:** https://github.com/YahnisElsts/plugin-update-checker
- **JSON Validator:** https://jsonlint.com
- **WordPress Plugin Development:** https://developer.wordpress.org/plugins/
- **SSL Certificate Test:** https://www.ssllabs.com/ssltest/

Good luck with your update system! 🚀
