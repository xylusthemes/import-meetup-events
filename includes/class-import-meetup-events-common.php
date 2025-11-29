<?php
/**
 * Common functions class for Import Meetup Events.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Meetup_Events
 * @subpackage Import_Meetup_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Import_Meetup_Events_Common {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_ime_render_terms_by_plugin', array( $this,'ime_render_terms_by_plugin' ) );
		add_action( 'ime_render_pro_notice', array( $this, 'render_pro_notice') );
		add_action( 'admin_init', array( $this, 'ime_check_for_minimum_pro_version' ) );
		add_action( 'admin_init', array( $this, 'ime_redirect_after_activation' ) );
	}	

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function render_import_into_and_taxonomy() {

		$active_plugins = $this->get_active_supported_event_plugins();
		?>	
		<div class="ime-inner-main-section event_plugis_wrapper">
			<div class="ime-inner-section-1" >
				<span class="ime-title-text" >
					<?php esc_attr_e( 'Import into','import-meetup-events' ); ?>
				</span>
			</div>
			<div class="ime-inner-section-2" >
				<select name="event_plugin" class="event_import_plugin">
					<?php
					if( !empty( $active_plugins ) ){
						foreach ($active_plugins as $slug => $name ) {
							?>
							<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_attr( $name ); ?></option>
							<?php
						}
					}
					?>
	            </select>
			</div>
		</div>

		<div class="ime-inner-main-section event_plugis_wrapper">
			<div class="ime-inner-section-1" >
				<span class="ime-title-text" >
					<?php esc_attr_e( 'Event Categories for Event Import','import-meetup-events' ); ?>
					<span class="ime-tooltip">
						<div>
							<svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="ime-circle-question-mark">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
							</svg>
							<span class="ime-popper">
								<?php esc_attr_e( 'These categories are assign to imported event.', 'import-meetup-events' ); ?>
								<div class="ime-popper__arrow"></div>
							</span>
						</div>
					</span>
				</span>
			</div>
			<div class="ime-inner-section-2" >
				<div class="event_taxo_terms_wraper"></div>
			</div>
		</div>

		<?php		

	}

	/**
	 * Redirect after activate the plugin.
	 */
	public function ime_redirect_after_activation() {
		if ( get_option( 'ime_plugin_activated' ) ) {
			delete_option( 'ime_plugin_activated' );
			wp_safe_redirect( admin_url( 'admin.php?page=meetup_import&tab=ime_setup_wizard' ) );
			exit;
		}
	}

	/**
	 * Check if user has minimum pro version.
	 *
	 * @since    1.5.8
	 * @return void
	 */
	public function ime_check_for_minimum_pro_version() {
		if ( defined( 'IMEPRO_VERSION' ) ) {
			if ( version_compare( IMEPRO_VERSION, IME_MIN_PRO_VERSION, '<' ) ) {
				global $ime_warnings;
				$ime_warnings[] = __( 'Your current "Import Meetup Event Pro" add-on is not compatible with Free plugin. Please upgrade Pro to the latest version to work on event importing flawlessly.', 'import-meetup-events' );
			}
		}
	}

	/**
	 * Get do not update data fields
	 *
	 * @since  1.5.3
	 * @return array
	 */
	public function ime_is_updatable( $field = '' ) {
		if ( empty( $field ) ){ return true; }
		if ( !ime_is_pro() ){ return true; }
		$ime_options = get_option( IME_OPTIONS, array() );
		$meetup_options = isset( $ime_options['dont_update'] ) ? $ime_options['dont_update'] : array();
		if ( isset( $meetup_options[$field] ) &&  'yes' == $meetup_options[$field] ){
			return false;
		}
		return true;
	}

	/**
	 * Render Taxonomy Terms based on event import into Selection.
	 *
	 * @since 1.0
	 * @return void
	 */
	function ime_render_terms_by_plugin() {
		global $ime_events;
		$event_plugin  = isset( $_REQUEST['event_plugin'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['event_plugin'] ) ) ) : '' ; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$event_taxonomy = '';
		switch ( $event_plugin ) {
			case 'ime':
				$event_taxonomy = $ime_events->ime->get_taxonomy();
				break;

			case 'tec':
				$event_taxonomy = $ime_events->tec->get_taxonomy();
				break;

			case 'em':
				$event_taxonomy = $ime_events->em->get_taxonomy();
				break;

			case 'eventon':
				$event_taxonomy = $ime_events->eventon->get_taxonomy();
				break;

			case 'event_organizer':
				$event_taxonomy = $ime_events->event_organizer->get_taxonomy();
				break;

			case 'aioec':
				$event_taxonomy = $ime_events->aioec->get_taxonomy();
				break;

			case 'my_calendar':
				$event_taxonomy = $ime_events->my_calendar->get_taxonomy();
				break;

			case 'eventprime':
				$event_taxonomy = $ime_events->eventprime->get_taxonomy();
				break;
			
			default:
				break;
		}
		
		$terms = array();
		if ( $event_taxonomy != '' ) {
			if( taxonomy_exists( $event_taxonomy ) ){
				$terms = get_terms( array( 'taxonomy'   => $event_taxonomy, 'hide_empty' => false, ) );
			}
		}
		if( ! empty( $terms ) ){ ?>
			<select name="event_cats[]" multiple="multiple">
		        <?php foreach ($terms as $term ) { ?>
					<option value="<?php echo esc_attr( $term->term_id ); ?>">
	                	<?php echo esc_attr( $term->name ); ?>                                	
	                </option>
				<?php } ?> 
			</select>
			<?php
		}
		wp_die();
	}

	/**
	 * Get Active supported active plugins.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_active_supported_event_plugins() {

		$supported_plugins = array();
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		// check The Events Calendar active or not if active add it into array.
		if( class_exists( 'Tribe__Events__Main' ) ){
			$supported_plugins['tec'] = __( 'The Events Calendar', 'import-meetup-events' );
		}

		// check Events Manager.
		if( defined( 'EM_VERSION' ) ){
			$supported_plugins['em'] = __( 'Events Manager', 'import-meetup-events' );
		}
		
		// Check event_organizer.
		if( defined( 'EVENT_ORGANISER_VER' ) &&  defined( 'EVENT_ORGANISER_DIR' ) ){
			$supported_plugins['event_organizer'] = __( 'Event Organiser', 'import-meetup-events' );
		}

		// check EventON.
		if( class_exists( 'EventON' ) ){
			$supported_plugins['eventon'] = __( 'EventON', 'import-meetup-events' );
		}

		// check EventPrime.
		if ( class_exists( 'Eventprime_Event_Calendar_Management_Admin' ) ) {
			$supported_plugins['eventprime'] = __( 'EventPrime', 'import-meetup-events' );
		}

		// check All in one Event Calendar
		if( class_exists( 'Ai1ec_Event' ) ){
			$supported_plugins['aioec'] = __( 'All in one Event Calendar', 'import-meetup-events' );
		}

		// check My Calendar
		if ( is_plugin_active( 'my-calendar/my-calendar.php' ) ) {
			$supported_plugins['my_calendar'] = __( 'My Calendar', 'import-meetup-events' );
		}		
		$supported_plugins['ime'] = __( 'Meetup Events', 'import-meetup-events' );
		$supported_plugins        = apply_filters( 'ime_supported_plugins', $supported_plugins );
		return $supported_plugins;
	}

	/**
	 * Setup Featured image to events
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @param int $image_url Image URL
	 * @return void
	 */
	public function setup_featured_image_to_event( $event_id, $image_url = '' ) {
		if ( $image_url == '' ) {
			return;
		}
		$event = get_post( $event_id );
		if( empty( $event ) ){
			return;
		}
		
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		$event_title = $event->post_title;
		//$image = media_sideload_image( $image_url, $event_id, $event_title );
		if ( ! empty( $image_url ) ) {

			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png|webp)\b/i', $image_url, $matches );
			if ( ! $matches ) {
				return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL', 'import-meetup-events' ) );
			}

			$args = array(
				'post_type'   => 'attachment',
				'post_status' => 'any',
				'fields'      => 'ids',
				'meta_query'  => array( // @codingStandardsIgnoreLine.
					array(
						'value' => $image_url,
						'key'   => '_ime_attachment_source',
					),
				),
			);

			$id = 0;
			$ids = get_posts( $args ); // @codingStandardsIgnoreLine.
			if ( $ids ) {
				$id = current( $ids );
			}

			if( $id && $id > 0 ){
				set_post_thumbnail( $event_id, $id );
				return $id;
			}

			$file_array = array();
			$file_array['name'] = $event->ID . '_image_'.basename( $matches[0] );
			
			if( has_post_thumbnail( $event_id ) ){
				$attachment_id = get_post_thumbnail_id( $event_id );
				$attach_filename = basename( get_attached_file( $attachment_id ) );
				if( $attach_filename == $file_array['name'] ){
					return $attachment_id;
				}
			}

			// Download file to temp location.
			$file_array['tmp_name'] = download_url( $image_url );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				return $file_array['tmp_name'];
			}

			// Do the validation and storage stuff.
			$att_id = media_handle_sideload( $file_array, $event_id, $event_title );

			// If error storing permanently, unlink.
			if ( is_wp_error( $att_id ) ) {
				@unlink( $file_array['tmp_name'] );
				return $att_id;
			}

			if ($att_id) {
				set_post_thumbnail($event_id, $att_id);
			}

			// Save attachment source for future reference.
			update_post_meta( $att_id, '_ime_attachment_source', $image_url );

			return $att_id;
		}
	}

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function display_import_success_message( $import_data = array(),$import_args = array(), $schedule_post = '' ) {
		global $ime_success_msg, $ime_errors;

		$import_status = $import_ids = array();
		foreach ($import_data as $key => $value) {
			if( $value['status'] == 'created'){
				$import_status['created'][] = $value;
			}elseif( $value['status'] == 'updated'){
				$import_status['updated'][] = $value;
			}elseif( $value['status'] == 'skipped'){
				$import_status['skipped'][] = $value;
			}elseif( $value['status'] == 'skip_trash'){
				$import_status['skip_trash'][] = $value;
			}else{

			}
			if( isset( $value['id'] ) ){
				$import_ids[] = $value['id'];
			}
		}
		$created = $updated = $skipped = $skipped = 0;
		$created = isset( $import_status['created'] ) ? count( $import_status['created'] ) : 0;
		$updated = isset( $import_status['updated'] ) ? count( $import_status['updated'] ) : 0;
		$skipped = isset( $import_status['skipped'] ) ? count( $import_status['skipped'] ) : 0;
		$skip_trash = isset( $import_status['skip_trash'] ) ? count( $import_status['skip_trash'] ) : 0;
		
		$success_message = esc_html__( 'Event(s) are imported successfully.', 'import-meetup-events' )."<br>";
		if( $created > 0 ){
			// translators: %d: Number of events created.
			$success_message .= "<strong>".sprintf( __( '%d Created', 'import-meetup-events' ), $created )."</strong><br>";
		}
		if( $updated > 0 ){
			// translators: %d: Number of events Updated.
			$success_message .= "<strong>".sprintf( __( '%d Updated', 'import-meetup-events' ), $updated )."</strong><br>";
		}
		if( $skipped > 0 ){
			// translators: %d: Number of events Skipped.
			$success_message .= "<strong>".sprintf( __( '%d Skipped (Already exists)', 'import-meetup-events' ), $skipped ) ."</strong><br>";
		}
		if ( $skip_trash > 0 ) {
			// translators: %d: Number of events creaSkippedted.
			$success_message .= "<strong>" . sprintf( __( '%d Skipped (Already exists in Trash )', 'import-meetup-events' ), $skip_trash ) . "</strong><br>";
		}
		$ime_success_msg[] = $success_message;

		if( $schedule_post != '' && $schedule_post > 0 ){
			$temp_title = get_the_title( $schedule_post );
		}else{
			$temp_title = 'Manual Import';
		}
		
		$insert_args = array(
			'post_type'   => 'ime_import_history',
			'post_status' => 'publish',
			'post_title'  => $temp_title . " - ".ucfirst( $import_args["import_origin"]),
		);
		
		$insert = wp_insert_post( $insert_args, true );
		if ( !is_wp_error( $insert ) ) {
			update_post_meta( $insert, 'import_origin', $import_args["import_origin"] );
			update_post_meta( $insert, 'created', $created );
			update_post_meta( $insert, 'updated', $updated );
			update_post_meta( $insert, 'skipped', $skipped );
			update_post_meta( $insert, 'skip_trash', $skip_trash );
			update_post_meta( $insert, 'import_data', $import_args );
			if( $schedule_post != '' && $schedule_post > 0 ){
				update_post_meta( $insert, 'schedule_import_id', $schedule_post );
			}
		}
	}

	/**
	 * Get Import events into selected destination.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function import_events_into( $centralize_array, $event_args ){
		global $ime_events;
		$import_result = array();
		$event_import_into = isset( $event_args['import_into'] ) ?  $event_args['import_into'] : 'tec';
		switch ( $event_import_into ) {
			case 'ime':
				$import_result = $ime_events->ime->import_event( $centralize_array, $event_args );
				break;

			case 'tec':
				$import_result = $ime_events->tec->import_event( $centralize_array, $event_args );
				break;

			case 'em':
				$import_result = $ime_events->em->import_event( $centralize_array, $event_args );
				break;

			case 'eventon':
				$import_result = $ime_events->eventon->import_event( $centralize_array, $event_args );
				break;
				
			case 'event_organizer':
				$import_result = $ime_events->event_organizer->import_event( $centralize_array, $event_args );
				break;

			case 'aioec':
				$import_result = $ime_events->aioec->import_event( $centralize_array, $event_args );
				break;

			case 'my_calendar':
				$import_result = $ime_events->my_calendar->import_event( $centralize_array, $event_args );
				break;

			case 'eventprime':
				$import_result = $ime_events->eventprime->import_event( $centralize_array, $event_args );
				break;
				
			default:
				break;
		}
		return $import_result;
	}

	/**
	 * Render import Frequency
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function render_import_frequency(){
		?>
		<select name="import_frequency" class="import_frequency" <?php if( !ime_is_pro()){ echo 'disabled="disabled"'; } ?>>
	        <option value='hourly'>
	            <?php esc_html_e( 'Once Hourly','import-meetup-events' ); ?>
	        </option>
	        <option value='twicedaily'>
	            <?php esc_html_e( 'Twice Daily','import-meetup-events' ); ?>
	        </option>
	        <option value="daily" selected="selected">
	            <?php esc_html_e( 'Once Daily','import-meetup-events' ); ?>
	        </option>
	        <option value="weekly" >
	            <?php esc_html_e( 'Once Weekly','import-meetup-events' ); ?>
	        </option>
	        <option value="monthly">
	            <?php esc_html_e( 'Once a Month','import-meetup-events' ); ?>
	        </option>
	    </select>
		<?php
	}

	/**
	 * Render import type, one time or scheduled
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function render_import_type(){
		?>
		<select name="import_type" id="import_type" <?php if( !ime_is_pro()){ echo 'disabled="disabled"'; } ?>>
			<option value="onetime" <?php if( !ime_is_pro()){ echo 'disabled="disabled"'; } ?> ><?php esc_attr_e( 'One-time Import','import-meetup-events' ); ?></option>
			<option value="scheduled" <?php if( !ime_is_pro()){ echo 'disabled="disabled"'; } ?>><?php esc_attr_e( 'Scheduled Import','import-meetup-events' ); ?></option>
	    </select>
	    <span class="hide_frequency">
	    	<?php $this->render_import_frequency(); ?>
	    </span>
	    <?php
	    do_action( 'ime_render_pro_notice' );
	}

	/**
	 * Clean URL.
	 *
	 * @since 1.0.0
	 */
	function clean_url( $url ) {
		
		$url = str_replace( '&amp;#038;', '&', $url );
		$url = str_replace( '&#038;', '&', $url );
		return $url;
		
	}

	/**
	 * Get UTC offset
	 *
	 * @since    1.0.0
	 */
	function get_utc_offset( $datetime ) {
		try {
			$datetime = new DateTime( $datetime );
		} catch ( Exception $e ) {
			return '';
		}

		$timezone = $datetime->getTimezone();
		$offset   = $timezone->getOffset( $datetime ) / 60 / 60;

		if ( $offset >= 0 ) {
			$offset = '+' . $offset;
		}

		return 'UTC' . $offset;
	}

	/**
	 * Render dropdown for Imported event status.
	 *
	 * @since 1.0
	 * @return void
	 */
	function render_eventstatus_input(){
		?>
		<div class="ime-inner-main-section event_status_wrapper">
			<div class="ime-inner-section-1" >
				<span class="ime-title-text" >
					<?php esc_attr_e( 'Status','import-meetup-events' ); ?>
				</span>
			</div>
			<div class="ime-inner-section-2" >
				<select name="event_status" >
	                <option value="publish">
	                    <?php esc_html_e( 'Published','import-meetup-events' ); ?>
	                </option>
	                <option value="pending">
	                    <?php esc_html_e( 'Pending','import-meetup-events' ); ?>
	                </option>
	                <option value="draft">
	                    <?php esc_html_e( 'Draft','import-meetup-events' ); ?>
	                </option>
	            </select>
			</div>
		</div>
		<?php
	}

	/**
	 * remove query string from URL.
	 *
	 * @since 1.0.0
	 */
	function convert_datetime_to_db_datetime( $datetime ) {
		try {
			$datetime = new DateTime( $datetime );
			return $datetime->format( 'Y-m-d H:i:s' );
		}
		catch ( Exception $e ) {
			return $datetime;
		}
	}

	/**
	 * Check for Existing Event
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @return /boolean
	 */
	public function get_event_by_event_id( $post_type, $event_id, $series_id = '' ) {
		global $wpdb;
		$ime_options = get_option( IME_OPTIONS );
		$skip_trash = isset( $ime_options['skip_trash'] ) ? $ime_options['skip_trash'] : 'no';
		if( $skip_trash === 'yes'){
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$get_post_id = $wpdb->get_col(
				$wpdb->prepare(
					'SELECT ' . $wpdb->prefix . 'posts.ID FROM ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta WHERE ' . $wpdb->prefix . 'posts.post_type = %s AND ' . $wpdb->prefix . 'postmeta.post_id = ' . $wpdb->prefix . 'posts.ID AND (' . $wpdb->prefix . 'postmeta.meta_key = %s AND ' . $wpdb->prefix . 'postmeta.meta_value = %s ) LIMIT 1',
					$post_type,
					'ime_event_id',
					$event_id
				)
			);
			if ( empty( $get_post_id[0] ) && !empty( $series_id ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$get_post_id = $wpdb->get_col(
					$wpdb->prepare(
						'SELECT ' . $wpdb->prefix . 'posts.ID FROM ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta WHERE ' . $wpdb->prefix . 'posts.post_type = %s AND ' . $wpdb->prefix . 'postmeta.post_id = ' . $wpdb->prefix . 'posts.ID AND (' . $wpdb->prefix . 'postmeta.meta_key = %s AND ' . $wpdb->prefix . 'postmeta.meta_value = %s ) LIMIT 1',
						$post_type,
						'ime_series_id',
						$series_id
					)
				);
			}
		}else{
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$get_post_id = $wpdb->get_col(
				$wpdb->prepare(
					'SELECT ' . $wpdb->prefix . 'posts.ID FROM ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta WHERE ' . $wpdb->prefix . 'posts.post_type = %s AND ' . $wpdb->prefix . 'postmeta.post_id = ' . $wpdb->prefix . 'posts.ID AND ' . $wpdb->prefix . 'posts.post_status != %s AND (' . $wpdb->prefix . 'postmeta.meta_key = %s AND ' . $wpdb->prefix . 'postmeta.meta_value = %s ) LIMIT 1',
					$post_type,
					'trash',
					'ime_event_id',
					$event_id
				)
			);
			if ( empty( $get_post_id[0] ) && !empty( $series_id ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$get_post_id = $wpdb->get_col(
					$wpdb->prepare(
						'SELECT ' . $wpdb->prefix . 'posts.ID FROM ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta WHERE ' . $wpdb->prefix . 'posts.post_type = %s AND ' . $wpdb->prefix . 'postmeta.post_id = ' . $wpdb->prefix . 'posts.ID AND ' . $wpdb->prefix . 'posts.post_status != %s AND (' . $wpdb->prefix . 'postmeta.meta_key = %s AND ' . $wpdb->prefix . 'postmeta.meta_value = %s ) LIMIT 1',
						$post_type,
						'trash',
						'ime_series_id',
						$series_id
					)
				);
			}
		}

		if ( !empty( $get_post_id[0] ) ) {
			return $get_post_id[0];
		}
		return false;
	}

	/**
	 * Convert event Title to series
	 *
	 * @since    1.6.2
	 */
	function genarate_series_id( $title ) {
		// Convert to lowercase
		$title = strtolower($title);

		// Remove unwanted characters and trim whitespace
		$title = preg_replace("/[^a-z]+/", "", $title);
	
		// Trim any leading or trailing whitespace
		$title = trim($title);
	
		return $title;
	}

	/**
	 * Display upgrade to pro notice in form.
	 *
	 * @since 1.0.0
	 */
	public function render_pro_notice(){
		if( !ime_is_pro() ){
			?>
			<span class="ime-blur-filter-cta">
		        <?php printf( '<span style="color: red">%s</span> <a href="' . esc_url( IME_PLUGIN_BUY_NOW_URL ) . '" target="_blank" >%s</a>', esc_html__( 'Available in Pro version.', 'import-meetup-events' ), esc_html__( 'Upgrade to PRO', 'import-meetup-events' ) ); ?>
		    </span>
			<?php
		}
	}

	/**
	 * Get Active supported active plugins.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function ime_get_country_code( $country ) {
		if( $country == '' ){
			return '';
		}
		
		$countries = array(
		    'AF'=>'AFGHANISTAN',
		    'AL'=>'ALBANIA',
		    'DZ'=>'ALGERIA',
		    'AS'=>'AMERICAN SAMOA',
		    'AD'=>'ANDORRA',
		    'AO'=>'ANGOLA',
		    'AI'=>'ANGUILLA',
		    'AQ'=>'ANTARCTICA',
		    'AG'=>'ANTIGUA AND BARBUDA',
		    'AR'=>'ARGENTINA',
		    'AM'=>'ARMENIA',
		    'AW'=>'ARUBA',
		    'AU'=>'AUSTRALIA',
		    'AT'=>'AUSTRIA',
		    'AZ'=>'AZERBAIJAN',
		    'BS'=>'BAHAMAS',
		    'BH'=>'BAHRAIN',
		    'BD'=>'BANGLADESH',
		    'BB'=>'BARBADOS',
		    'BY'=>'BELARUS',
		    'BE'=>'BELGIUM',
		    'BZ'=>'BELIZE',
		    'BJ'=>'BENIN',
		    'BM'=>'BERMUDA',
		    'BT'=>'BHUTAN',
		    'BO'=>'BOLIVIA',
		    'BA'=>'BOSNIA AND HERZEGOVINA',
		    'BW'=>'BOTSWANA',
		    'BV'=>'BOUVET ISLAND',
		    'BR'=>'BRAZIL',
		    'IO'=>'BRITISH INDIAN OCEAN TERRITORY',
		    'BN'=>'BRUNEI DARUSSALAM',
		    'BG'=>'BULGARIA',
		    'BF'=>'BURKINA FASO',
		    'BI'=>'BURUNDI',
		    'KH'=>'CAMBODIA',
		    'CM'=>'CAMEROON',
		    'CA'=>'CANADA',
		    'CV'=>'CAPE VERDE',
		    'KY'=>'CAYMAN ISLANDS',
		    'CF'=>'CENTRAL AFRICAN REPUBLIC',
		    'TD'=>'CHAD',
		    'CL'=>'CHILE',
		    'CN'=>'CHINA',
		    'CX'=>'CHRISTMAS ISLAND',
		    'CC'=>'COCOS (KEELING) ISLANDS',
		    'CO'=>'COLOMBIA',
		    'KM'=>'COMOROS',
		    'CG'=>'CONGO',
		    'CD'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
		    'CK'=>'COOK ISLANDS',
		    'CR'=>'COSTA RICA',
		    'CI'=>'COTE D IVOIRE',
		    'HR'=>'CROATIA',
		    'CU'=>'CUBA',
		    'CY'=>'CYPRUS',
		    'CZ'=>'CZECH REPUBLIC',
		    'DK'=>'DENMARK',
		    'DJ'=>'DJIBOUTI',
		    'DM'=>'DOMINICA',
		    'DO'=>'DOMINICAN REPUBLIC',
		    'TP'=>'EAST TIMOR',
		    'EC'=>'ECUADOR',
		    'EG'=>'EGYPT',
		    'SV'=>'EL SALVADOR',
		    'GQ'=>'EQUATORIAL GUINEA',
		    'ER'=>'ERITREA',
		    'EE'=>'ESTONIA',
		    'ET'=>'ETHIOPIA',
		    'FK'=>'FALKLAND ISLANDS (MALVINAS)',
		    'FO'=>'FAROE ISLANDS',
		    'FJ'=>'FIJI',
		    'FI'=>'FINLAND',
		    'FR'=>'FRANCE',
		    'GF'=>'FRENCH GUIANA',
		    'PF'=>'FRENCH POLYNESIA',
		    'TF'=>'FRENCH SOUTHERN TERRITORIES',
		    'GA'=>'GABON',
		    'GM'=>'GAMBIA',
		    'GE'=>'GEORGIA',
		    'DE'=>'GERMANY',
		    'GH'=>'GHANA',
		    'GI'=>'GIBRALTAR',
		    'GR'=>'GREECE',
		    'GL'=>'GREENLAND',
		    'GD'=>'GRENADA',
		    'GP'=>'GUADELOUPE',
		    'GU'=>'GUAM',
		    'GT'=>'GUATEMALA',
		    'GN'=>'GUINEA',
		    'GW'=>'GUINEA-BISSAU',
		    'GY'=>'GUYANA',
		    'HT'=>'HAITI',
		    'HM'=>'HEARD ISLAND AND MCDONALD ISLANDS',
		    'VA'=>'HOLY SEE (VATICAN CITY STATE)',
		    'HN'=>'HONDURAS',
		    'HK'=>'HONG KONG',
		    'HU'=>'HUNGARY',
		    'IS'=>'ICELAND',
		    'IN'=>'INDIA',
		    'ID'=>'INDONESIA',
		    'IR'=>'IRAN, ISLAMIC REPUBLIC OF',
		    'IQ'=>'IRAQ',
		    'IE'=>'IRELAND',
		    'IL'=>'ISRAEL',
		    'IT'=>'ITALY',
		    'JM'=>'JAMAICA',
		    'JP'=>'JAPAN',
		    'JO'=>'JORDAN',
		    'KZ'=>'KAZAKSTAN',
		    'KE'=>'KENYA',
		    'KI'=>'KIRIBATI',
		    'KP'=>'KOREA DEMOCRATIC PEOPLES REPUBLIC OF',
		    'KR'=>'KOREA REPUBLIC OF',
		    'KW'=>'KUWAIT',
		    'KG'=>'KYRGYZSTAN',
		    'LA'=>'LAO PEOPLES DEMOCRATIC REPUBLIC',
		    'LV'=>'LATVIA',
		    'LB'=>'LEBANON',
		    'LS'=>'LESOTHO',
		    'LR'=>'LIBERIA',
		    'LY'=>'LIBYAN ARAB JAMAHIRIYA',
		    'LI'=>'LIECHTENSTEIN',
		    'LT'=>'LITHUANIA',
		    'LU'=>'LUXEMBOURG',
		    'MO'=>'MACAU',
		    'MK'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
		    'MG'=>'MADAGASCAR',
		    'MW'=>'MALAWI',
		    'MY'=>'MALAYSIA',
		    'MV'=>'MALDIVES',
		    'ML'=>'MALI',
		    'MT'=>'MALTA',
		    'MH'=>'MARSHALL ISLANDS',
		    'MQ'=>'MARTINIQUE',
		    'MR'=>'MAURITANIA',
		    'MU'=>'MAURITIUS',
		    'YT'=>'MAYOTTE',
		    'MX'=>'MEXICO',
		    'FM'=>'MICRONESIA, FEDERATED STATES OF',
		    'MD'=>'MOLDOVA, REPUBLIC OF',
		    'MC'=>'MONACO',
		    'MN'=>'MONGOLIA',
		    'MS'=>'MONTSERRAT',
		    'MA'=>'MOROCCO',
		    'MZ'=>'MOZAMBIQUE',
		    'MM'=>'MYANMAR',
		    'NA'=>'NAMIBIA',
		    'NR'=>'NAURU',
		    'NP'=>'NEPAL',
		    'NL'=>'NETHERLANDS',
		    'AN'=>'NETHERLANDS ANTILLES',
		    'NC'=>'NEW CALEDONIA',
		    'NZ'=>'NEW ZEALAND',
		    'NI'=>'NICARAGUA',
		    'NE'=>'NIGER',
		    'NG'=>'NIGERIA',
		    'NU'=>'NIUE',
		    'NF'=>'NORFOLK ISLAND',
		    'MP'=>'NORTHERN MARIANA ISLANDS',
		    'NO'=>'NORWAY',
		    'OM'=>'OMAN',
		    'PK'=>'PAKISTAN',
		    'PW'=>'PALAU',
		    'PS'=>'PALESTINIAN TERRITORY, OCCUPIED',
		    'PA'=>'PANAMA',
		    'PG'=>'PAPUA NEW GUINEA',
		    'PY'=>'PARAGUAY',
		    'PE'=>'PERU',
		    'PH'=>'PHILIPPINES',
		    'PN'=>'PITCAIRN',
		    'PL'=>'POLAND',
		    'PT'=>'PORTUGAL',
		    'PR'=>'PUERTO RICO',
		    'QA'=>'QATAR',
		    'RE'=>'REUNION',
		    'RO'=>'ROMANIA',
		    'RU'=>'RUSSIAN FEDERATION',
		    'RW'=>'RWANDA',
		    'SH'=>'SAINT HELENA',
		    'KN'=>'SAINT KITTS AND NEVIS',
		    'LC'=>'SAINT LUCIA',
		    'PM'=>'SAINT PIERRE AND MIQUELON',
		    'VC'=>'SAINT VINCENT AND THE GRENADINES',
		    'WS'=>'SAMOA',
		    'SM'=>'SAN MARINO',
		    'ST'=>'SAO TOME AND PRINCIPE',
		    'SA'=>'SAUDI ARABIA',
		    'SN'=>'SENEGAL',
		    'SC'=>'SEYCHELLES',
		    'SL'=>'SIERRA LEONE',
		    'SG'=>'SINGAPORE',
		    'SK'=>'SLOVAKIA',
		    'SI'=>'SLOVENIA',
		    'SB'=>'SOLOMON ISLANDS',
		    'SO'=>'SOMALIA',
		    'ZA'=>'SOUTH AFRICA',
		    'GS'=>'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
		    'ES'=>'SPAIN',
		    'LK'=>'SRI LANKA',
		    'SD'=>'SUDAN',
		    'SR'=>'SURINAME',
		    'SJ'=>'SVALBARD AND JAN MAYEN',
		    'SZ'=>'SWAZILAND',
		    'SE'=>'SWEDEN',
		    'CH'=>'SWITZERLAND',
		    'SY'=>'SYRIAN ARAB REPUBLIC',
		    'TW'=>'TAIWAN, PROVINCE OF CHINA',
		    'TJ'=>'TAJIKISTAN',
		    'TZ'=>'TANZANIA, UNITED REPUBLIC OF',
		    'TH'=>'THAILAND',
		    'TG'=>'TOGO',
		    'TK'=>'TOKELAU',
		    'TO'=>'TONGA',
		    'TT'=>'TRINIDAD AND TOBAGO',
		    'TN'=>'TUNISIA',
		    'TR'=>'TURKEY',
		    'TM'=>'TURKMENISTAN',
		    'TC'=>'TURKS AND CAICOS ISLANDS',
		    'TV'=>'TUVALU',
		    'UG'=>'UGANDA',
		    'UA'=>'UKRAINE',
		    'AE'=>'UNITED ARAB EMIRATES',
		    'GB'=>'UNITED KINGDOM',
		    'US'=>'UNITED STATES',
		    'UM'=>'UNITED STATES MINOR OUTLYING ISLANDS',
		    'UY'=>'URUGUAY',
		    'UZ'=>'UZBEKISTAN',
		    'VU'=>'VANUATU',
		    'VE'=>'VENEZUELA',
		    'VN'=>'VIET NAM',
		    'VG'=>'VIRGIN ISLANDS, BRITISH',
		    'VI'=>'VIRGIN ISLANDS, U.S.',
		    'WF'=>'WALLIS AND FUTUNA',
		    'EH'=>'WESTERN SAHARA',
		    'YE'=>'YEMEN',
		    'YU'=>'YUGOSLAVIA',
		    'ZM'=>'ZAMBIA',
		    'ZW'=>'ZIMBABWE',
		  );

		foreach ($countries as $code => $name ) {
			if( strtoupper( $country) == $name ){
				return $code;
			}
		}
		return $country;
	}

	/**
	 * Converts a given date and time in a specific timezone to a UNIX timestamp in UTC.
	 *
	 * @param string $datetime The date and time string 
	 * @param string $timezone The timezone of the given date and time
	 * @return int UNIX timestamp in UTC.
	 */
	public function ime_convert_to_utc_timestamp( $datetime, $timezone ) {
		try {
			$date = new DateTime( $datetime, new DateTimeZone( $timezone ) );
			$date->setTimezone( new DateTimeZone( 'UTC' ) );
			return $date->getTimestamp();
		} catch ( Exception $e ) {
			return 0;
		}
	}
	
	/*
     * Create missing Scheduled Import
     *
     * @param int $post_id Post id.
     */
    public function ime_recreate_missing_schedule_import( $post_id ){

        $si_data           = get_post_meta( $post_id, 'import_eventdata', true );
        $import_frequency  = ( $si_data['import_frequency'] ) ? $si_data['import_frequency'] : 'not_repeat';
        $cron_time         = time() - (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

        if( $import_frequency !== 'not_repeat' ) {
            $scheduled = wp_schedule_event( $cron_time, $import_frequency, 'ime_run_scheduled_import', array( 'post_id' => $post_id ) );
        }
    }
	
	/**
	 * Render Page header Section
	 *
	 * @since 1.1
	 * @return void
	 */
	function ime_render_common_header( $page_title  ){
		?>
		<div class="ime-header" >
			<div class="ime-container" >
				<div class="ime-header-content" >
					<span style="font-size:18px;"><?php esc_attr_e('Dashboard','import-meetup-events'); ?></span>
					<span class="spacer"></span>
					<span class="page-name"><?php echo esc_attr( $page_title ); ?></span></span>
					<div class="header-actions" >
						<span class="round">
							<a href="<?php echo esc_url( 'https://docs.xylusthemes.com/docs/import-meetup-events/' ); ?>" target="_blank">
								<svg viewBox="0 0 20 20" fill="#000000" height="20px" xmlns="http://www.w3.org/2000/svg" class="ime-circle-question-mark">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
								</svg>
							</a>
						</span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Page Footer Section
	 *
	 * @since 1.1
	 * @return void
	 */
	function ime_render_common_footer(){
		?>
			<div id="ime-footer-links" >
				<div class="ime-footer">
					<div><?php esc_attr_e( 'Made with ♥ by the Xylus Themes','import-meetup-events'); ?></div>
					<div class="ime-links" >
						<a href="<?php echo esc_url( 'https://xylusthemes.com/support/' ); ?>" target="_blank" ><?php esc_attr_e( 'Support','import-meetup-events'); ?></a>
						<span>/</span>
						<a href="<?php echo esc_url( 'https://docs.xylusthemes.com/docs/import-meetup-events' ); ?>" target="_blank" ><?php esc_attr_e( 'Docs','import-meetup-events'); ?></a>
						<span>/</span>
						<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=xylus&tab=search&type=term' ) ); ?>" ><?php esc_attr_e( 'Free Plugins','import-meetup-events'); ?></a>
					</div>
					<div class="ime-social-links">
						<a href="<?php echo esc_url( 'https://www.facebook.com/xylusinfo/' ); ?>" target="_blank" >
							<svg class="ime-facebook">
								<path fill="currentColor" d="M16 8.05A8.02 8.02 0 0 0 8 0C3.58 0 0 3.6 0 8.05A8 8 0 0 0 6.74 16v-5.61H4.71V8.05h2.03V6.3c0-2.02 1.2-3.15 3-3.15.9 0 1.8.16 1.8.16v1.98h-1c-1 0-1.31.62-1.31 1.27v1.49h2.22l-.35 2.34H9.23V16A8.02 8.02 0 0 0 16 8.05Z"></path>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://www.linkedin.com/company/xylus-consultancy-service-xcs-/' ); ?>" target="_blank" >
							<svg class="ime-linkedin">
								<path fill="currentColor" d="M14 1H1.97C1.44 1 1 1.47 1 2.03V14c0 .56.44 1 .97 1H14a1 1 0 0 0 1-1V2.03C15 1.47 14.53 1 14 1ZM5.22 13H3.16V6.34h2.06V13ZM4.19 5.4a1.2 1.2 0 0 1-1.22-1.18C2.97 3.56 3.5 3 4.19 3c.65 0 1.18.56 1.18 1.22 0 .66-.53 1.19-1.18 1.19ZM13 13h-2.1V9.75C10.9 9 10.9 8 9.85 8c-1.1 0-1.25.84-1.25 1.72V13H6.53V6.34H8.5v.91h.03a2.2 2.2 0 0 1 1.97-1.1c2.1 0 2.5 1.41 2.5 3.2V13Z"></path>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://x.com/XylusThemes" target="_blank' ); ?>" target="_blank" >
							<svg class="ime-twitter" width="24" height="24" viewBox="0 0 24 24">
								<circle cx="12" cy="12" r="12" fill="currentColor"></circle>
								<g>
									<path d="M13.129 11.076L17.588 6H16.5315L12.658 10.4065L9.5665 6H6L10.676 12.664L6 17.9865H7.0565L11.1445 13.332L14.41 17.9865H17.9765L13.129 11.076ZM11.6815 12.7225L11.207 12.0585L7.4375 6.78H9.0605L12.1035 11.0415L12.576 11.7055L16.531 17.2445H14.908L11.6815 12.7225Z" fill="white"></path>
								</g>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://www.youtube.com/@xylussupport7784' ); ?>" target="_blank" >
							<svg class="ime-youtube">
								<path fill="currentColor" d="M16.63 3.9a2.12 2.12 0 0 0-1.5-1.52C13.8 2 8.53 2 8.53 2s-5.32 0-6.66.38c-.71.18-1.3.78-1.49 1.53C0 5.2 0 8.03 0 8.03s0 2.78.37 4.13c.19.75.78 1.3 1.5 1.5C3.2 14 8.51 14 8.51 14s5.28 0 6.62-.34c.71-.2 1.3-.75 1.49-1.5.37-1.35.37-4.13.37-4.13s0-2.81-.37-4.12Zm-9.85 6.66V5.5l4.4 2.53-4.4 2.53Z"></path>
							</svg>
						</a>
					</div>
				</div>
			</div>
		<?php   
	}

	/**
	 * Get Eventbrite Events Counts
	 *
	 * @since 1.5.0
	 * @return array
	 */
	function ime_get_meetup_events_counts() {
		global $wpdb;
	
		// Table names with WordPress prefix
		$posts_table    = $wpdb->prefix . 'posts';
		$postmeta_table = $wpdb->prefix . 'postmeta';
		
		// Current Unix timestamp
		$current_time = current_time( 'timestamp' );
	
		// Single query to get all counts
		$sql = "SELECT 
				COUNT( p.ID ) AS all_posts_count,
				SUM( CASE WHEN pm.meta_value > %d THEN 1 ELSE 0 END ) AS upcoming_events_count,
				SUM( CASE WHEN pm.meta_value <= %d THEN 1 ELSE 0 END ) AS past_events_count
			FROM {$posts_table} AS p
			INNER JOIN {$postmeta_table} AS pm ON p.ID = pm.post_id
			WHERE p.post_type = %s
			AND p.post_status = %s
			AND pm.meta_key = %s";

		$prepared_sql = $wpdb->prepare( $sql, $current_time, $current_time, 'meetup_events', 'publish', 'end_ts' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$counts       = $wpdb->get_row( $prepared_sql );
	
		// Return the counts as an array
		return [
			'all'      => intval( $counts->all_posts_count ),
			'upcoming' => intval( $counts->upcoming_events_count ),
			'past'     => intval( $counts->past_events_count ),
		];
	}

	/**
     * Get Plugin array
     *
     * @since 1.1.0
     * @return array
     */
    public function ime_get_xylus_themes_plugins(){
        return array(
            'wp-bulk-delete' => array( 'plugin_name' => esc_html__( 'WP Bulk Delete', 'import-meetup-events' ), 'description' => 'Delete posts, pages, comments, users, taxonomy terms and meta fields in bulk with different powerful filters and conditions.' ),
            'wp-event-aggregator' => array( 'plugin_name' => esc_html__( 'WP Event Aggregator', 'import-meetup-events' ), 'description' => 'WP Event Aggregator: Easy way to import Facebook Events, Eventbrite events, MeetUp events into your WordPress Event Calendar.' ),
            'import-facebook-events' => array( 'plugin_name' => esc_html__( 'Import Social Events', 'import-meetup-events' ), 'description' => 'Import Facebook events into your WordPress website and/or Event Calendar. Nice Display with shortcode & Event widget.' ),
            'import-eventbrite-events' => array( 'plugin_name' => esc_html__( 'Import Eventbrite Events', 'import-meetup-events' ), 'description' => 'Import Eventbrite Events into WordPress website and/or Event Calendar. Nice Display with shortcode & Event widget.' ),
            'event-schema' => array( 'plugin_name' => esc_html__( 'Event Schema / Structured Data', 'import-meetup-events' ), 'description' => 'Automatically Google Event Rich Snippet Schema Generator. This plug-in generates complete JSON-LD based schema (structured data for Rich Snippet) for events.' ),
            'wp-smart-import' => array( 'plugin_name' => esc_html__( 'WP Smart Import : Import any XML File to WordPress', 'import-meetup-events' ), 'description' => 'The most powerful solution for importing any CSV files to WordPress. Create Posts and Pages any Custom Posttype with content from any CSV file.' ),
            'xylus-events-calendar' => array( 'plugin_name' => esc_html__( 'Easy Events Calendar', 'import-meetup-events' ), 'description' => 'Display upcoming events from multiple sources in a responsive calendar with customizable layouts like grid, row, calendar, masonry, and slider.' ),
            'xt-feed-for-linkedin' => array( 'plugin_name' => esc_html__( 'XT Feed for LinkedIn', 'import-meetup-events' ), 'description' => 'XT Feed for LinkedIn auto-shares WordPress posts to LinkedIn with one click, making content distribution easy and boosting your reach effortlessly.' ),
        );
    }
}

/**
 * Check is pro active or not.
 *
 * @since  1.4.0
 * @return boolean
 */
function ime_is_pro(){
	if( !function_exists( 'is_plugin_active' ) ){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	if ( is_plugin_active( 'import-meetup-events-pro/import-meetup-events-pro.php' ) ) {
		return true;
	}
	if( class_exists('Import_Meetup_Events_Pro', false) ){
		return true;
	}
	return false;
}

/**
 * Gets and includes template files.
 *
 * @since 1.5.6
 * @param mixed  $template_name
 * @param array  $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function get_ime_template( $template_name, $args = array(), $template_path = 'import-meetup-events', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}
	include locate_ime_template( $template_name, $template_path, $default_path );
}

/**
 * Locates a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *      yourtheme       /   $template_path  /   $template_name
 *      yourtheme       /   $template_name
 *      $default_path   /   $template_name
 *
 * @since 1.5.6
 * @param string      $template_name
 * @param string      $template_path (default: 'import-meetup-events')
 * @param string|bool $default_path (default: '') False to not load a default
 * @return string
 */
function locate_ime_template( $template_name, $template_path = 'import-meetup-events', $default_path = '' ) {
	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);
	// Get default template
	if ( ! $template && $default_path !== false ) {
		$default_path = $default_path ? $default_path : IME_PLUGIN_DIR . '/templates/';
		if ( file_exists( trailingslashit( $default_path ) . $template_name ) ) {
			$template = trailingslashit( $default_path ) . $template_name;
		}
	}
	// Return what we found
	return apply_filters( 'ime_locate_template', $template, $template_name, $template_path );
}
