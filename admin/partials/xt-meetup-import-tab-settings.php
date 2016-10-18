<?php
/**
 * File for render meetup import tab content.
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
            <h3><?php esc_attr_e( 'Meetup Import Settings', 'xt-meetup-import' ); ?></h3>
            <form method="post" enctype="multipart/form-data" id="xtmi_meetup_setting_form">
                <div class="xtmi_element">
                    <label class="xtmi_label"> <?php esc_attr_e( 'Meetup API key','xt-meetup-import' ); ?> : </label>
                    <input class="xtmi_meetup_api_key" name="xtmi_meetup_api_key" type="text" required="required" value="<?php if ( isset( $xtmi_options['meetup_api_key'] ) ) { echo $xtmi_options['meetup_api_key']; } ?>" />
                    <span class="xtmi_small">
                        <?php _e( 'Insert your meetup.com API key you can get it from <a href="https://secure.meetup.com/meetup_api/key/" target="_blank">here</a>.', 'xt-meetup-import' ); ?>
                    </span>
                </div>
                <div class="xtmi_element">
                    <label class="xtmi_label"> <?php esc_attr_e( 'Default status to use for imported events','xt-meetup-import' ); ?> : </label>
                    <?php
                    $defualt_status = isset( $xtmi_options['default_status'] ) ? $xtmi_options['default_status'] : 'pending';
                    ?>
                    <select name="xtmi_default_status" >
                        <option value="publish" <?php if ( $defualt_status == 'publish' ) { echo 'selected="selected"'; } ?> >
                            <?php esc_html_e( 'Published','xt-meetup-import' ); ?>
                        </option>
                        <option value="pending" <?php if ( $defualt_status == 'pending' ) { echo 'selected="selected"'; } ?>>
                            <?php esc_html_e( 'Pending','xt-meetup-import' ); ?>
                        </option>
                        <option value="draft" <?php if ( $defualt_status == 'draft' ) { echo 'selected="selected"'; } ?> >
                            <?php esc_html_e( 'Draft','xt-meetup-import' ); ?>
                        </option>
                    </select>
                </div>

                <?php
                $import_type = isset( $xtmi_options['import_type'] ) ? $xtmi_options['import_type'] : 'cron';
                ?>
                <div class="xtmi_element">
                    <label class="xtmi_label"> <?php esc_attr_e( 'How do you want to import Meetup events','xt-meetup-import' ); ?> : </label>

                    <input class="import_type" name="import_type" type="radio" value="cron" <?php if ( $import_type == 'cron' ) { echo 'checked="checked"'; } ?> />
                    <?php esc_html_e( 'Automatic', 'xt-meetup-import' ); ?>
                    <input class="import_type" name="import_type" type="radio" value="manual" <?php if ( $import_type == 'manual') { echo 'checked="checked"'; } ?> />
                    <?php esc_html_e( 'Manual', 'xt-meetup-import' ); ?>

                    <span class="xtmi_small">
                        <?php esc_html_e( 'Select "Automatic" if you want plugin check for events on meetup.com and import it.', 'xt-meetup-import' ); ?><br />
                        <?php esc_html_e( 'Select "Manual" if you want to import event manually.', 'xt-meetup-import' ); ?>
                    </span>
                </div>

                <div class="xtmi_element">
                    <label class="xtmi_label"> <?php esc_attr_e( 'Import Frequency', 'xt-meetup-import' ); ?>: </label>
                    <?php
                    $cron_interval = isset( $xtmi_options['cron_interval'] ) ? $xtmi_options['cron_interval'] : 'twicedaily';
                    ?>
                    <select name="cron_interval" >
                        <option value='hourly' <?php if ( $cron_interval == 'hourly' ) { echo 'selected="selected"'; } ?> >
                            <?php esc_html_e( 'Once Hourly','xt-meetup-import' ); ?>
                        </option>
                        <option value='twicedaily' <?php if ( $cron_interval == 'twicedaily' ) { echo 'selected="selected"'; } ?>>
                            <?php esc_html_e( 'Twice Daily','xt-meetup-import' ); ?>
                        </option>
                        <option value="daily" <?php if ( $cron_interval == 'daily' ) { echo 'selected="selected"'; } ?> >
                            <?php esc_html_e( 'Once Daily','xt-meetup-import' ); ?>
                        </option>
                        <option value="weekly" <?php if ( $cron_interval == 'weekly' ) { echo 'selected="selected"'; } ?> >
                            <?php esc_html_e( 'Once Weekly','xt-meetup-import' ); ?>
                        </option>
                        <option value="monthly" <?php if ( $cron_interval == 'monthly' ) { echo 'selected="selected"'; } ?> >
                            <?php esc_html_e( 'Once a Month','xt-meetup-import' ); ?>
                        </option>
                    </select>
                    <span class="xtmi_small">
                        <?php esc_html_e( 'Applicable only if you had select "Automatic" import type.', 'xt-meetup-import' ); ?><br />
                    </span>
                </div>

                <?php
                $update_events = isset( $xtmi_options['update_events'] ) ? $xtmi_options['update_events'] : 'yes';
                ?>
                <div class="xtmi_element">
                    <label class="xtmi_label"> <?php esc_attr_e( 'Update imported Event ?', 'xt-meetup-import' ); ?> : </label>

                    <input class="update_events" name="update_events" type="radio" value="yes" <?php if ( $update_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                    <?php esc_html_e( 'Yes, Update imported events durring automatic syncronize.', 'xt-meetup-import' ); ?><br />
                    <input class="update_events" name="update_events" type="radio" value="no" <?php if( $update_events == 'no' ){ echo 'checked="checked"'; } ?> />
                    <?php esc_html_e( 'No, Leave imported events unmodified.', 'xt-meetup-import' ); ?>
                </div>

                <div class="xtmi_element">
                    <input type="hidden" name="xtmi_action" value="xtmi_save_settings" />
                    <?php wp_nonce_field( 'xtmi_setting_form_nonce_action', 'xtmi_setting_form_nonce' ); ?>
                    <input type="submit" class="button-primary xtmi_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'xt-meetup-import' ); ?>" />
                </div>

            </form>
        </div>
    </div>
</div>
