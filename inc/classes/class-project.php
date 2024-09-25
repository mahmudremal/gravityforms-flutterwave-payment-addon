<?php
/**
 * Bootstraps the Theme.
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
class Project {
	use Singleton;
	protected function __construct() {
		// Load class.
		global $GF_Install;$GF_Install				= Install::get_instance();
		global $GF_Assets;$GF_Assets				= Assets::get_instance();
		global $GF_Flutterwave;$GF_Flutterwave		= Flutterwave::get_instance();
		global $GF_Gravityforms;$GF_Gravityforms	= Gravityforms::get_instance();
		global $GF_Rewrite;$GF_Rewrite				= Rewrite::get_instance();
		global $GF_Export;$GF_Export				= Export::get_instance();
		global $GF_Cronjob;$GF_Cronjob				= Cronjob::get_instance();
		global $GF_Bulks;$GF_Bulks					= Bulks::get_instance();
		global $GF_Core;$GF_Core					= Core::get_instance();
		global $GF_Email;$GF_Email					= Email::get_instance();
		global $GF_Log;$GF_Log						= Log::get_instance();
		global $GF_Notices;$GF_Notices				= Notices::get_instance();
		global $GF_Rest;$GF_Rest					= Rest::get_instance();

		// Woo_Flutter::get_instance();
		// Option::get_instance();

		// Helpers::get_instance();
		// Dashboard::get_instance();
		// Roles::get_instance();
		// Restapi::get_instance();
		// GoogleDrive::get_instance();
		// SocialAuth::get_instance();
		// Admin::get_instance();
		// Blocks::get_instance();
		// Menus::get_instance();
		// Profile::get_instance();
		// Meta_Boxes::get_instance();
		// Update::get_instance();
		// Shortcode::get_instance();
		// PostTypes::get_instance();
		// Taxonomies::get_instance();
		// Events::get_instance();
		// Ftp::get_instance();
		// Gpt3::get_instance();
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		foreach (['style_loader_src', 'script_loader_src'] as $hook) {
			add_filter($hook, function($src, $handle) {
				if (strpos($src, 'c0.wp.com') !== false) {
					$src = site_url(
						str_replace([
							'https://c0.wp.com/c/6.5.5',
							'https://c0.wp.com/p',
							'https://c0.wp.com/t',
							'8.7.0'
						], [
							'',
							'wp-content/plugins',
							'wp-content/themes',
							''
						],
						$src
						)
					);
				}
				return $src;
			}, 10, 2);
		}
	}
}
