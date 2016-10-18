<?php
/**
 * File for render meetup import The Events Calendar tab content.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    XT_Meetup_Import
 * @subpackage XT_Meetup_Import/admin/partials
 */

?>
<div class="xtmi_container">
    <div class="xtmi_row">
        <div class="xtmi-column-12 xtmi_well">
            <h3><?php esc_attr_e( 'Meetup Import', 'xt-meetup-import' ); ?></h3>
            <form method="post" enctype="multipart/form-data" id="xtmi_meetup_form">
                <div class="xtmi_element">
                    <label class="xtmi_label"> <?php esc_attr_e( 'Meetup Group URL','xt-meetup-import' ); ?> : </label>
                    <input class="xtmi_text" name="xtmi_meet_url" type="url" required="required" />
                    <span class="xtmi_small">
                        <?php esc_attr_e( 'Insert meetup group url ( Eg. https://www.meetup.com/ny-tech/)', 'xt-meetup-import' ); ?>
                    </span>
                </div>
                <div class="xtmi_element">
                    <label class="xtmi_label"> <?php esc_attr_e( 'Event Categories for Event Import','xt-meetup-import' ); ?> : </label>
                    <select name="xtmi_event_cats[]" multiple="multiple">
                        <?php if( ! empty( $xtmi_event_cats ) ): ?>
                            <!-- print_r( $xtmi_event_cats); -->
                            <?php foreach ($xtmi_event_cats as $xtmi_cat ): ?>
                                <option value="<?php echo $xtmi_cat->term_id; ?>"><?php echo $xtmi_cat->name; ?></option>
                                <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <span class="xtmi_small">
                        <?php esc_attr_e( 'Events from this meetup group will assinged to these categories', 'xt-meetup-import' ); ?>
                    </span>
                </div>

                <div class="xtmi_element">
                    <input type="hidden" name="xtmi_action" value="xtmi_tec_url_submit" />
                    <?php wp_nonce_field( 'xtmi_insert_form_nonce_action', 'xtmi_insert_form_nonce' ); ?>
                    <input type="submit" class="button-primary xtmi_submit_button" style=""  value="<?php esc_attr_e( 'Add Meetup Group', 'xt-meetup-import' ); ?>" />
                </div>
            </form>
        </div>
        <?php
            $list_table = new XT_Meetup_Import_Tec_List_Table();
            $list_table->prepare_items();
            $list_table->display();
        ?>
    </div>
</div>
