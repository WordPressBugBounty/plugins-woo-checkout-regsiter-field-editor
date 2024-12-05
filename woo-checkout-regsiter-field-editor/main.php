<?php
/**
 * Plugin Name: Checkout Field Manager for WooCommerce (My Account, Register)
 * Description: Customize WooCommerce checkout and my account page edit woocommerce checkout fields (Add, Edit, Delete and re-arrange fields). best checkout fields editor plugin for woocommerce.
 * Author:      Jcodex
 * Version:     2.3.5
 * Author URI:  https://www.jcodex.com
 * Plugin URI:  https://jcodex.com/plugins
 * Text Domain: jwcfe
 * Domain Path: /languages/
 * WC requires at least: 3.0.0
 * WC tested up to: 9.4.2
 *
 * Copyright (C) 2018-2024 Jcodex Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
// Create a helper function for easy SDK access.

if (!defined('ABSPATH')) {
    exit;
}
// Avoid defining constants if they are already defined.
if (!defined('JWCFE_VERSION')) {
    define('JWCFE_VERSION', '3.5.2');
}

if (!defined('JWCFE_BASE_NAME')) {
    define('JWCFE_BASE_NAME', plugin_basename(__FILE__));
}

if (!defined('JWCFE_PATH')) {
    define('JWCFE_PATH', plugin_dir_path(__FILE__));
}

if (!defined('JWCFE_URL')) {
    define('JWCFE_URL', plugins_url('/', __FILE__));
}


    register_activation_hook( __FILE__, 'jwcfe_activate');
    add_action( 'admin_init', 'jwcfe_activation_redirect');

    /**
     * Plugin activation callback. Registers option to redirect on next admin load.
     */

    function jwcfe_activate() {

        if (is_plugin_active('woo-checkout-regsiter-field-editor-pro/main.php')) {
            deactivate_plugins('woo-checkout-regsiter-field-editor-pro/main.php');
        }
        
        add_option( 'jwcfe_activation_redirect', true );
    }


    function jwcfe_activation_redirect() {
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            if (get_option('jwcfe_activation_redirect', false)) {
                delete_option('jwcfe_activation_redirect');
                wp_safe_redirect(admin_url('admin.php?page=jwcfe_checkout_register_editor'));
                exit;
            }
        }
    }

    if (jwcfe_is_woocommerce_active()) {

        if (!class_exists('JWCFE')) {
            require_once JWCFE_PATH . 'includes/class-jwcfe.php';
        }
        if (!function_exists('run_jwcfe')) {
            function run_jwcfe() {
                $plugin = new JWCFE();
            }
        }
        run_jwcfe();
    }


    function jwcfe_is_woocommerce_active() {
        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
    }



    add_action('before_woocommerce_init', function () {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    });
    

function plugin_add_get_pro_button($links) {
    
    $pro_url = 'https://jcodex.com/plugins/woocommerce-custom-checkout-field-editor/';
    $pro_link = '<a href="' . esc_url($pro_url) . '" style="color: #46b450; font-weight: bold;" target="_blank">' . __('Get Pro', 'jwcfe') . '</a>';
    $links[] = $pro_link;

    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'plugin_add_get_pro_button');
