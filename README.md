# GSAP Scroll Animations Plugin

A powerful WordPress plugin that provides two complementary animation systems using GSAP (GreenSock Animation Platform) and ScrollTrigger.

## Features

### ✨ ID-Based Animation System (Non-Technical Users)
- Configure animations via WordPress admin interface
- 5 basic animation types: Fade In, Slide Up, Slide Left, Slide Right, Scale In
- Customize duration, delay, and easing for each animation
- Perfect for users without coding experience

### 🚀 Class-Based Animation System (Developers)
- Add animations using CSS classes and data attributes
- 20+ advanced animation types:
  - **Parallax Effects**: Vertical, horizontal, diagonal
  - **Fade Animations**: Fade in all directions
  - **Scale & Zoom**: Growing and zooming effects
  - **Rotation & Flip**: 2D and 3D rotations
  - **Pinning & Sticky**: Pin sections and sticky effects
  - **Advanced Effects**: Stagger, text reveal, counters, progress bars, skew, blur
- Full customization via data attributes
- Mobile-friendly with built-in responsive handling

### 🎯 Intelligent Loading
- GSAP loads only when needed
- Smart detection of both animation systems
- Single GSAP instance even with both systems active
- Automatic CSS loading for class-based animations

### ♿ Accessibility
- Respects `prefers-reduced-motion` user preference
- Works perfectly with screen readers
- Semantic HTML structure

### 📱 Mobile Optimized
- Responsive animations
- Optional mobile animation disabling
- Performance optimized for all devices

## Installation

1. Upload the `GSAP-Animations` folder to `/wp-content/plugins/`
2. Activate the plugin in WordPress admin
3. Go to **Settings → GSAP Animations** to configure

## Quick Start

### Using ID-Based Animations (Admin UI)

1. Go to **Settings → GSAP Animations**
2. Click **+ Add Row**
3. Enter:
   - **HTML ID**: The element's ID (without #)
   - **Animation Type**: Choose from dropdown
   - **Duration**: Animation time in seconds
   - **Delay**: Delay before animation starts
   - **Easing**: Animation easing function
4. Add this to your page HTML:
```html
<div id="my-element">Content to animate</div>
```

### Using Class-Based Animations

1. Go to **Settings → GSAP Animations**
2. Check **"Enable Class-Based Animation System"**
3. Add animation classes to your HTML elements:

```html
<!-- Fade up animation -->
<div class="gsap-fade-up" data-distance="100" data-duration="1.2">
    Content fades in from below
</div>

<!-- Parallax effect -->
<div class="gsap-parallax" data-speed="-5">
    Background layer
</div>

<!-- Stagger animation -->
<div class="gsap-stagger" data-stagger="0.2">
    <p>First paragraph</p>
    <p>Second paragraph</p>
    <p>Third paragraph</p>
</div>
```

## Available Animation Classes

### Parallax Effects
- `gsap-parallax` - Vertical parallax (data-speed)
- `gsap-horizontal` - Horizontal parallax (data-hspeed)
- `gsap-diagonal` - Diagonal movement (data-speed, data-hspeed)

### Fade Animations
- `gsap-fade` - Simple fade in
- `gsap-fade-up` - Fade from bottom (data-distance)
- `gsap-fade-down` - Fade from top (data-distance)
- `gsap-fade-left` - Fade from left (data-distance)
- `gsap-fade-right` - Fade from right (data-distance)

### Scale & Zoom
- `gsap-scale` - Scale in animation (data-scale)
- `gsap-zoom` - Zoom on scroll (data-scale)

### Rotation & Flip
- `gsap-rotate` - Rotate on scroll (data-rotate)
- `gsap-flip` - 3D flip animation (data-flip)

### Pinning & Sticky
- `gsap-pin` - Pin section on scroll (data-pinstart, data-pinend)
- `gsap-sticky-fade` - Sticky with fade out

### Advanced Effects
- `gsap-stagger` - Stagger children (data-stagger, data-distance)
- `gsap-text-reveal` - Text reveal line by line
- `gsap-counter` - Animated counter (data-target, data-duration)
- `gsap-progress` - Progress bar fill (data-percent, data-duration)
- `gsap-skew` - Skew on scroll (velocity-based)
- `gsap-blur` - Blur on scroll
- `gsap-mobile-disable` - Disable animation on mobile

