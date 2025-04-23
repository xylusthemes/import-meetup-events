<?php
/**
 *  List table for scheduled import.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Meetup_Events
 * @subpackage Import_Meetup_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class respoinsible for generate list table for scheduled import.
 */
class Import_Meetup_Events_List_Table extends WP_List_Table {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $status, $page;
			// Set parent defaults.
			parent::__construct( array(
				'singular'  => 'xt_scheduled_import',     // singular name of the listed records.
				'plural'    => 'ime_scheduled_import',    // plural name of the listed records.
				'ajax'      => false,        // does this table support ajax?
			) );
	}

	/**
	 * Setup output for default column.
	 *
	 * @since    1.0.0
	 * @param array  $item Items.
	 * @param string $column_name  Column name.
	 * @return string
	 */
	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Setup output for title column.
	 *
	 * @since    1.0.0
	 * @param array $item Items.
	 * @return array
	 */
	function column_title( $item ) {

		$ime_url_delete_args = array(
			'page'       => isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : 'meetup_import', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'ime_action' => 'ime_simport_delete',
			'import_id'  => absint( $item['ID'] ),
		);
		// Build row actions.
		$actions = array(
			'delete' => sprintf( '<a href="%1$s" onclick="return confirm(\'Warning!! Are you sure you want to delete all these import histories? Import history will be permanently deleted.\')">%2$s</a>',esc_url( wp_nonce_url( add_query_arg( $ime_url_delete_args ), 'ime_delete_import_nonce' ) ), esc_html__( 'Delete', 'import-meetup-events' ) ),
		);

		$source_data = get_post_meta( $item['ID'], 'import_eventdata', true );
		$source = 'No Data Found';
		if( !empty( $source_data['meetup_url'] ) ){
			$source = '<a href="' . esc_url( $source_data['meetup_url'] ) . '" target="_blank" >' . esc_attr( $item['title'] ) . '</a>';
		}

		// Return the title contents.
		return sprintf('<strong>%1$s</strong>
		<span>%4$s</span></br>
		<span>%5$s</span></br>
		<span style="color:silver">(id:%2$s)</span>%3$s',
		    $item['title'],
		    $item['ID'],
		    $this->row_actions( $actions ),
		    __('Origin', 'import-meetup-events') . ': <b>' . ucfirst( $item["import_origin"] ) . '</b>',
			__('Source', 'import-meetup-events') . ': <b>' . $source . '</b>'
		);
	}

	/**
	 * Setup output for Action column.
	 *
	 * @since    1.0.0
	 * @param array $item Items.
	 * @return array
	 */
	function column_action( $item ) {

		$xtmi_run_import_args = array(
			'page'       => isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : 'meetup_import', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'ime_action' => 'ime_run_import',
			'import_id'  => $item['ID'],
		);

		// Return the title contents.
		return sprintf( '<a class="button-primary" href="%1$s">%2$s</a><br/>%3$s',
			esc_url( wp_nonce_url( add_query_arg( $xtmi_run_import_args ), 'ime_run_import_nonce' ) ),
			esc_html__( 'Import Now', 'import-meetup-events' ),
			$item['last_import']
		);
	}

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("video")
			/*$2%s*/ $item['ID']             //The value of the checkbox should be the record's id
		);
	}

	/**
	 * Get column title.
	 *
	 * @since    1.0.0
	 */
	function get_columns() {
		$columns = array(
		 'cb'    => '<input type="checkbox" />',
		 'title'     => __( 'Scheduled import', 'import-meetup-events' ),
		 'import_status'   => __( 'Import Event Status', 'import-meetup-events' ),
		 'import_category'   => __( 'Import Category', 'import-meetup-events' ),
		 'import_frequency'   => __( 'Import Frequency', 'import-meetup-events' ),
		 'next_run' => __( 'Next Run', 'import-meetup-events' ),
		 'action'   => __( 'Action', 'import-meetup-events' ),
		);
		return $columns;
	}

	public function get_bulk_actions() {

		return array(
			'delete' => __( 'Delete', 'import-meetup-events' ),
		);

	}

	/**
	 * Prepare Meetup url data.
	 *
	 * @since    1.0.0
	 */
	function prepare_items( $origin = '' ) {
		$per_page = 10;
		$columns = $this->get_columns();
		$hidden = array( 'ID' );
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();
		
		if( $origin != '' ){
			$data = $this->get_scheduled_import_data( $origin );	
		}else{
			$data = $this->get_scheduled_import_data();
		}
		
		if ( ! empty( $data ) ) {
			$total_items = ( $data['total_records'] )? (int) $data['total_records'] : 0;
			// Set data to items.
			$this->items = ( $data['import_data'] )? $data['import_data'] : array();

			$this->set_pagination_args( array(
				'total_items' => $total_items,  // WE have to calculate the total number of items.
				'per_page'    => $per_page, // WE have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
			) );
		}
	}

	/**
	 * Get Meetup url data.
	 *
	 * @since    1.0.0
	 */
	function get_scheduled_import_data( $origin = '' ) {
		global $ime_events;

		$scheduled_import_data = array( 'total_records' => 0, 'import_data' => array() );
		$per_page = 10;
		$current_page = $this->get_pagenum();

		$query_args = array(
			'post_type' => 'ime_scheduled_import',
			'posts_per_page' => $per_page,
			'paged' => $current_page,
		);

		if( $origin != '' ){
			$query_args['meta_key'] = 'import_origin'; //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
			$query_args['meta_value'] = esc_attr( $origin ); //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		}
		$importdata_query = new WP_Query( $query_args );
		$scheduled_import_data['total_records'] = ( $importdata_query->found_posts ) ? (int) $importdata_query->found_posts : 0;
		$next_run_times = $this->get_ime_next_run_times();
		// The Loop.
		if ( $importdata_query->have_posts() ) {
			while ( $importdata_query->have_posts() ) {
				$importdata_query->the_post();

				$import_id = get_the_ID();
				$import_data = get_post_meta( $import_id, 'import_eventdata', true );
				$import_origin = get_post_meta( $import_id, 'import_origin', true );
				$import_plugin = isset( $import_data['import_into'] ) ? $import_data['import_into'] : '';
				$import_status = isset( $import_data['event_status'] ) ? $import_data['event_status'] : '';
				
				$term_names = array();
				$import_terms = isset( $import_data['event_cats'] ) ? $import_data['event_cats'] : array(); 
				
				if ( $import_terms && ! empty( $import_terms ) ) {
					foreach ( $import_terms as $term ) {
						$get_term = '';
						if( $import_plugin == 'tec' ){

							$get_term = get_term( $term, $ime_events->tec->get_taxonomy() );	

						}elseif( $import_plugin == 'em' ){

							$get_term = get_term( $term, $ime_events->em->get_taxonomy() );	

						}elseif( $import_plugin == 'eventon' ){

							$get_term = get_term( $term, $ime_events->eventon->get_taxonomy() );

						}elseif( $import_plugin == 'event_organizer' ){

							$get_term = get_term( $term, $ime_events->event_organizer->get_taxonomy() );

						}elseif( $import_plugin == 'aioec' ){

							$get_term = get_term( $term, $ime_events->aioec->get_taxonomy() );	

						}elseif( $import_plugin == 'my_calendar' ){

							$get_term = get_term( $term, $ime_events->my_calendar->get_taxonomy() );
								
						}elseif( $import_plugin == 'ime' ){

							$get_term = get_term( $term, $ime_events->ime->get_taxonomy() );	
						}else{
							$get_term = get_term( $term, $ime_events->tec->get_taxonomy() );
						}

						if( !is_wp_error( $get_term ) && !empty( $get_term ) ){
							$term_names[] = $get_term->name;
						}
					}
				}	

				$last_import_history_date = '';
				$history_args = array(
					'post_type'   => 'ime_import_history',
					'post_status' => 'publish',
					'posts_per_page' => 1,
					'meta_key'   => 'schedule_import_id', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
					'meta_value' => $import_id,           //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value 

				);

				$history = new WP_Query( $history_args );
				if ( $history->have_posts() ) {
					while ( $history->have_posts() ) {
						$history->the_post();
						// translators: %s: Human-readable time difference like "2 hours ago", "3 days ago", etc.
						$last_import_history_date = sprintf( esc_attr__( 'Last Import: %s ago', 'import-meetup-events' ), human_time_diff( get_the_date( 'U' ), current_time( 'timestamp' ) ) );
					}
				}
				wp_reset_postdata();

				$next_run = '-';
				if(isset($next_run_times[$import_id]) && !empty($next_run_times[$import_id])){
					$next_time = $next_run_times[$import_id];
					$next_run = sprintf( '%s (%s)',
						esc_html( get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $next_time ), 'Y-m-d H:i:s' ) ),
						esc_html( human_time_diff( current_time( 'timestamp', true ), $next_time ) )
					);
				}

				if( $next_run == '-' ){
						$ime_events->common->ime_recreate_missing_schedule_import( $import_id );
				}

				$scheduled_import_data['import_data'][] = array(
					'ID' => $import_id,
					'title' => get_the_title(),
					'import_status'   => ucfirst( $import_status ),
					'import_category' => implode( ', ', $term_names ),
					'import_frequency'=> isset( $import_data['import_frequency'] ) ? ucfirst( $import_data['import_frequency'] ) : '',
					'next_run'        => $next_run,
					'import_origin'   => $import_origin,
					'last_import'     => $last_import_history_date,
				);
			}
		}
		// Restore original Post Data.
		wp_reset_postdata();
		return $scheduled_import_data;
	}

	/**
	 * Get IME crons.
	 *
	 * @return Array
	 */
	function get_ime_crons(){
		$crons = array();
		if(function_exists('_get_cron_array') ){
			$crons = _get_cron_array();
		}
		$wpea_scheduled = array_filter($crons, function($cron) {
			$cron_name = array_keys($cron) ? array_keys($cron)[0] : '';
			if (strpos($cron_name, 'ime_run_scheduled_import') !== false) {
				return true;
			}
			return false;
		});
		return $wpea_scheduled;
	}


	/**
	 * Get Next run time array for schdeuled import.
	 *
	 * @return Array
	 */
	function get_ime_next_run_times(){
		$next_runs = array();
		$crons  = $this->get_ime_crons();
		foreach($crons as $time => $cron){
			foreach($cron as $cron_name){
				foreach($cron_name as $cron_post_id){
					if( isset($cron_post_id['args']) && isset($cron_post_id['args']['post_id']) ){
						$next_runs[$cron_post_id['args']['post_id']] = $time;
					}
				}
			}
		}
		return $next_runs;
	}
}

