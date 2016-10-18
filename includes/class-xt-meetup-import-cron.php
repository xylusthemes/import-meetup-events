<?php
/**
 * Meetup Event Import Cron.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    XT_Meetup_Import
 * @subpackage XT_Meetup_Import/includes
 */
class XT_Meetup_Import_Cron {

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
		$this->xtmi_load_scheduler();
	}

	/**
	 * Load the all requred hooks for load and render import settings and Import interface
	 *
	 * @since    1.0.0
	 */
	public function xtmi_load_scheduler() {
		// Remove cron on delete meetup url.
		add_action( 'delete_post', array( $this, 'xtmi_remove_cron_job' ) );

		// Setup cron on add new Meetup group for TEC.
		add_action( 'save_post_'.XTMI_TEC_MGROUP_POSTTYPE, array( $this, 'xtmi_add_tec_cronjob' ), 10, 3 );

		// Setup cron on add new Meetup group for EM.
		add_action( 'save_post_'.XTMI_EM_MGROUP_POSTTYPE, array( $this, 'xtmi_add_em_cronjob' ), 10, 3 );

		// setup custom cron recurrences.
		add_action( 'cron_schedules', array( $this, 'xtmi_setup_custom_cron_recurrences' ) );
	}

	/**
	 * Setup Meetup group cron job when new group added for TEC.
	 *
	 * @since    1.0.0
	 * @param int 	 $post_id Post ID.
	 * @param object $post Post.
	 * @param bool   $update is update or new insert.
	 * @return void
	 */
	public function xtmi_add_tec_cronjob( $post_id, $post, $update ) {
		// check if not post update.
		if ( ! $update ) {

			$xtmi_options = get_option( XTMI_OPTIONS, array() );
			$import_type = isset( $xtmi_options['import_type'] ) ? $xtmi_options['import_type'] : 'cron';
			if ( 'cron' == $import_type ) {
				$cron_interval = isset( $xtmi_options['cron_interval'] ) ? $xtmi_options['cron_interval'] : 'twicedaily';
				wp_schedule_event( time(), $cron_interval, 'xtmi_tec_run_import', array( 'post_id' => $post_id ) );
			} else {
				do_action( 'xtmi_tec_run_import', $post_id );
			}
		}
	}

	/**
	 * Setup Meetup group cronjob when new group added for EM.
	 *
	 * @since    1.0.0
	 * @param int 	 $post_id Post ID.
	 * @param object $post Post.
	 * @param bool   $update is update or new insert.
	 * @return void
	 */
	public function xtmi_add_em_cronjob( $post_id, $post, $update ) {
		// check if not post update.
		if ( ! $update ) {

			$xtmi_options = get_option( XTMI_OPTIONS, array() );
			$import_type = isset( $xtmi_options['import_type'] ) ? $xtmi_options['import_type'] : 'cron';
			if ( 'cron' == $import_type ) {
				$cron_interval = isset( $xtmi_options['cron_interval'] ) ? $xtmi_options['cron_interval'] : 'twicedaily';
				wp_schedule_event( time(), $cron_interval, 'xtmi_em_run_import', array( 'post_id' => $post_id ) );
			} else {
				do_action( 'xtmi_em_run_import', $post_id );
			}
		}
	}

	/**
	 * Remove Meetup url cron job when url deleted.
	 *
	 * @since    1.0.0
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function xtmi_remove_cron_job( $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( $post_type == XTMI_TEC_MGROUP_POSTTYPE ){
			wp_clear_scheduled_hook( 'xtmi_tec_run_import', array( 'post_id' => $post_id ) );
		}
		if ( $post_type == XTMI_EM_MGROUP_POSTTYPE ) {
			wp_clear_scheduled_hook( 'xtmi_em_run_import', array( 'post_id' => $post_id ) );
		}
	}

	/**
	 * Setup custom cron recurrences.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function xtmi_setup_custom_cron_recurrences() {
		// Weekly Schedule.
		$schedules['weekly'] = array(
			'display' => __( 'Once Weekly', 'xt-meetup-import' ),
			'interval' => 604800,
		);
		// Monthly Schedule.
		$schedules['monthly'] = array(
			'display' => __( 'Once a Month', 'xt-meetup-import' ),
			'interval' => 2635200,
		);
		return $schedules;
	}

}
