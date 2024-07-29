<?php
/**
 * Theme Sidebars.
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;

require_once('C:/Users/Lenovo/Local Sites/testbed/app/public/wp-content/plugins/gravityforms/includes/addon/class-gf-payment-addon.php');
// GFAddOn

class GFFlutterwavePaymentAddon extends \GFPaymentAddOn {
	use Singleton;
	protected $_version = '1.0';
	protected $_min_gravityforms_version = '2.5';
	protected $_slug = 'flutterwaveaddon';
	protected $_path = GRAVITYFORMS_FLUTTERWAVE_ADDONS__FILE__;
	protected $_full_path = GRAVITYFORMS_FLUTTERWAVE_ADDONS__FILE__;
	protected $_title = 'Gravity Forms Flutterwave Addon';
	protected $_short_title = 'Flutterwave';

    protected $is_payment_gateway = false;
    protected $_single_feed_submission = true;
    protected $_requires_smallest_unit = true;

    protected $_corn_called = 0;
    
	public function __construct() {
		parent::__construct();
	}
    public function pre_init() {
        parent::pre_init();
        add_filter('gform_currencies', [$this, 'supported_currencies'], 10, 1);
        $this->_corn_called = (int) get_option($this->get_slug() . '-corn-called-times', 0);
        if ($this->_corn_called > 10) {
            wp_die('Corn job called more then 10 times');
        }
    }
	public function init() {
        parent::init();
        // add_action('wp_footer', array($this, 'enqueue_scripts'));
        // add_filter('gform_submit_button', array($this, 'add_payment_button'), 10, 2);
        // print_r([$this->get_slug()]);
        // wp_die('A little fox jumps over the lazy dog');
    }
    
    //-------- Currency ----------------------
    public function supported_currencies( $currencies ) {
        $currencies = parent::supported_currencies( $currencies );
        $currencies['NGN'] = [
			'name'               => esc_html__('Nigerian Naira', 'gravitylovesflutterwave'),
			'symbol_left'        => '&#8358;',
			'symbol_right'       => '',
			'symbol_padding'     => ' ',
			'thousand_separator' => ',',
			'decimal_separator'  => '.',
			'decimals'           => 2,
			'code'               => 'NGN'
		];
		return $currencies;
	}

    // -------- Cron --------------------
	public function check_status() {
        update_option($this->get_slug() . '-corn-called-times', $this->_corn_called + 1);
	}

    // -------- WebHook ------------------
    public function callback() {
        return parent::callback();
	}

    //--------- Submission Process ------
    public function redirect_url__backup( $feed, $submission_data, $form, $entry ) {
        global $GF_Flutterwave;
        $transaction_id = rgar($entry, 'transaction_id');
        if (empty($transaction_id)) {
            $transaction_id = $entry['transaction_id'] = 'gfrm.'. time() .'.'. rgar($entry, 'id');
        }
        // 
        $args = [
            'txref'             => $transaction_id,
            'currency'          => rgar($entry, 'currency'),
            'amount'            => (float) rgar($submission_data, 'payment_amount'),
            'redirect_url'      => site_url('/payment/flutterwave/'. $transaction_id .'/status/'),
            'PBFPubKey'         => '', // $this->settings['publickey'],
            'customer_info'         => [
                'email'             => rgar($submission_data, 'email'),
                // 'customer_email' => rgar($submission_data, 'email'),
				'customer_name'     => rgar($submission_data, 'name'),
				'customer_phone'    => ''
            ]
        ];
        if (isset($feed['meta']) && isset($feed['meta']['enable_splits']) && $feed['meta']['enable_splits'] && isset($feed['meta']['_split_comissions']) && is_array($feed['meta']['_split_comissions']) && !empty($feed['meta']['_split_comissions'])) {
            foreach ($feed['meta']['_split_comissions'] as $_split) {
                if ($_split['type'] == 'flat_subaccount') { // fixed_amount
                    $_split['amount'] = (float) $_split['amount'] * 100;
                } else {
                    // $_split['amount'] = (float) $_split['amount'] * 0.01 * $args['amount'];
                    $_split['amount'] = (float) $_split['amount'] * 0.01;
                }
                $_subacc2split = [
                    'id'						=> $_split['account'],
					'transaction_charge'		=> $_split['amount'],
					'transaction_charge_type'	=> $_split['type'] //  == 'fixed_amount'?'flat_subaccount':'percentage_subaccount',
                ];
                $args['subaccounts'][] = $_subacc2split;
            }
        } 
        $result = $GF_Flutterwave->createPayment($args);
        // 
        if ($result && !empty($result)) {
            return esc_url($result);
        }
        return parent::redirect_url( $feed, $submission_data, $form, $entry );
	}
    public function redirect_url( $feed, $submission_data, $form, $entry ) {
        global $GF_Flutterwave;
    
        // Generate a unique transaction ID if not already present
        $transaction_id = rgar($entry, 'transaction_id');
        if (empty($transaction_id)) {
            $transaction_id = uniqid('flutterwave_', true);
            // Save the transaction ID in the entry meta
            gform_update_meta($entry['id'], 'transaction_id', $transaction_id);
        }
    
        // Define the redirect URL after payment
        // $redirect_url = add_query_arg(
        //     array(
        //         'entry_id' => $entry['id'],
        //         'transaction_id' => $transaction_id
        //     ),
        //     $this->get_return_url( $form['id'], $entry['id'] )
        // );
    
        // Define the payment arguments
        $args = [
            'txref'             => $transaction_id,
            'currency'          => rgar($entry, 'currency'),
            'amount'            => (float) rgar($submission_data, 'payment_amount'),
            // 'redirect_url'      => $redirect_url,
            'PBFPubKey'         => '', // $this->get_plugin_setting('publickey'),
            'customer_info'     => [
                'email'             => rgar($submission_data, 'email'),
                'customer_name'     => rgar($submission_data, 'name'),
                'customer_phone'    => ''
            ]
        ];
    
        // Create the payment link using Flutterwave API
        $result = $GF_Flutterwave->createPayment($args);
    
        // If the result is valid, return the payment link
        if ($result && !empty($result)) {
            return esc_url($result);
        }
    
        // Fallback to the parent method if the payment link is not created
        return parent::redirect_url( $feed, $submission_data, $form, $entry );
    }


    //--------- Feed Settings ----------------
    public function other_settings_fields() {
        $other_settings = parent::other_settings_fields();
        // foreach ($other_settings as $index => $_row) {
        //     if ($_row['name'] == 'options') {
        //         unset($other_settings[$index]);
        //     }
        // }
        $other_settings[] = [
            'name'    => 'enable_splits',
            'type'    => 'toggle',
            'label'   => __('Split Comissions', 'gravitylovesflutterwave'),
            // 'onclick' => 'ToggleConditionalLogic( false, "feed_condition" );',
            'tooltip'   => '<h6>' . esc_html(__('Split Comission amounts', 'gravitylovesflutterwave')) . '</h6>' . esc_html(__('Split comissions to your partners and stuffs on real time. you can add multiple subaccounts and subaccount will count and limit by total payable amount accordingly and if exceed, then rest of the subaccounts will not be counted.', 'gravitylovesflutterwave')),
        ];
        $other_settings[] = [
            'name'  => '_split_comissions',
			'type'  => 'hidden'
        ];
        $other_settings[] = [
            'name'  => 'split_comissions_html',
            'class' => 'remal-split split-comissions',
			'value' => 'remal_conditional_logic',

			'type'  => 'html',
            'html' => [$this, 'other_settings_split_comissions_html']
        ];
        return $other_settings;
    }
    public function option_choices() {
        return [];
    }
    public function other_settings_split_comissions_html() {
        // echo esc_attr(json_encode([]));
        ?>
        <div id="settings_split_comissions" data-comissions="[]" data-show-by="enable_splits" data-stored-on="_split_comissions">
            <?php echo esc_html(__('Please wait for javascript execution to complete', 'gravitylovesflutterwave')); ?>
        </div>
        <style>
            #settings_split_comissions.hide-minus button.gform-st-icon--circle-minus{display: none;}
            #settings_split_comissions {gap: 20px;display: none;margin: 20px 0;flex-direction: column;}
            .split_subaccount_rule {gap: 10px;display: flex;flex-wrap: nowrap;align-items: center;width: 100%;}
            @media screen and (max-width: 600px) {
                #settings_split_comissions .split_subaccount_rule {gap: 5px;flex-direction: column;border: 1px solid #eee;padding: 10px;}
                #settings_split_comissions .split_subaccount_rule > *:not(button) {width: 100%;}
            }
        </style>
        <script src="<?php echo esc_url(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_URI . '/templates/form_admin.js'); ?>"></script>
        <?php
    }
    
    //--------- Assebling Subaccounts ------
    public function get_sub_accounts_id($feed, $submission_data, $form, $entry) {
        global $GF_Gravityforms;
        try {
            $subaccounts = $this->getSubAccountsData($feed, $submission_data, $form, $entry);
			$getAllSubAccounts = $FWPFlutterwave->getAllSubAccounts();
			foreach($subaccounts as $i => $account) {
				$subaccounts[$i]['id'] = $GF_Gravityforms->search4ID($subaccounts[$i]['id'], $getAllSubAccounts);
			}
            return $subaccounts;
		} catch (\Exception $e) {
            $this->log_debug( __METHOD__ . '(): Failed to execute function getting subaccounts.' );
        }
        return [];
    }
    public function getSubAccountsData($feed, $submission_data, $form, $entry) {
        return [];
    }

    //--------- Scripts ------
    public function scripts() {
        $scripts = [
            [
                'handle'  => 'flutterwave_payment',
                'src'     => GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_URI . "/templates/form_payment.js",
                'version' => \GFCommon::$version,
                'strings' => [
                    'subscriptionCancelWarning' => __( "Warning! This subscription will be canceled. This cannot be undone. 'OK' to cancel subscription, 'Cancel' to stop", 'gravityforms' ),
                    'subscriptionCancelNonce'   => wp_create_nonce( 'flutterwave_cancel_subscription' ),
                    'subscriptionCanceled'      => __( 'Canceled', 'gravityforms' ),
                    'subscriptionError'         => __( 'The subscription could not be canceled. Please try again later.', 'gravityforms' )
                ],
                'enqueue' => [
                    // ['admin_page' => ['form_settings'], 'tab' => $this->get_slug()],
                    // ['admin_page' => ['entry_view']],
                ]
            ]
        ];
        // 
        return array_merge( parent::scripts(), $scripts );
    }
    // 
    public function enqueue_scripts($form = '', $is_ajax = false) {
        parent::enqueue_scripts($form, $is_ajax);
        ?>
        <script src="https://checkout.flutterwave.com/v3.js"></script>
        <script>
            function makePayment() {
                FlutterwaveCheckout({
                    public_key: "FLWPUBK_TEST-SANDBOXDEMOKEY-X",
                    tx_ref: "titanic-48981487343MDI0NzMx",
                    amount: 54600,
                    currency: "NGN",
                    payment_options: "card, mobilemoneyghana, ussd",
                    callback: function(payment) {
                        // Send AJAX verification request to backend
                        verifyTransactionOnBackend(payment.id);
                    },
                    onclose: function(incomplete) {
                        if (incomplete || window.verified === false) {
                            document.querySelector("#payment-failed").style.display = 'block';
                        } else {
                            document.querySelector("form").style.display = 'none';
                            if (window.verified == true) {
                                document.querySelector("#payment-success").style.display = 'block';
                            } else {
                                document.querySelector("#payment-pending").style.display = 'block';
                            }
                        }
                    },
                    meta: {
                        consumer_id: 23,
                        consumer_mac: "92a3-912ba-1192a",
                    },
                    customer: {
                        email: "rose@unsinkableship.com",
                        phone_number: "08102909304",
                        name: "Rose DeWitt Bukater",
                    },
                    customizations: {
                        title: "The Titanic Store",
                        description: "Payment for an awesome cruise",
                        logo: "https://www.logolynx.com/images/logolynx/22/2239ca38f5505fbfce7e55bbc0604386.jpeg",
                    },
                });
            }

            function verifyTransactionOnBackend(transactionId) {
                // Let's just pretend the request was successful
                setTimeout(function() {
                    window.verified = true;
                }, 200);
            }
        </script>
        <?php
    }
    // 
    public function add_payment_button($button, $form) {
        $payment_button = '<button type="button" onclick="makePayment()">Pay with Flutterwave</button>';
        return $button . $payment_button;
    }

}
