=== GSAP Scroll Animations ===
Contributors: thewhiterabbit
Tags: gsap, animations, scroll-trigger, scroll-animations, effects
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 6.6
Stable tag: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Two powerful animation systems: ID-based admin UI for non-technical users, and class-based system for developers with 20+ animation effects.

== Description ==

GSAP Scroll Animations is a comprehensive WordPress plugin that brings professional scroll-based animations to your website. It provides two complementary animation systems designed for different user skill levels:

**For Non-Technical Users (ID-Based System)**
- Configure animations directly in WordPress admin
- No coding required
- 5 animation types: Fade In, Slide Up, Slide Left, Slide Right, Scale In
- Customize duration, delay, and easing

**For Developers (Class-Based System)**
- Add animations using CSS classes and data attributes
- 20+ advanced animation types
- Full control via HTML data attributes
- Includes: Parallax, Fade, Scale, Rotate, Pin, Stagger, Text Reveal, Counter, Progress Bar, Skew, Blur

**Smart Features**
- Intelligent GSAP loading (loads only when needed)
- Works seamlessly with both animation systems
- Full accessibility support (respects prefers-reduced-motion)
- Mobile optimized
- Performance focused

== Installation ==

1. Upload the `GSAP-Animations` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to Settings → GSAP Animations to configure

== Quick Start ==

**ID-Based (Admin UI)**
1. Go to Settings → GSAP Animations
2. Click "+ Add Row"
3. Enter HTML ID, select animation type
4. Add `<div id="my-element">Content</div>` to your page

**Class-Based (HTML)**
1. Enable class-based system in admin
2. Add animation class to HTML: `<div class="gsap-fade-up">Content</div>`
3. Customize with data attributes: `data-duration="1.2"`

== Features ==

= Animation Types =
* Parallax (vertical, horizontal, diagonal)
* Fade (all directions)
* Scale & Zoom
* Rotation & 3D Flip
* Pinning & Sticky Elements
* Stagger (children animation)
* Text Reveal (line by line)
* Counter (animated numbers)
* Progress Bars
* Skew (velocity-based)
* Blur (scroll-based)

= Accessibility =
* Respects prefers-reduced-motion
* WCAG compliant
* Semantic HTML structure
* Screen reader friendly

= Performance =
* Smart conditional loading
* GPU-accelerated animations
* Optimized for all devices
* Mobile-friendly

= Admin Features =
* Intuitive settings interface
* Collapsible documentation
* Live preview support
* Easy animation management

== Frequently Asked Questions ==

= Do I need coding knowledge? =
No! The ID-based system is completely configurable from the WordPress admin. The class-based system is for developers who want more control.

= Will this slow down my site? =
No. GSAP only loads on pages that actually use animations. The plugin includes performance optimizations and GPU acceleration.

= Does it work on mobile? =
Yes! The plugin is fully responsive. You can optionally disable animations on mobile devices using the `gsap-mobile-disable` class.

= What about accessibility? =
The plugin fully supports accessibility. It respects the `prefers-reduced-motion` user preference and works with screen readers.

= Can I use both animation systems? =
Yes! Both ID-based and class-based animations work together perfectly. GSAP loads only once even with both systems active.

= What browsers are supported? =
Modern browsers (Chrome, Firefox, Safari, Edge). Graceful degradation on older browsers.

== Screenshots ==

1. Admin Settings Page - ID-Based Animation Configuration
2. Admin Settings Page - Class-Based Animation Documentation
3. Class-Based Animation Examples with Data Attributes
4. Collapsible Animation Documentation

== Changelog ==

= 1.0.0 =
* Initial release
* Complete integration of ID-based and class-based animation systems
* 20+ animation effects
* Comprehensive admin UI with documentation
* Full accessibility support
* Mobile optimization
* Smart GSAP loading

== Upgrade Notice ==

= 1.0.0 =
Initial release of the complete GSAP Scroll Animations plugin with both ID-based and class-based animation systems.

== Technical Details ==

= Requirements =
* WordPress 5.0+
* PHP 7.4+
* Modern browser with JavaScript support

= GSAP Version =
Uses GSAP 3.12.5 from jsDelivr CDN

= Files =
* gsap-scroll-animations.php - Main plugin (1100+ lines)
* assets/css/gsap-scrolltrigger.css - Animation styles
* README.md - Full documentation
* IMPLEMENTATION_SUMMARY.md - Technical details
* TESTING_GUIDE.md - Testing procedures

== Plugin Support ==

For detailed documentation:
1. Check the plugin's admin settings page (Settings → GSAP Animations)
2. Read README.md included with the plugin
3. Review TESTING_GUIDE.md for testing procedures

== License ==

This plugin is licensed under the GPL v2 or later.

== Credits ==

Built with GSAP (GreenSock Animation Platform) - https://greensock.com/
Uses ScrollTrigger plugin - https://greensock.com/scrolltrigger/

Author: The White Rabbit
Website: https://thewhiterabbit.nl
