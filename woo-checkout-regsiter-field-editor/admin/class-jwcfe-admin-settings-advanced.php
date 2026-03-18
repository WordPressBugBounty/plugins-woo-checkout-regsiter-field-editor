<?php
/**
 * Advanced Settings page for JWCFE plugin.
 *
 * @package    woo-checkout-regsiter-field-editor
 * @subpackage woo-checkout-regsiter-field-editor/admin
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'JWCFE_Admin_Settings_Advanced' ) ) :

class JWCFE_Admin_Settings_Advanced {

	const OPTION_KEY = 'jwcfe_advanced_settings';

	protected static $_instance = null;

	private $settings_fields = array();

	public function __construct() {
		$this->settings_fields = $this->get_settings_fields();
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/*--------------------------------------------------
	 * Settings Fields Definition
	 *-------------------------------------------------*/

	public function get_settings_fields() {
		return array(
			'enable_label_override' => array(
				'name'    => 'enable_label_override',
				'label'   => __( 'Enable label override for address fields.', 'jwcfe' ),
				'type'    => 'checkbox',
				'value'   => '1',
				'checked' => 1,
			),
			'enable_placeholder_override' => array(
				'name'    => 'enable_placeholder_override',
				'label'   => __( 'Enable placeholder override for address fields.', 'jwcfe' ),
				'type'    => 'checkbox',
				'value'   => '1',
				'checked' => 1,
			),
			'enable_class_override' => array(
				'name'    => 'enable_class_override',
				'label'   => __( 'Enable class override for address fields.', 'jwcfe' ),
				'type'    => 'checkbox',
				'value'   => '1',
				'checked' => 1,
			),
			'enable_priority_override' => array(
				'name'    => 'enable_priority_override',
				'label'   => __( 'Enable priority override for address fields.', 'jwcfe' ),
				'type'    => 'checkbox',
				'value'   => '1',
				'checked' => 1,
			),
			'enable_required_override' => array(
				'name'    => 'enable_required_override',
				'label'   => __( 'Enable required validation override for address fields.', 'jwcfe' ),
				'type'    => 'checkbox',
				'value'   => '1',
				'checked' => 1,
			),
		);
	}

	/*--------------------------------------------------
	 * Static Helpers
	 *-------------------------------------------------*/

	public static function get_advanced_settings() {
		$settings = get_option( self::OPTION_KEY );
		return empty( $settings ) ? false : $settings;
	}

	public static function get_setting( $key ) {
		$settings = self::get_advanced_settings();
		if ( is_array( $settings ) && isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}
		// Return default: enabled by default
		return '1';
	}

	public function save_advanced_settings( $settings ) {
		return update_option( self::OPTION_KEY, $settings, 'no' );
	}

	/*--------------------------------------------------
	 * Save / Reset
	 *-------------------------------------------------*/

	private function save_settings() {
		$nonce = isset( $_REQUEST['jwcfe_security_advanced_settings'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['jwcfe_security_advanced_settings'] ) )
			: false;

		if ( ! wp_verify_nonce( $nonce, 'jwcfe_advanced_settings' ) || ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		$settings = array();
		foreach ( $this->settings_fields as $name => $field ) {
			if ( $field['type'] === 'checkbox' ) {
				$settings[ $name ] = ! empty( $_POST[ 'i_' . $name ] ) ? '1' : '';
			} else {
				$value = ! empty( $_POST[ 'i_' . $name ] ) ? $_POST[ 'i_' . $name ] : '';
				$settings[ $name ] = ! empty( $value ) ? wc_clean( wp_unslash( $value ) ) : '';
			}
		}

		$result = $this->save_advanced_settings( $settings );

		if ( $result ) {
			$this->print_notice( __( 'Your changes were saved.', 'jwcfe' ), 'updated' );
		} else {
			$this->print_notice( __( 'Your changes were not saved (or you made none).', 'jwcfe' ), 'error' );
		}
	}

	private function reset_settings() {
		$nonce = isset( $_REQUEST['jwcfe_security_advanced_settings'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['jwcfe_security_advanced_settings'] ) )
			: false;

		if ( ! wp_verify_nonce( $nonce, 'jwcfe_advanced_settings' ) || ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		delete_option( self::OPTION_KEY );
		$this->print_notice( __( 'Settings successfully reset.', 'jwcfe' ), 'updated' );
	}

	/*--------------------------------------------------
	 * Render Page
	 *-------------------------------------------------*/

	public function render_page() {
		if ( isset( $_POST['jwcfe_reset_advanced_settings'] ) ) {
			$this->reset_settings();
		}
		if ( isset( $_POST['jwcfe_save_advanced_settings'] ) ) {
			$this->save_settings();
		}
		if ( isset( $_POST['jwcfe_import_settings'] ) ) {
			$this->save_plugin_settings();
		}

		$this->render_locale_settings_form();
		$this->render_import_export_form();
	}

	private function render_locale_settings_form() {
		$settings = self::get_advanced_settings();
		?>
		<div style="padding: 20px 30px;">
			<form id="jwcfe_advanced_settings_form" method="post" action="">
				<?php wp_nonce_field( 'jwcfe_advanced_settings', 'jwcfe_security_advanced_settings' ); ?>
				<h3><?php esc_html_e( 'Locale Override Settings', 'jwcfe' ); ?></h3>
				<table class="widefat" style="max-width:700px;">
					<tbody>
						<tr>
							<td colspan="2" style="background:#f9f9f9; font-weight:600; padding:10px 12px;">
								<?php esc_html_e( 'Address Field Overrides', 'jwcfe' ); ?>
							</td>
						</tr>
						<?php foreach ( $this->settings_fields as $name => $field ) : ?>
							<tr>
								<td style="padding:10px 12px;">
									<?php $this->render_checkbox( $field, $settings ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p class="submit" style="margin-top:15px;">
					<input type="submit" name="jwcfe_save_advanced_settings" class="button button-primary"
						value="<?php esc_attr_e( 'Save Changes', 'jwcfe' ); ?>">
					<input type="submit" name="jwcfe_reset_advanced_settings" class="button"
						value="<?php esc_attr_e( 'Reset to Default', 'jwcfe' ); ?>"
						onclick="return confirm('<?php esc_attr_e( 'Are you sure? All your changes will be deleted.', 'jwcfe' ); ?>')">
				</p>
			</form>
		</div>
		<?php
	}

	private function render_checkbox( $field, $settings ) {
		$name    = $field['name'];
		$label   = $field['label'];
		$checked = 1; // default on

		if ( is_array( $settings ) && isset( $settings[ $name ] ) ) {
			$checked = ( $settings[ $name ] === '1' ) ? 1 : 0;
		}

		$fid = 'jwcfe_adv_' . esc_attr( $name );
		?>
		<label for="<?php echo esc_attr( $fid ); ?>">
			<input type="checkbox"
				id="<?php echo esc_attr( $fid ); ?>"
				name="i_<?php echo esc_attr( $name ); ?>"
				value="1"
				<?php checked( $checked, 1 ); ?> />
			<?php echo esc_html( $label ); ?>
		</label>
		<?php
	}

	/*--------------------------------------------------
	 * Import / Export
	 *-------------------------------------------------*/

	private function render_import_export_form() {
		$plugin_settings = $this->prepare_export_data();
		?>
		<div style="padding: 0 30px 20px;">
			<form id="jwcfe_import_export_form" method="post" action="">
				<?php wp_nonce_field( 'jwcfe_import_settings', 'jwcfe_import_nonce' ); ?>
				<h3><?php esc_html_e( 'Backup and Import Settings', 'jwcfe' ); ?></h3>
				<p style="color:#666; max-width:700px;">
					<?php esc_html_e( 'You can transfer the saved settings data between different installs by copying the text inside the text box. To import data from another install, replace the data in the text box with the one from another install and click "Import Settings".', 'jwcfe' ); ?>
				</p>
				<textarea name="i_settings_data" rows="8" style="width:100%; max-width:700px; font-family:monospace; font-size:12px;"><?php echo esc_textarea( $plugin_settings ); ?></textarea>
				<p class="submit">
					<input type="submit" name="jwcfe_import_settings" class="button button-primary"
						value="<?php esc_attr_e( 'Import Settings', 'jwcfe' ); ?>">
				</p>
			</form>
		</div>
		<?php
	}

	private function prepare_export_data() {
		$data = array(
			'billing'    => get_option( 'jwcfe_wc_fields_billing' ),
			'shipping'   => get_option( 'jwcfe_wc_fields_shipping' ),
			'additional' => get_option( 'jwcfe_wc_fields_additional' ),
			'block_billing'    => get_option( 'jwcfe_wc_fields_block_billing' ),
			'block_shipping'   => get_option( 'jwcfe_wc_fields_block_shipping' ),
			'block_additional' => get_option( 'jwcfe_wc_fields_block_additional' ),
			'advanced'   => get_option( self::OPTION_KEY ),
		);
		return base64_encode( json_encode( $data ) );
	}

	public function save_plugin_settings() {
		check_admin_referer( 'jwcfe_import_settings', 'jwcfe_import_nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die();
		}

		if ( empty( $_POST['i_settings_data'] ) ) {
			$this->print_notice( __( 'No settings data provided.', 'jwcfe' ), 'error' );
			return false;
		}

		$encoded  = sanitize_textarea_field( wp_unslash( $_POST['i_settings_data'] ) );
		$decoded  = base64_decode( $encoded );

		if ( ! $this->is_json( $decoded ) ) {
			$this->print_notice( __( 'The entered import settings data is invalid. Please try again with valid data.', 'jwcfe' ), 'error' );
			return false;
		}

		$settings = json_decode( $decoded, true );
		$saved    = false;

		$map = array(
			'billing'          => 'jwcfe_wc_fields_billing',
			'shipping'         => 'jwcfe_wc_fields_shipping',
			'additional'       => 'jwcfe_wc_fields_additional',
			'block_billing'    => 'jwcfe_wc_fields_block_billing',
			'block_shipping'   => 'jwcfe_wc_fields_block_shipping',
			'block_additional' => 'jwcfe_wc_fields_block_additional',
		);

		foreach ( $map as $key => $option ) {
			if ( isset( $settings[ $key ] ) ) {
				update_option( $option, $settings[ $key ] );
				$saved = true;
			}
		}

		if ( isset( $settings['advanced'] ) ) {
			$this->save_advanced_settings( $settings['advanced'] );
			$saved = true;
		}

		if ( $saved ) {
			$this->print_notice( __( 'Settings imported successfully.', 'jwcfe' ), 'updated' );
		} else {
			$this->print_notice( __( 'Nothing was imported (or data was empty).', 'jwcfe' ), 'error' );
		}
	}

	private function is_json( $string ) {
		json_decode( $string );
		return json_last_error() === JSON_ERROR_NONE;
	}

	/*--------------------------------------------------
	 * Notice Helper
	 *-------------------------------------------------*/

	private function print_notice( $msg, $type = 'updated' ) {
		?>
		<div class="<?php echo esc_attr( $type ); ?> notice is-dismissible" style="margin:10px 0;">
			<p><?php echo esc_html( $msg ); ?></p>
		</div>
		<?php
	}
}

endif;