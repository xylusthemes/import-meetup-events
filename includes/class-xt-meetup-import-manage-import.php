<?php
/**
 * Class for manane Meetup Import ( Insert/ Delete )
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    XT_Meetup_Import
 * @subpackage XT_Meetup_Import/includes
 */
class XT_Meetup_Import_Manage_Import {

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
	 * Error generated during form submit
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $errors    Error generated during form submit
	 */
	protected $errors;

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

		add_action( 'init', array( $this, 'xtmi_register_session' ) );
		// For The Events calendar Groups
		add_action( 'init', array( $this, 'xtmi_handle_tec_insert_form' ), 100 );
		// For Events manager Groups
		add_action( 'init', array( $this, 'xtmi_handle_em_insert_form' ), 100 );

		add_action( 'init', array( $this, 'xtmi_handle_manual_import' ) );
		add_action( 'init', array( $this, 'xtmi_handle_delete_group' ), 100 );
		add_action( 'init', array( $this, 'xtmi_save_settings' ), 100 );
		add_action( 'admin_notices', array( $this, 'xtmi_display_errors' ), 100 );
		add_action( 'admin_notices', array( $this, 'xtmi_display_success_message' ), 70 );

	}

	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function xtmi_handle_tec_insert_form() {
		if ( isset( $_POST['xtmi_action'] ) && $_POST['xtmi_action'] == 'xtmi_tec_url_submit' &&  check_admin_referer( 'xtmi_insert_form_nonce_action', 'xtmi_insert_form_nonce' ) ) {

			// validate values.
			$xtmi_url = isset( $_POST['xtmi_meet_url'] ) ? sanitize_text_field( $_POST['xtmi_meet_url']) : '';
			$xtmi_cats = isset( $_POST['xtmi_event_cats'] ) ? (array) $_POST['xtmi_event_cats'] : array();

			if ( ! empty( $xtmi_cats ) ) {
				foreach ( $xtmi_cats as $xtmi_catk => $xtmi_catv ) {
					$xtmi_cats[ $xtmi_catk ] = (int) $xtmi_catv;
				}
			}
			$xtmi_url = $this->xtmi_format_url( $xtmi_url );
			if ( ! empty( $xtmi_url ) && empty( $this->errors ) ) {
				$this->xtmi_save_meetup_url( $xtmi_url, $xtmi_cats, 'tec' );
			}
		}
	}

	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function xtmi_handle_em_insert_form() {
		if ( isset( $_POST['xtmi_action'] ) && $_POST['xtmi_action'] == 'xtmi_em_url_submit' &&  check_admin_referer( 'xtmi_insert_form_nonce_action', 'xtmi_insert_form_nonce' ) ) {

			// validate values.
			$xtmi_url = isset( $_POST['xtmi_meet_url'] ) ? sanitize_text_field( $_POST['xtmi_meet_url']) : '';
			$xtmi_cats = isset( $_POST['xtmi_event_cats'] ) ? (array) $_POST['xtmi_event_cats'] : array();

			if ( ! empty( $xtmi_cats ) ) {
				foreach ( $xtmi_cats as $xtmi_catk => $xtmi_catv ) {
					$xtmi_cats[ $xtmi_catk ] = (int) $xtmi_catv;
				}
			}
			$xtmi_url = $this->xtmi_format_url( $xtmi_url );
			if ( ! empty( $xtmi_url ) && empty( $this->errors ) ) {
				$this->xtmi_save_meetup_url( $xtmi_url, $xtmi_cats, 'em' );
			}
		}
	}

	/**
	 * Save meetup group.
	 *
	 * @since    1.0.0
	 * @param string $url Meetup group url.
	 * @param array  $cats Meetup events cats.
	 */
	public function xtmi_save_meetup_url( $url, $cats = array(), $group_for ='tec'	) {
		$post_type = XTMI_TEC_MGROUP_POSTTYPE;
		$taxonomy = 'tribe_events_cat';
		if( 'em' == $group_for ){
			$post_type = XTMI_EM_MGROUP_POSTTYPE;
			$taxonomy = XTMI_EM_TAXONOMY;
		}

		if ( $this->xtmi_post_exists( $url, $group_for ) > 0 ) {
			$this->errors[] = __( 'Meetup group url already exists', 'xt-meetup-import');
			return false;
		}

		$insert_args = array(
			'post_title' => $url,
			'post_type' => $post_type,
			'post_status' => 'publish',
		);

		if ( ! empty( $cats ) ) {
			$insert_args['tax_input'] = array(
				$taxonomy => $cats,
			);
		}

		$insert = wp_insert_post( $insert_args, true );
		if ( is_wp_error( $insert ) ) {
			$this->errors[] = __('Something went wrong when insert url. ',  'xt-meetup-import') . $insert->get_error_message();
			return;
		}
		add_action( 'admin_notices', array( $this, 'xtmi_display_insert_success' ), 100 );

	}

	/**
	 * Check weather group already exists or not.
	 *
	 * @since    1.0.0
	 * @param string $url meetup url.
	 * @return boolean
	 */
	public function xtmi_post_exists( $url, $group_for = 'tec' ) {
		global $wpdb;
		$pos_type = XTMI_TEC_MGROUP_POSTTYPE;
		if( 'em' == $group_for ){
			$pos_type = XTMI_EM_MGROUP_POSTTYPE;
		}
		$post_title = wp_unslash( sanitize_post_field( 'post_title', $url, 0, 'db' ) );
		$query = "SELECT ID FROM $wpdb->posts WHERE `post_type`= '" . $pos_type . "'";
		$args = array();

		if ( !empty ( $url ) ) {
		    $query .= ' AND post_title = %s';
		    $args[] = $post_title;
		}
		if ( ! empty( $args ) ) {
			return (int) $wpdb->get_var( $wpdb->prepare( $query, $args ) );
		}
		return 0;
	}

	/**
	 * Run Manual Import
	 *
	 * @since    1.0.0
	 */
	public function xtmi_handle_manual_import() {
		if ( isset( $_GET['xtmi_action'] ) && $_GET['xtmi_action'] == 'xtmi_em_run_import' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'xtmi_run_import_nonce') ) {
			$url_id = absint( $_GET['url_id'] );
			if ( $url_id > 0 ) {
				do_action( 'xtmi_em_run_import', $url_id );
				$message = esc_html__( 'Event imported successfully.', 'xt-meetup-import' );
				$this->xtmi_set_success_message( $message );
				wp_redirect( remove_query_arg( array( 'xtmi_action', 'url_id', '_wpnonce' ) ) );
				exit;
			}
		}
		if ( isset( $_GET['xtmi_action'] ) && $_GET['xtmi_action'] == 'xtmi_tec_run_import' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'xtmi_run_import_nonce') ) {
			$url_id = (int)$_GET['url_id'];
			if ( $url_id > 0 ) {
				do_action( 'xtmi_tec_run_import', $url_id );
				$message = esc_html__( 'Event imported successfully.', 'xt-meetup-import' );
				$this->xtmi_set_success_message( $message );
				wp_redirect( remove_query_arg( array( 'xtmi_action', 'url_id', '_wpnonce' ) ) );
				exit;
			}
		}
	}

	/**
	 * Delete Meetup group for The Events calendar.
	 *
	 * @since    1.0.0
	 */
	public function xtmi_handle_delete_group() {
		if ( isset( $_GET['xtmi_action'] ) && $_GET['xtmi_action'] == 'xtmi_url_delete' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'xtmi_delete_url_nonce') ) {
			$url_id = absint( $_GET['url_id'] );
			if ( $url_id > 0 ) {
				$post_type = get_post_type( $url_id );
				if ( $post_type == XTMI_TEC_MGROUP_POSTTYPE || $post_type == XTMI_EM_MGROUP_POSTTYPE ) {
					wp_delete_post( $url_id, true );
					add_action( 'admin_notices', array( $this, 'xtmi_display_delete_success' ), 100 );
				}
			}
		}
	}

	/**
	 * Meetup Import Settings save.
	 *
	 * @since    1.0.0
	 */
	public function xtmi_save_settings() {
		if ( isset( $_POST['xtmi_action'] ) && $_POST['xtmi_action'] == 'xtmi_save_settings' &&  check_admin_referer( 'xtmi_setting_form_nonce_action', 'xtmi_setting_form_nonce' ) ) {

			$xtmi_meetup_options = array();
			// validate values.
			$xtmi_meetup_options['meetup_api_key'] = isset( $_POST['xtmi_meetup_api_key'] ) ? sanitize_text_field( $_POST['xtmi_meetup_api_key'] ) : '';
			$xtmi_meetup_options['default_status'] = isset( $_POST['xtmi_default_status'] ) ? sanitize_text_field( $_POST['xtmi_default_status'] ) : '';
			$xtmi_meetup_options['import_type'] = isset( $_POST['import_type'] ) ? sanitize_text_field( $_POST['import_type'] ) : '';
			$xtmi_meetup_options['cron_interval'] = isset( $_POST['cron_interval'] ) ? sanitize_text_field( $_POST['cron_interval'] ) : '';
			$xtmi_meetup_options['update_events'] = isset( $_POST['update_events'] ) ? sanitize_text_field( $_POST['update_events'] ) : '';

			$is_update = update_option( 'xtmi_meetup_options', $xtmi_meetup_options );
			add_action( 'admin_notices', array( $this, 'xtmi_display_setting_success' ), 100 );
		}
	}

	/**
	 * Validate url.
	 *
	 * @since    1.0.0
	 * @param string $url Meetup group url.
	 * @return string formatted url.
	 */
	public function xtmi_format_url( $url ) {
		// check url in not empty.
		if ( empty( $url ) ) {
			$this->errors[] = __('meetup group url is empty please provide meetup group url.',  'xt-meetup-import');
			return '';
		}
		// check provide url is valid.
		if ( filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
		    $this->errors[] = __( 'Not valid meetup group url please provide valid meetup group url.',  'xt-meetup-import');
			return '';
		}
		// check provided url is meetup url.
		if ( strpos( $url, 'meetup.com' ) === false ) {
			$this->errors[] = __( 'Not valid meetup group url please provide valid meetup group url.',  'xt-meetup-import');
			return '';
		}
		// add www to url if not there.
		if ( strpos( $url, 'www.' ) === false ) {
			$url = str_replace( 'https://', 'https://www.', $url );
			$url = str_replace( 'http://', 'http://www.', $url );
		}

		return $url;
	}

	/**
	 * Display Errors
	 *
	 * @since    1.0.0
	 */
	public function xtmi_display_errors() {
		if ( ! empty( $this->errors ) ) {
			foreach ( $this->errors as $error ) :
			    ?>
			    <div class="notice notice-error is-dismissible">
			        <p><?php echo $error; ?></p>
			    </div>
			    <?php
			endforeach;
		}
	}

	/**
	 * Display Insert Success
	 *
	 * @since    1.0.0
	 */
	public function xtmi_display_insert_success() {
	    ?>
	    <div class="notice notice-success is-dismissible">
	        <p><?php _e( 'Meetup URL for import inserted successfully.', 'xt-meetup-import' ) ?></p>
	    </div>
	    <?php
	}

	/**
	 * Display Delete Success
	 *
	 * @since    1.0.0
	 */
	public function xtmi_display_delete_success() {
	    ?>
	    <div class="notice notice-success is-dismissible">
	        <p><?php _e( 'Meetup URL deleted successfully.', 'xt-meetup-import' ) ?></p>
	    </div>
	    <?php
	}

	/**
	 * Display Settings Success
	 *
	 * @since    1.0.0
	 */
	public function xtmi_display_setting_success() {
	    ?>
	    <div class="notice notice-success is-dismissible">
	        <p><?php _e( 'Meetup import settings saved successfully.', 'xt-meetup-import' ) ?></p>
	    </div>
	    <?php
	}

	/**
	 * Set Success message
	 *
	 * @since    1.0.0
	 */
	public function xtmi_set_success_message( $message = '' ){
		$_SESSION['succ_message'] = $message;
	}

	/**
	 * Register Session
	 *
	 * @since    1.0.0
	 */
	public function xtmi_register_session(){
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * Set Success message
	 *
	 * @since    1.0.0
	 */
	public function xtmi_display_success_message() {
		if ( isset( $_SESSION['succ_message'] ) && $_SESSION['succ_message'] != "" ) {
		?>
	    <div class="notice notice-success is-dismissible">
	        <p><?php esc_html_e( $_SESSION['succ_message'] ); ?></p>
	    </div>
	    <?php
		unset( $_SESSION['succ_message'] );
		}
	}

}
