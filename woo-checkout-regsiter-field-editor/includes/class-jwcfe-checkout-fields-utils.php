<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('JWCFE_Checkout_Fields_Utils')) :

class JWCFE_Checkout_Fields_Utils {

    /**
     * Get a display-friendly filename for a file upload field value.
     * Returns just the filename stripped of path/URL prefix.
     *
     * @param  string $value      The stored file URL or path.
     * @param  bool   $full_path  Whether to return the full path (unused, kept for compat).
     * @return string
     */
    public static function get_file_display_name_order($value, $full_path = true) {
        if (empty($value)) {
            return '';
        }
        // Strip URL or path prefix — return just the original filename
        return basename($value);
    }

    /**
     * Check if WooCommerce version is 3.0 or above.
     *
     * @return bool
     */
    public function woo_version_check() {
        if (defined('WC_VERSION')) {
            return version_compare(WC_VERSION, '3.0', '>=');
        }
        return true;
    }

    /**
     * Get a PDF/packing-slip setting value from the advanced settings option.
     *
     * @param  string $name  Setting key.
     * @return mixed
     */
    public function get_settings($name) {
        $settings = get_option('jwcfe_advanced_settings', array());
        return isset($settings[$name]) ? $settings[$name] : '';
    }

    /**
     * Get all checkout field sections from plugin options.
     * Returns an array keyed by section name, each value is the raw fields array.
     *
     * @return array
     */
    public function get_checkout_sections() {
        $sections = array(
            'billing'    => get_option('jwcfe_wc_fields_billing',    array()),
            'shipping'   => get_option('jwcfe_wc_fields_shipping',   array()),
            'additional' => get_option('jwcfe_wc_fields_additional', array()),
        );
        return array_filter($sections); // remove empty sections
    }
}

endif;
