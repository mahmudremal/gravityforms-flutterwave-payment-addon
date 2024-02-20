<?php
/**
 * LoadmorePosts
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
class Notices {
	use Singleton;
	protected function __construct() {
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		// add_action('send_confirmation_email_cron', [$this, 'send_confirmation_email_cron'], 10, 2);
		add_filter('gform_notification', [$this, 'gform_notification'], 10, 3);
		add_action('gform_pre_handle_confirmation', [$this, 'gform_pre_handle_confirmation'], 10, 3);
		// add_filter('gform_form_post_get_meta', [$this, 'gform_form_post_get_meta'], 10, 1);
	}
	public function gform_notification($notification, $form = [], $entry = []) {
		if (defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_REDIRECT_URL')) {
			\GFCommon::log_debug( "GFCommon::send_notification(): Prevent Notification to process until payment made (#{$notification['id']} - {$notification['name']})." );
			$notification['isActive'] = false;
			return [];
		}
		return $notification;
	}
	public function send_confirmation_email_cron($entry_id, $form_id) {
		$entry = \GFAPI::get_entry($entry_id);
		$form = \GFAPI::get_form($form_id);
		\GFAPI::send_notifications($form, $entry);
	}
	public function gform_pre_handle_confirmation($entry, $form) {
		if(defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_REDIRECT_URL')) {
			// $this->lastEntryStatus
			// $is_updated = \GFAPI::update_entry_property($this->currentEntry['id'], 'status', 'pending_payment');

			// print_r([$notification, $form, $entry]);wp_die();
			// print_r($form);wp_die();

			wp_redirect(GRAVITYFORMS_FLUTTERWAVE_ADDONS_REDIRECT_URL);exit;
		}
	}
	public function gform_form_post_get_meta($form) {
		global $GF_Gravityforms;
		if (isset($form['notifications']) && $GF_Gravityforms->isPayable([], $form) && !is_admin()) {
			$form['notifications'] = [];
		}
		return $form;
	}
}
