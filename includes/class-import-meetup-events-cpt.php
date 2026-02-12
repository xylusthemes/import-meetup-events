<?php
/**
 * Class for Register and manage Events.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Meetup_Events
 * @subpackage Import_Meetup_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Import_Meetup_Events_Cpt {

	// The Events Calendar Event Taxonomy
	public $event_slug;

	// Event post type.
	protected $event_posttype;
	
	// Event post type.
	protected $event_category;
	
	// Event post type.
	protected $event_tag;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->event_slug = 'meetup-event';
		$this->event_posttype = 'meetup_events';
		$this->event_category = 'meetup_category';
		$this->event_tag = 'meetup_tag';

		$ime_options       = get_option( IME_OPTIONS );
		if (ime_is_pro()) {
			$this->event_slug = isset( $ime_options['event_slug'] ) ? $ime_options['event_slug'] : 'meetup-event';
		}
		add_action( 'init', array( $this, 'register_event_post_type' ) );
		add_action( 'init', array( $this, 'register_event_taxonomy' ) );

		add_action( 'add_meta_boxes', array($this, 'add_event_meta_boxes' ) );

		add_action( 'save_post', array($this, 'save_event_meta_boxes'), 10, 2);
		add_filter( 'manage_meetup_events_posts_columns', array( $this, 'meetup_events_columns' ), 10, 1 );

		add_action( 'manage_posts_custom_column', array( $this, 'meetup_events_columns_data' ), 10, 2 ); 
		add_filter( 'the_content', array( $this, 'meetup_events_meta_before_content' ) ); 
		add_shortcode('meetup_events', array( $this, 'meetup_events_archive' ) );
	}

	/**
	 * get Events Post type
	 *
	 * @since    1.0.0
	 */
	public function get_event_posttype(){
		return $this->event_posttype;
	}
	
	/**
	 * get events category taxonomy
	 *
	 * @since    1.0.0
	 */
	public function get_event_categroy_taxonomy(){
		return $this->event_category;
	}

	/**
	 * get events tag taxonomy
	 *
	 * @since    1.0.0
	 */
	public function get_event_tag_taxonomy(){
		return $this->event_tag;
	}

	/**
	 * Register Events Post type
	 *
	 * @since    1.0.0
	 */
	public function register_event_post_type(){

		/*
		 * Event labels
		 */
		$event_labels = array(
				'name'                  => _x( 'Meetup Events', 'Post Type General Name', 'import-meetup-events' ),
				'singular_name'         => _x( 'Meetup Event', 'Post Type Singular Name', 'import-meetup-events' ),
				'menu_name'             => __( 'Meetup Events', 'import-meetup-events' ),
				'name_admin_bar'        => __( 'Meetup Event', 'import-meetup-events' ),
				'archives'              => __( 'Event Archives', 'import-meetup-events' ),
				'parent_item_colon'     => __( 'Parent Event:', 'import-meetup-events' ),
				'all_items'             => __( 'Meetup Events', 'import-meetup-events' ),
				'add_new_item'          => __( 'Add New Event', 'import-meetup-events' ),
				'add_new'               => __( 'Add New', 'import-meetup-events' ),
				'new_item'              => __( 'New Event', 'import-meetup-events' ),
				'edit_item'             => __( 'Edit Event', 'import-meetup-events' ),
				'update_item'           => __( 'Update Event', 'import-meetup-events' ),
				'view_item'             => __( 'View Event', 'import-meetup-events' ),
				'search_items'          => __( 'Search Event', 'import-meetup-events' ),
				'not_found'             => __( 'Not found', 'import-meetup-events' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'import-meetup-events' ),
				'featured_image'        => __( 'Featured Image', 'import-meetup-events' ),
				'set_featured_image'    => __( 'Set featured image', 'import-meetup-events' ),
				'remove_featured_image' => __( 'Remove featured image', 'import-meetup-events' ),
				'use_featured_image'    => __( 'Use as featured image', 'import-meetup-events' ),
				'insert_into_item'      => __( 'Insert into Event', 'import-meetup-events' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Event', 'import-meetup-events' ),
				'items_list'            => __( 'Event Items list', 'import-meetup-events' ),
				'items_list_navigation' => __( 'Event Items list navigation', 'import-meetup-events' ),
				'filter_items_list'     => __( 'Filter Event items list', 'import-meetup-events' ),
		);
		$rewrite = array(
				'slug'                  => $this->event_slug,
				'with_front'            => false,
				'pages'                 => true,
				'feeds'                 => true,
				'ep_mask'               => EP_NONE
		);
		$event_cpt_args = array(
				'label'                 => __( 'Events', 'import-meetup-events' ),
				'description'           => __( 'Post type for Events', 'import-meetup-events' ),
				'labels'                => $event_labels,
				'supports'              => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions' ),
				'taxonomies'            => array( $this->event_category, $this->event_tag ),
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'menu_position'         => 5,
				'menu_icon'             => 'dashicons-calendar',
				'show_in_admin_bar'     => true,
				'show_in_nav_menus'     => true,
				'can_export'            => true,
				'has_archive'           => true,
				'exclude_from_search'   => false,
				'publicly_queryable'    => true,
				'rewrite'               => $rewrite,
		);
		register_post_type( $this->event_posttype, $event_cpt_args );
	}


	/**
	 * Register Event tag taxonomy
	 *
	 * @since    1.0.0
	 */
	 public function register_event_taxonomy(){

		 /* Register the Event Category taxonomy. */
		register_taxonomy( $this->event_category, array( $this->event_posttype ), array(
				'labels'                     => array(
					'name'                       => __( 'Event Categories',     'import-meetup-events' ),
					'singular_name'              => __( 'Event Category',       'import-meetup-events' ),
					'menu_name'                  => __( 'Event Categories',     'import-meetup-events' ),
					'name_admin_bar'             => __( 'Event Category',       'import-meetup-events' ),
					'search_items'               => __( 'Search Categories',    'import-meetup-events' ),
					'popular_items'              => __( 'Popular Categories',   'import-meetup-events' ),
					'all_items'                  => __( 'All Categories',       'import-meetup-events' ),
					'edit_item'                  => __( 'Edit Category',        'import-meetup-events' ),
					'view_item'                  => __( 'View Category',        'import-meetup-events' ),
					'update_item'                => __( 'Update Category',      'import-meetup-events' ),
					'add_new_item'               => __( 'Add New Category',     'import-meetup-events' ),
					'new_item_name'              => __( 'New Category Name',    'import-meetup-events' ),
				),
				'public'                     => true,
				'show_ui'                    => true,
				'show_in_nav_menus'          => true,
				'show_admin_column'          => true,
				'hierarchical'               => true,
				'query_var'                  => true,
			)
		);

		 /* Register the event Tag taxonomy. */
		register_taxonomy(
			$this->event_tag,
			array( $this->event_posttype ),
			array(
				'public'            => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => true,
				'show_admin_column' => true,
				'hierarchical'      => false,
				'query_var'         => $this->event_tag,
				/* Labels used when displaying taxonomy and terms. */
				'labels'            => array(
					'name'                       => __( 'Event Tags',                    'import-meetup-events' ),
					'singular_name'              => __( 'Event Tag',                     'import-meetup-events' ),
					'menu_name'                  => __( 'Event Tags',                    'import-meetup-events' ),
					'name_admin_bar'             => __( 'Event Tag',                     'import-meetup-events' ),
					'search_items'               => __( 'Search Tags',                   'import-meetup-events' ),
					'popular_items'              => __( 'Popular Tags',                  'import-meetup-events' ),
					'all_items'                  => __( 'All Tags',                      'import-meetup-events' ),
					'edit_item'                  => __( 'Edit Tag',                      'import-meetup-events' ),
					'view_item'                  => __( 'View Tag',                      'import-meetup-events' ),
					'update_item'                => __( 'Update Tag',                    'import-meetup-events' ),
					'add_new_item'               => __( 'Add New Tag',                   'import-meetup-events' ),
					'new_item_name'              => __( 'New Tag Name',                  'import-meetup-events' ),
					'separate_items_with_commas' => __( 'Separate tags with commas',     'import-meetup-events' ),
					'add_or_remove_items'        => __( 'Add or remove tags',            'import-meetup-events' ),
					'choose_from_most_used'      => __( 'Choose from the most used tags','import-meetup-events' ),
					'not_found'                  => __( 'No tags found',                 'import-meetup-events' ),
					'parent_item'                => null,
					'parent_item_colon'          => null,
				)
			)
		);
	 }


	/*
	 *  Add Meta box for team link meta box.
	 */
	public function add_event_meta_boxes() {
		add_meta_box(
				'meetup_events_metabox',
				__( 'Events Details', 'import-meetup-events' ),
				array($this,'render_event_meta_boxes'),
				array( $this->event_posttype ),
				'normal',
				'high'
		);
	}

	/*
	 * Event meta box render
	 */
	public function render_event_meta_boxes( $post ) {

		// Use nonce for verification
		wp_nonce_field( IME_PLUGIN_DIR, 'ime_event_metabox_nonce' );
		$start_hour     = get_post_meta( $post->ID, 'event_start_hour', true );
		$start_minute   = get_post_meta( $post->ID, 'event_start_minute', true );
		$start_meridian = get_post_meta( $post->ID, 'event_start_meridian', true );
		$end_hour       = get_post_meta( $post->ID, 'event_end_hour', true );
		$end_minute     = get_post_meta( $post->ID, 'event_end_minute', true );
		$end_meridian   = get_post_meta( $post->ID, 'event_end_meridian', true );

		$fields = [
			'venue_name'     => __( 'Venue', 'import-meetup-events' ),
			'venue_address'  => __( 'Address', 'import-meetup-events' ),
			'venue_city'     => __( 'City', 'import-meetup-events' ),
			'venue_state'    => __( 'State', 'import-meetup-events' ),
			'venue_country'  => __( 'Country', 'import-meetup-events' ),
			'venue_zipcode'  => __( 'Zipcode', 'import-meetup-events' ),
			'venue_lat'      => __( 'Latitude', 'import-meetup-events' ),
			'venue_lon'      => __( 'Longitude', 'import-meetup-events' ),
			'venue_url'      => __( 'Website', 'import-meetup-events' ),
			'organizer_name' => __( 'Organizer Name', 'import-meetup-events' ),
			'organizer_email'=> __( 'Email', 'import-meetup-events' ),
			'organizer_phone'=> __( 'Phone', 'import-meetup-events' ),
			'organizer_url'  => __( 'Website', 'import-meetup-events' ),
			'ime_event_link' => __( 'Source Link', 'import-meetup-events' ),
		];

		?>

		<div class="ime_form_section">
			<h3><?php esc_attr_e( 'Time & Date', 'import-meetup-events' ); ?></h3>
			<hr>
			<div class="ime_form_row">
				<label for="event_start_date"><?php esc_attr_e( 'Start Date & Time', 'import-meetup-events' ); ?>:</label>
				<div class="ime_form_input_group">
					<input type="text" name="event_start_date" class="xt_datepicker" id="event_start_date" value="<?php echo esc_attr( get_post_meta( $post->ID, 'event_start_date', true ) ); ?>" /> @ 
					<?php
						$this->generate_dropdown( 'event_start', 'hour', $start_hour );
						$this->generate_dropdown( 'event_start', 'minute', $start_minute );
						$this->generate_dropdown( 'event_start', 'meridian', $start_meridian );
					?>
				</div>
			</div>

			<div class="ime_form_row">
				<label for="event_end_date"><?php esc_attr_e( 'End Date & Time', 'import-meetup-events' ); ?>:</label>
				<div class="ime_form_input_group">
					<input type="text" name="event_end_date" class="xt_datepicker" id="event_end_date" value="<?php echo esc_attr( get_post_meta( $post->ID, 'event_end_date', true ) ); ?>" /> @ 
					<?php
						$this->generate_dropdown( 'event_end', 'hour', $end_hour );
						$this->generate_dropdown( 'event_end', 'minute', $end_minute );
						$this->generate_dropdown( 'event_end', 'meridian', $end_meridian );
					?>
				</div>
			</div>
		</div>

		<div class="ime_form_section">
			<h3><?php esc_attr_e( 'Location Details', 'import-meetup-events' ); ?></h3>
			<hr>
			<?php
			foreach ( [ 'venue_name', 'venue_address', 'venue_city', 'venue_state', 'venue_country', 'venue_zipcode', 'venue_lat', 'venue_lon', 'venue_url' ] as $field ) {
				?>
				<div class="ime_form_row">
					<label for="<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $fields[ $field ] ); ?>:</label>
					<div class="ime_form_input_group">
						<input type="text" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( get_post_meta( $post->ID, $field, true ) ); ?>" />
					</div>
				</div>
				<?php
			}
			?>
		</div>

		<div class="ime_form_section">
			<h3><?php esc_attr_e( 'Organizer Details', 'import-meetup-events' ); ?></h3>
			<hr>
			<?php
			foreach ( [ 'organizer_name', 'organizer_email', 'organizer_phone', 'organizer_url' ] as $field ) {
				$type = $field === 'organizer_email' ? 'email' : 'text';
				?>
				<div class="ime_form_row">
					<label for="<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $fields[ $field ] ); ?>:</label>
					<div class="ime_form_input_group">
						<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( get_post_meta( $post->ID, $field, true ) ); ?>" />
					</div>
				</div>
				<?php
			}
			?>
		</div>
		
		<div class="ime_form_section">
			<h3><?php esc_attr_e( 'Event Source Link', 'import-meetup-events' ); ?></h3>
			<hr>
			<div class="ime_form_row">
				<label for="ime_event_link"><?php echo esc_html( $fields['ime_event_link'] ); ?>:</label>
				<div class="ime_form_input_group">
					<input type="text" name="ime_event_link" id="ime_event_link" value="<?php echo esc_url( get_post_meta( $post->ID, 'ime_event_link', true ) ); ?>" />
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * generate dropdowns for event time.
	 */
	function generate_dropdown( $start_end, $type, $selected = '' ){
		if( $start_end == '' || $type == '' ){	
			return;
		}
		$select_name = $start_end.'_'.$type;
		if( $type == 'hour'){
			?>
			<select name="<?php echo esc_attr( $select_name ); ?>">
				<option value="01" <?php selected( $selected, '01' ); ?>>01</option>
				<option value="02" <?php selected( $selected, '02' ); ?>>02</option>
				<option value="03" <?php selected( $selected, '03' ); ?>>03</option>
				<option value="04" <?php selected( $selected, '04' ); ?>>04</option>
				<option value="05" <?php selected( $selected, '05' ); ?>>05</option>
				<option value="06" <?php selected( $selected, '06' ); ?>>06</option>
				<option value="07" <?php selected( $selected, '07' ); ?>>07</option>
				<option value="08" <?php selected( $selected, '08' ); ?>>08</option>
				<option value="09" <?php selected( $selected, '09' ); ?>>09</option>
				<option value="10" <?php selected( $selected, '10' ); ?>>10</option>
				<option value="11" <?php selected( $selected, '11' ); ?>>11</option>
				<option value="12" <?php selected( $selected, '12' ); ?>>12</option>
			</select>
			<?php
		}elseif( $type == 'minute'){
			?>
			<select name="<?php echo esc_attr( $select_name ); ?>">
				<option value="00" <?php selected( $selected, '00' ); ?>>00</option>
				<option value="05" <?php selected( $selected, '05' ); ?>>05</option>
				<option value="10" <?php selected( $selected, '10' ); ?>>10</option>
				<option value="15" <?php selected( $selected, '15' ); ?>>15</option>
				<option value="20" <?php selected( $selected, '20' ); ?>>20</option>
				<option value="25" <?php selected( $selected, '25' ); ?>>25</option>
				<option value="30" <?php selected( $selected, '30' ); ?>>30</option>
				<option value="35" <?php selected( $selected, '35' ); ?>>35</option>
				<option value="40" <?php selected( $selected, '40' ); ?>>40</option>
				<option value="45" <?php selected( $selected, '45' ); ?>>45</option>
				<option value="50" <?php selected( $selected, '50' ); ?>>50</option>
				<option value="55" <?php selected( $selected, '55' ); ?>>55</option>
			</select>
			<?php
		}elseif( $type == 'meridian'){
			?>
			<select name="<?php echo esc_attr( $select_name ); ?>">
				<option value="am" <?php selected( $selected, 'am' ); ?>>am</option>
				<option value="pm" <?php selected( $selected, 'pm' ); ?>>pm</option>
			</select>
			<?php
		}
	}

	/**
	 * Save Testimonial meta box Options
	 */
	public function save_event_meta_boxes($post_id, $post)
	{

		// Verify the nonce before proceeding.
		if ( !isset( $_POST['ime_event_metabox_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ime_event_metabox_nonce'] ) ) , IME_PLUGIN_DIR ) ){
			return $post_id;
		}

		// check user capability to edit post
		if(!current_user_can("edit_post", $post_id)){
			return $post_id;
		}

		// can't save if auto save
		if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE){
			return $post_id;
		}

		// check if team then save it.
		if($post->post_type !=  $this->event_posttype ){
			return $post_id;
		}

		// Event Date & time Details
		$event_start_date     = isset( $_POST['event_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_start_date'] ) ) : '';
		$event_end_date       = isset( $_POST['event_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_end_date'] ) ) : '';
		$event_start_hour     = isset( $_POST['event_start_hour'] ) ? sanitize_text_field(wp_unslash( $_POST['event_start_hour'] ) ) : '';
		$event_start_minute   = isset( $_POST['event_start_minute'] ) ? sanitize_text_field( wp_unslash( $_POST['event_start_minute'] ) ) : '';
		$event_start_meridian = isset( $_POST['event_start_meridian'] ) ? sanitize_text_field( wp_unslash( $_POST['event_start_meridian'] ) ) : '';
		$event_end_hour       = isset( $_POST['event_end_hour'] ) ? sanitize_text_field( wp_unslash( $_POST['event_end_hour'] ) ) : '';
		$event_end_minute     = isset( $_POST['event_end_minute'] ) ? sanitize_text_field( wp_unslash( $_POST['event_end_minute'] ) ) : '';
		$event_end_meridian   = isset( $_POST['event_end_meridian'] ) ? sanitize_text_field( wp_unslash( $_POST['event_end_meridian'] ) ) : '';

		$start_time = $event_start_date.' '.$event_start_hour.':'.$event_start_minute.' '.$event_start_meridian;
		$end_time = $event_end_date.' '.$event_end_hour.':'.$event_end_minute.' '.$event_end_meridian;
		$start_ts = strtotime( $start_time );
		$end_ts = strtotime( $end_time );

		// Venue Deatails
		$venue_name    = isset( $_POST['venue_name'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_name'] ) ) : '';
		$venue_address = isset( $_POST['venue_address'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_address'] ) ) : '';
		$venue_city    = isset( $_POST['venue_city'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_city'] ) ) : '';
		$venue_state   = isset( $_POST['venue_state'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_state'] ) ) : '';
		$venue_country = isset( $_POST['venue_country'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_country'] ) ) : '';
		$venue_zipcode = isset( $_POST['venue_zipcode'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_zipcode'] ) ) : '';
		$venue_lat     = isset( $_POST['venue_lat'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_lat'] ) ) : '';
		$venue_lon     = isset( $_POST['venue_lon'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_lon'] ) ) : '';
		$venue_url     = isset( $_POST['venue_url'] ) ? esc_url( wp_unslash( $_POST['venue_url'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Oraganizer Deatails
		$organizer_name  = isset( $_POST['organizer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['organizer_name'] ) ) : '';
		$organizer_email = isset( $_POST['organizer_email'] ) ? sanitize_text_field( wp_unslash( $_POST['organizer_email'] ) ) : '';
		$organizer_phone = isset( $_POST['organizer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['organizer_phone'] ) ) : '';
		$organizer_url   = isset( $_POST['organizer_url'] ) ? esc_url( wp_unslash(  $_POST['organizer_url'] ) ) : '';  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Event Source Link
		$ime_event_link   = isset( $_POST['ime_event_link'] ) ? esc_url( wp_unslash( $_POST['ime_event_link'] ) ) : '';  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Save Event Data
		// Date & Time
		update_post_meta( $post_id, 'event_start_date', $event_start_date );
		update_post_meta( $post_id, 'event_start_hour', $event_start_hour );
		update_post_meta( $post_id, 'event_start_minute', $event_start_minute );
		update_post_meta( $post_id, 'event_start_meridian', $event_start_meridian );
		update_post_meta( $post_id, 'event_end_date', $event_end_date );
		update_post_meta( $post_id, 'event_end_hour', $event_end_hour );
		update_post_meta( $post_id, 'event_end_minute', $event_end_minute );
		update_post_meta( $post_id, 'event_end_meridian', $event_end_meridian );
		update_post_meta( $post_id, 'start_ts', $start_ts );
		update_post_meta( $post_id, 'end_ts', $end_ts );

		// Venue
		update_post_meta( $post_id, 'venue_name', $venue_name );
		update_post_meta( $post_id, 'venue_address', $venue_address );
		update_post_meta( $post_id, 'venue_city', $venue_city );
		update_post_meta( $post_id, 'venue_state', $venue_state );
		update_post_meta( $post_id, 'venue_country', $venue_country );
		update_post_meta( $post_id, 'venue_zipcode', $venue_zipcode );
		update_post_meta( $post_id, 'venue_lat', $venue_lat );
		update_post_meta( $post_id, 'venue_lon', $venue_lon );
		update_post_meta( $post_id, 'venue_url', $venue_url );

		// Organizer
		update_post_meta( $post_id, 'organizer_name', $organizer_name );
		update_post_meta( $post_id, 'organizer_email', $organizer_email );
		update_post_meta( $post_id, 'organizer_phone', $organizer_phone );
		update_post_meta( $post_id, 'organizer_url', $organizer_url );

		// Event Source Link
		update_post_meta( $post_id, 'ime_event_link', $ime_event_link );
	}

	/**
	 * Add column to event listing in admin
	 */ 
	function meetup_events_columns( $cols ) {
		$cols['event_start_date'] = __('Event Date', 'import-meetup-events');
		return $cols;
	}

	/**
	 * set column data
	 */ 
	function meetup_events_columns_data( $column, $post_id ) {
		switch ( $column ) {
			case "event_start_date":
				$start_date = get_post_meta( $post_id, 'event_start_date', true);
				if( $start_date != '' ){
					$start_date = strtotime( $start_date );
					echo esc_attr( gmdate( 'F j, Y', $start_date ) );	
				}else{
					echo '-';
				}

			break;
		}
	}

	/**
	 * render event information above event content
	 */
	function meetup_events_meta_before_content( $content ) { 
		if ( is_singular( $this->event_posttype ) ) {
			$event_details = $this->meetup_events_get_event_meta( get_the_ID() );
			$content = $content . $event_details;
		}
		return $content;
	}

	/**
	 * get meta information for event.
	 */
	function meetup_events_get_event_meta( $event_id = '' ){	

		ob_start();
			
			include IME_PLUGIN_DIR . '/templates/event-meta.php';

		$event_meta_details = ob_get_contents();
		ob_end_clean();
		return $event_meta_details;		
	}

	/**
	 * render events lisiting.
	 */
	public function meetup_events_archive( $atts = array() ){
		//[meetup_events layout="style2" col='2' posts_per_page='12' category="cat1,cat2" past_events="yes" order="desc" orderby="" start_date="" end_date="" ]
		$current_date = current_time( 'timestamp' );
		$ajaxpagi     = isset( $atts['ajaxpagi'] ) ? $atts['ajaxpagi'] : '';
		if ( $ajaxpagi != 'yes' ) {
			$paged        = ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 );
			if ( is_front_page() ) {
				$paged = ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );
			}
		}else{
			$paged  = isset( $atts['paged'] ) ? $atts['paged'] : 1;
		}

		$eve_args = array(
			'post_type' => 'meetup_events',
			'post_status' => 'publish',
			'meta_key' => 'start_ts', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
			'paged' => $paged,
		);

		// post per page.
		if( isset( $atts['posts_per_page'] ) && $atts['posts_per_page'] != '' && is_numeric( $atts['posts_per_page'] ) ){
			$eve_args['posts_per_page'] = $atts['posts_per_page'];
		}

		// Past Events
		if( ( isset( $atts['start_date'] ) && $atts['start_date'] != '' ) || ( isset( $atts['end_date'] ) && $atts['end_date'] != '') ){
			$start_date_str = $end_date_str = '';
			if( isset( $atts['start_date'] ) && $atts['start_date'] != '' ){
				try {
					$start_date_str = strtotime( sanitize_text_field( $atts['start_date'] ));
				} catch (Exception $e) {
					$start_date_str = '';
				}
			}
			if( isset( $atts['end_date'] ) && $atts['end_date'] != '' ){
				try {
					$end_date_str = strtotime( sanitize_text_field( $atts['end_date'] ));
				} catch (Exception $e) {
					$end_date_str = '';
				}
			}

			if( $start_date_str != '' && $end_date_str != '' ){
				$eve_args['meta_query'] = array(  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							'relation' => 'AND',
							array(
								'key' => 'end_ts',
								'compare' => '>=',
								'value' => $start_date_str,
							),
							array(
								'key' => 'end_ts',
								'compare' => '<=',
								'value' => $end_date_str,
							),
						);
			}elseif( $start_date_str != '' ){
				$eve_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							array(
								'key' => 'end_ts',
								'compare' => '>=',
								'value' => $start_date_str,
							)
						);
			}elseif( $end_date_str != '' ){
				$eve_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							'relation' => 'AND',
							array(
								'key' => 'end_ts',
								'compare' => '>=',
								'value' => strtotime( gmdate( 'Y-m-d' ) ),
							),
							array(
								'key' => 'end_ts',
								'compare' => '<=',
								'value' => $end_date_str,
							),
						);
			}
		}else{
			if( isset( $atts['past_events'] ) && $atts['past_events'] === true ){
				$atts['past_events'] = "yes";
			}
			if( isset( $atts['past_events'] ) && $atts['past_events'] == 'yes' ){
				$eve_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								array(
									'key' => 'end_ts',
									'compare' => '<=',
									'value' => $current_date,
								)
							);
			}else{
				$eve_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								array(
									'key' => 'end_ts',
									'compare' => '>=',
									'value' => $current_date,
								)
						);
			}
		}

		// Category
		if( isset( $atts['category'] ) && $atts['category'] != '' ){
			$categories = explode(',', $atts['category'] );
			$tax_field = 'slug';
			if( is_numeric( implode('', $categories ) ) ){
				$tax_field = 'term_id';
			}
			if( !empty( $categories ) ){
				$eve_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query, WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'taxonomy' => $this->event_category,
						'field'    => $tax_field,
						'terms'    => $categories,
					)
				);
			}
		}

		// Order by
		if( isset( $atts['orderby'] ) && $atts['orderby'] != '' ){
			if( $atts['orderby'] == 'event_start_date' || $atts['orderby'] == 'event_end_date' ){
				if( $atts['orderby'] == 'event_end_date' ){
					$eve_args['meta_key'] = 'end_ts';  //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
				}
				$eve_args['orderby'] = 'meta_value';
			}else{
				$eve_args['orderby'] = sanitize_text_field( $atts['orderby'] );
			}
		}else{
			$eve_args['orderby'] = 'meta_value';
		}
		// Order
		if( isset( $atts['order'] ) && $atts['order'] != '' ){
			if( strtoupper( $atts['order'] ) == 'DESC' || strtoupper( $atts['order'] ) == 'ASC' ){
				$eve_args['order'] = sanitize_text_field( $atts['order'] );
			}
		}else{
			if( isset( $atts['past_events'] ) && $atts['past_events'] === true ){
				$atts['past_events'] = "yes";
			}
			if( isset( $atts['past_events'] ) && $atts['past_events'] == 'yes' && $eve_args['orderby'] == 'meta_value' ){
				$eve_args['order'] = 'DESC';
			}else{
				$eve_args['order'] = 'ASC';
			}
		}


		$col = 2;
		$css_class = 'col-ime-md-6';
		if( isset( $atts['col'] ) && $atts['col'] != '' && is_numeric( $atts['col'] ) ){
			$col = $atts['col'];
			switch ( $col ) {
				case '1':
					$css_class = 'col-ime-md-12';
					break;

				case '2':
					$css_class = 'col-ime-md-6';
					break;

				case '3':
					$css_class = 'col-ime-md-4';
					break;

				case '4':
					$css_class = 'col-ime-md-3';
					break;
				
				default:
					$css_class = 'col-ime-md-4';
					break;
			}
		}

		$meetup_events = new WP_Query( $eve_args );

		$wp_list_events = '';
		/* Start the Loop */
		if( is_front_page() ){
			$curr_paged = $paged;
			global $paged;
			$temp_paged = $paged;
			$paged = $curr_paged;
		}
		$ime_options = get_option( IME_OPTIONS );
		$accent_color = isset( $ime_options['accent_color'] ) ? esc_attr( $ime_options['accent_color'] ) : '#039ED7';
		$direct_link = isset($ime_options['direct_link']) ?  esc_attr( $ime_options['direct_link'] ) : 'no';
		if (!ime_is_pro()) {
			$direct_link = 'no';
		}
		ob_start();
		$classes = 'ime_archive row_grid';
		if( !empty( $atts['className'] ) ){ 
			$classes .= ' ' . $atts['className'];
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-paged="<?php echo esc_attr( $paged ); ?>" data-shortcode='<?php echo wp_json_encode( $atts ); ?>'>
			<?php
			$template_args                = array();
			$template_args['css_class']   = $css_class;
			$template_args['direct_link'] = $direct_link;
			if( $meetup_events->have_posts() ){
				while ( $meetup_events->have_posts() ) : $meetup_events->the_post();
					if( isset( $atts['layout'] ) && $atts['layout'] == 'style2' && ime_is_pro() ){
						get_ime_template( 'ime-archive-content2.php', $template_args );
					}elseif( isset( $atts['layout'] ) && $atts['layout'] == 'style3' ){
						get_ime_template( 'ime-archive-content3.php', $template_args );
					}elseif( isset( $atts['layout'] ) && $atts['layout'] == 'style4' ){
						get_ime_template( 'ime-archive-content4.php', $template_args );	
					}else{
						get_ime_template( 'archive-content.php', $template_args );
					}
				endwhile; // End of the loop.

				if ( isset( $atts['ajaxpagi'] ) && $atts['ajaxpagi'] == 'yes' ) {
					if ( $meetup_events->max_num_pages > 1 ) { ?>
						<div class="col-ime-md-12">
							<nav class="prev-next-posts">
								<div class="prev-posts-link alignright">
									<?php if( $paged < $meetup_events->max_num_pages ) : ?>
										<a href="#" class="ime-next-page" data-page="<?php echo $paged + 1; ?>"><?php esc_attr_e( 'Next Events &raquo;' ); ?></a>
									<?php endif; ?>
								</div>
								<div class="next-posts-link alignleft">
									<?php if( $paged > 1 ) : ?>
										<a href="#" class="ime-prev-page" data-page="<?php echo $paged - 1; ?>"><?php esc_attr_e( '&laquo; Previous Events' ); ?></a>
									<?php endif; ?>
								</div>
							</nav>
						</div>
						<?php
					}
				}else{
					if ( $meetup_events->max_num_pages > 1 ) : // custom pagination
					?>
						<div class="col-ime-md-12">
							<nav class="prev-next-posts">
								<div class="prev-posts-link alignright">
									<?php echo get_next_posts_link( 'Next Events &raquo;', $meetup_events->max_num_pages ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
								<div class="next-posts-link alignleft">
									<?php echo get_previous_posts_link( '&laquo; Previous Events' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							</nav>
						</div>
					<?php
					endif;
				}
			}else{
				echo esc_html( apply_filters( 'ime_no_events_found_message', __( 'There are no upcoming Events at this time.', 'import-meetup-events' ) ) );
			}

			?>
		</div>
		
		<style type="text/css">
			.ime_archive .ime_event .event_date{
				background-color: <?php echo esc_attr( $accent_color ); ?>;
			}
			.ime_archive .ime_event .event_desc .event_title{
				color: <?php echo esc_attr( $accent_color ); ?>;
			}
			.ime-event-count, .ime-event-meta > div, .ime-event-meta > div i, .ime-event-info h4 a:hover {
				color: <?php echo esc_attr( $accent_color ); ?>;
			}
			.ime-theme-btn{
				background: <?php echo esc_attr( $accent_color ); ?>;
			}
			.ime-style4-main-div > div::before {
				background-color: <?php echo esc_attr( $accent_color ); ?>;
			}
		</style>

		<?php
		do_action( 'ime_after_event_list', $meetup_events );
		$wp_list_events = ob_get_contents();
		ob_end_clean();
		wp_reset_postdata();
		if( is_front_page() ){
			global $paged;
			$paged = $temp_paged;
		}
		return $wp_list_events;

	}
}