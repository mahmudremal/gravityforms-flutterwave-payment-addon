<?php
/**
 * Theme Sidebars.
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
class GF_FlutterWave_Credit_Card_Field extends \GF_Field {
    public $type = 'flutterwave_credit_card';

    public function get_form_editor_field_title() {
        return esc_attr__('FlutterWave', 'gravitylovesflutterwave');
    }

    public function get_form_editor_field_icon() {
		// return 'gform-icon--cog';
        return file_get_contents(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_PATH . '/icons/money-business-and-finance-svgrepo-com.svg');
	}
    public function get_form_editor_field_description() {
		return esc_attr__( 'Secure payments though Flutterwave payment gateway.', 'domain' );
	}
    public function get_form_editor_inline_script_on_page_render() {
		$multiple_payment_methods_enabled = ('gf_stripe()->is_payment_element_supported()') ? 'true' : 'false';

		$js = sprintf( "function SetDefaultValues_%s(field) {field.label = '%s'; field.enableMultiplePaymentMethods = %s;
		field.linkEmailFieldId = '';
		field.inputs = [new Input(field.id + '.1', %s), new Input(field.id + '.4', %s), new Input(field.id + '.5', %s)];
		}", $this->type, esc_html__( 'Credit Card', 'gravityformsstripe' ), $multiple_payment_methods_enabled, json_encode( gf_apply_filters( array( 'gform_card_details', rgget( 'id' ) ), esc_html__( 'Card Details', 'gravityformsstripe' ), rgget( 'id' ) ) ), json_encode( gf_apply_filters( array( 'gform_card_type', rgget( 'id' ) ), esc_html__( 'Card Type', 'gravityformsstripe' ), rgget( 'id' ) ) ), json_encode( gf_apply_filters( array( 'gform_card_name', rgget( 'id' ) ), esc_html__( 'Cardholder Name', 'gravityformsstripe' ), rgget( 'id' ) ) ) ) . PHP_EOL;

		$js .= /** @lang JavaScript */ "
			gform.addFilter('gform_form_editor_can_field_be_added', function(result, type) {
				if (type === 'stripe_creditcard') {
				    if (GetFieldsByType(['stripe_creditcard']).length > 0) {" .
				        sprintf( "alert(%s);", json_encode( esc_html__( 'Only one Stripe Card field can be added to the form', 'gravityformsstripe' ) ) )
				       . " result = false;
					}
				}
				
				return result;
			});
		";

		$js .= /** @lang JavaScript */ "
			jQuery(document).bind('gform_load_field_settings', function(event, field, form) {
				var activeToggle = 'active1.png',
					inactiveToggle = 'active0.png',
					imagesUrl, input, isHidden, title, img;

				if ( field['type'] !=='stripe_creditcard' ) {
					return;
				}

				imagesUrl = '" . GFCommon::get_base_url() . '/images/' . "';
				input = field['inputs'][2];
				isHidden = typeof input.isHidden != 'undefined' && input.isHidden ? true : false;
				title = isHidden ? " . json_encode( esc_html__( 'Inactive', 'gravityforms' ) ) . ':' . json_encode( esc_html__( 'Active', 'gravityforms' ) ) . ";
				img = isHidden ? inactiveToggle : activeToggle;

				jQuery('.sub_labels_setting .field_custom_inputs_ui tr:eq(0)').prepend('<td><strong>" . esc_html__( 'Show', 'gravityforms' ) . "</strong></td>');
				jQuery('.sub_labels_setting .field_custom_inputs_ui tr:eq(1)').prepend('<td></td>');
				jQuery('.sub_labels_setting .field_custom_inputs_ui tr:eq(2)').prepend('<td><img data-input_id=\'' + field['id'] + '.5\' alt=\'' + title + '\' class=\'input_active_icon cardholder_name\' src=\'' + imagesUrl + img + '\'/></td>');
				jQuery('.input_placeholders tr:eq(1)').remove();
				jQuery( '.sub_labels_setting .field_custom_inputs_ui tr:eq(2) td:eq(2) input').prop('disabled', isHidden);

				jQuery('.sub_labels_setting').on('click keypress', '.input_active_icon.cardholder_name', function( e ) {
					e.stopImmediatePropagation();
					this.src = isHidden ? this.src.replace(inactiveToggle, activeToggle) : this.src.replace(activeToggle, inactiveToggle);
					
					jQuery('.sub_labels_setting .field_custom_inputs_ui tr:eq(2) td:eq(2) input').prop('disabled', !isHidden);
					
					SetInputHidden( !isHidden, input.id );
					
					// Toggle the state of isHidden for the next click or keypress.
					isHidden = !isHidden;
		        });
			});
		";

		$js .= /** @lang JavaScript */ "
			gform.addAction('gform_post_load_field_settings', function ([field, form]) {
				if ( field['type'] === 'stripe_creditcard' ) {	        
					// Hide #field_settings when the field has error conditions.
					// This is called right after the settings are shown. So that makes it feel like there's no settings.
					if ( jQuery('.gform_stripe_card_error').length ) {
						HideSettings( 'field_settings' );
					}
				}
			});";

		return $js;
	}


    public function get_form_editor_field_settings() {
        return [
            'conditional_logic_field_setting',
			'force_ssl_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'rules_setting',
			'description_setting',
			'css_class_setting',
			'enable_multiple_payment_methods_setting',
			'sub_labels_setting',
			'sub_label_placement_setting',
			'input_placeholders_setting',
        ];
    }

    public function get_form_editor_button() {
        return [
            'group' => 'pricing_fields', //  | advanced_fields
            'text'  => $this->get_form_editor_field_title()
        ];
    }
    // public function is_value_submission_empty($form_id) {
	// 	// If payment element is used, validation already happened in the front end, and the field has no value now.
    //     global $fwpGravityforms;
	// 	if(!$fwpGravityforms->isPayable(false, \GFAPI::get_form($form_id))) {return false;}
	// 	// check only the cardholder name.
	// 	$cardholder_name_input = \GFFormsModel::get_input( $this, $this->id . '.5' );
	// 	$hide_cardholder_name  = rgar( $cardholder_name_input, 'isHidden' );
	// 	$cardholder_name       = rgpost( 'input_' . $this->id . '_5' );
	// 	if ( ! $hide_cardholder_name && empty( $cardholder_name ) ) {
	// 		return true;
	// 	}
	// 	return false;
	// }
    // public function get_value_submission( $field_values, $get_from_post_global_var = true ) {
	// 	if ( $get_from_post_global_var ) {
	// 		$value[ $this->id . '.1' ] = $this->get_input_value_submission( 'input_' . $this->id . '_1', rgar( $this->inputs[0], 'name' ), $field_values, true );
	// 		$value[ $this->id . '.4' ] = $this->get_input_value_submission( 'input_' . $this->id . '_4', rgar( $this->inputs[1], 'name' ), $field_values, true );
	// 		$value[ $this->id . '.5' ] = $this->get_input_value_submission( 'input_' . $this->id . '_5', rgar( $this->inputs[2], 'name' ), $field_values, true );
	// 	} else {
	// 		$value = $this->get_input_value_submission( 'input_' . $this->id, $this->inputName, $field_values, $get_from_post_global_var );
	// 	}

	// 	return $value;
	// }

    
	public function get_field_input__( $form, $value = array(), $entry = null ) {
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();
		$is_admin        = $is_entry_detail || $is_form_editor;

		$form_id  = $form['id'];
		$id       = intval( $this->id );
		$field_id = $is_entry_detail || $is_form_editor || $form_id === 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$disabled_text = $is_form_editor ? "disabled='disabled'" : '';
		$class_suffix  = $is_entry_detail ? '_admin' : '';

		$form_sub_label_placement  = rgar( $form, 'subLabelPlacement' );
		$field_sub_label_placement = $this->subLabelPlacement;
		$is_sub_label_above        = $field_sub_label_placement === 'above' || ( empty( $field_sub_label_placement ) && $form_sub_label_placement === 'above' );
		$sub_label_class_attribute = $field_sub_label_placement === 'hidden_label' ? " class='hidden_sub_label screen-reader-text'" : " class='gform-field-label gform-field-label--type-sub'";

		$card_details_input     = GFFormsModel::get_input( $this, $this->id . '.1' );
		$card_details_sub_label = rgar( $card_details_input, 'customLabel' ) !== '' ? $card_details_input['customLabel'] : esc_html__( 'Card Details', 'gravityformsstripe' );
		$card_details_sub_label = gf_apply_filters( array( 'gform_card_details', $form_id, $this->id ), $card_details_sub_label, $form_id );

		$cardholder_name_input       = GFFormsModel::get_input( $this, $this->id . '.5' );
		$hide_cardholder_name        = rgar( $cardholder_name_input, 'isHidden' );
		$cardholder_name_sub_label   = rgar( $cardholder_name_input, 'customLabel' ) !== '' ? $cardholder_name_input['customLabel'] : esc_html__( 'Cardholder Name', 'gravityformsstripe' );
		$cardholder_name_sub_label   = gf_apply_filters( array( 'gform_card_name', $form_id, $this->id ), $cardholder_name_sub_label, $form_id );
		$cardholder_name_placeholder = $this->get_input_placeholder_attribute( $cardholder_name_input );

		if ( $cardholder_name_placeholder ) {
			$cardholder_name_placeholder = ' ' . $cardholder_name_placeholder;
		}

		// Prepare the values for checking the Stripe Card field error.
		$api_key        = gf_stripe()->get_publishable_api_key();
		$no_stripe_feed = ! gf_stripe()->has_feed( $form_id );

		// If we are in the form editor, display a placeholder field.
		if ( $is_admin ) {
			$validation_check = $this->admin_field_validation_check( $api_key, $no_stripe_feed, $form_id );
			if ( true !== $validation_check ) {
				return $validation_check;
			}

			return $this->get_admin_card_field( $field_id, $is_sub_label_above, $card_details_sub_label, $sub_label_class_attribute, $cardholder_name_placeholder, $hide_cardholder_name, $cardholder_name_sub_label, $class_suffix );
		}

		$cardholder_name = '';
		if ( ! empty( $value ) ) {
			$cardholder_name = esc_attr( rgget( $this->id . '.5', $value ) );
		}

		$card_error = '';

		// Display the no API connection error.
		if ( empty( $api_key ) ) {
			$card_error           = $this->get_card_error_message( $this->get_api_error_message() );
			$hide_cardholder_name = true;
		} elseif ( gf_stripe()->is_stripe_checkout_enabled() ) {
			// Display the Stripe Checkout error.
			/* translators: 1. Open div tag 2. Close div tag */
			$stripe_checkout_enabled_error = esc_html__( '%1$sThe Stripe Card field cannot work when the Payment Collection Method is set to Stripe Payment Form (Stripe Checkout).%2$s' );
			$card_error                    = $this->get_card_error_message( $stripe_checkout_enabled_error );
			$hide_cardholder_name          = true;
		} elseif ( $no_stripe_feed ) {
			// Display the no Stripe feed error.
			/* translators: 1. Open div tag 2. Close div tag */
			$no_stripe_feed_error = esc_html__( '%1$sPlease check if you have activated a Stripe feed for your form.%2$s' );
			$card_error           = $this->get_card_error_message( $no_stripe_feed_error );
			$hide_cardholder_name = true;
		} elseif ( $this->enableMultiplePaymentMethods && gf_stripe()->is_stripe_connect_enabled() === true ) {
			$hide_cardholder_name = true;
		}

		$cc_input = "<div class='ginput_complex{$class_suffix} ginput_container ginput_container_creditcard ginput_stripe_creditcard gform-grid-row' id='{$field_id}'>";

		$is_payment_element = ( $this->enableMultiplePaymentMethods && gf_stripe()->is_stripe_connect_enabled() === true ) ? 'true' : 'false';
		$field_control_class = $this->enableMultiplePaymentMethods ? 'StripeElement--payment-element' : 'gform-theme-field-control StripeElement--card';

		if ( $is_sub_label_above ) {

			$cc_input .= "<div class='ginput_full gform-grid-col' id='{$field_id}_1_container' data-payment-element='{$is_payment_element}'>";

			if ( ! $hide_cardholder_name ) {
				$cc_input .= "<label for='{$field_id}_1' id='{$field_id}_1_label'{$sub_label_class_attribute}>" . $card_details_sub_label . '</label>';
			}

			$cc_input .= "<div id='{$field_id}_1' class='{$field_control_class}'></div>";
			$cc_input .= $card_error;

			$cc_input .= '</div><!-- .ginput_full -->';

			if ( ! $hide_cardholder_name ) {
				$cc_input .= "<div class='ginput_full gform-grid-col' id='{$field_id}_5_container'>
					<label for='{$field_id}_5' id='{$field_id}_5_label'{$sub_label_class_attribute}>" . $cardholder_name_sub_label . "</label>
					<input type='text' name='input_{$id}.5' id='{$field_id}_5' value='{$cardholder_name}'{$cardholder_name_placeholder}>
				</div>";
			}
		} else {

			$cc_input .= "<div class='ginput_full gform-grid-col' id='{$field_id}_1_container' data-payment-element='{$is_payment_element}'>";
			$cc_input .= "<div id='{$field_id}_1' class='{$field_control_class}'></div>";
			$cc_input .= $card_error;

			if ( ! $hide_cardholder_name ) {
				$cc_input .= "<label for='{$field_id}_1' id='{$field_id}_1_label'{$sub_label_class_attribute}>" . $card_details_sub_label . '</label>';
			}

			$cc_input .= '</div><!-- .ginput_full -->';

			if ( ! $hide_cardholder_name ) {
				$cc_input .= "<div class='ginput_full gform-grid-col' id='{$field_id}_5_container'>
					<input type='text' name='input_{$id}.5' id='{$field_id}_5' value='{$cardholder_name}'{$cardholder_name_placeholder}>
					<label for='{$field_id}_5' id='{$field_id}_5_label'{$sub_label_class_attribute}>" . $cardholder_name_sub_label . '</label>
				</div>';
			}
		}

		$cc_input .= '</div><!-- .ginput_container -->';
		$cc_input .= '
			<style type="text/css">
				:root {
  					--link-login-string: "' . wp_strip_all_tags( __( 'Link login', 'gravityformsstripe' ) ) . '"
				}
			</style>';
		return $cc_input;
	}

    public function get_field_input($form, $value = '', $entry = null) {
        // Render the credit card input fields here
        do_action('gravityformsflutterwaveaddons/project/assets/register_styles');
        do_action('gravityformsflutterwaveaddons/project/assets/register_scripts');
        wp_enqueue_style('GravityformsFlutterwaveAddons');wp_enqueue_script('imask');
        wp_enqueue_script('GravityformsFlutterwaveAddons');ob_start();

        ?>
            <!-- Replace with your HTML markup for the credit card input fields -->
            <!-- <input type="text" name="credit_card_number" placeholder="Credit Card Number" />
            <input type="text" name="credit_card_expiry" placeholder="Expiry Date" />
            <input type="text" name="credit_card_cvv" placeholder="CVV" /> -->

            <!-- <script src="<?= GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_LIB_URI.'/js/imask.min.js'; ?>"></script> -->
            <div class="flutterwaves_credit_card">
                
                <!-- <div class="payment-title">
                    <h1>Payment Information</h1>
                </div> -->
                <div class="creditcard_container preload">
                    <div class="creditcard">
                        <div class="front">
                            <div id="ccsingle"></div>
                            <?php
                                $icon = GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_PATH . '/icons/sample-credit-card-front.svg';
                                $icon = (file_exists($icon)&&!is_dir($icon))?file_get_contents($icon):'<svg id="cardfront"></div>';
                            ?>
                            <?= $icon; ?>
                        </div>
                        <div class="back">
                            <?php
                                $icon = GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_PATH . '/icons/sample-credit-card-back.svg';
                                $icon = (file_exists($icon)&&!is_dir($icon))?file_get_contents($icon):'<svg id="cardback"></div>';
                            ?>
                            <?= $icon; ?>
                        </div>
                    </div>
                </div>
                <div class="form-container">
                    <div class="field-container">
                        <label for="name">Name</label>
                        <input id="name" maxlength="20" type="text" name="flutterwavecreditcard.name">
                    </div>
                    <div class="field-container">
                        <label for="cardnumber">Card Number</label>
                        <span id="generatecard">generate random</span>
                        <input id="cardnumber" type="text" name="flutterwavecreditcard.cardnumber" pattern="[0-9]*" inputmode="numeric">
                        <svg id="ccicon" class="ccicon" width="750" height="471" viewBox="0 0 750 471" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"></svg>
                    </div>
                    <div class="field-container">
                        <label for="expirationdate">Expiration (mm/yy)</label>
                        <input id="expirationdate" type="text" name="flutterwavecreditcard.expirationdate" pattern="[0-9]*" inputmode="numeric">
                    </div>
                    <div class="field-container">
                        <label for="securitycode">Security Code</label>
                        <input id="securitycode" type="text" name="flutterwavecreditcard.securitycode" pattern="[0-9]*" inputmode="numeric">
                    </div>
                </div>


            </div>
        <?php
        $output = ob_get_clean();
        return $output;
    }


    public function plugin_settings_fields() {
        return array(
            array(
                'title'  => esc_html__('Flutterwave Settings', 'gravitylovesflutterwave'),
                'fields' => array(
                    array(
                        'name'              => 'mytextbox',
                        'tooltip'           => esc_html__('This is the tooltip', 'gravitylovesflutterwave'),
                        'label'             => esc_html__('This is the label', 'gravitylovesflutterwave'),
                        'type'              => 'text',
                        'class'             => 'small',
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    )
                )
            )
        );
    }
 
    public function form_settings_fields( $form ) {
        return array(
            array(
                'title'  => esc_html__('Flutterwave Settings', 'gravitylovesflutterwave'),
                'fields' => array(
                    array(
                        'label'   => esc_html__('My checkbox', 'gravitylovesflutterwave'),
                        'type'    => 'checkbox',
                        'name'    => 'enabled',
                        'tooltip' => esc_html__('This is the tooltip', 'gravitylovesflutterwave'),
                        'choices' => array(
                            array(
                                'label' => esc_html__('Enabled', 'gravitylovesflutterwave'),
                                'name'  => 'enabled',
                            ),
                        ),
                    ),
                    array(
                        'label'   => esc_html__('My checkboxes', 'gravitylovesflutterwave'),
                        'type'    => 'checkbox',
                        'name'    => 'checkboxgroup',
                        'tooltip' => esc_html__('This is the tooltip', 'gravitylovesflutterwave'),
                        'choices' => array(
                            array(
                                'label' => esc_html__('First Choice', 'gravitylovesflutterwave'),
                                'name'  => 'first',
                            ),
                            array(
                                'label' => esc_html__('Second Choice', 'gravitylovesflutterwave'),
                                'name'  => 'second',
                            ),
                            array(
                                'label' => esc_html__('Third Choice', 'gravitylovesflutterwave'),
                                'name'  => 'third',
                            ),
                        ),
                    ),
                    array(
                        'label'   => esc_html__('My Radio Buttons', 'gravitylovesflutterwave'),
                        'type'    => 'radio',
                        'name'    => 'myradiogroup',
                        'tooltip' => esc_html__('This is the tooltip', 'gravitylovesflutterwave'),
                        'choices' => array(
                            array(
                                'label' => esc_html__('First Choice', 'gravitylovesflutterwave'),
                            ),
                            array(
                                'label' => esc_html__('Second Choice', 'gravitylovesflutterwave'),
                            ),
                            array(
                                'label' => esc_html__('Third Choice', 'gravitylovesflutterwave'),
                            ),
                        ),
                    ),
                    array(
                        'label'      => esc_html__('My Horizontal Radio Buttons', 'gravitylovesflutterwave'),
                        'type'       => 'radio',
                        'horizontal' => true,
                        'name'       => 'myradiogrouph',
                        'tooltip'    => esc_html__('This is the tooltip', 'gravitylovesflutterwave'),
                        'choices'    => array(
                            array(
                                'label' => esc_html__('First Choice', 'gravitylovesflutterwave'),
                            ),
                            array(
                                'label' => esc_html__('Second Choice', 'gravitylovesflutterwave'),
                            ),
                            array(
                                'label' => esc_html__('Third Choice', 'gravitylovesflutterwave'),
                            ),
                        ),
                    ),
                    array(
                        'label'   => esc_html__('My Dropdown', 'gravitylovesflutterwave'),
                        'type'    => 'select',
                        'name'    => 'mydropdown',
                        'tooltip' => esc_html__('This is the tooltip', 'gravitylovesflutterwave'),
                        'choices' => array(
                            array(
                                'label' => esc_html__('First Choice', 'gravitylovesflutterwave'),
                                'value' => 'first',
                            ),
                            array(
                                'label' => esc_html__('Second Choice', 'gravitylovesflutterwave'),
                                'value' => 'second',
                            ),
                            array(
                                'label' => esc_html__('Third Choice', 'gravitylovesflutterwave'),
                                'value' => 'third',
                            ),
                        ),
                    ),
                    array(
                        'label'             => esc_html__('My Text Box', 'gravitylovesflutterwave'),
                        'type'              => 'text',
                        'name'              => 'mytext',
                        'tooltip'           => esc_html__('This is the tooltip', 'gravitylovesflutterwave'),
                        'class'             => 'medium',
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    ),
                    array(
                        'label'   => esc_html__('My Text Area', 'gravitylovesflutterwave'),
                        'type'    => 'textarea',
                        'name'    => 'mytextarea',
                        'tooltip' => esc_html__('This is the tooltip', 'gravitylovesflutterwave'),
                        'class'   => 'medium merge-tag-support mt-position-right',
                    ),
                    array(
                        'label' => esc_html__('My Hidden Field', 'gravitylovesflutterwave'),
                        'type'  => 'hidden',
                        'name'  => 'myhidden',
                    ),
                    array(
                        'label' => esc_html__('My Custom Field', 'gravitylovesflutterwave'),
                        'type'  => 'my_custom_field_type',
                        'name'  => 'my_custom_field',
                        'args'  => array(
                            'text'     => array(
                                'label'         => esc_html__('A textbox sub-field', 'gravitylovesflutterwave'),
                                'name'          => 'subtext',
                                'default_value' => 'change me',
                            ),
                            'checkbox' => array(
                                'label'   => esc_html__('A checkbox sub-field', 'gravitylovesflutterwave'),
                                'name'    => 'my_custom_field_check',
                                'choices' => array(
                                    array(
                                        'label'         => esc_html__('Activate', 'gravitylovesflutterwave'),
                                        'name'          => 'subcheck',
                                        'default_value' => true,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
