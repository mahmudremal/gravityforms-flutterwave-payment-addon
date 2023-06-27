<?php
/**
 * Register Post Types
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
class PostTypes {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		/**
		 * Actions.
		 */
		add_action( 'init', [ $this, 'create_movie_cpt' ], 0 );
	}
	// Register Custom Post Type Movie
	public function create_movie_cpt() {
		$labels = [
			'name'                  => _x( 'Movies', 'Post Type General Name', 'gravitylovesflutterwave' ),
			'singular_name'         => _x( 'Movie', 'Post Type Singular Name', 'gravitylovesflutterwave' ),
			'menu_name'             => _x( 'Movies', 'Admin Menu text', 'gravitylovesflutterwave' ),
			'name_admin_bar'        => _x( 'Movie', 'Add New on Toolbar', 'gravitylovesflutterwave' ),
			'archives'              => __( 'Movie Archives', 'gravitylovesflutterwave' ),
			'attributes'            => __( 'Movie Attributes', 'gravitylovesflutterwave' ),
			'parent_item_colon'     => __( 'Parent Movie:', 'gravitylovesflutterwave' ),
			'all_items'             => __( 'All Movies', 'gravitylovesflutterwave' ),
			'add_new_item'          => __( 'Add New Movie', 'gravitylovesflutterwave' ),
			'add_new'               => __( 'Add New', 'gravitylovesflutterwave' ),
			'new_item'              => __( 'New Movie', 'gravitylovesflutterwave' ),
			'edit_item'             => __( 'Edit Movie', 'gravitylovesflutterwave' ),
			'update_item'           => __( 'Update Movie', 'gravitylovesflutterwave' ),
			'view_item'             => __( 'View Movie', 'gravitylovesflutterwave' ),
			'view_items'            => __( 'View Movies', 'gravitylovesflutterwave' ),
			'search_items'          => __( 'Search Movie', 'gravitylovesflutterwave' ),
			'not_found'             => __( 'Not found', 'gravitylovesflutterwave' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'gravitylovesflutterwave' ),
			'featured_image'        => __( 'Featured Image', 'gravitylovesflutterwave' ),
			'set_featured_image'    => __( 'Set featured image', 'gravitylovesflutterwave' ),
			'remove_featured_image' => __( 'Remove featured image', 'gravitylovesflutterwave' ),
			'use_featured_image'    => __( 'Use as featured image', 'gravitylovesflutterwave' ),
			'insert_into_item'      => __( 'Insert into Movie', 'gravitylovesflutterwave' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Movie', 'gravitylovesflutterwave' ),
			'items_list'            => __( 'Movies list', 'gravitylovesflutterwave' ),
			'items_list_navigation' => __( 'Movies list navigation', 'gravitylovesflutterwave' ),
			'filter_items_list'     => __( 'Filter Movies list', 'gravitylovesflutterwave' ),
		];
		$args   = [
			'label'               => __( 'Movie', 'gravitylovesflutterwave' ),
			'description'         => __( 'The movies', 'gravitylovesflutterwave' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-video-alt',
			'supports'            => [
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'revisions',
				'author',
				'comments',
				'trackbacks',
				'page-attributes',
				'custom-fields',
			],
			'taxonomies'          => [],
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'hierarchical'        => false,
			'exclude_from_search' => false,
			'show_in_rest'        => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		];
		register_post_type( 'movies', $args );
	}
}
