<?php
/**
 *  Register custom post type for Meetup URL.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Events_Manager_Meetup_Import
 * @subpackage Events_Manager_Meetup_Import/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class respoinsible for generate list table for Meetup urls.
 */
class XT_Meetup_Import_Em_List_Table extends WP_List_Table {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $status, $page;
	        // Set parent defaults.
	        parent::__construct( array(
	            'singular'  => 'xt_meetup_url',     // singular name of the listed records.
	            'plural'    => 'xt_meetup_urls',    // plural name of the listed records.
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

		$xt_url_delete_args = array(
			'page'   => wp_unslash( $_REQUEST['page'] ),
			'xtmi_action' => 'xtmi_url_delete',
			'url_id'  => absint( $item['ID'] ),
		);
		// Build row actions.
		$actions = array(
		    'delete' => sprintf( '<a href="%1$s" onclick="return confirm(\'Warning!! Are you sure to Delete this Meetup url? Url will be permanatly deleted.\')">%2$s</a>',esc_url( wp_nonce_url( add_query_arg( $xt_url_delete_args ), 'xtmi_delete_url_nonce' ) ), esc_html__( 'Delete', 'events-manager-meetup-import' ) ),
		);

		// Return the title contents.
		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
		    $item['title'],
		    $item['ID'],
		    $this->row_actions( $actions )
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

		$xt_run_import_args = array(
			'page'   => wp_unslash( $_REQUEST['page'] ),
			'xtmi_action' => 'xtmi_em_run_import',
			'url_id'  => absint( $item['ID'] ),
		);

		// Return the title contents.
		return sprintf( '<a class="button-primary" href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url( add_query_arg( $xt_run_import_args ), 'xtmi_run_import_nonce' ) ),
			esc_html__( 'Import Now', 'events-manager-meetup-import' )
		);
	}

	/**
	 * Get column title.
	 *
	 * @since    1.0.0
	 */
	function get_columns() {
		$columns = array(
		 'title'     => 'Meetup URL',
		 'xt_category'   => 'Import Category',
		 'action'   => 'Action',
		);
		return $columns;
	}

	/**
	 * Get column title.
	 *
	 * @since    1.0.0
	 */

	/**
	 * Prepare Meetup url data.
	 *
	 * @since    1.0.0
	 */
	function prepare_items() {
		$per_page = 10;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$data = $this->xt_get_meetup_url_data();
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
	function xt_get_meetup_url_data() {
		$meetup_data = array( 'total_records' => 0, 'import_data' => array() );
		$per_page = 10;
		$current_page = $this->get_pagenum();

		$query_args = array(
			'post_type' => XTMI_EM_MGROUP_POSTTYPE,
			'posts_per_page' => $per_page,
			'paged' => $current_page,
		);
		$url_query = new WP_Query( $query_args );
		$meetup_data['total_records'] = ( $url_query->found_posts ) ? (int) $url_query->found_posts : 0;
		// The Loop.
		if ( $url_query->have_posts() ) {
			while ( $url_query->have_posts() ) {
				$url_query->the_post();

				$url_id = get_the_ID();
				$url_terms = get_the_terms( $url_id, XTMI_EM_TAXONOMY );
				$url_term_names = array();
				if ( $url_terms && ! is_wp_error( $url_terms ) ) {
					foreach ( $url_terms as $term ) {
						$url_term_names[] = $term->name;
					}
				}
				$meetup_data['import_data'][] = array(
					'ID' => $url_id,
					'title' => get_the_title(),
					'xt_category' => implode( ', ', $url_term_names ),
				);
			}
		}
		// Restore original Post Data.
		wp_reset_postdata();
		return $meetup_data;
	}
}
