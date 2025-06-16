<?php
/**
 * Plugin Name: Customer Appreciation Perks
 * Description: A custom WooCommerce plugin that enhances the checkout experience by displaying a branded freebie popup.
 * Version: 1.0
 * Author: CMITEXPERTS TEAM
 * Author URI: www.cmitexperts.com
 * Text Domain: woo-cmitcap
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 4.0
 * WC tested up to: 6.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('CMITCAP_VERSION', '1.0');
define('CMITCAP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CMITCAP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CMITCAP_TEXT_DOMAIN', 'woo-cmitcap');

// Check if WooCommerce is active
function cmitcap_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'cmitcap_woocommerce_notice');
        return false;
    }
    return true;
}

// WooCommerce missing notice
function cmitcap_woocommerce_notice() {
    ?>
    <div class="error">
        <p><?php _e('Customer Appreciation Perks requires WooCommerce to be installed and active.', CMITCAP_TEXT_DOMAIN); ?></p>
    </div>
    <?php
}

// Enqueue scripts and styles
function cmitcap_enqueue_scripts() {
    if (!cmitcap_check_woocommerce()) {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style('cmitcap-style', CMITCAP_PLUGIN_URL . 'assets/css/cmitcap.css', array(), CMITCAP_VERSION);
    
    // Enqueue JS
    wp_enqueue_script('cmitcap-script', CMITCAP_PLUGIN_URL . 'assets/js/cmitcap.js', array('jquery'), CMITCAP_VERSION, true);
    
    // Localize script
    wp_localize_script('cmitcap-script', 'cmitcapData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cmitcap-nonce')
    ));

    // Enqueue Swiper CSS
    wp_enqueue_style('swiper-style', CMITCAP_PLUGIN_URL . 'assets/css/swiper.min.css', array(), CMITCAP_VERSION);
    // Enqueue Swiper JS
    wp_enqueue_script('swiper-script', CMITCAP_PLUGIN_URL . 'assets/js/swiper.min.js', array(), CMITCAP_VERSION, true);
}
add_action('wp_enqueue_scripts', 'cmitcap_enqueue_scripts');

// Create the settings menu
function cmitcap_create_menu() {
    add_menu_page(
        __('Customer Appreciation Perks', CMITCAP_TEXT_DOMAIN),
        __('Customer Perks', CMITCAP_TEXT_DOMAIN),
        'manage_options',
        'cmitcap-settings',
        'cmitcap_settings_page',
        'dashicons-gift',
        56
    );
}
add_action('admin_menu', 'cmitcap_create_menu');

// Settings page callback
function cmitcap_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings
    if (isset($_POST['cmitcap_save_settings'])) {
        check_admin_referer('cmitcap_settings_nonce');
        
        $settings = array(
            'enabled' => isset($_POST['cmitcap_enabled']) ? 'yes' : 'no',
            'expiration_date' => sanitize_text_field($_POST['cmitcap_expiration_date']),
            'selected_categories' => isset($_POST['cmitcap_categories']) ? array_map('sanitize_text_field', $_POST['cmitcap_categories']) : array(),
            'freebie_products' => isset($_POST['cmitcap_freebie_products']) ? array_map('sanitize_text_field', $_POST['cmitcap_freebie_products']) : array()
        );
        
        update_option('cmitcap_settings', $settings);
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', CMITCAP_TEXT_DOMAIN) . '</p></div>';
    }

    // Get current settings
    $settings = get_option('cmitcap_settings', array(
        'enabled' => 'no',
        'expiration_date' => '',
        'selected_categories' => array(),
        'freebie_products' => array()
    ));

    // Include settings template
    include CMITCAP_PLUGIN_DIR . 'templates/admin-settings.php';
}

// Helper: Get freebie product IDs
function cmitcap_get_freebie_ids() {
    $settings = get_option('cmitcap_settings');
    return !empty($settings['freebie_products']) ? $settings['freebie_products'] : array();
}

// Helper: Check if freebie is in cart
function cmitcap_cart_has_freebie() {
    $freebie_ids = cmitcap_get_freebie_ids();
    foreach (WC()->cart->get_cart() as $cart_item) {
        if (in_array($cart_item['product_id'], $freebie_ids)) {
            return $cart_item['product_id'];
        }
    }
    return false;
}

// Only show popup if no freebie in cart
function cmitcap_display_popup() {
    if (!is_checkout() || !cmitcap_check_woocommerce()) {
        return;
    }
    $settings = get_option('cmitcap_settings');
    if ($settings['enabled'] !== 'yes') {
        return;
    }
    // Check expiration date
    if (!empty($settings['expiration_date'])) {
        $expiration = strtotime($settings['expiration_date']);
        if (time() > $expiration) {
            return;
        }
    }
    // Check if freebie is in cart
    if (cmitcap_cart_has_freebie()) {
        return;
    }
    include CMITCAP_PLUGIN_DIR . 'templates/popup.php';
}
add_action('wp_footer', 'cmitcap_display_popup');

// Remove freebie from cart via query arg
function cmitcap_maybe_remove_freebie() {
    if (isset($_GET['remove_freebie']) && is_checkout()) {
        $freebie_id = intval($_GET['remove_freebie']);
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $freebie_id) {
                WC()->cart->remove_cart_item($cart_item_key);
                wc_add_notice(__('Perk removed. You can pick another!', CMITCAP_TEXT_DOMAIN), 'notice');
                wp_safe_redirect(wc_get_checkout_url());
                exit;
            }
        }
    }
}
add_action('template_redirect', 'cmitcap_maybe_remove_freebie');

// Add remove link to freebie in checkout
function cmitcap_checkout_freebie_remove_link($item_name, $cart_item, $cart_item_key) {
    $freebie_ids = cmitcap_get_freebie_ids();
    if (in_array($cart_item['product_id'], $freebie_ids) && is_checkout()) {
        $remove_url = add_query_arg('remove_freebie', $cart_item['product_id'], wc_get_checkout_url());
        $item_name .= ' <a href="' . esc_url($remove_url) . '" class="cmitcap-remove-freebie" style="color:#d00;">' . __('Remove Perk', CMITCAP_TEXT_DOMAIN) . '</a>';
    }
    return $item_name;
}
add_filter('woocommerce_cart_item_name', 'cmitcap_checkout_freebie_remove_link', 10, 3);

// Add selected freebie item to cart
function cmitcap_add_freebie_to_cart() {
    check_ajax_referer('cmitcap-nonce', 'nonce');

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if ($product_id > 0) {
        $cart_item_key = WC()->cart->add_to_cart($product_id, 1);
        
        if ($cart_item_key) {
            wp_send_json_success(array(
                'message' => __('Perk added to cart successfully!', CMITCAP_TEXT_DOMAIN)
            ));
        }
    }

    wp_send_json_error(array(
        'message' => __('Failed to add perk to cart.', CMITCAP_TEXT_DOMAIN)
    ));
}
add_action('wp_ajax_cmitcap_add_freebie', 'cmitcap_add_freebie_to_cart');
add_action('wp_ajax_nopriv_cmitcap_add_freebie', 'cmitcap_add_freebie_to_cart');

// Activation hook
function cmitcap_activate() {
    if (!cmitcap_check_woocommerce()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires WooCommerce to be installed and active.', CMITCAP_TEXT_DOMAIN));
    }

    // Create default settings
    $default_settings = array(
        'enabled' => 'no',
        'expiration_date' => '',
        'selected_categories' => array(),
        'freebie_products' => array()
    );
    
    add_option('cmitcap_settings', $default_settings);
}
register_activation_hook(__FILE__, 'cmitcap_activate');

// Deactivation hook
function cmitcap_deactivate() {
    // Cleanup if needed
}
register_deactivation_hook(__FILE__, 'cmitcap_deactivate'); 