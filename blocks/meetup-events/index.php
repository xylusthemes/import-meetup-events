<?php
/**
 * Meetup Events Block Initializer
 *
 * @since   1.6
 * @package    Import_Meetup_Events
 * @subpackage Import_Meetup_Events/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Gutenberg Block
 *
 * @return void
 */
function ime_register_gutenberg_block() {
	global $ime_events;
	if ( function_exists( 'register_block_type' ) ) {
		// Register block editor script.
		$js_dir = IME_PLUGIN_URL . 'assets/js/blocks/';
		wp_register_script(
			'ime-meetup-events-block',
			$js_dir . 'gutenberg.blocks.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			IME_VERSION
		);

		// Register block editor style.
		$css_dir = IME_PLUGIN_URL . 'assets/css/';
		wp_register_style(
			'ime-meetup-events-block-style',
			$css_dir . 'import-meetup-events.css',
			array(),
			IME_VERSION
		);
		wp_register_style(
			'ime-meetup-events-block-style2',
			$css_dir . 'grid-style2.css',
			array(),
			IME_VERSION
		);

		// Register our block.
		register_block_type( 'ime-block/meetup-events', array(
			'attributes'      => array(
				'col'            => array(
					'type'    => 'number',
					'default' => 3,
				),
				'posts_per_page' => array(
					'type'    => 'number',
					'default' => 12,
				),
				'past_events'    => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'start_date'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'end_date'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'order'          => array(
					'type'    => 'string',
					'default' => 'ASC',
				),
				'orderby'        => array(
					'type'    => 'string',
					'default' => 'event_start_date',
				),
				'layout'        => array(
					'type'    => 'string',
					'default' => '',
				),

			),
			'editor_script'   => 'ime-meetup-events-block', // The script name we gave in the wp_register_script() call.
			'editor_style'    => 'ime-meetup-events-block-style', // The script name we gave in the wp_register_style() call.
			'style'           => 'ime-meetup-events-block-style2',
			'render_callback' => array( $ime_events->cpt, 'meetup_events_archive' ),
		) );
	}
}

add_action( 'init', 'ime_register_gutenberg_block' );
