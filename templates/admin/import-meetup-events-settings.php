<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $ime_events;
$meetup_options = get_option( IME_OPTIONS );
$meetup_oauth_key = isset( $meetup_options['meetup_oauth_key'] ) ? $meetup_options['meetup_oauth_key'] : '';
$meetup_oauth_secret = isset( $meetup_options['meetup_oauth_secret'] ) ? $meetup_options['meetup_oauth_secret'] : '';

$ime_user_token_options = get_option( 'ime_user_token_options', array() );
$ime_authorized_user = get_option( 'ime_authorized_user', array() );
$ime_google_maps_api_key = get_option( 'ime_google_maps_api_key', array() );

if( is_object( $ime_authorized_user ) ){
    $ime_authorized_user = (array)$ime_authorized_user;
}

?>

<div class="ime-card" style="margin-top:20px;" >
    <div class="ime-content"  aria-expanded="true" style="padding: 10px 20px;">
        <div id="postbox-container-2" class="postbox-container">
            <div class="">
                <div class="ime-app">
                    <div class="ime-tabs">
                        <div class="tabs-scroller">
                            <div class="var-tabs var-tabs--item-horizontal var-tabs--layout-horizontal-padding ime_navbar nav-tab-wrapper">
                                <div class="var-tabs__tab-wrap var-tabs--layout-horizontal ime_nav_tabs">
                                    <a href="javascript:void(0)" class="var-tab var-tab--active ime_tab_link"  data-tab="settings">
                                        <span class="tab-label"><?php esc_attr_e( 'General Settings', 'import-meetup-events' ); ?></span>
                                    </a>
                                    <a href="javascript:void(0)"  class="var-tab var-tab--inactive ime_tab_link" data-tab="google_maps_key" >
                                        <span class="tab-label"><?php esc_attr_e( 'Google Maps API', 'import-meetup-events' ); ?></span>
                                    </a>
                                    <?php if( ime_is_pro() ){ ?>
                                        <a href="javascript:void(0)"  class="var-tab var-tab--inactive ime_tab_link" data-tab="license">
                                            <span class="tab-label"><?php esc_attr_e( 'License', 'import-meetup-events' ); ?></span>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                            
                <div id="poststuff">
                    <div id="settings" class="ime_tab_content var-tab--active" >
                        <div class="ime_container ime_source_import">
                            <div class="ime_row">
                                <div class="widefat ime-settings_notice">
                                    <?php
                                        $site_url = get_home_url();
                                        if( !isset( $_SERVER['HTTPS'] ) && false === stripos( $site_url, 'https' ) && $meetup_oauth_key != '' && $meetup_oauth_secret != '' && empty($ime_authorized_user) ) {
                                            ?>
                                            <div class="widefat ime_settings_error">
                                                <?php esc_attr_e( "It looks like you don't have HTTPS enabled on your website. Please enable it. HTTPS is required to authorize your meetup account.","import-meetup-events" ); ?>
                                            </div>
                                            <?php
                                        } 
                                    ?>
                                    
                                    <div class="widefat ime_settings_notice">
                                        <?php printf( '<b>%1$s</b> %2$s <b><a href="https://www.meetup.com/api/oauth/list/" target="_blank">%3$s</a></b> %4$s', esc_html__( 'Note : ','import-meetup-events' ), esc_html__( 'You have to create a Meetup OAuth Consumer before filling the following details.','import-meetup-events' ), esc_html__( 'Click here','import-meetup-events' ), esc_html__( 'to create new OAuth Consumer','import-meetup-events' ) ); ?>
                                        <br/>
                                        <?php esc_attr_e( 'For detailed step by step instructions ', 'import-meetup-events' ); ?>
                                        <strong><a href="http://docs.xylusthemes.com/docs/import-meetup-events/creating-oauth-consumer/" target="_blank"><?php esc_attr_e( 'Click here', 'import-meetup-events' ); ?></a></strong>.
                                        <br/>
                                        <?php echo '<strong>' . esc_html__( 'Set the Application Website as:', 'import-meetup-events' ) . '</strong>'; ?>
                                        <span style="color: green;"><?php echo esc_url( get_site_url() ); ?></span>
                                        <span class="dashicons dashicons-admin-page ime-btn-copy-shortcode ime_link_cp" data-value='<?php echo esc_url( get_site_url() ); ?>' ></span>
                                        <br/>
                                        <?php echo '<strong>' . esc_html__( 'Set Redirect URI:', 'import-meetup-events' ) . '</strong>'; ?>
                                        <span style="color: green;"><?php echo esc_url( admin_url( 'admin-post.php?action=ime_authorize_callback' ) ); ?></span>
                                        <span class="dashicons dashicons-admin-page ime-btn-copy-shortcode ime_link_cp" data-value='<?php echo esc_url( admin_url( 'admin-post.php?action=ime_authorize_callback' ) ); ?>' ></span>
                                    </div>

                                    <?php
                                        if( $meetup_oauth_key != '' && $meetup_oauth_secret != '' ){
                                            ?>
                                            <h4 class="setting_bar"><?php esc_attr_e( 'Connect your Meetup Account', 'import-meetup-events' ); ?></h4>
                                            <div class="ime_authorize">
                                                <div class="ime-inner-main-section"  >
                                                    <div class="ime-inner-section-1" style="padding: 10px;" >
                                                        <span class="ime-title-text" ><?php esc_attr_e( 'Meetup Authorization','import-meetup-events' ); ?></span>
                                                    </div>
                                                    <div class="ime-inner-section-2" style="padding: 10px;" >
                                                        <?php
                                                            if( !empty($ime_authorized_user) && isset($ime_authorized_user['name']) ) {
                                                                $email = isset($ime_authorized_user['email']) ? $ime_authorized_user['email'] : '';
                                                                $name = $ime_authorized_user['name'];
                                                                ?>
                                                                <div class="ime_connection_wrapper">
                                                                    <div class="name_wrap">
                                                                        <?php
                                                                            // translators: %s: The connected user's name in bold.
                                                                            printf( esc_html__( 'Connected as: %s', 'import-meetup-events' ), '<strong>' . esc_attr( $name ) . '</strong>' );
                                                                        ?>
                                                                        <br/>
                                                                        <?php echo esc_attr( $email ); ?>
                                                                        <br/>
                                                                        <a href="<?php echo esc_url( admin_url('admin-post.php?action=ime_deauthorize_action') ); ?>">
                                                                            <?php esc_attr_e('Remove Connection', 'import-meetup-events'); ?>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <?php
                                                            }else{
                                                                ?>
                                                                <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                                                                    <input type="hidden" name="action" value="ime_authorize_action"/>
                                                                    <?php wp_nonce_field('ime_authorize_action', 'ime_authorize_nonce'); ?>
                                                                    <?php
                                                                    $button_value = esc_html__('Connect', 'import-meetup-events');
                                                                    ?>
                                                                    <input type="submit" class="button" name="ime_authorize" value="<?php echo esc_attr( $button_value ); ?>" />
                                                                </form>
                                                                <span class="ime_small">
                                                                    <?php esc_attr_e( 'Please connect your meetup account for import meetup events.','import-meetup-events' ); ?>
                                                                </span>
                                                                <?php 
                                                            } 
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    ?>
                                </div>
                                <form method="post" id="ime_setting_form" style="margin-top: 20px;">
                                                                                                            
                                    <div class="ime-inner-main-section ime-new-feature" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" >
                                                <?php esc_attr_e( 'Import Event With Public API ', 'import-meetup-events' ); ?>
                                                <br/>
                                                <?php esc_attr_e( '(No Auth Required) ', 'import-meetup-events' ); ?>
                                            </span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <?php
                                                $using_public_api = isset( $meetup_options['using_public_api'] ) ? $meetup_options['using_public_api'] : 'no';
                                            ?>
                                            <input type="checkbox" name="meetup[using_public_api]" value="yes" <?php if( $using_public_api == 'yes' ) { echo 'checked="checked"'; } ?> />
                                            <span class="ime_small">
                                                <strong><?php esc_attr_e( 'Using "Import Event With Public API (No Auth Required)" lets you fetch events directly. No Key or authorization needed.', 'import-meetup-events' ); ?></strong>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="meetup_or_keyandsecrate">
                                            <span class="ime-title-text" ><?php esc_attr_e( '- OR -', 'import-meetup-events' ); ?></span>
                                        </div>
                                    </div>                                    

                                    <div class="ime-inner-main-section"  >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e( 'Meetup OAuth Key','import-meetup-events' ); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <input class="meetup_api_key" name="meetup[meetup_oauth_key]" type="text" value="<?php echo esc_attr( $meetup_oauth_key ); ?>" />
                                            <span class="ime_small">
                                                <?php printf('%s <a href="https://www.meetup.com/api/oauth/list/" target="_blank">%s</a>', esc_html__( 'Insert your meetup.com OAuth Key you can get it from', 'import-meetup-events' ), esc_html__( 'here', 'import-meetup-events' ) ); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e( 'Meetup OAuth Secret','import-meetup-events' ); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <input class="meetup_api_key" name="meetup[meetup_oauth_secret]" type="text" value="<?php echo esc_attr( $meetup_oauth_secret ); ?>" />
                                            <span class="ime_small">
                                                <?php printf('%s <a href="https://www.meetup.com/api/oauth/list/" target="_blank">%s</a>', esc_html__( 'Insert your meetup.com OAuth Secret you can get it from', 'import-meetup-events' ), esc_html__( 'here', 'import-meetup-events' ) ); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="meetup_or_keyandsecrate">
                                            <span class="ime-title-text" ><?php esc_attr_e( '- OR -', 'import-meetup-events' ); ?></span>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e( 'Meetup API key','import-meetup-events' ); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <input class="meetup_api_key" name="meetup[meetup_api_key]" type="text" value="<?php if ( isset( $meetup_options['meetup_api_key'] ) ) { echo esc_attr( $meetup_options['meetup_api_key'] ); } ?>" />
                                            <span class="ime_small">
                                                <?php printf('%s <a href="https://www.meetup.com/api/oauth/list/" target="_blank">%s</a>', esc_html__( 'Insert your meetup.com API key you can get it from', 'import-meetup-events' ), esc_html__( 'here', 'import-meetup-events' ) ); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e( 'Update existing events', 'import-meetup-events' ); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <?php
                                                $update_meetup_events = isset( $meetup_options['update_events'] ) ? $meetup_options['update_events'] : 'no';
                                            ?>
                                            <input type="checkbox" name="meetup[update_events]" value="yes" <?php if( $update_meetup_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                                            <span class="ime_small">
                                                <?php esc_attr_e( 'Check to updates existing events.', 'import-meetup-events' ); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e( 'Move past events in trash', 'import-meetup-events' ); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <?php
                                                $move_peit_events = isset( $meetup_options['move_peit'] ) ? $meetup_options['move_peit'] : 'no';
                                            ?>
                                            <input type="checkbox" name="meetup[move_peit]" value="yes" <?php if ( $move_peit_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                                            <span class="ime_small">
                                                <?php esc_attr_e( 'Check to move past events in the trash, Automatically move events to the trash 24 hours after their end date using wp-cron. This runs once daily in the background.', 'import-meetup-events' ); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e( 'Skip Trashed Events', 'import-meetup-events' ); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <?php
                                                $skip_trash = isset( $meetup_options['skip_trash'] ) ? $meetup_options['skip_trash'] : 'no';
                                            ?>
                                            <input type="checkbox" name="meetup[skip_trash]" value="yes" <?php if ( $skip_trash == 'yes') { echo 'checked="checked"'; }if (!ime_is_pro()) {echo 'disabled="disabled"'; } ?> />
                                            <span class="ime_small">
                                                <?php esc_attr_e('Check to enable skip-the-trash events during importing.', 'import-meetup-events'); ?>
                                            </span>
                                            <?php do_action('ime_render_pro_notice'); ?>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e('Direct link to Meetup', 'import-meetup-events'); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <?php
                                                $direct_link = isset($meetup_options['direct_link']) ? $meetup_options['direct_link'] : 'no';
                                            ?>
                                            <input type="checkbox" name="meetup[direct_link]" value="yes" <?php if ($direct_link == 'yes') { echo 'checked="checked"'; }if (!ime_is_pro()) {echo 'disabled="disabled"'; } ?> />
                                            <span class="ime_small">
                                                <?php esc_attr_e('Check to enable direct event link to Meetup instead of event detail page.', 'import-meetup-events'); ?>
                                            </span>
                                            <?php do_action('ime_render_pro_notice'); ?>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e( 'Advanced Synchronization', 'import-meetup-events' ); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <?php
                                                $advanced_sync = isset( $meetup_options['advanced_sync'] ) ? $meetup_options['advanced_sync'] : 'no';
                                            ?>
                                            <input type="checkbox" name="meetup[advanced_sync]" value="yes" <?php if( $advanced_sync == 'yes' ) { echo 'checked="checked"'; } if( !ime_is_pro()){ echo 'disabled="disabled"'; } ?> />
                                            <span class="ime_small">
                                                <?php esc_attr_e( 'Check to enable advanced synchronization; this will delete events that are removed from Meetup. Also, it deletes past events.', 'import-meetup-events' ); ?>
                                            </span>
                                            <?php do_action( 'ime_render_pro_notice' ); ?>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e( "Don't Update these data", "import-meetup-events" ); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <?php
                                                $dn_update   = isset($meetup_options['dont_update'])? $meetup_options['dont_update'] : array();
                                                $sdontupdate = isset( $dn_update['status'] ) ? $dn_update['status'] : 'no';
                                                $cdontupdate = isset( $dn_update['category'] ) ? $dn_update['category'] : 'no';
                                            ?>
                                            <input type="checkbox" name="meetup[dont_update][status]" value="yes" <?php checked( $sdontupdate, 'yes' ); disabled( ime_is_pro(), false );?> />
                                            <span class="ime_small">
                                                <?php esc_attr_e( 'Status ( Publish, Pending, Draft etc.. )', 'import-meetup-events' ); ?>
                                            </span><br/>
                                            <input type="checkbox" name="meetup[dont_update][category]" value="yes" <?php checked( $cdontupdate, 'yes' ); disabled( ime_is_pro(), false );?> />
                                            <span class="ime_small">
                                                <?php esc_attr_e( 'Event category', 'import-meetup-events' ); ?>
                                            </span><br/>
                                            <span class="ime_small">
                                                <?php esc_attr_e( "Select data that you don't want to update during existing events updates. (This is applicable only if you have checked 'update existing events.')", 'import-meetup-events' ); ?>
                                            </span>
                                            <?php do_action('ime_render_pro_notice'); ?>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e( 'Accent Color', 'import-meetup-events' ); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <?php
                                                $accent_color = isset( $meetup_options['accent_color'] ) ? $meetup_options['accent_color'] : '#039ED7';
                                            ?>
                                            <input class="ime_color_field" type="text" name="meetup[accent_color]" value="<?php echo esc_attr( $accent_color ); ?>"/>
                                            <span class="ime_small">
                                                <?php esc_attr_e( 'Choose accent color for front-end event grid and event widget.', 'import-meetup-events' ); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e('Event Slug', 'import-meetup-events'); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <?php
                                                $event_slug = isset($meetup_options['event_slug']) ? $meetup_options['event_slug'] : 'meetup-event';
                                            ?>
                                            <input type="text" name="meetup[event_slug]" value="<?php if ( $event_slug ) { echo esc_attr( $event_slug ); } ?>" <?php if (!ime_is_pro()) { echo 'disabled="disabled"'; } ?> />
                                            <span class="ime_small">
                                                <?php esc_attr_e('Slug for the event.', 'import-meetup-events'); ?>
                                            </span>
                                            <?php do_action('ime_render_pro_notice'); ?>
                                        </div>
                                    </div>

                                    <div class="ime-inner-main-section" >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e( 'Event Display Time Format', 'import-meetup-events' ); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <?php
                                                $time_format = isset( $meetup_options['time_format'] ) ? $meetup_options['time_format'] : '12hours';
                                            ?>
                                            <select name="meetup[time_format]">
                                                    <option value="12hours" <?php selected('12hours', $time_format); ?>><?php esc_attr_e( '12 Hours', 'import-meetup-events' );  ?></option>
                                                    <option value="24hours" <?php selected('24hours', $time_format); ?>><?php esc_attr_e( '24 Hours', 'import-meetup-events' ); ?></option>						
                                                    <option value="wordpress_default" <?php selected('wordpress_default', $time_format); ?>><?php esc_attr_e( 'WordPress Default', 'import-meetup-events' ); ?></option>
                                            </select>
                                            <span class="ime_small">
                                                <?php esc_attr_e( 'Choose event display time format for front-end.', 'import-meetup-events' ); ?>
                                            </span>
                                        </div>
                                    </div>
                                        
                                    <div class="">
                                        <input type="hidden" name="ime_action" value="ime_save_settings" />
                                        <?php wp_nonce_field( 'ime_setting_form_nonce_action', 'ime_setting_form_nonce' ); ?>
                                        <input type="submit" class="ime_button xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'import-meetup-events' ); ?>" />
                                    </div>


                                </form>
                            </div>
                        </div>
                    </div>

                    <div id="google_maps_key" class="ime_tab_content">
                        <div class="ime_container">
                            <div class="ime_row">
                                <form method="post" id="ime_gma_setting_form">

                                    <?php do_action( 'ime_before_settings_section' ); ?>

                                    <div class="ime-inner-main-section"  >
                                        <div class="ime-inner-section-1" >
                                            <span class="ime-title-text" ><?php esc_attr_e( 'Google Maps API', 'import-meetup-events' ); ?></span>
                                        </div>
                                        <div class="ime-inner-section-2" >
                                            <input class="ime_google_maps_api_key" name="ime_google_maps_api_key" Placeholder="Enter Google Maps API Key Here..." type="text" value="<?php echo( ! empty( $ime_google_maps_api_key ) ? esc_attr( $ime_google_maps_api_key ) : '' ); ?>" />
                                            <span class="ime_check_key"><a href="javascript:void(0)" ><?php esc_attr_e( 'Check Google Maps Key', 'import-meetup-events' ); ?></a><span class="ime_loader" id="ime_loader"></span></span>
                                            <span id="ime_gmap_error_message"></span>
                                            <span id="ime_gmap_success_message"></span>
                                            <div>
                                                <span class="ime_small">
                                                    <?php
                                                        printf(
                                                            '%s <a href="https://developers.google.com/maps/documentation/embed/get-api-key#create-api-keys" target="_blank">%s</a> / %s',
                                                            esc_attr__( 'Google maps API Key (Required)', 'import-meetup-events' ),
                                                            esc_attr__( 'How to get an API Key', 'import-meetup-events' ),
                                                            '<a href="https://developers.google.com/maps/documentation/embed/get-api-key#restrict_key" target="_blank">' . esc_attr__( 'Find out more about API Key restrictions', 'import-meetup-events' ) . '</a>'
                                                        );
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="">
                                        <input type="hidden" name="ime_gma_action" value="ime_save_gma_settings" />
                                        <?php wp_nonce_field( 'ime_gma_setting_form_nonce_action', 'ime_gma_setting_form_nonce' ); ?>
                                        <input type="submit" class="ime_button xtei_gma_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'import-meetup-events' ); ?>" />
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    <?php 
                        if( ime_is_pro() ){ 
                            ?>
                            <div id="license" class="ime_tab_content">
                                <?php
                                    if( class_exists( 'Import_Meetup_Events_Pro_Common' ) && method_exists( $ime_events->common_pro, 'ime_licence_page_in_setting' ) ){
                                        $ime_events->common_pro->ime_licence_page_in_setting(); 
                                    }else{
                                        $license_section = sprintf(
                                            '<h3 class="setting_bar" >Once you have updated the plugin Pro version <a href="%s">%s</a>, you will be able to access this section.</h3>',
                                            esc_url( admin_url( 'plugins.php?s=import+meetup+events+pro' ) ),
                                            esc_html__( 'Here', 'import-meetup-events' )
                                        );
                                        echo wp_kses_post( $license_section );
                                    }
                                ?>
                            </div>
                            <?php 
                        } 
                    ?>
            </div>
        </div>
    </div>
</div>
