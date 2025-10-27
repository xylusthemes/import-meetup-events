<?php
/**
 * Ajax functions class for Import Meetup Events.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Meetup_Events
 * @subpackage Import_Meetup_Events/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Import_Meetup_Events_Ajax {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_ime_load_paged_events',  array( $this, 'ime_load_paged_events_callback' ) );
        add_action( 'wp_ajax_nopriv_ime_load_paged_events',  array( $this, 'ime_load_paged_events_callback' ) );
	}

	public function ime_load_paged_events_callback() {
		if ( empty( $_POST['atts'] ) || empty( $_POST['page'] ) ) {
			wp_send_json_error( 'Missing params' );
		}

		$atts          = json_decode( stripslashes( $_POST['atts'] ), true );
		$atts['paged'] = intval( $_POST['page'] );
		$html          = do_shortcode( '[meetup_events ' . http_build_query( $atts, '', ' ' ) . ']' );

		wp_send_json_success( $html );
	}
}