/**
 * Class respoinsible for generate list table for scheduled import.
 */
class Import_Meetup_Events_History_List_Table extends WP_List_Table {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $status, $page;
			// Set parent defaults.
			parent::__construct( array(
				'singular'  => 'import_history',     // singular name of the listed records.
				'plural'    => 'ime_import_histories',   // plural name of the listed records.
				'ajax'      => false,        // does this table support ajax?
			) );
	}

	/**
	 * Setup output for default column.
	 *
	 * @since    1.0.0
	 * @param array  $item Items.
	 * @param string $column_name  Column name.
	 * @return string
	 */
	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Setup output for title column.
	 *
	 * @since    1.0.0
	 * @param array $item Items.
	 * @return array
	 */
	function column_title( $item ) {

		$ime_url_delete_args = array(
			'page'       => isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : 'meetup_import', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'tab'        => isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'history', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'ime_action' => 'ime_history_delete',
			'history_id' => absint( $item['ID'] ),
		);
		// Build row actions.
		$actions = array(
			'delete' => sprintf( '<a href="%1$s" onclick="return confirm(\'Warning!! Are you sure you want to delete all these import histories? Import history will be permanently deleted.\')">%2$s</a>',esc_url( wp_nonce_url( add_query_arg( $ime_url_delete_args ), 'ime_delete_history_nonce' ) ), esc_html__( 'Delete', 'import-meetup-events' ) ),
		);

		// Return the title contents.
		return sprintf('<strong>%1$s</strong><span>%3$s</span> %2$s',
			$item['title'],
			$this->row_actions( $actions ),
			__('Origin', 'import-meetup-events') . ': <b>' . ucfirst( get_post_meta( $item['ID'], 'import_origin', true ) ) . '</b>'
		);
	}

	/**
	 * Setup output for Action column.
	 *
	 * @since    1.0.0
	 * @param array $item Items.
	 * @return array
	 */
	function column_stats( $item ) {

		$created = get_post_meta( $item['ID'], 'created', true );
		$updated = get_post_meta( $item['ID'], 'updated', true );
		$skipped = get_post_meta( $item['ID'], 'skipped', true );
		$skip_trash = get_post_meta( $item['ID'], 'skip_trash', true );

		$success_message = '<span style="color: silver"><strong>';
		if( $created > 0 ){
			// translators: %d: Number of events Created.
			$success_message .= sprintf( __( '%d Created', 'import-meetup-events' ), $created )."<br>";
		}
		if( $updated > 0 ){
			// translators: %d: Number of events Updated.
			$success_message .= sprintf( __( '%d Updated', 'import-meetup-events' ), $updated )."<br>";
		}
		if( $skipped > 0 ){
			// translators: %d: Number of events Skipped.
			$success_message .= sprintf( __( '%d Skipped', 'import-meetup-events' ), $skipped ) ."<br>";
		}
		if( $skip_trash > 0 ){
			// translators: %d: Number of events Skipped.
			$success_message .= sprintf( __( '%d Skipped in Trash', 'import-meetup-events' ), $skip_trash ) ."<br>";
		}

		if( $created == 0 && $updated == 0 && $skipped == 0 && $skip_trash == 0  ){
			$success_message .= "There are no events imported/Updated";
		}

		$success_message .= "</strong></span>";

		// Return the title contents.
		return $success_message;
	}

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("video")
			/*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
		);
	}

	/**
	 * Get column title.
	 *
	 * @since    1.0.0
	 */
	function get_columns() {
		$columns = array(
		 'cb'    => '<input type="checkbox" />',
		 'title'     => __( 'Import', 'import-meetup-events' ),
		 'import_category' => __( 'Import Category', 'import-meetup-events' ),
		 'import_date'  => __( 'Import Date', 'import-meetup-events' ),
		 'stats' => __( 'Import Stats', 'import-meetup-events' ),
		);
		return $columns;
	}

	public function get_bulk_actions() {

		return array(
			'delete' => __( 'Delete', 'import-meetup-events' ),
		);

	}

	/**
	 * Prepare Meetup url data.
	 *
	 * @since    1.0.0
	 */
	function prepare_items( $origin = '' ) {
		$per_page = 10;
		$columns = $this->get_columns();
		$hidden = array( 'ID' );
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();
		
		if( $origin != '' ){
			$data = $this->get_import_history_data( $origin );	
		}else{
			$data = $this->get_import_history_data();
		}
		
		if ( ! empty( $data ) ) {
			$total_items = ( $data['total_records'] )? (int) $data['total_records'] : 0;
			// Set data to items.
			$this->items = ( $data['import_data'] )? $data['import_data'] : array();

			$this->set_pagination_args( array(
				'total_items' => $total_items,  // WE have to calculate the total number of items.
				'per_page'    => $per_page, // WE have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
			) );
		}
	}

	//import Delete botton 
	public function extra_tablenav( $which ) {

		if ( 'top' !== $which ) {
			return;
		}	
		$ime_url_all_delete_args = array(
			'page'       => isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : 'meetup_import', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'tab'        => isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'history', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'ime_action' => 'ime_all_history_delete',
		);

		$delete_ids = get_posts( array( 'numberposts' => 1,'fields' => 'ids', 'post_type' => 'ime_import_history' ) );
		if( !empty( $delete_ids ) ){
			$wp_delete_nonce_url = esc_url( wp_nonce_url( add_query_arg( $ime_url_all_delete_args, admin_url( 'admin.php' ) ), 'ime_delete_all_history_nonce' ) );
			$confirmation_message = esc_html__( "Warning!! Are you sure you want to delete all these import histories? Import history will be permanently deleted.", "import-meetup-events" );
			?>
			<a class="button apply" href="<?php echo esc_url( $wp_delete_nonce_url ); ?>" onclick="return confirm('<?php echo esc_attr( $confirmation_message ); ?>')">
				<?php esc_html_e( 'Clear Import History', 'import-meetup-events' ); ?>
			</a>
			<?php
		}
	}

	/**
	 * Get Meetup url data.
	 *
	 * @since    1.0.0
	 */
	function get_import_history_data( $origin = '' ) {
		global $ime_events;

		$scheduled_import_data = array( 'total_records' => 0, 'import_data' => array() );
		$per_page = 10;
		$current_page = $this->get_pagenum();

		$query_args = array(
			'post_type' => 'ime_import_history',
			'posts_per_page' => $per_page,
			'paged' => $current_page,
		);

		if( $origin != '' ){
			$query_args['meta_key'] = 'import_origin';       //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
			$query_args['meta_value'] = esc_attr( $origin ); //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value 
		}

		$importdata_query = new WP_Query( $query_args );
		$scheduled_import_data['total_records'] = ( $importdata_query->found_posts ) ? (int) $importdata_query->found_posts : 0;
		// The Loop.
		if ( $importdata_query->have_posts() ) {
			while ( $importdata_query->have_posts() ) {
				$importdata_query->the_post();

				$import_id = get_the_ID();
				$import_data = get_post_meta( $import_id, 'import_data', true );
				$import_origin = get_post_meta( $import_id, 'import_origin', true );
				$import_plugin = isset( $import_data['import_into'] ) ? $import_data['import_into'] : '';
				
				$term_names = array();
				$import_terms = isset( $import_data['event_cats'] ) ? $import_data['event_cats'] : array(); 
				
				if ( $import_terms && ! empty( $import_terms ) ) {
					foreach ( $import_terms as $term ) {
						$get_term = '';
						if( $import_plugin == 'tec' ){

							$get_term = get_term( $term, $ime_events->tec->get_taxonomy() );	

						}elseif( $import_plugin == 'em' ){

							$get_term = get_term( $term, $ime_events->em->get_taxonomy() );	

						}elseif( $import_plugin == 'eventon' ){

							$get_term = get_term( $term, $ime_events->eventon->get_taxonomy() );

						}elseif( $import_plugin == 'event_organizer' ){

							$get_term = get_term( $term, $ime_events->event_organizer->get_taxonomy() );

						}elseif( $import_plugin == 'aioec' ){

							$get_term = get_term( $term, $ime_events->aioec->get_taxonomy() );	

						}elseif( $import_plugin == 'my_calendar' ){

							$get_term = get_term( $term, $ime_events->my_calendar->get_taxonomy() );
								
						}elseif( $import_plugin == 'ime' ){

							$get_term = get_term( $term, $ime_events->ime->get_taxonomy() );	
						}else{
							
							$get_term = get_term( $term, $ime_events->tec->get_taxonomy() );

						}
						
						if( !is_wp_error( $get_term ) && !empty( $get_term ) ){
							$term_names[] = $get_term->name;
						}
					}
				}

				$scheduled_import_data['import_data'][] = array(
					'ID' => $import_id,
					'title' => get_the_title(),
					'import_category' => implode( ', ', $term_names ),
					'import_date' => get_the_date("F j Y, h:i A"),
				);
			}
		}
		// Restore original Post Data.
		wp_reset_postdata();
		return $scheduled_import_data;
	}
}

