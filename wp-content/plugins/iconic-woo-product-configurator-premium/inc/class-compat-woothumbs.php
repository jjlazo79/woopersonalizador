<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WooThumbs compatibility Class
 */
class Iconic_PC_Compat_WooThumbs {
	/**
	 * Init.
	 */
	public static function run() {
		if ( ! Iconic_PC_Core_Helpers::is_plugin_active( 'iconic-woothumbs/iconic-woothumbs.php' ) ) {
			return;
		}

		add_action( 'plugins_loaded', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Add hooks.
	 */
	public static function hooks() {
		if ( ! method_exists( 'Iconic_WooThumbs_Core_Licence', 'has_valid_licence' ) ) {
			return;
		}

		add_filter( 'woocommerce_get_script_data', array( __CLASS__, 'product_options' ), 10, 2 );
		add_action( 'woocommerce_before_single_product', array( __CLASS__, 'remove_default_display' ), 30 );
		add_filter( 'iconic_woothumbs_all_images_data', array( __CLASS__, 'all_images_data' ), 10, 3 );
	}

	/**
	 * Disable flex slider for configurator.
	 *
	 * @param $params
	 * @param $handle
	 *
	 * @return mixed
	 */
	public static function product_options( $params, $handle ) {
		if ( $handle !== 'wc-single-product' ) {
			return $params;
		}

		if ( ! Iconic_WooThumbs_Core_Licence::has_valid_licence() ) {
			return $params;
		}

		global $post, $iconic_woothumbs_class;

		if ( $iconic_woothumbs_class->is_enabled( $post->ID ) || ! Iconic_PC_Product::is_configurator_enabled( $post->ID ) ) {
			return $params;
		}

		$params['flexslider_enabled'] = false;

		return $params;
	}

	/**
	 * Process actions.
	 */
	public static function remove_default_display() {
		if ( ! Iconic_WooThumbs_Core_Licence::has_valid_licence() ) {
			return;
		}

		global $iconic_woothumbs_class, $product;

		$product_id = $product->get_id();

		if ( ! $iconic_woothumbs_class->is_enabled( $product_id ) || ! Iconic_PC_Product::is_configurator_enabled( $product_id ) ) {
			return;
		}

		global $jckpc;

		remove_action( 'woocommerce_before_single_product_summary', array( $jckpc, 'display_product_image' ), 20 );
		remove_action( 'iconic_woothumbs_before_thumbnail', array( 'Iconic_WooThumbs_Media', 'thumbnail_play_icon' ), 10 );
	}

	/**
	 * Remove Woo image wrapper class.
	 *
	 * @param $classes
	 *
	 * @return mixed
	 */
	public static function remove_wrapper_class( $classes ) {
		return Iconic_PC_Helpers::remove_item_by_value( $classes, 'woocommerce-product-gallery' );
	}

	/**
	 * Add configurator media to product.
	 *
	 * @param $images
	 * @param $product_id
	 *
	 * @return array
	 */
	public static function all_images_data( $images, $product_id ) {
		$parent_id  = wp_get_post_parent_id( $product_id );
		$product_id = $parent_id > 0 ? $parent_id : $product_id;

		if ( ! Iconic_PC_Product::is_configurator_enabled( $product_id ) ) {
			return $images;
		}

		add_filter( 'iconic_pc_images_wrapper_classes', array( __CLASS__, 'remove_wrapper_class' ), 10, 1 );
		add_filter( 'iconic_pc_image_layers', array( __CLASS__, 'remove_dummy_zoom_image' ), 10, 4 );

		global $jckpc;

		ob_start();
		$jckpc->display_product_image( false, $product_id );
		$images[0]['media_embed']   = ob_get_clean();
		$images[0]['no_media_icon'] = true;

		return $images;
	}

	/**
	 * Remove dummy zoom image.
	 *
	 * @param $images
	 * @param $product_id
	 * @param $setImages
	 * @param $defaults
	 *
	 * @return mixed
	 */
	public static function remove_dummy_zoom_image( $images, $product_id, $setImages, $defaults ) {
		unset( $images['dummy_zoom'] );

		return $images;
	}
}