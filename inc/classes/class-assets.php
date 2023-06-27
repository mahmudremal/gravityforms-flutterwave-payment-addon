<?php
/**
 * Enqueue theme assets
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
class Assets {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		/**
		 * Actions.
		 */
		add_action('wp_enqueue_scripts',[$this,'register_styles']);
		add_action('gravityformsflutterwaveaddons/project/assets/register_styles',[$this,'register_styles']);
		add_action('wp_enqueue_scripts',[$this,'register_scripts']);
		add_action('gravityformsflutterwaveaddons/project/assets/register_scripts',[$this,'register_scripts']);
		add_action('wp_enqueue_scripts',[$this,'wp_denqueue_scripts'],99);
		/**
		 * The 'enqueue_block_assets' hook includes styles and scripts both in editor and frontend,
		 * except when is_admin() is used to include them conditionally
		 */
		// add_action( 'enqueue_block_assets', [ $this, 'enqueue_editor_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 1, 1 );
		// add_action( 'admin_enqueue_scripts', [ $this, 'admin_denqueue_scripts' ], 99 );
		add_filter( 'gravityformsflutterwaveaddons/project/javascript/siteconfig', [ $this, 'siteConfig' ], 1, 1 );
	}
	public function register_styles() {
		$version = $this->filemtime(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_DIR_PATH.'/frontend.css');
		// Register styles.
		wp_register_script( 'checkout-flutterwave', 'https://checkout.flutterwave.com/v3.js', ['jquery'], false, true );
		wp_register_style('GravityformsFlutterwaveAddons', GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_URI.'/frontend.css?vers'.$version, [], $version, 'all');
		// wp_enqueue_style('GravityformsFlutterwaveAddons');
		// if( $this->allow_enqueue() ) {}
	}
	public function register_scripts() {
		$version = $this->filemtime(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_DIR_PATH.'/frontend.js');
		// Register scripts.
		wp_register_script('GravityformsFlutterwaveAddons', GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_URI.'/frontend.js?vers'.$version, ['jquery'], $version, true);
		wp_enqueue_script('GravityformsFlutterwaveAddons');
		// 'https://cdnjs.cloudflare.com/ajax/libs/imask/3.4.0/imask.min.js'
		wp_register_script('imask', GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_LIB_URI.'/js/imask.min.js', [], false, true);
		
		// if( $this->allow_enqueue() ) {}
		wp_localize_script( 'GravityformsFlutterwaveAddons', 'fwpSiteConfig', apply_filters( 'gravityformsflutterwaveaddons/project/javascript/siteconfig', [] ) );
	}
	private function allow_enqueue() {
		return ( function_exists( 'is_checkout' ) && ( is_checkout() || is_order_received_page() || is_wc_endpoint_url( 'order-received' ) ) );
	}
	/**
	 * Enqueue editor scripts and styles.
	 */
	public function enqueue_editor_assets() {
		$asset_config_file = sprintf( '%s/assets.php', GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_PATH );
		if ( ! file_exists( $asset_config_file ) ) {
			return;
		}
		$asset_config = require_once $asset_config_file;
		if ( empty( $asset_config['js/editor.js'] ) ) {
			return;
		}
		$editor_asset    = $asset_config['js/editor.js'];
		$js_dependencies = ( ! empty( $editor_asset['dependencies'] ) ) ? $editor_asset['dependencies'] : [];
		$version         = ( ! empty( $editor_asset['version'] ) ) ? $editor_asset['version'] : $this->filemtime( $asset_config_file );
		// Theme Gutenberg blocks JS.
		if ( is_admin() ) {
			wp_enqueue_script(
				'aquila-blocks-js',
				GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_URI . '/blocks.js',
				$js_dependencies,
				$version,
				true
			);
		}
		// Theme Gutenberg blocks CSS.
		$css_dependencies = [
			'wp-block-library-theme',
			'wp-block-library',
		];
		wp_enqueue_style(
			'aquila-blocks-css',
			GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_URI . '/blocks.css',
			$css_dependencies,
			$this->filemtime( GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_DIR_PATH . '/blocks.css' ),
			'all'
		);
	}
	public function admin_enqueue_scripts( $curr_page ) {
		global $post;
		// if( ! in_array( $curr_page, [ 'edit.php', 'post.php' ] ) || 'shop_order' !== $post->post_type ) {return;}
		$version = $this->filemtime(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_DIR_PATH.'/backend.css');
		wp_register_style('GravityformsFlutterwaveAddons',GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_CSS_URI.'/backend.css?v='.$version,[],$version,'all');
		$version = $this->filemtime(GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_DIR_PATH.'/backend.js');
		wp_register_script('GravityformsFlutterwaveAddons',GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_JS_URI.'/backend.js?v='.$version,['jquery'],$version,true);
		
		// if ('woocommerce_page_wc-settings' !== get_current_screen()->id) {return;}
		// $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style('GravityformsFlutterwaveAddons');
		wp_enqueue_script('GravityformsFlutterwaveAddons');
		wp_localize_script('GravityformsFlutterwaveAddons', 'fwpSiteConfig', apply_filters( 'gravityformsflutterwaveaddons/project/javascript/siteconfig', []));
	}
	private function filemtime( $path ) {
		return (file_exists($path)&&!is_dir($path))?filemtime($path):false;
		// return apply_filters( 'gravityformsflutterwaveaddons/project/filesystem/filemtime', false, $path );
	}
	public function siteConfig( $args ) {
		return wp_parse_args( [
			'ajaxUrl'    		=> admin_url( 'admin-ajax.php' ),
			'ajax_nonce' 		=> wp_create_nonce( 'gravityformsflutterwaveaddons/project/verify/nonce' ),
			'is_admin' 			=> is_admin(),
			'buildPath'  		=> GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_URI,
			'videoClips'  		=> ( function_exists( 'WC' ) && WC()->session !== null ) ? (array) WC()->session->get( 'uploaded_files_to_archive' ) : [],
			'i18n'					=> [
				'sureToSubmit'							=> __( 'Want to submit it? You can retake.', 'gravitylovesflutterwave' ),
				'uploading'									=> __( 'Uploading', 'gravitylovesflutterwave' ),
				'click_here'								=> __( 'Click here', 'gravitylovesflutterwave' ),
				'video_exceed_dur_limit'		=> __( 'Video exceed it\'s duration limit.', 'gravitylovesflutterwave' ),
				'file_exceed_siz_limit'			=> __( 'Filesize exceed it maximum limit 30MB.', 'gravitylovesflutterwave' ),
				'audio_exceed_dur_limit'		=> __( 'Audio exceed it\'s duration limit.', 'gravitylovesflutterwave' ),
				'invalid_file_formate'			=> __( 'Invalid file formate.', 'gravitylovesflutterwave' ),
				'device_error'							=> __( 'Device Error', 'gravitylovesflutterwave' ),
				'confirm_cancel_subscribe'	=> __( 'Do you really want to cancel this Subscription?', 'gravitylovesflutterwave' ),
				'i_confirm_it'							=> __( 'Yes I confirm it', 'gravitylovesflutterwave' ),
				'confirming'								=> __( 'Confirming', 'gravitylovesflutterwave' ),
				'successful'								=> __( 'Successful', 'gravitylovesflutterwave' ),
				'request_failed'						=> __( 'Request failed', 'gravitylovesflutterwave' ),
				'submit'										=> __( 'Submit', 'gravitylovesflutterwave' ),
				'cancel'										=> __( 'Cancel', 'gravitylovesflutterwave' ),
				'registration_link'					=> __( 'Registration link', 'gravitylovesflutterwave' ),
				'password_reset'						=> __( 'Password reset', 'gravitylovesflutterwave' ),
				'give_your_old_password'		=> __( 'Give here your old password', 'gravitylovesflutterwave' ),
				'you_paused'								=> __( 'Subscription Paused', 'gravitylovesflutterwave' ),
				'you_paused_msg'						=> __( 'Your retainer subscription has been successfully paused. We\'ll keep your account on hold until you\'re ready to resume. Thank you!', 'gravitylovesflutterwave' ),
				'you_un_paused'							=> __( 'Subscription Resumed', 'gravitylovesflutterwave' ),
				'you_un_paused_msg'					=> __( 'Welcome back! Your retainer subscription has been successfully resumed. We\'ll continue to provide you with our services as before. Thank you!', 'gravitylovesflutterwave' ),
				'are_u_sure'								=> __( 'Are you sure?', 'gravitylovesflutterwave' ),
				'sure_to_delete'						=> __( 'Are you sure about this deletation. Once you permit to delete, this user data will be removed from database forever. This can\'t be Undone', 'gravitylovesflutterwave' ),
				'sent_reg_link'							=> __( 'Registration Link sent successfully!', 'gravitylovesflutterwave' ),
				'sent_passreset'						=> __( 'Password reset link sent Successfully!', 'gravitylovesflutterwave' ),
				'sometextfieldmissing'			=> __( 'Some required field you missed. Pleae fillup them first, then we can proceed.', 'gravitylovesflutterwave' ),
				'retainer_zero'							=> __( 'Retainer Amount Zero', 'gravitylovesflutterwave' ),
				'retainer_zerowarn'					=> __( 'You must set retainer amount before send a registration email.', 'gravitylovesflutterwave' ),
				'selectcontract'						=> __( 'Select Contract', 'gravitylovesflutterwave' ),
				'sure2logout'								=> __( 'Are you to Logout?', 'gravitylovesflutterwave' ),
				'selectcontractwarn'				=> __( 'Please choose a contract to send the registration link. Once you have selected a contract and updated the form, you will be able to send the registration link.', 'gravitylovesflutterwave' ),
				'subscription_toggled'			=> __( 'Thank you for submitting your request. We have reviewed and accepted it, and it is now pending for today. You will have the option to change your decision tomorrow. Thank you for your patience and cooperation.', 'gravitylovesflutterwave' ),
				'rusure2unsubscribe'				=> __( 'You can only pause you retainer once every 60 days. Are you sure you want to pause your retainer?', 'gravitylovesflutterwave' ),
				'rusure2subscribe'					=> __( 'We are super happy you want to resume your retainer. Are you sure you want to start now?', 'gravitylovesflutterwave' ),
				'say2wait2pause'						=> __( 'You\'ve already paused your subscription this month. Please wait until 60 days over to pause again. If you need further assistance, please contact our administrative team.', 'gravitylovesflutterwave' ),
			],
			'leadStatus'		=> apply_filters( 'gravityformsflutterwaveaddons/project/action/statuses', ['no-action' => __( 'No action fetched', 'gravitylovesflutterwave' )], false )
		], (array) $args );
	}
	public function wp_denqueue_scripts() {}
	public function admin_denqueue_scripts() {
		if( ! isset( $_GET[ 'page' ] ) ||  $_GET[ 'page' ] !='crm_dashboard' ) {return;}
		wp_dequeue_script( 'qode-tax-js' );
	}
}
