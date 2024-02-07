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
		Install::get_instance();
		Assets::get_instance();
		Flutterwave::get_instance();
		Gravityforms::get_instance();
		Rewrite::get_instance();
		Export::get_instance();
		Cronjob::get_instance();
		Bulks::get_instance();

		// Woo_Flutter::get_instance();
		// Option::get_instance();

		// Load class.
		Core::get_instance();
		// Helpers::get_instance();
		// Dashboard::get_instance();
		// Roles::get_instance();
		// Restapi::get_instance();
		// GoogleDrive::get_instance();
		// SocialAuth::get_instance();
		// Notices::get_instance();
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
		// $this->setup_hooks();
	}
	protected function setup_hooks() {
		// Some additional functionalities can be added here.
	}
}
