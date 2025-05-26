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
		<tr class="event_plugis_wrapper">
			<th scope="row">
				<?php esc_attr_e( 'Import into','import-meetup-events' ); ?> :
			</th>
			<td>
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
			</td>
		</tr>

		<tr class="event_cats_wrapper">
			<th scope="row">
				<?php esc_attr_e( 'Event Categories for Event Import','import-meetup-events' ); ?> : 
			</th>
			<td>
				<div class="event_taxo_terms_wraper">

				</div>
				<span class="ime_small">
		            <?php esc_attr_e( 'These categories are assign to imported event.', 'import-meetup-events' ); ?>
		        </span>
			</td>
		</tr>
		<?php		

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
		<tr class="event_status_wrapper">
			<th scope="row">
				<?php esc_attr_e( 'Status','import-meetup-events' ); ?> :
			</th>
			<td>
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
			</td>
		</tr>
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
			<span class="ime_small">
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
