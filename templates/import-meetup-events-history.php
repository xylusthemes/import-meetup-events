<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $ime_events;
?>
<div class="ime_container">
    <div class="ime_row">
        <div class="">
        	<?php
        	$query = "SELECT sum(pm.meta_value) FROM jkfdawi_posts AS p INNER JOIN jkfdawi_postmeta AS pm ON p.ID = pm.post_id WHERE pm.meta_key = 'created'";
        	?>
			<form id="import-history" method="get">
				<input type="hidden" name="page" value="<?php echo isset( $_REQUEST['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) : 'meetup_import' ; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" />
				<input type="hidden" name="tab" value="<?php echo isset($_REQUEST['tab']) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) )  : 'history'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" />
				<input type="hidden" name="ntab" value="" />
        		<?php
				$listtable = new Import_Meetup_Events_History_List_Table();
				$listtable->prepare_items();
				$listtable->display();
        		?>
			</form>
        </div>
    </div>
</div>