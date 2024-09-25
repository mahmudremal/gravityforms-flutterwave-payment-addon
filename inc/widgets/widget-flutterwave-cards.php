<?php
/**
 * Theme Sidebars.
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;

class Widget_Flutterwave_Cards extends \GF_Field {
    public $type = 'flutterwave_credit_card';
    public $label = 'Flutterwave';
    public $description = ''; // 'Secure payments though Flutterwave payment gateway.';

    // public function init_admin() {
	// 	parent::init_admin();
    //     add_action('gform_field_standard_settings', [$this, 'gform_field_standard_settings'], 10, 2);
    // }

    public function get_form_editor_field_title() {
        return esc_attr__('FlutterWave', 'gravitylovesflutterwave');
    }

    public function get_form_editor_field_icon() {
        $icon = file_get_contents(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_PATH . '/icons/money-business-and-finance-svgrepo-com.svg');
        $icon = str_replace(['width="800px"', 'height="800px"'], ['width="20px"', 'height="20px"'], $icon);
        return $icon;
	}
    public function get_form_editor_field_description() {
        return '';
		return esc_attr__( 'Secure payments though Flutterwave payment gateway.', 'gravitylovesflutterwave' );
	}

    public function get_form_editor_field_settings() {
        return [
            'conditional_logic_field_setting',
			'force_ssl_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			// 'rules_setting',
			'description_setting',
			'css_class_setting',
			'enable_multiple_payment_methods_setting',
			'sub_labels_setting',
			'sub_label_placement_setting',
			'input_placeholders_setting',
			'field_flutterwave_settings',
        ];
    }

    public function get_form_editor_button() {
        return [
            'group' => 'pricing_fields',
            'text'  => $this->get_form_editor_field_title()
        ];
    }

    public function get_field_input($form, $value = '', $entry = null) {
        // Render the credit card input fields here
        do_action('gflutter/project/assets/register_styles');
        do_action('gflutter/project/assets/register_scripts');
        wp_enqueue_style('GravityformsFlutterwaveAddons');wp_enqueue_script('forge');
        wp_enqueue_script('imask');wp_enqueue_script('checkout-flutterwave');
        wp_enqueue_script('GravityformsFlutterwaveAddons');

        // unset($form['fields']);
        // print_r($this);
        // print_r([ 
        //     $this,
        //     $form, 
        //     $value, 
        //     $entry, $this->id
        //  ]);wp_die();
        ob_start();
        ?>
        <div class="">
            <?php if($this->enableCardPaymentMethod): ?>
                <!-- <label class="form-label"><?php esc_html_e('Payment Method', 'gravitylovesflutterwave'); ?></label> -->
                <div class="form-check">
                    <input class="form-check-input flutterwave_method" type="radio" name="input_<?php echo esc_attr($this->id).'.6'; ?>" id="flutterwave-credit" value="credit" <?php echo esc_attr(($this->flutterwaveDefaultModeCard == true)?'checked':''); ?>>
                    <label class="form-check-label" for="flutterwave-credit"><?php esc_html_e('Credit Card', 'gravitylovesflutterwave'); ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input flutterwave_method" type="radio" name="input_<?php echo esc_attr($this->id).'.6'; ?>" id="flutterwave-checkout" value="checkout" <?php echo esc_attr(($this->flutterwaveDefaultModeCard == true)?'':'checked'); ?>>
                    <label class="form-check-label" for="flutterwave-checkout"><?php esc_html_e('Flutterwave', 'gravitylovesflutterwave'); ?></label>
                </div>
            <?php endif; ?>
        </div>
        <?php if($this->enableCardPaymentMethod): ?>
            <div class="flutterwaves_credit_card" style="<?php echo esc_attr(($this->flutterwaveDefaultModeCard == true)?'display: flex;':'display: none;'); ?>">
                <div class="creditcard_container preload <?php echo esc_attr(($this->enablePreviewField)?'':'d-none'); ?>">
                    <div class="creditcard">
                        <div class="front">
                            <div id="ccsingle"></div>
                            <?php
                                $icon = GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_PATH . '/icons/sample-credit-card-front.svg';
                                $icon = (file_exists($icon)&&!is_dir($icon))?file_get_contents($icon):'<svg id="cardfront"></svg>';
                                echo $icon;
                            ?>
                        </div>
                        <div class="back">
                            <?php
                                $icon = GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_PATH . '/icons/sample-credit-card-back.svg';
                                $icon = (file_exists($icon)&&!is_dir($icon))?file_get_contents($icon):'<svg id="cardback"></svg>';
                                echo $icon;
                            ?>
                        </div>
                    </div>
                </div>
                <div class="form-container">
                    <div class="field-container">
                        <label for="name"><?php esc_html_e('Name', 'gravitylovesflutterwave'); ?></label>
                        <input id="name" maxlength="20" type="text" name="input_<?php echo esc_attr($this->id).'.5'; ?>" data-name="fullname" required>
                    </div>
                    <div class="field-container">
                        <label for="cardnumber"><?php esc_html_e('Card Number', 'gravitylovesflutterwave'); ?></label>
                        <span id="generatecard" class="<?php echo esc_attr(GRAVITYFORMS_FLUTTERWAVE_ADDONS_TEST_MODE?'':'d-none'); ?>"><?php esc_html_e('generate random', 'gravitylovesflutterwave'); ?></span>
                        <input id="cardnumber" type="text" name="input_<?php echo esc_attr($this->id).'.1'; ?>" pattern="[0-9]*" inputmode="numeric" data-name="card_number" required>
                        <svg id="ccicon" class="ccicon" width="750" height="471" viewBox="0 0 750 471" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"></svg>
                    </div>
                    <div class="field-container">
                        <label for="expirationdate"><?php esc_html_e('Expiration (mm/yy)', 'gravitylovesflutterwave'); ?></label>
                        <input id="expirationdate" type="text" name="input_<?php echo esc_attr($this->id).'.2_month'; ?>" pattern="[0-9]*" inputmode="numeric" data-name="expire" required>
                    </div>
                    <div class="field-container">
                        <label for="securitycode"><?php esc_html_e('Security Code', 'gravitylovesflutterwave'); ?></label>
                        <input id="securitycode" type="text" name="input_<?php echo esc_attr($this->id).'.3'; ?>" pattern="[0-9]*" inputmode="numeric" data-name="cvv" required>
                    </div>
                    <div class="field-container my-3">
                        <input type="hidden" name="input_<?php echo esc_attr($this->id).'.7'; ?>" data-name="unique" value="">
                        <button id="submitFlutterCard" type="button">
                            <?php echo esc_html(empty($this->submitBtnText)?__('Continue to pay', 'gravitylovesflutterwave'):$this->submitBtnText); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="flutterwaves_live_card" style="<?php echo esc_attr(($this->flutterwaveDefaultModeCard == true)?(
            ($this->enableCardPaymentMethod == true)?'display: none;':'display: flex;'
        ):'display: flex;'); ?>">
            <div class="card p-2">
                <p>
                    <?php
                    $message = empty($this->fluttercardMessage)?__('You will be redirected to a secure payment page.', 'gravitylovesflutterwave'):$this->fluttercardMessage;
                    echo esc_html(($this->enableCardPaymentMethod)?$message:$message); ?>
                </p>
            </div>
        </div>
        <?php
        $output = ob_get_clean();
        return $output;
    }
    
    public function validate($value, $form) {
        // Retrieve the field ID of the Flutterwave Payment field
        $field_id = 0;
        foreach($form['fields'] as $i => $field) {
            if($field['type']=='flutterwave_credit_card') {
                $field_id = $field['id'];
            }
        }
        $flutterwaveFieldId = $field_id; // Replace with the actual field ID
    
        // Check if the Flutterwave Payment field is present in the form
        if (isset($form['fields'][$flutterwaveFieldId])) {
            // Retrieve the submitted value of the Flutterwave Payment field
            $flutterwaveFieldValue = rgpost("input_{$flutterwaveFieldId}");
    
            // Check if the Flutterwave Payment field is empty
            if (rgblank($flutterwaveFieldValue)) {
                // Set the validation message for the Flutterwave Payment field
                $form['fields'][$flutterwaveFieldId]->failed_validation = true;
                $form['fields'][$flutterwaveFieldId]->validation_message = esc_html__('Flutterwave Payment: This field is required. Please fix it.', 'gravitylovesflutterwave');
            } else {
                // Retrieve the selected payment method
                $flutterwaveMethod = rgpost('flutterwave_method');
    
                // If Flutterwave method is selected, mark the field as valid
                if ($flutterwaveMethod === 'checkout') {
                    $form['fields'][$flutterwaveFieldId]->is_valid = true;
                }
            }
        }
    
        return $form;
    }
    public function is_value_submission_valid($value, $form) {
        $is_valid = true;
        $validation_message = '';

        // Validate the field value based on field settings
        $field_setting = $this->get_field_setting('field_setting');
        if (!empty($field_setting) && $value === $field_setting) {
            $is_valid = false;
            $validation_message = 'Field value cannot be the same as the field setting.';
        }
        return $is_valid ? true : ['is_valid' => false, 'message' => $validation_message];
    }
    public function get_form_editor_field_settings_js() {
        ?>
        <script type="text/javascript">
            (function($) {
                // Add custom fields when field_flutterwave_settings is clicked
                $(document).on('click', '#field_flutterwave_settings', function() {
                    // Clear existing fields
                    $('.custom-setting-fields').remove();
                    // Add custom fields
                    $('#field_settings').append('<div class="custom-setting-fields">' +
                        '<label for="custom_field_1">Custom Field 1:</label>' +
                        '<input type="text" id="custom_field_1" name="custom_field_1" />' +
                        '<label for="custom_field_2">Custom Field 2:</label>' +
                        '<input type="text" id="custom_field_2" name="custom_field_2" />' +
                        '</div>');
                });
            })(jQuery);
        </script>
        <?php
    }
    
    

    
    


    public function plugin_settings_fields() {
        return array(
            array(
                'title'  => esc_html__( 'Simple Add-On Settings', 'simpleaddon' ),
                'fields' => array(
                    array(
                        'name'              => 'mytextbox',
                        'tooltip'           => esc_html__( 'This is the tooltip', 'simpleaddon' ),
                        'label'             => esc_html__( 'This is the label', 'simpleaddon' ),
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
                'title'  => esc_html__( 'Simple Form Settings', 'simpleaddon' ),
                'fields' => array(
                    array(
                        'label'   => esc_html__( 'My checkbox', 'simpleaddon' ),
                        'type'    => 'checkbox',
                        'name'    => 'enabled',
                        'tooltip' => esc_html__( 'This is the tooltip', 'simpleaddon' ),
                        'choices' => array(
                            array(
                                'label' => esc_html__( 'Enabled', 'simpleaddon' ),
                                'name'  => 'enabled',
                            ),
                        ),
                    ),
                    array(
                        'label'   => esc_html__( 'My checkboxes', 'simpleaddon' ),
                        'type'    => 'checkbox',
                        'name'    => 'checkboxgroup',
                        'tooltip' => esc_html__( 'This is the tooltip', 'simpleaddon' ),
                        'choices' => array(
                            array(
                                'label' => esc_html__( 'First Choice', 'simpleaddon' ),
                                'name'  => 'first',
                            ),
                            array(
                                'label' => esc_html__( 'Second Choice', 'simpleaddon' ),
                                'name'  => 'second',
                            ),
                            array(
                                'label' => esc_html__( 'Third Choice', 'simpleaddon' ),
                                'name'  => 'third',
                            ),
                        ),
                    ),
                    array(
                        'label'   => esc_html__( 'My Radio Buttons', 'simpleaddon' ),
                        'type'    => 'radio',
                        'name'    => 'myradiogroup',
                        'tooltip' => esc_html__( 'This is the tooltip', 'simpleaddon' ),
                        'choices' => array(
                            array(
                                'label' => esc_html__( 'First Choice', 'simpleaddon' ),
                            ),
                            array(
                                'label' => esc_html__( 'Second Choice', 'simpleaddon' ),
                            ),
                            array(
                                'label' => esc_html__( 'Third Choice', 'simpleaddon' ),
                            ),
                        ),
                    ),
                    array(
                        'label'      => esc_html__( 'My Horizontal Radio Buttons', 'simpleaddon' ),
                        'type'       => 'radio',
                        'horizontal' => true,
                        'name'       => 'myradiogrouph',
                        'tooltip'    => esc_html__( 'This is the tooltip', 'simpleaddon' ),
                        'choices'    => array(
                            array(
                                'label' => esc_html__( 'First Choice', 'simpleaddon' ),
                            ),
                            array(
                                'label' => esc_html__( 'Second Choice', 'simpleaddon' ),
                            ),
                            array(
                                'label' => esc_html__( 'Third Choice', 'simpleaddon' ),
                            ),
                        ),
                    ),
                    array(
                        'label'   => esc_html__( 'My Dropdown', 'simpleaddon' ),
                        'type'    => 'select',
                        'name'    => 'mydropdown',
                        'tooltip' => esc_html__( 'This is the tooltip', 'simpleaddon' ),
                        'choices' => array(
                            array(
                                'label' => esc_html__( 'First Choice', 'simpleaddon' ),
                                'value' => 'first',
                            ),
                            array(
                                'label' => esc_html__( 'Second Choice', 'simpleaddon' ),
                                'value' => 'second',
                            ),
                            array(
                                'label' => esc_html__( 'Third Choice', 'simpleaddon' ),
                                'value' => 'third',
                            ),
                        ),
                    ),
                    array(
                        'label'             => esc_html__( 'My Text Box', 'simpleaddon' ),
                        'type'              => 'text',
                        'name'              => 'mytext',
                        'tooltip'           => esc_html__( 'This is the tooltip', 'simpleaddon' ),
                        'class'             => 'medium',
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    ),
                    array(
                        'label'   => esc_html__( 'My Text Area', 'simpleaddon' ),
                        'type'    => 'textarea',
                        'name'    => 'mytextarea',
                        'tooltip' => esc_html__( 'This is the tooltip', 'simpleaddon' ),
                        'class'   => 'medium merge-tag-support mt-position-right',
                    ),
                    array(
                        'label' => esc_html__( 'My Hidden Field', 'simpleaddon' ),
                        'type'  => 'hidden',
                        'name'  => 'myhidden',
                    ),
                    array(
                        'label' => esc_html__( 'My Custom Field', 'simpleaddon' ),
                        'type'  => 'my_custom_field_type',
                        'name'  => 'my_custom_field',
                        'args'  => array(
                            'text'     => array(
                                'label'         => esc_html__( 'A textbox sub-field', 'simpleaddon' ),
                                'name'          => 'subtext',
                                'default_value' => 'change me',
                            ),
                            'checkbox' => array(
                                'label'   => esc_html__( 'A checkbox sub-field', 'simpleaddon' ),
                                'name'    => 'my_custom_field_check',
                                'choices' => array(
                                    array(
                                        'label'         => esc_html__( 'Activate', 'simpleaddon' ),
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
    public function is_flutterwave_enabled() {
        $settings = GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS;
        return (isset($settings['secretkey']) && $settings['secretkey'] !== '');
    }

    public function gform_field_standard_settings($placement, $form_id) {
        // if($placement !== 1415) {return;}
		?>
		<li class="enable_multiple_payment_methods_setting field_setting">
			<label for="rules" class="section_label">
				<?php esc_html_e( 'Payment Methods', 'gravitylovesflutterwave' ); ?>
				<?php gform_tooltip( 'form_field_enable_card_payment_method' ); ?>
			</label>
			<?php
			if($this->is_flutterwave_enabled()) {
				?>
                <div>
                    <input type="checkbox" id="field_enable_card_payment_method" data-js="enable_multiple_payment_methods" onclick="SetFieldProperty('enableMultiplePaymentMethods', this.checked);" onkeypress="SetFieldProperty('enableMultiplePaymentMethods', this.checked);" />
                    <label for="field_enable_card_payment_method" class="inline">
                        <?php esc_html_e( 'Enable multiple payment methods', 'gravitylovesflutterwave' ); ?>
                        <?php gform_tooltip( 'form_field_enable_card_payment_method' ); ?>
                    </label>
                    <!-- <div id="field_multiple_payment_methods_description">
                        <?php
                        // translators: variables are the markup to generate a link.
                        // sprintf( esc_html__( 'Available payment methods can be configured in your %1$sFlutterwave Dashboard%2$s.', 'gravitylovesflutterwave' ), '<a href="https://dashboard.flutterwave.com/" target="_blank">', '</a>' );
                        ?>
                    </div> -->
                </div>
                <br>
                <div id="link_email_field_container">
                    <label for="link_email_field" class="section_label">
                        <?php esc_html_e( 'Link Email Field', 'gravitylovesflutterwave' ); ?>
                    </label>
                    <select id="link_email_field" name="link_email_field" class="inline">
                        <?php
                        $form = GFAPI::get_form( $form_id );
                        foreach ( $form['fields'] as $field ) {
                            if ( $field->type === 'email' ) {
                                ?>
                                    <option value="<?php echo esc_attr( $field->id ); ?>" <?php selected( $field->id, 1 ); ?>>
                                    <?php echo esc_html( $field->label . ' - Field ID:' . $field->id ); ?>
                                    </option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    <div>
                        <?php
                        // translators: variables are the markup to generate a link.
                        // sprintf( esc_html__( 'Link is a payment method that enables your customers to save their payment information so they can use it again on any site that uses Stripe Link. %1$sLearn more about Link%2$s.', 'gravitylovesflutterwave' ), '<a href="https://stripe.com/docs/payments/link" target="_blank">', '</a>' );
                        ?>
                    </div>
                </div>
			    <?php
            } else {
                ?>
				<div>
					<?php
					// translators: variables are the markup to generate a link.
					printf( esc_html__( 'This option is disabled because Flutterwave Secret keys not provided on settings or the key expired ot your account is in live mode but the secret key you provided is in test mode. %1$sDo a reCheck over Flutterwave dashboard%2$s.', 'gravitylovesflutterwave' ), '<a href="https://dashboard.flutterwave.com/" target="_blank">', '</a>' );
					?>
				</div>
			<?php } ?>
		</li>
		<?php
    }
}
