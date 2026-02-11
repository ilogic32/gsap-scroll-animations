# GSAP Scroll Animations - Installation Guide

## File Structure

```
GSAP-Animations/
├── gsap-scroll-animations.php              ⭐ Main plugin file (1100+ lines)
│
├── assets/
│   └── css/
│       └── gsap-scrolltrigger.css         ✅ Animation styles (part of plugin)
│
├── README.md                               📖 Complete documentation
├── readme.txt                              📋 WordPress plugin metadata
├── INSTALLATION.md                         📋 This file
├── IMPLEMENTATION_SUMMARY.md               🔧 Technical details
└── TESTING_GUIDE.md                        🧪 Testing procedures
```

## Installation Steps

### Step 1: Upload Files

**Using FTP/SFTP:**
1. Connect to your WordPress server via FTP
2. Navigate to `/wp-content/plugins/`
3. Upload the entire `GSAP-Animations` folder

**Using WordPress Admin:**
1. Go to Plugins → Add New
2. Click "Upload Plugin"
3. Choose the `GSAP-Animations` folder (zip it first)
4. Click "Install Now"

### Step 2: Activate Plugin

1. Go to WordPress Admin → Plugins
2. Find "GSAP Scroll Animations"
3. Click "Activate"

### Step 3: Configure Settings

1. Go to **Settings → GSAP Animations**
2. You'll see two sections:
   - **ID-Based Animations** (top) - for non-technical users
   - **Class-Based Animation System** (bottom) - for developers

## What's Included

### Main Plugin File
- **gsap-scroll-animations.php** (1100+ lines)
  - Plugin initialization and configuration
  - ID-based animation system
  - Class-based animation system with 20+ effects
  - Admin interface with documentation
  - WordPress hooks and actions
  - All necessary PHP and JavaScript

### CSS File
- **assets/css/gsap-scrolltrigger.css**
  - Animation-specific styles
  - Performance optimizations
  - Mobile responsive styles
  - Z-index management
  - Transform and will-change properties

### Documentation
- **README.md** - Complete user guide
- **readme.txt** - WordPress plugin standard
- **INSTALLATION.md** - This file
- **IMPLEMENTATION_SUMMARY.md** - Technical documentation
- **TESTING_GUIDE.md** - Testing procedures

## Verification

After installation, verify everything is working:

### 1. Check Plugin Activation
- Go to Plugins page
- "GSAP Scroll Animations" should show "Deactivate" button

### 2. Check Admin Page
- Go to Settings → GSAP Animations
- Should see both ID-based and class-based sections

### 3. Check CSS Loading
- Go to any page
- Open DevTools → Network tab
- Enable class-based system in admin
- Create a test page with `<div class="gsap-fade-up">Test</div>`
- You should see `gsap-scrolltrigger.css` loading

### 4. Check GSAP Loading
- In DevTools Network tab:
  - Look for `gsap.min.js` from jsDelivr
  - Look for `ScrollTrigger.min.js` from jsDelivr
  - Both should load on pages with animations

## Database

The plugin uses WordPress options to store configuration:

- `gsap_scroll_animations` - Array of ID-based animations
- `gsap_scroll_animations_class_based_enabled` - Boolean toggle

These are automatically created when you first save settings.

## File Permissions

Ensure proper permissions:
```bash
chmod 644 gsap-scroll-animations.php
chmod 755 assets/
chmod 755 assets/css/
chmod 644 assets/css/gsap-scrolltrigger.css
chmod 644 *.md
chmod 644 *.txt
```

## Troubleshooting Installation

### Plugin doesn't appear in admin
- Check file permissions
- Verify folder is in `/wp-content/plugins/`
- Check WordPress error logs

### "Cannot activate plugin" error
- Check PHP version (requires 7.4+)
- Check WordPress version (requires 5.0+)
- Look for fatal errors in error log

### Settings page doesn't load
- Clear browser cache
- Check console for JavaScript errors
- Verify WordPress is loading jQuery (required for admin)

### CSS not loading
- Check file exists: `/wp-content/plugins/GSAP-Animations/assets/css/gsap-scrolltrigger.css`
- Check permissions on assets folder
- Verify `assets/css/` folder structure

## Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **JavaScript**: Enabled in browser
- **Browser**: Modern browser (Chrome, Firefox, Safari, Edge)

## What GSAP Files Are Loaded?

The plugin loads GSAP from jsDelivr CDN (v3.12.5):
- `gsap.min.js` - Core GSAP library
- `ScrollTrigger.min.js` - ScrollTrigger plugin

These are only loaded on pages that use animations (smart loading).

## One-Click Setup

After activation, here's the minimal setup:

**For ID-Based (Admin UI):**
1. Go to Settings → GSAP Animations
2. Click "+ Add Row"
3. Enter ID: `my-element`
4. Select Animation Type: "Fade In"
5. Click "Save Settings"
6. Add to page: `<div id="my-element">Content</div>`

**For Class-Based (HTML):**
1. Go to Settings → GSAP Animations
2. Check "Enable Class-Based Animation System"
3. Click "Save Settings"
4. Add to page: `<div class="gsap-fade-up">Content</div>`

Done! Animations should work immediately.

## Updating the Plugin

To update:
1. Back up your WordPress database
2. Delete the old `GSAP-Animations` folder
3. Upload the new `GSAP-Animations` folder
4. Re-activate if needed

Your settings are stored in the database, so they'll be preserved.

## Uninstalling the Plugin

To completely remove:
1. Go to Plugins page
2. Click "Deactivate"
3. Click "Delete"
4. Delete from server via FTP

Note: This doesn't delete database entries. To clean them up:
```sql
DELETE FROM wp_options WHERE option_name LIKE 'gsap_scroll_animations%';
```

## Support

- Check **Settings → GSAP Animations** for built-in documentation
- Read **README.md** for detailed features
- See **TESTING_GUIDE.md** for testing procedures
- Review **IMPLEMENTATION_SUMMARY.md** for technical info

## Next Steps

After installation:
1. Review [README.md](README.md) for complete documentation
2. Check [TESTING_GUIDE.md](TESTING_GUIDE.md) for testing procedures
3. View the admin page for built-in documentation
4. Start adding animations to your pages!

---

**Ready to animate?** Go to Settings → GSAP Animations and get started!
