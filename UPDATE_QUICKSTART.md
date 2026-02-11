# GSAP Scroll Animations - Update System Quick Start

## TL;DR - Get This Done in 15 Minutes

### What You Have (Ready Now)
✅ Plugin with update checker integrated
✅ `update-info.json` metadata file
✅ `gsap-scroll-animations-1.0.0.zip` package

### What You Need to Do (3 Simple Steps)

---

## Step 1: Upload Files to blu8print.com (5 min)

**Via cPanel File Manager:**

1. Create folder structure: `/public_html/plugins/gsap-animations/`
2. Upload two files:
   - Source: `/Users/sebastiaan/Development/plugins/GSAP-Animations-COMPLETE/update-info.json`
   - Destination: `https://www.blu8print.com/plugins/gsap-animations/update-info.json`

   - Source: `/Users/sebastiaan/Development/plugins/gsap-scroll-animations-1.0.0.zip`
   - Destination: `https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.0.0.zip`

3. Set permissions: `644` on both files

---

## Step 2: Verify Server Access (5 min)

**Test in browser:**
```
✓ https://www.blu8print.com/plugins/gsap-animations/update-info.json
  → Should display JSON text (not download)

✓ https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.0.0.zip
  → Should trigger file download
```

**Or via command line:**
```bash
curl -I https://www.blu8print.com/plugins/gsap-animations/update-info.json
curl -I https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.0.0.zip
```

---

## Step 3: Test in WordPress (5 min)

1. Install plugin from your dev directory
2. Activate plugin
3. Go to WordPress Admin → Dashboard → Updates
4. Click "Check Again"
5. **Expected:** No updates available (versions match: 1.0.0 = 1.0.0)

**Done!** Your update system is live. ✅

---

## Files to Keep Handy

| File | Purpose |
|------|---------|
| `UPDATE_SYSTEM_SETUP.md` | Detailed server setup (screenshots included) |
| `UPDATE_TESTING_GUIDE.md` | 20 test procedures for verification |
| `UPDATE_IMPLEMENTATION_COMPLETE.md` | Full status report and reference |
| `UPDATE_QUICKSTART.md` | This file - quick reference |

---

## Releasing Version 1.1.0 (or any future version)

### Step-by-step:

```bash
# 1. Update version in plugin file
# Edit: /Users/sebastiaan/Development/plugins/GSAP-Animations-COMPLETE/gsap-scroll-animations.php
# Change line 6: * Version: 1.1.0

# 2. Create new ZIP
cd /Users/sebastiaan/Development/plugins
mv GSAP-Animations-COMPLETE gsap-scroll-animations
zip -r gsap-scroll-animations-1.1.0.zip gsap-scroll-animations/ \
  -x "*.DS_Store" "__MACOSX/*" "*.md" "ANIMATION-*.html"
mv gsap-scroll-animations GSAP-Animations-COMPLETE

# 3. Upload ZIP to server
# FTP/cPanel: gsap-scroll-animations-1.1.0.zip
# → /public_html/plugins/gsap-animations/gsap-scroll-animations-1.1.0.zip

# 4. Update JSON on server
# Edit: /public_html/plugins/gsap-animations/update-info.json
# Change: "version": "1.0.0" → "version": "1.1.0"
# Change: "download_url": "...1.0.0.zip" → "...1.1.0.zip"
# Update: "last_updated" timestamp
# Add: New version to "changelog"

# 5. Done! Users will see update within 12 hours
```

---

## One-Page Reference

### JSON File Structure
```json
{
  "version": "1.0.0",              ← Increment for new releases
  "download_url": "https://...zip", ← Points to latest ZIP
  "slug": "gsap-scroll-animations", ← Don't change
  "last_updated": "2026-02-11...",  ← Update on release
  "sections": {
    "changelog": "<h4>1.0.0</h4><ul>..."  ← Add release notes
  }
}
```

### Plugin Version Check
```php
// Line 6 in gsap-scroll-animations.php
* Version: 1.0.0    ← Must match or be LESS than JSON version
```

### Update Check Logic
- **Installed:** 1.0.0, JSON: 1.0.0 → No update
- **Installed:** 1.0.0, JSON: 1.0.1 → Update available ✓
- **Installed:** 1.0.0, JSON: 0.9.9 → No update (won't downgrade)

---

## Troubleshooting

### "Update not showing"
```bash
# Check JSON is valid
curl -s https://www.blu8print.com/plugins/gsap-animations/update-info.json

# Check version is higher
# grep "version" result

# Clear WordPress cache and try again
# WordPress Admin → Dashboard → Updates → "Check Again"
```

### "JSON shows 404"
```bash
# Verify file exists
ls -la /public_html/plugins/gsap-animations/update-info.json

# Check permissions
# Should be: -rw-r--r-- (644)

# Test URL
curl -v https://www.blu8print.com/plugins/gsap-animations/update-info.json
```

### "ZIP won't download"
```bash
# Verify file exists and is valid
unzip -t gsap-scroll-animations-1.0.0.zip

# Check it's in correct location
ls -la /public_html/plugins/gsap-animations/*.zip

# Test URL
curl -O https://www.blu8print.com/plugins/gsap-animations/gsap-scroll-animations-1.0.0.zip
```

---

## File Locations Reference

```
LOCAL DEVELOPMENT:
/Users/sebastiaan/Development/plugins/GSAP-Animations-COMPLETE/
├── gsap-scroll-animations.php        (main plugin)
├── lib/plugin-update-checker/        (update library)
├── update-info.json                  (metadata - upload)
└── UPDATE_*.md                       (documentation)

PLUGIN PACKAGE:
/Users/sebastiaan/Development/plugins/
└── gsap-scroll-animations-1.0.0.zip (upload this)

SERVER (AFTER SETUP):
https://www.blu8print.com/
└── public_html/
    └── plugins/
        └── gsap-animations/
            ├── update-info.json
            └── gsap-scroll-animations-1.0.0.zip
```

---

## Update System Features

✅ **Automatic Checking** - Every 12 hours
✅ **One-Click Updates** - From WordPress admin
✅ **Update Notifications** - On Plugins page
✅ **Changelog Display** - In popup details
✅ **Version Comparison** - Smart update logic
✅ **Error Handling** - Graceful fallback
✅ **Caching** - Efficient transients
✅ **Security** - HTTPS enforced
✅ **Control** - No approval needed
✅ **Instant Release** - Live immediately

---

## After Setup Checklist

- [ ] Files uploaded to blu8print.com
- [ ] JSON URL responds with content
- [ ] ZIP URL triggers download
- [ ] Plugin installs without errors
- [ ] No update shows (v1.0.0 = v1.0.0)
- [ ] Test update detection works
- [ ] Documentation saved for reference
- [ ] Release procedure documented

**All done? Your update system is production-ready!** 🚀

---

## Support

**Full guides available:**
- **Server Setup:** `UPDATE_SYSTEM_SETUP.md`
- **Testing:** `UPDATE_TESTING_GUIDE.md`
- **Reference:** `UPDATE_IMPLEMENTATION_COMPLETE.md`

**External Resources:**
- Plugin Update Checker: https://github.com/YahnisElsts/plugin-update-checker
- JSON Validator: https://jsonlint.com
- WordPress Plugins: https://developer.wordpress.org/plugins/
