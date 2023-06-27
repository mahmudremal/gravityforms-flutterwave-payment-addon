<?php
/**
 * LoadmorePosts
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
use \WP_Query;use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Option;
class Gravityforms {
	use Singleton;
	private $settings;
	private $settingSlug;
	private $gformSetting;
	protected function __construct() {
		$this->settingSlug = 'flutterwaveaddons';
		$this->settings = GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS;
		$this->id = 'flutterlovesgravity';
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		// add_action( 'init', [ $this, 'wp_init' ], 10, 0 );
		// add_action( 'admin_init', [ $this, 'admin_init' ], 10, 0 );
		// add_filter( 'pre_get_posts', [ $this, 'pre_get_posts' ], 10, 1 );

		// Hook into the Gravity Forms submission process
		add_action('gform_entry_post_save', [$this, 'process_flutterwave_payment'], 10, 2);

		// Handle the return URL after the Flutterwave payment
		add_action('template_redirect', [$this, 'handle_flutterwave_payment_return']);

		
		add_action('gform_field_standard_settings', [$this, 'gform_field_standard_settings'], 10, 2);
		// add_action('gform_field_content', [$this, 'gform_field_content'], 10, 5);
		add_filter('gform_pre_update_filter', [$this, 'gform_pre_update_filter'], 10, 2);
		// Action to inject supporting script to the form editor page
		add_action('gform_editor_js', [$this, 'editor_script']);
		//Filter to add a new tooltip
		add_filter('gform_tooltips', [$this, 'add_encryption_tooltips']);

		add_filter('gform_settings_menu', [$this, 'gform_settings_menu'], 1, 1);

		add_action('gform_settings_flutterwaveaddons', [$this, 'gform_settings_flutterwaveaddons'], 10, 0);
		
		// Refund Woocommerce Order
		add_action('woocommerce_order_status_refunded', [$this, 'process_flutterwave_refund'], 10, 1);


		/**
		 * Gravity form addon.
		 */
		// add_action( 'gform_loaded', [$this, 'load_gravityform_addon'], 5, 0 );

		/**
		 * Gravity form custom field.
		 */
		// add_action( 'gform_loaded', [$this, 'register_credit_card_field'], 5, 0 );
		// add_action('gform_field_standard_fields', [$this, 'gform_field_standard_fields'], 10, 1);
		add_action('gform_add_field_buttons', [$this, 'gform_add_field_buttons'], 10, 1);
		// Add a custom payment button
		add_filter('gform_submit_button', [$this, 'gform_submit_button'], 10, 2);
		add_filter('gform_editor_js', [$this, 'gform_editor_js'], 10, 2);
		add_filter('gform_field_css_class', [$this, 'gform_field_css_class'], 10, 3);

		// Gravityforms single form settings.
		add_filter('gform_tooltips', [$this, 'gform_tooltips'], 10, 1);
		add_filter('gform_form_settings_fields', [$this, 'gform_form_settings_fields'], 10, 2);

		add_action('init', function() {$this->gformSetting = \GFFormsModel::get_form_meta(1);}, 1, 0);


		add_filter('gform_pre_send_email', [$this, 'gform_pre_send_email'], 10, 4);
		add_action('gform_entry_created', [$this, 'gform_entry_created'], 10, 2);
		// add_filter('gform_payment_complete', [$this, 'approve_entry_and_trigger_notifications'], 10, 2);

		// add_action('gform_pre_submission', [$this, 'gform_pre_submission'], 10, 1);
		// add_action('gform_after_submission', [$this, 'gform_after_submission'], 10, 2);


		add_action('wp_ajax_gravityformsflutterwaveaddons/project/mailsystem/sendreminder', [$this, 'sendReminder'], 10, 0);

		// add_action( 'init', [ $this, 'wp_init' ], 10, 0 );
	}
	public function wp_init() {
		$entry_id = 64;
		$entry = \GFAPI::get_entry($entry_id);
		if (is_wp_error($entry)) {
			echo 'Entry not found.';
		} else {
			// print_r($entry);
			$custom_data = $this->extract_gravityentry_fields($entry);
			print_r($custom_data);
		}
		wp_die();
	}
	public function admin_init() {}
	public function pre_get_posts( $query ) {}
	
	public function extract_gravityentry_fields($entry) {
		$custom_data = [];
		$form = \GFAPI::get_form($entry['form_id']);
		$fields = $form['fields'];
		foreach ($fields as $field) {
			$field_id = $field->id;
			$field_type = $field->type;
			$field_label = $field->label;
			// print_r($field->type."\n");
			switch ($field->type) {
				case 'email':
				case 'total':
				case 'website':
				case 'phone':
				case 'quantity':
					$custom_data[$field->type] = rgar($entry, $field_id);
					break;
				case 'name':
					$custom_data['name'] = array(
						'first' => rgar($entry, $field_id . '.3'),
						'last' => rgar($entry, $field_id . '.6')
					);
					break;
				case 'address':
					$custom_data['address'] = [
						'street' => rgar($entry, $field_id . '.1'),
						'address2' => rgar($entry, $field_id . '.2'),
						'city' => rgar($entry, $field_id . '.3'),
						'state' => rgar($entry, $field_id . '.4'),
						'zip' => rgar($entry, $field_id . '.5'),
						'country' => rgar($entry, $field_id . '.6'),
					];
					break;
				case 'product':
					$custom_data['product'] = isset($custom_data['product'])?(array)$custom_data['product']:[];
					$custom_data['product'][] = rgar($entry, $field_id);
					break;
				default:
					break;
			}
		}
		return $custom_data;
	}
	
	public function process_flutterwave_payment($entry, $form) {
		// Check if the form has the required payment field
		if (!isset($form['paymentMethod']) || $form['paymentMethod']['gateway'] !== 'flutterwave') {
			return;
		}
		// Get the submission ID and transaction reference from the entry
		$submission_id = $entry['id'];
		$txref = 'your_transaction_reference'; // Replace with your transaction reference generation logic
		// Get the total payment amount from the entry
		$payment_amount = rgar($entry, 'payment_amount');
		// Create a payment request to Flutterwave
		$payment_request = $this->create_flutterwave_payment_request($txref, $payment_amount);
		// Store the payment request details in the entry meta
		gform_update_meta($submission_id, 'flutterwave_payment_request', $payment_request);
		// Redirect the user to the payment URL
		wp_redirect($payment_request['data']['link']);
		exit();
	}
	public function handle_flutterwave_payment_return() {
		// Check if the current request is the return URL from Flutterwave
		if (!isset($_GET['flutterwave_payment_return']) || $_GET['flutterwave_payment_return'] !== 'true') {
			return;
		}
	
		// Get the submission ID from the query string or session data
		$submission_id = isset($_GET['entry']) ? intval($_GET['entry']) : intval($_SESSION['flutterwave_submission_id']);
	
		// Get the payment request details from the entry meta
		$payment_request = gform_get_meta($submission_id, 'flutterwave_payment_request');
	
		// Verify the payment status and process the payment response
		$payment_status = $this->verify_flutterwave_payment_status($payment_request);
	
		// Update the entry with the payment status
		gform_update_meta($submission_id, 'flutterwave_payment_status', $payment_status);
	
		// Redirect the user to the appropriate confirmation page based on the payment status
		$confirmation_url = $this->get_flutterwave_confirmation_url($payment_status);
		wp_redirect($confirmation_url);
		exit();
	}
	// Example functions for creating a payment request, verifying payment status, and getting the confirmation URL
	public function create_flutterwave_payment_request($txref, $amount) {
		// Make a cURL request to Flutterwave API to create a payment request
		// Replace with your own implementation using the Flutterwave API

		// Return the payment request details (e.g., response from Flutterwave API)
		return $payment_request;
	}
	public function verify_flutterwave_payment_status($payment_request) {
		// Verify the payment status using the payment request details
		// Replace with your own implementation using the Flutterwave API

		// Return the payment status (e.g., success, failed, pending, etc.)
		return $payment_status;
	}
	public function get_flutterwave_confirmation_url($payment_status) {
		// Return the appropriate confirmation page URL based on the payment status
		// Replace with your own implementation or customize the logic as needed

		if ($payment_status === 'success') {
			return home_url('/payment-success');
		} else {
			return home_url('/payment-failed');
		}
	}


	public function gform_field_standard_settings( $position, $form_id ) {
		//create settings on position 25 (right after Field Label)
		if ( $position == 25 ) {
			?>
			<li class="encrypt_setting field_setting">
				<input type="checkbox" id="field_encrypt_value" onclick="SetFieldProperty('encryptField', this.checked);" />
				<label for="field_encrypt_value" style="display:inline;">
					<?php _e("Encrypt Field Value", "your_text_domain"); ?>
					<?php gform_tooltip("form_field_encrypt_value") ?>
				</label>
			</li>
			<?php
		}
	}
	public function gform_field_content($content, $field, $value, $lead_id, $form_id) {
		if ($field->type === 'my_custom_settings_field') {
			$content = stripslashes($value);
		}
		return $content;
	}
	public function gform_pre_update_filter($form, $settings) {
		if($form['slug'] === 'flutterwaveaddons') {
			$settings['paymentReminder'] = stripslashes($settings['paymentReminder']);
			return $settings;
		}
		return $form;
	}
	public function editor_script(){
		?>
		<script type='text/javascript'>
			//adding setting to fields of type "text"
			fieldSettings.text += ', .encrypt_setting';
			//binding to the load field settings event to initialize the checkbox
			jQuery(document).on('gform_load_field_settings', function(event, field, form){
				jQuery( '#field_encrypt_value' ).prop( 'checked', Boolean( rgar( field, 'encryptField' ) ) );
			});
		</script>
		<?php
	}
	public function add_encryption_tooltips( $tooltips ) {
		$tooltips['form_field_encrypt_value'] = "<h6>Encryption</h6>Check this box to encrypt this field's data";
		return $tooltips;
	}

	public function gform_settings_menu($setting_tabs) {
		$icon = GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_PATH . '/icons/money-business-and-finance-svgrepo-com.svg';
		$icon = (file_exists($icon)&&!is_dir($icon))?file_get_contents($icon):'gform-icon gform-icon--api';
		$tab = [
			'name'	=> 'flutterwaveaddons',
            'label'	=> 'Flutterwave',
            'title'	=> 'Gravity Forms FlutterWave Payment addons.',
            'icon'	=> $icon
		];
		$setting_tabs[12] = $tab;
		// $setting_tabs = array_merge(array_slice($setting_tabs, 0, 2), [$tab], array_slice($setting_tabs, 2));
		return $setting_tabs;
	}
	public function print_settings_fields($fields) {
		$html = '';
		foreach($fields as $field) {
			$html .= $this->display_field([
				'field'	=> $field
			]);
		}
		return $html;
	}
	public function gform_settings_flutterwaveaddons() {
		// Check if the user has permissions to access the settings page
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		// Save settings if form is submitted
		if (isset($_POST['gform_settings_flutterwaveaddons'])) {
			// Perform validation and save the settings
			update_option('flutterwaveaddons', $_POST['flutterwaveaddons']);
			$this->settings = $_POST['flutterwaveaddons'];
			// Add other necessary settings update code here
			// Display a success message
			echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
		}
		// Retrieve the current settings values
		$api_key = get_option('gf_flutterwave_api_key');
		// Render the settings page HTML
		$settings = $this->settings_fields();
		?>
		<form id="gform-settings" class="gform_settings_form" action="" method="post" enctype="multipart/form-data">
			<?php wp_nonce_field('gform_settings_flutterwaveaddons', 'gform_settings_flutterwaveaddons', true, true ); ?>
			<fieldset class="gform-settings-panel gform-settings-panel--full gform-settings-panel--with-title">
				<legend class="gform-settings-panel__title gform-settings-panel__title--header">
					<?php echo esc_html($settings['title']); ?>
				</legend>
				<div class="gform-settings-panel__content">
					<div class="gform-settings-description gform-kitchen-sink">
						<?php echo wp_kses_post($settings['description']); ?>
					</div>
					<?php echo $this->print_settings_fields($settings['fields']); ?>
				</div>
			</fieldset>
			<div class="gform-settings-save-container">
				<button type="submit" id="gform-settings-save" name="gform-settings-save" value="save" form="gform-settings" class="primary button large">Save Settings &nbsp;→</button>
			</div>
			<script type="text/javascript" src="http://localhost/wordpress/wp-content/plugins/gravityforms/js/plugin_settings.js"></script>
		</form>
		<?php
	}

	public function process_flutterwave_refund($order_id) {
		$order = wc_get_order($order_id);
	
		// Check if the order is paid using the Flutterwave payment gateway
		if( $this->id === $order->get_payment_method()) {
			// Get the Flutterwave transaction ID from the order metadata
			$transaction_id = $order->get_meta('_transaction_id');
	
			// Make the refund request to Flutterwave using the transaction ID
			$refund_amount = $order->get_total();
			$api_key = get_option('gf_flutterwave_api_key');
	
			// Make the API request to initiate the refund
			$url = 'https://api.flutterwave.com/v3/refunds';
			$headers = [
				'Authorization: Bearer ' . $api_key,
				'Content-Type: application/json',
			];
			$data = [
				'transaction_id' => $transaction_id,
				'amount' => $refund_amount,
				// Add any additional refund parameters as needed
			];
	
			$response = wp_remote_post($url, [
				'headers' => $headers,
				'body' => json_encode($data),
			]);
	
			// Process the response and update order status accordingly
			if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
				$order->add_order_note('Refund processed successfully via Flutterwave.');
				$order->update_status('refunded');
			} else {
				$error_message = is_wp_error($response) ? $response->get_error_message() : 'Refund request failed.';
				$order->add_order_note('Failed to process refund via Flutterwave: ' . $error_message);
			}
		}
	}


	public function settings_fields() {
		$args = [
			'title'							=> __( 'General', 'gravitylovesflutterwave' ),
			'description'				=> sprintf(
				__('Gravity Forms integration with FlutterWave payments will work on both Gravity Forms and WooCommerce plugins. A secret key is mostly required to connect with FlutterWave. If you don\'t have this API key, you can %sfollow this link.%s', 'gravitylovesflutterwave' ),
				'<a href="https://flutterwave.com/" target="_blank">', '</a>'
			),
			'fields'						=> [
				[
					'id' 						=> 'publickey',
					'label'					=> __( 'Public Key', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> true,
					// 'description'			=> __( 'Mark to enable function of this Plugin.', 'gravitylovesflutterwave' ),
					'help'					=> '<strong>Public Key</strong>Enter your Public Key, if you do not have a key you can register for one at the provided link.'
				],
				[
					'id' 						=> 'secretkey',
					'label'					=> __( 'Secret Key', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> true,
					// 'description'			=> __( 'Mark to enable function of this Plugin.', 'gravitylovesflutterwave' ),
					'help'					=> '<strong>Secret Key</strong>Enter your Secret Key, if you do not have a key you can register for one at the provided link.'
				],
				[
					'id' 						=> 'encryptionkey',
					'label'					=> __( 'Encryption Key', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> true,
					// 'description'			=> __( 'Mark to enable function of this Plugin.', 'gravitylovesflutterwave' ),
					'help'					=> '<strong>Encryption Key</strong>Enter your Encryption Key, if you do not have a key you can register for one at the provided link.'
				],
				[
					'id' 						=> 'amountZeroMsg',
					'label'					=> __( 'Amount required message', 'gravitylovesflutterwave' ),
					'description'		=> __( 'This message will be the default message on settings field if the calculated amount is zero.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> 'You must calculate an amount to make pay and proceed. Currently calculated amount is zero or less then zero!'
				],
				[
					'id'					=> 'paymentSuccess',
					'label'					=> __( 'Success status', 'gravitylovesflutterwave' ),
					'description'			=> __( 'Give here a long success message that will be display on payment success page. With the confirmation message that the form submitted successfully.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default_value'			=> "Congratulations! Your payment was successful. Thank you for your trust and support. We're delighted to inform you that your form submission has been received and processed successfully. Your payment has been successfully completed, and we appreciate your valuable contribution. We're grateful for your business and look forward to serving you again in the future. If you have any questions or need further assistance, please don't hesitate to reach out to our support team. Once again, thank you for choosing us, and have a wonderful day!"
				],
				[
					'id' 						=> 'paymentFailed',
					'label'					=> __( 'Failed status', 'gravitylovesflutterwave' ),
					'description'		=> __( "Give here a long error message that will be display on payment failed/cancelled/denaid status. With the confirmation message that the form didn't submitted.", 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default_value'			=> "We apologize for the inconvenience. Unfortunately, your payment was not successful. We understand that this may be disappointing. Please note that your form submission was not completed successfully. If you encountered any issues or have any questions regarding the payment or form submission process, please feel free to contact our support team. We're here to assist you and resolve any concerns you may have. We value your interest and hope to have the opportunity to serve you better in the future. Thank you for your understanding."
				],
				[
					'id' 					=> 'paymentReminderSubject',
					'label'					=> __( 'Mail reminder subject', 'gravitylovesflutterwave' ),
					'description'		=> __( "Give here a long error message that will be display on payment failed/cancelled/denaid status. With the confirmation message that the form didn't submitted.", 'gravitylovesflutterwave' ),
					'type'					=> 'text'
				],
				[
					'id' 					=> 'paymentReminder',
					'name' 					=> 'paymentReminder',
					'label'					=> __( 'Payment Reminder', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'description'		=> sprintf(
						__( "Give here any html template that will be applied for payment reminder email template from Entry list screen. Following tags could be applicable on this template. %s", 'gravitylovesflutterwave' ),
						'{{mailImagePath}}, {{customFullName}}, {{senderFullName}}, {{dateMMMMdd}}, {{dateYYYMMDD}}, {{productName}}, {{invoiceNumber}}, {{siteEmail}}, {{siteURL}}, {{siteAddress}}, {{customAddressFull}}, {{invoiceIssuedOn}}, {{invoiceUnit}}, {{invoiceTotal}}, {{invoiceTax}}, {{invoiceSubtotal}}'
					),
					'default_value'			=> ''
				],
			]
		];
		return $args;
	}
	public function display_field( $args ) {
		$field = wp_parse_args( $args['field'], [
			'placeholder'	=> ''
		] );
		$html = '';
		$option_name = $this->settingSlug ."[". $field['id']. "]";
		$field[ 'default' ] = isset( $field[ 'default' ] ) ? $field[ 'default' ] : '';
		$data = (isset($this->options[$field['id']])) ? $this->options[$field['id']] : $field[ 'default' ];
		$field['value'] = isset($this->settings[$field['id']])?$this->settings[$field['id']]:'';
		switch( $field['type'] ) {
			case 'text':case 'email':case 'password':case 'number':
			case 'date':case 'time':case 'color':case 'url':
				$html .= '
					<div class="gform-settings-field gform-settings-field__'.esc_attr($field['type']).'">
						<div class="gform-settings-field__header">
							<label class="gform-settings-label" for="public_key">'.esc_html($field['label']).'</label>
							'.wp_kses_post(
								(isset($field['help'])) ? '<button onclick="return false;" onkeypress="return false;" class="gf_tooltip tooltip tooltip_settings_recaptcha_public" aria-label="'.esc_attr($field['help']).'">
								<i class="gform-icon gform-icon--question-mark" aria-hidden="true"></i>
							</button>': ''
							).'
						</div>
						<span class="gform-settings-input__container">
						<input type="'.$field['type'].'" name="'.esc_attr($option_name).'" value="'.esc_attr($field['value']).'" id="'.esc_attr($field['id']).'" placeholder="'.esc_attr($field['placeholder']).'" value="'.esc_attr($data).'"'.$this->attributes($field).'>
						</span>
					</div>
				';
			break;
			case 'text_secret':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" ' . $this->attributes( $field ) . '/>' . "\n";
			break;
			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . $this->attributes( $field ) . '>' . $data . '</textarea><br/>'. "\n";
			break;
			case 'checkbox':
				$checked = '';
				if( ( $data && 'on' == $data ) || $field[ 'default' ] == true ) {
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $option_name ) . '" ' . $checked . ' ' . $this->attributes( $field ) . '/>' . "\n";
			break;
			case 'checkbox_multi':
				foreach( $field['options'] as $k => $v ) {
					$checked = false;
					if( is_array($data) && in_array( $k, $data ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;
			case 'radio':
				foreach( $field['options'] as $k => $v ) {
					$checked = false;
					if( $k == $data ) {$checked = true;}
					if( ! $checked && $k == $field[ 'default' ] ) {$checked = true;}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" ' . $this->attributes( $field ) . '/> ' . $v . '</label> ';
				}
			break;
			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '" ' . $this->attributes( $field ) . '>';
				foreach( $field['options'] as $k => $v ) {
					$selected = ( $k == $data );
					if( empty( $data ) && ! $selected && $k == $field[ 'default' ] ) {$selected = true;}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;
			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple" ' . $this->attributes( $field ) . '>';
				foreach( $field['options'] as $k => $v ) {
					$selected = false;
					if( in_array( $k, $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option> ';
				}
				$html .= '</select> ';
			break;
		}
		switch( $field['type'] ) {
			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= apply_filters( 'gravityformsflutterwaveaddons/project/settings/fields/label', '<br/><span class="description">' . $field['description'] . '</span>', $field );
			break;
			default:
				$html .= isset($field['description'])?apply_filters( 'gravityformsflutterwaveaddons/project/settings/fields/label', '<label for="' . esc_attr( $field['id'] ) . '"><span class="description">' . $field['description'] . '</span></label>' . "\n", $field ):'';
			break;
		}
		echo $html;
	}
	public function attributes( $field ) {
		if( ! isset( $field[ 'attr' ] ) || ! is_array( $field[ 'attr' ] ) || count( $field[ 'attr' ] ) < 1 ) {return '';}
		$html = '';
		foreach( $field[ 'attr' ] as $attr => $value ) {
			$html .= $attr . '="' . $value . '" ';
		}
		return $html;
	}

	public function load_gravityform_addon() {
		if(class_exists('GFForms')) {
			if(!method_exists('GFForms','include_addon_framework')) {return;}
			\GFForms::include_addon_framework();
		}
        require_once(untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH).'/inc/widgets/widget-flutterwave-payment.php'); // GFFlutterwavePaymentAddon::get_instance();
        \GFAddOn::register( 'GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\GFFlutterwavePaymentAddon' );
		
        // require_once(untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH).'/inc/widgets/widget-flutterwave-simple.php'); // GFFlutterwaveSimpleAddon::get_instance();
        // \GFAddOn::register( 'GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\GFFlutterwaveSimpleAddon' );
		
	}


	public function register_credit_card_field() {
		if (class_exists('GFForms')) {
			require_once(untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH).'/inc/widgets/widget-flutterwave-cards.php');
			\GF_Fields::register( new GF_FlutterWave_Credit_Card_Field() );
		}
	}
	public function gform_field_standard_fields($fields) {
		require_once(untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH).'/inc/widgets/widget-flutterwave-cards.php');
		$fields['credit_card'] = 'GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\GF_FlutterWave_Credit_Card_Field';
		
		// print_r($fields);wp_die();exit;
		
		return $fields;
	}
	public function gform_add_field_buttons($field_groups) {
		foreach ($field_groups as &$group) {
			if ($group['name'] === 'advanced_fields') {
				$group['fields'][] = array(
					'class' => 'button',
					'value' => __('Credit Card', 'gravitylovesflutterwave'),
					'onclick' => "StartAddField('credit_card');",
				);
				break;
			}
		}
		return $field_groups;
	}
	public function gform_submit_button($button, $form) {
		unset($form['fields']);
		if(!isset($form['enableFlutterwave']) || !$form['enableFlutterwave']) {return $button;}

		// print_r([$button, $form]);wp_die();
		$payment_gateway = 'flutterwave'; // Replace with your Flutterwave payment gateway ID | 
		$button_text = __('Pay with Flutterwave', 'gravitylovesflutterwave'); // Replace with your desired button text

		$has_payment = true;
		// $has_payment = array_search('flutterwave_credit_card', array_column($form['fields'], 'type'));
		// $button = ($has_payment)?"<button class='gform_button btn btn-primary button do_flutterwave_submit' type='button'>{$form['submitBtnText']}</button>":$button;
		$button = ($has_payment)?str_replace('gform_button', 'gform_button do_flutterwave_submit', $button):$button;
		$button = empty($form['submitBtnText'])?$button:preg_replace("/value='[^']*'/", "value='" . $form['submitBtnText'] . "'", $button);

		// Check if the form has the specified payment gateway
		// if(isset($form['gateway']) && in_array($payment_gateway, $form['gateway'])) {
		// 	$button = "<button class='gform_button button' onclick='alert(this);'>$button_text</button>";
		// }
	
		return $button;
	}
	public function gform_editor_js() {
		do_action('gravityformsflutterwaveaddons/project/assets/register_styles');
        do_action('gravityformsflutterwaveaddons/project/assets/register_scripts');
        wp_enqueue_style('GravityformsFlutterwaveAddons');wp_enqueue_script('imask');
        wp_enqueue_script('GravityformsFlutterwaveAddons');
		?>
		<script type="text/javascript">
			// Enable the custom field in the form editor
			if(typeof fieldSettings !== 'undefined') {
				fieldSettings['credit_card'] = ".label_setting, .admin_label_setting, .visibility_setting, .css_class_setting, .size_setting";
			}

			// Define the field button
			if(typeof fieldButtons !== 'undefined') {
				fieldButtons['credit_card'] = {
					title: '<?php echo esc_js(__('Credit Card', 'gravitylovesflutterwave')); ?>',
					onclick: function() {
						StartAddField('credit_card');
					}
				};
			}
			
		</script>
		<?php
	}
	public function gform_field_css_class($classes, $field, $form) {
		if ($field->type == 'custom_field_type') {
			$classes .= 'custom_field_setting';
		}
		return $classes;
	}
	public function gform_tooltips($tooltips) {
		$tooltips['form_flutterwave'] = sprintf(
			__('%s Enable Flutterwave %s Check this option to enable Flutterwave Payment for this form. This will calculate prices, taxes and prevent submittion before payment.', 'gravitylovesflutterwave'),
			'<strong>', '</strong>'
		);
		$tooltips['form_comissiontype'] = sprintf(
			__("%s Comission Type %s Choose a commission type: percentage or flat rate. If you select 'percentage,' the plugin will calculate a commission amount based on the percentage of the total payment. If you choose 'flat rate,' the plugin will deduct the specified flat amount from the total payment if the total exceeds that amount.", 'gravitylovesflutterwave'),
			'<strong>', '</strong>'
		);
		$tooltips['form_comissionpercent'] = sprintf(
			__("%s Comission Percent %s Give here a percentage of amount of comission for sub accounts.", 'gravitylovesflutterwave'),
			'<strong>', '</strong>'
		);
		$tooltips['form_comissionflat'] = sprintf(
			__("%s Flat Amount %s Give here a flat amount of comission for sub accounts. If calculated total is less then this flat amount, it won't be applied.", 'gravitylovesflutterwave'),
			'<strong>', '</strong>'
		);
		$tooltips['form_subaccounts'] = sprintf(
			__("%s Sub Accounts %s Select sub accounts for serving comissions. Flat amount or percentage, both will be prioritize on ascending order.", 'gravitylovesflutterwave'),
			'<strong>', '</strong>'
		);
		$tooltips['form_submittext'] = sprintf(
			__("%s Submit Button Text %s You can setup a submit button text here. E.g. Pay, Register, Submit.", 'gravitylovesflutterwave'),
			'<strong>', '</strong>'
		);
		$tooltips['form_require_amount_message'] = sprintf(
			__('%s Required Amount %s Give here a message that will show if form doesn\'t provide an amount to pay.', 'gravitylovesflutterwave'),
			'<strong>', '</strong>'
		);
		return $tooltips;
	}
	public function gform_form_settings_fields($fields, $form) {
		$fields[$this->id] = [
			'title'  => esc_html__( 'Flutterwave Payment', 'gravitylovesflutterwave' ),
			'fields' => [
				[
					'name'    => 'enableFlutterwave',
					'type'    => 'toggle',
					'label'   => esc_html__( 'Enable flutterwave Payment', 'gravitylovesflutterwave' ),
					'tooltip' => gform_tooltip( 'form_flutterwave', '', true )
				],
				[
					'name'    => 'comissionType',
					'type'    => 'radio',
					'label'   => esc_html__( 'Comission type', 'gravitylovesflutterwave' ),
					'tooltip' => gform_tooltip( 'form_comissiontype', '', true ),
					'choices' => [
						['label' => esc_html__( 'Percentage', 'gravityforms' ), 'value' => 'percentage'],
						['label' => esc_html__( 'Flat amount', 'gravityforms' ), 'value' => 'flatamount'],
					],
					'dependency' => [
						'live'   => true,
						'fields' => [
							['field' => 'enableFlutterwave'],
						],
					],
				],
				[
					'name'    => 'percentageAmount',
					'type'    => 'text',
					'label'   => esc_html__( 'Comission Percent', 'gravitylovesflutterwave' ),
					'tooltip' => gform_tooltip( 'form_comissionpercent', '', true ),
					'dependency' => [
						'live'   => true,
						'fields' => [
							['field' => 'enableFlutterwave'],
							['field' => 'comissionType', 'values' => ['percentage']]
						],
					],
				],
				[
					'name'    => 'flatrateAmount',
					'type'    => 'text',
					'label'   => esc_html__( 'Flat amount', 'gravitylovesflutterwave' ),
					'tooltip' => gform_tooltip( 'form_comissionflat', '', true ),
					'dependency' => [
						'live'   => true,
						'fields' => [
							['field' => 'enableFlutterwave'],
							['field' => 'comissionType', 'values' => ['flatamount']]
						],
					],
				],
				[
					'name'    => 'subAccounts',
					'type'    => 'select',
					'label'   => esc_html__( 'Sub Accounts', 'gravitylovesflutterwave' ),
					'tooltip' => gform_tooltip( 'form_subaccounts', '', true ),
					'choices' => $this->gforms_sub_accounts(),
					// 'multiple' => true,
					'dependency' => [
						'live'   => true,
						'fields' => [
							['field' => 'enableFlutterwave'],
						],
					],
				],
				[
					'name'    => 'submitBtnText',
					'type'    => 'text',
					'label'   => esc_html__( 'Submit text', 'gravitylovesflutterwave' ),
					'tooltip' => gform_tooltip( 'form_submittext', '', true ),
					'default_value' => 'Submit',
					'dependency' => [
						'live'   => true,
						'fields' => [
							['field' => 'enableFlutterwave']
						],
					],
				],
			],
		];


		
		// Error Messages
		$fields[$this->id]['fields'][] = [
			'name'       => 'requireAmountMessage',
			'type'       => 'textarea',
			'label'      => esc_html__( 'Require Amount Message', 'gravitylovesflutterwave' ),
			'tooltip'    => gform_tooltip( 'form_require_amount_message', '', true ),
			'allow_html' => true,
			'default'	 => isset($this->settings['amountZeroMsg'])?$this->settings['amountZeroMsg']:'',
			'dependency' => [
				'live'   => true,
				'fields' => [
					['field' => 'enableFlutterwave']
				],
			],
		];
		// print_r([$fields, $form]);wp_die();
		return $fields;
	}
	public function gforms_sub_accounts() {
		// $subaccounts = [];
		// for ($i=1; $i <= 6; $i++) {
		// 	$subaccounts[] = ['name' => 'account '.$i, 'label' => esc_html__( 'Sub account '.$i, 'gravityforms' ), 'value' => 'flatamount'];
		// }
		$subaccounts = (array) apply_filters('gravityformsflutterwaveaddons/project/payment/getallsubaccounts',[], false);
		if(isset($subaccounts)&&count($subaccounts)>=1) {
			foreach($subaccounts as $i => $subac) {
				$subaccounts[$i] = [
					'name'		=> $subac['id'],
					'value'		=> $subac['id'],
					'label'		=> "{$subac['full_name']} ({$subac['business_name']})",
				];
			}
		}
		return $subaccounts;
	}


	public function isPayable() {
		return true;
	}
	public function gform_pre_send_email($email, $message_format, $notification, $entry) {
		if(!$this->isPayable()) {return $notification;}
		 // Check the payment status of the entry
		 $payment_status = rgar($entry, 'payment_status');
		 // Disable notifications if payment status is not success
		 if ($payment_status !== 'success') {
			$notification['disableAutoformat'] = true;
			$notification['sendTo'] = '';
		 }
		 return $notification;
	}
	public function gform_entry_created($entry, $form) {
		if(!$this->isPayable()) {return $notification;}
		$this->createPayLinkandGo($entry, $form);
	}
	public function createPayLinkandGo($entry, $form) {
		global $FWPFlutterwave;
		// Perform your payment processing logic here
		// Assuming the payment is successful, update the entry status to "Pending Payment"
		$txref = 'gfrm.'.time().'.'.$entry['id'];
		
		/**
		 * 
		 */
		$formDate = $this->extract_gravityentry_fields($entry);
		
		$user_id = get_current_user_id();

		$entry['transaction_id'] = $txref;
		$entry['payment_status'] = 'pending';
		$entry['payment_date'] = null;
		$entry['payment_amount'] = rgar($entry, 'payment_amount');
		$entry['payment_method'] = '';
		$entry['is_fulfilled'] = null;
		$entry['created_by'] = $user_id;
		$entry['transaction_type'] = null;
		$entry['status'] = 'pending_payment';
		
		// Update the entry with the new status
		\GFAPI::update_entry($entry);

		$payment_amount = (isset($entry['payment_amount']) && $entry['payment_amount']!==null && (int) $entry['payment_amount'] > 0)?(int) $entry['payment_amount']:(isset($formDate['total'])?(int)$formDate['total']:1);
		$args = [
			'txref' => $txref,
            'amount' => $payment_amount,
            'currency' => isset($entry['currency'])?$entry['currency']:'USD',
            'customer_info' => [
				'email' => isset($formDate['email'])?$formDate['email']:get_bloginfo('admin_email'),
				'customer_name' => join(' ', isset($formDate['name'])?[$formDate['name']['first'], $formDate['name']['last']]:[]),
				'customer_phone' => $formDate['phone']
			],
			'subaccounts'	=> [
                // [
                //     "id" => $sub_account_id,
                //     "transaction_charge_type" => "flat_subaccount",
                //     "transaction_charge" => $sub_account_amount
                // ]
            ]
		];


		$form['subAccounts'] = explode(',', $form['subAccounts']);
		$subaccounts = (array) apply_filters('gravityformsflutterwaveaddons/project/payment/getallsubaccounts',[], false);
		if(isset($subaccounts)&&count($subaccounts)>=1) {
			foreach($subaccounts as $i => $subac) {
				if(in_array($subac['id'], $form['subAccounts'])) {
					$args['subaccounts'][] = [
						'id'						=> $subac['id'],
						'transaction_charge_type'	=> ($form['comissionType']=='percentage')?'percentage_subaccount':'flat_subaccount',
						'transaction_charge'		=> ($form['comissionType']=='percentage')?(int) $form['percentageAmount']:(int) $form['flatrateAmount']
					];
				}
			}
		}
		
		// print_r($args);wp_die();
		
		$link = $FWPFlutterwave->createPayment($args);
		if($link) {
			gform_update_meta($entry['id'], '_paymentlink', $link);
			wp_redirect($link);
		} else {
			wp_die(
				__('Can\'t create payment link. Please contact with administrative.', 'domain'),
				__('Error happening on creating payment.', 'domain')
			);exit;
		}
	}
	public function approve_entry_and_trigger_notifications($entry, $payment_result) {
		// Approve the entry
		\GFAPI::update_entry_property($entry['id'], 'is_approved', true);
		// Trigger notifications
		\GFCommon::send_notifications($entry, $form);
	}
	// Assuming you have a function to handle the payment success webhook
	public function handle_payment_success_webhook($payment_data) {
		$entry_id = $payment_data['entry_id']; // Assuming the entry ID is passed in the payment data

		// Approve the entry
		GFAPI::update_entry_property($entry_id, 'is_approved', true);

		// Get the entry object
		$entry = GFAPI::get_entry($entry_id);

		// Trigger notifications
		GFCommon::send_notifications($entry, $entry['form_id']);
	}

	
	public function gform_pre_submission($form) {
		add_filter('gform_disable_post_creation', '__return_true');
	}
	public function gform_after_submission($entry, $form) {
		$this->createPayLinkandGo($entry, $form);exit;
	}


	public function sendReminder() {
		$request = $_POST;
		check_ajax_referer('gravityformsflutterwaveaddons/project/verify/nonce', '_nonce', true);
		$args = [];
		$args['hooks'] = ['reminder-sent'];
		$args['message'] = __('Payment reminder mail sent successfully!', 'domain');
		$template = $this->replaceMagicWords(stripslashes(GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS['paymentReminder']), ['entry'=>$_POST['entry'],'form_id'=>$_POST['form_id']]);
		// $args['template'] = $template;

		if($template) {
			wp_send_json_success($args);
		} else {
			$args['message'] = __('Mail didn\'t properly sent. Entry ID or customer mail address not found!', 'domain');
			wp_send_json_error($args);
		}
	}

	public function replaceMagicWords($str, $args) {
		$user_info = get_userdata(get_current_user_id());
		$entry = (\GFAPI::entry_exists((int)$args['entry']))?\GFAPI::get_entry((int)$args['entry']):false;
		if(!$entry && !is_wp_error($entry)) {return false;}
		$form = (\GFAPI::form_id_exists((int)$args['form_id']))?\GFAPI::get_form((int)$args['form_id']):false;
		if(!$form && !is_wp_error($form)) {return false;}
		
		$formDate = $this->extract_gravityentry_fields($entry);
		
		// print_r($form);
		$replaceingData = [
			'mailImagePath' => GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_IMG_URI.'/images',
			'customFullName' => join(' ', isset($formDate['name'])?[$formDate['name']['first'], $formDate['name']['last']]:[]),
			'senderFullName' => $user_info->first_name.' '.$user_info->last_name,
			'dateMMMMdd' => wp_date('M, d'),
			'dateYYYMMDD' => wp_date('Y-m-d'),
			'invoiceNumber' => bin2hex((int)$args['entry']),
			'siteEmail' => get_bloginfo('admin_email'),
			'siteURL' => site_url(),
			'siteAddress' => '',
			'customAddressFull' => isset($formDate['address'])?$formDate['address']['street'] . '. ' . $formDate['address']['city'] . ' '.$formDate['address']['state'].' '.$formDate['address']['country']:'',
			'invoiceIssuedOn' => wp_date('M, d, H:i', strtotime($entry['date_created'])),
			'productName' => isset($formDate['product'][0])?$formDate['product'][0]:'Submission charge',
			'invoiceUnit' => isset($formDate['quantity'])?$formDate['quantity']:1,
			'invoiceTotal' => $entry['currency'].$entry['payment_amount'],
			'invoiceTax' => $entry['currency'].'0',
			'invoiceSubtotal' => $entry['currency'].$entry['payment_amount']
		];
		foreach($replaceingData as $needle => $replace) {
			$str = str_replace('{{'.$needle.'}}', $replace, $str);
		}
		if(isset($formDate['email']) && !empty($formDate['email'])) {
			wp_mail($formDate['email'], stripslashes(GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS['paymentReminderSubject']), $str, [
				'From: '.get_bloginfo().' <'.get_bloginfo('admin_email').'>',
				'Content-Type: text/html; charset=UTF-8'
			]);
		} else {
			return false;
		}
		return $str;
	}
}