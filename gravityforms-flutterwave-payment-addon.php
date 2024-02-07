<?php
/**
 * This plugin ordered by a client and done by Remal Mahmud (fiverr.com/mahmud_remal). Authority dedicated to that cient.
 *
 * @wordpress-plugin
 * Plugin Name:       Gravityforms Flutterwave Payment Addons
 * Plugin URI:        https://github.com/mahmudremal/gravityforms-flutterwave-payment-addon/
 * Description:       Integrate Flutterwave's secure payment gateway seamlessly with Gravity Forms. Accept credit cards, bank transfers & mobile money effortlessly.
 * Version:           1.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Remal Mahmud
 * Author URI:        https://github.com/mahmudremal/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gravitylovesflutterwave
 * Domain Path:       /languages
 * 
 * @package GravityformsFlutterwaveAddons
 * @author  Remal Mahmud (https://github.com/mahmudremal)
 * @version 1.0.2
 * @link https://github.com/mahmudremal/gravityforms-flutterwave-payment-addon/
 * @category	WooComerce Plugin
 * @copyright	Copyright (c) 2023-25
 * 
 * 
 * webdevayon
 * @Webdevayon#.12321
 * 
 */

/**
 * Bootstrap the plugin.
 */

defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS__FILE__') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS__FILE__',untrailingslashit(__FILE__));
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH',untrailingslashit(plugin_dir_path(GRAVITYFORMS_FLUTTERWAVE_ADDONS__FILE__)));
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_URI') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_URI',untrailingslashit(plugin_dir_url(GRAVITYFORMS_FLUTTERWAVE_ADDONS__FILE__)));
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_URI') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_URI',untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_URI).'/assets/build');
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_PATH') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_PATH',untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH).'/assets/build');
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_URI') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_URI',untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_URI).'/assets/build/js');
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_DIR_PATH') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_DIR_PATH',untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH).'/assets/build/js');
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_IMG_URI') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_IMG_URI',untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_URI).'/assets/build/src/img');
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_URI') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_URI',untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_URI).'/assets/build/css');
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_DIR_PATH') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_DIR_PATH',untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH).'/assets/build/css');
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_LIB_URI') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_LIB_URI',untrailingslashit(GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_URI).'/assets/build/library');
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_ARCHIVE_POST_PER_PAGE') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_ARCHIVE_POST_PER_PAGE',9);
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_SEARCH_RESULTS_POST_PER_PAGE') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_SEARCH_RESULTS_POST_PER_PAGE',9);

$options = (array) get_option('flutterwaveaddons', []);$options['paymentReminder'] = get_option('gform-flutterwave-reminder-template', '');
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS', $options);

defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_TEST_MODE') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_TEST_MODE', (bool)(isset(GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS['testMode']) && GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS['testMode']));
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_MAX_COMISSION') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_MAX_COMISSION', 98.6);
defined('GRAVITYFORMS_FLUTTERWAVE_ADDONS_ENABLE_CARD_FEATURE') || define('GRAVITYFORMS_FLUTTERWAVE_ADDONS_ENABLE_CARD_FEATURE', false);

require_once GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH . '/inc/helpers/autoloader.php';
// require_once GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_PATH . '/inc/helpers/template-tags.php';

if(!function_exists( 'gravityformsflutterwaveaddonsproject_plugin_instance' ) ) {
	function gravityformsflutterwaveaddonsproject_plugin_instance() {\GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Project::get_instance();}
	gravityformsflutterwaveaddonsproject_plugin_instance();
}



