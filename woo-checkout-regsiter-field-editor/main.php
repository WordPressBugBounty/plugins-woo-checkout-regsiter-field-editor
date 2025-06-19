<?php
/**
 * Plugin Name: Checkout Field Editor for Woocommerce - Checkout Manager
 * Description: Customize WooCommerce checkout and my account page edit woocommerce checkout fields (Add, Edit, Delete and re-arrange fields). best checkout fields editor plugin for woocommerce.
 * Author:      Jcodex
 * Version:     2.4.6
 * Author URI:  https://www.jcodex.com
 * Plugin URI:  https://www.jcodex.com
 * Text Domain: jwcfe
 * Domain Path: /languages/
 * WC requires at least: 3.0.0
 * WC tested up to: 9.8.2
 * Requires Plugins:  woocommerce
 *
 * Copyright (C) 2018-2025 Jcodex Inc.
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
    define('JWCFE_VERSION', '2.4.6');
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

        if (!class_exists( 'WooCommerce' )) {
            deactivate_plugins( JWCFE_BASE_NAME );
            wp_die( __( "WooCommerce is required for this plugin to work properly. Please activate WooCommerce.", 'jwcfe' ), "", array( 'back_link' => 1 ) );
        }
        
        if (is_plugin_active('woo-checkout-regsiter-field-editor-pro/main.php')) {
            deactivate_plugins('woo-checkout-regsiter-field-editor-pro/main.php');
        }
        
        add_option( 'jwcfe_activation_redirect', true );
    }
    
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'jwcfe_add_plugin_action_links');
    function jwcfe_add_plugin_action_links($links) {
        // Add Settings link
        $settings_url = admin_url('admin.php?page=jwcfe_checkout_register_editor');
        $settings_link = '<a href="' . esc_url($settings_url) . '">' . __('Settings', 'jwcfe') . '</a>';
        
        // Add Get Pro link
        $pro_url = 'https://jcodex.com/plugins/woocommerce-custom-checkout-field-editor/';
        $pro_link = '<a href="' . esc_url($pro_url) . '" style="color: #46b450; font-weight: bold;" target="_blank">' . __('Upgrade to Pro', 'jwcfe') . '</a>';
    
        // Prepend custom links to the beginning
        array_unshift($links, $settings_link, $pro_link);
    
        return $links;
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
    

            
        
        
    // Show feedback popup on plugin deactivation

    add_action('admin_enqueue_scripts', 'jwcfe_deactivation_feedback_scripts');
    
    function jwcfe_deactivation_feedback_scripts($hook) {
        if ($hook !== 'plugins.php') return;
    
        wp_enqueue_script('jquery');
    
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                const pluginSlug = "' . esc_js(plugin_basename(__FILE__)) . '";
                let deactivateLink = "";
    
                $("tr[data-plugin=\'' . esc_js(plugin_basename(__FILE__)) . '\'] .deactivate a").on("click", function(e) {
                    e.preventDefault();
                    deactivateLink = $(this).attr("href");
    
                    if ($("#jwcfe-feedback-popup").length === 0) {
                        $("body").append(`
                            <div id="jwcfe-feedback-popup" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:min(95%,520px);background:#fff;padding:1.5em;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.15);z-index:99999;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,sans-serif;width:48%">
                                <style>
                                    .jwcfe-modal-header {margin:0 0 1em;padding-bottom:1em;border-bottom:1px solid #ddd}
                                    .jwcfe-modal-title {font-size:1.3em;color:#1d2327;margin:0}
                                    .jwcfe-form-group {margin-bottom:0.8em}
                                    .jwcfe-radio-label {display:flex;align-items:flex-start;gap:8px;padding:8px;border-radius:4px;transition:background 0.2s;cursor:pointer;font-size:14px}
                                    .jwcfe-radio-label:hover {background:#f6f7f7}
                                    .jwcfe-radio-input {
                                        width:16px;
                                        height:16px;
                                        accent-color:#3858e9;
                                        margin:0;
                                        margin-top:4px !important;
                                        flex-shrink:0;
                                    }
                                    .jwcfe-textarea {
                                        width:100%;
                                        padding:10px;
                                        border:1px solid #ddd;
                                        border-radius:4px;
                                        min-height:90px;
                                        resize:vertical;
                                        font-size:14px;
                                        margin-top:8px;
                                    }
                                    .jwcfe-textarea::placeholder{
                                    color:#9f9fa0;
                                    }
                                    .jwcfe-textarea:focus {outline:none;border-color:#3858e9;box-shadow:0 0 0 2px rgba(56,88,233,0.1)}
                                    .modal-footer {
                                        display: flex;
                                        justify-content: space-between;
                                        align-items: center;
                                        margin-top: 20px;
                                        padding-top: 15px;
                                        border-top: 1px solid #ddd;
                                    }
                                    .jwcfd-left { margin-right: auto; }
                                    .jwcfd-right { display: flex; gap: 10px; }
                                    .jwcfd-link {
                                        text-decoration: none;
                                        padding: 8px 12px;
                                        border-radius: 5px;
                                        font-size: 14px;
                                        transition: all 0.2s;
                                    }
                                    .jwcfd-deactivate { color: #a00; }
                                    .jwcfd-deactivate:hover { color: #dc3232; background: #f8d7da; }
                                    .jwcfd-active { background: #3858e9; color: white; border:#3858e9}
                                    .jwcfd-active:hover { background: #2a46c7; }
                                    .jwcfd-close { background: #f0f0f0; color: #2c3338; }
                                    .jwcfd-close:hover { background: #e0e0e0; }
                                    #jwcfe-loading { display:none; margin-top:1em; color:#646970; font-style:italic }
                                </style>
                                <div class="jwcfe-modal-header">
                                    <img src="' . esc_url(plugin_dir_url(__FILE__)) . 'admin/assets/logo-blue.svg" alt="Logo" style="height: 30px; vertical-align: middle;">
                                    <h3 class="jwcfe-modal-title" style="display: inline-block; vertical-align: middle;">Quick Feedback</h3>
                                </div>

                                <h3>If you have a moment, please let us know why you want to deactivate this plugin</h3>
                                
                                <form id="jwcfe-feedback-form">
                                    
                                    <div class="jwcfe-form-group">
                                            <label class="jwcfe-radio-label">
                                                <input type="radio" name="reason" value="Not working properly" class="jwcfe-radio-input">
                                                <span>Not working as expected</span>
                                            </label>
                                        </div>
                                    
                                        <div class="jwcfe-form-group">
                                            <label class="jwcfe-radio-label">
                                                <input type="radio" name="reason" value="Broke my website" class="jwcfe-radio-input">
                                                <span>Broke my website</span>
                                            </label>
                                        </div>
                                    
                                        <div class="jwcfe-form-group">
                                            <label class="jwcfe-radio-label">
                                                <input type="radio" name="reason" value="Found another plugin" class="jwcfe-radio-input">
                                                <span>Found another better plugin</span>
                                            </label>
                                        </div>
                                    
                                        <div class="jwcfe-form-group">
                                            <label class="jwcfe-radio-label">
                                                <input type="radio" name="reason" value="Lacking features" class="jwcfe-radio-input">
                                                <span>Missing important features</span>
                                            </label>
                                        </div>
                                    
                                        <div class="jwcfe-form-group">
                                            <label class="jwcfe-radio-label">
                                                <input type="radio" name="reason" value="Other" class="jwcfe-radio-input">
                                                <span>Other reasons</span>
                                            </label>
                                        </div>
                                    
                                        <div class="jwcfe-form-group">
                                            <textarea class="jwcfe-textarea" name="other_reason" placeholder="Please help us understand your decision better..."></textarea>
                                        </div>
                                    <p>
                                This form is only for getting your valuable feedback. We do not collect your personal data.<br> To know more read our <a href="https://jcodex.com/privacy-policy/">Privacy Policy.</a>
                                </p>
                                    
                                    <footer class="modal-footer">
                                        <div class="jwcfd-left">
                                            <a class="jwcfd-link jwcfd-deactivate" href="#">Skip & Deactivate</a>
                                        </div>
                                        <div class="jwcfd-right">
                                            <a class="jwcfd-link jwcfd-active" target="_blank" href="https://jcodex.com/support/">Get Support</a>
                                            <button type="submit" class="jwcfd-link jwcfd-active jwcfd-submit-deactivate">Submit and Deactivate</button>
                                            <a class="jwcfd-link jwcfd-close" href="#">Cancel</a>
                                        </div>
                                    </footer>
                                
                            
                                    <p id="jwcfe-loading">Submitting your feedback...</p>
                                </form>
                            </div>
                            
                        `);
                        
                    }
    
                    // Skip & Deactivate handler
                    $(".jwcfd-deactivate").on("click", function(e) {
                        e.preventDefault();
                        window.location.href = deactivateLink;
                    });
    
                    // Cancel button handler
                    $(".jwcfd-close").on("click", function() {
                        $("#jwcfe-feedback-popup").remove();
                    });
    
                    // Form submission handler
                    $("#jwcfe-feedback-form").on("submit", function(event) {
                        event.preventDefault();
                        const reason = $("input[name=\'reason\']:checked").val();
                        const other = $("textarea[name=\'other_reason\']").val();
    
                        $("#jwcfe-loading").show();
    
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: "jwcfe_send_feedback",
                                reason: reason,
                                other_reason: other,
                                _ajax_nonce: "' . wp_create_nonce('jwcfe_feedback_nonce') . '"
                            },
                            success: function(response) {
                                window.location.href = deactivateLink;
                            },
                            error: function() {
                                alert("Something went wrong. Please try again.");
                                $("#jwcfe-loading").hide();
                            }
                        });
                    });
                });
            });
        ');
    }
    
    add_action('wp_ajax_jwcfe_send_feedback', 'jwcfe_send_feedback_callback');
    function jwcfe_send_feedback_callback() {
                check_ajax_referer('jwcfe_feedback_nonce');
            
                $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : '';
                $other = isset($_POST['other_reason']) ? sanitize_textarea_field($_POST['other_reason']) : '';
            
                // Get user information
                $current_user = wp_get_current_user();
                $user_email = $current_user->user_email;
                $site_name = get_bloginfo('name');
                $site_url = site_url();
            
                // Create clean email template
                $email_content = '
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                </head>
                <body style="margin: 0; padding: 20px; background-color: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="center">
                                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background: #ffffff; border-radius: 8px; padding: 30px;">
                                    <!-- Header -->
                                    <tr>
                                        <td style="border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; margin-bottom: 25px;">
                                            <h1 style="color: #1d2327; margin: 0 0 10px 0; font-size: 24px;">' . esc_html($site_name) . '</h1>
                                            <p style="color: #646970; margin: 0; font-size: 16px;">Plugin Deactivation Feedback</p>
                                        </td>
                                    </tr>
            
                                    <!-- Content -->
                                    <tr>
                                        <td>
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <!-- User Info -->
                                                <tr>
                                                    <td style="padding: 15px 0; border-bottom: 1px solid #f0f0f0;">
                                                        <strong style="color: #2c3338; display: block; margin-bottom: 5px;">User Email:</strong>
                                                        <span style="color: #646970;">' . esc_html($user_email) . '</span>
                                                    </td>
                                                </tr>
            
                                                <!-- Reason -->
                                                <tr>
                                                    <td style="padding: 15px 0; border-bottom: 1px solid #f0f0f0;">
                                                        <strong style="color: #2c3338; display: block; margin-bottom: 5px;">Deactivation Reason:</strong>
                                                        <span style="color: #646970;">' . esc_html($reason) . '</span>
                                                    </td>
                                                </tr>';
            
                // Additional Feedback
                if (!empty($other)) {
                    $email_content .= '
                                                <tr>
                                                    <td style="padding: 15px 0;">
                                                        <strong style="color: #2c3338; display: block; margin-bottom: 5px;">Additional Comments:</strong>
                                                        <div style="color: #646970; line-height: 1.6; white-space: pre-wrap;">' . nl2br(esc_html($other)) . '</div>
                                                    </td>
                                                </tr>';
                }
            
                $email_content .= '
                                            </table>
                                        </td>
                                    </tr>
            
                                    <!-- Footer -->
                                    <tr>
                                        <td style="padding-top: 25px; border-top: 2px solid #f0f0f0; color: #646970; font-size: 13px;">
                                            <p style="margin: 5px 0;">Date: ' . date('F j, Y \a\t g:i a') . '</p>
                                            <p style="margin: 5px 0;">Site: <a href="' . esc_url($site_url) . '" style="color: #3858e9; text-decoration: none;">' . esc_html($site_url) . '</a></p>
                                            <p style="margin: 5px 0; font-size: 12px; color: #8c8f94;">This is an automated notification. Please do not reply directly to this email.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>';
            
                // Set email headers
                $headers = array(
                    'Content-Type: text/html; charset=UTF-8',
                    'From: ' . $site_name . ' <' . sanitize_email(get_option('admin_email')) . '>',
                    'Reply-To: ' . sanitize_email(get_option('admin_email'))
                );
            
                // Send email
                wp_mail(
                    'support@jcodex.com', // Replace with your support email
                    'Plugin Deactivation Feedback: ' . esc_html($site_name),
                    $email_content,
                    $headers
                );
            
                wp_die();
    }   
    // add_action('wp_ajax_jwcfe_send_feedback', 'jwcfe_send_feedback_callback');
    // function jwcfe_send_feedback_callback() {
    //     check_ajax_referer('jwcfe_feedback_nonce');

    //     $data = array(
    //         'reason'      => sanitize_text_field($_POST['reason']),
    //         'other'       => sanitize_textarea_field($_POST['other_reason']),
    //         'user_email'  => wp_get_current_user()->user_email,
    //         'site_name'   => get_bloginfo('name'),
    //         'site_url'    => site_url(),
    //         'timestamp'   => current_time('mysql')
    //     );

    //     // Schedule background email (delay: 1 minute for reliability)
    //     wp_schedule_single_event(time() + 60, 'jwcfe_send_feedback_email', array($data));

    //     // Send quick response
    //     wp_send_json_success('Feedback submitted successfully. Thank you!');
    // }
    add_action('jwcfe_send_feedback_email', 'jwcfe_send_feedback_email_callback');
    function jwcfe_send_feedback_email_callback($data) {
        $email_content = '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="padding:20px; background:#f5f5f5; font-family:sans-serif;">
            <table width="100%" cellspacing="0" cellpadding="0" style="background:#fff; padding:30px; border-radius:8px;">
                <tr><td><h2 style="margin:0 0 10px 0;">' . esc_html($data['site_name']) . '</h2>
                <p>Plugin Deactivation Feedback</p></td></tr>
                <tr><td style="padding-top:15px;"><strong>User Email:</strong> ' . esc_html($data['user_email']) . '</td></tr>
                <tr><td style="padding-top:10px;"><strong>Reason:</strong> ' . esc_html($data['reason']) . '</td></tr>';

        if (!empty($data['other'])) {
            $email_content .= '<tr><td style="padding-top:10px;"><strong>Additional Comments:</strong><br>' .
                            nl2br(esc_html($data['other'])) . '</td></tr>';
        }

        $email_content .= '
                <tr><td style="padding-top:20px; font-size:12px; color:#666;">
                    Sent on ' . esc_html($data['timestamp']) . '<br>
                    Site: <a href="' . esc_url($data['site_url']) . '">' . esc_html($data['site_url']) . '</a><br>
                    <em>This is an automated email. Do not reply.</em>
                </td></tr>
            </table>
        </body>
        </html>';

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . sanitize_text_field($data['site_name']) . ' <' . sanitize_email(get_option('admin_email')) . '>',
            'Reply-To: ' . sanitize_email(get_option('admin_email'))
        );

        wp_mail(
            'support@jcodex.com', // Change to your support email
            'Plugin Deactivation Feedback: ' . esc_html($data['site_name']),
            $email_content,
            $headers
        );
    }
   

           