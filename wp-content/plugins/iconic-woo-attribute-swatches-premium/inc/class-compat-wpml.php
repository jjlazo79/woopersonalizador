<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WAS_WPML.
 *
 * @class    Iconic_WAS_WPML
 * @version  1.0.0
 * @since    1.2.1
 * @author   Iconic
 */
class Iconic_WAS_Compat_WPML {
	/**
	 * Run.
	 */
	public static function run() {
		if ( ! Iconic_WAS_Helpers::is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			return;
		}

		add_filter( 'iconic_was_swatch_data_args', array( __CLASS__, 'swatch_data_args' ), 10, 2 );
		add_filter( 'iconic_was_get_term_meta', array( __CLASS__, 'get_term_meta' ), 10, 2 );
		add_filter( 'iconic_was_get_terms', array( __CLASS__, 'get_terms' ), 10, 2 );
		add_filter( 'iconic_was_swatch_meta', array( __CLASS__, 'swatch_meta' ), 10, 2 );
	}

	/**
	 * Modify product ID.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function swatch_data_args( $args ) {
		if ( empty( $args['product_id'] ) ) {
			return $args;
		}

		global $sitepress;

		$args['product_id'] = (int) wpml_object_id_filter( $args['product_id'], 'product', true, $sitepress->get_default_language() );

		return $args;
	}

	/**
	 * Modify term meta used for swatch data.
	 *
	 * @param mixed   $term_meta
	 * @param WP_Term $term
	 *
	 * @return mixed
	 */
	public static function get_term_meta( $term_meta, $term ) {
		global $iconic_was;

		return self::get_term_meta_for_default_lang( $term->term_id, $term->taxonomy, $iconic_was->attributes_class()->attribute_term_meta_name, true );
	}

	/**
	 * Modify term meta used for swatch data.
	 *
	 * @param array|false $terms
	 * @param array       $args
	 *
	 * @return mixed
	 */
	public static function get_terms( $terms, $args ) {
		return self::get_terms_for_default_lang( $args );
	}

	/**
	 * Use translated product specific attribute meta.
	 *
	 * @param $swatch_meta
	 * @param $product_id
	 *
	 * @return array
	 */
	public static function swatch_meta( $swatch_meta, $product_id ) {
		if ( empty( $swatch_meta ) ) {
			return $swatch_meta;
		}

		global $sitepress;

		$translated_product_id = (int) wpml_object_id_filter( $product_id, 'product', true, $sitepress->get_current_language() );

		foreach ( $swatch_meta as $attribute_name => $attribute_data ) {
			if ( taxonomy_exists( $attribute_name ) ) {
				$modified_attribute_meta = self::modify_attribute_meta( $attribute_data, array(
					'attribute_name' => $attribute_name,
					'product_id'     => $translated_product_id,
				) );
			} else {
				$modified_attribute_meta = self::modify_per_product_attribute_meta( $attribute_data, array(
					'attribute_name' => $attribute_name,
					'product_id'     => $translated_product_id,
				) );
			}

			$swatch_meta[ $attribute_name ] = $modified_attribute_meta;
		}

		return $swatch_meta;
	}

	/**
	 * Modify global attribute meta.
	 *
	 * @param array $attribute_data
	 * @param array $args
	 *
	 * @return array
	 */
	public static function modify_attribute_meta( $attribute_data, $args = array() ) {
		$defaults = array(
			'attribute_name' => null,
			'product_id'     => null,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( in_array( null, $args ) ) {
			return $attribute_data;
		}

		if ( empty( $attribute_data['values'] ) ) {
			return $attribute_data;
		}

		foreach ( $attribute_data['values'] as $term_slug => $term_data ) {
			$term = get_term_by( 'slug', $term_slug, $args['attribute_name'] );

			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}

			$attribute_data['values'][ $term->slug ] = $term_data;
			$attribute_data['values'][ $term->slug ]['label'] = $term->name;
		}

		return $attribute_data;
	}

	/**
	 * Modify per product attribute meta.
	 *
	 * @param array $attribute_data
	 * @param array $args
	 *
	 * @return array
	 */
	public static function modify_per_product_attribute_meta( $attribute_data, $args = array() ) {
		$defaults = array(
			'attribute_name' => null,
			'product_id'     => null,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( in_array( null, $args ) ) {
			return $attribute_data;
		}

		$translated_product_attributes = get_post_meta( $args['product_id'], '_product_attributes', true );

		$stripped_attribute_name = str_replace( 'attribute_', '', $args['attribute_name'] );

		if ( ! isset( $translated_product_attributes[ $stripped_attribute_name ] ) ) {
			return $attribute_data;
		}

		if ( empty( $attribute_data['values'] ) ) {
			return $attribute_data;
		}

		$values = explode( ' | ', $translated_product_attributes[ $stripped_attribute_name ]['value'] );

		$i = 0;
		foreach ( $attribute_data['values'] as $value => $data ) {
			if ( ! isset( $values[ $i ] ) ) {
				continue;
			}

			$key           = strtolower( $values[ $i ] );
			$data['label'] = $values[ $i ];

			unset( $attribute_data['values'][ $value ] );
			$attribute_data['values'][ $key ] = $data;
			$i ++;
		}

		return $attribute_data;
	}

	/**
	 * Get term for default language.
	 *
	 * @param array $args
	 *
	 * @return array|null|WP_Error|WP_Term|false
	 */
	public static function get_terms_for_default_lang( $args ) {
		global $icl_adjust_id_url_filter_off;

		if ( empty( $args ) ) {
			return false;
		}

		$orig_flag_value = $icl_adjust_id_url_filter_off;

		$icl_adjust_id_url_filter_off = true;
		$terms                        = get_terms( $args );
		$icl_adjust_id_url_filter_off = $orig_flag_value;

		return $terms;
	}

	/**
	 * Get term meta for default language.
	 *
	 * @param int    $term_id
	 * @param string $taxonomy
	 * @param string $key
	 * @param bool   $single
	 *
	 * @return array|null|WP_Error|WP_Term|false
	 */
	public static function get_term_meta_for_default_lang( $term_id, $taxonomy, $key, $single = false ) {
		global $sitepress;
		global $icl_adjust_id_url_filter_off;

		$default_term_id = (int) wpml_object_id_filter( $term_id, $taxonomy, true, $sitepress->get_default_language() );

		$orig_flag_value = $icl_adjust_id_url_filter_off;

		$icl_adjust_id_url_filter_off = true;
		$term_meta                    = get_term_meta( $default_term_id, $key, $single );
		$icl_adjust_id_url_filter_off = $orig_flag_value;

		return $term_meta;
	}
}