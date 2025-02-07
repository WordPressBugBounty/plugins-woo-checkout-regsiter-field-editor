<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class JWCFE_WC_Checkout_Field_Editor_Export_Handler {    
    private $fields;

    public function __construct() {
        $this->fields = $this->get_fields();

        // Hook into WooCommerce CSV export filters
        add_filter( 'wc_customer_order_csv_export_order_headers', array( $this, 'jwcfe_order_csv_export_order_headers' ), 10, 2 );
        add_filter( 'wc_customer_order_csv_export_order_row', array( $this, 'jwcfe_customer_order_csv_export_order_row' ), 10, 4 );

		// Newly added filters for customer export
		// add_filter( 'wc_customer_order_csv_export_customer_headers', array( $this, 'jwcfe_order_csv_export_customer_headers' ), 10, 2 );
		// add_filter( 'wc_customer_order_csv_export_customer_row', array( $this, 'jwcfe_order_csv_export_customer_row' ), 10, 4 );


    }

    /**
     * Add custom field headers to CSV export.
     */
    // public function jwcfe_order_csv_export_order_headers( $headers, $csv_generator ) {
    //     $field_headers = array();

    //     // Add custom fields headers
    //     foreach ( $this->fields as $name => $options ) {
    //         $field_headers[ $name ] = $name;
    //     }

    //     return array_merge( $headers, $field_headers );
    // }

	public function jwcfe_order_csv_export_order_headers($headers, $csv_generator) {
		$field_headers = array();
	
		// Get the selected fields
		$selected_fields = get_option('jwcfe_selected_csv_fields', array());
	
		// Add only the selected fields as headers
		foreach ($this->fields as $name => $options) {
			if (in_array($name, $selected_fields)) {
				$field_headers[$name] = $name;
			}
		}
	
		return array_merge($headers, $field_headers);
	}
	

    /**
     * Add custom field data to CSV export rows.
     */
	// public function jwcfe_customer_order_csv_export_order_row( $order_data, $order, $csv_generator ) {
	// 	$field_data = array();
	
	// 	// Loop through custom fields and fetch their values
	// 	foreach ( $this->fields as $name => $options ) {
	// 		// Get meta data from the order
	// 		$order_meta = $order->get_meta_data();
	// 		foreach ( $order_meta as $meta ) {
	// 			$field_key = $meta->key;
	// 			$field_value = $meta->value;
	
	// 			// Exclude unwanted meta keys
	// 			if ( ! empty( $field_key ) 
	// 				 && ! in_array( $field_key, ['_order_key', '_order_currency'] ) 
	// 				 && strpos( $field_key, '_' ) !== 0 // Exclude fields starting with '_'
	// 				 && ! in_array( $field_key, ['_billing_address_index', '_shipping_address_index', 'is_vat_exempt'] ) ) {
					
	// 				// Check if the field value is an array
	// 				if ( is_array( $field_value ) ) {
	// 					// Convert the array to a comma-separated string
	// 					$field_value = implode( ', ', $field_value );
	// 				}
	
	// 				// Add the field data to the array
	// 				$field_data[ $field_key ] = $field_value ?: 'N/A';
	// 			}
	// 		}
	// 	}
	
	// 	$new_order_data = array();
	
	// 	// Check export format and handle data accordingly
	// 	if ( isset( $csv_generator->order_format ) && 
	// 		 ( $csv_generator->order_format === 'default_one_row_per_item' || $csv_generator->order_format === 'legacy_one_row_per_item' ) ) {
	// 		// Handle for one row per item format
	// 		foreach ( $order_data as $data ) {
	// 			$new_order_data[] = array_merge( $field_data, (array) $data );
	// 		}
	// 	} else {
	// 		// Handle for other formats
	// 		$new_order_data = array_merge( $field_data, $order_data );
	// 	}
	
	// 	return $new_order_data;
	// }
	public function jwcfe_customer_order_csv_export_order_row( $order_data, $order, $csv_generator ) {
		$field_data = array();
		
		// Get the selected fields from the settings page
		$selected_fields = get_option('jwcfe_selected_csv_fields', array());
		// error_log(print_r($selected_fields));
		if ( ! empty( $selected_fields ) && is_array( $selected_fields ) ) {
			foreach ( $selected_fields as $field_key ) {
				// Get the meta value for the selected field
				$field_value = $order->get_meta( $field_key, true );
				
				// Add to field data if value exists
				if ( $field_value ) {
					$field_data[ $field_key ] = is_array( $field_value ) 
						? implode( ', ', $field_value ) 
						: $field_value;
				} else {
					$field_data[ $field_key ] = 'N/A';
				}
			}
		}
	
		$new_order_data = array();
	
		// Check export format and handle data accordingly
		if ( isset( $csv_generator->order_format ) && 
			 ( $csv_generator->order_format === 'default_one_row_per_item' || $csv_generator->order_format === 'legacy_one_row_per_item' ) ) {
			foreach ( $order_data as $data ) {
				$new_order_data[] = array_merge( $field_data, (array) $data );
			}
		} else {
			$new_order_data = array_merge( $field_data, $order_data );
		}
	
		return $new_order_data;
	}
	

	// public function jwcfe_order_csv_export_customer_headers( $headers, $csv_generator ){
	// 	$field_headers = array();

	// 	foreach ( $this->fields as $name => $options ) {
	// 		if( isset($options['user_meta']) && $options['user_meta']){
	// 			$field_headers[ $name ] = $options['title'];
	// 		}
	// 	}

	// 	return array_merge( $headers, $field_headers );
	// }
	// public function jwcfe_order_csv_export_customer_row( $customer_data, $user, $csv_generator ){
	// 	foreach ( $this->fields as $key => $field ) {
	// 		if($user->ID && isset($field['user_meta']) && $field['user_meta']){
	// 			$type  = isset($field['type']) && $field['type'] ? $field['type'] : 'text';
	// 			$value = get_user_meta( $user->ID, $key, true );

	// 			if($type === 'file' && apply_filters('thwcfe_csv_export_display_only_the_name_of_uploaded_file', true, $key)){
	// 				$value = JWCFE_Checkout_Fields_Utils::get_file_display_name_order($value, false);
	// 			}else{
	// 				$value = is_array($value) ? implode(", ", $value) : $value;
	// 			}
				
	// 			$customer_data[$key] = $value;
	// 		}
	// 	}

	// 	return $customer_data;
	// }




    /**
     * Retrieve all checkout fields from options.
     */
    private function get_fields() {
        $fields = array();

        // Get billing fields
        $billing_fields = get_option( 'jwcfe_wc_fields_billing', array() );
        $fields = array_merge( $fields, $billing_fields );

        // Get shipping fields
        $shipping_fields = get_option( 'jwcfe_wc_fields_shipping', array() );
        $fields = array_merge( $fields, $shipping_fields );

        // Get additional fields
        $additional_fields = get_option( 'jwcfe_wc_fields_additional', array() );
        $fields = array_merge( $fields, $additional_fields );

        return $fields;
    }
}
