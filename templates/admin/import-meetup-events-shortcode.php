<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$shortcode_table = new IME_Shortcode_List_Table();
$shortcode_table->prepare_items();

?>

<div class="ime-xylus-promo-wrapper">
    <div class="ime-xylus-promo-header">
        <h2><?php esc_attr_e( '🎉 Try Our New Plugin – Easy Events Calendar', 'import-meetup-events' ); ?></h2>
        <p><?php esc_attr_e( 'A modern, clean and powerful way to display events. Includes calendar view, search, filters, pagination, and tons of settings. And it’s 100% FREE!', 'import-meetup-events' ); ?></p>
    </div>
    <div class="ime-xylus-main-inner-container">
        <div>
            <ul class="ime-xylus-feature-list">
                <li><?php esc_attr_e( '✅ Full Calendar Monthly View', 'import-meetup-events' ); ?></li>
                <li><?php esc_attr_e( '🔍 Event Search & Filter Support', 'import-meetup-events' ); ?></li>
                <li><?php esc_attr_e( '📅 Pagination & Multiple Layouts', 'import-meetup-events' ); ?></li>
                <li><?php esc_attr_e( '⚙️ Tons of Settings for Customization', 'import-meetup-events' ); ?></li>
                <li><?php esc_attr_e( '🎨 Frontend Styling Options', 'import-meetup-events' ); ?></li>
                <li><?php esc_attr_e( '💯 100% Free Plugin', 'import-meetup-events' ); ?></li>
            </ul>
            <?php
                $plugin_slug = 'xylus-events-calendar';
                $plugin_file = 'xylus-events-calendar/xylus-events-calendar.php';
                $current_page = admin_url( 'admin.php?page=meetup_import&tab=shortcodes' );
                if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
                    $install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug ), 'install-plugin_' . $plugin_slug );
                    echo '<a href="' . esc_url( $install_url ) . '" class="button button-primary">🚀 Install Now – It’s Free!</a>';
                } elseif ( ! is_plugin_active( $plugin_file ) ) {
                    $activate_url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ), 'activate-plugin_' . $plugin_file );
                    echo '<a href="' . esc_url( $activate_url ) . '" class="button button-secondary">⚡ Activate Plugin</a>';
                } else {
                    echo '<div class="ime-xylus-plugin-box">';
                    echo '<h3>✅ Easy Events Calendar is Active</h3>';
                    echo '<p style="margin: 0;">You can now display events anywhere using this shortcode</p>';
                    echo '<span class="ime_short_code">[easy_events_calendar]</span>';
                    echo '<button class="ime-btn-copy-shortcode ime_button" data-value="[easy_events_calendar]">Copy</button>';
                    echo '</div>';
                }
            ?>
        </div>
        <div class="ime-xylus-screenshot-slider">
            <div class="ime-screenshot-slide active">
                <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                <img src="<?php echo esc_url( IME_PLUGIN_URL.'assets/images/screenshot-1.jpg' ); ?>" alt="Monthly View">
            </div>
            <div class="ime-screenshot-slide">
                <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                <img src="<?php echo esc_url( IME_PLUGIN_URL.'assets/images/screenshot-2.jpg' ); ?>" alt="Event Settings">
            </div>
            <div class="ime-screenshot-slide">
                <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                <img src="<?php echo esc_url( IME_PLUGIN_URL.'assets/images/screenshot-3.jpg' ); ?>" alt="List View">
            </div>
            <div class="ime-screenshot-slide">
                <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                <img src="<?php echo esc_url( IME_PLUGIN_URL.'assets/images/screenshot-4.jpg' ); ?>" alt="Event Details">
            </div>
        </div>
    </div>
</div>

<div class="ime_container">
    <div class="ime_row">
    <h3 class="setting_bar"><?php esc_attr_e( 'Meetup Shortcodes', 'import-meetup-events' ); ?></h3>
        <?php $shortcode_table->display(); ?>
    </div>
</div>