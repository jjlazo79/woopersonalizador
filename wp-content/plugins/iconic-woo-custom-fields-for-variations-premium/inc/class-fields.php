<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_CFFV_Fields.
 *
 * @class    Iconic_CFFV_Fields
 * @version  1.0.0
 * @package  Iconic_CFFV
 * @category Class
 * @author   Iconic
 */
class Iconic_CFFV_Fields {
	/**
	 * Get product fields data.
	 *
	 * @param $product_id
	 *
	 * @return mixed
	 */
	public static function get_product_fields_data( $product_id ) {
		global $wpdb;

		static $data = array();

		if ( isset( $data[ $product_id ] ) ) {
			return $data[ $product_id ];
		}

		$data[ $product_id ] = false;

		$meta = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT *
			FROM $wpdb->postmeta
			WHERE post_id = '%d'
			AND meta_key LIKE 'iconic_cffv_%'
			",
			$product_id
		) );

		if ( ! $meta ) {
			return $data[ $product_id ];
		}

		$data[ $product_id ] = array();
		$groups = array();

		foreach( $meta as $meta_item ) {
			$ids = self::get_ids_from_meta_key( $meta_item->meta_key );

			if ( ! isset( $groups[ $ids['group_id'] ] ) ) {
				$groups[ $ids['group_id'] ] = Iconic_CFFV::get_variation_field_group_fields( $ids['group_id'] );
			}

			if ( ! isset( $groups[ $ids['group_id'] ][ $ids['field_id'] ] ) ) {
				continue;
			}

			$data[ $product_id ][ $ids['field_id'] ] = $groups[ $ids['group_id'] ][ $ids['field_id'] ];
			$data[ $product_id ][ $ids['field_id'] ]['value'] = maybe_unserialize( $meta_item->meta_value );
		}

		return $data[ $product_id ];
	}

	/**
	 * Get IDs from meta_key.
	 *
	 * @param string $meta_key
	 *
	 * @return array
	 */
	public static function get_ids_from_meta_key( $meta_key ) {
		$meta_key = str_replace( 'iconic_cffv_', '', $meta_key );
		$key_parts = explode( '_', $meta_key );

		return array(
			'group_id' => (int) array_shift( $key_parts ),
			'field_id' => implode( '_', $key_parts ),
		);
	}
}