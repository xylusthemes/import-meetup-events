<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $ime_events;
?>
<form method="post" enctype="multipart/form-data" id="ime_meetup_form">
	<div class="ime-card" style="margin-top:20px;" >			
		<div class="ime-content ime_source_import"  aria-expanded="true" style=" ">
			<div class="ime-inner-main-section">
				<div class="ime-inner-section-1" >
					<span class="ime-title-text" >
						<?php esc_attr_e( 'Import by', 'import-meetup-events' ); ?>
						<span class="ime-tooltip">
							<div>
								<svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="ime-circle-question-mark">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
								</svg>
								<span class="ime-popper">
									<?php 
										$text = sprintf(
											/* translators: 1: First option (by Facebook Event ID), 2: Second option (Facebook Page) */
											esc_html__( 'Select Event source. %1$s, %2$s.', 'import-meetup-events' ),
											'<br><strong>' . esc_html__( '1. Meetup Event ID', 'import-meetup-events' ) . '</strong>',
											'<br><strong>' . esc_html__( '2. Meetup Group URL', 'import-meetup-events' ) . '</strong>',
										);
										
										echo wp_kses(
											$text,
											array(
												'strong' => array(),
												'br' => array(),
											)
										);
									?>
									<div class="ime-popper__arrow"></div>
								</span>
							</div>
						</span>
					</span>
				</div>
				<div class="ime-inner-section-2 ">
					<select name="meetup_import_by" id="meetup_import_by">
						<option value="event_id"><?php esc_attr_e( 'Event ID', 'import-meetup-events' ); ?></option>
						<option value="group_url"><?php esc_attr_e( 'Group URL', 'import-meetup-events' ); ?></option>
					</select>
				</div>
			</div>

			<div class="ime-inner-main-section meetup_event_id">
				<div class="ime-inner-section-1" >
					<span class="ime-title-text" >
						<?php esc_attr_e( 'Meetup Event ID', 'import-meetup-events' ); ?>
					</span>
				</div>
				<div class="ime-inner-section-2" >
					<?php if ( ime_is_pro() ) { ?>
						<textarea class="ime_meetup_ids" name="ime_event_ids" type="text" rows="5" cols="53"></textarea>
						<div>
							<span class="ime_small">
								<?php echo wp_kses_post( 'One event ID per line. (E.g., Event ID for https://www.meetup.com/xxxx-xxx-xxxx/events/xxxxxxxxx is <span class=\"borderall\">xxxxxxxxx</span> ).<br> ', 'import-meetup-events' ); ?>
							</span>
						</div>
					<?php } else { ?>
						<input class="ime_text" name="ime_event_ids" type="text" />
						<div>
							<span class="ime_small">
								<?php echo wp_kses_post( 'Insert meetup event ID ( Eg. https://www.meetup.com/xxxx-xxx-xxxx/events/<span class="borderall">xxxxxxxxx</span>  ).', 'import-meetup-events' ); ?>
							</span>
						</div>
					<?php } ?>
				</div>
			</div>

			<div class="ime-inner-main-section meetup_group_url">
				<div class="ime-inner-section-1" >
					<span class="ime-title-text" >
						<?php esc_attr_e( 'Meetup Group URL','import-meetup-events' ); ?>
					</span>
				</div>
				<div class="ime-inner-section-2" >
					<input class="ime_text" name="meetup_url" type="text" <?php if ( ! ime_is_pro() ) { echo 'disabled="disabled"'; } ?> />
					<div>
						<span class="ime_small">
							<?php echo wp_kses_post( 'Insert meetup group url ( Eg. -<span class="borderall">https://www.meetup.com/xxxx-xxx-xxxx/</span>  ).', 'import-meetup-events' ); ?>
						</span>
					</div>
					<?php do_action( 'ime_render_pro_notice' ); ?>
				</div>
			</div>

			<div class="ime-inner-main-section meetup_group_url">
				<div class="ime-inner-section-1" >
					<span class="ime-title-text" >
						<?php esc_attr_e( 'Import type','import-meetup-events' ); ?>
					</span>
				</div>
				<div class="ime-inner-section-2" >
					<?php $ime_events->common->render_import_type(); ?>
				</div>
			</div>
			
			<?php
				// import into.
				$ime_events->common->render_import_into_and_taxonomy();
				$ime_events->common->render_eventstatus_input();
			?>

			<div class="ime-inner-main-section">
				<div class="ime-inner-section-1" >
					<span class="ime-title-text" ><?php esc_attr_e('Author','import-meetup-events'); ?> 
						<span class="ime-tooltip">
							<div>
								<svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="ime-circle-question-mark">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
								</svg>
								<span class="ime-popper">
									<?php esc_attr_e( 'Select event author for imported events. Default event auther is current loggedin user.', 'import-meetup-events' ); ?>
									<div class="ime-popper__arrow"></div>
								</span>
							</div>
						</span>
					</span>
				</div>
				<div class="ime-inner-section-2" >
					<?php wp_dropdown_users( array( 'show_option_none' => esc_attr__( 'Select Author','import-meetup-events'), 'name' => 'event_author', 'option_none_value' => get_current_user_id() ) ); ?>
				</div>
			</div>


			<div class="">
				<input type="hidden" name="import_origin" value="meetup" />
				<input type="hidden" name="ime_action" value="ime_import_submit" />
				<?php wp_nonce_field( 'ime_import_form_nonce_action', 'ime_import_form_nonce' ); ?>
				<input type="submit" class="ime_button ime_submit_button" style=""  value="<?php esc_attr_e( 'Import Event', 'import-meetup-events' ); ?>" />
			</div>
		</div>		
	</div>		
</form>