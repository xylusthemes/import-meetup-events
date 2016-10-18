<?php
/**
 * Plugin Name:       Import Meetup Events
 * Plugin URI:        https://xylusthemes.com/plugins/import-meetup-events/
 * Description:       Import Meetup Events allows you to automatically import Meetup (meetup.com) events into your WordPress site( The Events Calendar and Events Manager ).
 * Version:           1.0.0
 * Author:            xylus
 * Author URI:        http://xylusthemes.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       xt-meetup-import
 * Domain Path:       /languages
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 * @package    XT_Meetup_Import
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Global variables.
 */
define( 'XTMI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'XTMI_ADMIN_PATH', plugin_dir_path( __FILE__ ) . 'admin/' );
define( 'XTMI_INCLUDES_PATH', plugin_dir_path( __FILE__ ) . 'includes/' );
define( 'XTMI_OPTIONS', 'xtmi_meetup_options' );
define( 'XTMI_TEC_MGROUP_POSTTYPE', 'tec_meetup_group' );
define( 'XTMI_EM_MGROUP_POSTTYPE', 'em_meetup_group' );

// EM
if ( defined( 'EM_POST_TYPE_EVENT' ) ) {
	define( 'XTMI_EM_POSTTYPE', EM_POST_TYPE_EVENT );
} else {
	define( 'XTMI_EM_POSTTYPE', 'event' );
}
if ( defined( 'EM_TAXONOMY_CATEGORY' ) ) {
	define( 'XTMI_EM_TAXONOMY',EM_TAXONOMY_CATEGORY );
} else {
	define( 'XTMI_EM_TAXONOMY','event-categories' );
}
if ( defined( 'EM_POST_TYPE_LOCATION' ) ) {
	define( 'XTMI_LOCATION_POSTTYPE',EM_POST_TYPE_LOCATION );
} else {
	define( 'XTMI_LOCATION_POSTTYPE','location' );
}

/**
 * Runs during plugin activation.
 */
function activate_xt_meetup_import() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xt-meetup-import-activator.php';
	XT_Meetup_Import_Activator::activate();
}

/**
 * Runs during plugin deactivation.
 */
function deactivate_xt_meetup_import() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xt-meetup-import-deactivator.php';
	XT_Meetup_Import_Deactivator::deactivate();
}

/**
* Register Plugin activation and deactivation hooks
*/
register_activation_hook( __FILE__, 'activate_xt_meetup_import' );
register_deactivation_hook( __FILE__, 'deactivate_xt_meetup_import' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-xt-meetup-import.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_xt_meetup_import() {

	$plugin = new XT_Meetup_Import();
	$plugin->run();
	$plugin->xtmi_check_requirements( plugin_basename( __FILE__ ) );

}
run_xt_meetup_import();
