<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WooThumbs_Settings.
 *
 * @class    Iconic_WooThumbs_Settings
 * @version  1.0.0
 * @package  Iconic_WooThumbs
 * @category Class
 * @author   Iconic
 */
class Iconic_WooThumbs_Settings {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( "update_option_iconic_woothumbs_settings", array( __CLASS__, 'on_save' ), 10, 3 );
		add_filter( 'iconic_woothumbs_settings_validate', array( __CLASS__, 'validate_settings' ), 10, 1 );
	}

	/**
	 * On save settings.
	 *
	 * @param mixed  $old_value
	 * @param mixed  $value
	 * @param string $option
	 */
	public static function on_save( $old_value, $value, $option ) {
		if ( class_exists( 'WC_Regenerate_Images' ) ) {
			WC_Regenerate_Images::maybe_regenerate_images();
		}
	}

	/**
	 * Admin: Validate Settings
	 *
	 * @param array $settings Un-validated settings
	 *
	 * @return array $validated_settings
	 */
	public static function validate_settings( $settings ) {
		if ( isset( $_POST['iconic-woothumbs-delete-image-cache'] ) ) {
			add_settings_error( 'iconic-woothumbs-delete-image-cache', 'iconic-woothumbs', __( 'The image cache has been cleared.', 'iconic-woothumbs' ), 'updated' );
		}

		return $settings;
	}

	/**
	 * Get a list of image sizes for the site
	 *
	 * @return array
	 */
	public static function get_image_sizes() {
		$image_sizes = array_merge( get_intermediate_image_sizes(), array( 'full' ) );

		return array_combine( $image_sizes, $image_sizes );
	}

	/**
	 * Clear image cache link.
	 *
	 * @return string
	 */
	public static function clear_image_cache_link() {
		ob_start();

		?>
		<button name="iconic-woothumbs-delete-image-cache" class="button button-secondary"><?php _e( 'Clear Image Cache', 'iconic-woothumbs' ); ?></button>
		<?php

		return ob_get_clean();
	}

	/**
	 * Add ratio fields.
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public static function ratio_fields( $args ) {
		$defaults = array(
			'width'  => '',
			'height' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$width_name  = sprintf( '%s_width', $args['name'] );
		$height_name = sprintf( '%s_height', $args['name'] );
		$input       = '<input id="%s" name="iconic_woothumbs_settings[%s]" type="number" style="width: 50px;" value="%s">';
		$width       = sprintf( $input, $width_name, $width_name, $args['width'] );
		$height      = sprintf( $input, $height_name, $height_name, $args['height'] );

		return sprintf( '%s : %s', $width, $height );
	}
}