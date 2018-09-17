<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_PC_WPML.
 *
 * @class    Iconic_PC_WPML
 * @version  1.0.0
 * @since    1.2.1
 * @author   Iconic
 */
class Iconic_PC_WPML {
	/**
	 * Run.
	 */
	public static function run() {
		if ( ! Iconic_PC_Helpers::is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			return;
		}

		add_filter( 'iconic_pc_set_images', array( __CLASS__, 'set_images' ), 10, 2 );
		add_filter( 'iconic_pc_query_string_attributes', array( __CLASS__, 'query_string_attributes' ), 10 );
		add_filter( 'iconic_pc_localize_script', array( __CLASS__, 'localize_script' ), 10, 1 );
		add_action( 'iconic_pc_before_get_image_layer', array( __CLASS__, 'set_ajax_language' ), 10 );
	}

	/**
	 * Change set image keys to translated term.
	 *
	 * @param array|bool $images
	 * @param int        $product_id
	 *
	 * @return array|bool
	 */
	public static function set_images( $images, $product_id ) {
		if ( empty( $images ) ) {
			return $images;
		}

		foreach ( $images as $layer => $images_ids ) {
			if ( ! is_array( $images_ids ) ) {
				continue;
			}

			foreach ( $images_ids as $term_slug => $image_id ) {
				$taxonomy             = Iconic_PC_Helpers::strip_prefix( $layer );
				$term_slug_stripped   = Iconic_PC_Helpers::strip_prefix( $term_slug );
				$term                 = get_term_by( 'slug', $term_slug_stripped, $taxonomy );
				$translated_term_slug = sprintf( 'jckpc-%s', $term->slug );

				$images[ $layer ][ $translated_term_slug ] = $image_id;
			}
		}

		return $images;
	}

	/**
	 * Get original query string translations.
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public static function query_string_attributes( $attributes ) {
		if ( empty( $attributes ) ) {
			return $attributes;
		}

		foreach ( $attributes as $taxonomy => $term_slug ) {
			$stripped_taxonomy  = Iconic_PC_Helpers::strip_prefix( $taxonomy );
			$stripped_term_slug = Iconic_PC_Helpers::strip_prefix( $term_slug );
			$term               = get_term_by( 'slug', $stripped_term_slug, $stripped_taxonomy );
			$original_term      = self::get_term_for_default_lang( $term, $stripped_taxonomy );

			if ( ! $original_term || is_wp_error( $original_term ) ) {
				continue;
			}

			$attributes[ $taxonomy ] = sprintf( 'jckpc-%s', $original_term->slug );
		}

		return $attributes;
	}

	/**
	 * Get term for default language.
	 *
	 * @param int| WP_Term $term
	 * @param string       $taxonomy
	 *
	 * @return array|null|WP_Error|WP_Term|false
	 */
	public static function get_term_for_default_lang( $term, $taxonomy ) {
		global $sitepress;
		global $icl_adjust_id_url_filter_off;

		if ( ! $term || is_wp_error( $term ) ) {
			return false;
		}

		$term_id = is_int( $term ) ? $term : $term->term_id;

		$default_term_id = (int) wpml_object_id_filter( $term_id, $taxonomy, true, $sitepress->get_default_language() );

		$orig_flag_value = $icl_adjust_id_url_filter_off;

		$icl_adjust_id_url_filter_off = true;
		$term                         = get_term( $default_term_id, $taxonomy );
		$icl_adjust_id_url_filter_off = $orig_flag_value;

		return $term;
	}

	/**
	 * Modify script args.
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public static function localize_script( $args ) {
		$current_lang = apply_filters( 'wpml_current_language', null );

		if ( $current_lang ) {
			$args['ajaxurl'] = add_query_arg( 'wpml_lang', $current_lang, $args['ajaxurl'] );
		}

		return $args;
	}

	/**
	 * Set language in ajax.
	 */
	public static function set_ajax_language() {
		$lang = filter_input( INPUT_GET, 'wpml_lang', FILTER_SANITIZE_STRING );

		if ( $lang ) {
			do_action( 'wpml_switch_language', $lang );
		}
	}
}