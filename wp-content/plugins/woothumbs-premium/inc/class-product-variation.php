<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WooThumbs_Product_Variation.
 *
 * @class    Iconic_WooThumbs_Product_Variation
 * @version  1.0.0
 * @package  Iconic_WooThumbs
 * @category Class
 * @author   Iconic
 */
class Iconic_WooThumbs_Product_Variation {
	/**
	 * Run.
	 */
	public static function run() {
		add_filter( 'woocommerce_product_get_gallery_image_ids', array( __CLASS__, 'get_gallery_image_ids', ), 10, 2 );
		add_filter( 'woocommerce_product_variation_get_gallery_image_ids', array( __CLASS__, 'get_gallery_image_ids', ), 10, 2 );
	}

	/**
	 * Filter get_gallery_image_ids().
	 *
	 * @param array      $value
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	public static function get_gallery_image_ids( $value, $product ) {
		if ( ! $product->is_type( 'variation' ) ) {
			return $value;
		}

		$image_ids = get_post_meta( $product->get_id(), 'variation_image_gallery', true );
		$image_ids = array_filter( explode( ',', $image_ids ) );
		$parent_product_id = Iconic_WooThumbs_Product::get_parent_id( $product );

		if ( ( empty( $image_ids ) && ! has_post_thumbnail( $product->get_id() ) ) || Iconic_WooThumbs_Product::get_setting( $parent_product_id, 'maintain_product_gallery' ) ) {
			$parent_product    = wc_get_product( $parent_product_id );
			$image_ids         = array_filter( array_merge( $image_ids, Iconic_WooThumbs_Product::get_gallery_image_ids( $parent_product ) ) );
		}

		return $image_ids;
	}
}