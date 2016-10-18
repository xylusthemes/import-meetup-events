<?php
/**
 * The class responsible for import events for meetup.com
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Events_Manager_Meetup_Import
 * @subpackage Events_Manager_Meetup_Import/includes
 */
class XT_Meetup_Import_Em_Importer {

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
		$this->xt_load_importer();
	}

	/**
	 * Load the all requred hooks for load and render import settings and Import interface
	 *
	 * @since    1.0.0
	 */
	public function xt_load_importer() {
		// Import on init.
		add_action( 'xtmi_em_run_import', array( $this, 'xt_run_importer' ), 100 );
	}

	/**
	 * Run meetup event importer.
	 *
	 * @since    1.0.0
	 * @param int $post_id Options.
	 * @return null/void
	 */
	public function xt_run_importer( $post_id = 0 ) {
		$post = get_post( $post_id );
		$xt_options = get_option( XTMI_OPTIONS, array() );
		$xt_api_key = isset( $xt_options['meetup_api_key'] ) ? $xt_options['meetup_api_key'] : '';

		if ( ! $post || ! $xt_api_key ) {
			return;
		}

		$meetup_group_id = $this->xt_fetch_group_slug_from_url( $post->post_title );
		$meetup_api_url = 'https://api.meetup.com/' . $meetup_group_id . '/events?key=' . $xt_api_key;
	    $meetup_response = wp_remote_get( $meetup_api_url , array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

		if ( ! is_wp_error( $meetup_response ) ) {
			$meetup_events = json_decode( $meetup_response['body'], true );
			if ( is_array( $meetup_events ) && ! isset( $event_array['errors'] ) ) {
				foreach ( $meetup_events as $meetup_event ) {
					$this->xt_save_event( $meetup_event, $meetup_group_id, $post_id );
				}
			}
		}
	}

	/**
	 * Fetch group slug from group url.
	 *
	 * @since    1.0.0
	 * @param array $meetup_event Meetup event.
	 * @return array
	 */
	public function xt_save_event( $meetup_event = array(), $meetup_group_id = '', $post_id = null ) {

		if ( ! empty( $meetup_event ) && is_array( $meetup_event ) && array_key_exists( 'id', $meetup_event ) ) {
			$is_exitsing_event = $this->xt_get_event_by_meetup_event_id( $meetup_event['id'] );
			if ( $is_exitsing_event ) {
				// Check weather update existing events or not.
				$xt_options = get_option( XTMI_OPTIONS, array() );
				$update_events = isset( $xt_options['update_events'] ) ? $xt_options['update_events'] : 'yes';
				if ( 'yes' != $update_events ) {
					return;
				}
			}
			global $wpdb;
			// Get event data.
			if ( array_key_exists( 'time', $meetup_event ) ) {
				$event_start_time_utc = floor( $meetup_event['time'] / 1000 );
			} else {
				$event_start_time_utc = time();
			}
			$event_duration = array_key_exists( 'duration', $meetup_event ) ? $meetup_event['duration'] : 0;
			$event_duration = absint( floor( $event_duration / 1000 ) ); // convert to seconds.
			$event_end_time_utc = absint( $event_start_time_utc + $event_duration );
			$utc_offset = array_key_exists( 'utc_offset', $meetup_event ) ? $meetup_event['utc_offset'] : 0;
			$utc_offset = floor( $utc_offset / 1000 );
			$event_start_time = absint( $event_start_time_utc + $utc_offset );
			$event_end_time = absint( $event_end_time_utc + $utc_offset );
			$xt_options = get_option( XTMI_OPTIONS, array() );
			$default_status = isset( $xt_options['default_status'] ) ? $xt_options['default_status'] : 'pending';

			$eventdata = array(
				'post_title'  => array_key_exists( 'name', $meetup_event ) ? sanitize_text_field( $meetup_event['name'] ) : '',
				'post_content' => array_key_exists( 'description', $meetup_event ) ? $meetup_event['description'] : '',
				'post_type'   => XTMI_EM_POSTTYPE,
				'post_status' => $default_status,
			);
			if ( $is_exitsing_event ) {
				$eventdata['ID'] = $is_exitsing_event;
			}

			$event_id = wp_insert_post( $eventdata, true );

			if ( ! is_wp_error( $event_id ) ) {
				$event = get_post( $event_id );
				if ( empty( $event ) ) { return '';}

				// Assign category.
				$event_cats = get_the_terms( $post_id, XTMI_EM_TAXONOMY );
				if ( ! is_wp_error( $event_cats ) ) {
					$cat_ids = array();
					if ( ! empty( $event_cats ) ) {
						foreach ( $event_cats as $event_cat ) {
							$cat_ids[] = $event_cat->term_id;
						}
					}
					if ( ! empty( $cat_ids ) ) {
						wp_set_object_terms( $event_id, $cat_ids, XTMI_EM_TAXONOMY );
					}
				}

				if ( $is_exitsing_event ) {
					$location_id = $this->xt_get_location_args( $meetup_event, $event_id );
				}else{
					$location_id = $this->xt_get_location_args( $meetup_event, false );
				}

				$event_status = null;
				if ( $event->post_status == 'publish' ) { $event_status = 1;}
				if ( $event->post_status == 'pending' ) { $event_status = 0;}
				// Save Meta.
				//update_post_meta( $event_id, '_event_id', 0 );
				update_post_meta( $event_id, '_event_start_time', date( 'H:i:s', $event_start_time ) );
				update_post_meta( $event_id, '_event_end_time', date( 'H:i:s', $event_end_time ) );
				update_post_meta( $event_id, '_event_all_day', 0 );
				update_post_meta( $event_id, '_event_start_date', date( 'Y-m-d', $event_start_time ) );
				update_post_meta( $event_id, '_event_end_date', date( 'Y-m-d', $event_end_time ) );
				update_post_meta( $event_id, '_location_id', $location_id );
				update_post_meta( $event_id, '_event_status', $event_status );
				update_post_meta( $event_id, '_event_private', 0 );
				update_post_meta( $event_id, '_start_ts', str_pad( $event_start_time, 10, 0, STR_PAD_LEFT));
				update_post_meta( $event_id, '_end_ts', str_pad( $event_end_time, 10, 0, STR_PAD_LEFT));
				update_post_meta( $event_id, '_xt_meetup_event_id', absint( $meetup_event['id'] ) );
				update_post_meta( $event_id, '_xt_meetup_event_link', $meetup_event['link'] );
				update_post_meta( $event_id, '_xt_meetuop_response_raw_data', wp_json_encode( $meetup_event ) );

				// Custom table Details
				$event_array = array(
					'post_id' => $event_id,
					'event_slug' => $event->post_name,
					'event_owner' => $event->post_author,
					'event_name' => $event->post_title,
					'event_start_time' => date( 'H:i:s', $event_start_time ),
					'event_end_time' => date( 'H:i:s', $event_end_time ),
					'event_all_day' => 0,
					'event_start_date' => date( 'Y-m-d', $event_start_time ),
					'event_end_date' => date( 'Y-m-d', $event_end_time ),
					'post_content' => $event->post_content,
					'location_id' => $location_id,
					'event_status' => $event_status,
					'event_date_created' => $event->post_date,
				);
				//print_r( $event_array );
				//exit;
				$event_table = ( defined( 'EM_EVENTS_TABLE' ) ? EM_EVENTS_TABLE : $wpdb->prefix . 'em_events' );
				if ( $is_exitsing_event ) {
					$eve_id = get_post_meta( $event_id, '_event_id', true );
					$where = array( 'event_id' => $eve_id );
					$wpdb->update( $event_table , $event_array, $where );
				}else{
					if ( $wpdb->insert( $event_table , $event_array ) ) {
						update_post_meta( $event_id, '_event_id', $wpdb->insert_id );
					}
				}
			}
		}
	}

	/**
	 * Get Location args for event
	 *
	 * @since    1.0.0
	 * @param array $meetup_event Meetup event.
	 * @return array
	 */
	public function xt_get_location_args( $meetup_event, $event_id = false ) {
		global $wpdb;
		if ( ! array_key_exists( 'venue', $meetup_event ) ) {
			return null;
		}
		$event_venue = $meetup_event['venue'];

		$existing_venue = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => XTMI_LOCATION_POSTTYPE,
			'meta_key' => '_xt_meetup_event_location_id',
			'meta_value' => $event_venue['id'],
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) && ! $event_id ) {
			return get_post_meta( $existing_venue[0]->ID, '_location_id', true );
		}

		$title = isset( $event_venue['name'] ) ? sanitize_text_field( $event_venue['name'] ) : esc_html__( 'Unnamed Location', 'events-manager-meetup-import' );

		$locationdata = array(
			'post_title'  => $title,
			'post_content' => '',
			'post_type'   => XTMI_LOCATION_POSTTYPE,
			'post_status' => 'publish',
		);
		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			$locationdata['ID'] = $existing_venue[0]->ID;
		}
		$location_id = wp_insert_post( $locationdata, true );

		if ( ! is_wp_error( $location_id ) ) {

			$blog_id = 0;
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
			}
			$location = get_post( $location_id );
			if ( empty( $location ) ) { return null;}
			// Location information.
			$address = isset( $event_venue['address_1'] ) ? sanitize_text_field( $event_venue['address_1'] )  : '';
			$city = isset( $event_venue['city'] ) ? sanitize_text_field( $event_venue['city'] )  : '';
			$state = isset( $event_venue['state'] ) ? sanitize_text_field( $event_venue['state'] )  : '';
			$country = isset( $event_venue['country'] ) ? strtoupper( sanitize_text_field( $event_venue['country'] ) ) : '';
			$zip = isset( $event_venue['zip'] ) ? sanitize_text_field( $event_venue['zip'] )  : '';
			$lat = isset( $event_venue['lat'] ) ? sanitize_text_field( $event_venue['lat'] )  : '';
			$lon = isset( $event_venue['lon'] ) ? sanitize_text_field( $event_venue['lon'] )  : '';

			// Save metas.
			//update_post_meta( $location_id, '_location_id', 0 );
			update_post_meta( $location_id, '_blog_id', $blog_id );
			update_post_meta( $location_id, '_location_address', $address );
			update_post_meta( $location_id, '_location_town', $city );
			update_post_meta( $location_id, '_location_state', $state );
			update_post_meta( $location_id, '_location_postcode', $zip );
			update_post_meta( $location_id, '_location_region', '' );
			update_post_meta( $location_id, '_location_country', $country );
			update_post_meta( $location_id, '_location_latitude', $lat );
			update_post_meta( $location_id, '_location_longitude', $lon );
			update_post_meta( $location_id, '_location_status', 1 );
			update_post_meta( $location_id, '_xt_meetup_event_location_id', absint( $event_venue['id'] ) );

			$location_array = array(
				'post_id' => $location_id,
				'blog_id' => $blog_id,
				'location_slug' => $location->post_name,
				'location_name' => $location->post_title,
				'location_owner' => $location->post_author,
				'location_address' => $address,
				'location_town' => $city,
				'location_state' => $state,
				'location_postcode' => $zip,
				'location_region' => '',
				'location_country' => $country,
				'location_latitude' => $lat,
				'location_longitude' => $lon,
				'post_content' => $location->post_content,
				'location_status' => 1,
				'location_private' => 0,
			);

			if( defined( 'EM_LOCATIONS_TABLE' ) ){
				$event_location_table = EM_LOCATIONS_TABLE;
			}else{
				$event_location_table = $wpdb->prefix . 'em_locations';
			}


			if( $event_id && is_numeric( $event_id ) ){
				$loc_id = get_post_meta( $event_id, '_location_id', true );
				$where = array( 'location_id' => $loc_id );
				$wpdb->update( $event_location_table , $location_array , $where );
				return $loc_id;
			}else{
				if ( $wpdb->insert( $event_location_table , $location_array ) ) {
					$insert_loc_id = $wpdb->insert_id;
					update_post_meta( $location_id, '_location_id', $insert_loc_id );
					return $insert_loc_id;
				}
			}
		}
		return null;
	}

	/**
	 * Fetch group slug from group url.
	 *
	 * @since    1.0.0
	 * @param int $meetup_event_id Meetup event id.
	 * @return /boolean
	 */
	public function xt_get_event_by_meetup_event_id( $meetup_event_id ) {
		$event_args = array(
			'post_type' => XTMI_EM_POSTTYPE,
			'post_status' => array( 'pending', 'draft', 'publish' ),
			'posts_per_page' => -1,
			'meta_key'   => '_xt_meetup_event_id',
			'meta_value' => $meetup_event_id,
		);

		$events = new WP_Query( $event_args );
		if ( $events->have_posts() ) {
			while ( $events->have_posts() ) {
				$events->the_post();
				return get_the_ID();
			}
		}
		wp_reset_postdata();
		return false;
	}

	/**
	 * Fetch group slug from group url.
	 *
	 * @since    1.0.0
	 * @param string $url Meetup group url.
	 * @return string
	 */
	public function xt_fetch_group_slug_from_url( $url = '' ) {
		$url = str_replace( 'https://www.meetup.com/', '', $url );
		$url = str_replace( 'http://www.meetup.com/', '', $url );

		// Remove last slash and make grab slug upto slash.
		$slash_position = strpos( $url, '/' );
		if ( false !== $slash_position ) {
			$url = substr( $url, 0, $slash_position );
		}
		return $url;
	}
}
