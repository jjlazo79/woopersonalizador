<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_CFFV_Updates.
 *
 * @class    Iconic_CFFV_Updates
 * @version  1.0.0
 * @package  Iconic_CFFV
 * @category Class
 * @author   Iconic
 */
class Iconic_CFFV_Updates {
	/**
	 * Update fields.
	 */
	public static function fields() {
		self::update_field_groups();
		self::update_product_meta();
	}

	/**
	 * Update field groups.
	 */
	public static function update_field_groups() {
		global $wpdb;

		$groups = $wpdb->get_results(
			"
			SELECT *
			FROM $wpdb->postmeta
			WHERE meta_key = 'iconic-cffv-field-data'
			"
		);

		if ( empty( $groups ) ) {
			return;
		}

		foreach ( $groups as $group_i => $group ) {
			$fields = maybe_unserialize( $group->meta_value );
			$fields = array_map( 'stripslashes', $fields );
			$fields = array_map( 'json_decode', $fields );

			if ( empty( $fields ) ) {
				continue;
			}

			foreach ( $fields as $field_i => $field ) {
				$fields[ $field_i ]->id = str_replace( '-', '_', sanitize_title_with_dashes( $field->label ) );
			}

			$fields = array_map( 'json_encode', $fields );
			$fields = array_map( 'addslashes', $fields );

			$groups[ $group_i ]->meta_value = $fields;

			update_post_meta( $groups[ $group_i ]->post_id, $groups[ $group_i ]->meta_key, wp_slash( $groups[ $group_i ]->meta_value ) );
		}
	}

	/**
	 * Update product meta.
	 */
	public static function update_product_meta() {
		global $wpdb;

		$fields = $wpdb->get_results(
			"
			SELECT *
			FROM $wpdb->postmeta
			WHERE meta_key = 'iconic_cffv'
			"
		);

		if ( empty( $fields ) ) {
			return;
		}

		foreach ( $fields as $field ) {
			$value = maybe_unserialize( $field->meta_value );

			if ( empty( $value ) ) {
				continue;
			}

			foreach ( $value as $field_group_id => $values ) {
				$field_group_id     = (int) str_replace( 'field_group_', '', $field_group_id );
				$field_group_fields = Iconic_CFFV::get_variation_field_group_fields( $field_group_id );

				if ( empty( $field_group_fields ) ) {
					continue;
				}

				$i = 0;

				foreach ( $field_group_fields as $field_id => $field_group_field ) {
					if ( empty( $values[ $i ] ) ) {
						continue;
					}

					$meta_key   = sprintf( 'iconic_cffv_%d_%s', $field_group_id, $field_id );
					$meta_value = $values[ $i ];

					update_post_meta( $field->post_id, $meta_key, $meta_value );
					$i ++;
				}
			}

			delete_post_meta( $field->post_id, $field->meta_key );
		}
	}
}