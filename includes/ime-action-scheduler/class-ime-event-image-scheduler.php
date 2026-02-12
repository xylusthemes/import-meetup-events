<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class IME_Event_Image_Scheduler {

	public static function schedule_image_download( $event_id, $image_url, $event_args ) {
		if ( ! empty( $event_id ) && ! empty( $image_url ) ) {
			$ac_run_time = ( !empty($event_args['import_type']) && $event_args['import_type'] === 'onetime' ) ? 30 : 60;
			as_schedule_single_action( time() + $ac_run_time, 'ime_process_image_download', array( $event_id, $image_url ), 'ime_image_group' );
		}
	}

	public static function process_image_download( $event_id, $image_url ) {
        global $ime_events;
		if ( empty( $event_id ) || empty( $image_url ) ) return;
        
		if ( method_exists( $ime_events->common, 'setup_featured_image_to_event' ) ) {
			$ime_events->common->setup_featured_image_to_event( $event_id, $image_url );
		}
	}
}
