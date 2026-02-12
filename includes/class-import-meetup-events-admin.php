<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package     Import_Meetup_Events
 * @subpackage  Import_Meetup_Events/admin
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package     Import_Meetup_Events
 * @subpackage  Import_Meetup_Events/admin
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class Import_Meetup_Events_Admin {


	public $adminpage_url;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->adminpage_url = admin_url('admin.php?page=meetup_import' );

		add_action( 'init', array( $this, 'register_scheduled_import_cpt' ) );
		add_action( 'init', array( $this, 'register_history_cpt' ) );
		add_action( 'admin_init', array( $this, 'ime_check_delete_pst_event_as_status' ) );
		add_action( 'ime_delete_past_events_as', array( $this, 'ime_delete_past_events' ) );
		add_action( 'admin_init', array( $this, 'setup_success_messages' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_pages') );
		add_filter( 'submenu_file', array( $this, 'get_selected_tab_submenu_ime' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles') );
		add_action( 'admin_notices', array( $this,'ime_remove_default_notices' ), 1 );
		add_action( 'ime_display_all_notice', array( $this, 'ime_display_notices' ) );
		add_filter( 'admin_footer_text', array( $this, 'add_import_meetup_events_credit' ) );
		add_action( 'admin_init', array( $this, 'ime_wp_cron_check' ) );
	}

	/**
	 * Create the Admin menu and submenu and assign their links to global varibles.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_menu_pages(){
		global $submenu;	

		add_menu_page( __( 'Meetup Import', 'import-meetup-events' ), __( 'Meetup Import', 'import-meetup-events' ), 'manage_options', 'meetup_import', array( $this, 'admin_page' ), 'dashicons-calendar-alt', '30' );
		$submenu['meetup_import'][] = array( __( 'Dashboard', 'import-meetup-events' ), 'manage_options', admin_url( 'admin.php?page=meetup_import&tab=dashboard' ) );
		$submenu['meetup_import'][] = array( __( 'Meetup Import', 'import-meetup-events' ), 'manage_options', admin_url( 'admin.php?page=meetup_import&tab=meetup' ) );
		$submenu['meetup_import'][] = array( __( 'Schedule Import', 'import-meetup-events' ), 'manage_options', admin_url( 'admin.php?page=meetup_import&tab=scheduled' ) );
		$submenu['meetup_import'][] = array( __( 'Import History', 'import-meetup-events' ), 'manage_options', admin_url( 'admin.php?page=meetup_import&tab=history' ) );
		$submenu['meetup_import'][] = array( __( 'Settings', 'import-meetup-events' ), 'manage_options', admin_url( 'admin.php?page=meetup_import&tab=settings' ));
		$submenu['meetup_import'][] = array( __( 'Shortcodes', 'import-meetup-events' ), 'manage_options', admin_url( 'admin.php?page=meetup_import&tab=shortcodes' ));
		$submenu['meetup_import'][] = array( __( 'Support', 'import-meetup-events' ), 'manage_options', admin_url( 'admin.php?page=meetup_import&tab=support' ));
		$submenu['meetup_import'][] = array( __( 'Wizard', 'import-meetup-events' ), 'manage_options', admin_url( 'admin.php?page=meetup_import&tab=ime_setup_wizard' ));
		if( !ime_is_pro() ){
			$submenu['meetup_import'][] = array( '<li class="ime_upgrade_pro current">' . __( 'Upgrade to Pro', 'import-meetup-events' ) . '</li>', 'manage_options', esc_url( "https://xylusthemes.com/plugins/import-meetup-events/") );
		}
	}

	/**
	 * Check if WP-Cron is enabled
	 *
	 * Checks if WP-Cron is enabled and if the current page is the scheduled imports page.
	 * If WP-Cron is disabled, it will display an error message.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function ime_wp_cron_check() {
		global $ime_errors;
		
		$page = isset($_GET['page']) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : '';
		$tab  = isset($_GET['tab'])  ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) )  : '';

		if ( ! is_admin() || empty($page) || empty($tab) || $page !== 'meetup_import' || $tab !== 'scheduled' ) {
			return;
		}

		if ( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ) {
			$ime_errors[] = __(
				'<strong>Scheduled imports are paused.</strong> WP-Cron is currently disabled on your site, so Meetup scheduled imports will not run automatically. Please enable WP-Cron or set up a server cron job to keep imports running smoothly.',
				'import-meetup-events'
			);

		}
	}

	/**
	 * Remove All Notices
	 */
	public function ime_remove_default_notices() {
		// Remove default notices display.
		remove_action( 'admin_notices', 'wp_admin_notices' );
		remove_action( 'all_admin_notices', 'wp_admin_notices' );
	}

	/**
	 * Load Admin Scripts
	 *
	 * Enqueues the required admin scripts.
	 *
	 * @since 1.0
	 * @param string $hook Page hook
	 * @return void
	 */
	function enqueue_admin_scripts( $hook ) {

		$js_dir  = IME_PLUGIN_URL . 'assets/js/';
		wp_register_script( 'import-meetup-events', $js_dir . 'import-meetup-events-admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'wp-color-picker'), IME_VERSION, true );
		// wp_localize_script( 'import-meetup-events', 'imeImport', array( 'ajax_url' => admin_url('admin-ajax.php'), 'nonce'    => $nonce, ) );
		wp_enqueue_script( 'import-meetup-events' );

		
		if( isset( $_GET['tab'] ) && $_GET['tab'] == 'ime_setup_wizard' ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_register_script( 'import-meetup-events-wizard-js', $js_dir . 'import-meetup-events-wizard.js',  array( 'jquery', 'jquery-ui-core' ), IME_VERSION, false );
			wp_enqueue_script( 'import-meetup-events-wizard-js' );
		}
		
	}

	/**
	 * Load Admin Styles.
	 *
	 * Enqueues the required admin styles.
	 *
	 * @since 1.0
	 * @param string $hook Page hook
	 * @return void
	 */
	function enqueue_admin_styles( $hook ) {
		global $pagenow;
		$css_dir = IME_PLUGIN_URL . 'assets/css/';
		$page    = isset( $_GET['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Load styles on plugin admin page
		if ( 'meetup_import' === $page ) {
			wp_enqueue_style('jquery-ui', $css_dir . 'jquery-ui.css', false, "1.12.0" );
			wp_enqueue_style('import-meetup-events', $css_dir . 'import-meetup-events-admin.css', false, IME_VERSION );
			wp_enqueue_style('wp-color-picker');

			$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( 'ime_setup_wizard' === $tab ) {
				wp_enqueue_style( 'import-meetup-events-wizard-css', $css_dir . 'import-meetup-events-wizard.css', false, IME_VERSION );
			}
		}

		// Load styles on widgets/post screen
		if ( in_array( $pagenow, [ 'widgets.php', 'post.php', 'post-new.php' ], true ) || ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && in_array( $_GET['page'], [ 'meetup_import' ], true ) ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_enqueue_style( 'jquery-ui', $css_dir . 'jquery-ui.css', false, '1.12.0' );
			wp_enqueue_style( 'import-meetup-events-admin-global', $css_dir . 'import-meetup-events-admin-global.css', false, IME_VERSION );
			wp_enqueue_style( 'wp-color-picker' );
		}

	}

	/**
	 * Load Admin page.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function admin_page() {
		global $ime_events;

		$active_tab = isset( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) )  : 'meetup'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$gettab     = str_replace( 'by_', '', $active_tab );
		$gettab     = ucwords( str_replace( '_', ' & ', $gettab ) );
		if( $active_tab == 'support' ){
			$page_title = 'Support & Help';
		}elseif( $active_tab == 'meetup' ){
			$page_title = 'Meetup Import';
		}elseif( $active_tab == 'scheduled' ){
			$page_title = 'Scheduled Import';
		}else{
			$page_title = $gettab;
		}

		if( $active_tab == 'ime_setup_wizard' ){
			require_once IME_PLUGIN_DIR . '/templates/admin/ime-setup-wizard.php';
			exit();
		}

		$posts_header_result = $ime_events->common->ime_render_common_header( $page_title );

		if( $active_tab != 'dashboard' ){
			?>
				<div class="ime-container" style="margin-top: 60px;">
					<div class="ime-wrap" >
						<div id="poststuff">
							<div id="post-body" class="metabox-holder columns-2">
								<?php 
									do_action( 'ime_display_all_notice' );
								?>
								<div class="delete_notice"></div>
								<div id="postbox-container-2" class="postbox-container">
									<div class="ime-app">
										<div class="ime-tabs">
											<div class="tabs-scroller">
												<div class="var-tabs var-tabs--item-horizontal var-tabs--layout-horizontal-padding">
													<div class="var-tabs__tab-wrap var-tabs--layout-horizontal">
														<a href="?page=meetup_import&tab=meetup" class="var-tab <?php echo $active_tab == 'meetup' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Import', 'import-meetup-events' ); ?></span>
														</a>
														<a href="?page=meetup_import&tab=scheduled" class="var-tab <?php echo ( $active_tab == 'scheduled' || $active_tab == 'scheduled' )  ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Schedule Import', 'import-meetup-events' ); if( !ime_is_pro() ){ echo '<div class="ime-pro-badge"> PRO </div>'; } ?></span>
														</a>
														<a href="?page=meetup_import&tab=history" class="var-tab <?php echo $active_tab == 'history' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'History', 'import-meetup-events' ); ?></span>
														</a>
														<a href="?page=meetup_import&tab=settings" class="var-tab <?php echo $active_tab == 'settings' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Setting', 'import-meetup-events' ); ?></span>
														</a>
														<a href="?page=meetup_import&tab=shortcodes" class="var-tab <?php echo $active_tab == 'shortcodes' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Shortcodes', 'import-meetup-events' ); ?></span>
														</a>
														<a href="?page=meetup_import&tab=support" class="var-tab <?php echo $active_tab == 'support' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Support & Help', 'import-meetup-events' ); ?></span>
														</a>
													</div>
												</div>
											</div>
										</div>
									</div>

									<?php
										if ( $active_tab == 'meetup' ) {
											require_once IME_PLUGIN_DIR . '/templates/admin/meetup-import-events.php';
										} elseif ( $active_tab == 'settings' ) {
											require_once IME_PLUGIN_DIR . '/templates/admin/import-meetup-events-settings.php';
										} elseif ( $active_tab == 'scheduled' ) {
											if( ime_is_pro() ){
												require_once IMEPRO_PLUGIN_DIR . '/templates/scheduled-import-events.php';
											}else{
												?>
													<div class="ime-blur-filter" >
														<?php do_action( 'ime_render_pro_notice' ); ?>
													</div>
												<?php
											}
										}elseif ( $active_tab == 'history' ) {
											require_once IME_PLUGIN_DIR . '/templates/admin/import-meetup-events-history.php';
										} elseif ( $active_tab == 'support' ) {
											require_once IME_PLUGIN_DIR . '/templates/admin/import-meetup-events-support.php';
										}elseif ( 'shortcodes' === $active_tab ) {
											require_once IME_PLUGIN_DIR . '/templates/admin/import-meetup-events-shortcode.php';
										}
									?>
								</div>
							</div>
							<br class="clear">
						</div>
					</div>
				</div>
			<?php
		}else{
			require_once IME_PLUGIN_DIR . '/templates/admin/ime-dashboard.php';
		}
		$posts_footer_result = $ime_events->common->ime_render_common_footer();
	}


	/**
	 * Display notices in admin.
	 *
	 * @since    1.0.0
	 */
	public function ime_display_notices() {
		global $ime_errors, $ime_success_msg, $ime_warnings, $ime_info_msg;

		if ( ! empty( $ime_errors ) ) {
			foreach ( $ime_errors as $error ) :
				?>
				<div class="notice ime_notice notice-error is-dismissible">
					<p><?php echo $error; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				</div>
				<?php
			endforeach;
		}

		if ( ! empty( $ime_success_msg ) ) {
			foreach ( $ime_success_msg as $success ) :
				?>
				<div class="notice ime_notice notice-success is-dismissible">
					<p><?php echo $success; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				</div>
				<?php
			endforeach;
		}

		if ( ! empty( $ime_warnings ) ) {
			foreach ( $ime_warnings as $warning ) :
				?>
				<div class="notice ime_notice notice-warning is-dismissible">
					<p><?php echo $warning; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				</div>
				<?php
			endforeach;
		}

		if ( ! empty( $ime_info_msg ) ) {
			foreach ( $ime_info_msg as $info ) :
				?>
				<div class="notice ime_notice notice-info is-dismissible">
					<p><?php echo $info; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				</div>
				<?php
			endforeach;
		}

	}

	/**
	 * Register custom post type for scheduled imports.
	 *
	 * @since    1.0.0
	 */
	public function register_scheduled_import_cpt() {
		$labels = array(
			'name'               => _x( 'Scheduled Import', 'post type general name', 'import-meetup-events' ),
			'singular_name'      => _x( 'Scheduled Import', 'post type singular name', 'import-meetup-events' ),
			'menu_name'          => _x( 'Scheduled Imports', 'admin menu', 'import-meetup-events' ),
			'name_admin_bar'     => _x( 'Scheduled Import', 'add new on admin bar', 'import-meetup-events' ),
			'add_new'            => _x( 'Add New', 'book', 'import-meetup-events' ),
			'add_new_item'       => __( 'Add New Import', 'import-meetup-events' ),
			'new_item'           => __( 'New Import', 'import-meetup-events' ),
			'edit_item'          => __( 'Edit Import', 'import-meetup-events' ),
			'view_item'          => __( 'View Import', 'import-meetup-events' ),
			'all_items'          => __( 'All Scheduled Imports', 'import-meetup-events' ),
			'search_items'       => __( 'Search Scheduled Imports', 'import-meetup-events' ),
			'parent_item_colon'  => __( 'Parent Imports:', 'import-meetup-events' ),
			'not_found'          => __( 'No Imports found.', 'import-meetup-events' ),
			'not_found_in_trash' => __( 'No Imports found in Trash.', 'import-meetup-events' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Scheduled Imports.', 'import-meetup-events' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => false,
			'show_in_nav_menus'  => false,
			'can_export'         => false,
			'rewrite'            => false,
			'capability_type'    => 'page',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
			'menu_position'		=> 5,
		);

		register_post_type( 'ime_scheduled_import', $args );
	}

	/**
	 * Register custom post type for Save import history.
	 *
	 * @since    1.0.0
	 */
	public function register_history_cpt() {
		$labels = array(
			'name'               => _x( 'Import History', 'post type general name', 'import-meetup-events' ),
			'singular_name'      => _x( 'Import History', 'post type singular name', 'import-meetup-events' ),
			'menu_name'          => _x( 'Import History', 'admin menu', 'import-meetup-events' ),
			'name_admin_bar'     => _x( 'Import History', 'add new on admin bar', 'import-meetup-events' ),
			'add_new'            => _x( 'Add New', 'book', 'import-meetup-events' ),
			'add_new_item'       => __( 'Add New', 'import-meetup-events' ),
			'new_item'           => __( 'New History', 'import-meetup-events' ),
			'edit_item'          => __( 'Edit History', 'import-meetup-events' ),
			'view_item'          => __( 'View History', 'import-meetup-events' ),
			'all_items'          => __( 'All Import History', 'import-meetup-events' ),
			'search_items'       => __( 'Search History', 'import-meetup-events' ),
			'parent_item_colon'  => __( 'Parent History:', 'import-meetup-events' ),
			'not_found'          => __( 'No History found.', 'import-meetup-events' ),
			'not_found_in_trash' => __( 'No History found in Trash.', 'import-meetup-events' ),
		);

		$args = array(
			'labels'             => $labels,
	        'description'        => __( 'Import History', 'import-meetup-events' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => false,
			'show_in_nav_menus'  => false,
			'can_export'         => false,
			'rewrite'            => false,
			'capability_type'    => 'page',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
			'menu_position'      => 5,
		);

		register_post_type( 'ime_import_history', $args );
	}


	/**
	 * Add WP Event Aggregator ratting text
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_import_meetup_events_credit( $footer_text ){
		$page = isset( $_GET['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : '';  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $page != '' && $page == 'meetup_import' ) {
			$rate_url = 'https://wordpress.org/support/plugin/import-meetup-events/reviews/?rate=5#new-post';

			$footer_text .= sprintf(
				esc_html__( ' Rate %1$sImport Meetup Events%2$s %3$s', 'import-meetup-events' ), // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
				'<strong>',
				'</strong>',
				'<a href="' . $rate_url . '" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}
		return $footer_text;
	}

	/**
	 * Get Plugin array
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function get_xyuls_themes_plugins(){
		return array(
			'wp-event-aggregator' => esc_html__( 'WP Event Aggregator', 'import-meetup-events' ),
			'import-facebook-events' => esc_html__( 'Import Facebook Events', 'import-meetup-events' ),
			'import-eventbrite-events' => esc_html__( 'Import Eventbrite Events', 'import-meetup-events' ),
			'wp-bulk-delete' => esc_html__( 'WP Bulk Delete', 'import-meetup-events' ),
		);
	}

	/**
	 * Get Plugin Details.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function get_wporg_plugin( $slug ){

		if( $slug == '' ){
			return false;
		}

		$transient_name = 'support_plugin_box'.$slug;
		$plugin_data = get_transient( $transient_name );
		if( false === $plugin_data ){
			if ( ! function_exists( 'plugins_api' ) ) {
				include_once ABSPATH . '/wp-admin/includes/plugin-install.php';
			}

			$plugin_data = plugins_api( 'plugin_information', array(
				'slug' => $slug,
				'is_ssl' => is_ssl(),
				'fields' => array(
					'banners' => true,
					'active_installs' => true,
				)
			) );

			if ( ! is_wp_error( $plugin_data ) ) {
				set_transient( $transient_name, $plugin_data, 24 * HOUR_IN_SECONDS );
			} else {
				// If there was a bug on the Current Request just leave
				return false;
			}
		}
		return $plugin_data;
	}

	/**
	 * Tab Submenu got selected.
	 *
	 * @since 1.5.6
	 * @return void
	 */
	public function get_selected_tab_submenu_ime( $submenu_file ){
		 // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if( !empty( $_GET['page'] ) && esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) == 'meetup_import' ){ 
			$allowed_tabs = array( 'dashboard', 'meetup', 'scheduled', 'history', 'settings', 'shortcodes', 'support' );
			$tab = isset( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : 'meetup';  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if( in_array( $tab, $allowed_tabs ) ){
				$submenu_file = admin_url( 'admin.php?page=meetup_import&tab='.$tab );
			}
		}
		return $submenu_file;
	}

	/**
	 * Setup Success Messages.
	 *
	 * @since    1.0.0
	 */
	public function setup_success_messages() {
		global $ime_success_msg, $ime_errors;
		$mauthorized = isset( $_GET['m_authorize'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['m_authorize'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( !empty( $mauthorized ) ) { 
			if( trim( $mauthorized ) == '1' ){ 
				$ime_success_msg[] = esc_html__( 'Authorized Successfully.', 'import-meetup-events' );	
			} elseif( trim( $mauthorized ) == '2' ){
				$ime_errors[] = esc_html__( 'Please insert Meetup Auth Key and Secret.', 'import-meetup-events' );	
			} elseif( trim( $mauthorized ) == '0' ){
				$ime_errors[] = esc_html__( 'Something went wrong during authorization. Please try again.', 'import-meetup-events' );	
			}
		}
	}

	/**
	 * Render Delete Past Event in the meetup_events post type
	 * @return void
	 */
	public function ime_delete_past_events() {

		$current_time = current_time('timestamp');
		$args         = array(
			'post_type'       => 'meetup_events',
			'posts_per_page'  => 100,
			'post_status'     => 'publish',
			'fields'          => 'ids',
			'meta_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'end_ts',
					'value'   => current_time( 'timestamp' ) - ( 24 * 3600 ),
					'compare' => '<',      
					'type'    => 'NUMERIC',
				),
			),
		);
		$events = get_posts( $args );

		if ( empty( $events ) ) {
			return;
		}

		foreach ( $events as $event_id ) {
			wp_trash_post( $event_id );
		}
	}

	/**
	 * re-create if the past event cron is delete
	 */
	public function ime_check_delete_pst_event_as_status() {

	if ( ! class_exists( 'ActionScheduler' ) ) {
			return;
		}

		$ime_options        = get_option( IME_OPTIONS );
		$move_peit_ifevents = isset( $ime_options['move_peit'] ) ? $ime_options['move_peit'] : 'no';

		if ( 'yes' === $move_peit_ifevents ) {
			if ( ! as_next_scheduled_action( 'ime_delete_past_events_as' ) ) {
				as_schedule_recurring_action(
					time(),
					DAY_IN_SECONDS,
					'ime_delete_past_events_as',
					array(),
					'ime-import'
				);
			}
		} else {
			as_unschedule_all_actions( 'ime_delete_past_events_as', array(), 'ime-import' );
		}
	}
}