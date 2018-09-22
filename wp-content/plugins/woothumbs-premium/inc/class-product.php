<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WooThumbs_Product.
 *
 * @class    Iconic_WooThumbs_Product
 * @version  1.0.0
 * @package  Iconic_WooThumbs
 * @category Class
 * @author   Iconic
 */
class Iconic_WooThumbs_Product {
	/**
	 * Get Product
	 *
	 * @param int $id
	 *
	 * @return WC_Product
	 */
	public static function get_product( $id ) {
		$post_type = get_post_type( $id );

		if ( $post_type !== "product_variation" ) {
			return wc_get_product( absint( $id ) );
		}

		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			return wc_get_product( absint( $id ), array( 'product_type' => 'variable' ) );
		} else {
			return new WC_Product_Variation( absint( $id ) );
		}
	}

	/**
	 * Get parent ID
	 *
	 * @param WC_Product $product
	 *
	 * @return int
	 */
	public static function get_parent_id( $product ) {
		return method_exists( $product, 'get_parent_id' ) ? $product->get_parent_id() : $product->id;
	}

	/**
	 * Get gallery image IDs
	 *
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	public static function get_gallery_image_ids( $product ) {
		return method_exists( $product, 'get_gallery_image_ids' ) ? $product->get_gallery_image_ids() : $product->get_gallery_attachment_ids();
	}

	/**
	 * Find matching product variation
	 *
	 * @param WC_Product $product
	 * @param array      $attributes
	 *
	 * @return int Matching variation ID or 0.
	 */
	public static function find_matching_product_variation( $product, $attributes ) {
		foreach ( $attributes as $key => $value ) {
			if ( strpos( $key, 'attribute_' ) === 0 ) {
				continue;
			}

			unset( $attributes[ $key ] );
			$attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
		}

		$attributes_string = serialize( $attributes );
		$attributes_md5 = md5( $attributes_string );
		$transient_name = sprintf( 'iconic-woothumbs_matching_variation_%d_%s', $product->get_id(), $attributes_md5 );

		if ( $matching_product_variation = get_transient( $transient_name ) ) {
			return $matching_product_variation;
		}

		if ( class_exists( 'WC_Data_Store' ) ) {
			$data_store = WC_Data_Store::load( 'product' );
			$matching_product_variation = $data_store->find_matching_product_variation( $product, $attributes );
		} else {
			$matching_product_variation = $product->get_matching_variation( $attributes );
		}

		set_transient( $transient_name, $matching_product_variation, 48 * HOUR_IN_SECONDS );

		return $matching_product_variation;
	}

	/**
	 * Get product settings.
	 *
	 * @param int $product_id
	 *
	 * @return array
	 */
	public static function get_settings( $product_id ) {
		static $product_settings = array();

		if ( empty( $product_settings[ $product_id ] ) ) {
			$product_settings[ $product_id ] = (array) get_post_meta( $product_id, '_iconic_woothumbs', true );
		}

		return $product_settings[ $product_id ];
	}

	/**
	 * Get product setting.
	 *
	 * @param $product_id
	 * @param $setting
	 *
	 * @return mixed
	 */
	public static function get_setting( $product_id, $setting ) {
		$settings = self::get_settings( $product_id );

		if ( ! isset( $settings[ $setting ] ) ) {
			return null;
		}

		return $settings[ $setting ];
	}
}