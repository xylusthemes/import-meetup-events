<?php
/**
 * Template file for admin import events form.
 *
 * @package Import_Meetup_Events
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $ime_events;
$counts = $ime_events->common->ime_get_meetup_events_counts();

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
                        <div class="ime-card" style="margin-top:20px;" >			
                            <div class="ime-content"  aria-expanded="true"  >
                                <div id="ime-dashboard" class="wrap about-wrap" >
                                    <div class="ime-w-row" >
                                        <div class="ime-intro-section" >
                                            <div class="ime-w-box-content ime-intro-section-welcome" >
                                                <h3><?php esc_attr_e( 'Getting started with Import Meetup Events', 'import-meetup-events' ); ?></h3>
                                                <p style="margin-bottom: 25px;"><?php esc_attr_e( 'In this video, you can learn how to Import Meetup event into your website. Please watch this 2 minutes video to the end.', 'import-meetup-events' ); ?></p>
                                            </div>
                                            <div class="ime-w-box-content ime-intro-section-ifarme" >
                                                <iframe width="850" height="450" src="https://www.youtube.com/embed/NUmruo8gIVg?si=tKXhObIuUBTrZHcN" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen=""></iframe>
                                            </div>
                                            <div class="ime-intro-section-links wp-core-ui" >
                                                <a class="ime-intro-section-link-tag button ime-button-primary button-hero" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=meetup_events' ) ); ?>" target="_blank"><?php esc_attr_e( 'Add New Event', 'import-meetup-events' ); ?></a>
                                                <a class="ime-intro-section-link-tag button ime-button-secondary button-hero" href="<?php echo esc_url( admin_url( 'admin.php?page=meetup_import&tab=settings' ) ); ?>"target="_blank"><?php esc_attr_e( 'Settings', 'import-meetup-events' ); ?></a>
                                                <a class="ime-intro-section-link-tag button ime-button-secondary button-hero" href="https://docs.xylusthemes.com/docs/import-meetup-events/" target="_blank"><?php esc_attr_e( 'Documentation', 'import-meetup-events' ); ?></a>
                                            </div>
                                        </div>

                                        <div class="ime-counter-main-container" >
                                            <div class="ime-col-sm-3" >
                                                <div class="ime-w-box " >
                                                    <p class="ime_dash_count"><?php echo esc_attr( $counts['all'] ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Total Events', 'import-meetup-events' ); ?></strong></span>
                                                </div>
                                            </div>
                                            <div class="ime-col-sm-3" >
                                                <div class="ime-w-box " >
                                                    <p class="ime_dash_count"><?php echo esc_attr( $counts['upcoming'] ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Upcoming Events', 'import-meetup-events' ); ?></strong></span>
                                                </div>
                                            </div>
                                            <div class="ime-col-sm-3" >
                                                <div class="ime-w-box " >
                                                    <p class="ime_dash_count"><?php echo esc_attr( $counts['past'] ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Past Events', 'import-meetup-events' ); ?></strong></span>
                                                </div>
                                            </div>
                                            <div class="ime-col-sm-3" >
                                                <div class="ime-w-box " >
                                                    <p class="ime_dash_count"><?php echo esc_attr( IME_VERSION ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Version', 'import-meetup-events' ); ?></strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
</div>