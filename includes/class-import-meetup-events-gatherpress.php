<?php
/**
 * Class for Import Events into GatherPress
 *
 * GatherPress stores event date/time via a single 'gatherpress_datetime' JSON
 * postmeta key that its own `wp_after_insert_post` hook expands into a
 * custom DB table (`{$wpdb->prefix}gatherpress_events`) plus read-only mirror
 * postmeta. We pass that key through wp_insert_post()'s `meta_input` so it is
 * already present by the time GatherPress's own hook runs on the same
 * request - no direct DB writes and no duplicated GatherPress logic.
 *
 * Venues are real `gatherpress_venue` posts. GatherPress auto-creates a
 * hidden "shadow" taxonomy term (`_gatherpress_venue`) for every venue post
 * on save; assigning a venue to an event is done by attaching that term to
 * the event with wp_set_object_terms(), never by writing venue data as
 * event meta.
 *
 * GatherPress has no Organizer post type or taxonomy - "organizer" is simply
 * the event's post_author. We default post_author to the configured import
 * author and keep Meetup's raw organizer details (name/email/url/photo) as
 * informational meta only, matching this plugin's existing 'ime_*' meta
 * convention used by the other adapters.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Meetup_Events
 * @subpackage Import_Meetup_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Import_Meetup_Events_GatherPress {

	// GatherPress Event Posttype
	protected $event_posttype;

	// GatherPress Venue Posttype
	protected $venue_posttype;

	// GatherPress hidden venue "shadow" taxonomy
	protected $venue_taxonomy;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		// GatherPress doesn't define legacy string constants for these, so we
		// fall back to the documented literals when the namespaced classes
		// aren't loaded yet (e.g. plugin not active) - mirrors the defensive
		// `defined()`/class_exists() pattern used by the other adapters.
		if ( class_exists( '\GatherPress\Core\Event\Event' ) ) {
			$this->event_posttype = \GatherPress\Core\Event\Event::POST_TYPE;
		} else {
			$this->event_posttype = 'gatherpress_event';
		}

		if ( class_exists( '\GatherPress\Core\Venue\Venue' ) ) {
			$this->venue_posttype = \GatherPress\Core\Venue\Venue::POST_TYPE;
			$this->venue_taxonomy = \GatherPress\Core\Venue\Venue::TAXONOMY;
		} else {
			$this->venue_posttype = 'gatherpress_venue';
			$this->venue_taxonomy = '_gatherpress_venue';
		}
	}

	/**
	 * Get Posttype and Taxonomy Functions
	 *
	 * @return string
	 */
	public function get_event_posttype(){
		return $this->event_posttype;
	}
	public function get_venue_posttype(){
		return $this->venue_posttype;
	}
	public function get_taxonomy(){
		// GatherPress has no event category taxonomy - returning an empty
		// string tells Common::render_import_into_and_taxonomy() /
		// ime_render_terms_by_plugin() to skip the category dropdown for
		// this target, same as any adapter with nothing to map here.
		return '';
	}

	/**
	 * Import event into GatherPress
	 *
	 * @since    1.0.0
	 * @param  array $centralize_array event array.
	 * @param  array $event_args import options.
	 * @return array
	 */
	public function import_event( $centralize_array, $event_args ){
		global $ime_events;

		if( empty( $centralize_array ) || !isset( $centralize_array['ID'] ) ){
			return false;
		}

		if( !post_type_exists( $this->event_posttype ) ){
			return array(
				'status'  => 0,
				'message' => __( 'GatherPress is not active.', 'import-meetup-events' ),
			);
		}

		// Generate series id and compare (recurring Meetup events - same
		// convention used by every other adapter, since GatherPress has no
		// native recurrence support and each occurrence stays its own post).
		$series_id = '';
		if( $centralize_array['is_series'] == true ){
			$generatedt = isset( $centralize_array['name'] ) ? $ime_events->common->genarate_series_id( $centralize_array['name'] ) : '';
			$starttimel = isset( $centralize_array['starttime_local'] ) ? $centralize_array['starttime_local'] : '';
			$endtimel   = isset( $centralize_array['endtime_local'] ) ? $centralize_array['endtime_local'] : '';
			$series_id  = $generatedt . $starttimel . $endtimel;
		}
		$is_existing_event = $ime_events->common->get_event_by_event_id( $this->event_posttype, $centralize_array['ID'], $series_id );

		if ( $is_existing_event ) {
			// Update event or not?
			$options       = ime_get_import_options( $centralize_array['origin'] );
			$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
			$skip_trash    = isset( $options['skip_trash'] ) ? $options['skip_trash'] : 'no';
			$post_status   = get_post_status( $is_existing_event );
			if ( 'trash' == $post_status && $skip_trash == 'yes' ) {
				return array(
					'status' => 'skip_trash',
					'id'     => $is_existing_event,
				);
			}
			if ( 'yes' != $update_events ) {
				return array(
					'status' => 'skipped',
					'id'     => $is_existing_event
				);
			}
		}

		$post_title       = isset( $centralize_array['name'] ) ? $centralize_array['name'] : '';
		$post_description = isset( $centralize_array['description'] ) ? $centralize_array['description'] : '';
		$start_time       = $centralize_array['starttime_local'];
		$end_time         = $centralize_array['endtime_local'];
		$ticket_uri       = $centralize_array['url'];
		$timezone_name    = $this->get_valid_timezone( isset( $centralize_array['timezone'] ) ? $centralize_array['timezone'] : '' );

		// GatherPress's own hook (`Event\Setup::set_datetimes()`, on
		// `wp_after_insert_post`) reads 'gatherpress_datetime' the moment the
		// post is inserted/updated and writes GatherPress's custom event
		// table + read-only mirror meta from it. Passing it via `meta_input`
		// guarantees it is already saved by the time that hook fires on this
		// same wp_insert_post()/wp_update_post() call - so we never touch
		// the custom table or the mirrored meta ourselves.
		$gatherpress_datetime = wp_json_encode( array(
			'dateTimeStart' => gmdate( 'Y-m-d H:i:s', $start_time ),
			'dateTimeEnd'   => gmdate( 'Y-m-d H:i:s', $end_time ),
			'timezone'      => $timezone_name,
		) );

		$meta_input = array(
			'gatherpress_datetime'    => $gatherpress_datetime,
			'gatherpress_enable_rsvp' => 1,
			'ime_event_id'            => $centralize_array['ID'],
			'ime_series_id'           => $series_id,
			'ime_event_link'          => esc_url_raw( $ticket_uri ),
			'ime_event_origin'        => isset( $event_args['import_origin'] ) ? $event_args['import_origin'] : '',
		);

		// GatherPress online-event link (OPTIONAL SET).
		// Meetup's centralize array carries 'isOnline' (mapped from the
		// GraphQL `eventType` field — typically 'ONLINE' or 'PHYSICAL').
		// When the event isn't purely physical we store the Meetup event
		// URL as the online meeting link; GatherPress uses this meta key
		// to render its "Online Event" block and link attendees.
		$is_online = isset( $centralize_array['isOnline'] ) ? strtoupper( $centralize_array['isOnline'] ) : '';
		if ( '' !== $is_online && 'PHYSICAL' !== $is_online && ! empty( $ticket_uri ) ) {
			$meta_input['gatherpress_online_event_link'] = esc_url_raw( $ticket_uri );
		}

		// GatherPress attendance and guest limits.
		if ( isset( $centralize_array['maxTickets'] ) && '' !== $centralize_array['maxTickets'] ) {
			$meta_input['gatherpress_max_attendance_limit'] = absint( $centralize_array['maxTickets'] );
		}
		if ( isset( $centralize_array['guestLimit'] ) && '' !== $centralize_array['guestLimit'] ) {
			$meta_input['gatherpress_max_guest_limit'] = absint( $centralize_array['guestLimit'] );
		}

		// Keep Meetup's raw organizer details as informational meta only -
		// GatherPress has no per-event organizer fields, see class docblock.
		if ( ! empty( $centralize_array['organizer'] ) && is_array( $centralize_array['organizer'] ) ) {
			$organizer = $centralize_array['organizer'];
			$meta_input['ime_organizer_name']  = isset( $organizer['name'] ) ? sanitize_text_field( $organizer['name'] ) : '';
			$meta_input['ime_organizer_email'] = isset( $organizer['email'] ) ? sanitize_text_field( $organizer['email'] ) : '';
			$meta_input['ime_organizer_url']   = isset( $organizer['url'] ) ? esc_url_raw( $organizer['url'] ) : '';
			$meta_input['ime_organizer_photo'] = isset( $organizer['image_url'] ) ? esc_url_raw( $organizer['image_url'] ) : '';
		}

		$gpeventdata = array(
			'post_title'   => $post_title,
			'post_content' => $post_description,
			'post_type'    => $this->event_posttype,
			'post_status'  => 'pending',
			'post_author'  => isset( $event_args['event_author'] ) ? $event_args['event_author'] : get_current_user_id(),
			'meta_input'   => $meta_input,
		);

		if ( $is_existing_event ) {
			$gpeventdata['ID'] = $is_existing_event;
		}
		if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
			$gpeventdata['post_status'] = $event_args['event_status'];
		}
		if ( $is_existing_event && ! $ime_events->common->ime_is_updatable( 'status' ) ) {
			$gpeventdata['post_status'] = get_post_status( $is_existing_event );
			$event_args['event_status'] = get_post_status( $is_existing_event );
		}

		$inserted_event_id = wp_insert_post( $gpeventdata, true );

		if ( is_wp_error( $inserted_event_id ) ) {
			return array(
				'status'  => 0,
				'message' => $inserted_event_id->get_error_message(),
			);
		}

		$inserted_event = get_post( $inserted_event_id );
		if ( empty( $inserted_event ) ) { return ''; }

		// Assign Featured images - reuses the existing image logic/scheduler
		// shared by every adapter, no duplicated download/sideload code.
		$event_image = isset( $centralize_array['image_url'] ) ? $centralize_array['image_url'] : '';
		if ( $event_image != '' ) {
			$ime_events->common->ime_set_feature_image_logic( $inserted_event_id, $event_image, $event_args );
		} elseif ( $is_existing_event ) {
			delete_post_thumbnail( $inserted_event_id );
		}

		// Venue: create/update the gatherpress_venue post, then attach its
		// shadow-taxonomy term to the event - the only supported way to
		// link a venue to an event in GatherPress.
		if ( ! empty( $centralize_array['location'] ) && is_array( $centralize_array['location'] ) ) {
			$this->assign_venue( $inserted_event_id, $centralize_array['location'] );
		}

		if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
			wp_update_post( array(
				'ID'          => $inserted_event_id,
				'post_status' => sanitize_text_field( $event_args['event_status'] ),
			) );
		}

		if ( $is_existing_event ) {
			do_action( 'ime_after_update_gatherpress_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
			return array(
				'status' => 'updated',
				'id'     => $inserted_event_id
			);
		} else {
			do_action( 'ime_after_create_gatherpress_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
			return array(
				'status' => 'created',
				'id'     => $inserted_event_id
			);
		}
	}

	/**
	 * Create/update the GatherPress venue post for a Meetup venue and
	 * attach it to the event via GatherPress's own shadow taxonomy.
	 *
	 * @since    1.0.0
	 * @param int   $event_id GatherPress event post ID.
	 * @param array $venue    Meetup venue sub-array from the centralize array.
	 * @return int|null Venue post ID, or null when there's nothing to assign.
	 */
	public function assign_venue( $event_id, $venue ) {

		if ( ! isset( $venue['ID'] ) || '' === $venue['ID'] ) {
			return null;
		}

		$existing_venue_id = $this->get_venue_by_id( $venue['ID'] );

		// Fetch default venue template dynamically from GatherPress via WordPress Block Pattern Registry,
		// exactly matching how GatherPress's own maybe_apply_venue_template() method generates content.
		$venue_content = '';
		if ( class_exists( 'WP_Block_Patterns_Registry' ) ) {
			$registry = \WP_Block_Patterns_Registry::get_instance();
			$pattern  = $registry->get_registered( 'gatherpress/venue-template' );

			if ( ! empty( $pattern['content'] ) ) {
				$venue_content = function_exists( 'apply_block_hooks_to_content' ) ? apply_block_hooks_to_content( $pattern['content'], $pattern ) : $pattern['content'];
			}
		}

		if ( empty( $venue_content ) ) {
			$venue_content = '<!-- wp:gatherpress/venue {"patternPicked":true} /-->';
		}

		$venuedata = array(
			'post_title'   => isset( $venue['name'] ) ? sanitize_text_field( $venue['name'] ) : __( 'Untitled Venue', 'import-meetup-events' ),
			'post_content' => $venue_content,
			'post_type'    => $this->venue_posttype,
			'post_status'  => 'publish',
			'meta_input'  => array(
				// gatherpress_address is freeform and editor-writable.
				// city/state/country/postcode are read-only in GatherPress -
				// they're derived asynchronously from this address by
				// GatherPress's own geocoding cron, so we don't set them.
				'gatherpress_address'   => isset( $venue['full_address'] ) ? sanitize_text_field( $venue['full_address'] ) : '',
				'gatherpress_latitude'  => isset( $venue['lat'] ) && '' !== $venue['lat'] ? (string) round( (float) $venue['lat'], 6 ) : '',
				'gatherpress_longitude' => isset( $venue['long'] ) && '' !== $venue['long'] ? (string) round( (float) $venue['long'], 6 ) : '',
				'ime_event_venue_id'    => $venue['ID'],
			),
		);

		if ( $existing_venue_id ) {
			$venuedata['ID'] = $existing_venue_id;
		}

		$venue_post_id = wp_insert_post( $venuedata, true );

		if ( is_wp_error( $venue_post_id ) || ! $venue_post_id ) {
			return null;
		}

		$venue_post = get_post( $venue_post_id );
		if ( empty( $venue_post ) ) {
			return null;
		}

		// GatherPress's Shadow_Source primitive auto-creates/renames the
		// hidden term on save_post_gatherpress_venue - the term slug is
		// always '_' . the venue's post_name.
		$term_slug = '_' . $venue_post->post_name;

		wp_set_object_terms( $event_id, $term_slug, $this->venue_taxonomy );

		return $venue_post_id;
	}

	/**
	 * Check for an already-imported GatherPress venue for a given Meetup venue ID.
	 *
	 * @since    1.0.0
	 * @param string $venue_id Meetup venue id.
	 * @return int|false
	 */
	public function get_venue_by_id( $venue_id ) {
		$existing_venue = get_posts( array(
			'posts_per_page'   => 1,
			'post_type'        => $this->venue_posttype,
			'post_status'      => 'any',
			'meta_key'         => 'ime_event_venue_id', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'       => $venue_id,            //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return $existing_venue[0]->ID;
		}
		return false;
	}

	/**
	 * Validate/normalize a timezone string for GatherPress.
	 *
	 * GatherPress's Validate::timezone() requires a real IANA timezone name
	 * (or Etc/UTC) - a bare UTC offset is rejected and the event would fall
	 * back to the em-dash placeholder. Falls back to UTC when the value
	 * resolved by Import_Meetup_Events_Meetup::ime_get_timezone_from_datetime()
	 * isn't a name PHP itself recognizes.
	 *
	 * @since    1.0.0
	 * @param string $timezone Timezone name to validate.
	 * @return string
	 */
	protected function get_valid_timezone( $timezone ) {
		if ( empty( $timezone ) ) {
			return 'UTC';
		}
		try {
			new DateTimeZone( $timezone );
			return $timezone;
		} catch ( Exception $e ) {
			return 'UTC';
		}
	}

}
