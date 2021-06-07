<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ShortcodeTable = new IME_Shortcode_List_Table();
$ShortcodeTable->prepare_items();

?>
<div class="ime_container">
    <div class="ime_row">
    <h3 class="setting_bar"><?php esc_attr_e( 'Meetup Shortcodes', 'import-meetup-events' ); ?></h3>
        <?php $ShortcodeTable->display(); ?>
    </div>
</div>