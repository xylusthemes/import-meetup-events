<?php
/**
 * File for render meetup import tab Events Calendar content.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Events_Manager_Meetup_Import
 * @subpackage Events_Manager_Meetup_Import/admin/partials
 */

?>
<div class="xtmi_container">
    <div class="xtmi_row">
        <div class="xt-column-12 xtmi_well">
            <h3><?php esc_attr_e( 'Meetup Import', 'events-manager-meetup-import' ); ?></h3>
            <form method="post" enctype="multipart/form-data" id="xtmi_meetup_form">
                <div class="xtmi_element">
                    <label class="xtmi_label"> <?php esc_attr_e( 'Meetup Group URL','events-manager-meetup-import' ); ?> : </label>
                    <input class="xtmi_text" name="xtmi_meet_url" type="url" required="required" />
                    <span class="xtmi_small">
                        <?php esc_attr_e( 'Insert meetup group url ( Eg. https://www.meetup.com/ny-tech/)', 'events-manager-meetup-import' ); ?>
                    </span>
                </div>
                <div class="xtmi_element">
                    <label class="xtmi_label"> <?php esc_attr_e( 'Event Categories for Event Import','events-manager-meetup-import' ); ?> : </label>
                    <select name="xtmi_event_cats[]" multiple="multiple">
                        <?php if( ! empty( $xt_event_cats ) ): ?>
                            <!-- print_r( $xt_event_cats); -->
                            <?php foreach ($xt_event_cats as $xtmi_cat ): ?>
                                <option value="<?php echo $xtmi_cat->term_id; ?>"><?php echo $xtmi_cat->name; ?></option>
                                <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <span class="xtmi_small">
                        <?php esc_attr_e( 'Events from this meetup group will assinged to these categories', 'events-manager-meetup-import' ); ?>
                    </span>
                </div>
                <div class="xtmi_element">
                    <input type="hidden" name="xtmi_action" value="xtmi_em_url_submit" />
                    <?php wp_nonce_field( 'xtmi_insert_form_nonce_action', 'xtmi_insert_form_nonce' ); ?>
                    <input type="submit" class="button-primary xtmi_submit_button" style=""  value="<?php esc_attr_e( 'Add Meetup group', 'events-manager-meetup-import' ); ?>" />
                </div>
            </form>
        </div>
        <?php
            $list_table = new XT_Meetup_Import_Em_List_Table();
            $list_table->prepare_items();
            $list_table->display();
        ?>
    </div>
</div>
