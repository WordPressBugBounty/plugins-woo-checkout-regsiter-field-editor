<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://jcodex.com
 *
 * @package    woo-checkout-regsiter-field-editor-premium
 * @subpackage woo-checkout-regsiter-field-editor-premium/admin
 */

if (!defined('WPINC')) {
	die;
}

if (!class_exists('JWCFE_Admin')) :

	class JWCFE_Admin 
	{
		private $plugin_name;
		private $version;
		private $screen_id;
		private $settings_fields;
		private $settings_license;
		private $locale_fields = array();


		public function __construct($plugin_name, $version)
		{
			
			$this->plugin_name = $plugin_name;
			$this->version = $version;
			$this->locale_fields = array(
				'billing_address_1', 'billing_address_2', 'billing_state', 'billing_postcode', 'billing_city',
				'shipping_address_1', 'shipping_address_2', 'shipping_state', 'shipping_postcode', 'shipping_city',
				'order_comments'
			);
		}

		

		public function enqueue_admin_scripts() {
		    wp_enqueue_style('jwcfe-newstyle', JWCFE_ASSETS_URL_ADMIN . 'css/jwcfe-newstyle.css', array(), $this->version);

		    $deps = array('jquery', 'jquery-ui-tabs', 'jquery-ui-dialog', 'jquery-ui-sortable', 'woocommerce_admin', 'select2', 'jquery-tiptip');

		    // Enqueue the polyfill script
		    wp_enqueue_script('polyfill', JWCFE_ASSETS_URL_ADMIN . 'js/polyfill.js', array('jquery'), null, true);

		    // Add 'polyfill' to the dependencies of jwcfe-admin-pro.js
		    $deps[] = 'polyfill';

		    wp_enqueue_script('jwcfe-admin-script', JWCFE_ASSETS_URL_ADMIN . 'js/jwcfe-admin-pro.js', $deps, $this->version, true);

			

			$tab = $this->get_current_tab();
			
			$fields_options = [];

			if ($tab === 'fields') {
				$fields_options['shipping'] = get_option('jwcfe_wc_fields_shipping',[]);
				$fields_options['additional'] = get_option('jwcfe_wc_fields_additional',[]);
				$fields_options['billing'] = get_option('jwcfe_wc_fields_billing',[]);
				$fields_options['account'] = get_option('jwcfe_wc_fields_account',[]);
			}else if ($tab === 'block') {
				$fields_options['shipping'] = get_option('jwcfe_wc_fields_block_shipping',[]);
				$fields_options['additional'] = get_option('jwcfe_wc_fields_block_additional',[]);
				$fields_options['billing'] = get_option('jwcfe_wc_fields_block_billing',[]);
			}
			

		    wp_localize_script('jwcfe-admin-script', 'WcfeAdmin', array(
		        'MSG_INVALID_NAME' => 'NAME cannot contain spaces',
		        'ajaxurl' => admin_url('admin-ajax.php'),
		        'wc_fields' => $fields_options,

				
		    ));
		}



		public function admin_menu()
		{
			$this->screen_id = add_submenu_page(
				'woocommerce',
				esc_html__('WooCommerce Checkout & Register Form Editor', 'jwcfe'),
				esc_html__('Checkout & Register Editor', 'jwcfe'),
				'manage_woocommerce',
				'jwcfe_checkout_register_editor',
				array($this, 'the_editor')
			);
		}

		public function add_screen_id($ids)
		{
			$ids[] = 'woocommerce_page_jwcfe_checkout_register_editor';
			$ids[] = strtolower(esc_html__('WooCommerce', 'jwcfe')) . '_page_jwcfe_checkout_register_editor';
			return $ids;
		}




		public function the_editor()
		{

			$Fields_settings = JWCFE_Admin_Settings_Fields::instance($this->plugin_name, $this->version);
			$blocks_Setting = new JWCFE_Admin_Settings_Block_Fields($this->plugin_name, $this->version);

			$tab = $this->get_current_tab();

			if ($tab === 'fields') {
				$Fields_settings->checkout_form_field_editor();
			}else if ($tab === 'block') {
				$blocks_Setting->checkout_form_field_editor();
			}
			
		}
		

		// public function get_current_tab()
		// {
		// 	return isset($_GET['tab']) ? esc_attr($_GET['tab']) : 'fields';
		// }


		// public function get_current_section()
		// {
		// 	$tab = $this->get_current_tab();
		// 	$section = '';
		// 	if ($tab === 'fields') {
		// 		$section = isset($_GET['section']) ? esc_attr($_GET['section']) : 'billing';
		// 	}
		// 	return $section;
		// }
		public function get_current_tab()
		{
			$allowed_tabs = array('fields', 'block'); // Define allowed tabs
			$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'fields';
		
			return in_array($tab, $allowed_tabs) ? $tab : 'fields';
		}
		
		// public function get_current_section()
		// {
		// 	$tab = $this->get_current_tab();
			
		// 	// Define sections based on the selected tab
		// 	$sections_by_tab = array(
		// 		'fields' => array('billing', 'shipping', 'additional', 'account'),
		// 		'block'  => array('billing', 'shipping', 'additional'),
		// 	);
		
		// 	$default_section = 'billing'; // Default section
		// 	$sections = isset($sections_by_tab[$tab]) ? $sections_by_tab[$tab] : array();
		
		// 	if (isset($_GET['section']) && in_array($_GET['section'], $sections)) {
		// 		return sanitize_text_field($_GET['section']);
		// 	}
		
		// 	return $default_section;
		// }
		public function get_current_section()
{
    $tab = $this->get_current_tab();
    
    // Define sections based on the selected tab
    $sections_by_tab = array(
        'fields' => array('billing', 'shipping', 'additional', 'account'),
        'block'  => array('billing', 'shipping', 'additional'),
    );

    $default_section = 'billing'; // Default section
    $sections = isset($sections_by_tab[$tab]) ? $sections_by_tab[$tab] : array();

    if (isset($_GET['section']) && in_array($_GET['section'], $sections)) {
        return sanitize_text_field($_GET['section']);
    }

    return $default_section;
}

		public function get_all_variations_of_product()
		{
			if (isset($_POST['pID']) && is_array($_POST['pID']) && count($_POST['pID']) >= 1) {
				$product_ids = $_POST['pID'];
				$selected_variations = $_POST['selected_variations'];
                $attributes_dropdown = '';
                foreach ($product_ids as $product_id) {
                    $product = wc_get_product($product_id);
                    $title = $product->get_name();

                    if ($product && $product->is_type('variable')) {
                        $variations = $product->get_available_variations(); // Get all available variations
                        $attributes_dropdown .= '<select multiple="multiple" name="i_rule_operand_variation[]" data-placeholder="' . $title . ' to choose variations" class="jwcfe-enhanced-multi-select2 jwcfe-enhanced-multi-variations" style="width:200px;" value="">';
                        $variation_data = array();
                        foreach ($variations as $variation) {
                            $variation_id = $variation['variation_id'];
                            $variation_obj = wc_get_product($variation_id);
                            $variation_name = implode(", ", $variation_obj->get_variation_attributes()); // Get variation attributes as a string
                            $variation_price = $variation_obj->get_price(); // Get variation price
                            $variation_data[] = array(
                                'variation_id' => $variation_id,
                                'variation_name' => $variation_name,
                                'variation_price' => $variation_price
                            );
                            $selected = '';
                            if(!empty($selected_variations) && in_array($variation_id, $selected_variations)){
                                $selected = 'selected';
                            }
                            $attributes_dropdown .= '<option value="' . $variation_id . '" '.$selected.'>' . $variation_name . ' - $' . $variation_price . '</option>';
                        }
                        $attributes_dropdown .= '</select>';
                    }
                }
			}
            echo $attributes_dropdown;
			wp_die();
		}


		public function render_tabs_and_sections()
		
		{
			?>

				<div id="message" style="display:none; border-left-color: #6B2C88" class="wc-connect updated wcfe-notice">
					<div class="squeezer">
						<table>
							<tr>
								<td width="70%">
									<p><strong><i><?php esc_html_e('Custom Fields WooCommerce Checkout Page Pro Version','jwcfe'); ?></i></strong>
										<?php esc_html_e('premium version provides more features to design your checkout and my account page.','jwcfe'); ?>
									</p>
									<ul>
										<li><?php esc_html_e('18 field types are available: 15 input fields one field for title/heading and one for label.','jwcfe'); ?><br />(<i><?php esc_html_e('Text, Hidden, Password, Textarea, Radio, Checkbox, Select, Multi-select, Date Picker, Heading, Label','jwcfe'); ?></i>).
										</li>
										<li><?php esc_html_e('You can add all of these fields on my account page too.','jwcfe'); ?></li>
										<li><?php esc_html_e('You can add more sections in addition to the core sections (billing, shipping and additional) in checkout page.','jwcfe'); ?>
										</li>
										<li><?php esc_html_e('You Can Integration of My Account With Checkout page','jwcfe'); ?></li>
										<li><?php esc_html_e('Add Conditionally based fields','jwcfe'); ?></li>
				
										<li><?php esc_html_e('See Plugin','jwcfe'); ?> <a
												href="<?php echo esc_url('https://jcodex.com/dev/docs/woocommerce-custom-checkout-field-editor/'); ?>" target="_blank"
										class="doclink" ><?php esc_html_e('Documentation','jwcfe'); ?></a>
										</li>
										<li><?php esc_html_e('You can talk to support any time if you have any queries','jwcfe'); ?> <a
												href="<?php echo esc_url('https://jcodex.com/contact-us/'); ?>"><?php esc_html_e('Click here','jwcfe'); ?></a>
										</li>
				
										<li><?php esc_html_e('IF you found this plugin helpful,','jwcfe'); ?> <a
												href="<?php echo esc_url('https://www.paypal.com/donate/?hosted_button_id=QD4H8N3QVLLML'); ?>"
												style=""><?php esc_html_e('Donate using PayPal','jwcfe'); ?></a></li>
									</ul>
								</td>
				
								<td>
									<a href="https://jcodex.com/plugins/woocommerce-custom-checkout-field-editor/" target="_blank">
									<button id="purchase"
										style="background: url('<?php echo plugins_url('/assets/css/upgrade.png', __FILE__); ?>'); 
											width: 302px; 
											height: 63px; 
											cursor: pointer; 
											border: none;">
										&nbsp;
									</button>
				
									</a>
									<br>
								</td>
							</tr>
						</table>
				
					</div>
				</div>

			<?php
			// $tabs = array('fields' => 'Checkout & Account Fields');
			// $tab  = isset($_GET['tab']) ? esc_attr($_GET['tab']) : 'fields';
			// $sections = '';
			// $section  = '';

			// if ($tab === 'fields') {
			// 	$sections = array('billing', 'shipping', 'additional', 'account');
			// 	$section  = isset($_GET['section']) ? esc_attr($_GET['section']) : 'billing';
			// }
			// $tabs = array(
			// 	'fields' => 'Checkout & Account Fields',
			// 	'block'  => 'Block Checkout Fields'
			// );
			
			// $allowed_tabs = array_keys($tabs); // Use keys directly
			// $tab = isset($_GET['tab']) && in_array($_GET['tab'], $allowed_tabs) ? sanitize_text_field($_GET['tab']) : 'fields';
			
			// $sections = array();
			// $section  = 'billing';
			
			// if ($tab === 'fields') {
			// 	$sections = array('billing', 'shipping', 'additional', 'account');
			// } elseif ($tab === 'block') {
			// 	$sections = array('billing', 'shipping', 'additional');
			// }
			
			// // Validate the section
			// if (isset($_GET['section']) && in_array($_GET['section'], $sections)) {
			// 	$section = sanitize_text_field($_GET['section']);
			// }
			
			// echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';

			// foreach ($tabs as $key => $value) {
			// 	$active = ($key == $tab) ? 'nav-tab-active' : '';
			// 	echo '<a class="nav-tab ' . $active . '" href="' . admin_url('admin.php?page=jwcfe_checkout_register_editor&tab=' . $key) . '">' . $value . '</a>';
			// }
			// echo '</h2>';

			// if (!empty($sections)) {
			// 	echo '<ul class="jwcfe-sections">';
			// 	$size = sizeof($sections);
			// 	$i = 0;
			// 	foreach ($sections as $key) {
			// 		$i++;
			// 		$active = ($key == $section) ? 'current' : '';

			// 		$url = 'admin.php?page=jwcfe_checkout_register_editor&tab=fields&section=' . $key;
			// 		echo '<li>';
			// 		echo '<a href="' . admin_url($url) . '" class="' . $active . '" >' . ucwords($key) . ' ' . esc_html__('Fields', 'jwcfe') . ' <span class="circle"></span></a>';
			// 		echo ($size > $i) ? '' : '';
			// 		echo '</li>';
			// 	}
			// 	echo '</ul>';
			$tabs = array(
				'fields' => 'Checkout & Account Fields',
				'block'  => 'Block Checkout Fields'
			);
			
			$allowed_tabs = array_keys($tabs); // Use keys directly
			$tab = isset($_GET['tab']) && in_array($_GET['tab'], $allowed_tabs) ? sanitize_text_field($_GET['tab']) : 'fields';
			
			$sections = array();
			$section  = '';
			
			if ($tab === 'fields') {
				$sections = array('billing', 'shipping', 'additional', 'account');
			} elseif ($tab === 'block') {
				$sections = array('billing', 'shipping', 'additional');
			}
			
			// Validate the section
			if (isset($_GET['section']) && in_array($_GET['section'], $sections)) {
				$section = sanitize_text_field($_GET['section']);
			}
			
			echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';
			foreach ($tabs as $key => $value) {
				$active = ($key == $tab) ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . esc_attr($active) . '" href="' . esc_url(admin_url('admin.php?page=jwcfe_checkout_register_editor&tab=' . $key)) . '">' . esc_html($value) . '</a>';
			}
			echo '</h2>';
			
			// if (!empty($sections)) {
			// 	echo '<ul class="jwcfe-sections">';
			// 	$size = count($sections);
			// 	$i = 0;
			// 	foreach ($sections as $key) {
			// 		$i++;
			// 		$active = ($key == $section) ? 'current' : '';
			// 		$url = 'admin.php?page=jwcfe_checkout_register_editor&tab=' . $tab . '&section=' . $key;
					
			// 		echo '<li>';
			
			// 		// Customize display text only for the "block" tab
			// 		if ($tab === 'block') {
			// 			$display_text = ($key === 'billing') ? 'Contact Information' :
			// 				(($key === 'shipping') ? 'Address' :
			// 				(($key === 'additional') ? 'Additional Information' : ucwords($key) . ' Fields'));
			// 		} else {
			// 			// Default display text for other tabs
			// 			$display_text = ($key === 'billing') ? 'Billing Fields' :
			// 				(($key === 'shipping') ? 'Shipping Fields' :
			// 				(($key === 'additional') ? 'Additional Fields' : ucwords($key) . ' Fields'));
			// 		}
			
			// 		echo '<a href="' . esc_url(admin_url($url)) . '" class="' . esc_attr($active) . '" >' . esc_html__($display_text, 'jwcfe') . ' <span class="circle"></span></a>'; 
			// 		echo ($size > $i) ? '' : '';
			// 		echo '</li>';
			// 	}
			// 	echo '</ul>';
			// }
			$section = $this->get_current_section(); // Get the current section

if (!empty($sections)) {
    echo '<ul class="jwcfe-sections">';
    foreach ($sections as $key) {
        $active = ($key === $section) ? 'current' : ''; // Assign "current" class if active
        $url = 'admin.php?page=jwcfe_checkout_register_editor&tab=' . esc_attr($tab) . '&section=' . esc_attr($key);

        echo '<li>';
        
        // Customize display text only for the "block" tab
        if ($tab === 'block') {
            $display_text = ($key === 'billing') ? 'Contact Information' :
                (($key === 'shipping') ? 'Address' :
                (($key === 'additional') ? 'Additional Information' : ucwords($key) . ' Fields'));
        } else {
            // Default display text for other tabs
            $display_text = ($key === 'billing') ? 'Billing Fields' :
                (($key === 'shipping') ? 'Shipping Fields' :
                (($key === 'additional') ? 'Additional Fields' : ucwords($key) . ' Fields'));
        }

        echo '<a href="' . esc_url(admin_url($url)) . '" class="' . esc_attr($active) . '">' . 
                esc_html__($display_text, 'jwcfe') . 
                ' <span class="circle"></span>' . 
             '</a>';
        
        echo '</li>';
    }
    echo '</ul>';
}

		}


		public function save_options($section)
		{
			$tab = $this->get_current_tab();
			// error_log($tab);
			// if($tab==='fields'){
				if (isset($_POST['woo_checkout_editor_nonce']) && wp_verify_nonce($_POST['woo_checkout_editor_nonce'], 'woo_checkout_editor_settings')) {
					// Handle settings saving
					$o_fields      = JWCFE_Helper::get_fields($section);
					$fields = $o_fields;
					$f_order       = !empty($_POST['f_order']) ? $_POST['f_order'] : array();
					$f_names       = !empty($_POST['f_name']) ? $_POST['f_name'] : array();
					$f_names_new   = !empty($_POST['f_name_new']) ? $_POST['f_name_new'] : array();
					$f_types       = !empty($_POST['f_type']) ? $_POST['f_type'] : array();
					$f_labels      = !empty($_POST['f_label']) ? $_POST['f_label'] : array();
					$f_extoptions     = !empty($_POST['f_extoptions']) ? $_POST['f_extoptions'] : array();
					$f_access    = !empty($_POST['f_access']) ? $_POST['f_access'] : array();
					$f_placeholder = !empty($_POST['f_placeholder']) ? $_POST['f_placeholder'] : array();
					$i_min_time = !empty($_POST['i_min_time']) ? $_POST['i_min_time'] : array();
					$i_max_time = !empty($_POST['i_max_time']) ? $_POST['i_max_time'] : array();
					$i_time_step = !empty($_POST['i_time_step']) ? $_POST['i_time_step'] : array();
					$i_time_format = !empty($_POST['i_time_format']) ? $_POST['i_time_format'] : array();
					$f_maxlength = !empty($_POST['f_maxlength']) ? $_POST['f_maxlength'] : array();

					if (isset($_POST['f_options'])) {
						$f_options     = !empty($_POST['f_options']) ? $_POST['f_options'] : array();
					}

					$f_text      = !empty($_POST['f_text']) ? $_POST['f_text'] : array();

					if (isset($_POST['f_rules_action'])) {
						if (!empty($_POST['f_rules_action'])) {
							$f_rules_action = $_POST['f_rules_action'];
						} else {
							$f_rules_action = array();
						}
					}

					

					$f_rules = !empty($_POST['f_rules']) ? $_POST['f_rules'] : '';

					if (isset($_POST['f_rules_action_ajax'])) {
						if (!empty($_POST['f_rules_action_ajax'])) {
							$f_rules_action_ajax = $_POST['f_rules_action_ajax'];
						} else {
							$f_rules_action_ajax = array();
						}
					}

					

					$f_rules_ajax = !empty($_POST['f_rules_ajax'])? $_POST['f_rules_ajax'] : '';


					// $f_label_class  = !empty($_POST['f_label_class']) ? $_POST['f_label_class'] : array();
					$f_label_class_raw = $_POST['f_label_class'] ?? [];

					if (is_array($f_label_class_raw)) {
						$f_label_class = array_filter($f_label_class_raw, function ($val) {
							return !empty($val) && $val !== 'undefined';
						});
					} elseif (is_string($f_label_class_raw) && $f_label_class_raw !== 'undefined') {
						$f_label_class = [$f_label_class_raw];
					} else {
						$f_label_class = [];
					}
					
					$f_class       = !empty($_POST['f_class']) ? $_POST['f_class'] : array();
					$f_required    = !empty($_POST['f_required']) ? $_POST['f_required'] : array();
					$f_is_include    = !empty($_POST['f_is_include']) ? $_POST['f_is_include'] : array();
					$f_enabled     = !empty($_POST['f_enabled']) ? $_POST['f_enabled'] : array();
					$f_show_in_email = !empty($_POST['f_show_in_email']) ? $_POST['f_show_in_email'] : array();
					$f_show_in_order = !empty($_POST['f_show_in_order']) ? $_POST['f_show_in_order'] : array();
					$f_validation  = !empty($_POST['f_validation']) ? $_POST['f_validation'] : array();
					$f_deleted     = !empty($_POST['f_deleted']) ? $_POST['f_deleted'] : array();
					$f_position        = !empty($_POST['f_position']) ? $_POST['f_position'] : array();
					$f_display_options = !empty($_POST['f_display_options']) ? $_POST['f_display_options'] : array();
					
					// $max ='';

					$max = max(array_map('absint', array_keys($f_names)));

					for ($i = 0; $i <= $max; $i++) {
						$name     = empty($f_names[$i]) ? '' : urldecode(sanitize_title(wc_clean(stripslashes($f_names[$i]))));
						$new_name = empty($f_names_new[$i]) ? '' : urldecode(sanitize_title(wc_clean(stripslashes($f_names_new[$i]))));


						if (!empty($f_deleted[$i]) && $f_deleted[$i] == 1) {
							unset($fields[$name]);
							continue;
						}

						// Check reserved names
						if ($this->is_reserved_field_name($new_name)) {
							continue;
						}

						//if update field
						if ($name && $new_name && $new_name !== $name) {

							if (isset($fields[$name])) {
								$fields[$new_name] = $fields[$name];
							} else {
								$fields[$new_name] = array();
							}
							unset($fields[$name]);
							$name = $new_name;
						} else {
							$name = $name ? $name : $new_name;
						}

						if (!$name) {
							continue;
						}


						if ( $f_types[$i] == 'file' && empty( $f_extoptions[$i] ) ) {
							echo '<div class="error"><p>' . esc_html__( 'Allowed file types input field must be specified for file fields!.', 'jwcfe' ) . '</p></div>';
							continue;
						}


						// if new field

						if (!isset($fields[$name])) {
							$fields[$name] = array();
						}
						$o_type  = isset($o_fields[$name]['type']) ? $o_fields[$name]['type'] : 'text';

						$allowed_tags = array(
							'a' => array(
								'class' => array(),
								'href'  => array(),
								'rel'   => array(),
								'title' => array(),
							),
							'abbr' => array(
								'title' => array(),
							),
							'b' => array(),
							'blockquote' => array(
								'cite'  => array(),
							),
							'cite' => array(
								'title' => array(),
							),
							'code' => array(),
							'del' => array(
								'datetime' => array(),
								'title' => array(),
							),

							'dd' => array(),
							'div' => array(
								'class' => array(),
								'title' => array(),
								'style' => array(),
							),
							'dl' => array(),
							'dt' => array(),
							'em' => array(),
							'h1' => array(),
							'h2' => array(),
							'h3' => array(),
							'h4' => array(),
							'h5' => array(),
							'h6' => array(),
							'i' => array(),
							'img' => array(
								'alt'    => array(),
								'class'  => array(),
								'height' => array(),
								'src'    => array(),
								'width'  => array(),
							),

							'li' => array(
								'class' => array(),
							),
							'ol' => array(
								'class' => array(),
							),
							'p' => array(
								'class' => array(),
							),
							'q' => array(
								'cite' => array(),
								'title' => array(),
							),
							'span' => array(
								'class' => array(),
								'title' => array(),
								'style' => array(),
							),
							'strike' => array(),
							'strong' => array(),
							'ul' => array(
								'class' => array(),
							),
						);
						$fields[$name]['type']    	  = empty($f_types[$i]) ? $o_type : wc_clean($f_types[$i]);
						$fields[$name]['label']   	  = empty($f_labels[$i]) ? '' : wp_kses_post(trim(stripslashes($f_labels[$i])));
						$fields[$name]['text']   	  = empty($f_text[$i]) ? '' : $f_text[$i];
						$fields[$name]['access']    = empty($f_access[$i]) ? false : true;
						$fields[$name]['placeholder'] = empty($f_placeholder[$i]) ? '' : wc_clean(stripslashes($f_placeholder[$i]));
						$fields[$name]['min_time'] = empty($i_min_time[$i]) ? '' : wc_clean(stripslashes($i_min_time[$i]));
						$fields[$name]['max_time'] = empty($i_max_time[$i]) ? '' : wc_clean(stripslashes($i_max_time[$i]));
						$fields[$name]['time_step'] = empty($i_time_step[$i]) ? '' : wc_clean(stripslashes($i_time_step[$i]));
						$fields[$name]['time_format'] = empty($i_time_format[$i]) ? '' : wc_clean(stripslashes($i_time_format[$i]));
						$fields[$name]['options_json'] 	  = empty($f_options[$i]) ? '' : json_decode(urldecode($f_options[$i]), true);
						$fields[$name]['maxlength'] = empty($f_maxlength[$i]) ? '' : wc_clean(stripslashes($f_maxlength[$i]));
						$fields[$name]['class'] 	  = empty($f_class[$i]) ? array() : array_map('wc_clean', explode(',', $f_class[$i]));
						$fields[$name]['label_class'] = empty($f_label_class[$i]) ? array() : array_map('wc_clean', explode(',', $f_label_class[$i]));
						$fields[$name]['rules_action']    = empty($f_rules_action[$i]) ? '' : $f_rules_action[$i];
						$fields[$name]['rules']    = empty($f_rules[$i]) ? '' : $f_rules[$i];
						$fields[$name]['rules_action_ajax']    = empty($f_rules_action_ajax[$i]) ? '' : $f_rules_action_ajax[$i];
						$fields[$name]['rules_ajax']    = empty($f_rules_ajax[$i]) ? '' : $f_rules_ajax[$i];
						$fields[$name]['required']    = empty($f_required[$i]) ? false : true;
						$fields[$name]['is_include']    = empty($f_is_include[$i]) ? false : true;
						$fields[$name]['enabled']     = empty($f_enabled[$i]) ? false : true;
						$fields[$name]['order']       = empty($f_order[$i]) ? '' : wc_clean($f_order[$i]);

						if (!in_array($name, $this->locale_fields)) {
							$fields[$name]['validate'] = empty($f_validation[$i]) ? array() : explode(',', $f_validation[$i]);
						}

						$fields[$name]['extoptions'] 	  = empty($f_extoptions[$i]) ? array() : explode(',', $f_extoptions[$i]);

						if (!$this->is_default_field_name($name)) {
							$fields[$name]['custom'] = true;
							$fields[$name]['show_in_email'] = empty($f_show_in_email[$i]) ? false : true;
							$fields[$name]['show_in_order'] = empty($f_show_in_order[$i]) ? false : true;
						} else {
							$fields[$name]['custom'] = false;
						}

						$fields[$name]['label']   	  = $fields[$name]['label'];
						$fields[$name]['placeholder'] = esc_html__($fields[$name]['placeholder'], 'woocommerce');
						$fields[$name]['maxlength'] = esc_html__($fields[$name]['maxlength'], 'woocommerce');
					}

					uasort($fields, array($this, 'sort_fields_by_order'));
					if($tab === 'fields'){
						$result = update_option('jwcfe_wc_fields_' . $section, $fields);


					}else if ($tab === 'block') {
						if (isset($_POST['woo_checkout_editor_nonce']) && wp_verify_nonce($_POST['woo_checkout_editor_nonce'], 'woo_checkout_editor_settings')) {
							// Handle settings saving
							$o_fields      = JWCFE_Helper::get_fields($section);
							$fields = $o_fields;
							$f_order       = !empty($_POST['f_order']) ? $_POST['f_order'] : array();
							$f_names       = !empty($_POST['f_name']) ? $_POST['f_name'] : array();
							$f_names_new   = !empty($_POST['f_name_new']) ? $_POST['f_name_new'] : array();
							$f_types       = !empty($_POST['f_type']) ? $_POST['f_type'] : array();
							$f_labels      = !empty($_POST['f_label']) ? $_POST['f_label'] : array();
							$f_extoptions     = !empty($_POST['f_extoptions']) ? $_POST['f_extoptions'] : array();
							$f_access    	= !empty($_POST['f_access']) ? $_POST['f_access'] : array();
							$f_placeholder = !empty($_POST['f_placeholder']) ? $_POST['f_placeholder'] : array();
							$i_min_time	= !empty($_POST['i_min_time']) ? $_POST['i_min_time'] : array();
							$i_max_time = !empty($_POST['i_max_time']) ? $_POST['i_max_time'] : array();
							$i_time_step = !empty($_POST['i_time_step']) ? $_POST['i_time_step'] : array();
							$i_time_format = !empty($_POST['i_time_format']) ? $_POST['i_time_format'] : array();
							$f_maxlength = !empty($_POST['f_maxlength']) ? $_POST['f_maxlength'] : array();
		
							if (isset($_POST['f_options'])) {
								$f_options     = !empty($_POST['f_options']) ? $_POST['f_options'] : array();
							}
		
							$f_text      = !empty($_POST['f_text']) ? $_POST['f_text'] : array();
		
							if (isset($_POST['f_rules_action'])) {
								if (!empty($_POST['f_rules_action'])) {
									$f_rules_action = $_POST['f_rules_action'];
								} else {
									$f_rules_action = array();
								}
							}
		
							
		
							$f_rules = !empty($_POST['f_rules']) ? $_POST['f_rules'] : '';
		
							if (isset($_POST['f_rules_action_ajax'])) {
								if (!empty($_POST['f_rules_action_ajax'])) {
									$f_rules_action_ajax = $_POST['f_rules_action_ajax'];
								} else {
									$f_rules_action_ajax = array();
								}
							}
		
							
		
							$f_rules_ajax = !empty($_POST['f_rules_ajax'])? $_POST['f_rules_ajax'] : '';
		
		
							$f_label_class  = !empty($_POST['f_label_class']) ? $_POST['f_label_class'] : array();
							$f_class       = !empty($_POST['f_class']) ? $_POST['f_class'] : array();
							$f_required    = !empty($_POST['f_required']) ? $_POST['f_required'] : array();
							$f_is_include    = !empty($_POST['f_is_include']) ? $_POST['f_is_include'] : array();
							$f_enabled     = !empty($_POST['f_enabled']) ? $_POST['f_enabled'] : array();
							$f_show_in_email = !empty($_POST['f_show_in_email']) ? $_POST['f_show_in_email'] : array();
							$f_show_in_order = !empty($_POST['f_show_in_order']) ? $_POST['f_show_in_order'] : array();
							$f_validation  = !empty($_POST['f_validation']) ? $_POST['f_validation'] : array();
							$f_deleted     = !empty($_POST['f_deleted']) ? $_POST['f_deleted'] : array();
							$f_position        = !empty($_POST['f_position']) ? $_POST['f_position'] : array();
							$f_display_options = !empty($_POST['f_display_options']) ? $_POST['f_display_options'] : array();
		
							$max = max(array_map('absint', array_keys($f_names)));
		
							for ($i = 0; $i <= $max; $i++) {
								$name     = empty($f_names[$i]) ? '' : urldecode(sanitize_title(wc_clean(stripslashes($f_names[$i]))));
								$new_name = empty($f_names_new[$i]) ? '' : urldecode(sanitize_title(wc_clean(stripslashes($f_names_new[$i]))));
		
		
								if (!empty($f_deleted[$i]) && $f_deleted[$i] == 1) {
									unset($fields[$name]);
									continue;
								}
		
								// Check reserved names
								if ($this->is_reserved_field_name($new_name)) {
									continue;
								}
		
								//if update field
								if ($name && $new_name && $new_name !== $name) {
		
									if (isset($fields[$name])) {
										$fields[$new_name] = $fields[$name];
									} else {
										$fields[$new_name] = array();
									}
									unset($fields[$name]);
									$name = $new_name;
								} else {
									$name = $name ? $name : $new_name;
								}
		
								if (!$name) {
									continue;
								}
		
		
								if ( $f_types[$i] == 'file' && empty( $f_extoptions[$i] ) ) {
									echo '<div class="error"><p>' . esc_html__( 'Allowed file types input field must be specified for file fields!.', 'jwcfe' ) . '</p></div>';
									continue;
								}
		
		
								// if new field
		
								if (!isset($fields[$name])) {
									$fields[$name] = array();
								}
								$o_type  = isset($o_fields[$name]['type']) ? $o_fields[$name]['type'] : 'text';
		
								$allowed_tags = array(
									'a' => array(
										'class' => array(),
										'href'  => array(),
										'rel'   => array(),
										'title' => array(),
									),
									'abbr' => array(
										'title' => array(),
									),
									'b' => array(),
									'blockquote' => array(
										'cite'  => array(),
									),
									'cite' => array(
										'title' => array(),
									),
									'code' => array(),
									'del' => array(
										'datetime' => array(),
										'title' => array(),
									),
		
									'dd' => array(),
									'div' => array(
										'class' => array(),
										'title' => array(),
										'style' => array(),
									),
									'dl' => array(),
									'dt' => array(),
									'em' => array(),
									'h1' => array(),
									'h2' => array(),
									'h3' => array(),
									'h4' => array(),
									'h5' => array(),
									'h6' => array(),
									'i' => array(),
									'img' => array(
										'alt'    => array(),
										'class'  => array(),
										'height' => array(),
										'src'    => array(),
										'width'  => array(),
									),
		
									'li' => array(
										'class' => array(),
									),
									'ol' => array(
										'class' => array(),
									),
									'p' => array(
										'class' => array(),
									),
									'q' => array(
										'cite' => array(),
										'title' => array(),
									),
									'span' => array(
										'class' => array(),
										'title' => array(),
										'style' => array(),
									),
									'strike' => array(),
									'strong' => array(),
									'ul' => array(
										'class' => array(),
									),
								);
								$fields[$name]['type']    	  = empty($f_types[$i]) ? $o_type : wc_clean($f_types[$i]);
								$fields[$name]['label']   	  = empty($f_labels[$i]) ? '' : wp_kses_post(trim(stripslashes($f_labels[$i])));
								$fields[$name]['text']   	  = empty($f_text[$i]) ? '' : $f_text[$i];
								$fields[$name]['access']    = empty($f_access[$i]) ? false : true;
								$fields[$name]['placeholder'] = empty($f_placeholder[$i]) ? '' : wc_clean(stripslashes($f_placeholder[$i]));
								$fields[$name]['min_time'] = empty($i_min_time[$i]) ? '' : wc_clean(stripslashes($i_min_time[$i]));
								$fields[$name]['max_time'] = empty($i_max_time[$i]) ? '' : wc_clean(stripslashes($i_max_time[$i]));
								$fields[$name]['time_step'] = empty($i_time_step[$i]) ? '' : wc_clean(stripslashes($i_time_step[$i]));
								$fields[$name]['time_format'] = empty($i_time_format[$i]) ? '' : wc_clean(stripslashes($i_time_format[$i]));
								$fields[$name]['options_json'] 	  = empty($f_options[$i]) ? '' : json_decode(urldecode($f_options[$i]), true);
								$fields[$name]['maxlength'] = empty($f_maxlength[$i]) ? '' : wc_clean(stripslashes($f_maxlength[$i]));
								$fields[$name]['class'] 	  = empty($f_class[$i]) ? array() : array_map('wc_clean', explode(',', $f_class[$i]));
								$fields[$name]['label_class'] = empty($f_label_class[$i]) ? array() : array_map('wc_clean', explode(',', $f_label_class[$i]));
								$fields[$name]['rules_action']    = empty($f_rules_action[$i]) ? '' : $f_rules_action[$i];
								$fields[$name]['rules']    = empty($f_rules[$i]) ? '' : $f_rules[$i];
								$fields[$name]['rules_action_ajax']    = empty($f_rules_action_ajax[$i]) ? '' : $f_rules_action_ajax[$i];
								$fields[$name]['rules_ajax']    = empty($f_rules_ajax[$i]) ? '' : $f_rules_ajax[$i];
								$fields[$name]['required']    = empty($f_required[$i]) ? false : true;
								$fields[$name]['is_include']    = empty($f_is_include[$i]) ? false : true;
								$fields[$name]['enabled']     = empty($f_enabled[$i]) ? false : true;
								$fields[$name]['order']       = empty($f_order[$i]) ? '' : wc_clean($f_order[$i]);
		
								if (!in_array($name, $this->locale_fields)) {
									$fields[$name]['validate'] = empty($f_validation[$i]) ? array() : explode(',', $f_validation[$i]);
								}
		
								$fields[$name]['extoptions'] 	  = empty($f_extoptions[$i]) ? array() : explode(',', $f_extoptions[$i]);
		
								if (!$this->is_default_field_name($name)) {
									$fields[$name]['custom'] = true;
									$fields[$name]['show_in_email'] = empty($f_show_in_email[$i]) ? false : true;
									$fields[$name]['show_in_order'] = empty($f_show_in_order[$i]) ? false : true;
								} else {
									$fields[$name]['custom'] = false;
								}
		
								$fields[$name]['label']   	  = $fields[$name]['label'];
								$fields[$name]['placeholder'] = esc_html__($fields[$name]['placeholder'], 'woocommerce');
								$fields[$name]['maxlength'] = esc_html__($fields[$name]['maxlength'], 'woocommerce');
							}
							$excluded_fields = ['billing_first_name', 'billing_last_name', 'billing_country', 'billing_address_1', 'billing_city'];

							// Remove excluded fields before saving
							foreach ($excluded_fields as $field) {
								unset($fields[$field]);
							}
							
							// Sort fields before saving
							uasort($fields, array($this, 'sort_fields_by_order'));
							$result = update_option('jwcfe_wc_fields_block_' . $section, $fields);
							if ($result == true) {
								echo '<div class="updated"><p>' . esc_html__('Your changes were saved.', 'jwcfe') . '</p></div>';
							} else {
								echo '<div class="error"><p> ' . esc_html__('Your changes were not saved due to an error (or you made none!).', 'jwcfe') . '</p></div>';
							}
						} else {
							wp_die('Security check failed. Please try again or contact support for assistance.', 'Security Error');
						}
					}
					// else if ($tab === 'block') {
					// 	if (!isset($section) || !isset($fields) || !is_array($fields)) {
					// 		error_log("Error: Missing required variables or invalid data for the block tab.");
					// 		return;
					// 	}
					// 	foreach ($fields as $index => &$field_data) { // Handle only select, checkbox, and text fields

					// 		if (!is_array($field_data)) continue;
						
					// 		// Extract field type (default to 'text' if missing)
					// 		$field_type = isset($field_data['type']) ? sanitize_text_field($field_data['type']) : 'text';
						
					// 		// Ensure 'options_json' is properly formatted or remove if empty
					// 		if (isset($field_data['options_json']) && empty($field_data['options_json'][0]['key'])) {
					// 			unset($field_data['options_json']);
					// 		}
						
					// 		// Convert 'undefined' strings to empty and sanitize inputs
					// 		foreach (['rules_action', 'rules_action_ajax'] as $key) {
					// 			if (isset($field_data[$key])) {
					// 				$field_data[$key] = $field_data[$key] === 'undefined' ? '' : sanitize_text_field($field_data[$key]);
					// 			}
					// 		}
						
					// 		// Handle only the required field types
					// 		if (in_array($field_type, ['text', 'checkbox', 'select'])) {
					// 			switch ($field_type) {
					// 				case 'text':
					// 					if (isset($field_data['value'])) {
					// 						$field_data['value'] = sanitize_text_field($field_data['value']);
					// 					}
					// 					break;
						
					// 				case 'checkbox':
										
					// 					if (isset($field_data['value'])) {
					// 						$field_data['value'] = ($field_data['value'] === '1' || $field_data['value'] === 'true') ? '1' : '0';
					// 					}
					// 					break;
					// 				case 'select':
					// 						if (isset($field_data['value'])) {
					// 							$field_data['value'] = sanitize_text_field($field_data['value']);
					// 						}
					// 						if (isset($field_data['options_json']) && is_array($field_data['options_json'])) {
					// 							foreach ($field_data['options_json'] as &$option) {
					// 								if (isset($option['key']) && isset($option['value'])) {
					// 									$option['key'] = sanitize_text_field($option['key']);
					// 									$option['value'] = sanitize_text_field($option['value']);
					// 								}
					// 							}
					// 							unset($option);
					// 						}
					// 						break;
					// 			}
					// 		} else {
					// 			// Ignore other field types
					// 			error_log("Skipping unsupported field type: " . $field_type);
					// 		}
						
					// 		error_log("Processed Field: " . print_r($field_data, true));
					// 	}
					// 	unset($field_data); // Break reference
						
					// 	// Prepare option name
					// 	$option_name = 'jwcfe_wc_fields_block_' . sanitize_key($section);
					// 	error_log($option_name . ' - ' . maybe_serialize($fields));

					// 	// Use maybe_serialize to handle arrays properly
					// 	$result = update_option($option_name, maybe_serialize($fields));

					// 	if ($result === false) {
					// 		error_log("Error updating option for section: " . $section);
							
					// 		// Check if the option exists and compare values
					// 		$current_value = get_option($option_name);
					// 		if ($current_value === maybe_serialize($fields)) {
					// 			error_log("No change needed, data is identical.");
					// 		} else {
					// 			error_log("Potential database write issue.");
					// 		}
					// 	} else {
					// 		error_log("Successfully updated option for section: " . $section);
					// 	}
					// }
					
					
					
					if ($result == true) {
						echo '<div class="updated"><p>' . esc_html__('Your changes were saved.', 'jwcfe') . '</p></div>';
					} else {
						echo '<div class="error"><p> ' . esc_html__('Your changes were not saved due to an error (or you made none!).', 'jwcfe') . '</p></div>';
					}
				} else {
					wp_die('Security check failed. Please try again or contact support for assistance.', 'Security Error');
				}
		
			
			
		}
		

		public function save_jwcfe_options()
		{

			$section_label = "";
			$sync_with_checkout = "";

			foreach ($_POST['formdata'] as $formRow) {
				if ($formRow['name'] == 'section_label') {
					$section_label = $formRow['value'];
				}
				if ($formRow['name'] == 'section_email_heading') {

					$section_email_heading = $formRow['value'];
				}
				if ($formRow['name'] == 'section_order_heading') {
					$section_order_heading = $formRow['value'];
				}

				if ($formRow['name'] == 'sync_with_checkout') {
					$sync_with_checkout = $formRow['value'];
				}
			}

			if (!empty($section_label)) {
				update_option('jwcfe_account_label', $section_label);
			}

			if (!empty($section_email_heading)) {
				update_option('jwcfe_email_label', $section_email_heading);
			}

			if (!empty($section_order_heading)) {
				update_option('jwcfe_order_label', $section_order_heading);
			}
			if (!empty($sync_with_checkout)) {
				update_option('jwcfe_account_sync_fields', $sync_with_checkout);
			} else {
				update_option('jwcfe_account_sync_fields', 'off');
			}
			echo '1';
			die();
		}

	
		
		// jwcfe order details after placed order ===============

		public function jwcfe_checkout_field_display_admin_order_meta_billing($order)
		{
			if (JWCFE_Helper::jwcfe_woocommerce_version_check()) {
				$order_id = $order->get_id();
			} else {
				$order_id = $order->id;
			}

			$fields = JWCFE_Helper::get_fields('billing');

			$fields_html = '';
			if (is_array($fields) && !empty($fields)) {
				// Loop through all custom fields to see if it should be added
				foreach ($fields as $name => $options) {

					$enabled = (isset($options['enabled']) && $options['enabled'] == false) ? false : true;
					$is_custom_field = (isset($options['custom']) && $options['custom'] == true) ? true : false;

					if (isset($options['show_in_order']) && $options['show_in_order'] && $enabled && $is_custom_field) {

						$order = wc_get_order($order_id);
						$value = $order->get_meta($name, true);

						if (is_array($value)) {
							$value = implode(", ", $value); // Convert array to comma-separated string
						}

						if (!empty($value)) {
							$label = isset($options['label']) && !empty($options['label']) ? __($options['label'], 'jwcfe') : $name;

							if ($options['type'] == 'file') {
								$fields_html .= '<p><strong>' . __($label, 'jwcfe') . ':</strong>
                        <br><button> <a class="download-file" href="' . esc_url($value) . '" download>Download File</a>
                        </button></p>';
							} else {
								$fields_html .= '<p><strong>' . __($label, 'jwcfe') . ':</strong> <br/>' . esc_html($value) . '</p>';
							}
						}
					}
				}

				$allowtags = array(
					'h2' => array(),
					'table' => array(),
					'tr' => array(),
					'td' => array(),
					'strong' => array(),
					'br' => array(),
					'th' => array(),
					'p' => array(),
					'a' => array('href' => array(), 'download' => array()) // Allow 'a' tags with 'href' and 'download' attributes
				);
				echo wp_kses($fields_html, $allowtags);
			}
		}



		public function jwcfe_checkout_field_display_admin_order_meta_shipping($order)
		{

			if (JWCFE_Helper::jwcfe_woocommerce_version_check()) {
				$order_id = $order->get_id();
			} else {
				$order_id = $order->id;
			}

			// $fields = array();
			$fields = JWCFE_Helper::get_fields('shipping');

			if (!wc_ship_to_billing_address_only() && $order->needs_shipping_address()) {
				$fields = array_merge(JWCFE_Helper::get_fields('shipping'), JWCFE_Helper::get_fields('additional'));
			}


			$fields_html = '';
			if (is_array($fields) && !empty($fields)) {


				// Loop through all custom fields to see if it should be added
				foreach ($fields as $name => $options) {

					$enabled = (isset($options['enabled']) && $options['enabled'] == false) ? false : true;
					$is_custom_field = (isset($options['custom']) && $options['custom'] == true) ? true : false;

					if (isset($options['show_in_order']) && $options['show_in_order'] && $enabled && $is_custom_field) {

						if ($options['type'] == 'select') {
							$order = wc_get_order($order_id);
							$value = $order->get_meta($name, true);

							if (is_array($value)) {
								$value = implode(",", $value);
							} else {
								$order = wc_get_order($order_id);
								$value = $order->get_meta($name, true);
							}
						} else {

							$order = wc_get_order($order_id);
							$value = $order->get_meta($name, true);
						}

						if (!empty($value)) {
							$label = isset($options['label']) && !empty($options['label']) ? __($options['label'], 'jwcfe') : $name;
							$fields_html .= '<p><strong>' . __($label, 'jwcfe') . ':</strong> <br/>' . $value . '</p>';
						}
					}
				} //end of fields loop

				$allowtags = array('h2' => array(), 'table' => array(), 'tr' => array(), 'td' => array(), 'strong' => array(), 'br' => array(), 'th' => array(), 'p' => array());
				echo wp_kses($fields_html, $allowtags);
			}
		}

		/**
		 * Display custom checkout fields on view order pages
		 *
		 * @param  object $order
		 */

		public function jwcfe_order_details_after_customer_details_lite($order)
		{

			if (JWCFE_Helper::jwcfe_woocommerce_version_check()) {
				$order_id = $order->get_id();
			} else {
				$order_id = $order->id;
			}
			$fields = array();

			if (!wc_ship_to_billing_address_only() && $order->needs_shipping_address()) {
				if (get_option('jwcfe_account_sync_fields') && get_option('jwcfe_account_sync_fields') == "on") {
					$fields = array_merge(
						JWCFE_Helper::get_fields('account'),
						JWCFE_Helper::get_fields('billing'),
						JWCFE_Helper::get_fields('shipping'),
						JWCFE_Helper::get_fields('additional')
					);
				} else {
					$fields = array_merge(JWCFE_Helper::get_fields('billing'), JWCFE_Helper::get_fields('shipping'), JWCFE_Helper::get_fields('additional'));
				}
			} else {
				if (get_option('jwcfe_account_sync_fields') && get_option('jwcfe_account_sync_fields') == "on") {
					$fields = array_merge(JWCFE_Helper::get_fields('account'), JWCFE_Helper::get_fields('billing'), JWCFE_Helper::get_fields('additional'));
				} else {
					$fields = array_merge(JWCFE_Helper::get_fields('billing'), JWCFE_Helper::get_fields('additional'));
				}
			}


			if (is_array($fields) && !empty($fields)) {
				$fields_html = '';
				// Loop through all custom fields to see if it should be added
				foreach ($fields as $name => $options) {
					$enabled = (isset($options['enabled']) && $options['enabled'] == false ? false : true);
					$is_custom_field = (isset($options['custom']) && $options['custom'] == true ? true : false);

					if (isset($options['show_in_order']) && $options['show_in_order'] && $enabled && $is_custom_field) {

						if ($options['type'] == 'select' || $options['type'] == 'checkboxgroup' || $options['type'] == 'timepicker' || $options['type'] == 'multiselect') {

							$order = wc_get_order($order_id);
							$value = $order->get_meta($name, true);

							if (is_array($value)) {
								$value = implode(",", $value);
							} else {

								$order = wc_get_order($order_id);
								$value = $order->get_meta($name, true);
							}
						} else {

							$order = wc_get_order($order_id);
							$value = $order->get_meta($name, true);
						}

						if (!empty($value)) {
							$label = (isset($options['label']) && !empty($options['label']) ? __($options['label'], 'jwcfe') : $name);

							if (is_account_page()) {

								if (apply_filters('jwcfe_view_order_customer_details_table_view', true)) {
									if (isset($options['type']) && $options['type'] == 'file') {
										$fields_html .= '<tr><th class="custom-th">' . esc_attr($label) . ':</th><td class="custom-td"><a href="' . esc_url($value) . '" download>Download File</a></td></tr>';
									} else {
										$fields_html .= '<tr><th class="custom-th">' . esc_attr($label) . ':</th><td class="custom-td">' . wptexturize($value) . '</td></tr>';
									}
								} else {

									if (isset($options['type']) && $options['type'] == 'file') {
										$fields_html .= '<br/><dt>' . esc_attr($label) . ':</dt><dd><a href="' . esc_url($value) . '" download>Download File</a></dd>';
									} else {
										$fields_html .= '<br/><dt>' . esc_attr($label) . ':</dt><dd>' . wptexturize($value) . '</dd>';
									}
								}
							} else {

								if (apply_filters('jwcfe_thankyou_customer_details_table_view', true)) {

									if (isset($options['type']) && $options['type'] == 'file') {
										$fields_html .= '<tr><th class="custom-th">' . esc_attr($label) . ':</th><td class="custom-td"><a href="' . esc_url($value) . '" download>Download File</a></td></tr>';
									} else {
										$fields_html .= '<tr><th class="custom-th">' . esc_attr($label) . ':</th><td class="custom-td">' . wptexturize($value) . '</td></tr>';
									}
								} else {

									if (isset($options['type']) && $options['type'] == 'file') {
										$fields_html .= '<br/><dt>' . esc_attr($label) . ':</dt><dd><a href="' . esc_url($value) . '" download>Download File</a></dd>';
									} else {
										$fields_html .= '<br/><dt>' . esc_attr($label) . ':</dt><dd>' . wptexturize($value) . '</dd>';
									}
								}
							}
						}
					}
				}
				
				if ($fields_html) {
					do_action('jwcfe_order_details_before_custom_fields_table', $order);
					?>
					<h3 class="woocommerce-column__title">Checkout Fields
					</h3>
					<table class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields" 
					style="	border: 1px solid hsla(0, 0%, 7%, .11);
							border-radius: 4px;
  							border-spacing: 0 ;
  							width: 100%;">
						<?php
						echo  $fields_html;
						?>
					</table>
<?php
					do_action('jwcfe_order_details_after_custom_fields_table', $order);
				}
			}
		}

	


		public function is_reserved_field_name($field_name)
		{
			if ($field_name && in_array($field_name, array(

				'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state',

				'billing_country', 'billing_postcode', 'billing_phone', 'billing_email',

				'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state',

				'shipping_country', 'shipping_postcode', 'customer_note', 'order_comments',

				'account_username', 'account_password'

			))) {

				return true;
			}
			return false;
		}

		function is_default_field_name($field_name)
		{

			if ($field_name && in_array($field_name, array(

				'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state',

				'billing_country', 'billing_postcode', 'billing_phone', 'billing_email',

				'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state',

				'shipping_country', 'shipping_postcode', 'customer_note', 'order_comments',

				'account_username', 'account_password'

			))) {
				return true;
			}
			return false;
		}
		
	}

endif;
