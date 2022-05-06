<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$shortcode_table = new IME_Shortcode_List_Table();
$shortcode_table->prepare_items();

?>
<div class="ime_container">
    <div class="ime_row">
    <h3 class="setting_bar"><?php esc_attr_e( 'Meetup Shortcodes', 'import-meetup-events' ); ?></h3>
        <?php $shortcode_table->display(); ?>
    </div>
</div>