<?php
/**
 * Plugin Name: GSAP Scroll Animations
 * Plugin URI: www.blu8print.com
 * Description: Simple GSAP scroll animations by HTML ID with accessibility support
 * Version: 1.0.0
 * Author: Blueprint 8
 * Author URI: www.blu8print.com
 * License: GPL v2 or later
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

// Initialize update checker
$gsapUpdateChecker = PucFactory::buildUpdateChecker(
    'https://blu8print.com/plugin-updates/gsap-animations/update-info.json',
    __FILE__,
    'gsap-scroll-animations'
);

// Optional: Enable update checker debug mode (uncomment to enable)
// $gsapUpdateChecker->debugMode = true;
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

                <hr style="margin-top: 40px; margin-bottom: 30px;">

                <h2>Class-Based Animation System</h2>
                <p>Enable CSS class-based animations for more advanced effects. Add classes like <code>gsap-fade-up</code>, <code>gsap-parallax</code>, etc. to your HTML elements.</p>

                <div style="background: #f5f5f5; padding: 15px; border-left: 4px solid #0073aa; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="class_based_enabled" value="1" <?php checked(get_option($this->option_name . '_class_based_enabled'), true); ?>>
                        <strong>Enable Class-Based Animation System</strong>
                    </label>
                    <p style="margin-top: 10px; margin-bottom: 0; font-size: 13px; color: #666;">
                        When enabled, animations with CSS classes will load GSAP only on pages containing these classes.
                    </p>
                </div>

                <h3>Available Animation Classes</h3>
                <p style="color: #666;">Click on category headings to see available classes and their data attributes:</p>

                <div style="background: #fff; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px;">
                    <!-- Parallax Effects -->
                    <div style="border-bottom: 1px solid #ddd;">
                        <button type="button" class="class-category-toggle" style="width: 100%; text-align: left; padding: 12px 15px; background: #f9f9f9; border: none; cursor: pointer; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
                            <span>Parallax Effects</span>
                            <span class="toggle-icon">▼</span>
                        </button>
                        <div class="class-category-content" style="display: none; padding: 15px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 180px;"><code>gsap-parallax</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Vertical parallax effect</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 200px;"><code>data-speed="-5"</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>gsap-horizontal</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Horizontal parallax</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>data-hspeed="50"</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px;"><code>gsap-diagonal</code></td>
                                    <td style="padding: 8px;">Diagonal movement</td>
                                    <td style="padding: 8px;"><code>data-speed</code>, <code>data-hspeed</code></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Fade Animations -->
                    <div style="border-bottom: 1px solid #ddd;">
                        <button type="button" class="class-category-toggle" style="width: 100%; text-align: left; padding: 12px 15px; background: #f9f9f9; border: none; cursor: pointer; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
                            <span>Fade Animations</span>
                            <span class="toggle-icon">▼</span>
                        </button>
                        <div class="class-category-content" style="display: none; padding: 15px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 180px;"><code>gsap-fade</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Simple fade in</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 200px;"><code>data-duration</code>, <code>data-delay</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>gsap-fade-up</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Fade from bottom</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>data-distance="60"</code>, <code>data-duration</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>gsap-fade-down</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Fade from top</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>data-distance="60"</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>gsap-fade-left</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Fade from left</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>data-distance="60"</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px;"><code>gsap-fade-right</code></td>
                                    <td style="padding: 8px;">Fade from right</td>
                                    <td style="padding: 8px;"><code>data-distance="60"</code></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Scale & Zoom -->
                    <div style="border-bottom: 1px solid #ddd;">
                        <button type="button" class="class-category-toggle" style="width: 100%; text-align: left; padding: 12px 15px; background: #f9f9f9; border: none; cursor: pointer; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
                            <span>Scale & Zoom</span>
                            <span class="toggle-icon">▼</span>
                        </button>
                        <div class="class-category-content" style="display: none; padding: 15px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 180px;"><code>gsap-scale</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Scale in animation</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 200px;"><code>data-scale="0.8"</code>, <code>data-duration</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px;"><code>gsap-zoom</code></td>
                                    <td style="padding: 8px;">Zoom on scroll</td>
                                    <td style="padding: 8px;"><code>data-scale="1.2"</code></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Rotation & Flip -->
                    <div style="border-bottom: 1px solid #ddd;">
                        <button type="button" class="class-category-toggle" style="width: 100%; text-align: left; padding: 12px 15px; background: #f9f9f9; border: none; cursor: pointer; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
                            <span>Rotation & Flip</span>
                            <span class="toggle-icon">▼</span>
                        </button>
                        <div class="class-category-content" style="display: none; padding: 15px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 180px;"><code>gsap-rotate</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Rotate on scroll</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 200px;"><code>data-rotate="360"</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px;"><code>gsap-flip</code></td>
                                    <td style="padding: 8px;">3D flip animation</td>
                                    <td style="padding: 8px;"><code>data-flip="90"</code>, <code>data-duration</code></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Pinning & Sticky -->
                    <div style="border-bottom: 1px solid #ddd;">
                        <button type="button" class="class-category-toggle" style="width: 100%; text-align: left; padding: 12px 15px; background: #f9f9f9; border: none; cursor: pointer; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
                            <span>Pinning & Sticky</span>
                            <span class="toggle-icon">▼</span>
                        </button>
                        <div class="class-category-content" style="display: none; padding: 15px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 180px;"><code>gsap-pin</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Pin section on scroll</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 200px;"><code>data-pinstart</code>, <code>data-pinend</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px;"><code>gsap-sticky-fade</code></td>
                                    <td style="padding: 8px;">Sticky with fade out</td>
                                    <td style="padding: 8px;">No additional data attributes</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Advanced Effects -->
                    <div>
                        <button type="button" class="class-category-toggle" style="width: 100%; text-align: left; padding: 12px 15px; background: #f9f9f9; border: none; cursor: pointer; font-weight: 500; display: flex; justify-content: space-between; align-items: center;">
                            <span>Advanced Effects</span>
                            <span class="toggle-icon">▼</span>
                        </button>
                        <div class="class-category-content" style="display: none; padding: 15px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 180px;"><code>gsap-stagger</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Stagger children animations</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee; width: 200px;"><code>data-stagger="0.1"</code>, <code>data-distance</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>gsap-text-reveal</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Text reveal line by line</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Reveals <code>p</code>, <code>h1-h6</code> tags</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>gsap-counter</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Animated counter</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>data-target="100"</code>, <code>data-duration</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>gsap-progress</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Progress bar fill</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>data-percent="100"</code>, <code>data-duration</code></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>gsap-skew</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Skew on scroll</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Skew based on scroll velocity</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;"><code>gsap-blur</code></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Blur on scroll</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">Creates blur effect during scroll</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px;"><code>gsap-mobile-disable</code></td>
                                    <td style="padding: 8px;">Disable on mobile</td>
                                    <td style="padding: 8px;">Add to prevent animation on screens ≤768px</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <h3 style="margin-top: 30px;">Usage Example</h3>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px;"><code>&lt;!-- Fade up with custom distance and duration --&gt;
&lt;div class="gsap-fade-up" data-distance="100" data-duration="1.2" data-delay="0.3"&gt;
    Content fades in from below
&lt;/div&gt;

&lt;!-- Parallax effect --&gt;
&lt;div class="gsap-parallax" data-speed="-5"&gt;
    Background moves slower
&lt;/div&gt;

&lt;!-- Stagger animation --&gt;
&lt;div class="gsap-stagger" data-stagger="0.2"&gt;
    &lt;p&gt;First paragraph&lt;/p&gt;
    &lt;p&gt;Second paragraph&lt;/p&gt;
    &lt;p&gt;Third paragraph&lt;/p&gt;
&lt;/div&gt;</code></pre>

                <!-- Demo Files Section -->
                <hr style="margin-top: 40px; margin-bottom: 30px;">

                <h2>📚 Animation Resources</h2>

                <!-- Animation Examples Reference -->
                <div style="background: #f5f5f5; padding: 20px; border-left: 4px solid #0073aa; margin-bottom: 20px;">
                    <h3 style="margin-top: 0;">📖 Animation Examples & Code Reference</h3>
                    <p>Complete reference guide with all animation classes, code examples, and data attributes.</p>
                    <a href="<?php echo esc_url(plugin_dir_url(__FILE__) . 'ANIMATION-EXAMPLES.html'); ?>" target="_blank" class="button button-secondary" style="margin-top: 10px;">
                        Open Examples Guide →
                    </a>
                </div>

                <!-- Live Animation Demo -->
                <div style="background: #f5f5f5; padding: 20px; border-left: 4px solid #00a32a; margin-bottom: 20px;">
                    <h3 style="margin-top: 0;">🎬 Live Animation Demo</h3>
                    <p>See all 20+ animations in action with working scroll effects.</p>
                    <a href="<?php echo esc_url(plugin_dir_url(__FILE__) . 'ANIMATION-DEMO.html'); ?>" target="_blank" class="button button-secondary" style="margin-top: 10px;">
                        Open Live Demo →
                    </a>
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

            // Toggle class category sections
            $('.class-category-toggle').on('click', function() {
                $(this).next('.class-category-content').slideToggle(300);
                var toggleIcon = $(this).find('.toggle-icon');
                if (toggleIcon.text() === '▼') {
                    toggleIcon.text('▲');
                } else {
                    toggleIcon.text('▼');
                }
            });
        });
        </script>
        <?php
    }

}

// Initialize plugin
new GSAP_Scroll_Animations();
