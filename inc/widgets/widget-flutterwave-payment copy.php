<?php
/**
 * Theme Sidebars.
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;

class GFFlutterwavePaymentAddon extends \GFAddOn {
	use Singleton;
	protected $_version = '1.0';
	protected $_min_gravityforms_version = '2.5';
	protected $_slug = 'flutterwaveaddon';
	protected $_path = GRAVITYFORMS_FLUTTERWAVE_ADDONS__FILE__;
	protected $_full_path = GRAVITYFORMS_FLUTTERWAVE_ADDONS__FILE__;
	protected $_title = 'Gravity Forms Flutterwave Addon';
	protected $_short_title = 'Flutterwave';

	public function __construct() {
		parent::__construct();
	}
	public function init() {
        parent::init();

        if(defined('FWP_CHECK_IF_GRAVITYWOO_CALLEED')) {
            define('FWP_CHECK_IF_GRAVITYWOO_CALLEED', true);
            add_filter('gform_payment_gateways', array($this, 'add_payment_gateway'));
            add_filter('gform_addon_navigation', array($this, 'add_addon_navigation'));
            add_action('gform_entry_info', array($this, 'display_payment_status'), 10, 2);
            add_action('gform_after_submission', array($this, 'process_payment'), 10, 2);
            add_filter( 'gform_submit_button', array( $this, 'form_submit_button' ), 10, 2 );
        }
		// wp_die();
    }

    public function add_payment_gateway($gateways) {
        $gateways['flutterwave'] = array(
            'label' => 'Flutterwave',
            'feed' => true,
            'form' => $this->_slug,
            'payment_callback' => array($this, 'process_payment'),
        );

        return $gateways;
    }

    public function add_addon_navigation($menu_items) {
        $menu_items[] = array(
            'name' => $this->_slug,
            'label' => $this->_title,
            'callback' => array($this, 'addon_page'),
        );

        return $menu_items;
    }

    public function addon_page() {
        // Implement your addon settings page here
    }

    public function display_payment_status($form_id, $entry) {
        // Implement displaying payment status on the entry details page here
    }

    public function process_payment($entry, $form) {
        // Implement the payment processing logic using cURL
        // Here, you'll make API requests to Flutterwave for payment processing

        // Get the payment amount from the form entry
        $payment_amount = rgar($entry, 'payment_amount');

        // Construct the request payload for Flutterwave API
        $payload = array(
            'amount' => $payment_amount,
            'currency' => 'NGN', // Adjust based on your desired currency
            'customer' => array(
                'email' => rgar($entry, 'email'),
                'name' => rgar($entry, 'name'),
            ),
            // Add other necessary parameters for your specific use case
        );

        // Send the request to Flutterwave API using cURL
        $response = $this->send_request_to_flutterwave($payload);

        // Handle the API response and process the payment status accordingly
        if ($response && $response->status == 'success') {
            // Payment is successful
            $this->fulfill_order($entry, $response->transaction_id);
        } else {
            // Payment failed
            $this->fail_payment($entry);
        }
    }

    private function send_request_to_flutterwave($payload) {
        // Implement cURL request to Flutterwave API
        // Set up cURL options, headers, and make the API call

        // Example cURL request
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.flutterwave.com/v3/payments',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer YOUR_FLUTTERWAVE_SECRET_KEY',
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_message = curl_error($curl);
            // Handle cURL error
        }

        curl_close($curl);

        // Parse and return the response
        return json_decode($response);
    }

    private function fulfill_order($entry, $transaction_id) {
        // Update the entry or perform any necessary actions for a successful payment
    }

    private function fail_payment($entry) {
        // Handle failed payment scenario
    }


    
    
    public function scripts() {
        $scripts = array(
            array(
                'handle'  => 'my_script_js',
                'src'     => $this->get_base_url() . '/js/my_script.js',
                'version' => $this->_version,
                'deps'    => array( 'jquery' ),
                'strings' => array(
                    'first'  => esc_html__( 'First Choice', 'simpleaddon' ),
                    'second' => esc_html__( 'Second Choice', 'simpleaddon' ),
                    'third'  => esc_html__( 'Third Choice', 'simpleaddon' )
                ),
                'enqueue' => array(
                    array(
                        'admin_page' => array( 'form_settings' ),
                        'tab'        => 'simpleaddon'
                    )
                )
            ),
 
        );
 
        return array_merge( parent::scripts(), $scripts );
    }
 
    public function styles() {
        $styles = array(
            array(
                'handle'  => 'my_styles_css',
                'src'     => $this->get_base_url() . '/css/my_styles.css',
                'version' => $this->_version,
                'enqueue' => array(
                    array( 'field_types' => array( 'poll' ) )
                )
            )
        );
 
        return array_merge( parent::styles(), $styles );
    }
 
    function form_submit_button( $button, $form ) {
        $settings = $this->get_form_settings( $form );
        if ( isset( $settings['enabled'] ) && true == $settings['enabled'] ) {
            $text   = $this->get_plugin_setting( 'mytextbox' );
            $button = "</pre>
                <div>{$text}</div>
                <pre>" . $button;
        }
 
        return $button;
    }
 
    public function plugin_page() {
        echo 'This page appears in the Forms menu';
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
 
    public function settings_my_custom_field_type( $field, $echo = true ) {
        echo '</pre>
        <div>' . esc_html__( 'My custom field contains a few settings:', 'simpleaddon' ) . '</div>
        <pre>';
 
        // get the text field settings from the main field and then render the text field
        $text_field = $field['args']['text'];
        $this->settings_text( $text_field );
 
        // get the checkbox field settings from the main field and then render the checkbox field
        $checkbox_field = $field['args']['checkbox'];
        $this->settings_checkbox( $checkbox_field );
    }
 
    public function is_valid_setting( $value ) {
        return strlen( $value ) > 5;
    }
}
