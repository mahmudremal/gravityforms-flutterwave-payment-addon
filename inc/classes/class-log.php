<?php
/**
 * WP E-Signature integration plugin.
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
class Log {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		add_action('wp_mail_succeeded', [$this, 'wp_mail_succeeded'], 10, 1);
		add_action('init', [$this, 'print_mail_sent_log'], 10, 0);
	}
	public function wp_mail_succeeded($mail_data) {
		$args = (array) $this->get_mail_sent_log();
		$args[] = $mail_data;
		update_option('gflutter_mail_log', $args);
	}
	public function erase_mail_sent_log() {
		return update_option('gflutter_mail_log', []);
	}
	public function get_mail_sent_log() {
		return get_option('gflutter_mail_log', []);
	}
	public function print_mail_sent_log() {
		if (isset($_GET['developer_mode'])) {
			print_r($this->get_mail_sent_log());

			if (isset($_GET['erase_mode'])) {
				$this->erase_mail_sent_log();
			}

			wp_die();
		}
	}
}
