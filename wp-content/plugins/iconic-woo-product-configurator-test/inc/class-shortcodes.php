<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_PC_Shortcodes.
 *
 * @class    Iconic_PC_Shortcodes
 * @version  1.0.0
 * @since    1.2.2
 * @author   Iconic
 */
class Iconic_PC_Shortcodes {

	/**
	 * Init shortcodes.
	 */
	public static function run() {
		if ( is_admin() ) {
			return;
		}

		add_shortcode( 'iconic-wpc-gallery', array( __CLASS__, 'gallery' ) );
	}

	/**
	 * @param $attributes
	 *
	 * @return string|bool
	 */
	public static function gallery( $attributes ) {
		global $post, $jckpc;

		$attributes = shortcode_atts( array(
			'id' => false,
		), $attributes, 'iconic-wpc-gallery' );

		$attributes['id'] = $attributes['id'] ? $attributes['id'] : $post->ID;

		if ( ! $attributes['id'] ) {
			return false;
		}

		ob_start();

		$post_object = get_post( $attributes['id'] );

		if ( ! $post_object ) {
			return false;
		}

		$GLOBALS['post'] =& $post_object;

		setup_postdata( $GLOBALS['post'] );

		$jckpc->display_product_image();

		wp_reset_postdata();

		return ob_get_clean();
	}
}