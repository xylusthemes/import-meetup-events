<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Load Action Scheduler if not loaded
if ( ! class_exists( 'ActionScheduler' ) ) {
	require_once IME_PLUGIN_DIR . 'includes/ime-action-scheduler/action-scheduler/action-scheduler.php';
}

// Load custom scheduler
require_once IME_PLUGIN_DIR . 'includes/ime-action-scheduler/class-ime-event-image-scheduler.php';

// Register hook
add_action( 'ime_process_image_download', array( 'IME_Event_Image_Scheduler', 'process_image_download' ), 10, 2 );
