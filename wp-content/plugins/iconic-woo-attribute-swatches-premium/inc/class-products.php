<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Products
 *
 * This class is for anything product related
 *
 * @class          Iconic_WAS_Products
 * @version        1.0.0
 * @category       Class
 * @author         Iconic
 */
class Iconic_WAS_Products {
	/**
	 * Swatch data for a single (current) product
	 *
	 * @var array $swatch_data
	 */
	public $swatch_data = array();

	/**
	 * Run actions/filters for this class
	 */
	public function run() {
		if ( is_admin() ) {
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'product_tab' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'product_tab_fields' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_fields' ) );
			add_action( 'wp_ajax_iconic_was_get_product_attribute_fields', array( $this, 'get_product_attribute_fields' ) );
		} else {
			add_action( 'init', array( $this, 'position_swatches_in_loop' ) );
			add_filter( 'woocommerce_dropdown_variation_attribute_options_args', array( $this, 'dropdown_variation_attribute_options_args' ), 10, 1 );
		}
	}

	/**
	 * Admin: Add tab to product edit page
	 */
	public function product_tab() {
		global $post, $iconic_was;

		printf( '<li class="%1$s-options-tab show_if_variable"><a href="#%1$s-options"><span>%2$s</span></a></li>', $iconic_was->slug, __( 'Swatches', 'iconic-was' ) );
	}

	/**
	 * Admin: Add custom product fields
	 */
	public function product_tab_fields() {
		global $woocommerce, $post, $iconic_was;

		// $product_settings = get_post_meta($post->ID, '_jck_wt', true);

		include_once( ICONIC_WAS_PATH . 'inc/admin/product-tab.php' );
	}

	/**
	 * Admin: Save custom product fields
	 *
	 * @param int $product_id
	 */
	public function save_product_fields( $product_id ) {
		$product_settings = array();

		if ( isset( $_POST['iconic-was'] ) ) {
			if ( empty( $_POST['iconic-was'] ) ) {
				return;
			}

			foreach ( $_POST['iconic-was'] as $attribute_slug => $value ) {
				if ( ! empty( $value['swatch_type'] ) ) {
					$product_settings[ $attribute_slug ] = $value;
				} else {
					$product_settings[ $attribute_slug ] = array( 'swatch_type' => '' );
				}
			}

			update_post_meta( $product_id, '_iconic-was', $product_settings );
		}
	}

	/**
	 * Admin: Get product swatch data for attribute
	 *
	 * @param int    $product_id
	 * @param string $attribute_slug
	 *
	 * @return array
	 */
	public function get_product_swatch_data_for_attribute( $product_id, $attribute_slug ) {
		if ( ! isset( $this->swatch_data[ $product_id ] ) ) {
			$this->swatch_data[ $product_id ] = get_post_meta( $product_id, '_iconic-was', true );
		}

		if ( isset( $this->swatch_data[ $product_id ][ $attribute_slug ] ) ) {
			return $this->swatch_data[ $product_id ][ $attribute_slug ];
		}

		return array(
			'swatch_type' => "",
			'values'      => false,
		);
	}

	/**
	 * Ajax: Get product attribute fields
	 */
	public function get_product_attribute_fields() {
		global $iconic_was;

		$return = array(
			'success' => true,
			'fields'  => false,
		);

		$attributes = $iconic_was->attributes_class()->get_variation_attributes_for_product( $_POST['product_id'] );
		$attribute  = isset( $attributes[ $_POST['attribute_slug'] ] ) ? $attributes[ $_POST['attribute_slug'] ] : false;

		$saved_values = $iconic_was->swatches_class()->get_product_swatch_data( $_POST['product_id'], $_POST['attribute_slug'] );
		$swatch_type  = $_POST['swatch_type'];

		if ( $saved_values && ! empty( $saved_values ) ) {
			// Remove values if we're loading a different swatch type.
			$saved_values['values'] = $swatch_type === $saved_values['swatch_type'] ? $saved_values['values'] : array();
		}

		ob_start();
		include( ICONIC_WAS_PATH . 'inc/admin/product-attribute-options.php' );
		$return['fields'] = ob_get_clean();

		wp_send_json( $return );
	}

	/**
	 * Position swatches in loop
	 */
	public function position_swatches_in_loop() {
		$loop_position = apply_filters( 'iconic_was_loop_position', 'woocommerce_after_shop_loop_item' );
		$loop_priority = apply_filters( 'iconic_was_loop_priority', 8 );
		add_action( $loop_position, array( $this, 'add_swatches_to_loop' ), $loop_priority );
	}

