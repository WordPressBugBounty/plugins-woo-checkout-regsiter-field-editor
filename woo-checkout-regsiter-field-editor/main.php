<?php
/**
 * Plugin Name: Checkout Field Editor for Woocommerce - Checkout Manager
 * Description: Easily Add, Edit, Remove or re-arrange any fields on WooCommerce Checkout page.
 * Author:      Jcodex
 * Version:     2.5.5
 * Author URI:  https://www.jcodex.com
 * Plugin URI:  https://www.jcodex.com
 * Text Domain: jwcfe
 * Domain Path: /languages/
 * WC requires at least: 3.0.0
 * WC tested up to: 10.8.1
 *
 * Copyright (C) 2018-2026 Jcodex Inc.
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
    define('JWCFE_VERSION', '2.5.5');
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

        // Activation timestamp, kept for reference/diagnostics (no longer used to gate the review notice).
        update_option( 'jwcfe_activated_at', time() );
    }

    /**
     * How long the review notice campaign runs for, per site, before it stops showing
     * to everyone automatically — regardless of whether anyone dismissed it (default: 2 weeks).
     *
     * @return int
     */
    function jwcfe_get_review_notice_campaign_seconds() {
        return (int) apply_filters( 'jwcfe_review_notice_campaign_seconds', 14 * DAY_IN_SECONDS );
    }

    /**
     * Stamps the moment this review campaign starts on a given site (first admin page
     * load after this code is active) so the notice appears immediately — including for
     * sites that already had the plugin installed and activated before this update.
     */
    add_action( 'admin_init', function () {
        if ( ! is_admin() || ! is_user_logged_in() || ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        if ( false === get_option( 'jwcfe_review_notice_campaign_started_at', false ) ) {
            update_option( 'jwcfe_review_notice_campaign_started_at', time() );
        }
    }, 5 );

    /**
     * Handles clicks on the review notice's action links. Both actions permanently
     * dismiss the notice for that admin — either they already left a review, or they
     * just closed it. Either way, nobody sees it again after responding once.
     */
    add_action( 'admin_init', function () {
        if ( ! is_admin() || ! is_user_logged_in() ) {
            return;
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( ! isset( $_GET['jwcfe_review_notice_action'] ) ) {
            return;
        }

        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'jwcfe_review_notice_action' ) ) {
            return;
        }

        $action  = sanitize_text_field( wp_unslash( $_GET['jwcfe_review_notice_action'] ) );
        $user_id = get_current_user_id();

        if ( 'already_reviewed' === $action || 'dismiss' === $action ) {
            update_user_meta( $user_id, 'jwcfe_review_notice_dismissed', 1 );
        }
    } );

    /**
     * True when viewing this plugin's settings screen (notice is shown inline there, not in admin_notices).
     */
    function jwcfe_is_plugin_settings_admin_screen() {
        return isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'jwcfe_checkout_register_editor';
    }

    /**
     * Review notice: 'inline' = below header on plugin page (full width). 'global' = WordPress admin_notices strip on other admin pages.
     *
     * @param string $context 'inline'|'global'.
     */
    function jwcfe_render_review_notice( $context = 'inline' ) {
        if ( ! is_admin() || ! is_user_logged_in() ) {
            return;
        }
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $user_id = get_current_user_id();

        // Permanently dismissed (already reviewed, or closed it) — never show again.
        if ( get_user_meta( $user_id, 'jwcfe_review_notice_dismissed', true ) ) {
            return;
        }

        // Campaign window: shows immediately (no per-site waiting period) on every site running
        // this update — including sites where the plugin was already active before this update —
        // then stops appearing for everyone once the window closes, dismissed or not.
        $campaign_started_at = (int) get_option( 'jwcfe_review_notice_campaign_started_at', 0 );
        if ( ! $campaign_started_at ) {
            $campaign_started_at = time();
            update_option( 'jwcfe_review_notice_campaign_started_at', $campaign_started_at );
        }
        if ( ( time() - $campaign_started_at ) >= jwcfe_get_review_notice_campaign_seconds() ) {
            return;
        }

        $context = ( 'global' === $context ) ? 'global' : 'inline';
        $extra_class = ( 'global' === $context ) ? 'jwcfe-review-notice--global' : 'jwcfe-review-notice--inline';

        $review_url = 'https://wordpress.org/support/plugin/woo-checkout-regsiter-field-editor/reviews/#new-post';

        $already_reviewed_url = wp_nonce_url(
            add_query_arg( 'jwcfe_review_notice_action', 'already_reviewed' ),
            'jwcfe_review_notice_action'
        );
        $dismiss_url = wp_nonce_url(
            add_query_arg( 'jwcfe_review_notice_action', 'dismiss' ),
            'jwcfe_review_notice_action'
        );

        $logo_url = plugin_dir_url( __FILE__ ) . 'admin/assets/logo-blue.svg';
        ?>
        <style>
            #jwcfe-review-notice.jwcfe-review-notice--inline {
                position: relative;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                margin: 12px 0 16px 0;
                border-left: none;
                padding: 0;
                border-radius: 6px;
                overflow: hidden;
                box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            }
            #jwcfe-review-notice.jwcfe-review-notice--global {
                position: relative;
                max-width: 100%;
                box-sizing: border-box;
                border-left: none;
                padding: 0;
                border-radius: 6px;
                overflow: hidden;
                box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            }
            .jwcfe-notice-inner {
                display: flex;
                align-items: center;
                gap: 15px;
                padding: 12px 40px 12px 16px;
                background: #f0f6ff;
                border-left: 4px solid #2271b1;
                border-radius: 6px;
            }
            .jwcfe-notice-close {
                position: absolute;
                top: 8px;
                right: 10px;
                width: 22px;
                height: 22px;
                display: flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
                font-size: 16px;
                color: #646970 !important;
                text-decoration: none !important;
                border-radius: 3px;
            }
            .jwcfe-notice-close:hover,
            .jwcfe-notice-close:focus {
                color: #3c434a !important;
                background: rgba(0,0,0,0.05);
            }
            .jwcfe-notice-logo img {
                width: 40px;
                height: 40px;
                display: block;
            }
            .jwcfe-notice-text {
                flex: 1;
                font-size: 13px;
                color: #1d2327;
                line-height: 1.5;
            }
            .jwcfe-notice-text strong {
                display: block;
                margin-bottom: 2px;
                font-size: 13px;
            }
            .jwcfe-notice-actions {
                margin-top: 6px;
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 8px 14px;
            }
            .jwcfe-notice-actions a.jwcfe-btn-review,
            .jwcfe-notice-actions a.jwcfe-btn-review:visited {
                display: inline-block;
                background: #2271b1;
                color: #ffffff !important;
                padding: 5px 14px;
                border-radius: 4px;
                text-decoration: none;
                font-size: 12px;
                border: 1px solid #2271b1;
            }
            .jwcfe-notice-actions a.jwcfe-btn-review:hover,
            .jwcfe-notice-actions a.jwcfe-btn-review:focus,
            .jwcfe-notice-actions a.jwcfe-btn-review:active {
                background: #135e96;
                border-color: #135e96;
                color: #ffffff !important;
            }
            .jwcfe-notice-actions a.jwcfe-btn-secondary,
            .jwcfe-notice-actions a.jwcfe-btn-secondary:visited {
                color: #2271b1 !important;
                text-decoration: underline;
                font-size: 12px;
            }
            .jwcfe-notice-actions a.jwcfe-btn-secondary:hover,
            .jwcfe-notice-actions a.jwcfe-btn-secondary:focus {
                color: #135e96 !important;
            }
        </style>

        <?php
        // WordPress common.js moves div.notice after the first h1 unless it has class .inline.
        $notice_classes = array( 'notice', $extra_class );
        if ( 'inline' === $context ) {
            $notice_classes[] = 'inline';
        }
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $notice_classes ) ); ?>" id="jwcfe-review-notice">
            <a href="<?php echo esc_url( $dismiss_url ); ?>" class="jwcfe-notice-close" aria-label="<?php esc_attr_e( 'Dismiss this notice', 'jwcfe' ); ?>" title="<?php esc_attr_e( 'Dismiss', 'jwcfe' ); ?>">&times;</a>
            <div class="jwcfe-notice-inner">
                <div class="jwcfe-notice-logo">
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="JCodex Logo" />
                </div>
                <div class="jwcfe-notice-text">
                    <strong><?php esc_html_e( 'Enjoying Checkout Field Editor? 🙌', 'jwcfe' ); ?></strong>
                    <?php esc_html_e( 'A quick review helps other store owners find this plugin. Got a minute?', 'jwcfe' ); ?>
                    <div class="jwcfe-notice-actions">
                        <a href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener noreferrer" class="jwcfe-btn-review">
                            ⭐ <?php esc_html_e( 'Leave a Review', 'jwcfe' ); ?>
                        </a>
                        <a href="<?php echo esc_url( $already_reviewed_url ); ?>" class="jwcfe-btn-secondary">
                            <?php esc_html_e( 'I already left a review', 'jwcfe' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    add_action(
        'admin_notices',
        function () {
            if ( ! function_exists( 'jwcfe_is_plugin_settings_admin_screen' ) || jwcfe_is_plugin_settings_admin_screen() ) {
                return;
            }
            jwcfe_render_review_notice( 'global' );
        }
    );

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'jwcfe_add_plugin_action_links');
function jwcfe_add_plugin_action_links($links) {
    // Add Settings link
    $settings_url = admin_url('admin.php?page=jwcfe_checkout_register_editor');
    $settings_link = '<a href="' . esc_url($settings_url) . '">' . __('Settings', 'jwcfe') . '</a>';

   

    // Add Upgrade to Pro link
    $pro_url = 'https://jcodex.com/plugins/woocommerce-custom-checkout-field-editor/';
    $pro_link = '<a href="' . esc_url($pro_url) . '" style="color: #215125ff; font-weight: bold;" target="_blank">' . __('Get Pro', 'jwcfe') . '</a>';

    // Insert links in custom order: Settings | Deactivate | Upgrade to Pro
    if (isset($links['deactivate'])) {
        $deactivate_link = $links['deactivate'];
        unset($links['deactivate']);
    } else {
        $deactivate_link = '';
    }

    $custom_links = array();
    $custom_links['settings'] = $settings_link;
    if ($deactivate_link) {
        $custom_links['deactivate'] = $deactivate_link;
    }
    $custom_links['pro'] = $pro_link;

    return $custom_links;
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
    
    /**
     * Hide "Additional information" heading in WooCommerce emails while keeping the fields.
     */
    add_filter( 'woocommerce_email_additional_information_heading', function ( $heading ) {
        return '';
    } );

    /**
     * Some WooCommerce email templates use the order meta heading filter instead.
     * Hide it too, while leaving the actual meta rows intact.
     */
    add_filter( 'woocommerce_email_order_meta_heading', function ( $heading, $sent_to_admin = false, $order = null ) {
        return '';
    }, 10, 3 );

    /**
     * Last-resort: remove the literal "Additional information" heading in:
     * - WooCommerce emails (including admin previews)
     * - Order received page / My Account → View order
     *
     * Some templates output the heading as a hard-coded translated string, not a filter.
     */
    $GLOBALS['jwcfe_is_rendering_wc_email'] = false;
    add_action( 'woocommerce_email_header', function () {
        $GLOBALS['jwcfe_is_rendering_wc_email'] = true;
    }, 0 );
    add_action( 'woocommerce_email_footer', function () {
        $GLOBALS['jwcfe_is_rendering_wc_email'] = false;
    }, PHP_INT_MAX );

    $jwcfe_maybe_hide_additional_information_heading = function ( $translated, $text, $domain ) {
        // This heading is normally a WooCommerce string, but some themes/plugins may output it from other domains.
        // We keep matching strict to the literal text to avoid side effects.
        $t1 = strtolower( trim( (string) $text ) );
        $t2 = strtolower( trim( (string) $translated ) );

        $matches_heading = in_array( $t1, [ 'additional information', 'additional information:' ], true )
            || in_array( $t2, [ 'additional information', 'additional information:' ], true );

        if ( ! $matches_heading ) {
            return $translated;
        }

        $is_order_details = ( function_exists( 'is_order_received_page' ) && is_order_received_page() )
            || ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'view-order' ) );

        if ( ! empty( $GLOBALS['jwcfe_is_rendering_wc_email'] ) || $is_order_details ) {
            return '';
        }

        return $translated;
    };

    add_filter( 'gettext', $jwcfe_maybe_hide_additional_information_heading, 20, 3 );
    add_filter( 'gettext_with_context', function ( $translated, $text, $context, $domain ) use ( $jwcfe_maybe_hide_additional_information_heading ) {
        return $jwcfe_maybe_hide_additional_information_heading( $translated, $text, $domain );
    }, 20, 4 );

    /**
     * WooCommerce Blocks: remove the "Additional information" heading on the
     * Order Confirmation page additional fields wrapper block.
     *
     * Block name: woocommerce/order-confirmation-additional-fields-wrapper
     */
    add_filter( 'render_block', function ( $block_content, $block ) {
        if (
            ! is_array( $block ) ||
            empty( $block['blockName'] ) ||
            $block['blockName'] !== 'woocommerce/order-confirmation-additional-fields-wrapper'
        ) {
            return $block_content;
        }

        // Remove only the heading, keep the fields list.
        return preg_replace( '#<h2\b[^>]*>\s*Additional information\s*</h2>#i', '', (string) $block_content );
    }, 20, 2 );


require_once JWCFE_PATH . 'includes/class-jwcfe-deactivation-feedback.php';

           