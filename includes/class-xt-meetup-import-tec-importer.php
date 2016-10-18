<?php
/**
 * The class responsible for import events for meetup.com
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    XT_Meetup_Import
 * @subpackage XT_Meetup_Import/includes
 */
class XT_Meetup_Import_Tec_Importer {

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
		$this->xtmi_load_importer();
	}

	/**
	 * Load the all requred hooks for load and render import settings and Import interface
	 *
	 * @since    1.0.0
	 */
	public function xtmi_load_importer() {
		// Import on init.
		add_action( 'xtmi_tec_run_import', array( $this, 'xtmi_run_importer' ), 100 );
	}

	/**
	 * Run meetup event importer.
	 *
	 * @since    1.0.0
	 * @param int $post_id Options.
	 * @return null/void
	 */
	public function xtmi_run_importer( $post_id = 0 ) {
		$post = get_post( $post_id );
		$xtmi_options = get_option( XTMI_OPTIONS, array() );
		$xtmi_api_key = isset( $xtmi_options['meetup_api_key'] ) ? $xtmi_options['meetup_api_key'] : '';
		if ( ! $post || ! $xtmi_api_key ) {
			return;
		}

		$meetup_group_id = $this->xtmi_fetch_group_slug_from_url( $post->post_title );

		$meetup_api_url = 'https://api.meetup.com/' . $meetup_group_id . '/events?key=' . $xtmi_api_key;
	    $meetup_response = wp_remote_get( $meetup_api_url , array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

		if ( ! is_wp_error( $meetup_response ) ) {
			$meetup_events = json_decode( $meetup_response['body'], true );
			if ( is_array( $meetup_events ) && ! isset( $meetup_events['errors'] ) ) {
				foreach ( $meetup_events as $meetup_event ) {
					$this->xtmi_save_tec_event( $meetup_event, $meetup_group_id, $post_id );
				}
			}
		}
	}

	/**
	 * Save (Create or update) Meetup imported to The Event Calendar Events from a Meetup.com event.
	 *
	 * @since  1.0.0
	 * @param array  $meetup_event Event array get from Meetup.com.
	 * @param string $meetup_group_id Meetup group slug.
	 * @param int    $post_id Meetup Url id.
	 * @return void
	 */
	public function xtmi_save_tec_event( $meetup_event = array(), $meetup_group_id = '', $post_id = null ) {

		if ( ! empty( $meetup_event ) && is_array( $meetup_event ) && array_key_exists( 'id', $meetup_event ) ) {

			$is_exitsing_event = $this->xtmi_get_event_by_meetup_event_id( $meetup_event['id'] );
			$formated_args = $this->xtmi_format_event_args_for_tec( $meetup_event, $meetup_group_id );

			if ( $is_exitsing_event ) {
				// Update event using TEC advanced functions if already exits.
				$xtmi_options = get_option( XTMI_OPTIONS, array() );
				$update_events = isset( $xtmi_options['update_events'] ) ? $xtmi_options['update_events'] : 'yes';
				if ( 'yes' == $update_events ) {
					tribe_update_event( $is_exitsing_event, $formated_args );
					do_action( 'xtmi_after_update_meetup_event', $is_exitsing_event, $formated_args );
				}
			} else {
				$this->xtmi_create_meetup_event( $meetup_event, $formated_args, $post_id );
			}
		}
	}

	/**
	 * Create New meetup event.
	 *
	 * @since    1.0.0
	 * @param array $meetup_event Meetup event.
	 * @param array $formated_args Formated arguments for meetup event.
	 * @param int   $post_id Post id.
	 * @return void
	 */
	public function xtmi_create_meetup_event( $meetup_event = array(), $formated_args = array(), $post_id = null  ) {
		// Create event using TEC advanced functions.
		$new_event_id = tribe_create_event( $formated_args );
		if ( $new_event_id ) {
			update_post_meta( $new_event_id, 'xtmi_meetup_event_id', absint( $meetup_event['id'] ) );
			update_post_meta( $new_event_id, 'xtmi_meetup_event_link', esc_url( $meetup_event['link'] ) );
			update_post_meta( $new_event_id, 'xtmi_meetuop_response_raw_data', wp_json_encode( $meetup_event ) );

			// Asign event category.
			$event_cats = get_the_terms( $post_id, 'tribe_events_cat' );
			if ( ! is_wp_error( $event_cats ) ) {
				$cat_ids = array();
				if ( ! empty( $event_cats ) ) {
					foreach ( $event_cats as $event_cat ) {
						$cat_ids[] = $event_cat->term_id;
					}
				}
				if ( ! empty( $cat_ids ) ) {
					wp_set_object_terms( $new_event_id, $cat_ids, 'tribe_events_cat' );
				}
			}

			do_action( 'xtmi_after_create_meetup_event', $new_event_id, $formated_args, $meetup_event );
		}
	}

	/**
	 * Fetch group slug from group url.
	 *
	 * @since    1.0.0
	 * @param array $meetup_event Meetup event.
	 * @return array
	 */
	public function xtmi_format_event_args_for_tec( $meetup_event ) {

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
		$xtmi_options = get_option( XTMI_OPTIONS, array() );
		$default_status = isset( $xtmi_options['default_status'] ) ? $xtmi_options['default_status'] : 'pending';
		$post_type = 'tribe_events';
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			$post_type = Tribe__Events__Main::POSTTYPE;
		}

		$event_args  = array(
			'post_type'             => $post_type,
			'post_title'            => array_key_exists( 'name', $meetup_event ) ? sanitize_text_field( $meetup_event['name'] ) : '',
			'post_status'           => $default_status,
			'post_content'          => array_key_exists( 'description', $meetup_event ) ? $meetup_event['description'] : '',
			'EventStartDate'        => date( 'Y-m-d', $event_start_time ),
			'EventStartHour'        => date( 'h', $event_start_time ),
			'EventStartMinute'      => date( 'i', $event_start_time ),
			'EventStartMeridian'    => date( 'a', $event_start_time ),
			'EventEndDate'          => date( 'Y-m-d', $event_end_time ),
			'EventEndHour'          => date( 'h', $event_end_time ),
			'EventEndMinute'        => date( 'i', $event_end_time ),
			'EventEndMeridian'      => date( 'a', $event_end_time ),
			'EventStartDateUTC'     => date( 'Y-m-d H:i:s', $event_start_time_utc ),
			'EventEndDateUTC'       => date( 'Y-m-d H:i:s', $event_end_time_utc ),
			'EventURL'              => array_key_exists( 'link', $meetup_event ) ? $meetup_event['link'] : '',
			//'FeaturedImage'         => $this->get_featured_image( $event_id, $record ),
		);

		if ( array_key_exists( 'group', $meetup_event ) ) {
			$event_args['organizer'] = $this->xtmi_get_organizer_args( $meetup_event );
		}

		if ( array_key_exists( 'venue', $meetup_event ) ) {
			$event_args['venue'] = $this->xtmi_get_venue_args( $meetup_event );
		}

		return $event_args;
	}

	/**
	 * Get organizer args for event
	 *
	 * @since    1.0.0
	 * @param array $meetup_event Meetup event.
	 * @return array
	 */
	public function xtmi_get_organizer_args( $meetup_event ) {
		if ( ! array_key_exists( 'group', $meetup_event ) ) {
			return null;
		}
		$event_organizer = $meetup_event['group'];
		$post_type = 'tribe_organizer';
		if ( class_exists( 'Tribe__Events__Organizer' ) ) {
			$post_type = Tribe__Events__Organizer::POSTTYPE;
		}
		$existing_organizer = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => $post_type,
			'meta_key' => 'xtmi_meetup_event_organizer_id',
			'meta_value' => $event_organizer['id'],
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_organizer ) && ! empty( $existing_organizer ) ) {
			return array(
				'OrganizerID' => $existing_organizer[0]->ID,
			);
		}

		$creat_organizer = tribe_create_organizer( array(
			'Organizer' => ( $event_organizer['name'] ) ? $event_organizer['name'] : '',
		) );

		if ( $creat_organizer ) {
			update_post_meta( $creat_organizer, 'xtmi_meetup_event_organizer_id', $event_organizer['id'] );
			return array(
				'OrganizerID' => $creat_organizer,
			);
		}

		return null;
	}

	/**
	 * Get venue args for event
	 *
	 * @since    1.0.0
	 * @param array $meetup_event Meetup event.
	 * @return array
	 */
	public function xtmi_get_venue_args( $meetup_event ) {
		if ( ! array_key_exists( 'venue', $meetup_event ) ) {
			return null;
		}
		$event_venue = $meetup_event['venue'];
		$post_type = 'tribe_venue';
		if ( class_exists( 'Tribe__Events__Venue' ) ) {
			$post_type = Tribe__Events__Venue::POSTTYPE;
		}

		$existing_venue = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => $post_type,
			'meta_key' => 'xtmi_meetup_event_venue_id',
			'meta_value' => $event_venue['id'],
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return array(
				'VenueID' => $existing_venue[0]->ID,
			);
		}

		$crate_venue = tribe_create_venue( array(
			'Venue' => $event_venue['name'],
			'Address' => ( $event_venue['address_1'] ) ? $event_venue['address_1'] : '',
			'City' => ( $event_venue['city'] ) ? $event_venue['city'] : '',
			'State' => ( $event_venue['state'] ) ? $event_venue['state'] : '',
			'Country' => ( $event_venue['country'] ) ? strtoupper( $event_venue['country'] ) : '',
			'Zip' => ( $event_venue['zip'] ) ? $event_venue['zip'] : '',
			'Phone' => ( $event_venue['phone'] ) ? $event_venue['phone'] : '',
		) );

		if ( $crate_venue ) {
			update_post_meta( $crate_venue, 'xtmi_meetup_event_venue_id', $event_venue['id'] );
			return array(
				'VenueID' => $crate_venue,
			);
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
	public function xtmi_get_event_by_meetup_event_id( $meetup_event_id ) {
		$event_args = array(
			'post_type' => 'tribe_events',
			'post_status' => array( 'pending', 'draft', 'publish' ),
			'posts_per_page' => -1,
			'meta_key'   => 'xtmi_meetup_event_id',
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
	public function xtmi_fetch_group_slug_from_url( $url = '' ) {
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
