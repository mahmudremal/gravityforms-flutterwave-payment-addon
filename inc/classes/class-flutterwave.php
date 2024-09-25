<?php
/**
 * LoadmorePosts
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
use \WP_Query;
class Flutterwave {
	use Singleton;
	private $theTable;
	private $productID;
	public $settings;
	private $lastResult;
	private $userInfo;
	private $successUrl;
	private $cancelUrl;
	private $public_key;
	private $api_key;
	private $encryptionKey;
    private $base_url = 'https://api.flutterwave.com/v3';
    private $is_test_mode;
	// 
	protected function __construct() {
        $this->settings = GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS;
		$this->api_key  = isset($this->settings['secretkey'])?$this->settings['secretkey']:false;
		$this->public_key  = isset($this->settings['publickey'])?$this->settings['publickey']:false;
		$this->encryptionKey  = isset($this->settings['encryptionkey'])?$this->settings['encryptionkey']:false;
        $this->is_test_mode = GRAVITYFORMS_FLUTTERWAVE_ADDONS_TEST_MODE;
        global $FWPFlutterwave;$FWPFlutterwave = $this;

		add_action( 'init', [ $this, 'setup_hooks' ], 1, 0 );

        add_filter('gflutter/project/payment/getallsubaccounts', [$this, 'getAllSubAccounts'], 10, 0);
	}

    public function isTest() {
        if (strripos($this->api_key, '_TEST-') !== false) {
            return true;
        }
        return $this->is_test_mode;
    }
    
	public function setup_hooks() {
		global $wpdb;$this->theTable				= $wpdb->prefix . 'fwp_flutterwave_subscriptions';
		$this->productID							= 'prod_NJlPpW2S6i75vM';
		$this->lastResult							= false;$this->userInfo = false;
		$this->successUrl							= site_url( 'payment/flutterwave/{CHECKOUT_SESSION_ID}/success' );
		$this->cancelUrl							= site_url( 'payment/flutterwave/{CHECKOUT_SESSION_ID}/cancel' );
		
		
		add_filter('gflutter/project/rewrite/rules', [ $this, 'rewriteRules' ], 10, 1);
		add_filter('query_vars', [ $this, 'query_vars' ], 10, 1);
		add_filter('template_include', [ $this, 'template_include' ], 10, 1);
		// add_filter('gflutter/project/payment/stripe/handle/status', [$this, 'handleStatus'], 10, 3);
		add_filter('gflutter/project/payment/flutterwave/verify', [$this, 'verify'], 10, 2);

        // Add Flutterwave gateway to available payment gateways in WooCommerce
        // add_filter('woocommerce_payment_gateways', [$this, 'add_flutterwave_gateway']);
        // Step 2: Display Flutterwave Payment Option on Checkout Page
        // add_filter('woocommerce_available_payment_gateways', [$this, 'add_flutterwave_payment_option']);
        // Step 3: WooCommerce Settings Page Integration
        // Add a new section to the WooCommerce settings page
        add_filter('woocommerce_settings_tabs_array', [$this, 'add_flutterwave_settings_tab'], 50);
        // Add settings fields to the Flutterwave settings tab
        add_action('woocommerce_settings_tabs_flutterwave', [$this, 'output_flutterwave_settings']);

	}
	public function getToken() {
        // Check if a token is already stored in the database or cache
        $token = $this->getStoredToken();

        // If no token is stored or token is expired, generate or refresh a new token
        if (!$token || $this->isTokenExpired($token)) {
            $token = $this->generateToken();
        }

        return $token;
    }
    private function getStoredToken() {
        // Retrieve the stored token from the database or cache
        // Replace with your own implementation based on your storage mechanism
        $stored_token = null;
		$stored_token = get_option('flutterwave_last_token', false );
		$this->last_stored = $stored_token['time'];
		$stored_token = $stored_token['token'];
        // Retrieve the token and return it
        return $stored_token;
    }
    private function isTokenExpired($token) {
        // Check if the token has expired
        // Replace with your own implementation based on token expiration logic
        $expired = (strtotime('+24 hours', $this->last_stored) >= time());
        // Perform the expiration check and return the result
        return $expired;
    }
    private function generateToken() {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "{$this->base_url}/token/create");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer {$this->api_key}"
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        print_r($response);

        if ($err) {
            // Handle error case
            return null;
        } else {
            $token = json_decode($response, true);

            // Store the token in the database or cache for future use
            $this->storeToken($token);

            return $token;
        }
    }
    private function storeToken($token) {
        // Store the token in the database or cache for future use
        // Replace with your own implementation based on your storage mechanism
		$token = ['time'=>time(), 'token'=> $token];
		update_option('flutterwave_last_token', $token, true);
    }


	public function paymentStatus($transaction_id) {
        $url = "{$this->base_url}/transactions/{$transaction_id}/verify";

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer {$this->api_key}"
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return false;
        } else {
            $payment_status = json_decode($response, true);
            // Process the payment status and return the result
            return (isset($payment_status['data']) && isset($payment_status['data']['status']))?$payment_status['data']['status']:false;
        }
    }

    public function createPayment($args) {
        $args = wp_parse_args($args, [
            'txref' => '',
            'amount' => '',
            'currency' => '',
            'redirect_url' => site_url('/payment/flutterwave/'.$args['txref'].'/status/'),
            'PBFPubKey' => $this->settings['publickey'],
            'customer_info' => [
                'email' => '',
                // 'customer_email' => '',
				'customer_name' => '',
				'customer_phone' => ''
            ]
        ]);
        $args['customer_info']['email'] = ($args['customer_info']['email'] == '')?get_bloginfo('admin_email'):$args['customer_info']['email'];

        $data = [
            'tx_ref'        => $args['txref'],
            'amount'        => $args['amount'],
            'currency'      => $args['currency'],
            'redirect_url'  => $args['redirect_url'],
            'customer'      => $args['customer_info'],
            'payment_options' => [
                'card' => '1',
                'mobile_money' => '1',
                'bank_transfer' => '1',
                'ussd' => '1',
                'qr' => '1',
                'barter' => '1',
                'bank_account' => '1',
                'credit' => '1',
                'debit' => '1',
                'transfer' => '1'
            ]
        ];
        if(isset($args['subaccounts'])) {
            $data['subaccounts'] = $args['subaccounts'];
        }

        $data_string = json_encode($data);

        // print_r($data);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "{$this->base_url}/payments");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer {$this->api_key}" // $this->getToken()
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            // Handle error case
            return null;
        } else {
            $payment_request = json_decode($response, true);
            if($payment_request['status']!=='success') {return false;}
            // Process the payment request and return the result
            return (isset($payment_request['data'])&& isset($payment_request['data']['link']))?$payment_request['data']['link']:false;
        }
    }
    public function createSplitPayment($txref, $amount, $currency, $redirect_url, $customer_info, $sub_account_id, $sub_account_amount) {
        $url = "{$this->base_url}/payments";
    
        $data = array(
            "tx_ref" => $txref,// transaction reference
            "amount" => $amount,
            "currency" => $currency,
            "redirect_url" => $redirect_url,
            "customer" => $customer_info,
            "subaccounts" => [
                [
                    "id" => $sub_account_id,
                    "transaction_charge_type" => "flat_subaccount",
                    "transaction_charge" => $sub_account_amount
                ]
            ]
        );
    
        $data_string = json_encode($data);
    
        $curl = curl_init();
    
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer {$this->api_key}"
        ));
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
    
        curl_close($curl);
    
        if ($err) {
            // Handle error case
            return null;
        } else {
            $payment_request = json_decode($response, true);

            // Process the payment request and return the result
            return $payment_request;
        }
    }
    public function processCardPayment__($args) {
        $args = wp_parse_args($args, [
            'amount'                => '',
            'currency'              => '',
            'token'                 => [],
            'customer_email'        => get_bloginfo('admin_email'),
            'redirect_url'			=> site_url('/payment/flutterwave/'.$args['tx_ref'].'/status/'),
        ]);
        $args['token'] = wp_parse_args($args['token'], [
            'card_number'      => '',
            'expiry_month'     => '',
            'expiry_year'      => '',
            'cvv'              => ''
        ]);
        $data_string = json_encode($args);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->base_url}/payments",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer {$this->api_key}"
            ],
        ));
    
        $response = curl_exec($curl);
        $error = curl_error($curl);
    
        curl_close($curl);
    
        if ($error) {
            // Handle cURL error
            return null;
        } else {
            $payment_request = json_decode($response, true);
            if($payment_request['status']!=='success') {return false;}
            // Process the payment request and return the result
            return (isset($payment_request['data'])&& isset($payment_request['data']['link']))?$payment_request['data']['link']:false;
        }
    }
    public function processCardPayment($args) {
        if(!isset($args['client']) || empty($args['client'])) {
            $args = wp_parse_args($args, [
                'tx_ref'            => '',
                'name'              => 'N/A',
                'amount'            => '',
                'currency'          => 'NGN',
                'customer_email'    => get_bloginfo('admin_email'),
                'redirect_url'      => site_url('/payment/flutterwave/'.$args['tx_ref'].'/status/'),
                
                'card_number'      => '',
                'expiry_month'     => '',
                'expiry_year'      => '',
                'cvv'              => '',
                'otp'              => '',
                'subaccounts'       => [],
            ]);
        
            $chargeData = [
                "tx_ref" => $args['tx_ref'],
                "amount" => $args['amount'],
                "currency" => $args['currency'],
                "email" => $args['customer_email'],
                "fullname" => $args['name'],

                "card_number" => $args['card_number'],
                "cvv" => $args['cvv'],
                "expiry_month" => $args['expiry_month'],
                "expiry_year" => $args['expiry_year'],

                "redirect_url" => $args['redirect_url']
            ];
            if(isset($args['subaccounts']) &&count($args['subaccounts'])>=1) {
                $chargeData['subaccounts'] = $args['subaccounts'];
            }
        }
        // Step 1: Charge the card to get the OTP prompt
        $chargeUrl = "{$this->base_url}/charges?type=card";
        $chargeHeaders = [
            "Content-Type: application/json",
            "Authorization: Bearer {$this->api_key}"
        ];
        // $chargePayload = $this->encryptPayload(json_encode($chargeData));
        $chargePayload = ['client' => $args['client']];
        $chargeCh = curl_init();
        curl_setopt($chargeCh, CURLOPT_URL, $chargeUrl);
        curl_setopt($chargeCh, CURLOPT_POST, true);
        curl_setopt($chargeCh, CURLOPT_POSTFIELDS, $chargePayload);
        curl_setopt($chargeCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chargeCh, CURLOPT_HTTPHEADER, $chargeHeaders);
        $chargeResult = curl_exec($chargeCh);
        curl_close($chargeCh);

        // print_r($chargeResult);wp_die();
        
        if(curl_errno($chargeCh)) {throw new \Exception('Communication Error: ' . curl_error($chargeCh));}
        // if(curl_getinfo($chargeCh, CURLINFO_HTTP_CODE) !== 200) {throw new \Exception($chargeResult);}
        $chargeResponse = json_decode($chargeResult, true);
        if(isset($chargeResponse['error_id'])) {throw new \Exception('Flutterwave ' . $chargeResponse['message']);}
        if($chargeResponse['status'] == 'success') {
            return $chargeResponse;
        } else {
            throw new \Exception('Something error happens while tring to issue this card.');
        }
    }
    public function processCardVerify($args) {
        // Step 2: Submit the OTP for payment authorization
        $otpUrl = "{$this->base_url}/validate-charge";
        $otpData = array(
            "otp" => $args['otp'],
            "flw_ref" => $args['flw_ref']
        );
        $otpHeaders = array(
            "Content-Type: application/json",
            "Authorization: Bearer {$this->api_key}",
        );
        $otpPayload = json_encode($otpData);
        $otpCh = curl_init();
        curl_setopt($otpCh, CURLOPT_URL, $otpUrl);
        curl_setopt($otpCh, CURLOPT_POST, true);
        curl_setopt($otpCh, CURLOPT_POSTFIELDS, $otpPayload);
        curl_setopt($otpCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($otpCh, CURLOPT_HTTPHEADER, $otpHeaders);
        $otpResult = curl_exec($otpCh);
        curl_close($otpCh);
        if (curl_errno($otpCh)) {throw new \Exception('Communication Error: ' . curl_error($otpCh));}
        // if(curl_getinfo($otpCh, CURLINFO_HTTP_CODE) !== 200) {throw new \Exception('Payment Failed: ' . $otpResult);}
        $otpResult = json_decode($otpResult, true);
        if($otpResult['status'] == 'error') {throw new \Exception($otpResult['message']);}
        // if($otpResult['status'] == 'success') {
        //     $otpUrl = "{$this->base_url}/transactions/{$otpResult['data']['transaction_id']}/verify";
        //     $otpData = ["transaction_id" => $otpResult['data']['transaction_id']];
        //     $otpPayload = json_encode($otpData);
        //     $otpCh = curl_init();
        //     curl_setopt($otpCh, CURLOPT_URL, $otpUrl);
        //     curl_setopt($otpCh, CURLOPT_POST, true);
        //     curl_setopt($otpCh, CURLOPT_POSTFIELDS, $otpPayload);
        //     curl_setopt($otpCh, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($otpCh, CURLOPT_HTTPHEADER, $otpHeaders);
        //     $otpResult = curl_exec($otpCh);
        //     curl_close($otpCh);
        // }
        return $otpResult;
    }
    public function encryptPayload($payload) {
        $iv = openssl_random_pseudo_bytes(8);
        $encryptedPayload = openssl_encrypt($payload, 'DES-EDE3', $this->encryptionKey, OPENSSL_RAW_DATA, $iv);
        $encryptedPayload = base64_encode($encryptedPayload);
        $encryptedPayload = bin2hex($iv) . $encryptedPayload;
        return $encryptedPayload;
    }
    

    public function getAllSubAccounts() {
        $url = "{$this->base_url}/subaccounts";
        if(!$this->is_test_mode) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Authorization: Bearer {$this->api_key}"
                ]
            ]);
            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);
            $result = json_decode($response, true);

            if (curl_errno($curl)) {throw new \Exception('Communication Error: '.curl_error($curl));}
            if(isset($result['status']) && $result['status']=='error') {throw new \Exception('Flutterwave '. $result['message']);}
            
            if($result && isset($result['data']) && isset($result['status']) && $result['status'] == 'success') {
                return $result['data'];
            }
            return [];
        } else {
            $error = false;
            $response = '{"status":"success","message":"Subaccounts fetched","meta":{"page_info":{"total":6,"current_page":1,"total_pages":1}},"data":[{"id":12121,"account_number":"0047826178","account_bank":"044","business_name":"Olusetire Mayowa","full_name":"OLUSETIRE JOHN OLUMAYOWA","created_at":"2020-05-18T16:39:32.000Z","meta":[{"swift_code":""}],"account_id":128989,"split_ratio":1,"split_type":"percentage","split_value":0.115,"subaccount_id":"RS_4283E678FFC8F333938A4F0D753B6DC0","bank_name":"ACCESS BANK NIGERIA","country":"NG"},{"id":6270,"account_number":"0000745342","account_bank":"058","business_name":"Association of Telecoms Companies of Nigeria","full_name":"ASS OF TELECOMM CO OF NIGERIA","created_at":"2020-02-21T14:09:21.000Z","meta":[{"swift_code":""}],"account_id":97479,"split_ratio":1,"split_type":"percentage","split_value":0.025,"subaccount_id":"RS_A66BD37EFD525CE91C5B5EF2F6404873","bank_name":"GTBANK PLC","country":"NG"},{"id":6269,"account_number":"0599948014","account_bank":"214","business_name":"Ikeja Golf Club Bar","full_name":"IKEJA GOLF CLUB","created_at":"2020-02-21T14:01:04.000Z","meta":[{"swift_code":""}],"account_id":97477,"split_ratio":1,"split_type":"percentage","split_value":0.025,"subaccount_id":"RS_8C5B213F80BFE1EF65279F2790C963FE","bank_name":"FIRST CITY MONUMENT BANK PLC","country":"NG"},{"id":6268,"account_number":"2122011891","account_bank":"050","business_name":"Ikeja Golf Club Office","full_name":"IKEJA GOLF CLUB","created_at":"2020-02-21T13:57:46.000Z","meta":[{"swift_code":""}],"account_id":97476,"split_ratio":1,"split_type":"percentage","split_value":0.025,"subaccount_id":"RS_1D3B547192398961C575B7885981553A","bank_name":"ECOBANK NIGERIA LIMITED","country":"NG"},{"id":6267,"account_number":"0007314334","account_bank":"058","business_name":"Howson Wright Estate","full_name":"HOWSON-WRIGHT EST.RESIDENT ASS","created_at":"2020-02-21T13:13:34.000Z","meta":[{"swift_code":""}],"account_id":97470,"split_ratio":1,"split_type":"percentage","split_value":0.025,"subaccount_id":"RS_21573CD8AA0F96BFFC3FECA2C04B9C2F","bank_name":"GTBANK PLC","country":"NG"},{"id":5493,"account_number":"9200181686","account_bank":"221","business_name":"DigiServe Paypoint","full_name":"OLANREWAJU PETER AJAYI","created_at":"2019-11-20T14:48:09.000Z","meta":[{"swift_code":""},{},{},{},{},{},{},{},{}],"account_id":87432,"split_ratio":1,"split_type":"percentage","split_value":0.035,"subaccount_id":"RS_53C41E2945EC5D2A8FA4DE1DD66C0509","bank_name":"STANBIC IBTC BANK PLC","country":"NG"}]}';
            $response = json_decode($response, true);
            return $response['data'];
        }
    }


    public function refund($transaction_id, $amount) {
        $url = "{$this->base_url}/transactions/{$transaction_id}/refund";

        $data = array(
            "amount" => $amount
        );

        $data_string = json_encode($data);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer {$this->api_key}"
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        if(curl_error($curl) || curl_errno($curl)) {throw new \Exception('Communication Error: ' . curl_error($curl));}
        // if(curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 200) {throw new \Exception('Payment Failed: ' . $response);}
        $refund_status = json_decode($response, true);
        if($refund_status['status'] === 'error') {
            throw new \Exception('Flutterwave '. $refund_status['message']);
        } else {
            return $refund_status;
        }
    }

	public function verify($transaction_id, $status) {
        $flutterWaveStatus = $this->paymentStatus($transaction_id);
        if (!$flutterWaveStatus) {return false;}
        if (is_array($status)) {
            return in_array($flutterWaveStatus, (array) $status);
        }
        return ($flutterWaveStatus == $status);
	}
	
	public function rewriteRules( $rules ) {
		$rules[] = [ 'payment/flutterwave/([^/]*)/([^/]*)/?', 'index.php?transaction_id=$matches[1]&payment_status=$matches[2]', 'top' ];
		return $rules;
	}
	public function query_vars( $query_vars ) {
		$query_vars[] = 'status';
		$query_vars[] = 'tx_ref';
		$query_vars[] = 'transaction_id';
		$query_vars[] = 'payment_status';
    	return $query_vars;
	}
	public function template_include( $template ) {
		$transaction_id		= (get_query_var('transaction_id') != '')?get_query_var('transaction_id'):false;
		$payment_status		= (get_query_var('payment_status') != '')?get_query_var('payment_status'):get_query_var('status');
        $tx_ref = get_query_var('tx_ref');
        $tx_ref_split = explode('.', $tx_ref);
        if (!$tx_ref || empty($tx_ref) || $tx_ref_split[0] != 'gfrm') {
            return $template;
        }
		$file				= GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH . '/templates/dashboard/cards/flutterwave.php';
        // return $template;
        // $payment_status&&!empty($payment_status)&&
		if($transaction_id && file_exists($file)&&!is_dir($file)) {
			return $file;
		} else {
			return $template;
		}
	}
	public function handleStatus($status, $transaction_id, $payment_status) {
		return $status;
	}
	


    public function add_flutterwave_gateway($gateways) {
        $gateways[] = 'GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Woo_Flutter';
        return $gateways;
    }
    public function add_flutterwave_payment_option($gateways) {
        $gateways[] = 'GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Woo_Flutter';
        return $gateways;
    }
    public function add_flutterwave_settings_tab($settings_tabs) {
        $settings_tabs['flutterwave'] = 'Flutterwave';
        return $settings_tabs;
    }
    public function output_flutterwave_settings() {
        // Output the settings fields for the Flutterwave payment gateway
        // Include fields for pausing, unpausing, and setting up the gateway options
    }
    
}
