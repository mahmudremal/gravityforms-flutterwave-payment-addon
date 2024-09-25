<?php
/**
 * Register Meta Boxes
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
/**
 * Class Meta_Boxes
 */
class Meta_Boxes {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		/**
		 * Actions.
		 */
		add_action( 'add_meta_boxes', [ $this, 'add_custom_meta_box' ] );
		// add_action( 'save_post', [ $this, 'save_post_meta_data' ] );
	}
	/**
	 * Add custom meta box.
	 *
	 * @return void
	 */
	public function add_custom_meta_box() {
		$screens = [ 'shop_order' ];
		foreach ( $screens as $screen ) {
			add_meta_box(
				'checkout_video_clip',           				// Unique ID
				__( 'Checkout Video Clip', 'gravitylovesflutterwave' ),  // Box title
				[ $this, 'custom_meta_box_html' ],  		// Content callback, must be of type callable
				$screen,                   							// Post type
				'side'                   								// context
			);
		}
	}
	/**
	 * Custom meta box HTML( for form )
	 *
	 * @param object $post Post.
	 *
	 * @return void
	 */
	public function custom_meta_box_html( $post ) {
		$meta = (array) get_post_meta( $post->ID, 'checkout_video_clip', true );
		$shortHand = site_url( '/clip/' . dechex( $post->ID ) );
		$shortned = str_replace( [ 'https://www.', 'http://www.' ], [ '', '' ], $shortHand );
		if( ! isset( $meta[ 'full_url' ] ) || empty( $meta[ 'full_url' ] ) ) :
			esc_html_e( 'No video uploaded for this order.', 'gravitylovesflutterwave' );
			else :
		?>
		<div class="fwp-tabs__container">
			<div class="fwp-tabs__wrap">
				<div class="fwp-tabs__navs">
					<div class="fwp-tabs__nav-item active" data-target="#the-qrcode"><?php esc_html_e( 'Scan Code', 'gravitylovesflutterwave' ); ?></div>
					<div class="fwp-tabs__nav-item" data-target="#the-video"><?php esc_html_e( 'Play Video', 'gravitylovesflutterwave' ); ?></div>
				</div>
				<div class="fwp-tabs__tabs-field">
					<div class="fwp-tabs__content active" id="the-qrcode">
						<canvas class="fwp-qrzone-field" data-code="<?php echo esc_url( $shortHand ); ?>"></canvas>
						<p class="qrcode-subtitle"><?php echo esc_html( $shortned ); ?></p>
					</div>
					<div class="fwp-tabs__content" id="the-video">
						<div class="fwp-video-player-wraper">
							<div class="fwp-video-wrap">
								<video id="fwp-videojs-playing-field" playsinline class="video-js vjs-default-skin" controls preload="auto" data-temp-poster="" data-setup='{ "controls": true, "autoplay": false, "preload": "none" }'>
									<source src="<?php echo esc_url( $meta['full_url'] ); ?>" type="<?php echo esc_attr( $meta['type'] ); ?>"></source>
									<p class="vjs-no-js">
										<?php esc_html_e( 'To view this video please enable JavaScript, and consider upgrading to a
										web browser that', 'gravitylovesflutterwave' ); ?>
										<a href="https://videojs.com/html5-video-support/" target="_blank">
											<?php esc_html_e( 'supports HTML5 video', 'gravitylovesflutterwave' ); ?>
										</a>
									</p>
								</video>
								<!-- <a class="fwp-metabox-download-button" href="<?php echo esc_url( $meta['full_url'] ); ?>" download="<?php echo esc_url( $meta['name'] ); ?>"><?php esc_html_e( 'Download this Video', 'gravitylovesflutterwave' ); ?></a> -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		endif;
	}
	/**
	 * Save post meta into database
	 * when the post is saved.
	 *
	 * @param integer $post_id Post id.
	 *
	 * @return void
	 */
	public function save_post_meta_data( $post_id ) {
		/**
		 * When the post is saved or updated we get $_POST available
		 * Check if the current user is authorized
		 */
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		/**
		 * Check if the nonce value we received is the same we created.
		 */
		if ( ! isset( $_POST['hide_title_meta_box_nonce_name'] ) ||
		     ! wp_verify_nonce( $_POST['hide_title_meta_box_nonce_name'], plugin_basename(__FILE__) )
		) {
			return;
		}
		if ( array_key_exists( 'aquila_hide_title_field', $_POST ) ) {
			update_post_meta(
				$post_id,
				'_hide_page_title',
				$_POST['aquila_hide_title_field']
			);
		}
	}
}
