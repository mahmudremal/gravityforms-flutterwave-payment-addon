<?php
/**
 * Register Menus
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
class Menus {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		/**
		 * Actions.
		 */
		// add_action( 'init', [ $this, 'register_menus' ] );
		
    add_filter( 'gravityformsflutterwaveaddons/project/settings/general', [ $this, 'general' ], 10, 1 );
    add_filter( 'gravityformsflutterwaveaddons/project/settings/fields', [ $this, 'menus' ], 10, 1 );
		add_action( 'in_admin_header', [ $this, 'in_admin_header' ], 100, 0 );
	}
	public function register_menus() {
		register_nav_menus([
			'aquila-header-menu' => esc_html__( 'Header Menu', 'gravitylovesflutterwave' ),
			'aquila-footer-menu' => esc_html__( 'Footer Menu', 'gravitylovesflutterwave' ),
		]);
	}
	/**
	 * Get the menu id by menu location.
	 *
	 * @param string $location
	 *
	 * @return integer
	 */
	public function get_menu_id( $location ) {
		// Get all locations
		$locations = get_nav_menu_locations();
		// Get object id by location.
		$menu_id = ! empty($locations[$location]) ? $locations[$location] : '';
		return ! empty( $menu_id ) ? $menu_id : '';
	}
	/**
	 * Get all child menus that has given parent menu id.
	 *
	 * @param array   $menu_array Menu array.
	 * @param integer $parent_id Parent menu id.
	 *
	 * @return array Child menu array.
	 */
	public function get_child_menu_items( $menu_array, $parent_id ) {
		$child_menus = [];
		if ( ! empty( $menu_array ) && is_array( $menu_array ) ) {
			foreach ( $menu_array as $menu ) {
				if ( intval( $menu->menu_item_parent ) === $parent_id ) {
					array_push( $child_menus, $menu );
				}
			}
		}
		return $child_menus;
	}
	public function in_admin_header() {
		if( ! isset( $_GET[ 'page' ] ) || $_GET[ 'page' ] != 'crm_dashboard' ) {return;}
		
		remove_all_actions('admin_notices');
		remove_all_actions('all_admin_notices');
		// add_action('admin_notices', function () {echo 'My notice';});
	}
	/**
	 * Supply necessry tags that could be replace on frontend.
	 * 
	 * @return string
	 * @return array
	 */
	public function commontags( $html = false ) {
		$arg = [];$tags = [
			'username', 'sitename', 
		];
		if( $html === false ) {return $tags;}
		foreach( $tags as $tag ) {
			$arg[] = sprintf( "%s{$tag}%s", '<code>{', '}</code>' );
		}
		return implode( ', ', $arg );
	}
	public function contractTags( $tags ) {
		$arg = [];
		foreach( $tags as $tag ) {
			$arg[] = sprintf( "%s{$tag}%s", '<code>{', '}</code>' );
		}
		return implode( ', ', $arg );
	}

  /**
   * WordPress Option page.
   * 
   * @return array
   */
	public function general( $args ) {
		return $args;
	}
	public function menus( $args ) {
    // get_FwpOption( 'key', 'default' ) | apply_filters( 'gravityformsflutterwaveaddons/project/system/getoption', 'key', 'default' )
		// is_FwpActive( 'key' ) | apply_filters( 'gravityformsflutterwaveaddons/project/system/isactive', 'key' )
		$args = [];
		$args['standard'] 		= [
			'title'							=> __( 'General', 'gravitylovesflutterwave' ),
			'description'				=> __( 'Generel fields comst commonly used to changed.', 'gravitylovesflutterwave' ),
			'fields'						=> [
				[
					'id' 						=> 'general-enable',
					'label'					=> __( 'Enable', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Mark to enable function of this Plugin.', 'gravitylovesflutterwave' ),
					'type'					=> 'checkbox',
					'default'				=> true
				],
				[
					'id' 						=> 'general-address',
					'label'					=> __( 'Address', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Company address, that might be used on invoice and any public place if needed.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'general-archivedelete',
					'label'					=> __( 'Archive delete', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Enable archive delete permission on frontend, so that user can delete archive files and data from their profile.', 'gravitylovesflutterwave' ),
					'type'					=> 'checkbox',
					'default'				=> ''
				],
				[
					'id' 						=> 'general-leaddelete',
					'label'					=> __( 'Delete User', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Enable this option to apear a possibility to delete user/lead with one click. If it\'s disabled, then user delete option on list and single user details page will gone until turn it on.', 'gravitylovesflutterwave' ),
					'type'					=> 'checkbox',
					'default'				=> false
				],
			]
		];
		$args['permalink'] 		= [
			'title'						=> __( 'Permalink', 'gravitylovesflutterwave' ),
			'description'			=> __( 'Setup some permalink like dashboard and like this kind of things.', 'gravitylovesflutterwave' ),
			'fields'					=> [
				[
					'id' 							=> 'permalink-dashboard',
					'label'						=> __( 'Dashboard Slug', 'gravitylovesflutterwave' ),
					'description'			=> __( 'Enable dashboard parent Slug. By default it is "/dashboard". Each time you change this field you\'ve to re-save permalink settings.', 'gravitylovesflutterwave' ),
					'type'						=> 'text',
					'default'					=> 'dashboard'
				],
				[
					'id' 						=> 'permalink-userby',
					'label'					=> __( 'Dashboard Slug', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Enable dashboard parent Slug. By default it is "/dashboard".', 'gravitylovesflutterwave' ),
					'type'					=> 'radio',
					'default'				=> 'id',
					'options'				=> [ 'id' => __( 'User ID', 'gravitylovesflutterwave' ), 'slug' => __( 'User Unique Name', 'gravitylovesflutterwave' ) ]
				],
			]
		];
		$args['dashboard'] 		= [
			'title'							=> __( 'Dashboard', 'gravitylovesflutterwave' ),
			'description'				=> __( 'Dashboard necessery fields, text and settings can configure here. Some tags on usable fields can be replace from here.', 'gravitylovesflutterwave' ) . $this->commontags( true ),
			'fields'						=> [
				[
					'id' 						=> 'dashboard-disablemyaccount',
					'label'					=> __( 'Disable My Account', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Disable WooCommerce My Account dashboard and form redirect user to new dashboard. If you enable it, it\'ll apply. But be aware, WooCommerce orders and paid downloads are listed on My Account page.', 'gravitylovesflutterwave' ),
					'type'					=> 'checkbox',
					'default'				=> false
				],
				[
					'id' 						=> 'dashboard-title',
					'label'					=> __( 'Dashboard title', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The title on dahsboard page. make sure you user tags properly.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> sprintf( __( 'Client Dashoard | %s | %s', 'gravitylovesflutterwave' ), '{username}', '{sitename}' )
				],
				[
					'id' 						=> 'dashboard-yearstart',
					'label'					=> __( 'Year Starts', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The Year range on dashboard starts from.', 'gravitylovesflutterwave' ),
					'type'					=> 'number',
					'default'				=> date( 'Y' )
				],
				[
					'id' 						=> 'dashboard-yearend',
					'label'					=> __( 'Yeah Ends with', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The Year range on dashboard ends on.', 'gravitylovesflutterwave' ),
					'type'					=> 'number',
					'default'				=> ( date( 'Y' ) + 3 )
				],
				[
					'id' 						=> 'dashboard-headerbg',
					'label'					=> __( 'Header Background', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Dashboard header background image url.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
			]
		];
		$args['links'] 		= [
			'title'							=> __( 'Links', 'gravitylovesflutterwave' ),
			'description'				=> __( 'Documentation feature and their links can be change from here. If you leave blank anything then these "Learn More" never display.', 'gravitylovesflutterwave' ) . $this->commontags( true ),
			'fields'						=> [
				[
					'id' 						=> 'docs-monthlyretainer',
					'label'					=> __( 'Monthly Retainer', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Your Monthly retainer that could be chaged anytime. Once you\'ve changed this amount, will be sync with your stripe account.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-monthlyretainerurl',
					'label'					=> __( 'Learn more', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The URL to place on Learn more.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-contentcalendly',
					'label'					=> __( 'Content Calendar', 'gravitylovesflutterwave' ),
					'description'		=> __( 'See your content calendar on Calendly.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-contentcalendlyurl',
					'label'					=> __( 'Learn more', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The URL to place on Learn more.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-contentlibrary',
					'label'					=> __( 'Content Library', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Open content library from here.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-contentlibraryurl',
					'label'					=> __( 'Learn more', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The URL to place on Learn more.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-clientrowvideos',
					'label'					=> __( 'Client Raw Video Archive', 'gravitylovesflutterwave' ),
					'description'		=> __( 'All of the video files are here. Click on the buton to open all archive list.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-clientrowvideosurl',
					'label'					=> __( 'Learn more', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The URL to place on Learn more.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-manageretainer',
					'label'					=> __( 'Manage your Retainer', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Manage your retainer from here. You can pause or cancel it from here.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-manageretainerurl',
					'label'					=> __( 'Learn more', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The URL to place on Learn more.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-paymenthistory',
					'label'					=> __( 'Payment History', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Payment history is synced form your stripe account since you started subscription.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-paymenthistoryurl',
					'label'					=> __( 'Learn more', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The URL to place on Learn more.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-changepassword',
					'label'					=> __( 'Payment History', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Change your password from here. This won\'t store on our database. Only encrypted password we store and make sure you\'ve saved your password on a safe place.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-changepasswordurl',
					'label'					=> __( 'Learn more', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The URL to place on Learn more.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-emailaddress',
					'label'					=> __( 'Email Address', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Email address required. Don\'t worry, we won\'t sent spam.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-emailaddressurl',
					'label'					=> __( 'Learn more', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The URL to place on Learn more.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-contactnumber',
					'label'					=> __( 'Contact Number', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Your conatct number is necessery in case if you need to communicate with you.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-contactnumberurl',
					'label'					=> __( 'Learn more', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The URL to place on Learn more.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-website',
					'label'					=> __( 'Website URL', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Give here you websute url if you have. Some case we might need to get idea about your and your company information.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'docs-websiteurl',
					'label'					=> __( 'Learn more', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The URL to place on Learn more.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
			]
		];
		$args['rest'] 		= [
			'title'							=> __( 'Rest API', 'gravitylovesflutterwave' ),
			'description'				=> __( 'Setup what happened when a rest api request fired on this site.', 'gravitylovesflutterwave' ),
			'fields'						=> [
				[
					'id' 						=> 'rest-createprofile',
					'label'					=> __( 'Create profile', 'gravitylovesflutterwave' ),
					'description'		=> __( 'When a request email doesn\'t match any account, so will it create a new user account?.', 'gravitylovesflutterwave' ),
					'type'					=> 'checkbox',
					'default'				=> true
				],
				[
					'id' 						=> 'rest-updateprofile',
					'label'					=> __( 'Update profile', 'gravitylovesflutterwave' ),
					'description'		=> __( 'When a request email detected an account, so will it update profile with requested information?.', 'gravitylovesflutterwave' ),
					'type'					=> 'checkbox',
					'default'				=> false
				],
				[
					'id' 						=> 'rest-preventemail',
					'label'					=> __( 'Prevent Email', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Creating an account will send an email by default. Would you like to prevent sending email from rest request operation?', 'gravitylovesflutterwave' ),
					'type'					=> 'checkbox',
					'default'				=> true
				],
				[
					'id' 						=> 'rest-defaultpass',
					'label'					=> __( 'Default Password', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The default password will be applied if any request contains emoty password or doesn\'t. Default value is random number.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
			]
		];
		$args['auth'] 		= [
			'title'							=> __( 'Social Auth', 'gravitylovesflutterwave' ),
			'description'				=> __( 'Social anuthentication requeired provider API keys and some essential information. Claim them and setup here. Every API has an expiry date. So further if you face any problem with social authentication, make sure if api validity expired.', 'gravitylovesflutterwave' ),
			'fields'						=> [
				[
					'id' 						=> 'auth-enable',
					'label'					=> __( 'Enable Social Authetication', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Mark this field to run social authentication. Once you disable from here, social authentication will be disabled from everywhere.', 'gravitylovesflutterwave' ),
					'type'					=> 'checkbox',
					'default'				=> true
				],
				[
					'id' 						=> 'auth-google',
					'label'					=> __( 'Enable Google Authetication', 'gravitylovesflutterwave' ),
					'description'		=> __( 'If you don\'t want to enable google authentication, you can disable this function from here.', 'gravitylovesflutterwave' ),
					'type'					=> 'checkbox',
					'default'				=> true
				],
				[
					'id' 						=> 'auth-connectdrive',
					'label'					=> __( 'Connect with Google Drive?', 'gravitylovesflutterwave' ),
					'description'		=> sprintf( __( 'Click on this %slink%s and allow access to connect with it.', 'gravitylovesflutterwave' ), '<a href="'. site_url( '/auth/drive/redirect/' ) . '" target="_blank">', '</a>' ),
					'type'					=> 'textcontent'
				],
				[
					'id' 						=> 'auth-googleclientid',
					'label'					=> __( 'Google Client ID', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Your Google client or App ID, that you created for Authenticate.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'auth-googleclientsecret',
					'label'					=> __( 'Google Client Secret', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Your Google client or App Secret. Is required here.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'auth-googledrivefolder',
					'label'					=> __( 'Storage Folder ID', 'gravitylovesflutterwave' ),
					'description'		=> __( 'ID of that specific folder where you want to sync files.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'auth-googleclientredirect',
					'label'					=> __( 'Google App Redirect', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Place this link on Google Auth Callback or Redirect field on your Google App.', 'gravitylovesflutterwave' ) . '<code>' . apply_filters( 'gravityformsflutterwaveaddons/project/socialauth/redirect', '/handle/google', 'google' ) . '</code>',
					'type'					=> 'textcontent'
				],
				[
					'id' 						=> 'auth-googleauthlink',
					'label'					=> __( 'Google Auth Link', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Use this link on your "Login with Google" button.', 'gravitylovesflutterwave' ) . '<code>' . apply_filters( 'gravityformsflutterwaveaddons/project/socialauth/link', '/auth/google', 'google' ) . '</code>',
					'type'					=> 'textcontent'
				],
			]
		];
		$args['social'] 		= [
			'title'							=> __( 'Social', 'gravitylovesflutterwave' ),
			'description'				=> __( 'Setup your social links her for client dashboard only. Only people who loggedin, can access these social links.', 'gravitylovesflutterwave' ),
			'fields'						=> [
				[
					'id' 						=> 'social-contact',
					'label'					=> __( 'Enable Contact', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Enable contact now tab on client dashboard.', 'gravitylovesflutterwave' ),
					'type'					=> 'checkbox',
					'default'				=> true
				],
				[
					'id' 						=> 'social-telegram',
					'label'					=> __( 'Telegram', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Provide Telegram messanger link here.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
				[
					'id' 						=> 'social-whatsapp',
					'label'					=> __( 'WhatsApp', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Provide WhatsApp messanger link here.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
				[
					'id' 						=> 'social-email',
					'label'					=> __( 'Email', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Email address for instant support.', 'gravitylovesflutterwave' ),
					'type'					=> 'email',
					'default'				=> ''
				],
				[
					'id' 						=> 'social-contactus',
					'label'					=> __( 'Contact Us', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Place the Contact Us page link here.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
			]
		];
		$args['signature'] 		= [
			'title'							=> __( 'E-Signature', 'gravitylovesflutterwave' ),
			'description'				=> __( 'Setup e-signature plugin some customize settings from here. Four tags for Contract is given below.', 'gravitylovesflutterwave' ) . $this->contractTags( ['{client_name}','{client_address}','{todays_date}','{retainer_amount}'] ),
			'fields'						=> [
				[
					'id' 						=> 'signature-addressplaceholder',
					'label'					=> __( 'Address Placeholder', 'gravitylovesflutterwave' ),
					'description'		=> __( 'What shouldbe replace if address1 & address2 both are empty. If you leave it blank, then it\'ll be blank.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> 'N/A'
				],
				[
					'id' 						=> 'signature-dateformat',
					'label'					=> __( 'Date formate', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The date format which will apply on {{todays_date}} place.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> get_option('date_format')
				],
				[
					'id' 						=> 'signature-emptyrrtainer',
					'label'					=> __( 'Empty Retainer amount', 'gravitylovesflutterwave' ),
					'description'		=> __( 'if anytime we found empty retainer amount, so what will be replace there?', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> 'N/A'
				],
				[
					'id' 						=> 'signature-defaultcontract',
					'label'					=> __( 'Default contract form', 'gravitylovesflutterwave' ),
					'description'		=> __( 'When admin doesn\'t select a registration from before sending it to client, user is taken to this contract. It should be a page where a simple wp-form will apear with client name, service type, retainer amount if necessery.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> ''
				],
			]
		];
		$args['email'] 		= [
			'title'							=> __( 'E-Mail', 'gravitylovesflutterwave' ),
			'description'				=> __( 'Setup email configuration here', 'gravitylovesflutterwave' ) . $this->contractTags( ['{client_name}','{client_address}','{todays_date}','{retainer_amount}', '{registration_link}', '{{site_name}}', '{{passwordreset_link}}' ] ),
			'fields'						=> [
				// [
				// 	'id' 						=> 'email-registationlink',
				// 	'label'					=> __( 'Registration Link', 'gravitylovesflutterwave' ),
				// 	'description'		=> __( 'Registration link that contains WP-Form registration form.', 'gravitylovesflutterwave' ),
				// 	'type'					=> 'text',
				// 	'default'				=> ""
				// ],
				[
					'id' 						=> 'email-registationsubject',
					'label'					=> __( 'Subject', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The Subject, used on registration link sending mail.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> "Invitation to Register for [Event/Service/Product]"
				],
				[
					'id' 						=> 'email-sendername',
					'label'					=> __( 'Sender name', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Sender name that should be on mail metadata..', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> "Invitation to Register for [Event/Service/Product]"
				],
				[
					'id' 						=> 'email-registationbody',
					'label'					=> __( 'Registration link Template', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The template, used on registration link sending mail.', 'gravitylovesflutterwave' ),
					'type'					=> 'textarea',
					'default'				=> "Dear [Name],\nWe are delighted to invite you to join us for [Event/Service/Product], a [brief description of event/service/product].\n[Event/Service/Product] offers [brief summary of benefits or features]. As a valued member of our community, we would like to extend a special invitation for you to be part of this exciting opportunity.\nTo register, simply click on the link below:\n[Registration link]\nShould you have any questions or require additional information, please do not hesitate to contact us at [contact information].\nWe look forward to seeing you at [Event/Service/Product].\nBest regards,\n[Your Name/Company Name]",
					'attr'					=> [ 'data-a-tinymce' => true ]
				],
				[
					'id' 						=> 'email-passresetsubject',
					'label'					=> __( 'Password Reset Subject', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The email subject on password reset mail.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> __( 'Password Reset Request',   'gravitylovesflutterwave' )
				],
				[
					'id' 						=> 'email-passresetbody',
					'label'					=> __( 'Password Reset Template', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The template, used on password reset link sending mail.', 'gravitylovesflutterwave' ),
					'type'					=> 'textarea',
					'default'				=> "Dear {{client_name}},\n\nYou recently requested to reset your password for your {{site_name}} account. Please follow the link below to reset your password:\n\n{{passwordreset_link}}\n\nIf you did not make this request, you can safely ignore this email.\n\nBest regards,\n{{site_name}} Team"
				],
			]
		];
		$args['stripe'] 		= [
			'title'							=> __( 'Stripe', 'gravitylovesflutterwave' ),
			'description'				=> __( 'Stripe payment system configuration process should be do carefully. Here some field is importent to work with no inturrupt. Such as API key or secret key, if it\'s expired on your stripe id, it won\'t work here. New user could face problem fo that reason.', 'gravitylovesflutterwave' ),
			'fields'						=> [
				[
					'id' 						=> 'stripe-cancelsubscription',
					'label'					=> __( 'Cancellation', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Enable it to make a possibility to user to cancel subscription from client dashboard.', 'gravitylovesflutterwave' ),
					'type'					=> 'checkbox',
					'default'				=> false
				],
				[
					'id' 						=> 'stripe-publishablekey',
					'label'					=> __( 'Publishable Key', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The key which is secure, could import into JS, and is safe evenif any thirdparty got those code. Note that, secret key is not a publishable key.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'stripe-secretkey',
					'label'					=> __( 'Secret Key', 'gravitylovesflutterwave' ),
					'description'		=> __( 'The secret key that never share with any kind of frontend functionalities and is ofr backend purpose. Is required.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> ''
				],
				[
					'id' 						=> 'stripe-currency',
					'label'					=> __( 'Currency', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Default currency which will use to create payment link.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> 'usd'
				],
				[
					'id' 						=> 'stripe-productname',
					'label'					=> __( 'Product name text', 'gravitylovesflutterwave' ),
					'description'		=> __( 'A text to show on product name place on checkout sanbox.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> __( 'Subscription',   'gravitylovesflutterwave' )
				],
				[
					'id' 						=> 'stripe-productdesc',
					'label'					=> __( 'Product Description', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Some text to show on product description field.', 'gravitylovesflutterwave' ),
					'type'					=> 'text',
					'default'				=> __( 'Payment for',   'gravitylovesflutterwave' ) . ' ' . get_option( 'blogname', 'We Make Content' )
				],
				[
					'id' 						=> 'stripe-productimg',
					'label'					=> __( 'Product Image', 'gravitylovesflutterwave' ),
					'description'		=> __( 'A valid image url for product. If image url are wrong or image doesn\'t detect by stripe, process will fail.', 'gravitylovesflutterwave' ),
					'type'					=> 'url',
					'default'				=> esc_url( GRAVITYFORMS_FLUTTERWAVE_ADDONS_BUILD_URI . '/icons/Online payment_Flatline.svg' )
				],
				[
					'id' 						=> 'stripe-paymentmethod',
					'label'					=> __( 'Payment Method', 'gravitylovesflutterwave' ),
					'description'		=> __( 'Select which payment method you will love to get payment.', 'gravitylovesflutterwave' ),
					'type'					=> 'select',
					'default'				=> 'card',
					'options'				=> apply_filters( 'gravityformsflutterwaveaddons/project/payment/stripe/payment_methods', [] )
				],
			]
		];
		$args['regis'] 		= [
			'title'							=> __( 'Registrations', 'gravitylovesflutterwave' ),
			'description'				=> sprintf( __( 'Setup registration link and WP-forms information here. %s will replace with a unique number to avoid cache.', 'gravitylovesflutterwave' ), '<code>{{nonce}}</code>' ),
			'fields'						=> [
				[
					'id' 						=> 'regis-rows',
					'label'					=> __( 'Rows', 'gravitylovesflutterwave' ),
					'description'		=> __( 'How many registration links do you have.', 'gravitylovesflutterwave' ),
					'type'					=> 'number',
					'default'				=> 2
				],
			]
		];
		for( $i = 1;$i <= apply_filters( 'gravityformsflutterwaveaddons/project/system/getoption', 'regis-rows', 3 ); $i++ ) {
			$args['regis'][ 'fields' ][] = [
				'id' 						=> 'regis-link-title-' . $i,
				'label'					=> __( 'Link title #' . $i, 'gravitylovesflutterwave' ),
				'description'		=> '',
				'type'					=> 'text',
				'default'				=> 'Link #' . $i
			];
			$args['regis'][ 'fields' ][] = [
				'id' 						=> 'regis-link-url-' . $i,
				'label'					=> __( 'Link URL #' . $i, 'gravitylovesflutterwave' ),
				'description'		=> '',
				'type'					=> 'url',
				'default'				=> ''
			];
			$args['regis'][ 'fields' ][] = [
				'id' 						=> 'regis-link-pageid-' . $i,
				'label'					=> __( 'Page ID#' . $i, 'gravitylovesflutterwave' ),
				'description'		=> __( 'Registration Page ID, leave it blank if you don\'t want to disable it without invitation.', 'gravitylovesflutterwave' ),
				'type'					=> 'text',
				'default'				=> ''
			];
		}
		$args['docs'] 		= [
			'title'							=> __( 'Documentations', 'gravitylovesflutterwave' ),
			'description'				=> __( 'The workprocess is tring to explain here.', 'gravitylovesflutterwave' ),
			'fields'						=> [
				[
					'id' 						=> 'auth-brifing',
					'label'					=> __( 'How to setup thank you page?', 'gravitylovesflutterwave' ),
					'description'		=> sprintf( __( 'first go to %sthis link%s Create or Edit an "Stand Alone" document. Give your thankyou custom page link here %s', 'gravitylovesflutterwave' ), '<a href="'. admin_url( 'admin.php?page=esign-docs&document_status=stand_alone' ) . '" target="_blank">', '</a>', '<img src="' . GRAVITYFORMS_FLUTTERWAVE_ADDONS_DIR_URI . '/docs/Stand-alone-esign-metabox.PNG' . '" alt="" />' ),
					'type'					=> 'textcontent'
				],
			]
		];
		return $args;
	}
}

/**
 * {{client_name}}, {{client_address}}, {{todays_date}}, {{retainer_amount}}
 */
