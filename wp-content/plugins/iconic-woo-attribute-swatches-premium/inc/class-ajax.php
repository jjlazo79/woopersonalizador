<?php
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

/**
 * Class Iconic_WAS_Ajax
 */
class Iconic_WAS_Ajax {
	/**
	 * Hook in ajax handlers.
	 */
	public static function run() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'get_attribute_terms' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_iconic_was_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_iconic_was_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Get attribute terms.
	 */
	public static function get_attribute_terms() {
		$term         = filter_input( INPUT_GET, 'term' );
		$attribute_id = filter_input( INPUT_GET, 'include', FILTER_SANITIZE_NUMBER_INT );
		$taxonomy     = wc_attribute_taxonomy_name_by_id( $attribute_id );

		$terms = array();
		$search_terms = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'name__like' => $term,
		) );

		if ( ! $search_terms ) {
			wp_send_json( $terms );
		}

		foreach( $search_terms as $term ) {
			$terms[ $term->term_id ] = $term->name;
		}

		wp_send_json( $terms );
	}
}