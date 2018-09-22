<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Attibutes
 *
 * This class is for anything attribute related
 *
 * @class          Iconic_WAS_Attributes
 * @version        1.0.0
 * @category       Class
 * @author         Iconic
 */
class Iconic_WAS_Attributes {
	/**
	 * Attribute meta name for fields
	 *
	 * @var str $attribute_meta_name
	 */
	public $attribute_meta_name = 'iconic_was_attribute_meta';

	/**
	 * Attribute term meta name for fields
	 *
	 * @var str $attribute_term_meta_name
	 */
	public $attribute_term_meta_name = 'iconic_was_term_meta';

	/**
	 * Run actions/filters for this class
	 */
	public function run() {
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'add_attribute_term_fields' ), 10 );

			add_action( 'woocommerce_attribute_added', array( $this, 'add_attribute_fields' ), 10, 2 );
			add_action( 'woocommerce_attribute_updated', array( $this, 'update_attribute_fields' ), 10, 3 );
			add_action( 'iconic_was_attribute_updated', array( $this, 'update_term_groups' ), 10, 2 );

			add_action( 'wp_ajax_iconic_was_get_attribute_fields', array( $this, 'ajax_get_attribute_fields' ) );
		}

		add_filter( 'woocommerce_attribute_label', array( $this, 'modify_attribute_label' ), 100, 3 );
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array(
			$this,
			'modify_attribute_html',
		), 10, 2 );
		add_filter( 'wc_dropdown_variation_attribute_options_html', array( $this, 'modify_attribute_html' ), 10, 2 );
		add_filter( 'woocommerce_layered_nav_term_html', array( $this, 'modify_layered_nav_term_html' ), 10, 4 );
	}

	/**
	 * Admin: Add attribute term fields
	 */
	public function add_attribute_term_fields() {
		$attributes = wc_get_attribute_taxonomies();

		if ( ! $attributes ) {
			return;
		}

		foreach ( $attributes as $attribute ) {
			add_action( sprintf( 'pa_%s_add_form_fields', $attribute->attribute_name ), array(
				$this,
				'output_attribute_term_fields',
			), 100, 2 );
			add_action( sprintf( 'pa_%s_edit_form', $attribute->attribute_name ), array(
				$this,
				'output_attribute_term_fields',
			), 100, 2 );

			add_action( sprintf( 'create_pa_%s', $attribute->attribute_name ), array(
				$this,
				'save_attribute_term_fields',
			) );
			add_action( sprintf( 'edited_pa_%s', $attribute->attribute_name ), array(
				$this,
				'save_attribute_term_fields',
			) );

			add_filter( sprintf( 'manage_edit-pa_%s_columns', $attribute->attribute_name ), array(
				$this,
				'add_attribute_columns',
			) );
			add_filter( sprintf( 'manage_pa_%s_custom_column', $attribute->attribute_name ), array(
				$this,
				'add_attribute_column_content',
			), 10, 3 );
		}
	}

	/**
	 * Admin: Add attribute term fields
	 *
	 * @param int $term the concrete term
	 */
	public function output_attribute_term_fields( $term = false ) {
		global $iconic_was;

		if ( empty( $_GET['taxonomy'] ) ) {
			return;
		}

		$swatch_type = $iconic_was->swatches_class()->get_swatch_option( 'swatch_type', $_GET['taxonomy'] );

		if ( empty( $swatch_type ) ) {
			return;
		}

		$field_data_method_name = sprintf( 'get_%s_fields', str_replace( '-', '_', $swatch_type ) );

		if ( ! method_exists( $iconic_was->swatches_class(), $field_data_method_name ) ) {
			return;
		}

		$fields = $iconic_was->swatches_class()->$field_data_method_name( array(
			'term'       => $term,
			'field_name' => sprintf( '%s[%s]', $this->attribute_term_meta_name, $swatch_type ),
		) );

		$is_edit_page = is_object( $term );

		if ( $fields ) {
			if ( $is_edit_page ) {
				printf( '<h3>%s</h3>', __( 'Swatch Options', 'iconic-was' ) );

				echo "<table class='form-table'>";
				echo "<tbody>";

				foreach ( $fields as $field ) {
					echo "<tr class='form-field'>";
					echo sprintf( '<th scope="row">%s</th>', $field['label'] );
					echo "<td>";
					echo $field['field'];
					echo $field['description'];
					echo "</td>";
					echo "</tr>";
				}

				echo "</tbody>";
				echo "</table>";
			} else {
				foreach ( $fields as $field ) {
					echo "<div class='form-field'>";
					echo $field['label'];
					echo $field['field'];
					echo $field['description'];
					echo "</div>";
				}
			}
		}
	}

	/**
	 * Admin: Save fields for product categories
	 *
	 * @param int $term_id ID of the term we are saving
	 */
	public function save_attribute_term_fields( $term_id ) {
		if ( isset( $_POST[ $this->attribute_term_meta_name ] ) ) {
			$previous_termmeta = get_term_meta( $term_id, $this->attribute_term_meta_name, true );
			$previous_termmeta = $previous_termmeta ? $previous_termmeta : array();

			// get value, sanitise, and save it into the database
			$new_termmeta = isset( $_POST[ $this->attribute_term_meta_name ] ) ? $_POST[ $this->attribute_term_meta_name ] : '';

			$termmeta = array_replace( $previous_termmeta, $new_termmeta );

			update_term_meta( $term_id, $this->attribute_term_meta_name, $termmeta );
		}
	}

	/**
	 * Admin: Add attribute fields
	 *
	 * @param int   $attribute_id
	 * @param array $attribute
	 */
	public function add_attribute_fields( $attribute_id, $attribute ) {
		if ( isset( $_POST[ $this->attribute_meta_name ] ) ) {
			$this->update_attribute_option( $attribute_id, $_POST[ $this->attribute_meta_name ] );
		}
	}

	/**
	 * Admin: Update attribute fields
	 *
	 * @param $attribute_id
	 * @param $attribute
	 * @param $old_attribute_name
	 */
	public function update_attribute_fields( $attribute_id, $attribute, $old_attribute_name ) {
		$value = isset( $_POST[ $this->attribute_meta_name ] ) ? $_POST[ $this->attribute_meta_name ] : false;

		if ( ! $value ) {
			return;
		}

		$update = $this->update_attribute_option( $attribute_id, $value );

		if ( $update ) {
			do_action( 'iconic_was_attribute_updated', $attribute_id, $value );
		}
	}

	/**
	 * Update term groups when attribute is updated.
	 *
	 * @param $attribute_id
	 * @param $value
	 */
	public function update_term_groups( $attribute_id, $value ) {
		if ( ! isset( $value['groups'] ) ) {
			return;
		}

		$attribute = wc_get_attribute( $attribute_id );
		$terms     = get_terms( array(
				'taxonomy' => $attribute->slug,
			)
		);

		if ( empty( $terms ) ) {
			return;
		}

		foreach ( $terms as $term ) {
			$meta = $this->get_term_meta( $term );

			if ( empty ( $meta['group'] ) ) {
				continue;
			}

			if ( ! in_array( $meta['group'], $value['groups'] ) ) {
				$meta['group'] = '';
				update_term_meta( $term->term_id, $this->attribute_term_meta_name, $meta );
			}
		}
	}

	/**
	 * Helper: Update attribute option
	 *
	 * @param int   $attribute_id
	 * @param array $value
	 *
	 * @return bool
	 */
	public function update_attribute_option( $attribute_id, $value ) {
		$option_name = $this->get_attribute_option_name( $attribute_id );

		if ( get_option( $option_name ) !== false ) {
			return update_option( $option_name, $value );
		}

		$deprecated = null;
		$autoload   = 'no';

		return add_option( $option_name, $value, $deprecated, $autoload );
	}

	/**
	 * Ajax: Get attribute fields
	 */
	public function ajax_get_attribute_fields() {
		$return = array(
			'success' => true,
			'fields'  => false,
		);

		$attribute_id = ( ! empty( $_POST['attribute_id'] ) && $_POST['attribute_id'] > 0 ) ? (int) $_POST['attribute_id'] : false;

		// swatch type
		$return['fields'] = $this->get_attribute_fields( array(
			'attribute_id' => $attribute_id,
		) );

		wp_send_json( $return );
	}

	/**
	 * Get attribute fields.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_attribute_fields( $args ) {
		$defaults = array(
			'attribute_id'   => false,
			'attribute_slug' => false,
			'product_id'     => false,
		);

		$args = wp_parse_args( $args, $defaults );

		global $iconic_was;

		$fields = array();

		$is_product                    = $args['product_id'];
		$field_name_prefix             = $is_product ? sprintf( 'iconic-was[%s]', $args['attribute_slug'] ) : $this->attribute_meta_name;
		$saved_values                  = $is_product ? $iconic_was->swatches_class()
		                                                          ->get_product_swatch_data( $args['product_id'], $args['attribute_slug'] ) : $this->get_attribute_option_value( $args['attribute_id'] );
		$swatch_type_blank_option_name = $is_product ? __( 'Default', 'iconic-was' ) : __( 'None', 'iconic-was' );

		// swatch type
		$swatch_type_id        = Iconic_WAS_Helpers::strip_brackets( sprintf( '%s-swatch-type', $field_name_prefix ) );
		$saved_swatch_type     = $saved_values && isset( $saved_values['swatch_type'] ) ? $saved_values['swatch_type'] : "";
		$fields['swatch_type'] = array(
			'label'       => __( 'Swatch Type', 'iconic-was' ),
			'description' => __( 'Choose the type of swatches to use for this attribute.', 'iconic-was' ),
			'field'       => $iconic_was->helpers_class()->get_field( array(
				'type'        => 'select',
				'id'          => $swatch_type_id,
				'name'        => sprintf( '%s[swatch_type]', $field_name_prefix ),
				'options'     => $iconic_was->swatches_class()->get_swatch_types( $swatch_type_blank_option_name ),
				'value'       => $saved_swatch_type,
				'conditional' => $swatch_type_id,
			) ),
			'class'       => array(),
			'condition'   => false,
			'match'       => array(),
		);

		/**
		 * Fields for visual swatches
		 */

		$is_visible = $iconic_was->swatches_class()->is_swatch_visual( $saved_swatch_type );

		// swatch shape
		$fields['swatch_shape'] = array(
			'label'       => __( 'Swatch Shape', 'iconic-was' ),
			'description' => __( 'The shape of your swatches on the frontend.', 'iconic-was' ),
			'field'       => $iconic_was->helpers_class()->get_field( array(
				'type'    => 'select',
				'name'    => sprintf( '%s[swatch_shape]', $field_name_prefix ),
				'options' => array( 'round' => __( 'Round', 'iconic-was' ), 'square' => __( 'Square', 'iconic-was' ) ),
				'value'   => $saved_values && isset( $saved_values['swatch_shape'] ) ? $saved_values['swatch_shape'] : "",
			) ),
			'class'       => array( 'iconic-was-visual-swatch' ),
			'condition'   => $swatch_type_id,
			'match'       => array( 'image-swatch', 'colour-swatch' ),
		);

		// swatch size
		$fields['swatch_size'] = array(
			'label'       => __( 'Swatch Size (px)', 'iconic-was' ),
			'description' => __( 'The size of your swatches on the frontend.', 'iconic-was' ),
			'field'       => $iconic_was->helpers_class()->get_field( array(
				'type'  => 'dimensions',
				'name'  => sprintf( '%s[swatch_size]', $field_name_prefix ),
				'value' => ! empty( $saved_values['swatch_size'] )
					? $saved_values['swatch_size']
					: apply_filters( 'iconic_was_default_swatch_size', array( 'width' => 30, 'height' => 30 ) ),
			) ),
			'class'       => array( 'iconic-was-visual-swatch' ),
			'condition'   => $swatch_type_id,
			'match'       => array( 'image-swatch', 'colour-swatch' ),
		);

		// swatch tooltips
		$fields['tooltips'] = array(
			'label'       => __( 'Enable Tooltips?', 'iconic-was' ),
			'description' => __( 'When enabled, a tooltip style text description will accompany your swatches.', 'iconic-was' ),
			'field'       => $iconic_was->helpers_class()->get_field( array(
				'type'    => 'select',
				'name'    => sprintf( '%s[tooltips]', $field_name_prefix ),
				'options' => array( '1' => __( 'Yes', 'iconic-was' ), '0' => __( 'No', 'iconic-was' ) ),
				'value'   => $saved_values && isset( $saved_values['tooltips'] ) ? $saved_values['tooltips'] : "",
			) ),
			'class'       => array( 'iconic-was-visual-swatch' ),
			'condition'   => $swatch_type_id,
			'match'       => array( 'image-swatch', 'colour-swatch' ),
		);

		// swatch tooltips
		$fields['large_preview'] = array(
			'label'       => __( 'Show Large Preview?', 'iconic-was' ),
			'description' => __( 'Display a larger preview of the image swatch within a tooltip.', 'iconic-was' ),
			'field'       => $iconic_was->helpers_class()->get_field( array(
				'type'    => 'select',
				'name'    => sprintf( '%s[large_preview]', $field_name_prefix ),
				'options' => array( '0' => __( 'No', 'iconic-was' ), '1' => __( 'Yes', 'iconic-was' ) ),
				'value'   => $saved_values && isset( $saved_values['large_preview'] ) ? $saved_values['large_preview'] : "",
			) ),
			'class'       => array(),
			'condition'   => $swatch_type_id,
			'match'       => array( 'image-swatch' ),
		);

		// swatch loop
		$swatch_loop_id = Iconic_WAS_Helpers::strip_brackets( sprintf( '%s-loop', $field_name_prefix ) );
		$fields['loop'] = array(
			'label'       => __( 'Show Swatch in Catalog?', 'iconic-was' ),
			'description' => __( 'When enabled, available swatches will be displayed in the catalog listing for each product.', 'iconic-was' ),
			'field'       => $iconic_was->helpers_class()->get_field( array(
				'type'        => 'select',
				'id'          => $swatch_loop_id,
				'name'        => sprintf( '%s[loop]', $field_name_prefix ),
				'options'     => array( '0' => __( 'No', 'iconic-was' ), '1' => __( 'Yes', 'iconic-was' ) ),
				'value'       => $saved_values && isset( $saved_values['loop'] ) ? $saved_values['loop'] : "",
				'conditional' => $swatch_loop_id,
			) ),
			'class'       => array( 'iconic-was-u-hide' ),
			'condition'   => $swatch_type_id,
			'match'       => array( 'image-swatch', 'colour-swatch', 'text-swatch', 'radio-buttons' ),
		);

		$fields['loop-method'] = array(
			'label'       => __( 'Catalog Swatch Method', 'iconic-was' ),
			'description' => __( 'What should happen when a user clicks on the swatch in the catalog.', 'iconic-was' ),
			'field'       => $iconic_was->helpers_class()->get_field( array(
				'type'    => 'select',
				'name'    => sprintf( '%s[loop-method]', $field_name_prefix ),
				'options' => array(
					'link'  => __( 'Link to Product', 'iconic-was' ),
					'image' => __( 'Change Product Image', 'iconic-was' ),
				),
				'value'   => $saved_values && isset( $saved_values['loop-method'] ) ? $saved_values['loop-method'] : "link",
			) ),
			'class'       => array( 'iconic-was-u-hide' ),
			'condition'   => array( $swatch_type_id, $swatch_loop_id ),
			'match'       => array( array( 'image-swatch', 'colour-swatch', 'text-swatch', 'radio-buttons' ), '1' ),
		);

		if ( ! $is_product ) {
			$fields['groups'] = array(
				'label'       => __( 'Groups', 'iconic-was' ),
				'description' => __( 'Enter group labels into the field and press enter or select from the dropdown.', 'iconic-was' ),
				'field'       => $iconic_was->helpers_class()->get_field( array(
					'type'         => 'groups',
					'attribute_id' => $args['attribute_id'],
					'value'        => $saved_values && isset( $saved_values['groups'] ) ? $saved_values['groups'] : array(),
				) ),
				'class'       => array( 'iconic-was-u-hide' ),
				'condition'   => array( $swatch_type_id ),
				'match'       => array( array( 'image-swatch', 'colour-swatch', 'text-swatch', 'radio-buttons' ) ),
			);
		}

		if ( ! empty( $saved_swatch_type ) ) {
			$fields['loop']['class']        = $iconic_was->helpers_class()
			                                             ->unset_item( 'iconic-was-u-hide', $fields['loop']['class'] );
			$fields['loop-method']['class'] = $iconic_was->helpers_class()
			                                             ->unset_item( 'iconic-was-u-hide', $fields['loop-method']['class'] );
		}

		if ( ! $is_visible ) {
			$fields['swatch_shape']['class'][] = 'iconic-was-u-hide';
			$fields['swatch_size']['class'][]  = 'iconic-was-u-hide';
			$fields['tooltips']['class'][]     = 'iconic-was-u-hide';
		}

		if ( $saved_swatch_type !== "image-swatch" ) {
			$fields['large_preview']['class'][] = 'iconic-was-u-hide';
		}

		return $fields;
	}

	/**
	 * Helper: Get attribute option name
	 *
	 * @param int $attribute_id
	 *
	 * @return string
	 */
	public function get_attribute_option_name( $attribute_id ) {
		return sprintf( '%s_%d', $this->attribute_meta_name, $attribute_id );
	}

	/**
	 * Helper: Get attribute option values
	 *
	 * @param int $attribute_id
	 *
	 * @return array|bool
	 */
	public function get_attribute_option_value( $attribute_id ) {
		if ( empty( $attribute_id ) ) {
			return false;
		}

		static $attribute_option_values = array();

		if ( isset( $attribute_option_values[ $attribute_id ] ) ) {
			return $attribute_option_values[ $attribute_id ];
		}

		if ( $attribute_id ) {
			$attribute_option_values[ $attribute_id ] = get_option( $this->get_attribute_option_name( $attribute_id ) );
		}

		return $attribute_option_values[ $attribute_id ];
	}

	/**
	 * Helper: Get option name by term ID
	 *
	 * @param string $attribute
	 * @param int    $term_id
	 *
	 * @return string
	 */

	public function get_attribute_term_option_name( $attribute, $term_id ) {
		return sprintf( "pa_%s_%s", $attribute, $term_id );
	}

	/**
	 * Modify attribute HTML on frontend
	 *
	 * @param string $html
	 * @param array  $args
	 *
	 * @return string
	 */
	public function modify_attribute_html( $html, $args ) {
		global $product, $iconic_was;

		if ( empty( $args['options'] ) ) {
			return $html;
		}

		$_product                = isset( $args['product'] ) ? $args['product'] : $product;
		$product_id              = $_product->get_id();
		$args['attribute']       = $this->format_attribute_slug( $args['attribute'] );
		$attribute_name          = $this->format_attribute_slug( $args['attribute'], true );
		$swatch_type             = $iconic_was->swatches_class()
		                                      ->get_swatch_option( 'swatch_type', $args['attribute'] );
		$swatch_shape            = $iconic_was->swatches_class()
		                                      ->get_swatch_option( 'swatch_shape', $args['attribute'] );
		$tooltips                = (bool) $iconic_was->swatches_class()
		                                             ->get_swatch_option( 'tooltips', $args['attribute'] );
		$large_preview           = (bool) $iconic_was->swatches_class()
		                                             ->get_swatch_option( 'large_preview', $args['attribute'] );
		$has_product_swatch_meta = $iconic_was->products_class()->has_swatch_meta( $product_id, $args['attribute'] );

		if ( empty( $swatch_type ) ) {
			return $html;
		}

		$visual   = $iconic_was->swatches_class()
		                       ->is_swatch_visual( $swatch_type ) ? "iconic-was-swatches--visual" : false;
		$tooltips = $visual && ( $tooltips || $large_preview ) ? "iconic-was-swatches--tooltips" : false;
		$shape    = $visual && $swatch_shape == "round" ? "iconic-was-swatches--round" : "iconic-was-swatches--square";
		$style    = $iconic_was->settings['style_general_selected'];

		$swatches_list_html = sprintf( '<ul class="iconic-was-swatches iconic-was-swatches--%s iconic-was-swatches--%s %s %s %s" data-attribute="%s">', $style, $swatch_type, $visual, $tooltips, $shape, $attribute_name );

		$swatch_data = $iconic_was->swatches_class()->get_swatch_data( array(
			'product_id'     => $_product->get_id(),
			'attribute_slug' => $args['attribute'],
		) );

		$args['options'] = $this->sort_attribute_terms( $_product->get_id(), $args['attribute'], $args['options'] );

		foreach ( $args['options'] as $label => $options ) {
			if ( $label !== 'iconic-was-default' && ! $has_product_swatch_meta ) {
				$label_item         = sprintf( '<li class="iconic-was-swatches__label">%s</li>', $label );
				$swatches_list_html .= apply_filters( 'iconic_was_swatch_group_label', $label_item, $args, $_product, $label );
			}

			foreach ( $options as $option ) {
				$option_sanitized = sanitize_title( $option );

				$option_data = isset( $swatch_data['values'][ $option_sanitized ] ) ? $swatch_data['values'][ $option_sanitized ] : false;

				if ( ! $option_data ) {
					continue;
				}

				$swatch_html = $iconic_was->swatches_class()->get_swatch_html( $swatch_data, $option );
				$selected    = $args['selected'] == $option ? "iconic-was-swatch--selected" : "";

				$swatch_item_html = sprintf( '<li class="iconic-was-swatches__item"><a href="javascript: void(0);" data-attribute-value="%s" data-attribute-value-name="%s" class="iconic-was-swatch iconic-was-swatch--%s %s">%s</a></li>', esc_attr( $option ), esc_attr( $option_data['label'] ), esc_attr( $swatch_data['swatch_type'] ), $selected, $swatch_html );

				$swatches_list_html .= apply_filters( 'iconic_was_swatch_item_html', $swatch_item_html, $args, $swatch_data, $swatch_html, $option );
			}
		}

		$swatches_list_html .= '</ul>';

		$swatches_list_html = apply_filters( 'iconic_was_swatches_html', $swatches_list_html, $args, $swatch_data );
		$swatches_list_html .= sprintf( '<div style="display: none;">%s</div>', $html );

		return apply_filters( 'iconic_was_variation_attribute_options_html', $swatches_list_html, $args, $swatch_data );
	}

	/**
	 * Helper: Sort attribute terms
	 *
	 * @since 1.0.1
	 *
	 * @param int    $product_id
	 * @param string $attribute
	 * @param array  $options
	 *
	 * @return array
	 */
	public function sort_attribute_terms( $product_id, $attribute, $options ) {
		static $options_sorted = array();

		$key = sprintf( '%s_%s', $product_id, $attribute );

		if ( isset( $options_sorted[ $key ] ) ) {
			return $options_sorted[ $key ];
		}

		$default_key = 'iconic-was-default';
		$terms       = wc_get_product_terms( $product_id, $attribute, array( 'fields' => 'all' ) );

		$options_sorted[ $key ]                 = array();
		$options_sorted[ $key ][ $default_key ] = $options;

		if ( ! $terms ) {
			return $options_sorted[ $key ];
		}

		foreach ( $terms as $term ) {
			if ( in_array( $term->slug, $options ) ) {
				$group = Iconic_WAS_Swatches::get_swatch_value( 'taxonomy', 'group', $term );
				$group = $group ? $group : $default_key;

				$options_sorted[ $key ][ $group ][]     = $term->slug;
				$options_sorted[ $key ][ $default_key ] = Iconic_WAS_Helpers::remove_array_item_by_value( $options_sorted[ $key ][ $default_key ], $term->slug );
			}
		}

		return array_filter( $options_sorted[ $key ] );
	}

	/**
	 * Modify attribute label on frontend
	 *
	 * @param str $label
	 * @param str $name
	 * @param obj $product
	 *
	 * @return str
	 */
	public function modify_attribute_label( $label, $name, $product ) {
		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : false;

		if ( ! is_product() && $action !== "jckqv" ) {
			return $label;
		}

		return sprintf( '<strong>%s</strong>: <span class="iconic-was-chosen-attribute"></span>', $label );
	}

	/**
	 * Helper: Get an attribute ID by name.
	 *
	 * @param string $attribute_slug
	 *
	 * @return string|bool
	 */
	public function get_attribute_id_by_slug( $attribute_slug ) {
		global $wpdb;

		static $ids = array();

		$attribute_slug = str_replace( 'pa_', '', $attribute_slug );

		if ( isset( $ids[ $attribute_slug ] ) ) {
			return $ids[ $attribute_slug ];
		}

		$attribute_id = $wpdb->get_var( $wpdb->prepare( "
            SELECT attribute_id
            FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
            WHERE attribute_name = %s
        ", $attribute_slug ) );

		if ( ! $attribute_id || is_wp_error( $attribute_id ) ) {
			$attribute_id = false;
		}

		$ids[ $attribute_slug ] = $attribute_id;

		return $ids[ $attribute_slug ];
	}

	/**
	 * Admin: Add column to attribute list
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_attribute_columns( $columns ) {
		global $iconic_was;

		if ( ! isset( $_GET['taxonomy'] ) ) {
			return $columns;
		}

		$swatch_type = $iconic_was->swatches_class()->get_swatch_option( 'swatch_type', $_GET['taxonomy'] );

		if ( $iconic_was->swatches_class()->is_swatch_visual( $swatch_type ) ) {
			$columns['iconic-was-swatch'] = __( 'Swatch', 'iconic-was' );
		}

		$groups = $this->get_groups( $_GET['taxonomy'] );

		if ( $groups ) {
			$columns['iconic-was-group'] = __( 'Group', 'iconic-was' );
		}

		return $columns;
	}

	/**
	 * Admin: Add content to attribute columns
	 *
	 * @param str $content
	 * @param str $column_name
	 * @param int $term_id
	 *
	 * @return str
	 */
	public function add_attribute_column_content( $content, $column_name, $term_id ) {
		global $iconic_was;

		if ( ! isset( $_GET['taxonomy'] ) ) {
			return $content;
		}

		$swatch_data    = $iconic_was->swatches_class()->get_attribute_swatch_data( $_GET['taxonomy'] );
		$attribute_term = get_term( $term_id, $_GET['taxonomy'] );
		$swatch_html    = $attribute_term ? $iconic_was->swatches_class()
		                                               ->get_swatch_html( $swatch_data, $attribute_term->slug ) : "";

		switch ( $column_name ) {
			case 'iconic-was-swatch':
				$content = $swatch_html;
				break;

			case 'iconic-was-group':
				$term    = get_term( $term_id );
				$content = Iconic_WAS_Swatches::get_swatch_value( 'taxonomy', 'group', $term );
				break;

			default:
				break;
		}

		return $content;
	}

	/**
	 * Helper: Get variation attributes for product
	 *
	 * @param int $product_id
	 *
	 * @return bool|array
	 */
	public function get_variation_attributes_for_product( $product_id ) {
		global $iconic_was;

		$attributes           = maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );
		$variation_attributes = array();

		if ( ! $attributes ) {
			return false;
		}

		foreach ( $attributes as $attribute ) {
			if ( ! $attribute['is_variation'] ) {
				continue;
			}

			$attribute['options'] = array();
			$attribute['slug']    = $this->format_attribute_slug( $attribute['name'] );

			if ( $attribute['is_taxonomy'] ) {
				$attribute['slug'] = $attribute['name'];

				$options          = wp_get_post_terms( $product_id, $attribute['name'] );
				$attribute_object = get_taxonomy( $attribute['name'] );

				$attribute['label'] = $attribute_object->label;

				if ( $options ) {
					foreach ( $options as $option ) {
						$attribute['options'][] = array(
							'id'   => $option->term_id,
							'slug' => $option->slug,
							'name' => $option->name,
							'term' => $option,
						);
					}
				}
			} else {
				$attribute['label'] = $attribute['name'];

				if ( ! empty( $attribute['value'] ) ) {
					$options = explode( ' | ', $attribute['value'] );

					foreach ( $options as $index => $option ) {
						$attribute['options'][] = array(
							'id'   => $index,
							'slug' => sanitize_title( $option ),
							'name' => $option,
							'term' => false,
						);
					}
				}
			}

			$variation_attributes[ $attribute['slug'] ] = $attribute;
		}

		return $variation_attributes;
	}

	/**
	 * Helper: Get non taxonomy attribute value slug
	 *
	 * @param int $index
	 * @param str $value
	 *
	 * @return str
	 */
	public function get_variation_attribute_value_slug( $index, $value ) {
		$attribute_slug = $this->format_attribute_slug( $value );

		return sprintf( '%s_%d', $attribute_slug, $index );
	}

	/**
	 * Helper: format attribute slug
	 *
	 * @param str  $attribute_slug
	 * @param bool $prefix
	 *
	 * @return str
	 */
	public function format_attribute_slug( $attribute_slug, $prefix = false ) {
		if ( ( ! $this->is_taxonomy( $attribute_slug ) || $prefix ) && strpos( $attribute_slug, 'attribute_' ) === false ) {
			$attribute_slug = 'attribute_' . sanitize_title( $attribute_slug );
		}

		return $attribute_slug;
	}

	/**
	 * Helper: Is attribute a taxonomy?
	 *
	 * @param str $attribute_slug
	 *
	 * @return bool
	 */
	public function is_taxonomy( $attribute_slug ) {
		return substr( $attribute_slug, 0, 3 ) === "pa_";
	}

	/**
	 * Get term meta.
	 *
	 * This method allows plugins to hook in before
	 * the get_term_meta call. Useful for WPML.
	 *
	 * @param WP_Term $term
	 *
	 * @return mixed
	 */
	public function get_term_meta( $term ) {
		$term_meta = apply_filters( 'iconic_was_get_term_meta', false, $term );

		if ( ! empty( $term_meta ) ) {
			return $term_meta;
		}

		return get_term_meta( $term->term_id, $this->attribute_term_meta_name, true );
	}

	/**
	 * Get terms.
	 *
	 * This method allows plugins to hook in before
	 * the get_terms call. Useful for WPML.
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function get_terms( $args ) {
		$terms = apply_filters( 'iconic_was_get_terms', false, $args );

		if ( ! empty( $terms ) ) {
			return $terms;
		}

		return get_terms( $args );
	}

	/**
	 * Modify layered nav term HTML.
	 *
	 * @param $term_html
	 * @param $term
	 * @param $link
	 * @param $count
	 *
	 * @return mixed
	 */
	public function modify_layered_nav_term_html( $term_html, $term, $link, $count ) {
		return $term_html;
	}

	/**
	 * Get terms from attribute ID.
	 *
	 * @param int $attribute_id
	 *
	 * @return array
	 */
	public static function get_terms_from_attribute_id( $attribute_id ) {
		static $terms = array();

		if ( isset( $terms[ $attribute_id ] ) ) {
			return $terms[ $attribute_id ];
		}

		$terms[ $attribute_id ] = array();
		$taxonomy               = wc_attribute_taxonomy_name_by_id( $attribute_id );

		if ( ! $taxonomy ) {
			return $terms[ $attribute_id ];
		}

		$get_terms = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		) );

		if ( ! $get_terms ) {
			return $terms[ $attribute_id ];
		}

		foreach ( $get_terms as $term ) {
			$terms[ $attribute_id ][ $term->term_id ] = $term->name;
		}

		return $terms[ $attribute_id ];
	}

	/**
	 * Get groups.
	 *
	 * @param int|string $attribute
	 *
	 * @return bool|array
	 */
	public function get_groups( $attribute ) {
		if ( ! is_numeric( $attribute ) ) {
			$attribute = wc_attribute_taxonomy_id_by_name( $attribute );
		}

		if ( ! $attribute ) {
			return false;
		}

		$attribute_data = $this->get_attribute_option_value( $attribute );

		return ! empty( $attribute_data['groups'] ) ? $attribute_data['groups'] : false;
	}
}