## Common Data Attributes

| Attribute | Purpose | Example |
|-----------|---------|---------|
| `data-duration` | Animation duration (seconds) | `data-duration="1.5"` |
| `data-delay` | Animation delay (seconds) | `data-delay="0.3"` |
| `data-distance` | Movement distance (pixels) | `data-distance="100"` |
| `data-speed` | Parallax speed | `data-speed="-5"` |
| `data-scale` | Scale factor | `data-scale="0.8"` |
| `data-rotate` | Rotation degrees | `data-rotate="360"` |
| `data-flip` | Flip angle | `data-flip="90"` |
| `data-stagger` | Stagger delay | `data-stagger="0.1"` |
| `data-target` | Counter target | `data-target="500"` |
| `data-percent` | Progress percentage | `data-percent="75"` |
| `data-start` | Custom scroll start | `data-start="top 80%"` |

## Plugin Structure

```
GSAP-Animations/
├── gsap-scroll-animations.php          (Main plugin file - 1100+ lines)
├── assets/
│   └── css/
│       └── gsap-scrolltrigger.css      (Animation styles)
├── README.md                            (This file)
├── IMPLEMENTATION_SUMMARY.md            (Technical documentation)
└── TESTING_GUIDE.md                     (Testing procedures)
```

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

**Note**: Animations gracefully degrade on older browsers that don't support modern JavaScript.

## Performance

- **Smart Loading**: GSAP loads only on pages with animations
- **Optimized CSS**: Uses `will-change` and GPU acceleration
- **Efficient Queries**: Uses GSAP's optimized DOM queries
- **No jQuery Required**: Pure JavaScript animation engine
- **Mobile Optimized**: Disables heavy animations on mobile when needed

## Accessibility

The plugin automatically:
- Respects `prefers-reduced-motion` CSS media query
- Skips animations for users with motion sensitivity
- Maintains full semantic HTML
- Works with screen readers

To test accessibility:
- **macOS**: System Preferences → Accessibility → Display → Reduce Motion
- **Windows**: Settings → Ease of Access → Display → Show Animations
- **Browser DevTools**: Emulate `prefers-reduced-motion: reduce`

## Troubleshooting

### Animations not playing
- Check if class-based system is enabled in admin
- Verify element has correct class name (case-sensitive)
- Check browser console for errors
- Ensure GSAP files are loading (check Network tab in DevTools)

### GSAP loading multiple times
- Verify only one instance of plugin is installed
- Check admin settings for duplicate configurations

### CSS not applied
- Verify CSS file path is correct
- Check browser Network tab for 404 errors
- Clear WordPress cache if using a caching plugin

### Mobile animations not working
- Add `gsap-mobile-disable` class if you want to disable on mobile
- Check viewport size (≤768px disables by default for some classes)
- Test with device emulation in DevTools

## JavaScript API

For advanced users, GSAP is available globally:

```javascript
// Check if GSAP is loaded
if (typeof gsap !== 'undefined') {
    // Create custom animations
    gsap.to('.my-element', {
        duration: 1,
        opacity: 1,
        scrollTrigger: {
            trigger: '.my-element',
            start: 'top 80%'
        }
    });
}
```

## Internationalization

Plugin text is translation-ready. To create translations:
1. Use PoEdit or similar tool
2. Target the `gsap-scroll-animations` text domain
3. Place `.mo` files in `/languages/` folder

## Support & Documentation

- **Admin Documentation**: Settings → GSAP Animations (see collapsible sections)
- **Technical Docs**: IMPLEMENTATION_SUMMARY.md
- **Testing Guide**: TESTING_GUIDE.md

## Version History

**1.0.0** (Current)
- Complete integration of ID-based and class-based animation systems
- 20+ animation effects
- Admin UI with documentation
- Full accessibility support
- Mobile optimization

## License

GPL v2 or later

## Author

The White Rabbit
https://thewhiterabbit.nl

---

**Built with GSAP v3.12.5** - https://greensock.com

For more information about GSAP and ScrollTrigger, visit the official documentation at https://greensock.com/docs/
