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
        return __('FlutterWave Credit Card', 'gravitylovesflutterwave');
    }

    public function get_form_editor_field_settings() {
        return [
            'label_setting',
            'admin_label_setting',
            'visibility_setting',
            'css_class_setting',
            'size_setting'
        ];
    }

    public function get_form_editor_button() {
        return [
            'group' => 'advanced_fields',
            'text'  => $this->get_form_editor_field_title()
        ];
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
}
