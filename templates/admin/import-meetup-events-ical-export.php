<?php
/**
 * iCal export admin page.
 *
 * @package Import_Meetup_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $ime_events;

$export_post_type = $ime_events->ical_export->get_export_post_type();
$export_taxonomy  = $ime_events->ical_export->get_export_taxonomy();

?>

<div class="ime-card ime-ical-export-card" style="margin-top: 20px;">
	<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ime-ical-export-form">
		<input type="hidden" name="action" value="ime_export_ical" />
		<?php wp_nonce_field( 'ime_export_ical_nonce_action', 'ime_export_ical_nonce' ); ?>

		<?php
		if ( empty( $export_post_type ) || ! post_type_exists( $export_post_type ) ) : ?>
			<h3><?php esc_html_e( 'The Meetup Events post type is not available for export.', 'import-meetup-events' ); ?></h3>
		<?php else : ?>
			<div class="ime-inner-main-section">
				<div class="ime-inner-section-1">
					<label for="ime_ical_date_filter"><?php esc_html_e( 'Date filter', 'import-meetup-events' ); ?></label>
				</div>
				<div class="ime-inner-section-2">
					<select id="ime_ical_date_filter" name="date_filter" class="ime-ical-control">
						<option value="upcoming"><?php esc_html_e( 'Upcoming only', 'import-meetup-events' ); ?></option>
						<option value="past"><?php esc_html_e( 'Past only', 'import-meetup-events' ); ?></option>
						<option value="all"><?php esc_html_e( 'All events', 'import-meetup-events' ); ?></option>
						<option value="range"><?php esc_html_e( 'Custom date range', 'import-meetup-events' ); ?></option>
					</select>
				</div>
			</div>

			<div class="ime-inner-main-section date-range-section" style="display: none;">
				<div class="ime-inner-section-1">
					<span><?php esc_html_e( 'Date range', 'import-meetup-events' ); ?></span>
				</div>
				<div class="ime-inner-section-2">
					<div class="ime-ical-inline-fields">
						<label for="ime_ical_start_date"><?php esc_html_e( 'Start', 'import-meetup-events' ); ?></label>
						<input type="date" id="ime_ical_start_date" name="start_date" value="" />

						<label for="ime_ical_end_date"><?php esc_html_e( 'End', 'import-meetup-events' ); ?></label>
						<input type="date" id="ime_ical_end_date" name="end_date" value="" />
					</div>
					<p class="description"><?php esc_html_e( 'Used only when Custom date range is selected.', 'import-meetup-events' ); ?></p>
				</div>
			</div>

			<div class="ime-inner-main-section">
				<div class="ime-inner-section-1">
					<label for="ime_ical_post_status"><?php esc_html_e( 'Post status', 'import-meetup-events' ); ?></label>
				</div>
				<div class="ime-inner-section-2">
					<select id="ime_ical_post_status" name="post_status" class="ime-ical-control">
						<option value="publish"><?php esc_html_e( 'Published', 'import-meetup-events' ); ?></option>
						<option value="future"><?php esc_html_e( 'Scheduled', 'import-meetup-events' ); ?></option>
						<option value="draft"><?php esc_html_e( 'Draft', 'import-meetup-events' ); ?></option>
						<option value="pending"><?php esc_html_e( 'Pending', 'import-meetup-events' ); ?></option>
						<option value="private"><?php esc_html_e( 'Private', 'import-meetup-events' ); ?></option>
						<option value="any"><?php esc_html_e( 'Any status', 'import-meetup-events' ); ?></option>
					</select>
				</div>
			</div>

			<div class="ime-inner-main-section">
				<div class="ime-inner-section-1">
					<label for="ime_ical_event_cat"><?php esc_html_e( 'Category', 'import-meetup-events' ); ?></label>
				</div>
				<div class="ime-inner-section-2">
					<select id="ime_ical_event_cat" name="event_cat" class="ime-ical-control">
						<option value="0"><?php esc_html_e( 'Any category', 'import-meetup-events' ); ?></option>
						<?php
						$terms = array();
						if ( ! empty( $export_taxonomy ) && taxonomy_exists( $export_taxonomy ) ) {
							$terms = get_terms(
								array(
									'taxonomy'   => $export_taxonomy,
									'hide_empty' => false,
								)
							);
						}

						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) :
							foreach ( $terms as $term ) :
								?>
								<option value="<?php echo esc_attr( $term->term_id ); ?>">
									<?php echo esc_html( $term->name ); ?>
								</option>
								<?php
							endforeach;
						endif;
						?>
					</select>
				</div>
			</div>

			<div class="ime-inner-main-section">
				<div class="ime-inner-section-1">
					<label for="ime_ical_search"><?php esc_html_e( 'Keyword', 'import-meetup-events' ); ?></label>
				</div>
				<div class="ime-inner-section-2">
					<input type="search" id="ime_ical_search" name="s" placeholder="<?php esc_attr_e( 'Search Keywords...', 'import-meetup-events' ); ?>" class="regular-text ime-ical-control" value="" />
				</div>
			</div>

			<div class="ime-inner-main-section">
				<div class="ime-inner-section-1">
					<label for="ime_ical_limit"><?php esc_html_e( 'Maximum events', 'import-meetup-events' ); ?></label>
				</div>
				<div class="ime-inner-section-2">
					<input type="number" id="ime_ical_limit" name="limit" min="1" max="5000" value="500" class="ime-ical-number" />
				</div>
			</div>

			<div class="ime-inner-main-section">
				<div class="ime-inner-section-1">
					<span><?php esc_html_e( 'iCal fields', 'import-meetup-events' ); ?></span>
				</div>
				<div class="ime-inner-section-2">
					<div class="ime-ical-checkboxes">
						<label>
							<input type="checkbox" name="include_description" value="1" checked="checked" />
							<?php esc_html_e( 'Include description', 'import-meetup-events' ); ?>
						</label>
						<label>
							<input type="checkbox" name="include_location" value="1" checked="checked" />
							<?php esc_html_e( 'Include location', 'import-meetup-events' ); ?>
						</label>
					</div>
				</div>
			</div>

			<div class="ime-ical-actions">
				<?php submit_button( __( 'Export iCal File', 'import-meetup-events' ), 'primary', 'submit', false ); ?>
			</div>
		<?php endif; ?>
	</form>
</div>

