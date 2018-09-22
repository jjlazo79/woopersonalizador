<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Iconic_PC_Ajax
 */
class Iconic_PC_Ajax {
	/**
	 * Init
	 */
	public static function run() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// iconic_pc_{event} => nopriv
		$ajax_events = array(
			'get_conditional_group' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_iconic_pc_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_iconic_pc_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Get conditional group.
	 */
	public static function get_conditional_group() {
		check_ajax_referer( 'iconic-pc', 'nonce' );

		$data = array(
			'layer_id' => filter_input( INPUT_POST, 'layer_id', FILTER_SANITIZE_STRING ),
			'product_id' => absint( filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT ) ),
			'condition_id' => absint( filter_input( INPUT_POST, 'condition_id', FILTER_SANITIZE_NUMBER_INT ) ),
		);

		if ( empty( $data['product_id'] ) ) {
			wp_send_json_error( $data );
		}

		$attributes = Iconic_PC_Product::get_attributes( $data['product_id'] );

		ob_start();
		Iconic_PC_Templates::conditional_layer( $data['layer_id'], $attributes, null, $data['condition_id'] );
		$data['html'] = ob_get_clean();

		wp_send_json_success( $data );
	}

	/**
	 * Add item to wishlist.
	 */
	public static function add_to_wishlist() {
		check_ajax_referer( 'iconic-ww', 'nonce' );

		$default_error = __( 'An error occurred when adding that item. Please try again.', 'iconic-ww' );

		$data = array(
			'request_id' => absint( filter_input( INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT ) ),
		);

		$post_data = Iconic_WW_Helpers::get_formatted_post_data( $_POST['data'] );

		if ( empty( $post_data ) || empty( $post_data['product_id'] ) ) {
			$data['message'] = $default_error;
			wp_send_json_error( $data );
		}

		$product = wc_get_product( $post_data['product_id'] );

		if ( ! $product ) {
			$data['message'] = $default_error;
			wp_send_json_error( $data );
		}

		if ( $post_data['wishlist_type'] === 'existing' ) {
			if ( empty( $post_data['existing_wishlist'] ) ) {
				$data['message'] = __( 'Please choose an existing wishlist.', 'iconic-ww' );
				wp_send_json_error( $data );
			}

			$wishlist_id = $post_data['existing_wishlist'];
		} else {
			if ( empty( $post_data['existing_wishlist'] ) ) {
				$data['message'] = __( 'Please enter a name for your wishlist.', 'iconic-ww' );
				wp_send_json_error( $data );
			}

			$wishlist_id = Iconic_WW_Wishlists::create_wishlist( array(
				'post_title' => $post_data['new_wishlist'],
			) );
		}

		$wishlist = new Iconic_WW_Wishlist( $wishlist_id );
		$add_item = $wishlist->add_item( $post_data['product_id'] );

		if ( ! $add_item ) {
			$data['message'] = $default_error;
			wp_send_json_error( $data );
		}

		if ( $add_item === 'exists' ) {
			$data['message'] = sprintf( __( 'You have already added %s to "%s".', 'iconic-ww' ), $product->get_name(), $wishlist->get( 'post_title' ) );
			wp_send_json_error( $data );
		}

		$data['message'] = sprintf( __( '%s was successfully added to "%s".', 'iconic-ww' ), $product->get_name(), $wishlist->get( 'post_title' ) );

		wp_send_json_success( $data );
	}
}