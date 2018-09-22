<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WooThumbs_Images.
 *
 * @class    Iconic_WooThumbs_Images
 * @version  1.0.0
 * @package  Iconic_WooThumbs
 * @category Class
 * @author   Iconic
 */
class Iconic_WooThumbs_Images {
	/**
	 * Run.
	 */
	public static function run() {
		add_action( 'after_setup_theme', array( __CLASS__, 'modify_theme_support' ), 11 );
		add_filter( 'woocommerce_logger_log_message', array( __CLASS__, 'after_regenerate_images' ), 10, 3 );
		add_filter( 'woocommerce_get_image_size_single', array( __CLASS__, 'get_image_size_single' ), 10 );
		add_filter( 'woocommerce_get_image_size_gallery_thumbnail', array( __CLASS__, 'get_image_size_gallery_thumbnail' ), 10 );
	}

	/**
	 * Change single width theme support.
	 */
	public static function modify_theme_support() {
		$theme_support = get_theme_support( 'woocommerce' );
		$theme_support = is_array( $theme_support ) ? $theme_support[0] : array();

		$theme_support['single_image_width'] = self::get_image_width( 'single' );

		remove_theme_support( 'woocommerce' );
		add_theme_support( 'woocommerce', $theme_support );
	}

	/**
	 * Get image width.
	 *
	 * @param string $size
	 *
	 * @return int
	 */
	public static function get_image_width( $size = 'single' ) {
		$woothumbs_settings = get_option( 'iconic_woothumbs_settings' );
		$setting_name       = sprintf( 'display_images_%s_image_width', $size );
		$default_width      = self::get_default_image_width( $size );
		$image_width        = ! empty( $woothumbs_settings[ $setting_name ] ) ? $woothumbs_settings[ $setting_name ] : $default_width;

		return absint( $image_width );
	}

	/**
	 * Get default image width.
	 *
	 * @param string $size
	 *
	 * @return int
	 */
	public static function get_default_image_width( $size = 'single' ) {
		switch ( $size ) {
			case 'single':
				return absint( get_option( 'woocommerce_single_image_width', 600 ) );
			case 'gallery_thumbnail':
				return 100;
		}
	}

	/**
	 * Get image crop.
	 *
	 * @param string $size
	 * @param bool   $dimension
	 *
	 * @return array|bool|mixed
	 */
	public static function get_image_crop( $size = 'single', $dimension = false ) {
		$woothumbs_settings  = get_option( 'iconic_woothumbs_settings' );
		$setting_name        = sprintf( 'display_images_%s_image_crop_', $size );

		$crop = array(
			'width'  => ! empty( $woothumbs_settings[ $setting_name . 'width' ] ) ? floatval( $woothumbs_settings[ $setting_name . 'width' ] ) : '',
			'height' => ! empty( $woothumbs_settings[ $setting_name . 'height' ] ) ? floatval( $woothumbs_settings[ $setting_name . 'height' ] ) : '',
		);

		if ( $dimension ) {
			return $crop[ $dimension ];
		}

		if ( empty( $crop['width'] ) || empty( $crop['height'] ) ) {
			return false;
		}

		return $crop;
	}