/**
* Class for the shortcode list table.
*/
class IME_Shortcode_List_Table extends WP_List_Table {

	public function prepare_items() {

		$columns 	= $this->get_columns();
		$hidden 	= $this->get_hidden_columns();
		$sortable 	= $this->get_sortable_columns();
		$data 		= $this->table_data();

		$per_page 		= 10;
		$current_page 	= $this->get_pagenum();
		$total_items 	= count( $data );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );

		$data = array_slice( $data, ( ( $current_page-1 ) * $per_page ), $per_page );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {
		$columns = array(
			'id'            => __( 'ID', 'import-meetup-events' ),
			'how_to_use'    => __( 'Title', 'import-meetup-events' ),
			'shortcode'     => __( 'Shortcode', 'import-meetup-events' ),
			'action'        => __( 'Action', 'import-meetup-events' ),
		);
		return $columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	private function table_data() {
		$data = array();
		
		$data[] = array(
			'id'            => 1,
			'how_to_use'    => 'Display All Events',
			'shortcode'     => '<p class="ime_short_code">[meetup_events]</p>',
			'action'        => '<button class="ime-btn-copy-shortcode button-primary"  data-value="[meetup_events]">Copy</button>',
		);
		$data[] = array(
			'id'            => 2,
			'how_to_use'    => 'New Grid Layouts <span style="color:green;font-weight: 900;">( PRO )</span>',
			'shortcode'     => '<p class="ime_short_code">[meetup_events layout="style2"]</p>',
			'action'     	=> "<button class='ime-btn-copy-shortcode button-primary'  data-value='[meetup_events layout=\"style2\"]'>Copy</button>",
		);
		$data[] = array(
			'id'            => 3,
			'how_to_use'    => 'Display with column',
			'shortcode'     => '<p class="ime_short_code">[meetup_events col="2"]</p>',
			'action'     	=> "<button class='ime-btn-copy-shortcode button-primary' data-value='[meetup_events col=\"2\"]' >Copy</button>",
		);
		$data[] = array(
			'id'            => 4,
			'how_to_use'    => 'Limit for display events',
			'shortcode'     => '<p class="ime_short_code">[meetup_events posts_per_page="12"]</p>',
			'action'     	=> "<button class='ime-btn-copy-shortcode button-primary' data-value='[meetup_events posts_per_page=\"12\"]' >Copy</button>",
		);
		$data[] = array(
			'id'            => 5,
			'how_to_use'    => 'Display Events based on order',
			'shortcode'     => '<p class="ime_short_code">[meetup_events order="asc"]</p>',
			'action'     	=> "<button class='ime-btn-copy-shortcode button-primary' data-value='[meetup_events order=\"asc\"]' >Copy</button>",
		);
		$data[] = array(
			'id'            => 6,
			'how_to_use'    => 'Display events based on category',
			'shortcode'     => '<p class="ime_short_code" >[meetup_events category="cat1"]</p>',
			'action'     	=> "<button class='ime-btn-copy-shortcode button-primary' data-value='[meetup_events category=\"cat1\"]' >Copy</button>",
		);
		$data[] = array(
			'id'            => 7,
			'how_to_use'    => 'Display Past events',
			'shortcode'     => '<p class="ime_short_code">[meetup_events past_events="yes"]</p>',
			'action'     	=> "<button class='ime-btn-copy-shortcode button-primary' data-value='[meetup_events past_events=\"yes\"]' >Copy</button>",
		);
		$data[] = array(
			'id'            => 8,
			'how_to_use'    => 'Display Events based on orderby',
			'shortcode'     => '<p class="ime_short_code">[meetup_events order="asc" orderby="post_title"]</p>',
			'action'     	=> "<button class='ime-btn-copy-shortcode button-primary' data-value='[meetup_events order=\"asc\" orderby=\"post_title\"]' >Copy</button>",
		);
		$data[] = array(
			'id'            => 9,
			'how_to_use'    => 'Full Short-code',
			'shortcode'     => '<p class="ime_short_code">[meetup_events  col="2" posts_per_page="12" category="cat1" past_events="yes" order="desc" orderby="post_title" start_date="YYYY-MM-DD" end_date="YYYY-MM-DD"]</p>',
			'action'     	=> "<button class='ime-btn-copy-shortcode button-primary' data-value='[meetup_events col=\"2\" posts_per_page=\"12\" category=\"cat1\" past_events=\"yes\" order=\"desc\" orderby=\"post_title\" start_date=\"YYYY-MM-DD\" end_date=\"YYYY-MM-DD\"]' >Copy</button>",
		);
		return $data;
	}
	
	/**
	 * Define what data to show on each column of the table
	 *
	 * @param Array $item        Data
	 * @param String $column_name - Current column name
	 */
	public function column_default( $item, $column_name )
	{
		switch( $column_name ) {
			case 'id':
			case 'how_to_use':
			case 'shortcode':
			case 'action':
				return $item[ $column_name ];

			default:
				return print_r( $item, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}
}