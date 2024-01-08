<?php
/**
 * Class for manane Imports submissions.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Meetup_Events
 * @subpackage Import_Meetup_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Import_Meetup_Events_Manage_Import {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup_success_messages' ) );
		add_action( 'admin_init', array( $this, 'handle_import_form_submit' ) , 99);
		add_action( 'admin_init', array( $this, 'handle_import_settings_submit' ), 99 );
		add_action( 'admin_init', array( $this, 'handle_listtable_oprations' ), 99 );
		add_action( 'admin_init', array( $this, 'handle_gma_settings_submit' ), 99 );
	}

	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_form_submit() {
		global $ime_errors; 
		$event_data = array();

		if ( isset( $_POST['ime_action'] ) && $_POST['ime_action'] == 'ime_import_submit' &&  check_admin_referer( 'ime_import_form_nonce_action', 'ime_import_form_nonce' ) ) {
			
			$event_data['import_into'] = isset( $_POST['event_plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['event_plugin'] ) ) : '';
			if( $event_data['import_into'] == '' ){
				$ime_errors[] = esc_html__( 'Please provide an import plugin for Event import.', 'import-meetup-events' );
				return;
			}
			
			$event_data['import_type'] = 'onetime';
			$event_data['import_frequency'] = 'daily';
			$event_data['event_status'] = isset( $_POST['event_status'] ) ? sanitize_text_field( wp_unslash( $_POST['event_status'] ) ) : 'pending';
			$event_data['event_cats'] = isset( $_POST['event_cats'] ) ? wp_unslash( $_POST['event_cats'] ) : array();
			$event_data['event_author']     = !empty( $_POST['event_author'] ) ? sanitize_text_field( wp_unslash( $_POST['event_author'] ) ) : get_current_user_id();
			$event_origin = isset( $_POST['import_origin'] ) ? sanitize_text_field( wp_unslash( $_POST['import_origin'] ) ):'meetup';

			$this->handle_meetup_import_form_submit( $event_data );
		}
	}

	/**
	 * Process insert google maps api key for embed maps
	 *
	 * @since    1.5.8
	 */
	public function handle_gma_settings_submit() {
		global $ime_errors, $ime_success_msg;
		if ( isset( $_POST['ime_gma_action'] ) && 'ime_save_gma_settings' === sanitize_text_field( wp_unslash( $_POST['ime_gma_action'] ) ) && check_admin_referer( 'ime_gma_setting_form_nonce_action', 'ime_gma_setting_form_nonce' ) ) { // input var okay.
			$gma_option = array();
			$gma_option['ime_google_maps_api_key'] = isset( $_POST['ime_google_maps_api_key'] ) ? wp_unslash( $_POST['ime_google_maps_api_key'] ) : ''; // input var okay.
			$is_update = update_option( 'ime_google_maps_api_key', $gma_option['ime_google_maps_api_key'] );
			if ( $is_update ) {
				$ime_success_msg[] = __( 'Google Maps API Key has been saved successfully.', 'import-meetup-events' );
			} else {
				$ime_errors[] = __( 'Something went wrong! please try again.', 'import-meetup-events' );
			}
		}
	}

	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_settings_submit() {
		global $ime_errors, $ime_success_msg;
		if ( isset( $_POST['ime_action'] ) && $_POST['ime_action'] == 'ime_save_settings' &&  check_admin_referer( 'ime_setting_form_nonce_action', 'ime_setting_form_nonce' ) ) {
			
			$ime_options = isset( $_POST['meetup'] ) ? wp_unslash( $_POST['meetup'] ): array();
			$is_update = update_option( IME_OPTIONS, $ime_options );
			if( $is_update ){
				$ime_success_msg[] = __( 'Import settings has been saved successfully.', 'import-meetup-events' );
			}else{
				$ime_errors[] = __( 'Something went wrong! please try again.', 'import-meetup-events' );
			}
		}
	}

	/**
	 * Delete scheduled import from list table.
	 *
	 * @since    1.0.0
	 */
	public function handle_listtable_oprations() {

		global $ime_success_msg;
		if ( isset( $_GET['ime_action'] ) && $_GET['ime_action'] == 'ime_simport_delete' && isset($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ime_delete_import_nonce') ) {
			$import_id = isset( $_GET['import_id'] ) ? sanitize_text_field( wp_unslash( $_GET['import_id'] ) ) : '';
			$page = isset($_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'meetup_import';
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ): 'scheduled';
			$wp_redirect = admin_url( 'admin.php?page='.$page );
			if ( $import_id > 0 ) {
				$post_type = get_post_type( $import_id );
				if ( $post_type == 'ime_scheduled_import' ) {
					wp_delete_post( $import_id, true );
					$query_args = array( 'ime_msg' => 'import_del', 'tab' => $tab );
					wp_safe_redirect( add_query_arg( $query_args, $wp_redirect ) );
					exit;
				}
			}
		}

		if ( isset( $_GET['ime_action'] ) && $_GET['ime_action'] == 'ime_history_delete' && isset($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ime_delete_history_nonce' ) ) {
			$history_id = isset( $_GET['history_id'] ) ? sanitize_text_field( wp_unslash( (int)$_GET['history_id'] ) ): '';
			$page = isset($_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) )  : 'meetup_import';
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'history';
			$wp_redirect = admin_url( 'admin.php?page='.$page );
			if ( $history_id > 0 ) {
				wp_delete_post( $history_id, true );
				$query_args = array( 'ime_msg' => 'history_del', 'tab' => $tab );
				wp_safe_redirect(  add_query_arg( $query_args, $wp_redirect ) );
				exit;
			}
		}

		if ( isset( $_GET['ime_action'] ) && $_GET['ime_action'] == 'ime_run_import' && isset($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ime_run_import_nonce') ) {
			$import_id = isset( $_GET['import_id'] ) ? sanitize_text_field( wp_unslash( (int)$_GET['import_id'] ) ): '';
			$page = isset($_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'meetup_import';
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'scheduled';
			$wp_redirect = admin_url( 'admin.php?page='.$page );
			if ( $import_id > 0 ) {
				do_action( 'ime_run_scheduled_import', $import_id );
				$query_args = array( 'ime_msg' => 'import_success', 'tab' => $tab );
				wp_safe_redirect(  add_query_arg( $query_args, $wp_redirect ) );
				exit;
			}
		}

		$is_bulk_delete = ( ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' ) || ( isset( $_GET['action2'] ) && sanitize_text_field( wp_unslash( $_GET['action2'] ) )  == 'delete' ) );

		if ( $is_bulk_delete && isset($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk-ime_scheduled_import') ) {
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'scheduled';
			$wp_redirect = get_site_url() . urldecode( $_REQUEST['_wp_http_referer'] );
			$delete_ids = isset( $_REQUEST['xt_scheduled_import'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['xt_scheduled_import'] ) ) : '0';
			if( !empty( $delete_ids ) ){
				foreach ($delete_ids as $delete_id ) {
					$timestamp = wp_next_scheduled( 'ime_run_scheduled_import', array( 'post_id' => (int)$delete_id ) );
					if ( $timestamp ) {
						wp_unschedule_event( $timestamp, 'ime_run_scheduled_import', array( 'post_id' => (int)$delete_id ) );
					}
					wp_delete_post( $delete_id, true );
				}
			}
			$query_args = array( 'ime_msg' => 'import_dels', 'tab' => $tab );
			wp_safe_redirect(  add_query_arg( $query_args, $wp_redirect ) );
			exit;
		}

		if ( $is_bulk_delete && isset($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk-ime_import_histories') ) {
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'history';
			$wp_redirect = get_site_url() . urldecode( $_REQUEST['_wp_http_referer'] );
			$delete_ids = isset( $_REQUEST['import_history'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['import_history'] ) ) : '0';
			if( !empty( $delete_ids ) ){
				foreach ($delete_ids as $delete_id ) {
					wp_delete_post( $delete_id, true );
				}
			}
			$query_args = array( 'ime_msg' => 'history_dels', 'tab' => $tab );
			wp_safe_redirect(  add_query_arg( $query_args, $wp_redirect ) );
			exit;
		}

		// Delete All History Data 
		if ( isset( $_GET['ime_action'] ) && $_GET['ime_action'] == 'ime_all_history_delete' && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ime_delete_all_history_nonce' ) ) {
			$page        = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'meetup_import';
			$tab         = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'history';
			$wp_redirect = admin_url( 'admin.php?page=' . $page );

			$delete_ids  = get_posts( array( 'numberposts' => -1,'fields' => 'ids', 'post_type' => 'ime_import_history' ) );

			if ( ! empty( $delete_ids ) ) {
				foreach ( $delete_ids as $delete_id ) {
					wp_delete_post( $delete_id, true );
				}
			}		
			$query_args = array(
				'ime_msg' => 'history_dels',
				'tab'     => $tab,
			);			
			wp_safe_redirect( add_query_arg( $query_args, $wp_redirect ) );
			exit;
		}

	}

	/**
	 * Handle meetup import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_meetup_import_form_submit( $event_data ){
		global $ime_errors, $ime_success_msg, $ime_events;

		$event_data['import_origin'] = 'meetup';
		$event_data['import_by']     = isset( $_POST['meetup_import_by'] ) ? sanitize_text_field( wp_unslash( $_POST['meetup_import_by'] ) ) : '';
		$event_data['ime_event_ids'] = isset( $_POST['ime_event_ids'] ) ? array_map( 'trim', array_map( 'sanitize_text_field', explode( "\n", preg_replace( "/^\n+|^[\t\s]*\n+/m", '', sanitize_text_field( wp_unslash( $_POST['ime_event_ids'] ) ) ) ) ) ) : array(); // input var okay.
		$event_data['meetup_url']    = isset( $_POST['meetup_url'] ) ? sanitize_text_field( wp_unslash( $_POST['meetup_url'] ) ) : '';

		if ( 'group_url' === $event_data['import_by'] && !empty( $event_data['meetup_url'] ) ) {
			if ( filter_var( $event_data['meetup_url'], FILTER_VALIDATE_URL) === false ) {
				$ime_errors[] = esc_html__( 'Please provide valid Meetup group URL.', 'import-meetup-events' );
				return;
			}
			$event_data['meetup_url'] = esc_url( $event_data['meetup_url'] );
		}

		$import_events = $ime_events->meetup->import_events( $event_data );
		if( $import_events && !empty( $import_events ) ){
			$ime_events->common->display_import_success_message( $import_events, $event_data );
		}
	}
	
	/**
	 * Setup Success message.
	 *
	 * @since    1.0.0
	 */
	public function setup_success_messages(){
		global $ime_success_msg;
		if( isset( $_GET['ime_msg'] ) && $_GET['ime_msg'] != '' ){
			switch ( $_GET['ime_msg'] ) {
				case 'import_del':
					$ime_success_msg[] = esc_html__( 'Scheduled import deleted successfully.', 'import-meetup-events' );
					break;

				case 'import_dels':
					$ime_success_msg[] = esc_html__( 'Scheduled imports are deleted successfully.', 'import-meetup-events' );
					break;

				case 'import_success':
					$ime_success_msg[] = esc_html__( 'Scheduled import has been run successfully.', 'import-meetup-events' );
					break;

				case 'history_del':
					$ime_success_msg[] = esc_html__( 'Import history deleted successfully.', 'import-meetup-events' );
					break;

				case 'history_dels':
					$ime_success_msg[] = esc_html__( 'Import histories are deleted successfully.', 'import-meetup-events' );
					break;					
								
				default:
					$ime_success_msg[] = esc_html__( 'Scheduled imports are deleted successfully.', 'import-meetup-events' );
					break;
			}
		}
	}
}
