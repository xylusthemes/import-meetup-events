<?php
/**
 * Plugin Name:       Import Meetup Events
 * Plugin URI:        https://xylusthemes.com/plugins/import-meetup-events/
 * Description:       Import Meetup Events allows you to import Meetup (meetup.com) events into your WordPress site effortlessly.
 * Version:           1.6.7
 * Author:            xylus
 * Author URI:        http://xylusthemes.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       import-meetup-events
 * Domain Path:       /languages
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 * @package    Import_Meetup_Events
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Import_Meetup_Events')):

/**
 * Main Import Meetup Events class
 */
    class Import_Meetup_Events {

        /** Singleton *************************************************************/
        /**
         * Import_Meetup_Events The one true Import_Meetup_Events.
         */
        private static $instance;
        public $common, $cpt, $meetup, $admin, $manage_import, $ime, $tec, $em, $eventon, $event_organizer, $aioec, $my_calendar, $cron, $common_pro, $authorize;

        /**
         * Main Import Meetup Events Instance.
         *
         * Insure that only one instance of Import_Meetup_Events exists in memory at any one time.
         * Also prevents needing to define globals all over the place.
         *
         * @since 1.0.0
         * @static object $instance
         * @uses Import_Meetup_Events::setup_constants() Setup the constants needed.
         * @uses Import_Meetup_Events::includes() Include the required files.
         * @uses Import_Meetup_Events::laod_textdomain() load the language files.
         * @see run_Import_Meetup_Events()
         * @return object| Import Meetup Events the one true Import Meetup Events.
         */
        public static function instance() {
            if (!isset(self::$instance) && !(self::$instance instanceof Import_Meetup_Events)) {
                self::$instance = new Import_Meetup_Events;
                self::$instance->setup_constants();

                add_action('plugins_loaded', array(self::$instance, 'load_textdomain'));
                add_action( 'plugins_loaded', array( self::$instance, 'load_authorize_class' ), 20 );
                add_action('wp_enqueue_scripts', array(self::$instance, 'ime_enqueue_style'));
                add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( self::$instance, 'ime_setting_doc_links' ) );

                self::$instance->includes();
                self::$instance->common = new Import_Meetup_Events_Common();
                self::$instance->cpt = new Import_Meetup_Events_Cpt();
                self::$instance->meetup = new Import_Meetup_Events_Meetup();
                self::$instance->admin = new Import_Meetup_Events_Admin();
                if (ime_is_pro()) {
                    self::$instance->manage_import = new Import_Meetup_Events_Pro_Manage_Import();
                } else {
                    self::$instance->manage_import = new Import_Meetup_Events_Manage_Import();
                }
                self::$instance->ime = new Import_Meetup_Events_IME();
                self::$instance->tec = new Import_Meetup_Events_TEC();
                self::$instance->em = new Import_Meetup_Events_EM();
                self::$instance->eventon = new Import_Meetup_Events_EventON();
                self::$instance->event_organizer = new Import_Meetup_Events_Event_Organizer();
                self::$instance->aioec = new Import_Meetup_Events_Aioec();
                self::$instance->my_calendar = new Import_Meetup_Events_My_Calendar();

            }
            return self::$instance;
        }

        /** Magic Methods *********************************************************/

        /**
         * A dummy constructor to prevent Import_Meetup_Events from being loaded more than once.
         *
         * @since 1.0.0
         * @see Import_Meetup_Events::instance()
         * @see run_Import_Meetup_Events()
         */
        private function __construct() {
            /* Do nothing here */
        }

        /**
         * A dummy magic method to prevent Import_Meetup_Events from being cloned.
         *
         * @since 1.0.0
         */
        public function __clone() {
            _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'import-meetup-events'), '1.6.7');
        }

        /**
         * A dummy magic method to prevent Import_Meetup_Events from being unserialized.
         *
         * @since 1.0.0
         */
        public function __wakeup() {
            _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'import-meetup-events'), '1.6.7');
        }

        /**
         * Setup plugins constants.
         *
         * @access private
         * @since 1.0.0
         * @return void
         */
        private function setup_constants() {

            // Plugin version.
            if (!defined('IME_VERSION')) {
                define('IME_VERSION', '1.6.7');
            }

            // Minimum Pro plugin version.
            if( ! defined( 'IME_MIN_PRO_VERSION' ) ){
                define( 'IME_MIN_PRO_VERSION', '1.6.1' );
            }

            // Plugin folder Path.
            if (!defined('IME_PLUGIN_DIR')) {
                define('IME_PLUGIN_DIR', plugin_dir_path(__FILE__));
            }

            // Plugin folder URL.
            if (!defined('IME_PLUGIN_URL')) {
                define('IME_PLUGIN_URL', plugin_dir_url(__FILE__));
            }

            // Plugin root file.
            if (!defined('IME_PLUGIN_FILE')) {
                define('IME_PLUGIN_FILE', __FILE__);
            }

            // Options
            if (!defined('IME_OPTIONS')) {
                define('IME_OPTIONS', 'xtmi_meetup_options');
            }

            // Pro plugin Buy now Link.
            if (!defined('IME_PLUGIN_BUY_NOW_URL')) {
                define('IME_PLUGIN_BUY_NOW_URL', 'http://xylusthemes.com/plugins/import-meetup-events/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin');
            }
        }

        /**
         * Include required files.
         *
         * @access private
         * @since 1.0.0
         * @return void
         */
        private function includes() {
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-common.php';
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-list-table.php';
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-admin.php';
            if (ime_is_pro()) {
                require_once IMEPRO_PLUGIN_DIR . 'includes/class-import-meetup-events-manage-import.php';
            } else {
                require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-manage-import.php';
            }
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-cpt.php';
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-meetup.php';
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-ime.php';
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-tec.php';
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-em.php';
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-eventon.php';
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-event_organizer.php';
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-aioec.php';
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-my-calendar.php';
            require_once IME_PLUGIN_DIR . 'includes/class-ime-plugin-deactivation.php';
            require_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-api.php';
            require_once IME_PLUGIN_DIR . 'includes/libs/IMEParsedown.php';
            // Gutenberg Block
            require_once IME_PLUGIN_DIR . 'blocks/meetup-events/index.php';
        }

        /**
         * Loads the plugin language files.
         *
         * @access public
         * @since 1.0.0
         * @return void
         */
        public function load_textdomain() {
            load_plugin_textdomain(
                'import-meetup-events',
                false,
                basename(dirname(__FILE__)) . '/languages'
            );
        }

        /**
         * IME setting And docs link add in plugin page.
         *
         * @since 1.0
         * @return void
         */
        public function ime_setting_doc_links( $links ) {
            $ime_setting_doc_link = array(
                'ime-event-setting' => sprintf(
                    '<a href="%s">%s</a>',
                    esc_url( admin_url( 'admin.php?page=meetup_import&tab=settings' ) ),
                    esc_html__( 'Setting', 'import-meetup-events' )
                ),
                'ime-event-docs' => sprintf(
                    '<a target="_blank" href="%s">%s</a>',
                    esc_url( 'https://docs.xylusthemes.com/docs/import-meetup-events/' ),
                    esc_html__( 'Docs', 'import-meetup-events' )
                ),
            );

            $upgrade_to_pro = array();
            if( !ime_is_pro() ){
                $upgrade_to_pro = array( 'ime-event-pro-link' => sprintf(
                    '<a href="%s" target="_blank" style="color:#1da867;font-weight: 900;">%s</a>',
                    esc_url( 'https://xylusthemes.com/plugins/import-meetup-events/' ),
                    esc_html__( 'Upgrade to Pro', 'import-meetup-events' )
                ) ) ;
            }
            return array_merge( $links, $ime_setting_doc_link, $upgrade_to_pro );
        }

        /**
         * Loads the Meetup authorize class
         *
         * @access public
         * @since 1.5
         * @return void
         */
        public function load_authorize_class(){

            if( !class_exists( 'Import_Meetup_Events_Authorize', false ) ){
                include_once IME_PLUGIN_DIR . 'includes/class-import-meetup-events-authorize.php';
                global $ime_events;
                if( class_exists('Import_Meetup_Events_Authorize', false ) && !empty( $ime_events ) ){
                    $ime_events->authorize = new Import_Meetup_Events_Authorize();
                }
            }
        }

        /**
         * enqueue style front-end
         *
         * @access public
         * @since 1.0.0
         * @return void
         */
        public function ime_enqueue_style() {
            $css_dir = IME_PLUGIN_URL . 'assets/css/';
            wp_enqueue_style('font-awesome', $css_dir . 'font-awesome.min.css', false, IME_VERSION );
            wp_enqueue_style('import-meetup-events-front', $css_dir . 'import-meetup-events.css', false, IME_VERSION );
            wp_enqueue_style('import-meetup-events-front-style2', $css_dir . 'grid-style2.css', false, IME_VERSION );
        }

    }

endif; // End If class exists check.

/**
 * The main function for that returns Import_Meetup_Events
 *
 * The main function responsible for returning the one true Import_Meetup_Events
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $ime_events = run_import_meetup_events(); ?>
 *
 * @since 1.0.0
 * @return object|Import_Meetup_Events The one true Import_Meetup_Events Instance.
 */
function run_import_meetup_events() {
    return Import_Meetup_Events::instance();
}

/**
 * Get Import events setting options
 *
 * @since 1.0
 * @return void
 */
function ime_get_import_options($type = '') {
    $ime_options = get_option(IME_OPTIONS);
    return $ime_options;
}

// Get Import_Meetup_Events Running.
global $ime_events, $ime_errors, $ime_success_msg, $ime_warnings, $ime_info_msg;
$ime_events = run_import_meetup_events();
$ime_errors = $ime_warnings = $ime_success_msg = $ime_info_msg = array();

/**
 * The code that runs during plugin activation.
 *
 * @since 1.1.1
 */
function ime_activate_import_meetup_events() {
    global $ime_events;
    $ime_events->cpt->register_event_post_type();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'ime_activate_import_meetup_events');
