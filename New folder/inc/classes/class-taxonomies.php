<?php
/**
 * Register Custom Taxonomies
 *
 * @package GravityformsFlutterwaveAddons
 */
namespace GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc;
use GRAVITYFORMS_FLUTTERWAVE_ADDONS\Inc\Traits\Singleton;
class Taxonomies {
	use Singleton;
	protected function __construct() {
		// load class.
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		/**
		 * Actions.
		 */
		add_action( 'init', [ $this, 'create_genre_taxonomy' ] );
		add_action( 'init', [ $this, 'create_year_taxonomy' ] );
	}
	// Register Taxonomy Genre
	public function create_genre_taxonomy() {
		$labels = [
			'name'              => _x( 'Genres', 'taxonomy general name', 'gravitylovesflutterwave' ),
			'singular_name'     => _x( 'Genre', 'taxonomy singular name', 'gravitylovesflutterwave' ),
			'search_items'      => __( 'Search Genres', 'gravitylovesflutterwave' ),
			'all_items'         => __( 'All Genres', 'gravitylovesflutterwave' ),
			'parent_item'       => __( 'Parent Genre', 'gravitylovesflutterwave' ),
			'parent_item_colon' => __( 'Parent Genre:', 'gravitylovesflutterwave' ),
			'edit_item'         => __( 'Edit Genre', 'gravitylovesflutterwave' ),
			'update_item'       => __( 'Update Genre', 'gravitylovesflutterwave' ),
			'add_new_item'      => __( 'Add New Genre', 'gravitylovesflutterwave' ),
			'new_item_name'     => __( 'New Genre Name', 'gravitylovesflutterwave' ),
			'menu_name'         => __( 'Genre', 'gravitylovesflutterwave' ),
		];
		$args   = [
			'labels'             => $labels,
			'description'        => __( 'Movie Genre', 'gravitylovesflutterwave' ),
			'hierarchical'       => true,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_tagcloud'      => true,
			'show_in_quick_edit' => true,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
		];
		register_taxonomy( 'genre', [ 'movies' ], $args );
	}
	// Register Taxonomy Year
	public function create_year_taxonomy() {
		$labels = [
			'name'              => _x( 'Years', 'taxonomy general name', 'gravitylovesflutterwave' ),
			'singular_name'     => _x( 'Year', 'taxonomy singular name', 'gravitylovesflutterwave' ),
			'search_items'      => __( 'Search Years', 'gravitylovesflutterwave' ),
			'all_items'         => __( 'All Years', 'gravitylovesflutterwave' ),
			'parent_item'       => __( 'Parent Year', 'gravitylovesflutterwave' ),
			'parent_item_colon' => __( 'Parent Year:', 'gravitylovesflutterwave' ),
			'edit_item'         => __( 'Edit Year', 'gravitylovesflutterwave' ),
			'update_item'       => __( 'Update Year', 'gravitylovesflutterwave' ),
			'add_new_item'      => __( 'Add New Year', 'gravitylovesflutterwave' ),
			'new_item_name'     => __( 'New Year Name', 'gravitylovesflutterwave' ),
			'menu_name'         => __( 'Year', 'gravitylovesflutterwave' ),
		];
		$args   = [
			'labels'             => $labels,
			'description'        => __( 'Movie Release Year', 'gravitylovesflutterwave' ),
			'hierarchical'       => false,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_tagcloud'      => true,
			'show_in_quick_edit' => true,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
		];
		register_taxonomy( 'movie-year', [ 'movies' ], $args );
	}
}
