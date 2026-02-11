<?php
/**
 * Plugin Name: GSAP Scroll Animations
 * Plugin URI: www.blu8print.com
 * Description: Simple GSAP scroll animations by HTML ID with accessibility support
 * Version: 1.1.2
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Tested up to: 6.7
 * Author: Blueprint 8
 * Author URI: www.blu8print.com
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// ============================================
// PLUGIN UPDATE CHECKER
// ============================================
// Include the update checker library
require __DIR__ . '/lib/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Initialize update checker - Using GitHub releases
$gsapUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/ilogic32/gsap-scroll-animations/',
    __FILE__,
    'gsap-scroll-animations'
);

// Optional: Enable update checker debug mode (uncomment to enable)
// $gsapUpdateChecker->debugMode = true;

// Set the branch to check for updates (optional, defaults to 'main')
$gsapUpdateChecker->setBranch('main');
// ============================================

class GSAP_Scroll_Animations {

    private $option_name = 'gsap_scroll_animations';

    public function __construct() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Enqueue GSAP on frontend
        add_action('wp_enqueue_scripts', array($this, 'enqueue_gsap'));

        // Output custom animations
        add_action('wp_footer', array($this, 'output_animations'));

        // Output class-based animations
        add_action('wp_footer', array($this, 'output_class_animations'));
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            'GSAP Scroll Animations',
            'GSAP Animations',
            'manage_options',
            'gsap-scroll-animations',
            array($this, 'render_admin_page')
        );
    }
    
    public function register_settings() {
        register_setting('gsap_scroll_animations_group', $this->option_name);
        register_setting('gsap_scroll_animations_group', $this->option_name . '_class_based_enabled');
    }

    /**
     * Determine if GSAP should be loaded based on configured animations
     */
    private function should_load_gsap() {
        // Check ID-based animations
        $id_animations = get_option($this->option_name, array());
        if (!empty($id_animations)) {
            return true;
        }

        // Check if class-based system is enabled
        $class_based_enabled = get_option($this->option_name . '_class_based_enabled', false);
        if ($class_based_enabled && is_singular()) {
            global $post;
            if (isset($post->post_content) && strpos($post->post_content, 'gsap-') !== false) {
                return true;
            }
        }

        return false;
    }

    public function enqueue_gsap() {
        // Only load if we have animations
        if (!$this->should_load_gsap()) {
            return;
        }

        // GSAP core
        wp_enqueue_script(
            'gsap',
            'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
            array(),
            '3.12.5',
            true
        );

        // ScrollTrigger plugin
        wp_enqueue_script(
            'gsap-scrolltrigger',
            'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js',
            array('gsap'),
            '3.12.5',
            true
        );

        // Enqueue CSS for class-based animations
        $class_based_enabled = get_option($this->option_name . '_class_based_enabled', false);
        if ($class_based_enabled) {
            wp_enqueue_style(
                'gsap-scrolltrigger-styles',
                plugin_dir_url(__FILE__) . 'assets/css/gsap-scrolltrigger.css',
                array(),
                '1.0.0'
            );
        }
    }
    
    public function output_animations() {
        $animations = get_option($this->option_name, array());
        
        if (empty($animations)) {
            return;
        }
        
        // Filter out empty rows
        $animations = array_filter($animations, function($anim) {
            return !empty($anim['id']) && !empty($anim['type']);
        });
        
        if (empty($animations)) {
            return;
        }
        
        // Animation configurations
        $configs = array(
            'fadeIn' => array(
                'opacity' => 0
            ),
            'fadeUp' => array(
                'opacity' => 0,
                'y' => 60
            ),
            'fadeDown' => array(
                'opacity' => 0,
                'y' => -60
            ),
            'fadeLeft' => array(
                'opacity' => 0,
                'x' => 60
            ),
            'fadeRight' => array(
                'opacity' => 0,
                'x' => -60
            ),
            'slideUp' => array(
                'opacity' => 0,
                'y' => 100
            ),
            'slideDown' => array(
                'opacity' => 0,
                'y' => -100
            ),
            'slideLeft' => array(
                'opacity' => 0,
                'x' => 100
            ),
            'slideRight' => array(
                'opacity' => 0,
                'x' => -100
            ),
            'scaleIn' => array(
                'opacity' => 0,
                'scale' => 0.8
            ),
            'zoomIn' => array(
                'opacity' => 0,
                'scale' => 1.2
            ),
            'rotateIn' => array(
                'opacity' => 0,
                'rotation' => 180
            ),
            'flipIn' => array(
                'opacity' => 0,
                'rotationY' => 90
            )
        );
        
        // Build JavaScript animations array
        $js_animations = array();
        foreach ($animations as $animation) {
            $id = sanitize_html_class($animation['id']);
            $type = sanitize_text_field($animation['type']);
            
            if (isset($configs[$type])) {
                $config = $configs[$type];
                
                // Add custom duration, delay, and easing
                $config['duration'] = isset($animation['duration']) ? floatval($animation['duration']) : 1;
                
                if (isset($animation['delay']) && floatval($animation['delay']) > 0) {
                    $config['delay'] = floatval($animation['delay']);
                }
                
                if (isset($animation['easing'])) {
                    $config['ease'] = sanitize_text_field($animation['easing']);
                }
                
                $js_animations[] = array(
                    'id' => $id,
                    'config' => $config
                );
            }
        }
        
        if (empty($js_animations)) {
            return;
        }
        
        $animations_json = json_encode($js_animations);
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Exit if GSAP not loaded or user prefers reduced motion
            if (typeof gsap === "undefined" || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
                return;
            }
            
            // Register ScrollTrigger
            gsap.registerPlugin(ScrollTrigger);
            
            // Animations configuration
            var animations = <?php echo $animations_json; ?>;
            
            // Apply animations
            animations.forEach(function(anim) {
                var element = document.getElementById(anim.id);
                if (element) {
                    var config = Object.assign({}, anim.config);
                    config.scrollTrigger = {
                        trigger: '#' + anim.id,
                        start: 'top 80%',
                        toggleActions: 'play none none none'
                    };
                    gsap.from('#' + anim.id, config);
                }
            });
        });
        </script>
        <?php
    }

    public function output_class_animations() {
        // Only if class-based is enabled
        $enabled = get_option($this->option_name . '_class_based_enabled', false);
        if (!$enabled) {
            return;
        }

        // Only on singular pages
        if (!is_singular()) {
            return;
        }

        // Check if page content contains gsap- classes
        global $post;
        if (!isset($post->post_content) || strpos($post->post_content, 'gsap-') === false) {
            return;
        }

        // Only output if GSAP is loaded
        if (!$this->should_load_gsap()) {
            return;
        }
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Exit if GSAP not loaded or user prefers reduced motion
            if (typeof gsap === 'undefined' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            gsap.registerPlugin(ScrollTrigger);

            // ============================================
            // PARALLAX EFFECTS
            // ============================================

            // Vertical parallax
            gsap.utils.toArray('.gsap-parallax').forEach((elem) => {
                const speed = elem.dataset.speed || -5;
                gsap.to(elem, {
                    yPercent: speed * 10,
                    ease: 'none',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top bottom',
                        end: 'bottom top',
                        scrub: true
                    }
                });
            });

            // Horizontal parallax
            gsap.utils.toArray('.gsap-horizontal').forEach((elem) => {
                const speed = elem.dataset.hspeed || 50;
                gsap.to(elem, {
                    xPercent: speed,
                    ease: 'none',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top bottom',
                        end: 'bottom top',
                        scrub: true
                    }
                });
            });

            // Diagonal movement (combined)
            gsap.utils.toArray('.gsap-diagonal').forEach((elem) => {
                const vSpeed = elem.dataset.speed || -5;
                const hSpeed = elem.dataset.hspeed || 5;

                const tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top bottom',
                        end: 'bottom top',
                        scrub: 1
                    }
                });

                tl.to(elem, {
                    yPercent: vSpeed * 10,
                    xPercent: hSpeed * 10,
                    ease: 'none'
                });
            });

            // ============================================
            // FADE ANIMATIONS
            // ============================================

            // Fade in from bottom
            gsap.utils.toArray('.gsap-fade-up').forEach((elem) => {
                gsap.from(elem, {
                    y: elem.dataset.distance || 60,
                    opacity: 0,
                    duration: elem.dataset.duration || 1,
                    delay: elem.dataset.delay || 0,
                    scrollTrigger: {
                        trigger: elem,
                        start: elem.dataset.start || 'top 85%',
                        toggleActions: 'play none none reverse'
                    }
                });
            });

            // Fade in from top
            gsap.utils.toArray('.gsap-fade-down').forEach((elem) => {
                gsap.from(elem, {
                    y: -(elem.dataset.distance || 60),
                    opacity: 0,
                    duration: elem.dataset.duration || 1,
                    delay: elem.dataset.delay || 0,
                    scrollTrigger: {
                        trigger: elem,
                        start: elem.dataset.start || 'top 85%',
                        toggleActions: 'play none none reverse'
                    }
                });
            });

            // Fade in from left
            gsap.utils.toArray('.gsap-fade-left').forEach((elem) => {
                gsap.from(elem, {
                    x: -(elem.dataset.distance || 60),
                    opacity: 0,
                    duration: elem.dataset.duration || 1,
                    delay: elem.dataset.delay || 0,
                    scrollTrigger: {
                        trigger: elem,
                        start: elem.dataset.start || 'top 85%',
                        toggleActions: 'play none none reverse'
                    }
                });
            });

            // Fade in from right
            gsap.utils.toArray('.gsap-fade-right').forEach((elem) => {
                gsap.from(elem, {
                    x: elem.dataset.distance || 60,
                    opacity: 0,
                    duration: elem.dataset.duration || 1,
                    delay: elem.dataset.delay || 0,
                    scrollTrigger: {
                        trigger: elem,
                        start: elem.dataset.start || 'top 85%',
                        toggleActions: 'play none none reverse'
                    }
                });
            });

            // Simple fade (no movement)
            gsap.utils.toArray('.gsap-fade').forEach((elem) => {
                gsap.from(elem, {
                    opacity: 0,
                    duration: elem.dataset.duration || 1.5,
                    delay: elem.dataset.delay || 0,
                    scrollTrigger: {
                        trigger: elem,
                        start: elem.dataset.start || 'top 85%',
                        toggleActions: 'play none none reverse'
                    }
                });
            });

            // ============================================
            // SCALE & ZOOM EFFECTS
            // ============================================

            // Scale in
            gsap.utils.toArray('.gsap-scale').forEach((elem) => {
                gsap.from(elem, {
                    scale: elem.dataset.scale || 0.8,
                    opacity: 0,
                    duration: elem.dataset.duration || 1,
                    scrollTrigger: {
                        trigger: elem,
                        start: elem.dataset.start || 'top 80%'
                    }
                });
            });

            // Zoom in on scroll
            gsap.utils.toArray('.gsap-zoom').forEach((elem) => {
                gsap.to(elem, {
                    scale: elem.dataset.scale || 1.2,
                    ease: 'none',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top bottom',
                        end: 'bottom top',
                        scrub: true
                    }
                });
            });

            // ============================================
            // ROTATION EFFECTS
            // ============================================

            // Rotate on scroll
            gsap.utils.toArray('.gsap-rotate').forEach((elem) => {
                const rotation = elem.dataset.rotate || 360;
                gsap.to(elem, {
                    rotation: rotation,
                    ease: 'none',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top bottom',
                        end: 'bottom top',
                        scrub: true
                    }
                });
            });

            // 3D flip
            gsap.utils.toArray('.gsap-flip').forEach((elem) => {
                gsap.from(elem, {
                    rotationY: elem.dataset.flip || 90,
                    opacity: 0,
                    duration: elem.dataset.duration || 1.2,
                    scrollTrigger: {
                        trigger: elem,
                        start: elem.dataset.start || 'top 80%'
                    }
                });
            });

            // ============================================
            // PINNING & STICKY
            // ============================================

            // Pin sections
            gsap.utils.toArray('.gsap-pin').forEach((elem) => {
                ScrollTrigger.create({
                    trigger: elem,
                    start: elem.dataset.pinstart || 'top top',
                    end: elem.dataset.pinend || 'bottom top',
                    pin: true,
                    pinSpacing: elem.dataset.pinspacing !== 'false'
                });
            });

            // Sticky with fade
            gsap.utils.toArray('.gsap-sticky-fade').forEach((elem) => {
                ScrollTrigger.create({
                    trigger: elem,
                    start: 'top top',
                    end: 'bottom top',
                    pin: true,
                    pinSpacing: false,
                    onUpdate: self => {
                        gsap.to(elem, {
                            opacity: 1 - self.progress,
                            duration: 0.1
                        });
                    }
                });
            });

            // ============================================
            // ADVANCED EFFECTS
            // ============================================

            // Stagger children animations
            gsap.utils.toArray('.gsap-stagger').forEach((elem) => {
                const children = gsap.utils.toArray(elem.children);

                // Set initial state
                gsap.set(children, {
                    opacity: 0,
                    y: elem.dataset.distance || 30
                });

                // Animate on scroll
                gsap.to(children, {
                    y: 0,
                    opacity: 1,
                    duration: elem.dataset.duration || 0.6,
                    stagger: elem.dataset.stagger || 0.1,
                    delay: elem.dataset.delay || 0,
                    scrollTrigger: {
                        trigger: elem,
                        start: elem.dataset.start || 'top 80%',
                        toggleActions: 'play none none reverse'
                    }
                });
            });

            // Text reveal (line by line)
            gsap.utils.toArray('.gsap-text-reveal').forEach((elem) => {
                const lines = elem.querySelectorAll('.line, p, h1, h2, h3, h4, h5, h6');
                gsap.from(lines, {
                    y: 100,
                    opacity: 0,
                    duration: 0.8,
                    stagger: 0.1,
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top 80%'
                    }
                });
            });

            // Counter animation
            gsap.utils.toArray('.gsap-counter').forEach((elem) => {
                const target = elem.dataset.target || 100;
                const duration = elem.dataset.duration || 2;
                const obj = { value: 0 };

                gsap.to(obj, {
                    value: target,
                    duration: duration,
                    ease: 'power1.inOut',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top 80%',
                        once: true
                    },
                    onUpdate: () => {
                        elem.textContent = Math.round(obj.value);
                    }
                });
            });

            // Progress bar fill
            gsap.utils.toArray('.gsap-progress').forEach((elem) => {
                const percent = elem.dataset.percent || 100;
                gsap.to(elem, {
                    width: percent + '%',
                    duration: elem.dataset.duration || 1.5,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top 85%'
                    }
                });
            });

            // Morph/Skew on scroll
            gsap.utils.toArray('.gsap-skew').forEach((elem) => {
                let proxy = { skew: 0 };
                gsap.to(proxy, {
                    skew: 0,
                    ease: 'none',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top bottom',
                        end: 'bottom top',
                        onUpdate: self => {
                            let velocity = self.getVelocity();
                            let skew = gsap.utils.clamp(-20, 20, velocity / -300);
                            if (Math.abs(skew) > Math.abs(proxy.skew)) {
                                proxy.skew = skew;
                                gsap.to(proxy, {
                                    skew: 0,
                                    duration: 0.8,
                                    ease: 'power3',
                                    overwrite: true,
                                    onUpdate: () => gsap.set(elem, { skewY: proxy.skew })
                                });
                            }
                        }
                    }
                });
            });

            // Blur on scroll
            gsap.utils.toArray('.gsap-blur').forEach((elem) => {
                gsap.to(elem, {
                    filter: 'blur(10px)',
                    ease: 'none',
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top bottom',
                        end: 'bottom top',
                        scrub: true
                    }
                });
            });

            // ============================================
            // MOBILE OPTIMIZATIONS
            // ============================================

            // Disable animations on mobile if needed
            if (window.innerWidth <= 768) {
                gsap.utils.toArray('.gsap-mobile-disable').forEach((elem) => {
                    ScrollTrigger.getAll().forEach(st => {
                        if (st.trigger === elem) {
                            st.kill();
                        }
                    });
                });
            }

            // Refresh on resize
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    ScrollTrigger.refresh();
                }, 250);
            });
        });
        </script>
        <?php
    }

    public function render_admin_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle form submission
        if (isset($_POST['gsap_animations_submit'])) {
            check_admin_referer('gsap_animations_save');

            $animations = array();

            if (isset($_POST['animation_id']) && is_array($_POST['animation_id'])) {
                foreach ($_POST['animation_id'] as $index => $id) {
                    if (!empty($id)) {
                        $duration = isset($_POST['animation_duration'][$index]) ? floatval($_POST['animation_duration'][$index]) : 1;
                        $delay = isset($_POST['animation_delay'][$index]) ? floatval($_POST['animation_delay'][$index]) : 0;
                        $easing = isset($_POST['animation_easing'][$index]) ? sanitize_text_field($_POST['animation_easing'][$index]) : 'power1.out';

                        $animations[] = array(
                            'id' => sanitize_text_field($id),
                            'type' => isset($_POST['animation_type'][$index]) ? sanitize_text_field($_POST['animation_type'][$index]) : 'fadeIn',
                            'duration' => max(0.1, $duration),
                            'delay' => max(0, $delay),
                            'easing' => $easing
                        );
                    }
                }
            }

            update_option($this->option_name, $animations);

            // Handle class-based toggle
            $class_based_enabled = isset($_POST['class_based_enabled']) ? true : false;
            update_option($this->option_name . '_class_based_enabled', $class_based_enabled);

            echo '<div class="notice notice-success is-dismissible"><p>Settings saved!</p></div>';
        }
        
        $animations = get_option($this->option_name, array());
        ?>
        <div class="wrap">
            <h1>GSAP Scroll Animations</h1>
            <p>Add HTML IDs and select animation types. Animations will trigger when elements scroll into view.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('gsap_animations_save'); ?>
                
                <table class="widefat" id="gsap-animations-table">
                    <thead>
                        <tr>
                            <th style="width: 25%">HTML ID (without #)</th>
                            <th style="width: 25%">Animation Type</th>
                            <th style="width: 12%">Duration (s)</th>
                            <th style="width: 12%">Delay (s)</th>
                            <th style="width: 18%">Easing</th>
                            <th style="width: 8%">Remove</th>
                        </tr>
                    </thead>
                    <tbody id="animation-rows">
                        <?php if (!empty($animations)): ?>
                            <?php foreach ($animations as $index => $animation): ?>
                                <tr>
                                    <td>
                                        <input type="text" 
                                               name="animation_id[]" 
                                               value="<?php echo esc_attr($animation['id']); ?>" 
                                               class="regular-text" 
                                               placeholder="e.g., hero-section">
                                    </td>
                                    <td>
                                        <select name="animation_type[]">
                                            <option value="fadeIn" <?php selected($animation['type'], 'fadeIn'); ?>>Fade In</option>
                                            <option value="fadeUp" <?php selected($animation['type'], 'fadeUp'); ?>>Fade Up</option>
                                            <option value="fadeDown" <?php selected($animation['type'], 'fadeDown'); ?>>Fade Down</option>
                                            <option value="fadeLeft" <?php selected($animation['type'], 'fadeLeft'); ?>>Fade Left</option>
                                            <option value="fadeRight" <?php selected($animation['type'], 'fadeRight'); ?>>Fade Right</option>
                                            <option value="slideUp" <?php selected($animation['type'], 'slideUp'); ?>>Slide Up</option>
                                            <option value="slideDown" <?php selected($animation['type'], 'slideDown'); ?>>Slide Down</option>
                                            <option value="slideLeft" <?php selected($animation['type'], 'slideLeft'); ?>>Slide Left</option>
                                            <option value="slideRight" <?php selected($animation['type'], 'slideRight'); ?>>Slide Right</option>
                                            <option value="scaleIn" <?php selected($animation['type'], 'scaleIn'); ?>>Scale In</option>
                                            <option value="zoomIn" <?php selected($animation['type'], 'zoomIn'); ?>>Zoom In</option>
                                            <option value="rotateIn" <?php selected($animation['type'], 'rotateIn'); ?>>Rotate In</option>
                                            <option value="flipIn" <?php selected($animation['type'], 'flipIn'); ?>>Flip In</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="animation_duration[]" 
                                               value="<?php echo esc_attr(isset($animation['duration']) ? $animation['duration'] : '1'); ?>" 
                                               step="0.1" 
                                               min="0.1"
                                               style="width: 70px;"
                                               placeholder="1">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="animation_delay[]" 
                                               value="<?php echo esc_attr(isset($animation['delay']) ? $animation['delay'] : '0'); ?>" 
                                               step="0.1" 
                                               min="0"
                                               style="width: 70px;"
                                               placeholder="0">
                                    </td>
                                    <td>
                                        <select name="animation_easing[]">
                                            <?php
                                            $easing = isset($animation['easing']) ? $animation['easing'] : 'power1.out';
                                            ?>
                                            <option value="power1.out" <?php selected($easing, 'power1.out'); ?>>Power1 Out</option>
                                            <option value="power2.out" <?php selected($easing, 'power2.out'); ?>>Power2 Out</option>
                                            <option value="power3.out" <?php selected($easing, 'power3.out'); ?>>Power3 Out</option>
                                            <option value="back.out" <?php selected($easing, 'back.out'); ?>>Back Out</option>
                                            <option value="elastic.out" <?php selected($easing, 'elastic.out'); ?>>Elastic Out</option>
                                            <option value="bounce.out" <?php selected($easing, 'bounce.out'); ?>>Bounce Out</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="button remove-row">Remove</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td>
                                    <input type="text" 
                                           name="animation_id[]" 
                                           value="" 
                                           class="regular-text" 
                                           placeholder="e.g., hero-section">
                                </td>
                                <td>
                                    <select name="animation_type[]">
                                        <option value="fadeIn">Fade In</option>
                                        <option value="fadeUp">Fade Up</option>
                                        <option value="fadeDown">Fade Down</option>
                                        <option value="fadeLeft">Fade Left</option>
                                        <option value="fadeRight">Fade Right</option>
                                        <option value="slideUp">Slide Up</option>
                                        <option value="slideDown">Slide Down</option>
                                        <option value="slideLeft">Slide Left</option>
                                        <option value="slideRight">Slide Right</option>
                                        <option value="scaleIn">Scale In</option>
                                        <option value="zoomIn">Zoom In</option>
                                        <option value="rotateIn">Rotate In</option>
                                        <option value="flipIn">Flip In</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number"
                                           name="animation_duration[]"
                                           value="1"
                                           step="0.1"
                                           min="0.1"
                                           style="width: 70px;"
                                           placeholder="1">
                                </td>
                                <td>
                                    <input type="number"
                                           name="animation_delay[]"
                                           value="0"
                                           step="0.1"
                                           min="0"
                                           style="width: 70px;"
                                           placeholder="0">
                                </td>
                                <td>
                                    <select name="animation_easing[]">
                                        <option value="power1.out">Power1 Out</option>
                                        <option value="power2.out">Power2 Out</option>
                                        <option value="power3.out">Power3 Out</option>
                                        <option value="back.out">Back Out</option>
                                        <option value="elastic.out">Elastic Out</option>
                                        <option value="bounce.out">Bounce Out</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="button remove-row">Remove</button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <p>
                    <button type="button" id="add-row" class="button">+ Add Row</button>
                </p>


                <!-- HTML ID Usage Guide -->
                <div style="background: #ffffff; border: 2px solid #353f6d; border-radius: 8px; margin-top: 40px; overflow: hidden;">
                    <!-- Header -->
                    <div style="background: #353f6d; color: white; padding: 25px 30px;">
                        <h2 style="margin: 0; color: white; font-size: 24px; font-weight: 600;">How to Use HTML ID Animations</h2>
                        <p style="margin: 10px 0 0 0; color: #ffffff; opacity: 0.95; font-size: 14px;">Simple, beginner-friendly guide to adding scroll animations</p>
                    </div>

                    <!-- Content -->
                    <div style="padding: 30px;">
                        <!-- Step-by-step Guide -->
                        <div style="margin-bottom: 35px;">
                            <h3 style="color: #353f6d; margin-top: 0; font-size: 20px; border-bottom: 2px solid #353f6d; padding-bottom: 10px;">Quick Start Guide</h3>

                            <div style="display: grid; gap: 20px; margin-top: 25px;">
                                <!-- Step 1 -->
                                <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; border-left: 4px solid #353f6d;">
                                    <div style="font-weight: 600; color: #353f6d; font-size: 16px; margin-bottom: 10px;">
                                        <span style="background: #353f6d; color: white; padding: 4px 10px; border-radius: 4px; margin-right: 10px;">1</span>
                                        Add an Animation in the Table Above
                                    </div>
                                    <p style="margin: 10px 0 10px 40px; color: #555; line-height: 1.6;">
                                        Fill in an HTML ID (like <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;">hero-section</code>), choose an animation type, and set your timing preferences.
                                    </p>
                                </div>

                                <!-- Step 2 -->
                                <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; border-left: 4px solid #353f6d;">
                                    <div style="font-weight: 600; color: #353f6d; font-size: 16px; margin-bottom: 10px;">
                                        <span style="background: #353f6d; color: white; padding: 4px 10px; border-radius: 4px; margin-right: 10px;">2</span>
                                        Add the ID to Your HTML Element
                                    </div>
                                    <p style="margin: 10px 0 10px 40px; color: #555; line-height: 1.6;">
                                        In your page builder or HTML editor, add the ID attribute to the element you want to animate:
                                    </p>
                                    <pre style="background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 6px; margin: 10px 0 0 40px; overflow-x: auto; font-size: 13px; line-height: 1.5;"><code>&lt;div id="hero-section"&gt;
    Your content here
&lt;/div&gt;</code></pre>
                                </div>

                                <!-- Step 3 -->
                                <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; border-left: 4px solid #353f6d;">
                                    <div style="font-weight: 600; color: #353f6d; font-size: 16px; margin-bottom: 10px;">
                                        <span style="background: #353f6d; color: white; padding: 4px 10px; border-radius: 4px; margin-right: 10px;">3</span>
                                        Save and Watch it Animate!
                                    </div>
                                    <p style="margin: 10px 0 0 40px; color: #555; line-height: 1.6;">
                                        Click "Save Settings" below, then view your page. The element will smoothly animate into view when users scroll to it.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Animation Types Reference -->
                        <div style="margin-bottom: 35px;">
                            <h3 style="color: #353f6d; font-size: 20px; border-bottom: 2px solid #353f6d; padding-bottom: 10px;">Available Animation Types</h3>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px;">
                                <!-- Fade Animations -->
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                                    <div style="font-weight: 600; color: #353f6d; margin-bottom: 8px; font-size: 14px;">Fade Effects</div>
                                    <ul style="margin: 0; padding-left: 20px; color: #555; font-size: 13px; line-height: 1.8;">
                                        <li>Fade In</li>
                                        <li>Fade Up</li>
                                        <li>Fade Down</li>
                                        <li>Fade Left</li>
                                        <li>Fade Right</li>
                                    </ul>
                                </div>

                                <!-- Slide Animations -->
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                                    <div style="font-weight: 600; color: #353f6d; margin-bottom: 8px; font-size: 14px;">Slide Effects</div>
                                    <ul style="margin: 0; padding-left: 20px; color: #555; font-size: 13px; line-height: 1.8;">
                                        <li>Slide Up</li>
                                        <li>Slide Down</li>
                                        <li>Slide Left</li>
                                        <li>Slide Right</li>
                                    </ul>
                                </div>

                                <!-- Special Effects -->
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                                    <div style="font-weight: 600; color: #353f6d; margin-bottom: 8px; font-size: 14px;">Special Effects</div>
                                    <ul style="margin: 0; padding-left: 20px; color: #555; font-size: 13px; line-height: 1.8;">
                                        <li>Scale In</li>
                                        <li>Zoom In</li>
                                        <li>Rotate In</li>
                                        <li>Flip In</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Timing Explained -->
                        <div style="margin-bottom: 35px;">
                            <h3 style="color: #353f6d; font-size: 20px; border-bottom: 2px solid #353f6d; padding-bottom: 10px;">Understanding Timing Settings</h3>

                            <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin-top: 20px;">
                                <div style="margin-bottom: 20px;">
                                    <div style="font-weight: 600; color: #353f6d; margin-bottom: 8px; font-size: 15px;">⏱️ Duration</div>
                                    <p style="margin: 0; color: #555; line-height: 1.6; font-size: 14px;">
                                        How long the animation takes to complete, in seconds. <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;">1</code> second is smooth and natural for most effects.
                                    </p>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <div style="font-weight: 600; color: #353f6d; margin-bottom: 8px; font-size: 15px;">⏲️ Delay</div>
                                    <p style="margin: 0; color: #555; line-height: 1.6; font-size: 14px;">
                                        Wait time before the animation starts, in seconds. Use <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;">0</code> to start immediately, or <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;">0.3</code> for a subtle delay.
                                    </p>
                                </div>

                                <div>
                                    <div style="font-weight: 600; color: #353f6d; margin-bottom: 8px; font-size: 15px;">📈 Easing</div>
                                    <p style="margin: 0; color: #555; line-height: 1.6; font-size: 14px;">
                                        Controls the animation's acceleration curve. <strong>Power1 Out</strong> is great for beginners—smooth start, gentle end. Try <strong>Back Out</strong> for a playful bounce effect!
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Example Showcase -->
                        <div style="margin-bottom: 20px;">
                            <h3 style="color: #353f6d; font-size: 20px; border-bottom: 2px solid #353f6d; padding-bottom: 10px;">Example: Animating a Hero Section</h3>

                            <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin-top: 20px;">
                                <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6; font-size: 14px;">
                                    <strong>In the table above, add:</strong>
                                </p>
                                <ul style="margin: 0 0 15px 0; color: #555; line-height: 1.8; padding-left: 25px; font-size: 14px;">
                                    <li>HTML ID: <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;">hero-section</code></li>
                                    <li>Animation Type: <strong>Fade Up</strong></li>
                                    <li>Duration: <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;">1.2</code> seconds</li>
                                    <li>Delay: <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px;">0.2</code> seconds</li>
                                    <li>Easing: <strong>Power2 Out</strong></li>
                                </ul>

                                <p style="margin: 0 0 10px 0; color: #555; line-height: 1.6; font-size: 14px;">
                                    <strong>Then in your HTML or page builder:</strong>
                                </p>
                                <pre style="background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 13px; line-height: 1.5; margin: 0;"><code>&lt;section id="hero-section"&gt;
    &lt;h1&gt;Welcome to Our Website&lt;/h1&gt;
    &lt;p&gt;This will fade up smoothly when it scrolls into view!&lt;/p&gt;
&lt;/section&gt;</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="background: #f8f9fa; padding: 20px 30px; border-top: 2px solid #e9ecef; text-align: center;">
                        <p style="margin: 0; color: #6c757d; font-size: 13px;">
                            Powered by <strong style="color: #353f6d;">Blueprint8</strong> •
                            <a href="https://www.blu8print.com" target="_blank" style="color: #353f6d; text-decoration: none; font-weight: 600;">www.blu8print.com</a>
                        </p>
                    </div>
                </div>

                <p class="submit">
                    <input type="submit" name="gsap_animations_submit" class="button button-primary" value="Save Settings">
                </p>
            </form>
        </div>
        
        <style>
            #gsap-animations-table {
                margin-top: 20px;
            }
            #gsap-animations-table th {
                padding: 10px;
            }
            #gsap-animations-table td {
                padding: 10px;
            }
            #add-row {
                margin-top: 10px;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Add new row
            $('#add-row').on('click', function() {
                var newRow = `
                    <tr>
                        <td>
                            <input type="text" 
                                   name="animation_id[]" 
                                   value="" 
                                   class="regular-text" 
                                   placeholder="e.g., hero-section">
                        </td>
                        <td>
                            <select name="animation_type[]">
                                <option value="fadeIn">Fade In</option>
                                <option value="fadeUp">Fade Up</option>
                                <option value="fadeDown">Fade Down</option>
                                <option value="fadeLeft">Fade Left</option>
                                <option value="fadeRight">Fade Right</option>
                                <option value="slideUp">Slide Up</option>
                                <option value="slideDown">Slide Down</option>
                                <option value="slideLeft">Slide Left</option>
                                <option value="slideRight">Slide Right</option>
                                <option value="scaleIn">Scale In</option>
                                <option value="zoomIn">Zoom In</option>
                                <option value="rotateIn">Rotate In</option>
                                <option value="flipIn">Flip In</option>
                            </select>
                        </td>
                        <td>
                            <input type="number"
                                   name="animation_duration[]"
                                   value="1"
                                   step="0.1"
                                   min="0.1"
                                   style="width: 70px;"
                                   placeholder="1">
                        </td>
                        <td>
                            <input type="number"
                                   name="animation_delay[]"
                                   value="0"
                                   step="0.1"
                                   min="0"
                                   style="width: 70px;"
                                   placeholder="0">
                        </td>
                        <td>
                            <select name="animation_easing[]">
                                <option value="power1.out">Power1 Out</option>
                                <option value="power2.out">Power2 Out</option>
                                <option value="power3.out">Power3 Out</option>
                                <option value="back.out">Back Out</option>
                                <option value="elastic.out">Elastic Out</option>
                                <option value="bounce.out">Bounce Out</option>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="button remove-row">Remove</button>
                        </td>
                    </tr>
                `;
                $('#animation-rows').append(newRow);
            });
            
            // Remove row
            $(document).on('click', '.remove-row', function() {
                if ($('#animation-rows tr').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    alert('You need at least one row.');
                }
            });
        });
        </script>
        <?php
    }

}

// Initialize plugin
new GSAP_Scroll_Animations();
