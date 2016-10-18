<?php
/**
 * Content for meetup import page.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    XT_Meetup_Import
 * @subpackage XT_Meetup_Import/admin/partials
 */

?>
<div class="wrap">
    <h2><?php esc_html_e( 'Meetup import', 'xt-meetup-import' ); ?></h2>
    <?php
    // Set Default Tab to S`ettings.
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';
    ?>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <div id="postbox-container-1" class="postbox-container">

            </div>
            <div id="postbox-container-2" class="postbox-container">

                <h2 class="nav-tab-wrapper">
                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>" class="nav-tab <?php if ( $active_tab == 'settings' ) { echo 'nav-tab-active'; } ?>">
                        <?php esc_html_e( 'Settings', 'xt-meetup-import' ); ?>
                    </a>
                    <?php
                    if ( ! function_exists( 'is_plugin_active' ) ) {
            			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            		}
            		if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) ) { ?>
                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'tec_import' ) ); ?>" class="nav-tab <?php if ( $active_tab == 'tec_import' ) { echo 'nav-tab-active'; } ?>">
                        <?php esc_html_e( 'Import for The Events Calendar', 'xt-meetup-import' ); ?>
                    </a>
                    <?php } ?>

                    <?php if ( is_plugin_active( 'events-manager/events-manager.php' ) ) { ?>
                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'em_import' ) ); ?>" class="nav-tab <?php if ( $active_tab == 'em_import' ) { echo 'nav-tab-active'; } ?>">
                        <?php esc_html_e( 'Import for Events Manager', 'xt-meetup-import' ); ?>
                    </a>
                    <?php } ?>

                </h2>
                <?php
                    if ( $active_tab == 'settings' ) {
                        require_once 'xt-meetup-import-tab-settings.php';
                    } elseif ( $active_tab == 'tec_import' ) {
                        $xtmi_event_cats = get_terms( 'tribe_events_cat', array( 'hide_empty' => 0 ) );
                        require_once 'xt-meetup-import-tec-tab-content.php';
                    } elseif ( $active_tab == 'em_import' ) {
                        $xt_event_cats = get_terms( XTMI_EM_TAXONOMY, array( 'hide_empty' => 0 ) );
                        require_once 'xt-meetup-import-em-tab-content.php';
                    }
                    ?>
                </div>
        </div>
        <br class="clear">
    </div>
</div>
