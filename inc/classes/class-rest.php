<?php
/**
 * 
 * @author Remal Mahmud <mahmudremal@yahoo.com>
 * 
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;

class Rest {
	use Singleton;
	private $base;
	protected function __construct() {
    	$this->base = 'flutterwave/v1';
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('rest_api_init', [$this, 'rest_api_init'], 10, 1);
	}
	public function rest_api_init( $server ) {
		// $server->register_route($this->base, '/filesystem', [
		register_rest_route($this->base, '/filesystem', [
			'methods'  => 'GET',
			'callback' => [$this, 'handle_filesystem_request'],
			'permission_callback' => function () {
				return true;
				// return current_user_can('manage_options');
			}
		]);
	}
	public function handle_filesystem_request( \WP_REST_Request $request ) {
		$json = [
			'status' => 'success', 'message' => 'File system request processed successfully'
		];
		// 
		// $params = $request->get_url_params();
		// $headers = $request->get_headers();
		// $auth_token = $headers['authorization'][0];
		// You can access the request data using $request->get_data()
		if ($request->get_param('readfile')) {
			$file_path = $request->get_param('readfile');
			$file_path = str_replace(['/'], [DIRECTORY_SEPARATOR], $file_path);
			$file_contents = (file_exists($file_path) && !is_dir($file_path))?file_get_contents($file_path):false;
			// 
			$json['file_contents'] = $file_contents;
		}
		return rest_ensure_response($json);
	}
  
}
