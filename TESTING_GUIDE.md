# GSAP Scroll Animations - Testing Guide

## Quick Start Testing

### 1. Enable Class-Based Animations

1. Go to **WordPress Admin → Settings → GSAP Animations**
2. Scroll down to **Class-Based Animation System** section
3. Check the box: **"☑ Enable Class-Based Animation System"**
4. Click **Save Settings**

### 2. Test Fade-Up Animation

Create a test page with this HTML:

```html
<div class="gsap-fade-up" data-distance="100" data-duration="1.2">
    <h2>This content fades in from below</h2>
    <p>Scroll down to see the animation trigger</p>
</div>
```

**Expected Result:**
- Element is invisible initially (opacity: 0)
- When element comes into view (top 85% of viewport)
- Element fades in and moves up 100px
- Animation takes 1.2 seconds

### 3. Test Parallax Effect

Create this HTML:

```html
<div class="gsap-parallax" data-speed="-5">
    <img src="background.jpg" alt="Background">
</div>
```

**Expected Result:**
- As you scroll, the element moves slower than scroll speed
- Creates a depth/layering effect
- Adjust `data-speed` for more/less parallax (-10 is more extreme)

### 4. Test Stagger Animation

Create this HTML:

```html
<div class="gsap-stagger" data-stagger="0.2" data-duration="0.6">
    <p>First item</p>
    <p>Second item</p>
    <p>Third item</p>
</div>
```

**Expected Result:**
- Each paragraph appears one after another
- 0.2 second delay between each
- Each animation takes 0.6 seconds

### 5. Test Both Systems Together

1. Keep class-based enabled
2. Go to **Settings → GSAP Animations** → ID-based section
3. Add an ID-based animation:
   - ID: `test-element`
   - Type: `slideUp`
   - Duration: `1`
4. Create a test page with both:

```html
<div id="test-element">
    <h2>ID-Based Animation (Slide Up)</h2>
</div>

<div class="gsap-fade-up" data-distance="100">
    <h2>Class-Based Animation (Fade Up)</h2>
</div>
```

**Expected Result:**
- Both animations work correctly
- No conflicts between systems
- GSAP loads only once

### 6. Verify GSAP Loads Only Once

1. Go to your test page
2. Open **Chrome DevTools → Network tab**
3. Filter by "gsap"
4. Look for:
   - `gsap.min.js` - should load once
   - `ScrollTrigger.min.js` - should load once
   - `gsap-scrolltrigger.css` - should load once (if class-based enabled)

**Expected Result:**
- Each file loads exactly once, not multiple times
- Files load from CDN (jsDelivr)
- CSS loads from plugin directory

### 7. Test Accessibility (Prefers Reduced Motion)

**On macOS:**
1. Go to **System Preferences → Accessibility → Display**
2. Enable "Reduce motion"
3. Refresh your test page

**On Windows:**
1. Go to **Settings → Ease of Access → Display**
2. Turn on "Show animations"
3. Refresh your test page

**Expected Result:**
- Animations don't play
- Content is still visible
- Page is still functional

### 8. Test Mobile View

1. Open **Chrome DevTools**
2. Toggle **Device Toolbar** (Ctrl+Shift+M)
3. Set viewport to ≤768px (e.g., iPhone 12)
4. Navigate to page with animations

**Expected Result:**
- Regular animations still work
- Elements with `gsap-mobile-disable` class don't animate
- Page is responsive

### 9. Test Admin UI

1. Go to **Settings → GSAP Animations**
2. Scroll to "Available Animation Classes" section
3. Click on category headings (e.g., "Parallax Effects")

**Expected Result:**
- Category expands showing table of animations
- Table shows: Class Name | Effect | Data Attributes
- Icon changes from ▼ to ▲
- Clicking again collapses the section

### 10. Test Conditional Loading

1. **Disable both systems:**
   - Remove all ID-based animations
   - Disable class-based toggle

2. Go to any page
3. Open DevTools → Network
4. Look for GSAP files

**Expected Result:**
- GSAP files don't load
- Page loads normally without animations

### Advanced Test Cases

#### Test Custom Data Attributes

