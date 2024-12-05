<?php

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../includes/class-jwcfe-helper.php';

/**
 * WC_Checkout_Field_Editor checkout class.
 */

if (!class_exists('JWCFE_Public_Checkout')) :

	class JWCFE_Public_Checkout
	{
		private $plugin_name;
		private $version;
		private $rules;

		/**
		 * __construct function.

		 */

		public function __construct($plugin_name, $version)
		{


			$this->plugin_name = $plugin_name;
			$this->version = $version;

		}


		public function define_public_checkout_hooks()
		{
			add_filter('woocommerce_enable_order_notes_field', array($this, 'jwcfe_enable_order_notes_field'), 1001);
			add_filter('woocommerce_get_country_locale_base', array($this, 'jwcfe_prepare_country_locale'));
			add_filter('woocommerce_get_country_locale', array($this, 'jwcfe_woo_get_country_locale'));

			add_filter('woocommerce_billing_fields', array($this, 'jwcfe_billing_fields_lite_paid'), 1001, 2);
			add_filter('woocommerce_shipping_fields', array($this, 'jwcfe_shipping_fields_lite'), 1001, 2);
			add_filter('woocommerce_checkout_fields', array($this, 'jwcfe_checkout_fields_lite'), apply_filters('jwcfe_checkout_fields_priority', 1000));
			add_filter('woocommerce_default_address_fields', array($this, 'jwcfe_woo_default_address_fields'));
			add_action('woocommerce_checkout_update_order_meta', array($this, 'save_data'), 10, 2);
			

			add_action('woocommerce_after_checkout_validation', array($this, 'jwcfe_check_field_validations'), 10, 4);
			add_action('woocommerce_email_order_meta', array($this, 'jwcfe_display_custom_fields_in_emails_lite'), 10, 3);


			add_filter('woocommerce_form_field_checkbox', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_checkboxgroup', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_month', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_week', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_multiselect', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_date', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_textarea', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_text', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_email', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_phone', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_select', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_radio', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_file', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_timepicker', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_number', array($this, 'jwcfe_checkout_form_field'), 10, 4);


			add_filter('woocommerce_form_field_heading', array($this, 'jwcfe_checkout_fields_heading_field'), 10, 4);
			add_filter('woocommerce_form_field_customcontent', array($this, 'jwcfe_checkout_fields_customcontent_field'), 10, 4);
			add_filter('woocommerce_form_field_paragraph', array($this, 'jwcfe_checkout_fields_pro_paragraph_field'), 10, 4);

		}

		/**
		 * generating tooltip for fields in checkoutpage*
		 */

		 public function generate_tooltip($text) {
			if (empty($text)) {
				return '';
			}
			
			$tooltip_html = '<span style="position: relative; display: inline-block; cursor: pointer; margin-left: 5px;">&#9432;
							 <span style="visibility: hidden; width: 220px; background-color: #555; color: #fff; text-align: center; border-radius: 6px; padding: 5px; position: absolute; z-index: 1; bottom: 125%; left: 50%; margin-left: -60px; opacity: 0; transition: opacity 0.3s; 
							 pointer-events: none; font-size: 12px;" class="tooltip-text">' . __(stripcslashes($text), 'jwcfe') . '</span></span>';
		
			$tooltip_script = '<script>
								document.addEventListener("DOMContentLoaded", function() {
									const tooltipElements = document.querySelectorAll("label span");
									tooltipElements.forEach((element) => {
										element.addEventListener("mouseover", () => {
											const tooltipText = element.querySelector(".tooltip-text");
											if (tooltipText) {
												tooltipText.style.visibility = "visible";
												tooltipText.style.opacity = "1";
											}
										});
										element.addEventListener("mouseout", () => {
											const tooltipText = element.querySelector(".tooltip-text");
											if (tooltipText) {
												tooltipText.style.visibility = "hidden";
												tooltipText.style.opacity = "0";
											}
										});
									});
								});
							   </script>';
		
			return $tooltip_html . $tooltip_script;
		}
		/**
		 * wc_checkout_fields_scripts function.*
		 */

		public function jwcfe_checkout_fields_frontend_scripts()
		{

			global $wp_scripts;
			if (is_checkout() || is_account_page()) {
				wp_enqueue_style('jwcfe-style-front', JWCFE_ASSETS_URL_PUBLIC . '/css/jwcfe-style-front.css', JWCFE_VERSION);
				$jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
				if (is_checkout()) {
					$currentScreen = "checkout";
				} else {
					$currentScreen = "account";
				}

				wp_enqueue_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css');

				$in_footer = apply_filters('jwcfe_enqueue_script_in_footer', true);
				wp_register_script(
					'jwcfe-checkout-editor-frontend',
					JWCFE_ASSETS_URL_PUBLIC . 'js/jwcfe-checkout-field-editor-frontend.js',
					array('jquery', 'jquery-ui-datepicker', 'select2'),
					JWCFE_VERSION,
					$in_footer
				);
				wp_enqueue_script('jwcfe-checkout-editor-frontend');
				wp_localize_script('jwcfe-checkout-editor-frontend', 'jwcfe_checkout_obj', array(
					'ajaxurl' => admin_url('admin-ajax.php'),
				));

				wp_enqueue_script('jwcfe-upload', JWCFE_ASSETS_URL_PUBLIC . 'js/jwcfe-upload.js', array('jquery'), JWCFE_VERSION, true);

				wp_localize_script('jwcfe-upload', 'MyAjax', array(

					// URL to wp-admin/admin-ajax.php to process the request

					'ajaxurl' => admin_url('admin-ajax.php'),
					'loaderPath' => JWCFE_ASSETS_URL_PUBLIC . 'js/preloader.gif',
					'donePath' => JWCFE_ASSETS_URL_PUBLIC . 'js/ajax-done.png',
					'currentScreen' => $currentScreen
				));

				$pattern = array(
					//day

					'd',		//day of the month
					'j',		//3 letter name of the day
					'l',		//full name of the day
					'z',		//day of the year
					'S',

					//month

					'F',		//Month name full
					'M',		//Month name short
					'n',		//numeric month no leading zeros
					'm',		//numeric month leading zeros

					//year

					'Y', 		//full numeric year
					'y'		//numeric year: 2 digit

				);

				$replace = array(
					'dd', 'd', 'DD', 'o', '',

					'MM', 'M', 'm', 'mm',

					'yy', 'y'
				);

				foreach ($pattern as &$p) {
					$p = '/' . $p . '/';
				}

				wp_localize_script('wc-checkout-editor-frontend', 'wc_checkout_fields', array(
					'date_format' => preg_replace($pattern, $replace, wc_date_format())
				));
			}
		}


		public function jwcfe_check_field_validations($A)
		{
			$fields = get_option('jwcfe_wc_fields_billing');

			if (is_array($fields) && !empty($fields)) {
				foreach ($fields as $name => $field) {

					if (isset($field['enabled']) && $field['enabled'] == false) {
						unset($fields[$name]);
					} else {
						$new_field = $field;
						$label = '';

						if (isset($new_field['label'])) {
							$label = $new_field['label'];
						} else {
							$label = $name;
						}

						if (isset($new_field['rules_action_ajax']) && !empty($new_field['rules_action_ajax']) && isset($new_field['rules_ajax']) && !empty($new_field['rules_ajax'])) {

							if (!empty($new_field['rules_ajax'])) {
								if (!empty($A[$name])) {
									continue;
								}
								$rulesArr = json_decode(urldecode($new_field['rules_ajax']), true);
								foreach ($rulesArr[0][0] as $singleRule) {
									$operand = $singleRule[0]['operand'][0];

								}
							}
						}
					}
				}
			}
		}


		/**
		 * Hide Additional Fields title if no fields available.
		 *
		 * @param mixed $old
		 */
		
		public function jwcfe_enable_order_notes_field($fields)
		{
			global $supress_field_modification;
			if ($supress_field_modification) {
				return $fields;
			}

			$additional_fields = get_option('jwcfe_wc_fields_additional');

			// Check if additional fields exist and are enabled
			if (is_array($additional_fields)) {
				$enabled = 0;
				foreach ($additional_fields as $field) {
					if ($field['enabled']) {
						$enabled++;
					}
				}

				// If enabled, modify the 'order_comments' field to add a tooltip
				if ($enabled > 0) {
					if (isset($fields['order']['order_comments'])) {
						// Tooltip text (you can customize this)
						$tooltip_text = "Provide any specific instructions for delivery or other important order-related notes.";

						// Generate tooltip HTML
						$tooltip = $this->generate_tooltip($tooltip_text);

						// Modify label with the tooltip
						$fields['order']['order_comments']['label'] .= $tooltip;
					}
					return $fields;
				}
			}
			return $fields;
		}
		

		public function jwcfe_checkout_form_field($field, $key, $args, $value){
				$image_url = JWCFE_ASSETS_URL_PUBLIC . 'assets/ic_info.svg';
						
				if (!empty($args['clear'])) {
					$after = '<div class="clear"></div>';
				} else {
					$after = '';
				}
				$data_validations='';
				if ($args['required']) {
					$args['class'][] = 'validate-required';
					$data_validations = 'validate-required';
					$required = ' <abbr class="required" title="' . esc_attr__('required', 'jwcfe') . '">*</abbr>';
				} else {
					$required = '';
				}

				$args['maxlength'] = ($args['maxlength']) ? 'maxlength="' . absint($args['maxlength']) . '"' : '';
	
				
				if (isset($args['text'])) {
					$tooltip = $this->generate_tooltip($args['text']);
				} else {
					$tooltip = ''; 
				}
				
				switch ($args['type']) {

					case 'checkboxgroup':
						$field 	= '';
						$field 	= '<div class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
						$field .= '<fieldset><legend><label for="' . esc_attr($args['id']) . '" class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . esc_html($args['label']) . $required . $tooltip . '</label></legend>';
					
						// Retrieve previous values from session
						$previous_values = WC()->session->get($key, []); // Get the array of previously checked values
					
						if (!empty($args['options_json'])) {
							foreach ($args['options_json'] as $option) {
								// Check if the current option is in the previously selected values
								$is_checked = in_array(esc_attr($option['key']), (array) $previous_values, true);
								$field .= '<label><input type="checkbox" ' . checked($is_checked, true, false) . ' name="' . esc_attr($key) . '[]" id="' . $key . '_' . esc_attr($option['key']) . '" value="' . esc_attr($option['key']) . '" /> ' . esc_html($option['text']) . '</label>';
							}
						}
					
						$field .= '</fieldset></div>' . $after;
						return $field;
						break;
					case 'checkbox':
							$field = '';
							$field = '<div class="form-row custom-checkbox-field ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
							
							$field .= '<fieldset>';
							
							$field .= '<div class="checkbox-wrapper custom-checkboxes">';
							$field .= '<label for="' . esc_attr($args['id']) . '" style="margin-bottom: 0;">';
							
							// Get the previous value from the session
							$previous_value = WC()->session->get($key, ''); // Get the previous value
						
							// Determine if the checkbox should be checked
							$is_checked = (!empty($previous_value) || (!empty($args['checked']) && $args['checked'] === true));
						
							$field .= '<input type="checkbox" class="jwcfe-price-field" id="' . esc_attr($args['id']) . '" name="' . esc_attr($key) . '" value="' . esc_attr($key) . '"';
							
							// Set the checked attribute based on the previous value or the args
							if ($is_checked) {
								$field .= ' checked="checked"';
							}
						
							$field .= ' />';
							$field .= esc_html($args['label']) . $required . $tooltip;
							$field .= '</label>';
							$field .= '</div>'; 
							
							$field .= '</fieldset></div>' . $after;
							
							return $field;
						
							break;
					case 'month':
								$fieldLabel = '';
								$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
							
								if ($args['label']) {
									$fieldLabel = $args['label'];
									$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
								}
							
								// Check for previous value in the session
								$previous_value = WC()->session->get($key, ''); // Get the previous value from session
								$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
							
								$field .= '<input type="month" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
							
								if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
									foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
										$field .= ' ' . $customattr_key . '="' . esc_attr($customattr_val) . '" ';
									}
								}
							
								// Use the value from session or posted data
								$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
							
								$field .= '</p>' . $after;
							
								return $field;
							
						break;
							
					
					case 'week':
							$fieldLabel = '';
						
							$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
						
							if ($args['label']) {
								$fieldLabel = $args['label'];
								$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
							}
						
							// Check for previous value in the session
							$previous_value = WC()->session->get($key, ''); // Get the previous value from session
							$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
						
							$field .= '<input type="week" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
						
							if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
								foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
									$field .= ' ' . $customattr_key . '="' . esc_attr($customattr_val) . '" ';
								}
							}
						
							// Use the value from session or posted data
							$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
						
							$field .= '</p>' . $after;
						
							return $field;
						
							break;
					case 'multiselect':
								$customer_user_id = get_current_user_id(); 
								$customer_orders = wc_get_orders(array(
									'meta_key' => '_customer_user',
									'meta_value' => $customer_user_id,
									'posts_per_page' => 1,
									'orderby' => 'ID',
									'orderby' => 'DESC'
								));
								
								// Initialize selected values as an array
								$selectedVals = array();
							
								// Retrieve selected values from customer orders
								foreach ($customer_orders as $order) {
									$order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
							
									$order = wc_get_order($order_id);
									$valArr = $order->get_meta($key, true);
							
									if (!empty($valArr) && is_array($valArr)) {
										// Merge existing values into selectedVals array
										$selectedVals = array_merge($selectedVals, $valArr);
									}
								}
							
								// Handle previous values stored in session
								$session_values = WC()->session->get($key, array()); // Retrieve values from the session
								if (!empty($session_values) && is_array($session_values)) {
									$selectedVals = array_merge($selectedVals, $session_values); // Merge with selected values from orders
								}
							
								$options = '';
								if (!empty($args['options_json'])) {
									foreach ($args['options_json'] as $option) {
										// Check if the option key is in selected values
										$isSelected = in_array($option['key'], $selectedVals) ? 'selected' : '';
										$options .= '<option value="' . esc_attr($option['key']) . '" ' . $isSelected . '>' . esc_html($option['text']) . '</option>';
									}
							
									$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
							
									if ($args['label']) {
										$fieldLabel = $args['label'];
										$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html($args['label']) . $required . $tooltip . '</label>';
									}
									$class = '';
							
									$field .= '<select data-placeholder="' . esc_attr__('Select some options', 'jwcfe') . '" multiple="multiple"';
							
									if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
										foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
											$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
										}
									}
							
									$field .= 'name="' . esc_attr($key) . '[]" id="' . esc_attr($key) . '" class="checkout_chosen_select select wc-enhanced-select ' . esc_attr($class) . '">';
									
									$field .= $options; // Add options to the select field
									$field .= '</select></p>' . $after;
								}
								return $field;
							
							break;
					case 'date':
								$fieldLabel = '';
								$field = '';
							
								// Check for previous value in session if not provided
								$previous_value = WC()->session->get($key, ''); // Get value from session
								$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
							
								$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
							
								if ($args['label']) {
									$fieldLabel = $args['label'];
									$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
								}
							
								$field .= '<input type="date" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
							
								if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
									foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
										$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
									}
								}
							
								// Use the display value that we have ensured is from session or posted data
								$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
								$field .= '</p>' . $after;
							
								return $field;
							
							break;
							
					case 'textarea':
								$field = '';
								$fieldLabel = '';
								
								// Check for previous value in session if not provided
								$previous_value = WC()->session->get($key, ''); // Get value from session
								$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
							
								$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
							
								if ($args['label']) {
									$fieldLabel = $args['label'];
									$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
								}
							
								$field .= '<textarea class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
							
								if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
									foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
										$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
									}
								}
							
								// Use the display value that we have ensured is from session or posted data
								$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . '>' . esc_html($display_value) . '</textarea>';
								$field .= '</p>' . $after;
							
								return $field;
							
							break;
					case 'text':
							$fieldLabel = '';
							$field = '';
							// Check for previous value in session if not provided
							$previous_value = WC()->session->get($key, ''); // Get value from session
							$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
						
							$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field" data-validations="' . $data_validations . '">';
						
							if ($args['label']) {
								$fieldLabel = $args['label'];
								$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
							}
						
							$field .= '<input type="text" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
						
							if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
								foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
									$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
								}
							}
						
							// Use the display value that we have ensured is from session or posted data
							$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
							$field .= '</p>' . $after;
						
							return $field;
						
						break;
						
					case 'email':
							$fieldLabel = '';
							$field = '';
						
							// Check for previous value in session if not provided
							$previous_value = WC()->session->get($key, ''); // Get value from session
							$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
						
							$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field" data-validations="' . $data_validations . '">';
						
							if ($args['label']) {
								$fieldLabel = $args['label'];
						
								$tooltipText = isset($args['text']) ? stripcslashes($args['text']) : '';
								$tooltip = $this->generate_tooltip($tooltipText);
						
								$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
							}
						
							$field .= '<input type="email" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
						
							if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
								foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
									$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
								}
							}
						
							// Use the display value that we have ensured is from session or posted data
							$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
							$field .= '</p>' . $after;
						
							return $field;
						
						break;
					case 'phone':
							$fieldLabel = '';
							$field = '';
							$previous_value = WC()->session->get($key, ''); // Get value from session
							$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
						
							$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field" data-validations="' . $data_validations . '">';
							
							if ($args['label']) {
								$fieldLabel = $args['label'];
								$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . $args['label'] . $required . $tooltip . '</label>';
							}
						
							$field .= '<input type="tel" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
						
							if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
								foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
									$field .= ' ' . $customattr_key . '="' . esc_attr($customattr_val) . '" ';
								}
							}
						
							$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
							$field .= '</p>' . $after;
						
							return $field;
							break;
						
					case 'select':
								$options = '<option selected value>' . __('Please Select', 'jwcfe') . '</option>';
								$selectedVal = WC()->session->get($key, ''); // Get value from session
								
								// Get the current user ID and orders
								$customer_user_id = get_current_user_id();
								$customer_orders = wc_get_orders([
									'meta_key' => '_customer_user',
									'meta_value' => $customer_user_id,
									'posts_per_page' => 1,
									'orderby' => 'ID',
									'order' => 'DESC'
								]);
							
								// Get previous value if available
								$previous_value = WC()->session->get($key, '');
								$display_values = !empty($previous_value) ? $previous_value : $value;
								$display_value = is_array($display_values) ? reset($display_values) : $display_values;
							
								// Loop through customer orders to retrieve previously selected value
								foreach ($customer_orders as $order) {
									$order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
									$order = wc_get_order($order_id);
									$valArr = $order->get_meta($key, true);
							
									if (!empty($valArr) && is_array($valArr)) {
										$selectedVal = reset($valArr); // Get the first item from array if multiple values exist
									}
								}
							
								// Ensure $args['options_json'] is defined and loop through options
								if (!empty($args['options_json']) && is_array($args['options_json'])) {
									foreach ($args['options_json'] as $option) {
										if (isset($option['key'], $option['text'])) { // Check if 'key' and 'text' exist
											$selectedOptions = selected($selectedVal, $option['key'], false); // Use session or order value
											$option_value = !empty($display_value) ? esc_attr($display_value) : esc_attr($option['key']);
											$options .= '<option ' . $selectedOptions . ' value="' . $option_value . '">' . esc_html($option['text']) . '</option>';
										}
									}
								}
							
								// Construct the field HTML
								$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
								if (!empty($args['label'])) {
									$field .= '<label for="' . esc_attr($key) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html($args['label']) . $required . $tooltip . '</label>';
								}
								$field .= '<select name="' . esc_attr($args['id']) . '[]" id="' . esc_attr($args['id']) . '" class="checkout_chosen_select select wc-enhanced-select">';
								$field .= $options;
								$field .= '</select></p>' . $after;
							
								return $field;
							
								break;
							case 'radio':
								$field = '';										
								$field = '<div class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
								$field .= '<fieldset><legend><label for="' . esc_attr($args['id']) . '" class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . esc_html($args['label']) . $required . $tooltip . '</label></legend>';
								
								$field .= '<div class="custom-radio-container">';
								
								// Check for previous value in session if not provided
								$previous_value = WC()->session->get($key, ''); // Get value from session
								$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
							
								if (!empty($args['options_json'])) {
									foreach ($args['options_json'] as $option) {
										$field .= '<div class="custom-radio-wrapper">'; 
										$field .= '<input type="radio" id="' . $args['id'] . '_' . $option['key'] . '" ';
										
										if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
											foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
												$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
											}
										}
							
										// Use the display value that we have ensured is from session or posted data
										$field .= 'name="' . esc_attr($args['id']) . '" value="' . esc_attr($option['key']) . '"';
										$field .= checked($display_value, $option['key'], false) . '>';
										$field .= '<label for="' . $args['id'] . '_' . $option['key'] . '">' . esc_html($option['text']) . '</label>';
										$field .= '</div>'; 
									}
								}
								
								$field .= '</div>';
								$field .= '</fieldset>';
								$field .= '</div>' . $after;
							
								return $field;
							break;
					case 'number':
						$fieldLabel = '';
						$field = '';
					
						// Check for previous value in session if not provided
						$previous_value = WC()->session->get($key, ''); // Get value from session
						$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
					
						$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field" data-validations="' . $data_validations . '">';
					
						if ($args['label']) {
							$fieldLabel = $args['label'];
							$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . $args['label'] . $required . $tooltip . '</label>';
						}
					
						// Extract and sanitize maxlength from custom_attributes array
						$max_length = isset($args['custom_attributes']['maxlength']) ? intval(preg_replace('/[^0-9]/', '', $args['custom_attributes']['maxlength'])) : '';
					
						$field .= '<input type="number" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
					
						if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
							foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
								if ($customattr_key === 'maxlength') {
									$field .= ' maxlength="' . esc_attr($max_length) . '" ';
								} else {
									$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
								}
							}
						}
					
						// Apply placeholder and value
						$field .= ' placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" value="' . esc_attr($display_value) . '">';
						$field .= '</p>' . $after;
					
						// Add JavaScript to enforce maxlength
						if ($max_length) {
							$field .= "<script>
								document.addEventListener('DOMContentLoaded', function () {
									const numberInput = document.getElementById('" . esc_attr($args['id']) . "');
									const maxLength = " . (int)$max_length . ";
					
									if (numberInput && maxLength) {
										numberInput.addEventListener('input', function () {
											if (this.value.length > maxLength) {
												this.value = this.value.slice(0, maxLength);
											}
										});
									}
								});
							</script>";
						}
					
						return $field;
						break;
					
					case 'timepicker':
					// Check for previous value in session if not provided
					$previous_value = WC()->session->get($key, ''); 
					$display_values = !empty($previous_value) ? $previous_value : $value; 
					
					if (is_array($display_values) && !empty($display_values)) {
						$display_value = $display_values[0]; // Get the first value
					} else {
						$display_value = ''; // Set to an empty string if no values
					}
					
					set_time_limit(0);
					$customer_user_id = get_current_user_id(); 
					$customer_orders = wc_get_orders(array(
						'meta_key' => '_customer_user',
						'meta_value' => $customer_user_id,
						'posts_per_page' => 1,
						'orderby' => 'ID',
						'order' => 'DESC'
					));
					
					$selectedVal = '';
					foreach ($customer_orders as $order) {
						$order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
						$valArr = $order->get_meta($key, true);
						
						if (!empty($valArr) && is_array($valArr)) {
							// Use the latest selected value if it exists
							$selectedVal = implode(',', $valArr);
						}
					}
				
					// Merge display value with selectedVal for comparison
					$selectedValArray = array_filter(array_merge(explode(',', $selectedVal), [$display_value]));
					
					$after = (!empty($args['clear'])) ? '<div class="clear"></div>' : '';
					$required = $args['required'] ? ' <abbr class="required" title="' . esc_attr__('required', 'jwcfe') . '">*</abbr>' : '';
					
					$options = '<option value="">' . esc_html__('Please Select', 'jwcfe') . '</option>';
					$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
					
					if ($args['label']) {
						$field .= '<label for="' . esc_attr($key) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
					}
					
					$min_time = strtotime($args['min_time']);
					$max_time = strtotime($args['max_time']);
					$time_step = $args['time_step'];
					$time_format = $args['time_format'];
					
					for ($hours = 0; $hours < 24; $hours++) {
						for ($mins = 0; $mins < 60; $mins += $time_step) {
							$rawtime = str_pad(($hours % 24), 2, '0', STR_PAD_LEFT) . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT);
							$formatted_time = date($time_format, strtotime($rawtime));
							if (strtotime($rawtime) >= $min_time && strtotime($rawtime) <= $max_time) {
								// Check if this time was previously selected
								$option_attributes = '';
								if (in_array($formatted_time, $selectedValArray)) {
									$option_attributes = ' selected'; // Add selected attribute if this time was previously selected
								}
								
								$options .= '<option value="' . esc_attr($formatted_time) . '"' . $option_attributes . '>' . esc_html($formatted_time) . '</option>';
							}
						}
					}
					
					// Build the select field
					$field .= '<select name="' . esc_attr($args['id']) . '[]" id="' . esc_attr($args['id']) . '"';
					foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
						$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
					}
					$field .= 'class="checkout_chosen_select select wc-enhanced-select">' . $options . '</select></p>' . $after;
				
					return $field;
					break;
				
			
			}
		}	
		function jwcfe_checkout_fields_number_field( $field, $key, $args, $value ) {
		
			if ( ( ! empty( $args['clear'] ) ) ) $after = '<div class="clear"></div>'; else $after = '';
			$data_validations = '';
			if ( $args['required'] ) {
				$args['class'][] = 'validate-required';
				$data_validations = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'jwcfe'  ) . '">*</abbr>';
			} else {
				$required = '';
			}

			$args['maxlength'] = ( $args['maxlength'] ) ? 'maxlength="' . absint( $args['maxlength'] ) . '"' : '';
			
			
			$fieldLabel = '';
			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field" data-validations="'.$data_validations.'" >';
			if ( $args['label'] ) {
				$fieldLabel = $args['label'];
				$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . implode( ' ', $args['label_class'] ) .'">' . __($args['label'],'jwcfe') . $required . '</label>';
			}
			
			$field .= '<input type="number" class="input-number '.esc_attr( implode( ' ', $args['input_class'] ) ).'" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '"';
			if(!empty($args['custom_attributes']) && is_array($args['custom_attributes'])){
				foreach($args['custom_attributes'] as $customattr_key=>$customattr_val){
					$field .= ' '.$customattr_key.'='.'"'.$customattr_val.'" ';
				}
				
			}
			$field .= 'placeholder="' . __($args['placeholder'], 'jwcfe') . '" '.$args['maxlength'].' value="' . esc_attr( $value ) . '" />';
			
			$field .= '</p>' . $after;

			return $field;
		}
		/**
		 * jwcfe_checkout_fields_heading_field function.
		 *
		 * @param string $field (default: '')
		 * @param mixed $key
		 * @param mixed $args
		 * @param mixed $value
		 */


		
		public function jwcfe_checkout_fields_heading_field($field, $key, $args, $value) {
			if ((!empty($args['clear']))) $after = '';
			else $after = '';
			
			$data_validations = '';
			
			if ($args['required']) {
				$args['class'][] = 'validate-required';
				$data_validations = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__('required', 'jwcfe') . '">*</abbr>';
			} else {
				$required = '';
			}
			
			$args['maxlength'] = ($args['maxlength']) ? 'maxlength="' . absint($args['maxlength']) . '"' : '';
			
		
			
			$singleq = "'";
			$fieldLabel = '';
			
			// Get the previous value from session
			$previous_value = WC()->session->get($key, ''); // Get value from session
			$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
			
			// Start building the field HTML
			$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field"  data-validations="' . $data_validations . '" >';
			$tooltip = $this->generate_tooltip($args['text']);
			if ($args['label']) {
				$fieldLabel = $args['label'];
		
				$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html($args['label']) . $required . $tooltip . '</label>';
			}
		
			// Add the input field with the previous or default value
			$field .= '<input type="text" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
		
			if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
				foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
					$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
				}
			}
		
			// Set the placeholder and value
			$field .= 'placeholder="Enter your text" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />'; // Use display_value here
		
			$field .= '</p>' . $after;
		
			return $field;
		}
		
		/**
		 * jwcfe_checkout_fields_custom_field function.
		 *

		 * @param string $field (default: '')
		 * @param mixed $key
		 * @param mixed $args
		 * @param mixed $value
		 */

		
		
		public function jwcfe_checkout_fields_customcontent_field($field, $key, $args, $value)
		{
			if ((!empty($args['clear']))) $after = '';
			else $after = '';
		
			$data_validations = '';
		
			if ($args['required']) {
				$args['class'][] = 'validate-required';
				$data_validations = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__('required', 'jwcfe') . '">*</abbr>';
			} else {
				$required = '';
			}
		
			$args['maxlength'] = ($args['maxlength']) ? 'maxlength="' . absint($args['maxlength']) . '"' : '';
		
			$fieldLabel = '';
		
			// Start building the field HTML
			$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field"  data-validations="' . $data_validations . '" >';
		
			if ($args['label']) {
				$fieldLabel = $args['label'];
		
				// Add label with tooltip
				$tooltip = $this->generate_tooltip($args['text']);
				$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . $args['label'] . $required . $tooltip . '</label>';
			}
		
			
				$field .= '<input type="text" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
		
				if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
					foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
						$field .= ' ' . $customattr_key . '="' . esc_attr($customattr_val) . '" ';
					}
				}
		
				$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr__($value, 'jwcfe') . '" />';
			
		
			$field .= '</p>' . $after;
		
			return $field;
		}

		//   jwcfe_checkout_fields_paragraph_field function.

		public function jwcfe_checkout_fields_pro_paragraph_field($field, $key, $args, $value) {
			// Check for previous value in session if not provided
			$previous_value = WC()->session->get($key, ''); // Get value from session
			$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
		
			if ((!empty($args['clear']))) $after = '';
			else $after = '';
		
			$data_validations = '';
		
			if ($args['required']) {
				$args['class'][] = 'validate-required';
				$data_validations = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__('required', 'jwcfe') . '">*</abbr>';
			} else {
				$required = '';
			}
		
			$args['maxlength'] = ($args['maxlength']) ? 'maxlength="' . absint($args['maxlength']) . '"' : '';
		
			
			$fieldLabel = '';
		
			$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field"  data-validations="' . $data_validations . '" >';
			$tooltip = $this->generate_tooltip($args['text']);
			if ($args['label']) {
				$fieldLabel = $args['label'];
				
			// Add mouseover and mouseout events for the tooltip
				$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . $args['label'] . $required . $tooltip . '</label>';
			}
		
			$field .= '<input type="text" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
		
			if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
				foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
					$field .= ' ' . $customattr_key . '="' . esc_attr($customattr_val) . '" ';
				}
			}
		
			// Use the value that we have ensured is from session or posted data
			$field .= 'placeholder="Write some text" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
		
			$field .= '</p>' . $after;
		
		
			return $field;
		}
		
		
		/** Save Data function. */

		public function save_data($order_id, $posted) {
			if (get_option('jwcfe_account_sync_fields') && get_option('jwcfe_account_sync_fields') == "on") {
				$types = array('account', 'billing', 'shipping', 'additional');
			} else {
				$types = array('billing', 'shipping', 'additional');
			}
		
			foreach ($types as $type) {
				$fields = JWCFE_Helper::get_fields($type);
				foreach ($fields as $name => $field) {
					if (isset($field['custom']) && $field['custom'] && isset($posted[$name])) {
						$value = $posted[$name];
						
						// Ensure the value is not an array before cleaning
						if (is_array($value)) {
							$value = implode(',', $value); // or handle it as needed
						} else {
							$value = wc_clean($value);
						}
		
						if ($value) {
							// Save the value to the session
							WC()->session->set($name, $value);
							// Save the value to order meta
							$order = wc_get_order($order_id);
							$order->update_meta_data($name, $value);
							$order->save();
						}
					}
		
					// Handle file type fields if needed (as per your original logic)
					if (isset($field['custom']) && $field['custom'] && $field["type"] == "file") {
						$value = WC()->session->get($name);
						if ($value) {
							// Ensure the value is cleaned and is a string
							if (is_array($value)) {
								$value = implode(',', $value); // or handle it as needed
							} else {
								$value = wc_clean($value);
							}
							WC()->session->set($name, $value);
							$order = wc_get_order($order_id);
							$order->update_meta_data($name, $value);
							$order->save();
						}
					}
				}
			}
		}
		

		

		public function jwcfe_woo_default_address_fields($fields)
		{
			$sname = apply_filters('jwcfe_address_field_override_with', 'billing');

			if ($sname === 'billing' || $sname === 'shipping') {
				$address_fields = get_option('jwcfe_wc_fields_' . $sname);
				if (is_array($address_fields) && !empty($address_fields) && !empty($fields)) {
					$override_required = apply_filters('jwcfe_address_field_override_required', true);
					foreach ($fields as $name => $field) {
						$fname = $sname . '_' . $name;
						if ($this->jwcfe_is_locale_field($fname) && $override_required) {
							$custom_field = (isset($address_fields[$fname]) ? $address_fields[$fname] : false);
							if ($custom_field && !(isset($custom_field['enabled']) && $custom_field['enabled'] == false)) {
								$fields[$name]['required'] = (isset($custom_field['required']) && $custom_field['required'] ? true : false);
							}
						}
					}
				}
			}
			return $fields;
		}

		public function jwcfe_prepare_country_locale($fields)
		{
			if (is_array($fields)) {
				foreach ($fields as $key => $props) {
					$override_ph = apply_filters('jwcfe_address_field_override_placeholder', true);
					$override_label = apply_filters('jwcfe_address_field_override_label', true);
					$override_required = apply_filters('jwcfe_address_field_override_required', false);
					$override_priority = apply_filters('jwcfe_address_field_override_priority', true);
					if ($override_ph && isset($props['placeholder'])) {
						unset($fields[$key]['placeholder']);
					}
					if ($override_label && isset($props['label'])) {
						unset($fields[$key]['label']);
					}
					if ($override_required && isset($props['required'])) {
						unset($fields[$key]['required']);
					}

					if ($override_priority && isset($props['priority'])) {
						unset($fields[$key]['priority']);
					}
				}
			}
			return $fields;
		}

		public function jwcfe_woo_get_country_locale($locale)
		{
			if (is_array($locale)) {
				foreach ($locale as $country => $fields) {
					$locale[$country] = $this->jwcfe_prepare_country_locale($fields);
				}
			}
			return $locale;
		}

		/**
		 * wc_checkout_fields_modify_billing_fields function.
		 *
		 * @param mixed $fields
		 */
		public function jwcfe_billing_fields_lite_paid($fields, $country)
		{
			

			global  $supress_field_modification;
			if ($supress_field_modification) {
				return $fields;
			}
			if (is_wc_endpoint_url('edit-address')) {
				return $fields;
			} else {

				if (get_option('jwcfe_account_sync_fields') && get_option('jwcfe_account_sync_fields') == "on") {
					$fields_set = array();
					if (is_array(get_option('jwcfe_wc_fields_account'))) {
						foreach (get_option('jwcfe_wc_fields_account') as $name => $field) {
							if ($name == 'account_username' || $name == 'account_password') {
								continue;
							}
							if (isset($field['type']['type']) && $field['type'] === 'hidden') {
								$field['required'] = 0;
							}
							if (isset($field['type']) && $field['type'] === 'heading') {
								$field['required'] = 0;
							}
							if (isset($field['type']) && $field['type'] === 'customcontent') {
								$field['required'] = 0;
							}

							if (isset($field['type']) && $field['type'] === 'file' && WC()->session->get($name)) {
								$field['required'] = 0;
							}

							$fields_set[$name] = $field;
						}
					}
					$billing_fields = get_option('jwcfe_wc_fields_billing');

					if (isset($billing_fields) && is_array(get_option('jwcfe_wc_fields_billing'))) {
						$billing_fields = get_option('jwcfe_wc_fields_billing');
						$fields_set = array_merge($billing_fields, $fields_set);
					}
				} else {
					$fields_set = get_option('jwcfe_wc_fields_billing');
				}
				return $this->jwcfe_prepare_address_fields_paid(
					$fields_set,
					$fields,
					'billing',
					$country
				);
			}
		}

		/**
		 * wc_checkout_fields_modify_shipping_fields function.
		 *
		 * @param mixed $old
		 */
		public function jwcfe_shipping_fields_lite($fields, $country)
		{
			global  $supress_field_modification;
			if ($supress_field_modification) {
				return $fields;
			}

			if (is_wc_endpoint_url('edit-address')) {
				return $fields;
			} else {
				return  $this->jwcfe_prepare_address_fields_paid(
					get_option('jwcfe_wc_fields_shipping'),
					$fields,
					'shipping',
					$country
				);
			}
		}

		/**
		 * wc_checkout_fields_modify_shipping_fields function.
		 *
		 * @param mixed $old
		 */
		
		public function jwcfe_checkout_fields_lite($fields) {
			global $supress_field_modification;
			
			if ($supress_field_modification) {
				return $fields;
			}
			
			// Get additional fields from options
			if ($additional_fields = get_option('jwcfe_wc_fields_additional')) {
				if (isset($fields['order']) && is_array($fields['order'])) {
					$fields['order'] = $additional_fields + $fields['order'];
				}
				
				// Check if order_comments is enabled/disabled
				if (is_array($additional_fields) && !$additional_fields['order_comments']['enabled']) {
					unset($fields['order']['order_comments']);
				}
			}
			
			// Prepare checkout fields
			if (isset($fields['order']) && is_array($fields['order'])) {
				$fields['order'] = $this->jwcfe_prepare_checkout_fields_lite_paid($fields['order'], false);
			}
			
			// Ensure 'order' is an array
			if (isset($fields['order']) && !is_array($fields['order'])) {
				unset($fields['order']);
			}
			
			return $fields;
		}

		/**
		 *
		 */
		public function jwcfe_prepare_address_fields_paid(
			$fieldset,
			$original_fieldset = false,
			$sname = 'billing',
			$country = ''
		) {

			if (is_array($fieldset) && !empty($fieldset)) {
				$locale = WC()->countries->get_country_locale();
				if (isset($locale[$country]) && is_array($locale[$country])) {
					foreach ($locale[$country] as $key => $value) {
						if (is_array($value) && isset($fieldset[$sname . '_' . $key])) {
							if (isset($value['required'])) {
								$fieldset[$sname . '_' . $key]['required'] = $value['required'];
							}
						}
					}
				}

				if (get_option('jwcfe_wc_fields_billing')) {
					$fieldset = $this->jwcfe_prepare_checkout_fields_lite_paid($fieldset, $original_fieldset, $sname);
				} else {
					$fieldset = array_merge($original_fieldset, $fieldset);
				}

				return $fieldset;
			} else {
				return $original_fieldset;
			}
		}


		/**
		 * checkout_fields_modify_fields function.
		 *
		 * @param mixed $data
		 * @param mixed $old
		 */
		
		public function jwcfe_prepare_checkout_fields_lite_paid($fields, $original_fields, $sname = "") {
		
			if (is_array($fields) && !empty($fields)) {
				foreach ($fields as $name => $field) {
					// Check if field is enabled
					if (isset($field['enabled']) && $field['enabled'] === false) {
						unset($fields[$name]);
						continue;
					}
		
					// Prepare new field
					$new_field = $original_fields && isset($original_fields[$name]) ? $original_fields[$name] : $field;
					$new_field['label'] = isset($field['label']) ? $field['label'] : '';
					$new_field['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : '';
					$new_field['class'] = isset($field['class']) && is_array($field['class']) ? $field['class'] : array();
					$new_field['validate'] = isset($field['validate']) && is_array($field['validate']) ? $field['validate'] : array();
					$new_field['required'] = isset($field['required']) ? $field['required'] : 0;
					$new_field['clear'] = isset($field['clear']) ? $field['clear'] : 0;
		
					// Add conditional field classes
					if (isset($new_field['rules_action_ajax']) && !empty($new_field['rules_action_ajax']) && isset($new_field['rules_ajax']) && !empty($new_field['rules_ajax'])) {
						$new_field['class'][] = 'jwcfe-conditional-field';
						$new_field['required'] = false;
					}
		
					if (isset($new_field['rules_action']) && !empty($new_field['rules_action']) && isset($new_field['rules']) && !empty($new_field['rules'])) {
						$new_field['class'][] = 'jwcfe-conditional-field';
					}
		
				
					// Handle specific field types
					if (isset($new_field['type']) && in_array($new_field['type'], ['file', 'hidden', 'heading', 'customcontent'])) {
						$new_field['required'] = false;
					}

					if (isset($new_field['type']) && $new_field['type'] === 'select') {
						if (apply_filters('jwcfe_enable_select2_for_select_fields', true)) {
							$new_field['input_class'][] = 'jwcfe-enhanced-select';
						}
					}
		
					// Set field order and priority
					$new_field['order'] = isset($field['order']) && is_numeric($field['order']) ? $field['order'] : 0;
					$priority = ($new_field['order'] + 1) * 10;
					$new_field['priority'] = $priority;
		
					// Translate labels and placeholders
					if (isset($new_field['label'])) {
						$new_field['label'] = __($new_field['label'], 'jwcfe');
					}
					if (isset($new_field['placeholder'])) {
						$new_field['placeholder'] = __($new_field['placeholder'], 'jwcfe');
					}
					$new_field['input_class'][] = 'jwcfe-input-field';
					$fields[$name] = $new_field;
		
				}
			}
		
			return $fields;
		}
		

		/*****************************************
     	 ----- Display Field Values - START ------
		 *****************************************/
		/**
		 * Display custom fields in emails
		 *
		 * @param array $keys
		 * @return array
		 */
		
		
		public function jwcfe_display_custom_fields_in_emails_lite($order, $sent_to_admin, $plain_text) {
			$fields_html = '';
			$fields_to_display = []; // Store fields that have data to display
		
			$fields = array_merge(
				JWCFE_Helper::get_fields('billing'),
				JWCFE_Helper::get_fields('shipping'),
				JWCFE_Helper::get_fields('additional')
			);
				
			// Retrieve order
			$order = wc_get_order($order->get_id());
		
			// Loop through all custom fields to check if they should be displayed
			foreach ($fields as $key => $options) {
		
				if (isset($options['show_in_email']) && $options['show_in_email']) {
					$value = $this->fetch_order_meta_value($order, $key, $options['type']);
		
					
					if (!empty($value)) {
						$label = (isset($options['label']) && $options['label'] ? $options['label'] : $key);
						$fields_to_display[] = [
							'label' => esc_attr($label),
							'value' => $value,
							'type'  => $options['type'] ?? ''
						];
					}
				}
			}
		
			// Only render the section if there are fields to display
			if (!empty($fields_to_display)) {
				if ($plain_text === false) {
					$sec_heading = 'Additional Fields';
					$fields_html .= '<h2>' . esc_html($sec_heading, 'jwcfe') . '</h2>';
					$fields_html .= '<table style="width: 100%; border-collapse: collapse;">'; // Start the table
					$fields_html .= '<thead><tr><th style="border: 1px solid #ddd; padding: 8px;">Field</th><th style="border: 1px solid #ddd; padding: 8px;">Value</th></tr></thead>';
					$fields_html .= '<tbody>'; // Start the body of the table
		
					// Add rows for each field
					foreach ($fields_to_display as $field) {
						$fields_html .= '<tr>';
						$fields_html .= '<th style="border: 1px solid #ddd; padding: 8px;">' . esc_html($field['label']) . '</th>';
						$fields_html .= '<td style="border: 1px solid #ddd; padding: 8px;">';
		
						if ($field['type'] === 'file') {
							$fields_html .= '<a href="' . esc_url($field['value']) . '" download>Download File</a>';
						} else {
							$fields_html .= esc_html($field['value']);
						}
		
						$fields_html .= '</td>';
						$fields_html .= '</tr>';
					}
		
					$fields_html .= '</tbody>'; // Close the table body
					$fields_html .= '</table>'; // Close the table
				} else {
					foreach ($fields_to_display as $field) {
						$fields_html .= $field['label'] . ': ' . $field['value'] . "\n";
					}
				}
			}
		
			echo $fields_html; // Output the final HTML
		}
		
		private function fetch_order_meta_value($order, $key, $type) {
			if (in_array($type, ['select', 'checkboxgroup', 'timepicker', 'multiselect'])) {
				$value = $order->get_meta($key, true);
				if (is_array($value)) {
					return implode(",", $value);
				}
			}
			return $order->get_meta($key, true);
		}
		public function jwcfe_is_locale_field($field_name)
		{
			if (!empty($field_name) && in_array($field_name, array(
				'billing_address_1',
				'billing_address_2',
				'billing_state',
				'billing_postcode',
				'billing_city',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_state',
				'shipping_postcode',
				'shipping_city'
			))) {
				return true;
			}
			return false;
		}
	}

endif;