	/**
	 * Get an attachment properties.
	 *
	 * @param int|string $attachment_id
	 *
	 * @return bool|array
	 */
	public static function get_attachment_props( $attachment_id ) {
		$attachment_props = array(
			'title'        => null,
			'caption'      => null,
			'url'          => null,
			'alt'          => null,
			'src'          => null,
			'srcset'       => null,
			'sizes'        => null,
			'full_src'     => null,
			'full_src_w'   => null,
			'full_src_h'   => null,
			'thumb_src'    => null,
			'thumb_src_w'  => null,
			'thumb_src_h'  => null,
			'src_w'        => null,
			'src_h'        => null,
			'thumb_srcset' => null,
			'thumb_sizes'  => null,
			'large_src'    => null,
			'large_src_w'  => null,
			'large_src_h'  => null,
			'large_srcset' => null,
			'large_sizes'  => null,
		);

		if ( $attachment_id == 'placeholder' ) {
			$placeholder                   = wc_placeholder_img_src();
			$attachment_props['src']       = $placeholder;
			$attachment_props['thumb_src'] = $placeholder;
			$attachment_props['large_src'] = $placeholder;

			return $attachment_props;
		}

		$wc_attachment_props = wc_get_product_attachment_props( $attachment_id );
		$attachment_props    = wp_parse_args( $wc_attachment_props, $attachment_props );

		if ( version_compare( WC_VERSION, '3.3', '<' ) ) {
			// Large version.
			$src                            = wp_get_attachment_image_src( $attachment_id, 'full' );
			$attachment_props['full_src']   = $src[0];
			$attachment_props['full_src_w'] = $src[1];
			$attachment_props['full_src_h'] = $src[2];

			// Thumbnail version.
			$src                                         = wp_get_attachment_image_src( $attachment_id, 'shop_thumbnail' );
			$attachment_props['gallery_thumbnail_src']   = $src[0];
			$attachment_props['gallery_thumbnail_src_w'] = $src[1];
			$attachment_props['gallery_thumbnail_src_h'] = $src[2];

			// Image source.
			$src                        = wp_get_attachment_image_src( $attachment_id, 'shop_single' );
			$attachment_props['src']    = $src[0];
			$attachment_props['src_w']  = $src[1];
			$attachment_props['src_h']  = $src[2];
			$attachment_props['srcset'] = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, 'shop_single' ) : false;
			$attachment_props['sizes']  = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, 'shop_single' ) : false;
		}

		if ( empty( $attachment_props['src'] ) ) {
			return false;
		}

		global $iconic_woothumbs_class;

		$thumbnail_size_name = self::get_thumbnail_size_name();

		// Thumbnail retina
		$attachment_props['gallery_thumbnail_srcset'] = wp_get_attachment_image_srcset( $attachment_id, $thumbnail_size_name );
		$attachment_props['gallery_thumbnail_sizes']  = wp_get_attachment_image_sizes( $attachment_id, $thumbnail_size_name );

		// Large version.
		$large_size                       = $iconic_woothumbs_class->settings['display_images_large_image_size'];
		$src                              = wp_get_attachment_image_src( $attachment_id, $large_size );
		$attachment_props['large_src']    = $src[0];
		$attachment_props['large_src_w']  = $src[1];
		$attachment_props['large_src_h']  = $src[2];
		$attachment_props['large_srcset'] = wp_get_attachment_image_srcset( $attachment_id, $large_size );
		$attachment_props['large_sizes']  = wp_get_attachment_image_sizes( $attachment_id, $large_size );

		return $attachment_props;
	}

	/**
	 * Get thumbnail size name.
	 *
	 * @return string
	 */
	public static function get_thumbnail_size_name() {
		if ( version_compare( WC_VERSION, '3.3', '<' ) ) {
			return 'shop_thumbnail';
		} else {
			return 'woocommerce_gallery_thumbnail';
		}
	}

	/**
	 * After regenerate images.
	 *
	 * @param $message
	 * @param $level
	 * @param $context
	 *
	 * @return mixed
	 */
	public static function after_regenerate_images( $message, $level, $context ) {
		if ( empty( $context['source'] ) ) {
			return $message;
		}

		if ( $context['source'] !== 'wc-image-regeneration' ) {
			return $message;
		}

		if ( strpos( $message, 'Completed' ) === false ) {
			return $message;
		}

		global $iconic_woothumbs_class;

		$iconic_woothumbs_class->delete_transients( true );

		return $message;
	}

	/**
	 * Get image size data.
	 *
	 * @param string $size
	 *
	 * @return array
	 */
	public static function get_image_size_data( $size ) {
		$size_data = array();
		$crop      = self::get_image_crop( $size );

		$size_data['width']  = self::get_image_width( $size );
		$size_data['crop']   = $crop ? 1 : 0;
		$size_data['height'] = $crop ? self::get_image_height_from_crop( $size_data['width'], $crop ) : 9999;

		return $size_data;
	}

	/**
	 * Modify single image size.
	 *
	 * @param array $size_data
	 *
	 * @return array
	 */
	public static function get_image_size_single( $size_data ) {
		if ( version_compare( WC_VERSION, '3.3', '<' ) ) {
			return $size_data;
		}

		return self::get_image_size_data( 'single' );
	}

	/**
	 * Modify gallery thumbnail image size.
	 *
	 * @param array $size_data
	 *
	 * @return array
	 */
	public static function get_image_size_gallery_thumbnail( $size_data ) {
		if ( version_compare( WC_VERSION, '3.3', '<' ) ) {
			return $size_data;
		}

		return self::get_image_size_data( 'gallery_thumbnail' );
	}

	/**
	 * Get image height from width/crop settings.
	 *
	 * @param float $width
	 * @param array $crop
	 *
	 * @return float
	 */
	public static function get_image_height_from_crop( $width, $crop ) {
		return $width / ( ( $width * $crop['width'] ) / ( $width * $crop['height'] ) );
	}
}