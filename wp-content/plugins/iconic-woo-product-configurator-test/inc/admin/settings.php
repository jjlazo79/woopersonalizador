<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'wpsf_register_settings_iconic_woo_product_configurator', 'iconic_woo_product_configurator_settings' );

/**
 * WooCommerce Product Configurator Settings
 *
 * @param array $wpsf_settings
 *
 * @return array
 */
function iconic_woo_product_configurator_settings( $wpsf_settings ) {
	// Tabs.

	$wpsf_settings['tabs'][] = array(
		'id'    => 'general',
		'title' => __( 'General', 'jckpc' ),
	);

	$wpsf_settings['tabs'][] = array(
		'id'    => 'display',
		'title' => __( 'Display', 'jckpc' ),
	);

	$wpsf_settings['tabs'][] = array(
		'id'    => 'thumbnails',
		'title' => __( 'Thumbnails', 'jckpc' ),
	);

	$wpsf_settings['tabs'][] = array(
		'id'    => 'loader',
		'title' => __( 'Loader', 'jckpc' ),
	);

	// Sections.

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'dashboard',
		'section_id'          => 'tools',
		'section_title'       => __( 'Tools', 'jckpc' ),
		'section_description' => '',
		'section_order'       => 20,
		'fields'              => array(
			array(
				'id'       => 'install_db',
				'title'    => __( 'Install Database Tables', 'jckpc' ),
				'subtitle' => __( "If there's an issue with the database tables, you can run this tool to ensure they're all properly installed.", 'jckpc' ),
				'type'     => 'custom',
				'default'  => '<button type="submit" name="iconic_pc_install_db" class="button button-secondary">' . __( 'Install Tables', 'jckpc' ) . '</button>',
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'general',
		'section_id'          => 'cache',
		'section_title'       => __( 'Cache Settings', 'jckpc' ),
		'section_description' => '',
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'       => 'enable',
				'title'    => __( 'Enable Image Cache', 'jckpc' ),
				'subtitle' => __( "Once added to cart, the customer's final variation image will be cached. Without this, images will be generated dynamically every time, and could slow down your website.", 'jckpc' ),
				'type'     => 'checkbox',
				'default'  => 0,
			),
			array(
				'id'      => 'duration',
				'title'   => __( 'Cache Duration (Hours)', 'jckpc' ),
				'type'    => 'number',
				'default' => 24,
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'display',
		'section_id'          => 'images',
		'section_title'       => __( 'Image Settings', 'jckpc' ),
		'section_description' => '',
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'       => 'width',
				'title'    => __( 'Width (%)', 'jckpc' ),
				'subtitle' => __( 'Enter a percentage for the width of the image display. This is entirely theme dependant, but usually resides around 48.', 'jckpc' ),
				'type'     => 'number',
				'default'  => 48,
			),
			array(
				'id'       => 'position',
				'title'    => __( 'Position', 'jckpc' ),
				'subtitle' => __( 'Choose a position for the images.', 'jckpc' ),
				'type'     => 'select',
				'default'  => 'left',
				'choices'  => array(
					'left'  => __( 'Left', 'jckpc' ),
					'right' => __( 'Right', 'jckpc' ),
					'none'  => __( 'None', 'jckpc' ),
				),
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'display',
		'section_id'          => 'responsive',
		'section_title'       => __( 'Responsive Settings', 'jckpc' ),
		'section_description' => '',
		'section_order'       => 10,
		'fields'              => array(
			array(
				'id'      => 'enable',
				'title'   => __( 'Enable Breakpoint', 'jckpc' ),
				'type'    => 'checkbox',
				'default' => 1,
			),
			array(
				'id'       => 'breakpoint',
				'title'    => __( 'Breakpoint (px)', 'jckpc' ),
				'subtitle' => __( 'Settings below will be applied when the screen size is below this value.', 'jckpc' ),
				'type'     => 'number',
				'default'  => 768,
			),
			array(
				'id'       => 'width',
				'title'    => __( 'Width (%)', 'jckpc' ),
				'subtitle' => __( 'Image container with after the breakpoint.', 'jckpc' ),
				'type'     => 'number',
				'default'  => 100,
			),
			array(
				'id'       => 'position',
				'title'    => __( 'Position', 'jckpc' ),
				'subtitle' => __( 'Choose a position for the images.', 'jckpc' ),
				'type'     => 'select',
				'default'  => 'none',
				'choices'  => array(
					'left'  => __( 'Left', 'jckpc' ),
					'right' => __( 'Right', 'jckpc' ),
					'none'  => __( 'None', 'jckpc' ),
				),
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'thumbnails',
		'section_id'          => 'general',
		'section_title'       => __( 'Thumbnail Settings', 'jckpc' ),
		'section_description' => '',
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'       => 'enable',
				'title'    => __( 'Enable Thumbnails', 'jckpc' ),
				'subtitle' => __( "When enabled, the product gallery will be displayed below the main product image.", 'jckpc' ),
				'type'     => 'checkbox',
				'default'  => 1,
			),
			array(
				'id'       => 'columns',
				'title'    => __( 'Column Count', 'jckpc' ),
				'subtitle' => __( 'Number of thumbnails in a row.', 'jckpc' ),
				'type'     => 'number',
				'default'  => 3,
			),
			array(
				'id'       => 'spacing',
				'title'    => __( 'Spacing (px)', 'jckpc' ),
				'subtitle' => __( 'Space between thumbnail images.', 'jckpc' ),
				'type'     => 'number',
				'default'  => 10,
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'loader',
		'section_id'          => 'overlay',
		'section_title'       => __( 'Loading Overlay Settings', 'jckpc' ),
		'section_description' => '',
		'section_order'       => 0,
		'fields'              => array(
			array(
				'id'      => 'color',
				'title'   => __( 'Colour', 'jckpc' ),
				'type'    => 'color',
				'default' => '#ffffff',
			),
			array(
				'id'       => 'opacity',
				'title'    => __( 'Opacity', 'jckpc' ),
				'subtitle' => __( 'Enter a number between 0 and 1. 0 = transparent, 1 = opaque.', 'jckpc' ),
				'type'     => 'number',
				'default'  => 0.5,
			),
		),
	);

	$wpsf_settings['sections'][] = array(
		'tab_id'              => 'loader',
		'section_id'          => 'icon',
		'section_title'       => __( 'Loading Icon Settings', 'jckpc' ),
		'section_description' => '',
		'section_order'       => 10,
		'fields'              => array(
			array(
				'id'      => 'style',
				'title'   => __( 'Icon Style', 'jckpc' ),
				'type'    => 'select',
				'default' => 'none',
				'choices' => array(
					'none'  => 'None',
					'spin1' => 'Spinner 1',
					'spin2' => 'Spinner 2',
					'spin3' => 'Spinner 3',
					'spin4' => 'Spinner 4',
					'spin5' => 'Spinner 5',
					'spin6' => 'Spinner 6',
				),
			),
			array(
				'id'      => 'color',
				'title'   => __( 'Colour', 'jckpc' ),
				'type'    => 'color',
				'default' => '#ffffff',
			),
		),
	);

	return $wpsf_settings;
}