	/**
	 * Add swatches to loop based on settings for attribute
	 */
	public function add_swatches_to_loop() {
		global $product, $iconic_was;

		// SSV integration: Use parent product temporarily
		if( is_a( $product, 'WC_Product_Variation') ) {
			$parent_product   = $product->get_parent_id();
			$original_product = $product;
			$product          = wc_get_product( $parent_product );
		} 
        
		if ( ! $product || ! $product->is_type( "variable" ) ) {
			return;
		}

		$attributes = $product->get_variation_attributes();

		if ( ! $attributes ) {
			// SSV integration: Revert to original product variation
			if ( ! empty( $original_product ) ) {
				$product = $original_product;
			}

			return;
		}

		foreach ( $attributes as $attribute_slug => $attribute_terms ) {
			if ( empty( $attribute_terms ) ) {
				continue;
			}

			$attribute_slug = $iconic_was->attributes_class()->format_attribute_slug( $attribute_slug );
			$product_id     = $product->get_id();
			$swatch_data    = $iconic_was->swatches_class()->get_swatch_data( array(
				'product_id'     => $product_id,
				'attribute_slug' => $attribute_slug,
			) );

			if ( empty( $swatch_data['loop'] ) ) {
				continue;
			}

			$product_url = $product->get_permalink();

			$swatch_type   = $swatch_data['swatch_type'] === "radio-buttons" ? "text-swatch" : $swatch_data['swatch_type'];
			$swatch_shape  = $iconic_was->swatches_class()->get_swatch_option( 'swatch_shape', $attribute_slug );
			$tooltips      = (bool) $iconic_was->swatches_class()->get_swatch_option( 'tooltips', $attribute_slug );
			$large_preview = (bool) $iconic_was->swatches_class()
			                                   ->get_swatch_option( 'large_preview', $attribute_slug );
			$visual        = $iconic_was->swatches_class()
			                            ->is_swatch_visual( $swatch_type ) ? "iconic-was-swatches--visual" : false;
			$tooltips      = $visual && ( $tooltips || $large_preview ) ? "iconic-was-swatches--tooltips" : false;
			$shape         = $visual && $swatch_shape == "round" ? "iconic-was-swatches--round" : "iconic-was-swatches--square";
			$loop_method   = empty( $swatch_data['loop-method'] ) ? "link" : $swatch_data['loop-method'];

			$swatches_html = sprintf( '<ul class="iconic-was-swatches iconic-was-swatches--loop iconic-was-swatches--%s %s %s %s" data-attribute="%s">', $swatch_type, $visual, $tooltips, $shape, $attribute_slug );

			foreach ( $attribute_terms as $attribute_term ) {
				if ( ! $attribute_term ) {
					continue;
				}

				$first_variation_id = $this->get_first_variation_id_for_attribute_value( $product, $attribute_slug, $attribute_term );

				if ( ! $first_variation_id ) {
					continue;
				}

				$variation_image_url     = false;
				$attribute_slug_prefixed = $iconic_was->attributes_class()
				                                      ->format_attribute_slug( $attribute_slug, true );
				$url                     = esc_url( add_query_arg( array( $attribute_slug_prefixed => $attribute_term ), $product_url ) );
				$swatch_html             = $iconic_was->swatches_class()
				                                      ->get_swatch_html( $swatch_data, $attribute_term );

				if ( $loop_method === "image" ) {
					$variation_image_url = $this->get_variation_image_by_attribute( $product, $attribute_slug, $attribute_term );
					$url                 = $variation_image_url ? $variation_image_url[0] : $url;

					if ( $variation_image_url ) {
						$swatch_item_html = sprintf( '<li class="iconic-was-swatches__item"><a href="%s" class="iconic-was-swatch iconic-was-swatch--follow iconic-was-swatch--change-image iconic-was-swatch--%s" data-srcset="%s" data-sizes="%s">%s</a></li>', $url, $swatch_data['swatch_type'], $variation_image_url['srcset'], $variation_image_url['sizes'], $swatch_html );
					}
				}

				if ( $loop_method === "link" || ! $variation_image_url ) {
					$swatch_item_html = sprintf( '<li class="iconic-was-swatches__item"><a href="%s" class="iconic-was-swatch iconic-was-swatch--follow iconic-was-swatch--%s">%s</a></li>', $url, $swatch_data['swatch_type'], $swatch_html );
				}

				$swatches_html .= apply_filters( 'iconic_was_swatch_item_loop_html', $swatch_item_html, $swatch_data, $attribute_term, $product );
			}

			$swatches_html .= '</ul>';

			// SSV integration: Revert to original product variation
			if ( ! empty( $original_product ) ) {
				$product = $original_product;
			}

			echo apply_filters( 'iconic_was_swatches_loop_html', $swatches_html, $swatch_data, $product );
		}
	}

