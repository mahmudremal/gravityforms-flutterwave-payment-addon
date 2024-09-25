<?php
/**
 * Flutterwave payment Addon.
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;

// require_once('C:/Users/Lenovo/Local Sites/testbed/app/public/wp-content/plugins/gravityforms/includes/addon/class-gf-payment-addon.php');
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

    protected $_supports_callbacks = true;
    protected $_capabilities = ['gravityforms_flutterwave', 'gravityforms_flutterwave_uninstall'];
    protected $_capabilities_settings_page = 'gravityforms_flutterwave';
    protected $_capabilities_form_settings = 'gravityforms_flutterwave';
    protected $_capabilities_uninstall = 'gravityforms_flutterwave_uninstall';
    protected $_enable_rg_autoupgrade = true;
    protected $_requires_credit_card = false;

    protected $_corn_called = 0;
    
	public function __construct() {
		parent::__construct();
	}
    public function pre_init() {
        parent::pre_init();
        add_filter('gform_currencies', [$this, 'supported_currencies'], 10, 1);
		// For form confirmation redirection, this must be called in `wp`,
		// or confirmation redirect to a page would throw PHP fatal error.
		// Run before calling parent method. We don't want to run anything else before displaying thank you page.
		add_action('wp', [$this, 'maybe_thankyou_page'], 5);

        add_filter('gform_settings_menu', [$this, 'gform_settings_menu'], 10, 1);
		add_action('gform_settings_flutterwaveaddons', [$this, 'gform_settings_flutterwaveaddons'], 10, 0);
        
        add_action('wp_ajax_gflutter/project/payment/updatelink', [$this, 'payment_link_update'], 10, 0);

        add_action('wp_ajax_gflutter/project/payment/get_token', [$this, 'flutterwave_get_public_key'], 10, 0);
        add_action('wp_ajax_nopriv_gflutter/project/payment/get_token', [$this, 'flutterwave_get_public_key'], 10, 0);
        
        // $this->_corn_called = (int) get_option($this->get_slug() . '-corn-called-times', 0);
        // if ($this->_corn_called > 10) {
        //     wp_die('Corn job called more then 10 times');
        // }
    }

	public function init() {
        parent::init();
        add_filter('gform_submit_button', array($this, 'add_payment_button'), 10, 2);
    }
    
    // ------- Inline Entry operation -------
    public function entry_post_save($entry, $form) {
		if ( ! $this->is_payment_gateway ) {
			return $entry;
		}
		// Saving which gateway was used to process this entry.
		gform_update_meta($entry['id'], 'payment_gateway', $this->get_slug());
		// 
        $transaction_id = rgar($entry, 'transaction_id');
        if (empty($transaction_id) && isset($_POST['transaction_id'])) {
            $transaction_id = $_POST['transaction_id'];
            $entry['is_fulfilled'] = '1';
            $entry['payment_status'] = 'Paid';
            $entry['transaction_type'] = 'payment';
            $entry['transaction_id'] = $transaction_id;
            $entry['payment_method'] = $this->get_slug();
            $entry['payment_date'] = gmdate('y-m-d H:i:s');
            if (empty(rgar($entry, 'payment_amount')) && isset($_POST['payment_amount'])) {
                $entry['payment_amount'] = (float) $_POST['payment_amount'];
            }
            \GFAPI::update_entry($entry);

            $this->complete_payment($entry, [
                'amount' => rgar($entry, 'payment_amount'),
                'payment_date' => gmdate('y-m-d H:i:s'),
                'transaction_id' => $transaction_id,
                'transaction_type' => 'payment',
                'payment_status' => 'Paid',
            ]);
        }
		return parent::entry_post_save($entry, $form);
	}
    
    // -------- Validation ------------------
    public function maybe_validate($validation_result, $context = 'api-submit') {
        $is_valid = parent::maybe_validate($validation_result, $context);
        if (! $is_valid) {
            return $is_valid;
        }
        if (!isset($_POST['transaction_id'])) {
            $is_valid = false;
        }
        return $is_valid;
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
        return parent::check_status();
        // update_option($this->get_slug() . '-corn-called-times', $this->_corn_called + 1);
	}

    // -------- WebHook ------------------
    public function callback() {
        $entry_id = $_GET['entry'];
        $entry = GFAPI::get_entry($entry_id);

        $response = wp_remote_get("https://api.flutterwave.com/v3/transactions/{$entry['transaction_id']}/verify", [
            'headers' => [
                'Authorization' => 'Bearer YOUR_SECRET_KEY',
            ],
        ]);

        if (is_wp_error($response)) {
            return;
        }

        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        $status = $response_body['data']['status'];

        if ($status == 'successful') {
            GFAPI::update_entry_property($entry['id'], 'payment_status', 'Paid');
            GFAPI::update_entry_property($entry['id'], 'is_fulfilled', 1);
        } else {
            GFAPI::update_entry_property($entry['id'], 'payment_status', 'Failed');
        }
        return parent::callback();
    }

    //--------- Submission Process ------
    /*
    public function redirect_url($feed, $submission_data, $form, $entry) {
        // 
        // If the payment_link is valid, return the payment link
        $payment_link = $this->get_flutterwave_payment_intend($feed, $submission_data, $form, $entry);
        if ($payment_link && !empty($payment_link)) {
            return esc_url($payment_link);
        }
    
        // Fallback to the parent method if the payment link is not created
        return parent::redirect_url($feed, $submission_data, $form, $entry);
    }
    */
    public function get_with_split_subaccounts($args, $feed, $submission_data, $form, $entry) {
        // 
        // if (isset($submission_data['line_items'])) {
        //     foreach ($submission_data['line_items'] as $_lineItem) {
        //         $args['line_items'] = $args['line_items']??[];
        //         $args['line_items'][] = [
        //             'name'          => $_lineItem['name'],
        //             'description'   => $_lineItem['description'],
        //             'unit_price'    => $_lineItem['unit_price'],
        //             'quantity'      => $_lineItem['quantity'],
        //         ];
        //     }
        // }
        // 
        if (isset($feed['meta']) && isset($feed['meta']['enable_splits']) && $feed['meta']['enable_splits'] && isset($feed['meta']['_split_comissions'])) {
            foreach ($feed['meta']['_split_comissions'] as $_split) {
                if (isset($_split['account']) && !empty($_split['account'])) {
                    if ($_split['type'] == 'flat_subaccount') { // fixed_amount
                        $_split['amount'] = (float) $_split['amount'] * 100;
                    } else {
                        // $_split['amount'] = (float) $_split['amount'] * 0.01 * $args['amount'];
                        $_split['amount'] = (float) $_split['amount'] * 0.01;
                    }
                    $args['subaccounts'] = $args['subaccounts']??[];
                    $args['subaccounts'][] = [
                        'id'                        => $_split['account'],
                        'transaction_charge'        => $_split['amount'],
                        'transaction_charge_type'   => $_split['type'] //  == 'fixed_amount'?'flat_subaccount':'percentage_subaccount',
                    ];
                }
            }
        }
        return $args;
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
    public function billing_info_fields() {
		$fields = [
            ['name' => 'name', 'label' => __('Name', 'gravitylovesflutterwave'), 'required' => false],
            ['name' => 'email', 'label' => __('Email', 'gravitylovesflutterwave'), 'required' => false],
			['name' => 'phone', 'label' => __('Phone', 'gravitylovesflutterwave'), 'required' => false],
			['name' => 'address', 'label' => __('Address', 'gravitylovesflutterwave'), 'required' => false],
			// ['name' => 'address2', 'label' => __('Address 2', 'gravitylovesflutterwave'), 'required' => false],
			// ['name' => 'city', 'label' => __('City', 'gravitylovesflutterwave'), 'required' => false],
			// ['name' => 'state', 'label' => __('State', 'gravitylovesflutterwave'), 'required' => false],
			// ['name' => 'zip', 'label' => __('Zip', 'gravitylovesflutterwave'), 'required' => false],
			// ['name' => 'country', 'label' => __('Country', 'gravitylovesflutterwave'), 'required' => false],
        ];

		return $fields;
	}
    public function option_choices() {
        return [];
    }
    public function other_settings_split_comissions_html() {
        global $GF_Assets;// echo esc_attr(json_encode([]));
        ?>
        <div id="settings_split_comissions" data-comissions="<?php echo esc_attr(json_encode([])); ?>" data-show-by="enable_splits" data-stored-on="_split_comissions">
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
        <?php
    }

    //--------- Payment ----------------
    
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
        global $GF_Assets;
        $scripts = [
            // [
            //     'handle'  => 'flutterwave_payment',
            //     'src'     => GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_URI . '/gformsettings.js',
            //     'version' => $GF_Assets->filemtime(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_DIR_PATH . '/gformsettings.js'),
            //     // 'src'     => GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_URI . "/templates/form_payment.js",
            //     // 'version' => \GFCommon::$version,
            //     'strings' => [
            //         'subscriptionCancelWarning' => __( "Warning! This subscription will be canceled. This cannot be undone. 'OK' to cancel subscription, 'Cancel' to stop", 'gravityforms' ),
            //         'subscriptionCancelNonce'   => wp_create_nonce( 'flutterwave_cancel_subscription' ),
            //         'subscriptionCanceled'      => __( 'Canceled', 'gravityforms' ),
            //         'subscriptionError'         => __( 'The subscription could not be canceled. Please try again later.', 'gravityforms' )
            //     ],
            //     'enqueue' => [
            //         [$this, 'frontend_script_callback']
            //         // ['admin_page' => ['form_settings'], 'tab' => $this->get_slug()],
            //         // ['admin_page' => ['entry_view']],
            //     ]
            // ]
        ];
        // 
        return array_merge( parent::scripts(), $scripts );
    }
    public function frontend_script_callback( $form ) {
        return $form && $this->has_feed($form['id']);
    }
    // 
    public function enqueue_scripts($form = '', $is_ajax = false) {
        parent::enqueue_scripts($form, $is_ajax);
        global $GF_Assets;
        if (is_admin()) {
            wp_enqueue_script('flutterwave_payment', GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_URI . '/gformsettings.js', ['jquery'], $GF_Assets->filemtime(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_DIR_PATH . '/gformsettings.js'), true);
            wp_enqueue_style('flutterwave_payment', GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_URI . '/gformsettings.css', [], $GF_Assets->filemtime(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_DIR_PATH . '/gformsettings.css'), 'all');
        } else {
            wp_enqueue_script('flutterwave_payment', GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_URI . '/gformflutterwave.js', ['jquery'], $GF_Assets->filemtime(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_DIR_PATH . '/gformflutterwave.js'), true);
            wp_enqueue_style('flutterwave_payment', GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_URI . '/gformflutterwave.css', [], $GF_Assets->filemtime(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_DIR_PATH . '/gformflutterwave.css'), 'all');

            wp_enqueue_script('flutterwave_inline_checkout', 'https://checkout.flutterwave.com/v3.js', ['jquery'], false, true);
        }
        return;
        ?>
        <!-- <script src="https://checkout.flutterwave.com/v3.js"></script>
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
        </script> -->
        <?php
    }
    // 
    public function add_payment_button($button, $form) {
        // 
        $feed = false;
        foreach ($this->get_feeds($form['id']) as $cFeed) {
            if ($cFeed['addon_slug'] == $this->get_slug()) {
                $feed = $cFeed;
                break;
            }
        }
        if (! $feed || ! $feed['is_active']) {
            return $button;
        }
        $meta = $feed['meta'];
        $args = [
            'currency' => '',
            'tx_ref' => time(),
            'form_title' => rgar($form, 'title'),
            'paymentAmount' => rgar($meta, 'paymentAmount'),
            'form_description' => rgar($form, 'description'),
            'transactionType' => rgar($meta, 'transactionType'),
            'billingInformation_name' => (string) rgar($meta, 'billingInformation_name'),
            'billingInformation_email' => (string) rgar($meta, 'billingInformation_email'),
            'billingInformation_phone' => (string) rgar($meta, 'billingInformation_phone'),
            'billingInformation_address' => (string) rgar($meta, 'billingInformation_address'),
        ];
        $args = $this->get_with_split_subaccounts($args, $feed, false, false, false);
        if (! is_numeric($args['paymentAmount']) && $args['paymentAmount'] == 'form_total') {
            $fields = $form['fields'];$payment_key = str_replace('form_', '', $args['paymentAmount']);
            foreach ($fields as $field) {
                if ($field->type == $payment_key) {
                    $args['paymentAmount'] = (string) $field->id;
                }
            }
        }
        // echo '<pre>';print_r($form);wp_die();
        // 
        ob_start();
        ?>
        <input type="submit" class="pay-flutterwave" data-form-id="<?php echo esc_attr(rgar($form, 'id')); ?>" data-token="<?php echo esc_attr(wp_create_nonce('flutterwave_get_public_key')); ?>" value="<?php echo esc_html(__('Pay with Flutterwave', 'gravitylovesflutterwave')); ?>" data-args="<?php echo esc_attr(json_encode($args)); ?>" />
        <?php
        $payment_button = ob_get_clean();
        return $payment_button;
    }

    // ---------- Common --------------
	public function gform_settings_menu($setting_tabs) {
		if (!current_user_can('manage_options')) {return $setting_tabs;}
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
    
	public function gform_settings_flutterwaveaddons() {
        global $GF_Gravityforms;
		// Check if the user has permissions to access the settings page
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		// Save settings if form is submitted
		if (isset($_POST['gform_settings_flutterwaveaddons'])) {
			// Perform validation and save the settings
			update_option('flutterwaveaddons', $_POST['flutterwaveaddons']);

			$GF_Gravityforms->changeSubaccountsPercentageonAllForms($GF_Gravityforms->settings, $_POST['flutterwaveaddons']);
			
			$GF_Gravityforms->settings = $_POST['flutterwaveaddons'];
			// Add other necessary settings update code here
			// Display a success message
			echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
		}
		// Retrieve the current settings values
		$api_key = get_option('gf_flutterwave_api_key');
		// Render the settings page HTML
		$settings = $GF_Gravityforms->settings_fields();
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
					<?php echo $GF_Gravityforms->print_settings_fields($settings['fields']); ?>
				</div>
			</fieldset>
			<div class="gform-settings-save-container">
				<button type="submit" id="gform-settings-save" name="gform-settings-save" value="save" form="gform-settings" class="primary button large">Save Settings &nbsp;→</button>
			</div>
		</form>
		<?php
	}
    
    // ----------------- Delay processing -----------------
    // public function maybe_delay_feed_processing($is_delayed, $form, $entry, $slug) {
    //     if (!$this->is_gravityforms_supported()) {
    //         return $is_delayed;
    //     }
    //     return parent::maybe_delay_feed_processing($is_delayed, $form, $entry, $slug);
    // }
    

    // ---------- Thank you page --------------
    public function maybe_thankyou_page() {
        global $GF_Flutterwave;
        if (!$this->is_gravityforms_supported()) {
            return;
        }
    
        $transaction_id = rgget('transaction_id');
        $draft_trx_id = rgget('draft_trx_id');
        $entry_id = rgget('entry_id');
        $status = rgget('status');
    
        // !$transaction_id || 
        if (!$draft_trx_id || !$entry_id || !$status) {
            return;
        }
    
        $entry = \GFAPI::get_entry($entry_id);
        if (!$entry || is_wp_error($entry)) {
            return;
        }

        if (strtolower(rgar($entry, 'payment_status')) == 'paid') {
            return;
        }
        
        $gfotm_trx_id = gform_get_meta($entry_id, 'transaction_id');
    
        // if ($gfotm_trx_id != $draft_trx_id || strpos($gfotm_trx_id, 'flutterwave_') === false) {
        //     return;
        // }
        
        $statuses = [
            'success' => ['complete', 'completed', 'successful', 'success'],
            'failed' => ['cancel', 'cancelled', 'fail', 'failed']
        ];
    
        
        // Check the payment status
        if (in_array($status, $statuses['success']) && $GF_Flutterwave->verify($transaction_id, $statuses['success'])) {
            // Payment successful - update entry to complete status
            gform_update_meta($entry_id, 'payment_status', 'Paid');
            $entry['payment_status'] = 'Paid';
            $entry['transaction_id'] = $transaction_id;
            if (!rgar($entry, 'payment_date')) {
                $entry['payment_date'] = wp_date(get_option('date_format'));
            }
            \GFAPI::update_entry($entry);
    
            // Complete payment process
            $this->complete_payment($entry, [
                'amount' => rgar($entry, 'payment_amount'),
                'payment_date' => gmdate('y-m-d H:i:s'),
                'transaction_id' => $transaction_id,
                'transaction_type' => 'payment',
                'payment_status' => 'Paid',
            ]);
    
            // Trigger confirmation or redirection
            if (!class_exists('GFFormDisplay')) {
                require_once(\GFCommon::get_base_path() . '/form_display.php');
            }
    
            $form_id = $entry['form_id'];
            $form = \GFAPI::get_form($form_id);
            $confirmation = \GFFormDisplay::handle_confirmation($form, $entry, false);
    
            if (is_array($confirmation) && isset($confirmation['redirect'])) {
                wp_redirect($confirmation['redirect']);
                exit;
            } else {
                \GFFormDisplay::$submission[$form_id] = [
                    'is_confirmation' => true,
                    'confirmation_message' => $confirmation,
                    'form' => $form,
                    'lead' => $entry,
                ];
                if (!is_array($confirmation)) {
                    echo $confirmation;
                }
            }
        } else if (in_array($status, $statuses['failed'])) {
            $this->fail_payment($entry, [
                'is_fulfilled' => '1',
                'payment_date' => gmdate('y-m-d H:i:s'),
                'payment_status' => 'Failed',
            ]);
            // if (is_user_logged_in() && get_current_user_id() != rgar($entry, 'created_by')) {
            //     return;
            // }
            // if (!is_user_logged_in() && rgar($entry, 'created_by') !== false) {
            //     return;
            // }
            // \GFAPI::delete_entry($entry_id);
        } else {}
    }


    // Ajax Requests
    public function payment_link_update() {
        global $GF_Flutterwave;$args = [];
        check_ajax_referer('gflutter/project/verify/nonce', '_nonce', true);
        if (!$this->is_gravityforms_supported()) {
            $args['message'] = __('Gravityform currently not supporting this feed.', 'gravitylovesflutterwave');
			wp_send_json_error($args);
        }
        // 
        $entry_id = $_POST['entry'];
        $entry = \GFAPI::get_entry($entry_id);
        if ($entry && !is_wp_error($entry)) {
            $form_id = $entry['form_id'];
            $form = \GFAPI::get_form($form_id);
            if ($form && !is_wp_error($form)) {
                $payment_link = $this->get_flutterwave_payment_intend(false, false, $form, $entry);
                // 
                if ($payment_link) {
                    gform_update_meta($entry_id, '_paymentlink', $payment_link);
                    $args['message'] = __('Payment link updated successfully!', 'gravitylovesflutterwave');
                    $args['payment_link'] = $payment_link;
                    wp_send_json_success($args);
                }
            }
        }
        $args['message'] = __('Something went wrong. We can\'t update payment link.', 'gravitylovesflutterwave');
        wp_send_json_error($args);
    }
    public function flutterwave_get_public_key() {
        global $GF_Flutterwave;
        if (!wp_verify_nonce($_POST['token'], 'flutterwave_get_public_key')) {
            wp_send_json_error(__('Public key not found!', 'gravitylovesflutterwave'));
        }
        $token = $GF_Flutterwave->settings['publickey'];
        if (!$token || empty($token)) {
            wp_send_json_error(__('Invalid token found!', 'gravitylovesflutterwave'));
        }
        // 
        wp_send_json_success(['token' => base64_encode($token)]);
    }

    public function get_flutterwave_payment_intend($feed, $submission_data, $form, $entry) {
        global $GF_Flutterwave;

        if (! $submission_data && $entry) {
            $submission_data = $entry;
        }
        
        if (! $feed) {
            foreach ($this->get_feeds($form['id']) as $cFeed) {
                if ($cFeed['addon_slug'] == $this->get_slug()) {
                    $feed = $cFeed;
                    break;
                }
            }
            if (! $feed || ! $feed['is_active']) {
                return false;
            }
        }
        $meta = $feed['meta'];
        
        if ($feed['is_active']) {
            // Generate a unique transaction ID if not already present
            $transaction_id = rgar($entry, 'transaction_id');
            if (empty($transaction_id)) {
                $transaction_id = uniqid('flutterwave_', true);
            }
            // Define the redirect URL after payment
            $redirect_url = add_query_arg(
                array(
                    'entry_id' => $entry['id'],
                    'draft_trx_id' => $transaction_id
                ),
                site_url()
            );
            // 
            // $submission_data
            // 
            // Process payment data | Mark the entry as pending payment
            $payment_amount = (float) rgar($submission_data, 'payment_amount');
            $entry['payment_amount'] = $payment_amount;
            $entry['transaction_id'] = $transaction_id;
            $entry['status'] = 'draft';
            $entry['payment_status'] = 'pending';
            // \GFAPI::update_entry($entry);

            // gravityform entry status could be
            //  Authorized , Paid , Processing , Pending , Active , Expired , Failed , Cancelled , Approved , Reversed , Refunded , Voided , or a custom value set by a third-party add-on.

            // Define the payment arguments
            $args = [
                'txref' => $transaction_id,
                'currency' => rgar($entry, 'currency'),
                'amount' => $payment_amount,
                'redirect_url' => $redirect_url,
                'PBFPubKey' => $GF_Flutterwave->settings['publickey'],
                'customer_info' => [
                    'email' => rgar($submission_data, 'email'),
                    'customer_name' => rgar($submission_data, 'name'),
                    'customer_phone' => rgar($submission_data, 'phone'),
                ]
            ];
        
            $args = $this->get_with_split_subaccounts($args, $feed, $submission_data, $form, $entry);
            $eSubmission = $this->get_entry_submissions($entry, $feed['meta']);
            // 
            $args['customer_info'] = [
                'email' => rgar($eSubmission, 'email'),
                'address' => rgar($eSubmission, 'address'),
                'customer_name' => rgar($eSubmission, 'name'),
                'customer_phone' => rgar($eSubmission, 'phone'),
            ];
            $args['amount'] = rgar($eSubmission, 'amount');
            // 
            // Create the payment link using Flutterwave API
            // echo '<pre>';print_r($args);wp_die();
            $payment_link = $GF_Flutterwave->createPayment($args);
            if ($payment_link && !is_wp_error($payment_link)) {
                return $payment_link;
            }
        }
        return false;
    }

    public function get_entry_submissions($entry, $meta) {
        $submission = [];$form = \GFAPI::get_form($entry['form_id']);
        $fieldsToMap = [
            'amount' => (string) $meta['paymentAmount'],
            'name' => (string) $meta['billingInformation_name'],
            'email' => (string) $meta['billingInformation_email'],
            'phone' => (string) $meta['billingInformation_phone'],
            'address' => (string) $meta['billingInformation_address'],
        ];
        foreach ($fieldsToMap as $f_key => $f_val) {$submission[$f_key] = [];}
        $fieldsToMap_keys = array_keys($fieldsToMap);
        $fieldsToMap_values = array_values($fieldsToMap);

        // 
        if (! is_numeric($fieldsToMap['amount']) && $fieldsToMap['amount'] == 'form_total') {
            $fields = $form['fields'];$payment_key = str_replace('form_', '', $fieldsToMap['amount']);
            foreach ($fields as $field) {
                if ($field->type == $payment_key) {
                    $fieldsToMap['amount'] = (string) $field->id;
                }
            }
        }
        // Iterate over each entry
        foreach ($entry as $key => $value) {
            // Explode entry key to handle field indexes like "1.3"
            $keyParts = explode('.', $key);

            // Continue if key doesn't match any of our mapped fields
            if (!is_numeric($keyParts[0]) || !in_array($keyParts[0], $fieldsToMap)) {
                continue;
            }

            // Iterate through all field mappings to handle multiple fields with the same entry key prefix
            foreach ($fieldsToMap as $fieldKey => $fieldPrefix) {
                if ($fieldPrefix === $keyParts[0]) {
                    if (empty(trim($value))) {continue;}
                    $submission[$fieldKey][] = $value;
                }
            }
        }
        foreach ($submission as $sKey => $sValue) {
            switch ($sKey) {
                case 'email':
                    $submission[$sKey] = $sValue[0]??'';
                    break;
                default:
                    $submission[$sKey] = trim(implode(' ', $sValue));
            }
        }
        return $submission;
    }
    
}



