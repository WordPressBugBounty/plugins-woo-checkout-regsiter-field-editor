<?php
/**
 * Plugin Name:  Checkout Fields Manager (Checkout, My Account, Register)
 * Description: Customize WooCommerce checkout and my account page edit woocommerce checkout fields (Add, Edit, Delete and re-arrange fields). best checkout fields editor plugin for woocommerce.
 * Author:      Jcodex
 * Version:     2.3.4
 * Author URI:  https://www.jcodex.com
 * Plugin URI:  https://jcodex.com/plugins
 * Text Domain: jwcfe
 * Domain Path: /languages/
 * WC requires at least: 3.0.0
 * WC tested up to: 9.3.0
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

if (!function_exists('jwcfe_is_woocommerce_active')) {
    function jwcfe_is_woocommerce_active() {
        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
    }
}



add_action('before_woocommerce_init', 'jwcfe_before_woocommerce_hpos');
function jwcfe_before_woocommerce_hpos (){ 
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) { 
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 
            'custom_order_tables', __FILE__, true ); 
    }
}
    

if (jwcfe_is_woocommerce_active()) {
    define('JWCFE_VERSION', '3.5.0');
    !defined('JWCFE_BASE_NAME') && define('JWCFE_BASE_NAME', plugin_basename(__FILE__));
    !defined('JWCFE_PATH') && define('JWCFE_PATH', plugin_dir_path(__FILE__));
    !defined('JWCFE_URL') && define('JWCFE_URL', plugins_url('/', __FILE__));

    require_once JWCFE_PATH . 'includes/class-jwcfe.php';

    function run_jwcfe() {
        $plugin = new JWCFE();
    }
	run_jwcfe();
}
register_activation_hook( __FILE__, 'jwcfe_activate');
add_action( 'admin_init', 'jwcfe_activation_redirect');

/**
 * Plugin activation callback. Registers option to redirect on next admin load.
 */
function jwcfe_activate() {
	deactivate_plugins( '/woo-checkout-regsiter-field-editor-premium/woo-checkout-regsiter-field-editor-premium.php' );
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