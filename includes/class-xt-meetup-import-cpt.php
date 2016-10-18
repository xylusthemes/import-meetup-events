<?php
/**
 *  Register custom post type for Meetup URL.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    XT_Meetup_Import
 * @subpackage XT_Meetup_Import/includes
 */
class XT_Meetup_Import_Cpt {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action( 'init', array( $this, 'xtmi_register_meetup_url_cpt' ), 100 );
	}

	/**
	 * Register custom post type for Meetup URL.
	 *
	 * @since    1.0.0
	 */
	public function xtmi_register_meetup_url_cpt() {
		$labels = array(
			'name'               => _x( 'Meetup URL', 'post type general name', 'xt-meetup-import' ),
			'singular_name'      => _x( 'Meetup URL', 'post type singular name', 'xt-meetup-import' ),
			'menu_name'          => _x( 'Meetup URLs', 'admin menu', 'xt-meetup-import' ),
			'name_admin_bar'     => _x( 'Meetup URL', 'add new on admin bar', 'xt-meetup-import' ),
			'add_new'            => _x( 'Add New', 'book', 'xt-meetup-import' ),
			'add_new_item'       => __( 'Add New URL', 'xt-meetup-import' ),
			'new_item'           => __( 'New URL', 'xt-meetup-import' ),
			'edit_item'          => __( 'Edit URL', 'xt-meetup-import' ),
			'view_item'          => __( 'View URL', 'xt-meetup-import' ),
			'all_items'          => __( 'All Meetup URLs', 'xt-meetup-import' ),
			'search_items'       => __( 'Search Meetup URLs', 'xt-meetup-import' ),
			'parent_item_colon'  => __( 'Parent URLs:', 'xt-meetup-import' ),
			'not_found'          => __( 'No URLs found.', 'xt-meetup-import' ),
			'not_found_in_trash' => __( 'No URLs found in Trash.', 'xt-meetup-import' ),
		);

		$args = array(
			'labels'             => $labels,
	        'description'        => __( 'Post type for Meetup URL.', 'xt-meetup-import' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => false,
			'show_in_nav_menus'  => false,
			'can_export'         => false,
			'rewrite'            => false,
			'capability_type'    => 'page',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
			'taxonomies'		 => array( 'tribe_events_cat' ),
			'menu_position'		=> 5,
		);

		register_post_type( 'xtmi_meetup_url', $args );
	}
}