```html
<!-- Custom duration and delay -->
<div class="gsap-fade-up" data-duration="2.5" data-delay="0.5" data-distance="200">
    Slower fade from farther down
</div>

<!-- Custom easing with rotation -->
<div class="gsap-rotate" data-rotate="720" data-duration="2">
    Spins 720 degrees
</div>

<!-- Counter animation -->
<div class="gsap-counter" data-target="500" data-duration="3">
    0
</div>

<!-- Progress bar -->
<div class="gsap-progress" data-percent="75" data-duration="2"></div>
```

#### Test Text Reveal

```html
<div class="gsap-text-reveal" data-duration="0.8" data-start="top 80%">
    <h2>This text reveals line by line</h2>
    <p>Each paragraph animates separately</p>
    <p>Creates an engaging text entry effect</p>
</div>
```

#### Test Pin/Sticky

```html
<div class="gsap-pin" data-pinstart="top top" data-pinend="bottom top">
    <h2>This section pins to viewport</h2>
    <p>As you scroll, this stays in place</p>
</div>
```

### Browser Console Debugging

Copy/paste into browser console to check:

```javascript
// Check if GSAP is loaded
console.log('GSAP loaded:', typeof gsap !== 'undefined');

// Check if ScrollTrigger is registered
console.log('ScrollTrigger available:', typeof ScrollTrigger !== 'undefined');

// List all active ScrollTrigger instances
if (typeof ScrollTrigger !== 'undefined') {
    console.log('Active triggers:', ScrollTrigger.getAll().length);
    ScrollTrigger.getAll().forEach((trigger, i) => {
        console.log(`Trigger ${i}:`, trigger.trigger.className);
    });
}

// Check for reduced motion preference
console.log('Prefers reduced motion:',
    window.matchMedia('(prefers-reduced-motion: reduce)').matches);

// Test animation on specific element
gsap.to('.gsap-fade-up', {
    duration: 1,
    opacity: 1,
    scrollTrigger: {
        trigger: '.gsap-fade-up',
        start: 'top center'
    }
});
```

### Common Issues & Solutions

**Issue: Animations not playing**
- Check if class-based is enabled in admin
- Verify element has correct class name (case-sensitive)
- Open DevTools → check for JavaScript errors
- Make sure GSAP files are loading

**Issue: GSAP loads multiple times**
- Check Network tab to confirm duplicates
- This indicates multiple calls to `wp_enqueue_script()`
- Verify `should_load_gsap()` is being used

**Issue: CSS not loading**
- Check Network tab for CSS file
- Verify plugin directory path is correct
- Check browser console for 404 errors

**Issue: Animations stutter on scroll**
- Check if ScrollTrigger.refresh() is being called
- This auto-refreshes on resize in the code
- Try disabling other plugins for conflict testing

**Issue: Mobile animations still playing**
- Add `gsap-mobile-disable` class to element
- Or implement custom media query logic in JavaScript

### Performance Testing

1. Open **Chrome DevTools → Performance**
2. Record a 5-second video while scrolling through animated elements
3. Look at:
   - Frame rate (should be 60fps)
   - CPU/memory usage
   - Long tasks (should be minimal)

**Good Performance Signs:**
- Smooth 60fps animation
- No frame drops during scroll
- CPU usage under 50%
- Memory stable (not continuously growing)

### Rollback Instructions

If issues arise:

1. **To disable class-based system:**
   - Admin → Settings → GSAP Animations → Uncheck "Enable Class-Based..."
   - ID-based animations will still work

2. **To restore original plugin:**
   - Delete `gsap-scroll-animations.php`
   - Upload backup version
   - ID-based animations will be restored

3. **Database cleaning (if needed):**
   ```sql
   DELETE FROM wp_options WHERE option_name = 'gsap_scroll_animations_class_based_enabled';
   ```

## Checklist for Production

Before deploying to production:

- [ ] Test all 6 major animation types (fade, parallax, scale, rotate, pin, stagger)
- [ ] Verify GSAP loads only on pages with animations
- [ ] Test accessibility (prefers-reduced-motion)
- [ ] Test mobile responsiveness
- [ ] Test both systems working together
- [ ] Performance: ensure 60fps on scroll
- [ ] Test in multiple browsers (Chrome, Firefox, Safari)
- [ ] Verify admin UI is intuitive
- [ ] Check CSS is loading from correct path
- [ ] Documentation is complete and accurate
