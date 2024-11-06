<?php
/**
 * this file includes to intilize plugin functions
 * 
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if(!class_exists('JWCFE_Helper')):

class JWCFE_Helper {

	function __construct() {}
    public static function get_fields($key){

		$fields = array_filter(get_option('jwcfe_wc_fields_'. $key, array()));

		if(empty($fields) || sizeof($fields) == 0){
			if($key === 'billing' || $key === 'shipping'){
				$fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(), $key . '_');
			} else if($key === 'additional'){

				$fields = array(

					'order_comments' => array(
						'type'        => 'textarea',
						'class'       => array('notes'),
						'label'       => esc_html__('Order Notes', 'jwcfe'),
						'placeholder' => _x('Notes about your order, e.g. special notes for delivery.', 'placeholder', 'jwcfe')
					)
				);
			}
				else if($key === 'account'){

				$fields = array(
					'account_username' => array(
						'type' => 'text',
						'label' => esc_html__('Email address', 'jwcfe')
					),

					'account_password' => array(
						'type' => 'password',
						'label' => esc_html__('Password', 'jwcfe')
					)
				);
			}
		}
		return $fields;
	}

		public static function jwcfe_woocommerce_version_check( $version = '3.0' ) {
		if ( function_exists( 'jwcfe_is_woocommerce_active' ) && jwcfe_is_woocommerce_active() ) {
				global  $woocommerce ;
				if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
					return true;
				}
			}
			
			return false;
	}


	public static function get_version_jwcfe($license_key){
		$current_site_url = get_site_url();
		$response = wp_remote_post( 'https://jcodex.com/', array(
			'body' => array(
				'edd_action' => 'get_version',
				'item_id' => 4111,
				'license' =>  $license_key,
				'url' => $current_site_url
			)
		) );

		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			return "Error: $error_message";
		} else {
			$response_code = wp_remote_retrieve_response_code($response);
			if ($response_code == 200) {
				$response_body = wp_remote_retrieve_body($response);
				$response_body = json_decode($response_body,true);
				return $response_body;
			} else {
				return "Error: Unexpected response code $response_code";
			}
		}
	}
}

endif;