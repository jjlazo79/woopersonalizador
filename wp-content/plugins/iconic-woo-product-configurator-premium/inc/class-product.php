<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_PC_Product.
 *
 * @class    Iconic_PC_Product
 * @version  1.0.0
 * @since    1.2.0
 * @author   Iconic
 */
class Iconic_PC_Product {
	/**
	 * Get image size name.
	 *
	 * @param $size
	 *
	 * @return string
	 */
	public static function get_image_size( $size ) {
		$is_woo_greater_than_3 = version_compare( WC_VERSION, '3.0.0', '>=' );

		switch ( $size ) {
			case 'single':
				return $is_woo_greater_than_3 ? 'woocommerce_single' : 'shop_single';
			case 'thumbnail':
				return $is_woo_greater_than_3 ? 'woocommerce_thumbnail' : 'shop_thumbnail';
			case 'catalog':
				return $is_woo_greater_than_3 ? 'woocommerce_thumbnail' : 'shop_catalog';
			default:
				return $size;
		}
	}

	/**
	 * Get gallery image ids.
	 *
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	public static function get_gallery_image_ids( $product ) {
		if ( method_exists( $product, 'get_gallery_image_ids' ) ) {
			return $product->get_gallery_image_ids();
		} else {
			return $product->get_gallery_attachment_ids();
		}
	}

	/**
	 * Get set images for product.
	 *
	 * @param int $product_id
	 *
	 * @return array|bool
	 */
	public static function get_set_images( $product_id ) {
		static $images = array();

		if ( ! isset( $images[ $product_id ] ) ) {
			$images[ $product_id ] = get_post_meta( $product_id, 'jckpc_images', true );
		}

		return apply_filters( 'iconic_pc_set_images', $images[ $product_id ], $product_id );
	}

	/**
	 * Get product attributes in a unified format.
	 *
	 * @param int $product_id
	 *
	 * @return array
	 */
	public static function get_attributes( $product_id ) {
		static $attribute_values = array();

		if ( ! empty( $attribute_values[ $product_id ] ) ) {
			return $attribute_values[ $product_id ];
		}

		$product                         = wc_get_product( $product_id );
		$attributes                      = $product->get_attributes();
		$attribute_values[ $product_id ] = array();

		if ( empty( $attributes ) ) {
			return $attribute_values[ $product_id ];
		}

		foreach ( $attributes as $attribute_name => $attribute_data ) {
			if ( ! $attribute_data['is_variation'] ) {
				continue;
			}

			$attribute_values[ $product_id ][ $attribute_name ] = array();

			$attribute_value_i = 0;

			if ( $attribute_data['is_taxonomy'] ) {
				$tax                                                        = get_taxonomy( $attribute_name );
				$attribute_values[ $product_id ][ $attribute_name ]['name'] = $tax->labels->name;
				$terms                                                      = get_terms( $attribute_name, array( "hide_empty" => false ) );

				if ( is_array( $terms ) ) {
					foreach ( $terms as $term ) {
						if ( has_term( $term->term_id, $attribute_name, $product_id ) ) {
							$attribute_values[ $product_id ][ $attribute_name ]['values'][ $attribute_value_i ]['att_val_name'] = $term->name;
							$attribute_values[ $product_id ][ $attribute_name ]['values'][ $attribute_value_i ]['att_val_slug'] = $term->slug;
							$attribute_values[ $product_id ][ $attribute_name ]['values'][ $attribute_value_i ]['att_val_id']   = $term->term_id;
							$attribute_value_i ++;
						}
					}
				}
			} else {
				$attribute_values[ $product_id ][ $attribute_name ]['name'] = $attribute_data['name'];
				$terms                                                      = explode( ' | ', $attribute_data['value'] );

				if ( is_array( $terms ) ) {
					foreach ( $terms as $term ) {
						$attribute_values[ $product_id ][ $attribute_name ]['values'][ $attribute_value_i ]['att_val_name'] = $term;
						$attribute_values[ $product_id ][ $attribute_name ]['values'][ $attribute_value_i ]['att_val_slug'] = sanitize_title( $term );
						$attribute_value_i ++;
					}
				}
			}
		}

		return $attribute_values[ $product_id ];
	}

	/**
	 * Get Attribute Value ID
	 *
	 * Get the att val ID for use when checking or altering
	 * stock levels for individual attribute values.
	 *
	 * @param int    $product_id
	 * @param string $chosen_attribute_slug
	 * @param string $chosen_attribute_value
	 *
	 * @return string|bool
	 */
	public static function get_attribute_value_id( $product_id, $chosen_attribute_slug, $chosen_attribute_value ) {
		static $attribute_value_ids = array();

		$chosen_attribute_slug            = Iconic_PC_Helpers::sanitise_str( $chosen_attribute_slug );
		$sanitised_chosen_attribute_value = Iconic_PC_Helpers::sanitise_str( $chosen_attribute_value );
		$key                              = sprintf( '%d_%s_%s', $product_id, $chosen_attribute_slug, $sanitised_chosen_attribute_value );

		if ( isset( $attribute_value_ids[ $key ] ) ) {
			return $attribute_value_ids[ $key ];
		}

		$attribute_value_ids[ $key ] = false;
		$available_attributes        = Iconic_PC_Product::get_attributes( $product_id );

		if ( empty( $available_attributes ) ) {
			return false;
		}

		foreach ( $available_attributes as $attribute_slug => $attribute_data ) {
			$attribute_slug = Iconic_PC_Helpers::sanitise_str( $attribute_slug );

			if ( $attribute_slug !== $chosen_attribute_slug ) {
				continue;
			}

			foreach ( $attribute_data['values'] as $attribute_value ) {
				if ( ! in_array( $chosen_attribute_value, array( $attribute_value['att_val_name'], $attribute_value['att_val_slug'] ) ) ) {
					continue;
				}

				$attribute_value_slug        = Iconic_PC_Helpers::sanitise_str( $attribute_value['att_val_slug'] );
				$attribute_value_ids[ $key ] = sprintf( '%s_%s', $attribute_slug, $attribute_value_slug );
				break;
			}
		}

		return $attribute_value_ids[ $key ];
	}

	/**
	 * Is configurator enabled?
	 *
	 * @param $product_id
	 *
	 * @return bool
	 */
	public static function is_configurator_enabled( $product_id = false ) {
		if ( ! $product_id ) {
			global $product;

			if ( ! $product ) {
				return false;
			}

			$product_id = $product->get_id();
		}

		static $enabled = array();

		if ( isset( $enabled[ $product_id ] ) ) {
			return $enabled[ $product_id ];
		}

		$product = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			$enabled[ $product_id ] = false;

			return $enabled[ $product_id ];
		}

		$enabled[ $product_id ] = $product->get_meta( 'jckpc_enabled', true ) === 'yes';

		return $enabled[ $product_id ];
	}
}