<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://jcodex.com
 *
 * @package    woo-checkout-regsiter-field-editor
 * @subpackage woo-checkout-regsiter-field-editor/admin
 */

if (!defined('WPINC')) {
	die;
}

if (!class_exists('JWCFE_Admin_Settings_Block_Fields')) :
	class JWCFE_Admin_Settings_Block_Fields extends JWCFE_Admin
	{

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
		
		    // print_r($sections);
			return $default_section;
		}
		
        public function checkout_form_field_editor()
		{
            $tab = $this->get_current_tab();
			$section = $this->get_current_section();
            if ($section == 'account') {
                
                echo '<div class="wrap woocommerce jwcfe-wrap"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>';
                
                $this->render_tabs_and_sections();
                
                if (isset($_POST['save_fields'])) {
                    echo $this->save_options($section);
                }
            
                if (isset($_POST['reset_fields'])) {
                    echo $this->reset_checkout_fields();
                }
            
                global $supress_field_modification;
                $supress_field_modification = false;
            
                // Display premium message with image link
                echo '<div class="premium-message"><a href="https://jcodex.com/plugins/woocommerce-custom-checkout-field-editor/">';
                echo '<img src="' . plugins_url('/assets/css/account_sec.jpg', __FILE__) . '"></a></div>';
            
                // Display new and edit field forms
                $this->jwcfe_new_field_form_pp();
                $this->jwcfe_edit_field_form_pp();
            
                echo '</div>';
                $this->jwcfe_field_popup();
            
            }else if ($section == 'additional') {
                
                echo '<div class="wrap woocommerce jwcfe-wrap"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>';
                $this->render_tabs_and_sections();
                if (isset($_POST['save_fields']))
                    echo $this->save_options($section);

                if (isset($_POST['reset_fields']))
                    echo $this->reset_checkout_fields();

                global $supress_field_modification;
                $supress_field_modification = false; ?>

                <form method="post" id="jwcfe_checkout_fields_form" action="">
                    <?php wp_nonce_field('woo_checkout_editor_settings', 'woo_checkout_editor_nonce'); ?>
                    <table id="jwcfe_checkout_fields" class="wc_gateways widefat" cellspacing="0">
                        <thead>
                            <tr><?php $this->render_actions_row($section); ?></tr>
                            <tr><?php $this->render_checkout_fields_heading_row(); ?></tr>
                        </thead>
                        <tfoot>
                            <tr><?php $this->render_checkout_fields_heading_row(); ?></tr>
                            <tr><?php $this->render_actions_row($section); ?></tr>
                        </tfoot>
                        <tbody class="ui-sortable">
                            <?php
                            $i = 0;
                            // $excluded_fields = [
                            //     'order_comments'
                            // ];
                            
                            foreach (JWCFE_Helper::get_fields($section) as $name => $options) :
                                // if (in_array($name, $excluded_fields)) {
                                //     continue; // Skip this field
                                // }
                            
                                if (isset($options['custom']) && $options['custom'] == 1) {
                                    $options['custom'] = '1';
                                } else {
                                    $options['custom'] = '0';
                                }
                                if (!isset($options['label'])) {
                                    $options['label'] = '';
                                }
                                if (!isset($options['text'])) {
                                    $options['text'] = '';
                                }
                                if (!isset($options['placeholder'])) {
                                    $options['placeholder'] = '';
                                }
                                
                                if (!isset($options['min_time'])) {
                                    $options['min_time'] = '';
                                }
                                if (!isset($options['max_time'])) {
                                    $options['max_time'] = '';
                                }
                                if (!isset($options['time_step'])) {
                                    $options['time_step'] = '';
                                }
                                if (!isset($options['time_format'])) {
                                    $options['time_format'] = '';
                                }
                                if (!isset($options['rules'])) {
                                    $options['rules'] = '';
                                }
                                if (!isset($options['rules_action'])) {
                                    $options['rules_action'] = '';
                                }
                                if (!isset($options['rules_ajax'])) {
                                    $options['rules_ajax'] = '';
                                }
                                if (!isset($options['rules_action_ajax'])) {
                                    $options['rules_action_ajax'] = '';
                                }
                                if (isset($options['options_json']) && is_array($options['options_json'])) {
                                    $options['options_json'] =  urlencode(json_encode($options['options_json']));
                                } else {
                                    $options['options_json'] = '';
                                }
                                if (isset($options['extoptions']) && is_array($options['extoptions'])) {
                                    $options['extoptions'] = implode(",", $options['extoptions']);
                                } else {
                                    $options['extoptions'] = '';
                                }
                                if (isset($options['class']) && is_array($options['class'])) {
                                    $options['class'] = implode(",", $options['class']);
                                } else {
                                    $options['class'] = '';
                                }
                                if (isset($options['label_class']) && is_array($options['label_class'])) {
                                    $options['label_class'] = implode(",", $options['label_class']);
                                } else {
                                    $options['label_class'] = '';
                                }
                                if (isset($options['validate']) && is_array($options['validate'])) {
                                    $options['validate'] = implode(",", $options['validate']);
                                } else {
                                    $options['validate'] = '';
                                }
                                if (isset($options['required']) && $options['required'] == 1) {
                                    $options['required'] = '1';
                                } else {
                                    $options['required'] = '0';
                                }
                                if (isset($options['is_include']) && $options['is_include'] == 1) {
                                    $options['is_include'] = '1';
                                } else {
                                    $options['is_include'] = '0';
                                }
                                if (isset($options['access']) && $options['access'] == 1) {
                                    $options['access'] = '1';
                                } else {
                                    $options['access'] = '0';
                                }
                                if (!isset($options['enabled']) || $options['enabled'] == 1) {
                                    $options['enabled'] = '1';
                                } else {
                                    $options['enabled'] = '0';
                                }
                                if (!isset($options['type'])) {
                                    $options['type'] = 'text';
                                }
                                if (isset($options['show_in_email']) && $options['show_in_email'] == 1) {
                                    $options['show_in_email'] = '1';
                                } else {
                                    $options['show_in_email'] = '0';
                                }
                                if (isset($options['show_in_order']) && $options['show_in_order'] == 1) {
                                    $options['show_in_order'] = '1';
                                } else {
                                    $options['show_in_order'] = '0';
                                }
                               
                                ?>
                                <?php
                                $disabled = false;
                                if ($name == 'account_username' || $name == 'account_password') {
                                    $disabled = true;
                                ?>
                                    <tr class="row_<?php echo $i;
                                                    echo ' jwcfe-disabled'; ?>">
                                    <?php } else { ?>
                                    <tr class="row_<?php echo $i;
                                                    echo ($options['enabled'] == 1 ? '' : ' jwcfe-disabled') ?>">
                                    <?php } ?>
                                    <td width="1%" class="sort ui-sortable-handle">
                                     
                                        <input type="hidden" name="rowId[<?php echo $i; ?>]" class="rowId" value="<?php echo $options['custom']; ?>" />
                                        <input type="hidden" name="f_custom[<?php echo $i; ?>]" class="f_custom" value="<?php echo $options['custom']; ?>" />
                                        <input type="hidden" name="f_order[<?php echo $i; ?>]" class="f_order" value="<?php echo $i; ?>" />
                                        <input type="hidden" name="f_name[<?php echo $i; ?>]" class="f_name" value="<?php echo esc_attr($name); ?>" />
                                        <input type="hidden" name="f_name_new[<?php echo $i; ?>]" class="f_name_new" value="" />
                                        <input type="hidden" name="f_type[<?php echo $i; ?>]" class="f_type" value="<?php echo $options['type']; ?>" />
                                        <input type="hidden" name="f_label[<?php echo $i; ?>]" class="f_label" value="<?php echo htmlspecialchars($options['label']); ?>" />
                                        <input type="hidden" name="f_text[<?php echo $i; ?>]" class="f_text" value="<?php echo stripcslashes(stripcslashes($options['text'])); ?>" />
                                        <input type="hidden" name="f_extoptions[<?php echo $i; ?>]" class="f_extoptions" value="<?php echo ($options['extoptions']) ?>" />
                                        <input type="hidden" name="f_access[<?php echo $i; ?>]" class="f_access" value="<?php echo ($options['access']) ?>" />
                                        <?php if (isset($options['maxlength'])) { ?>
                                            <input type="hidden" name="f_maxlength[<?php echo $i; ?>]" class="f_maxlength" value="<?php echo $options['maxlength']; ?>" />
                                        <?php } ?>
                                        <input type="hidden" name="f_placeholder[<?php echo $i; ?>]" class="f_placeholder" value="<?php echo $options['placeholder']; ?>" />
                                        <input type="hidden" name="i_min_time[<?php echo $i; ?>]" class="i_min_time" value="<?php echo $options['min_time']; ?>" />
                                        <input type="hidden" name="i_max_time[<?php echo $i; ?>]" class="i_max_time" value="<?php echo $options['max_time']; ?>" />
                                        <input type="hidden" name="i_time_step[<?php echo $i; ?>]" class="i_time_step" value="<?php echo $options['time_step']; ?>" />
                                        <input type="hidden" name="i_time_format[<?php echo $i; ?>]" class="i_time_format" value="<?php echo $options['time_format']; ?>" />
                                        <input type="hidden" name="f_rules_action[<?php echo $i; ?>]" class="f_rules_action" value="<?php echo $options['rules_action']; ?>" />
                                        <input type="hidden" name="f_rules[<?php echo $i; ?>]" class="f_rules" value="<?php echo $options['rules']; ?>" />
                                        <input type="hidden" name="f_rules_action_ajax[<?php echo $i; ?>]" class="f_rules_action_ajax" value="<?php echo $options['rules_action_ajax']; ?>" />
                                        <input type="hidden" name="f_rules_ajax[<?php echo $i; ?>]" class="f_rules_ajax" value="<?php echo $options['rules_ajax']; ?>" />
                                        <input type="hidden" name="f_options[<?php echo $i; ?>]" class="f_options" value="<?php echo ($options['options_json']); ?>" />
                                        <input type="hidden" name="f_class[<?php echo $i; ?>]" class="f_class" value="<?php echo $options['class']; ?>" />
                                        <input type="hidden" name="f_label_class[<?php echo $i; ?>]" class="f_label_class" value="<?php echo $options['label_class']; ?>" />
                                        <input type="hidden" name="f_required[<?php echo $i; ?>]" class="f_required" value="<?php echo ($options['required']); ?>" />
                                        <input type="hidden" name="f_is_include[<?php echo $i; ?>]" class="f_is_include" value="<?php echo ($options['is_include']); ?>" />
                                        <input type="hidden" name="f_enabled[<?php echo $i; ?>]" class="f_enabled" value="<?php echo ($options['enabled']); ?>" />
                                        <input type="hidden" name="f_validation[<?php echo $i; ?>]" class="f_validation" value="<?php echo ($options['validate']); ?>" />
                                        <input type="hidden" name="f_show_in_email[<?php echo $i; ?>]" class="f_show_in_email" value="<?php echo ($options['show_in_email']); ?>" />
                                        <input type="hidden" name="f_show_in_order[<?php echo $i; ?>]" class="f_show_in_order" value="<?php echo ($options['show_in_order']); ?>" />
                                        <input type="hidden" name="f_deleted[<?php echo $i; ?>]" class="f_deleted" value="0" />
                                        <!--$properties = array('type', 'label', 'placeholder', 'class', 'required', 'clear', 'label_class', 'options');-->
                                    </td>
                                        <td class="td_select"><input type="checkbox" name="select_field" /></td>
                                        <td class="td_name"><?php echo esc_attr($name); ?></td>
                                        <td class="td_type"><?php echo $options['type']; ?></td>
                                        <td class="td_label"><?php echo $options['label']; ?></td>
                                        <td class="td_placeholder"><?php echo $options['text']; ?></td>
                                        <td class="td_validate"><?php echo $options['validate']; ?></td>
                                        <td class="td_required status"><?php echo ($options['required'] == 1 ? '<span class="dashicons dashicons-saved"></span>' : '-') ?></td>
                                        <td class="td_enabled status">
                                            <label class="pure-material-switch">
                                                <input type="checkbox" class="toggle-checkbox" <?php echo ($options['enabled'] == 1 ? 'checked' : ''); ?> />
                                                <span class="label">No</span>
                                            </label>
                                            <span class="toggle-label">yes</span>
                                        </td>
                                        <td class="td_edit">
                                            <div class="f_edit_btn" <?php echo ($options['enabled'] == 1 ? '' : 'disabled') ?> onclick="openEditFieldForm(this,<?php echo $i; ?>)">
                                                <img class="edit-icon" src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'admin/assets/css/pencil.png'; ?>" alt="" width="12" height="14">
                                                <?php _e('', 'jwcfe'); ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php $i++;
                            endforeach; ?>
                        </tbody>
                    </table>
                </form>

                <?php
                
                $this->jwcfe_field_popup();
                // $this->jwcfe_new_field_form_pp();
                // $this->jwcfe_edit_field_form_pp();

                ?>

                </div>
                <?php
            }else if ($section == 'billing'){
                
                echo '<div class="wrap woocommerce jwcfe-wrap"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>';
                $this->render_tabs_and_sections();
                if (isset($_POST['save_fields']))
                    echo $this->save_options($section);

                if (isset($_POST['reset_fields']))
                    echo $this->reset_checkout_fields();

                global $supress_field_modification;
                $supress_field_modification = false; ?>

                <form method="post" id="jwcfe_checkout_fields_form" action="">
                    <?php wp_nonce_field('woo_checkout_editor_settings', 'woo_checkout_editor_nonce'); ?>
                    <table id="jwcfe_checkout_fields" class="wc_gateways widefat" cellspacing="0">
                        <thead>
                            <tr><?php $this->render_actions_row($section); ?></tr>
                            <tr><?php $this->render_checkout_fields_heading_row(); ?></tr>
                        </thead>
                        <tfoot>
                            <tr><?php $this->render_checkout_fields_heading_row(); ?></tr>
                            <tr><?php $this->render_actions_row($section); ?></tr>
                        </tfoot>
                        <tbody class="ui-sortable">
                            <?php
                            $i = 0;
                            $excluded_fields = [
                                'billing_first_name',
                                'billing_last_name',
                                'billing_company',
                                'billing_country',
                                'billing_address_1',
                                'billing_address_2',
                                'billing_city',
                                'billing_state',
                                'billing_postcode',
                                'billing_phone'
                            ];


                            foreach (JWCFE_Helper::get_fields($section) as $name => $options) :
                            //    print_r($options);
                                if (in_array($name, $excluded_fields)) {
                                    continue; // Skip this field
                                }
                            
                                if (isset($options['custom']) && $options['custom'] == 1) {
                                    $options['custom'] = '1';
                                } else {
                                    $options['custom'] = '0';
                                }
                                if (!isset($options['label'])) {
                                    $options['label'] = '';
                                }
                                if (!isset($options['text'])) {
                                    $options['text'] = '';
                                }
                                if (!isset($options['placeholder'])) {
                                    $options['placeholder'] = '';
                                }
                                
                                if (!isset($options['min_time'])) {
                                    $options['min_time'] = '';
                                }
                                if (!isset($options['max_time'])) {
                                    $options['max_time'] = '';
                                }
                                if (!isset($options['time_step'])) {
                                    $options['time_step'] = '';
                                }
                                if (!isset($options['time_format'])) {
                                    $options['time_format'] = '';
                                }
                                if (!isset($options['rules'])) {
                                    $options['rules'] = '';
                                }
                                if (!isset($options['rules_action'])) {
                                    $options['rules_action'] = '';
                                }
                                if (!isset($options['rules_ajax'])) {
                                    $options['rules_ajax'] = '';
                                }
                                if (!isset($options['rules_action_ajax'])) {
                                    $options['rules_action_ajax'] = '';
                                }
                                if (isset($options['options_json']) && is_array($options['options_json'])) {
                                    $options['options_json'] =  urlencode(json_encode($options['options_json']));
                                } else {
                                    $options['options_json'] = '';
                                }
                                if (isset($options['extoptions']) && is_array($options['extoptions'])) {
                                    $options['extoptions'] = implode(",", $options['extoptions']);
                                } else {
                                    $options['extoptions'] = '';
                                }
                                if (isset($options['class']) && is_array($options['class'])) {
                                    $options['class'] = implode(",", $options['class']);
                                } else {
                                    $options['class'] = '';
                                }
                                if (isset($options['label_class']) && is_array($options['label_class'])) {
                                    $options['label_class'] = implode(",", $options['label_class']);
                                } else {
                                    $options['label_class'] = '';
                                }
                                if (isset($options['validate']) && is_array($options['validate'])) {
                                    $options['validate'] = implode(",", $options['validate']);
                                } else {
                                    $options['validate'] = '';
                                }
                                if (isset($options['required']) && $options['required'] == 1) {
                                    $options['required'] = '1';
                                } else {
                                    $options['required'] = '0';
                                }
                                if (isset($options['is_include']) && $options['is_include'] == 1) {
                                    $options['is_include'] = '1';
                                } else {
                                    $options['is_include'] = '0';
                                }
                                if (isset($options['access']) && $options['access'] == 1) {
                                    $options['access'] = '1';
                                } else {
                                    $options['access'] = '0';
                                }
                                if (!isset($options['enabled']) || $options['enabled'] == 1) {
                                    $options['enabled'] = '1';
                                } else {
                                    $options['enabled'] = '0';
                                }
                                if (!isset($options['type'])) {
                                    $options['type'] = 'text';
                                }
                                if (isset($options['show_in_email']) && $options['show_in_email'] == 1) {
                                    $options['show_in_email'] = '1';
                                } else {
                                    $options['show_in_email'] = '0';
                                }
                                if (isset($options['show_in_order']) && $options['show_in_order'] == 1) {
                                    $options['show_in_order'] = '1';
                                } else {
                                    $options['show_in_order'] = '0';
                                }
                               
                            ?>
                                <?php
                                    $disabled = false;
                                    if ($tab == 'block' && $name =='billing_email'|| $name == 'account_username' || $name == 'account_password') {
                                            $disabled = true;
                                            ?>
                                            <tr class="row_<?php echo $i;
                                                echo ' jwcfe-disabled'; ?>">
                                            <?php 
                                        } else { ?>
                                            <tr class="row_<?php echo $i;
                                                echo ($options['enabled'] == 1 ? '' : ' jwcfe-disabled') ?>">
                                            <?php 
                                        } 
                                ?>
                                    <td width="1%" class="sort ui-sortable-handle">
                                     
                                        <input type="hidden" name="rowId[<?php echo $i; ?>]" class="rowId" value="<?php echo $options['custom']; ?>" />
                                        <input type="hidden" name="f_custom[<?php echo $i; ?>]" class="f_custom" value="<?php echo $options['custom']; ?>" />
                                        <input type="hidden" name="f_order[<?php echo $i; ?>]" class="f_order" value="<?php echo $i; ?>" />
                                        <input type="hidden" name="f_name[<?php echo $i; ?>]" class="f_name" value="<?php echo esc_attr($name); ?>" />
                                        <input type="hidden" name="f_name_new[<?php echo $i; ?>]" class="f_name_new" value="" />
                                        <input type="hidden" name="f_type[<?php echo $i; ?>]" class="f_type" value="<?php echo $options['type']; ?>" />
                                        <input type="hidden" name="f_label[<?php echo $i; ?>]" class="f_label" value="<?php echo htmlspecialchars($options['label']); ?>" />
                                        <input type="hidden" name="f_text[<?php echo $i; ?>]" class="f_text" value="<?php echo stripcslashes(stripcslashes($options['text'])); ?>" />
                                        <input type="hidden" name="f_extoptions[<?php echo $i; ?>]" class="f_extoptions" value="<?php echo ($options['extoptions']) ?>" />
                                        <input type="hidden" name="f_access[<?php echo $i; ?>]" class="f_access" value="<?php echo ($options['access']) ?>" />
                                        <?php if (isset($options['maxlength'])) { ?>
                                            <input type="hidden" name="f_maxlength[<?php echo $i; ?>]" class="f_maxlength" value="<?php echo $options['maxlength']; ?>" />
                                        <?php } ?>
                                        <input type="hidden" name="f_placeholder[<?php echo $i; ?>]" class="f_placeholder" value="<?php echo $options['placeholder']; ?>" />
                                        <input type="hidden" name="i_min_time[<?php echo $i; ?>]" class="i_min_time" value="<?php echo $options['min_time']; ?>" />
                                        <input type="hidden" name="i_max_time[<?php echo $i; ?>]" class="i_max_time" value="<?php echo $options['max_time']; ?>" />
                                        <input type="hidden" name="i_time_step[<?php echo $i; ?>]" class="i_time_step" value="<?php echo $options['time_step']; ?>" />
                                        <input type="hidden" name="i_time_format[<?php echo $i; ?>]" class="i_time_format" value="<?php echo $options['time_format']; ?>" />
                                        <input type="hidden" name="f_rules_action[<?php echo $i; ?>]" class="f_rules_action" value="<?php echo $options['rules_action']; ?>" />
                                        <input type="hidden" name="f_rules[<?php echo $i; ?>]" class="f_rules" value="<?php echo $options['rules']; ?>" />
                                        <input type="hidden" name="f_rules_action_ajax[<?php echo $i; ?>]" class="f_rules_action_ajax" value="<?php echo $options['rules_action_ajax']; ?>" />
                                        <input type="hidden" name="f_rules_ajax[<?php echo $i; ?>]" class="f_rules_ajax" value="<?php echo $options['rules_ajax']; ?>" />
                                        <input type="hidden" name="f_options[<?php echo $i; ?>]" class="f_options" value="<?php echo ($options['options_json']); ?>" />
                                        <input type="hidden" name="f_class[<?php echo $i; ?>]" class="f_class" value="<?php echo $options['class']; ?>" />
                                        <input type="hidden" name="f_label_class[<?php echo $i; ?>]" class="f_label_class" value="<?php echo $options['label_class']; ?>" />
                                        <input type="hidden" name="f_required[<?php echo $i; ?>]" class="f_required" value="<?php echo ($options['required']); ?>" />
                                        <input type="hidden" name="f_is_include[<?php echo $i; ?>]" class="f_is_include" value="<?php echo ($options['is_include']); ?>" />
                                        <input type="hidden" name="f_enabled[<?php echo $i; ?>]" class="f_enabled" value="<?php echo ($options['enabled']); ?>" />
                                        <input type="hidden" name="f_validation[<?php echo $i; ?>]" class="f_validation" value="<?php echo ($options['validate']); ?>" />
                                        <input type="hidden" name="f_show_in_email[<?php echo $i; ?>]" class="f_show_in_email" value="<?php echo ($options['show_in_email']); ?>" />
                                        <input type="hidden" name="f_show_in_order[<?php echo $i; ?>]" class="f_show_in_order" value="<?php echo ($options['show_in_order']); ?>" />
                                        <input type="hidden" name="f_deleted[<?php echo $i; ?>]" class="f_deleted" value="0" />
                                        <!--$properties = array('type', 'label', 'placeholder', 'class', 'required', 'clear', 'label_class', 'options');-->
                                    </td>
                                        <td class="td_select"><input type="checkbox" name="select_field" /></td>
                                        <td class="td_name"><?php echo esc_attr($name); ?></td>
                                        <td class="td_type"><?php echo $options['type']; ?></td>
                                        <td class="td_label"><?php echo $options['label']; ?></td>
                                        <td class="td_placeholder"><?php echo $options['text']; ?></td>
                                        <td class="td_validate"><?php echo $options['validate']; ?></td>
                                        <td class="td_required status"><?php echo ($options['required'] == 1 ? '<span class="dashicons dashicons-saved"></span>' : '-') ?></td>
                                      
                                        <td class="td_enabled status">
                                            <label class="pure-material-switch">
                                                <input type="checkbox" class="toggle-checkbox" 
                                                    <?php echo ($options['enabled'] == 1 ? 'checked' : ''); ?>
                                                    <?php echo ($name == 'billing_email' || $name == 'account_username' || $name == 'account_password') ? 'disabled' : ''; ?> />
                                                <span class="label">No</span>
                                            </label>
                                            <span class="toggle-label">yes</span>
                                        </td>

                                        <td class="td_edit">
                                            <div class="f_edit_btn <?php echo (in_array($name, [
                                                'billing_email',  
                                                'account_username', 
                                                'account_password'
                                            ])) ? 'disabled-edit' : ''; ?>" 
                                            <?php echo (in_array($name, [
                                                'billing_email', 
                                                'account_username', 
                                                'account_password'
                                            ])) ? '' : 'onclick="openEditFieldForm(this,' . $i . ')"'; ?>>
                                                <img class="edit-icon" src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'admin/assets/css/pencil.png'; ?>" alt="" width="12" height="14">
                                            </div>
                                        </td>

                                    </tr>
                                <?php $i++;
                            endforeach; ?>
                        </tbody>
                    </table>
                </form>

                <?php
                
                $this->jwcfe_field_popup();
                ?>

                </div>
                <?php
            }else if ($section == 'shipping' ) {
                
                echo '<div class="wrap woocommerce jwcfe-wrap"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>';
                $this->render_tabs_and_sections();
                if (isset($_POST['save_fields']))
                    echo $this->save_options($section);

                if (isset($_POST['reset_fields']))
                    echo $this->reset_checkout_fields();

                global $supress_field_modification;
                $supress_field_modification = false; ?>

                <form method="post" id="jwcfe_checkout_fields_form" action="">
                    <?php wp_nonce_field('woo_checkout_editor_settings', 'woo_checkout_editor_nonce'); ?>
                    <table id="jwcfe_checkout_fields" class="wc_gateways widefat" cellspacing="0">
                        <thead>
                            <tr><?php $this->render_actions_row($section); ?></tr>
                            <tr><?php $this->render_checkout_fields_heading_row(); ?></tr>
                        </thead>
                        <tfoot>
                            <tr><?php $this->render_checkout_fields_heading_row(); ?></tr>
                            <tr><?php $this->render_actions_row($section); ?></tr>
                        </tfoot>
                        <tbody class="ui-sortable">
                            <?php
                            $i = 0;
                            $excluded_fields = [
                                'shipping_first_name',
                                'shipping_last_name',
                                'shipping_company',
                                'shipping_country',
                                'shipping_address_1',
                                'shipping_address_2',
                                'shipping_city',
                                'shipping_state',
                                'shipping_postcode',
                                'shipping_phone'                            
                            ];
                            
                            foreach (JWCFE_Helper::get_fields($section) as $name => $options) :
                                // if (in_array($name, $excluded_fields)) {
                                //     continue; // Skip this field
                                // }
                                if (isset($options['custom']) && $options['custom'] == 1) {
                                    $options['custom'] = '1';
                                } else {
                                    $options['custom'] = '0';
                                }
                                if (!isset($options['label'])) {
                                    $options['label'] = '';
                                }
                                if (!isset($options['text'])) {
                                    $options['text'] = '';
                                }
                                if (!isset($options['placeholder'])) {
                                    $options['placeholder'] = '';
                                }
                                
                                if (!isset($options['min_time'])) {
                                    $options['min_time'] = '';
                                }
                                if (!isset($options['max_time'])) {
                                    $options['max_time'] = '';
                                }
                                if (!isset($options['time_step'])) {
                                    $options['time_step'] = '';
                                }
                                if (!isset($options['time_format'])) {
                                    $options['time_format'] = '';
                                }
                                if (!isset($options['rules'])) {
                                    $options['rules'] = '';
                                }
                                if (!isset($options['rules_action'])) {
                                    $options['rules_action'] = '';
                                }
                                if (!isset($options['rules_ajax'])) {
                                    $options['rules_ajax'] = '';
                                }
                                if (!isset($options['rules_action_ajax'])) {
                                    $options['rules_action_ajax'] = '';
                                }
                                if (isset($options['options_json']) && is_array($options['options_json'])) {
                                    $options['options_json'] =  urlencode(json_encode($options['options_json']));
                                } else {
                                    $options['options_json'] = '';
                                }
                                if (isset($options['extoptions']) && is_array($options['extoptions'])) {
                                    $options['extoptions'] = implode(",", $options['extoptions']);
                                } else {
                                    $options['extoptions'] = '';
                                }
                                if (isset($options['class']) && is_array($options['class'])) {
                                    $options['class'] = implode(",", $options['class']);
                                } else {
                                    $options['class'] = '';
                                }
                                if (isset($options['label_class']) && is_array($options['label_class'])) {
                                    $options['label_class'] = implode(",", $options['label_class']);
                                } else {
                                    $options['label_class'] = '';
                                }
                                if (isset($options['validate']) && is_array($options['validate'])) {
                                    $options['validate'] = implode(",", $options['validate']);
                                } else {
                                    $options['validate'] = '';
                                }
                                if (isset($options['required']) && $options['required'] == 1) {
                                    $options['required'] = '1';
                                } else {
                                    $options['required'] = '0';
                                }
                                if (isset($options['is_include']) && $options['is_include'] == 1) {
                                    $options['is_include'] = '1';
                                } else {
                                    $options['is_include'] = '0';
                                }
                                if (isset($options['access']) && $options['access'] == 1) {
                                    $options['access'] = '1';
                                } else {
                                    $options['access'] = '0';
                                }
                                if (!isset($options['enabled']) || $options['enabled'] == 1) {
                                    $options['enabled'] = '1';
                                } else {
                                    $options['enabled'] = '0';
                                }
                                if (!isset($options['type'])) {
                                    $options['type'] = 'text';
                                }
                                if (isset($options['show_in_email']) && $options['show_in_email'] == 1) {
                                    $options['show_in_email'] = '1';
                                } else {
                                    $options['show_in_email'] = '0';
                                }
                                if (isset($options['show_in_order']) && $options['show_in_order'] == 1) {
                                    $options['show_in_order'] = '1';
                                } else {
                                    $options['show_in_order'] = '0';
                                }
                              
                                
                            ?>
                                <?php
                                $disabled = false;
                                if ($tab == 'block' && $name =='shipping_first_name'|| 
                                    $name =='shipping_last_name'|| 
                                    $name =='shipping_country'|| 
                                    $name =='shipping_address_1'|| 
                                    $name =='shipping_address_2'|| 
                                    $name =='shipping_city'|| 
                                    $name =='shipping_state'|| 
                                    $name =='shipping_postcode'|| 
                                    $name == 'account_username' || $name == 'account_password') {
                                    $disabled = true;
                                ?>
                                    <tr class="row_<?php echo $i;
                                                    echo ' jwcfe-disabled'; ?>">
                                    <?php } else { ?>
                                    <tr class="row_<?php echo $i;
                                                    echo ($options['enabled'] == 1 ? '' : ' jwcfe-disabled') ?>">
                                    <?php } ?>
                                    <td width="1%" class="sort ui-sortable-handle">
                                     
                                        <input type="hidden" name="rowId[<?php echo $i; ?>]" class="rowId" value="<?php echo $options['custom']; ?>" />
                                        <input type="hidden" name="f_custom[<?php echo $i; ?>]" class="f_custom" value="<?php echo $options['custom']; ?>" />
                                        <input type="hidden" name="f_order[<?php echo $i; ?>]" class="f_order" value="<?php echo $i; ?>" />
                                        <input type="hidden" name="f_name[<?php echo $i; ?>]" class="f_name" value="<?php echo esc_attr($name); ?>" />
                                        <input type="hidden" name="f_name_new[<?php echo $i; ?>]" class="f_name_new" value="" />
                                        <input type="hidden" name="f_type[<?php echo $i; ?>]" class="f_type" value="<?php echo $options['type']; ?>" />
                                        <input type="hidden" name="f_label[<?php echo $i; ?>]" class="f_label" value="<?php echo htmlspecialchars($options['label']); ?>" />
                                        <input type="hidden" name="f_text[<?php echo $i; ?>]" class="f_text" value="<?php echo stripcslashes(stripcslashes($options['text'])); ?>" />
                                        <input type="hidden" name="f_extoptions[<?php echo $i; ?>]" class="f_extoptions" value="<?php echo ($options['extoptions']) ?>" />
                                        <input type="hidden" name="f_access[<?php echo $i; ?>]" class="f_access" value="<?php echo ($options['access']) ?>" />
                                        <?php if (isset($options['maxlength'])) { ?>
                                            <input type="hidden" name="f_maxlength[<?php echo $i; ?>]" class="f_maxlength" value="<?php echo $options['maxlength']; ?>" />
                                        <?php } ?>
                                        <input type="hidden" name="f_placeholder[<?php echo $i; ?>]" class="f_placeholder" value="<?php echo $options['placeholder']; ?>" />
                                        <input type="hidden" name="i_min_time[<?php echo $i; ?>]" class="i_min_time" value="<?php echo $options['min_time']; ?>" />
                                        <input type="hidden" name="i_max_time[<?php echo $i; ?>]" class="i_max_time" value="<?php echo $options['max_time']; ?>" />
                                        <input type="hidden" name="i_time_step[<?php echo $i; ?>]" class="i_time_step" value="<?php echo $options['time_step']; ?>" />
                                        <input type="hidden" name="i_time_format[<?php echo $i; ?>]" class="i_time_format" value="<?php echo $options['time_format']; ?>" />
                                        <input type="hidden" name="f_rules_action[<?php echo $i; ?>]" class="f_rules_action" value="<?php echo $options['rules_action']; ?>" />
                                        <input type="hidden" name="f_rules[<?php echo $i; ?>]" class="f_rules" value="<?php echo $options['rules']; ?>" />
                                        <input type="hidden" name="f_rules_action_ajax[<?php echo $i; ?>]" class="f_rules_action_ajax" value="<?php echo $options['rules_action_ajax']; ?>" />
                                        <input type="hidden" name="f_rules_ajax[<?php echo $i; ?>]" class="f_rules_ajax" value="<?php echo $options['rules_ajax']; ?>" />
                                        <input type="hidden" name="f_options[<?php echo $i; ?>]" class="f_options" value="<?php echo ($options['options_json']); ?>" />
                                        <input type="hidden" name="f_class[<?php echo $i; ?>]" class="f_class" value="<?php echo $options['class']; ?>" />
                                        <input type="hidden" name="f_label_class[<?php echo $i; ?>]" class="f_label_class" value="<?php echo $options['label_class']; ?>" />
                                        <input type="hidden" name="f_required[<?php echo $i; ?>]" class="f_required" value="<?php echo ($options['required']); ?>" />
                                        <input type="hidden" name="f_is_include[<?php echo $i; ?>]" class="f_is_include" value="<?php echo ($options['is_include']); ?>" />
                                        <input type="hidden" name="f_enabled[<?php echo $i; ?>]" class="f_enabled" value="<?php echo ($options['enabled']); ?>" />
                                        <input type="hidden" name="f_validation[<?php echo $i; ?>]" class="f_validation" value="<?php echo ($options['validate']); ?>" />
                                        <input type="hidden" name="f_show_in_email[<?php echo $i; ?>]" class="f_show_in_email" value="<?php echo ($options['show_in_email']); ?>" />
                                        <input type="hidden" name="f_show_in_order[<?php echo $i; ?>]" class="f_show_in_order" value="<?php echo ($options['show_in_order']); ?>" />
                                        <input type="hidden" name="f_deleted[<?php echo $i; ?>]" class="f_deleted" value="0" />
                                        <!--$properties = array('type', 'label', 'placeholder', 'class', 'required', 'clear', 'label_class', 'options');-->
                                    </td>
                                        <td class="td_select"><input type="checkbox" name="select_field" /></td>
                                        <td class="td_name"><?php echo esc_attr($name); ?></td>
                                        <td class="td_type"><?php echo $options['type']; ?></td>
                                        <td class="td_label"><?php echo $options['label']; ?></td>
                                        <td class="td_placeholder"><?php echo $options['text']; ?></td>
                                        <td class="td_validate"><?php echo $options['validate']; ?></td>
                                        <td class="td_required status"><?php echo ($options['required'] == 1 ? '<span class="dashicons dashicons-saved"></span>' : '-') ?></td>
                                  
                                        <td class="td_enabled status">
                                            <label class="pure-material-switch">
                                                <input type="checkbox" class="toggle-checkbox" 
                                                    <?php echo ($options['enabled'] == 1 ? 'checked' : ''); ?>
                                                    <?php echo (in_array($name, [
                                                        'shipping_first_name', 
                                                        'shipping_last_name', 
                                                        'shipping_country', 
                                                        'shipping_address_1', 
                                                        'shipping_address_2', 
                                                        'shipping_city', 
                                                        'shipping_state', 
                                                        'shipping_postcode', 
                                                        'account_username', 
                                                        'account_password'
                                                    ])) ? 'disabled' : ''; ?> />
                                                <span class="label">No</span>
                                            </label>
                                            <span class="toggle-label">yes</span>
                                        </td>
                                        <td class="td_edit">
                                            <div class="f_edit_btn <?php echo (in_array($name, [
                                                'shipping_first_name', 
                                                'shipping_last_name', 
                                                'shipping_country', 
                                                'shipping_address_1', 
                                                'shipping_address_2', 
                                                'shipping_city', 
                                                'shipping_state', 
                                                'shipping_postcode', 
                                                'account_username', 
                                                'account_password'
                                            ])) ? 'disabled-edit' : ''; ?>" 
                                            <?php echo (in_array($name, [
                                                'shipping_first_name', 
                                                'shipping_last_name', 
                                                'shipping_country', 
                                                'shipping_address_1', 
                                                'shipping_address_2', 
                                                'shipping_city', 
                                                'shipping_state', 
                                                'shipping_postcode', 
                                                'account_username', 
                                                'account_password'
                                            ])) ? '' : 'onclick="openEditFieldForm(this,' . $i . ')"'; ?>>
                                                <img class="edit-icon" src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'admin/assets/css/pencil.png'; ?>" alt="" width="12" height="14">
                                            </div>
                                        </td>

                                    </tr>
                                <?php $i++;
                            endforeach; ?>
                        </tbody>
                    </table>
                </form>

                <?php
                
                $this->jwcfe_field_popup();

                ?>

                </div>
                <?php
            }
            
		}
        function reset_checkout_fields()
		{
          
            delete_option('jwcfe_wc_fields_block_billing');
            delete_option('jwcfe_wc_fields_block_shipping');
            delete_option('jwcfe_wc_fields_block_additional');
			echo '<div class="updated"><p>' . esc_html__('SUCCESS: Checkout fields successfully reset', 'jwcfe') . '</p></div>';
		}
        public function jwcfe_field_popup(){
            
            $field_types = $this->get_field_types();
                $formTitle = 'New Checkout Field';
                $addClass = '';
                if (isset($_GET['section']) && $_GET['section'] == 'account') {

                    $formTitle = 'New Account Page Field';
                    $addClass = 'accountdialog';
                }
            ?>
            <div id="jwcfeModal" class="jwcfemodal" style="display: none;">
            
                <div class="jwcfemodal-content">
                
                    
                    <div id="jwcfe_new_field_form_pp"  title="<?php echo esc_html($formTitle); ?>" class="<?php echo $addClass; ?> jwcfe_popup_wrapper">
                        <form method="POST" id="jwcfe_new_field_form" action="">
                            <div class="jwcfe_tabs" class="jwcfe-tabs">
                            <div class="jwcfemodal-content-main-div" style="position: sticky;top: 0;z-index: 1000;">
                                <div class="jwcfemodal-content-div">
                                    <span class="jwcfecloseBtn" onclick="closejwcfeModal()">&times;</span>
                                    <h2 class="ui-dialog-title"><?php echo $formTitle; ?></h2>
                                </div>
                                <ul style="position: sticky;top: 0;z-index: 1000;">
                                    <li><a href="#tab-1"><?php echo esc_html__('General Settings', 'jwcfe'); ?></a></li>
                                    <span class="circle1"></span>
                                </ul>
                            </div>
                                

                                <div id="jwcfe_field_editor_form_new">
                                    <div id="tab-1">
                                        <input type="hidden" name="i_options" value="" />
                                    
                                        <div class="jwcfe_form_container">
                                            <div class="">
                                                <div class="">
                                                    <div class="rowfield" style="display: flex; align-items: center;">
                                                            <div id="fieldlabel" style="width: 40%; margin-right: 10px;">
                                                                <?php esc_html_e('Field Type:', 'jwcfe'); ?>
                                                            </div>
                                                            <div>
                                                                <select name="ftype" style="" onchange="jwcfeFieldTypeChangeListnerblock(this)">
                                                                    
                                                                <option value="text">Text</option>
                                                                <option value="select">Select</option>
                                                                <option value="checkbox">Checkbox</option>

                                                            </select>
                                                            </div>
                                                    </div>

                                                    <div class="rowName" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel"  style="width: 40%;margin-right: 70px;margin-bottom: 22px;"><?php esc_html_e('Name:', 'jwcfe'); ?><font color="red"><?php echo esc_html__('*', 'jwcfe'); ?></font></div>
                                                        <div>
                                                            <input type="text" value="" name="fname" placeholder="<?php esc_attr_e('eg. new_field', 'jwcfe'); ?>" require />
                                                            <br>
                                                            <span style="font-size: 10px;"><?php esc_html_e('Must be unique for each field', 'jwcfe'); ?></span>
                                                            <br><span class="err_msgs"></span>

                                                        </div>

                                                    </div>

                                                    <div class="rowLabel" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel"  style="width: 40%; margin-right: 10px;"><?php esc_html_e('Label of Field:', 'jwcfe'); ?></div>
                                                        <div>
                                                            <input type="text" name="flabel" placeholder="<?php esc_attr_e('eg. new_field', 'jwcfe'); ?>" />
                                                        </div>
                                                    </div>
                                                    <!-- <div class="rowheading" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel"  style="width: 40%; margin-right: 10px;"><?php esc_html_e('Heading Text:', 'jwcfe'); ?></div>
                                                        <div>
                                                            <input type="text" name="flabel" placeholder="<?php esc_attr_e('Enter Your Heading Text', 'jwcfe'); ?>" />
                                                        </div>
                                                    </div> -->
                                                    <div class="rowMaxlength" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel" style="width: 40%; margin-right: 10px;"><?php esc_html_e('Character limit:', 'jwcfe'); ?></div>
                                                        <div><input type="number" name="fmaxlength" style="" /></div>
                                                    </div>
                                                    <div class="rowPlaceholder" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel" style="width: 40%; margin-right: 10px;"><?php esc_html_e('Placeholder:', 'jwcfe'); ?></div>
                                                        <div><input type="text" name="fplaceholder" placeholder="<?php esc_attr_e('eg. new_field', 'jwcfe'); ?>" style="" /></div>
                                                    </div>
                                                    <div class="rowValidate" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel" style="width: 40%; margin-right: 10px;"><?php esc_html_e('Validation:', 'jwcfe'); ?></div>
                                                        <div class="validationtxt">
                                                            <select multiple="multiple" name="fvalidate" placeholder="<?php esc_attr_e('Selecgt Validations', 'jwcfe'); ?>" class="jwcfe-enhanced-multi-select" style="width:  300px !important;height: 40px;">
                                                                <option value="email"><?php esc_html_e('Email', 'jwcfe'); ?></option>
                                                                <option value="phone"><?php esc_html_e('Phone', 'jwcfe'); ?></option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="rowDescription" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel" style="width: 40%; margin-right: 10px;"><?php esc_html_e('Description:', 'jwcfe'); ?></div>
                                                        <div>
                                                            <textarea class="custom-textarea-css" type="text" name="ftext" placeholder=""></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="rowClass" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel"  style="width: 40%; margin-right: 10px;"><?php esc_html_e('Field Width:', 'jwcfe'); ?></div>
                                                        <div class="fieldtxt">
                                                            <select name="fclass" style="">
                                                                <option value="form-row-wide"><?php esc_html_e('Full-Width', 'jwcfe'); ?></option>
                                                                <option value="form-row-first"><?php esc_html_e('Half-Width', 'jwcfe'); ?></option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="rowTimepicker" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel"  style="width: 40%; margin-right: 10px;"><?php esc_html_e('Min. Time:', 'jwcfe'); ?><br><span class="thpladmin-subtitle"><?php esc_html_e('ex: 12:30am', 'jwcfe'); ?></span>
                                                        </div>
                                                        <div width="32%"><input type="text" name="i_min_time" value="12:00am" style=""></div>
                                                    </div>
                                                    <div class="rowTimepicker" style="display: flex; align-items: center;">
                                                        <div width="15%"><?php esc_html_e('Max. Time:', 'jwcfe'); ?> <br><span class="thpladmin-subtitle"><?php esc_html_e('ex: 11:30pm', 'jwcfe'); ?></span>
                                                        </div>
                                                        <div width="32%"><input type="text" name="i_max_time" value="11:30pm" style=""></div>
                                                    </div>
                                                    <div class="rowTimepicker"  style="display: flex; align-items: center;">
                                                        <div class="fieldlabel" style="width: 40%; margin-right: 10px;"><?php esc_html_e('Time Format:', 'jwcfe'); ?></div>
                                                        <div width="32%"><select name="i_time_format" value="h:i A" style="">
                                                                <option value="h:i A" selected=""><?php esc_html_e('12-hour format', 'jwcfe'); ?></option>
                                                                <option value="H:i"><?php esc_html_e('24-hour format', 'jwcfe'); ?></option>
                                                            </select></div>
                                                    </div>
                                                    <div class="rowTimepicker"  style="display: flex; align-items: center;">
                                                        <div width="15%"><?php esc_html_e('Time Step:', 'jwcfe'); ?> <br><span class="thpladmin-subtitle"><?php esc_html_e('In minutes, ex: 30', 'jwcfe'); ?></span>
                                                        </div>
                                                        <div width="32%"><input type="text" name="i_time_step" value="30" style=""></div>
                                                    </div>

                                                    <div class="rowExtoptions" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel" style="width: 40%; margin-right: 10px;"><?php esc_html_e('Allowed file types:', 'jwcfe'); ?><font color="red"><?php echo esc_html__('*', 'jwcfe'); ?></font>
                                                        </div>
                                                        <div>
                                                            <select multiple="multiple" name="fextoptions" placeholder="<?php esc_attr_e('Select file types', 'jwcfe'); ?>" class="jwcfe-enhanced-multi-select" style="width: 300px; height:40px;">
                                                                <option value="jpg"><?php esc_html_e('Jpeg', 'jwcfe'); ?></option>
                                                                <option value="png"><?php esc_html_e('Png', 'jwcfe'); ?></option>
                                                                <option value="gif"><?php esc_html_e('Gif', 'jwcfe'); ?></option>
                                                                <option value="doc"><?php esc_html_e('Doc', 'jwcfe'); ?></option>
                                                                <option value="pdf"><?php esc_html_e('PDF', 'jwcfe'); ?></option>
                                                                <option value="txt"><?php esc_html_e('Text', 'jwcfe'); ?></option>
                                                                <option value="ppt"><?php esc_html_e('PPT', 'jwcfe'); ?></option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="rowLabel1" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel" style="width: 40%; margin-right: 10px;"><?php esc_html_e('Label of Field:', 'jwcfe'); ?></div>
                                                        <div><input type="text" name="flabel" placeholder="<?php esc_attr_e('eg. new_field', 'jwcfe'); ?>" />
                                                        </div>
                                                    </div>

                                                    <div class="rowDescription2" style="display: flex; align-items: center;">
                                                        <div class="fieldlabel" style="width: 40%; margin-right: 10px;"><?php esc_html_e('Description:', 'jwcfe'); ?></div>
                                                        <div><textarea class="" type="text" name="ftext" placeholder=""></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php

                                            if (isset($_GET['section']) && $_GET['section'] == 'account') {
                                            ?>
                                                <div class="rowAccess">
                                                    <div>&nbsp;</div>
                                                    <div>
                                                        <input type="checkbox" name="faccess" value="yes" />
                                                        <label><?php esc_html_e("User Can't edit this field", 'jwcfe'); ?></label><br />
                                                    </div>
                                                </div>
                                            <?php
                                            }
                                            ?>

                                           

                                            <div class="rowOptions" style="display: none;">
                                                <div class="fieldlabel">
                                                    <?php esc_html_e('Options:', 'jwcfe'); ?>
                                                    <font color="red"><?php echo esc_html__('*', 'jwcfe'); ?></font>
                                                </div>
                                                <div class="jwcfe_options">
                                                    <div class="jwcfe-option-list thpladmin-dynamic-row-table custom-jwcfe-options">
                                                        <div class="ui-sortable">
                                                            <div class="jwcfe-opt-container custom-scroll-option">
                                                                <div class="jwcfe-opt-row">
                                                                    <div style="width:280px;">
                                                                        <input type="text" name="i_options_key[]" placeholder="<?php esc_attr_e('Option Value', 'jwcfe'); ?>" style="width:280px;"
                                                                            value="<?php echo isset($previous_value['key']) ? esc_attr($previous_value['key']) : esc_attr__('Default Option Value', 'jwcfe'); ?>">
                                                                    </div>
                                                                    <div style="width:280px;">
                                                                        <input type="text" name="i_options_text[]" placeholder="<?php esc_attr_e('Option Text', 'jwcfe'); ?>" style="width:280px;"
                                                                            value="<?php echo isset($previous_value['text']) ? esc_attr($previous_value['text']) : esc_attr__('Default Option Text', 'jwcfe'); ?>">
                                                                    </div>

                                                                    <div class="action-cell">
                                                                        <a href="javascript:void(0)" onclick="jwcfeAddNewOptionRow(this)" class="btn btn-blue" title="Add new option">+</a>
                                                                    </div>

                                                                    <div class="action-cell">
                                                                        <a href="javascript:void(0)" onclick="jwcfeRemoveOptionRow(this)" class="btn btn-red" title="Remove option">x</a>
                                                                    </div>

                                                                    <div class="action-cell sort ui-sortable-handle">
                                                                        <span class="btn btn-tiny sort ui-jwcf-sortable-handle" onclick="jwcfe_handler_OptionRow(this)" title="Drag to sort">⇅</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <table class="checkbox-table">
                                                <tbody style="margin-left: 19px;">
                                                   
                                                    <tr class="checkbox-row">
                                                        <td class="checkbox-cell">
                                                            <input type="checkbox" id="requiredechk" name="frequired" value="yes" checked />
                                                            <label for="requiredechk">Required</label>
                                                        </td>
                                                    </tr>
                                                    <tr class="checkbox-row">
                                                        <td class="checkbox-cell">
                                                            <input type="checkbox" id="enabledchk" name="fenabled" value="yes" checked />
                                                            <label for="enabledchk">show/hide</label>
                                                        </td>
                                                    </tr>
                                                    
                                                    <tr class="checkbox-row">
                                                        <td class="checkbox-cell">
                                                            <input type="checkbox" id="showinorder" name="fshowinorder" value="order-review" checked />
                                                            <label for="showinorder">Display in orders Detail</label>
                                                        </td>
                                                    </tr>
                                                    <tr class="checkbox-row">
                                                        <td class="checkbox-cell">
                                                            <input type="checkbox" name="fshowinemail" value="email" id="showinemail" checked />
                                                            <label for="showinemail">Display in Emails</label>
                                                        </td>
                                                    </tr>
                                                    
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="popup_button_dailogbox" 
                                 style="position: sticky;bottom: 0;  background-color: #f6f6f6; padding: 10px 0;z-index: 100;height: 37px;border-top: 1px solid #e0e0e0;">
                                    
                                    <div class="dialog-button-set">
                                        <button type="button" id="btnaddfield" name="" class="" value="yes">Add New Field</button>
                                    </div>
                                    <div class="dialog-button-set">
                                        <button type="button" id="btncancel" class="btncancel" value="yes" >Cancel</button>
                                    </div>
                            </div>
                        </form>
                    </div>
                </div>
            
            </div>

            <?php

        }
        function get_field_types()
		{

			return array(

				'text'          => 'Text',
				'number'        => 'Number',
				'password'      => 'Password',
				'hidden'        => 'Hidden',
				'email'         => 'Email',
				'phone'         => 'Phone',
				'textarea'      => 'Textarea',
				'select'        => 'Select',
				'multiselect'   => 'Multi-Select',
				'timepicker'    => 'Time Picker',
				'checkbox'      => 'Checkbox',
				'checkboxgroup' => 'Checkbox Group',
				'radio'	        => 'Radio Button',
				'date'	        => 'Date Picker',
				'month'	        => 'Month Picker',
				'week'	        => 'Week Picker',
				'paragraph'	    => 'Paragraph',
				'heading'           => 'Heading'

			);
		}

        function sort_fields_by_order($a, $b)
		{
			if (!isset($a['order']) || $a['order'] == $b['order']) {
				return 0;
			}
			return ($a['order'] < $b['order']) ? -1 : 1;
		}
        function render_checkout_fields_heading_row()
		{

                ?>

                    <th class="sort"></th>

                    <th class="check-column" style="padding-left:0px !important;"><input type="checkbox" style="margin-left:7px;" onclick="jwcfeSelectAllCheckoutFields(this)" /></th>

                    <th class="name"><?php esc_html_e('Name', 'jwcfe'); ?></th>

                    <th class="id"><?php esc_html_e('Type', 'jwcfe'); ?></th>

                    <th><?php esc_html_e('Label', 'jwcfe'); ?></th>

                    <th><?php esc_html_e('Description', 'jwcfe'); ?></th>

                    <th><?php esc_html_e('Validation Rules', 'jwcfe'); ?></th>

                    <th class="status"><?php esc_html_e('Required', 'jwcfe'); ?></th>

                    <th class="status"><?php esc_html_e('Show / Hide', 'jwcfe'); ?></th>

                    <th class="status"><?php esc_html_e('Edit', 'jwcfe'); ?></th>

                <?php

		}


        function render_actions_row($section)
		{

            ?>

                <th colspan="7">
                    <button type="button" class="button button-primary" onclick="openNewFieldForm('<?php echo $section; ?>')"><?php _e('+ Add new field', 'jwcfe'); ?></button>
                    
                    <input type="submit" class="button" name="save_fields" value="<?php _e('Remove', 'jwcfe'); ?>" onclick="removeSelectedFields()">
                    <!-- <button type="button" class="button" onclick="removeSelectedFields()"><?php _e('Remove', 'jwcfe'); ?></button> -->
                    <button type="button" class="button" onclick="enableSelectedFields()"><?php _e('Show', 'jwcfe'); ?></button>
                    <button type="button" class="button" onclick="disableSelectedFields()"><?php _e('Hide', 'jwcfe'); ?></button>
                </th>

                <th colspan="4">

                    <input type="submit" name="save_fields" class="button-primary" value="<?php _e('Save changes', 'jwcfe') ?>" style="float:right" />

                    <input type="submit" name="reset_fields" class="button" value="<?php _e('Reset to default fields', 'jwcfe') ?>" style="float:right; margin-right: 5px;" onclick="return confirm('Are you sure you want to reset to default fields? all your changes will be deleted.');" />

                </th>

            <?php

		}

    }



endif;
