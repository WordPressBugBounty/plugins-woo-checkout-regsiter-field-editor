<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use OM4\WooCommerceZapier\WooCommerceResource\Base;
// use OM4\WooCommerceZapier\Plugin\Base;
class JWCFE_WC_Zapier_Handler {

    private $trigger_keys = array(
		'wc.new_order', // New Order
		'wc.order_status_change' // New Order Status Change
	);

	public function __construct() {
		$zapier_version = $this->get_zapier_version();
        // error_log($zapier_version);
		if($zapier_version < '1.9.0'){
            // error_log("hello");
			foreach ( $this->trigger_keys as $trigger_key ) {
                // error_log($trigger_key);
				add_filter( "wc_zapier_data_{$trigger_key}", array( $this, 'zapier_data_override_legacy' ), 10, 4 );
			}
			add_action( "jwcfe-checkout-fields-updated", array( $this, 'checkout_fields_updated_legacy' ), 10, 0 );
		}	
	}

	private function get_zapier_version(){
		$data = get_plugin_data( WP_PLUGIN_DIR."/zapier/zapier.php", false, false );
		if(is_array($data) && isset($data['Version'])){
			return $data['Version'];
		}
		return false;
	}

	public function zapier_data_override_legacy( $order_data, WC_Zapier_Trigger $trigger ) {
        // error_log("in the zapier override legacy function");
		$sections = $this->get_checkout_sections();
		$order_id = $order_data['id'];
		
		if($order_id && $sections && is_array($sections)){
			foreach($sections as $sname => $section){
				if(THWCFE_Utils_Section::is_valid_section($section)){
					$fields = THWCFE_Utils_Section::get_fields($section);
					if($fields){
						foreach($fields as $name => $field){	
							if(THWCFE_Utils_Field::is_enabled($field) && ! isset( $order_data[$name] ) ) {
								if ( $trigger->is_sample() ) {
									// Sending sample data: Send the label of the custom checkout field as the field's value.
									$order_data[$name] = $field->get_property('title');
								} else {
									// Sending real data: Send the saved value of this checkout field.
									// If the order doesn't contain this custom field, an empty string will be used as the value.
									$order_data[$name] = $this->get_order_meta_value($name, $field, $order_id);
								}
							}
						}
					}
				}
			}
		}
		return $order_data;
	}

    }

new JWCFE_WC_Zapier_Handler();
