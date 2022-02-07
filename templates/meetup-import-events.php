<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $ime_events;
?>
<div class="ime_container">
    <div class="ime_row">
        <div class="ime-column ime_well">
            <h3><?php esc_attr_e( 'Meetup Import', 'import-meetup-events' ); ?></h3>
            <form method="post" id="ime_meetup_form">
				
				<table class="form-table">
		            <tbody>
						<tr>
							<th scope="row">
								<?php esc_attr_e( 'Import by', 'import-meetup-events' ); ?> :
							</th>
							<td>
								<select name="meetup_import_by" id="meetup_import_by">
									<option value="event_id"><?php esc_attr_e( 'Event ID', 'import-meetup-events' ); ?></option>
									<option value="group_url"><?php esc_attr_e( 'Group URL', 'import-meetup-events' ); ?></option>
								</select>
								<span class="ime_small">
									<?php _e( 'Select Event source. 1. by Event ID, 2. by Group URL', 'import-meetup-events' ); ?>
								</span>
							</td>
						</tr>

						<tr class="meetup_event_id">
							<th scope="row">
								<?php esc_attr_e( 'Meetup Event ID', 'import-meetup-events' ); ?> : 
							</th>
							<td>
								<?php if ( ime_is_pro() ) { ?>
								<textarea class="ime_meetup_ids" name="ime_event_id" type="text" rows="5" cols="50"></textarea>
								<span class="ime_small">
									<?php _e( 'Insert meetup event ID ( Eg. https://www.meetup.com/xxxx-xxx-xxxx/events/<span class="borderall">xxxxxxxxx</span>  ).', 'import-meetup-events' ); ?>
								</span>
								<?php } else { ?>
								<input class="ime_text" name="ime_event_id" type="text" />
								<span class="ime_small">
									<?php _e( 'Insert meetup event ID ( Eg. https://www.meetup.com/xxxx-xxx-xxxx/events/<span class="borderall">xxxxxxxxx</span>  ).', 'import-meetup-events' ); ?>
								</span>
								<?php } ?>
							</td>
						</tr>

						<tr class="meetup_group_url">
							<th scope="row">
								<?php esc_attr_e( 'Meetup Group URL','import-meetup-events' ); ?> : 
							</th>
							<td>
								<input class="ime_text" name="ime_group_url" type="text" <?php if ( ! ime_is_pro() ) { echo 'disabled="disabled"'; } ?> />
								<span class="ime_small">
									<?php _e( 'Insert meetup group url ( Eg. -<span class="borderall">https://www.meetup.com/xxxx-xxx-xxxx/</span>  ).', 'import-meetup-events' ); ?>
								</span>
								<?php do_action( 'ime_render_pro_notice' ); ?>
							</td>
						</tr>

					    <tr class="import_type_wrapper">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Import type','import-meetup-events' ); ?> : 
					    	</th>
					    	<td>
						    	<?php $ime_events->common->render_import_type(); ?>
					    	</td>
					    </tr>

					    <?php 
					    $ime_events->common->render_import_into_and_taxonomy();
					    $ime_events->common->render_eventstatus_input();
					    ?>

						<tr>
							<th scope="row">
								<?php _e('Author','import-meetup-events'); ?> :
							</th>
							<td>
								<?php wp_dropdown_users( array( 'show_option_none' => esc_attr__( 'Select Author','import-meetup-events'), 'name' => 'event_author', 'option_none_value' => get_current_user_id() ) ); ?>
								<span class="ime_small">
									<?php _e( 'Select event author for imported events. Default event auther is current loggedin user.', 'import-meetup-events' ); ?>
								</span>
							</td>
						</tr>

					</tbody>
		        </table>
                
                <div class="ime_element">
                	<input type="hidden" name="import_origin" value="meetup" />
                    <input type="hidden" name="ime_action" value="ime_import_submit" />
                    <?php wp_nonce_field( 'ime_import_form_nonce_action', 'ime_import_form_nonce' ); ?>
                    <input type="submit" class="button-primary ime_submit_button" style=""  value="<?php esc_attr_e( 'Import Event', 'import-meetup-events' ); ?>" />
                </div>
            </form>
        </div>
    </div>
</div>
