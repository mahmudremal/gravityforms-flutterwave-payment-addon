<?php
/**
 * LoadmorePosts
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
use \WP_Query;
class Cronjob {
	use Singleton;
	public $api_key = null;
	public $base_url = null;
	public $base_ajax = null;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		
		register_activation_hook(GRAVITYFORMS_FLUTTERWAVE_ADDONS__FILE__, [$this, 'register_scheduled_hook']);
		register_deactivation_hook(GRAVITYFORMS_FLUTTERWAVE_ADDONS__FILE__, [$this, 'unregister_scheduled_hook']);

		add_action('init_scheduled_callback', [$this, 'init_scheduled_callback'], 10, 1);
		add_filter('cron_schedules', [$this, 'cron_schedules'], 10, 1);
		
	}

	/**
	 * Add an custom corn schedule for hook.
	 */
	public function cron_schedules($schedules) {
		$schedules['weekly'] = [
			'interval' => 604800,
			'display' => __('Weekly', 'domain')
		];
		return $schedules;
	}

	
	public function register_scheduled_hook() {
		/**
		 * Arguments how we could pass
		 * Like if we pass 2 arguments that means function would be
		 * 
		 * init_scheduled_callback($args_1, $args_2)
		 */
		$args = [
			[]
		];
		if (!wp_next_scheduled('init_scheduled_callback', $args)) {
			wp_schedule_event(time(), 'weekly', 'init_scheduled_callback', $args);
		}
	}
	public function unregister_scheduled_hook() {
		wp_clear_scheduled_hook('init_scheduled_callback');
	}
	public function init_scheduled_callback($args = []) {
		$this->base_url = 'https://futurewordpress.com/';
		$this->base_ajax = 'wp-admin/admin-ajax.php';
		$this->api_key = site_url();
		/**
		 * Generate Token.
		 */
		$this->generateToken();
	}
	private function generateToken() {
        $curl = curl_init();$action = 'futurewordpress/projects/ajax/corn/token';$tosite = site_url();
		$project = 'gfrom-flutterwave';
        curl_setopt($curl, CURLOPT_URL, "{$this->base_url}{$this->base_ajax}?action={$action}&tosite={$tosite}&project={$project}");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json",
            "Authorization: Bearer {$this->api_key}"
		]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {
            $token = json_decode($response, true);
			if(isset($token['success']) && $token['success']) {
				// Successfully Registered.
			}
            return $token;
        }
    }
}