	/**
	 * Get Variation Image by Attribute
	 *
	 * @return bool|array
	 */
	public function get_variation_image_by_attribute( $product, $attribute_name, $attribute_value ) {
		global $iconic_was;

		$attribute_name = $iconic_was->attributes_class()->format_attribute_slug( $attribute_name );

		$first_variation_id = $this->get_first_variation_id_for_attribute_value( $product, $attribute_name, $attribute_value );

		if ( ! $first_variation_id ) {
			return false;
		}

		$post_thumbnail_id = get_post_thumbnail_id( $first_variation_id );

		if ( ! $post_thumbnail_id ) {
			return false;
		}

		$thumbnail_size = Iconic_WAS_Helpers::get_image_size_name( 'shop_thumbnail' );
		$post_thumbnail = wp_get_attachment_image_src( $post_thumbnail_id, $thumbnail_size );

		if ( ! $post_thumbnail ) {
			return false;
		}

		$post_thumbnail['srcset'] = wp_get_attachment_image_srcset( $post_thumbnail_id, $thumbnail_size );
		$post_thumbnail['sizes']  = wp_get_attachment_image_sizes( $post_thumbnail_id, $thumbnail_size );

		return $post_thumbnail;
	}

	/**
	 * Get first variation for attribute value.
	 *
	 * @param WC_Product_Variable $product
	 * @param string              $attribute_name
	 * @param string              $attribute_value
	 *
	 * @return bool
	 */
	public function get_first_variation_id_for_attribute_value( $product, $attribute_name, $attribute_value ) {
		$product_id = $product->get_id();
		$id         = hash( 'md5', $product_id . $attribute_name . $attribute_value );

		static $variation_ids = array();

		if ( isset( $variation_ids[ $id ] ) ) {
			return $variation_ids[ $id ];
		}

		$variation_ids[ $id ] = false;
		$attribute_name       = 'attribute_' . sanitize_title( $attribute_name );
		$variations           = $product->get_available_variations();

		if ( empty( $variations ) ) {
			return $variation_ids[ $id ];
		}

		foreach ( $variations as $variation ) {
			foreach ( $variation['attributes'] as $variation_attribute_name => $variation_attribute_value ) {
				if ( $attribute_name !== $variation_attribute_name ) {
					continue;
				}

				if ( $attribute_value !== $variation_attribute_value && ! empty( $variation_attribute_value ) ) {
					continue;
				}

				$variation_ids[ $id ] = $variation['variation_id'];
				break;
			}

			if ( $variation_ids[ $id ] ) {
				break;
			}
		}

		return $variation_ids[ $id ];
	}

	/**
	 * Modify Dropdown variation attribute options args
	 *
	 * Some themes add the label into the dropdown,
	 * let's remove it!
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function dropdown_variation_attribute_options_args( $args ) {
		$args['show_option_none'] = __( 'Choose an option', 'woocommerce' );

		return $args;
	}

	/**
	 * Get swatch meta for product.
	 *
	 * @param int $product_id
	 *
	 * @return mixed
	 */
	public function get_swatch_meta( $product_id ) {
		$swatch_meta = get_post_meta( $product_id, '_iconic-was', true );

		return apply_filters( 'iconic_was_swatch_meta', $swatch_meta, $product_id );
	}

	/**
	 * Has product-specific swatch meta.
	 *
	 * @param $product_id
	 * @param $attribute
	 *
	 * @return bool
	 */
	public function has_swatch_meta( $product_id, $attribute ) {
		$swatch_meta = $this->get_swatch_meta( $product_id );

		if ( ! $swatch_meta ) {
			return false;
		}

		if ( empty( $swatch_meta[ $attribute ] ) || empty( $swatch_meta[ $attribute ]['swatch_type'] ) ) {
			return false;
		}

		return true;
	}
}