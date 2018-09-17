<?php
/**
 * Plugin Name: WooCommerce Custom Fields for Variations by Iconic
 * Plugin URI: https://iconicwp.com
 * Description: Add custom fields to your product variaitons
 * Version: 1.2.0
 * Author: Iconic
 * Author URI: https://iconicwp.com
 * Text Domain: iconic-cffv
 * WC requires at least: 2.6.14
 * WC tested up to: 3.4.5
 */

if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

class Iconic_CFFV {
	/**
	 * Class prefix
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var string $class_prefix
	 */
	protected $class_prefix = "Iconic_CFFV_";

	/**
	 * Version
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var string $version
	 */
	protected $version = "1.2.0";

	/**
	 * Update version.
	 *
	 * @since  1.1.1
	 * @access protected
	 * @var string $update_version
	 */
	protected static $update_version = "1.0.0";

	/**
	 * Post Types
	 *
	 * @since  1.0.0
	 * @access public
	 * @var Iconic_Helper_Post_Types $post_types
	 */
	public $post_types;

	/**
	 * Meta boxes
	 *
	 * @since  1.0.0
	 * @access public
	 * @var Iconic_Helper_Meta_Boxes $meta_boxes
	 */
	public $meta_boxes;

	/**
	 * Variation field groups post type name
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var Iconic_Helper_Post_Types $post_types
	 */
	protected $field_groups_post_type_name = "cpt_variation_fields";

	/**
	 * Construct
	 */
	public function __construct() {
		$this->textdomain();
		$this->define_constants();;
		$this->load_classes();

		if ( ! Iconic_CFFV_Core_Licence::has_valid_licence() ) {
			return;
		}

		add_action( 'init', array( $this, 'initiate_hook' ) );
		add_action( 'plugins_loaded', array( $this, 'update' ) );
	}

	/**
	 * Load textdomain
	 */
	public function textdomain() {
		load_plugin_textdomain( 'iconic-cffv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Load classes
	 */
	private function load_classes() {
		require_once( ICONIC_CFFV_INC_PATH . 'class-core-autoloader.php' );

		Iconic_CFFV_Core_Autoloader::run( array(
			'prefix'   => 'Iconic_CFFV_',
			'inc_path' => ICONIC_CFFV_INC_PATH,
		) );

		Iconic_CFFV_Core_Licence::run( array(
			'basename' => ICONIC_CFFV_BASENAME,
			'urls'     => array(
				'product'  => 'https://iconicwp.com/products/woocommerce-custom-fields-variations/',
				'settings' => admin_url( 'admin.php?page=iconic-cffv-settings' ),
				'account'  => admin_url( 'admin.php?page=iconic-cffv-settings-account' ),
			),
			'paths'    => array(
				'inc'    => ICONIC_CFFV_INC_PATH,
				'plugin' => ICONIC_CFFV_PATH,
			),
			'freemius' => array(
				'id'         => '1040',
				'slug'       => 'iconic-woo-custom-fields-for-variations',
				'public_key' => 'pk_b581bd1b38e6033604046bacc8dc9',
				'menu'       => array(
					'slug' => 'iconic-cffv-settings',
				),
			),
		) );

		Iconic_CFFV_Core_Settings::run( array(
			'vendor_path'   => ICONIC_CFFV_VENDOR_PATH,
			'title'         => 'WooCommerce Custom Fields for Variations',
			'version'       => $this->version,
			'menu_title'    => 'Custom Fields for Variations',
			'settings_path' => ICONIC_CFFV_INC_PATH . 'admin/settings.php',
			'option_group'  => 'iconic_cffv',
			'docs'          => array(
				'collection'      => '/collection/146-woocommerce-custom-fields-for-variations',
				'troubleshooting' => '/collection/146-woocommerce-custom-fields-for-variations',
				'getting-started' => '/category/150-getting-started',
			),
			'cross_sells'   => array(
				'iconic-woo-show-single-variations',
				'iconic-woothumbs',
			),
		) );

		Iconic_CFFV_Settings::run();
	}

	/**
	 * Define Constants.
	 */
	private function define_constants() {
		$this->define( 'ICONIC_CFFV_PATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'ICONIC_CFFV_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'ICONIC_CFFV_INC_PATH', ICONIC_CFFV_PATH . 'inc/' );
		$this->define( 'ICONIC_CFFV_VENDOR_PATH', ICONIC_CFFV_INC_PATH . 'vendor/' );
		$this->define( 'ICONIC_CFFV_BASENAME', plugin_basename( __FILE__ ) );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name
	 * @param string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Init
	 */
	public function initiate_hook() {
		$this->update();
		$this->add_post_types();
		$this->add_meta_boxes();

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

			add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'add_variation_fields' ), 10, 3 );

			add_action( 'save_post', array( $this, 'save_field_data' ), 10, 3 );
			add_action( 'woocommerce_save_product_variation', array( $this, 'save_product_variation' ), 10, 2 );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ) );

			add_filter( 'woocommerce_available_variation', array( $this, 'alter_variation_json' ), 10, 3 );
		}
	}

	/**
	 * Update plugin.
	 */
	public function update() {
		$saved_update_version_name = 'iconic_cffv_udpate_version';

		if ( version_compare( self::$update_version, get_site_option( $saved_update_version_name ), '>' ) ) {
			Iconic_CFFV_Updates::fields();
			update_option( $saved_update_version_name, self::$update_version );
		}
	}

	/**
	 * Add Post Types
	 */
	public function add_post_types() {
		$this->post_types = new Iconic_Helper_Post_Types();

		$this->post_types->add( array(
			'key'                 => $this->field_groups_post_type_name,
			'public'              => false,
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => false,
			'plural'              => __( 'Variation Field Groups', 'iconic-cffv' ),
			'singular'            => __( 'Variation Field Group', 'iconic-cffv' ),
			'show_in_menu'        => 'edit.php?post_type=product',
		) );
	}

	/**
	 * Add meta boxes
	 */
	public function add_meta_boxes() {
		$this->meta_boxes = new Iconic_Helper_Meta_Boxes();

		$this->meta_boxes->add( array(
			'id'       => 'iconic_cffv_fields',
			'title'    => __( 'Fields', 'iconic-cffv' ),
			'screen'   => $this->field_groups_post_type_name,
			'callback' => array( $this, 'meta_create_variation_fields' ),
		) );

		add_action( 'add_meta_boxes', array( $this->meta_boxes, 'run' ) );
	}

	/**
	 * Admin: Save field data
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 */
	public function save_field_data( $post_id, $post, $update ) {
		if ( $this->field_groups_post_type_name != $post->post_type ) {
			return;
		}

		if ( isset( $_REQUEST['iconic-cffv-field-data'] ) ) {
			update_post_meta( $post_id, 'iconic-cffv-field-data', wp_slash( $_REQUEST['iconic-cffv-field-data'] ) );
		}
	}

	/**
	 * Admin: Create variation fields
	 *
	 * The call back for our fields metabox, displaying our field
	 * editor and any previously created fields
	 */
	public function meta_create_variation_fields() {
		include( ICONIC_CFFV_PATH . 'inc/admin/views/fields-editor.php' );
	}

	/**
	 * Admin: Add fields to variation
	 *
	 * @param int     $loop
	 * @param array   $variation_data
	 * @param WP_Post $variation
	 */
	public function add_variation_fields( $loop, $variation_data, $variation ) {
		include( ICONIC_CFFV_PATH . 'inc/admin/views/field-groups.php' );
	}

	/**
	 * Helper: Get variation field groups
	 *
	 * @return array
	 */
	public function get_variation_field_groups() {
		$field_groups = array();

		$args = array(
			'post_type'      => $this->field_groups_post_type_name,
			'posts_per_page' => - 1,
		);

		$field_groups_query = new WP_Query( $args );

		if ( $field_groups_query->have_posts() ) {
			$i = 0;
			while ( $field_groups_query->have_posts() ) {
				$field_groups_query->the_post();

				$title  = get_the_title();
				$fields = Iconic_CFFV::get_variation_field_group_fields( get_the_id() );

				$field_groups[] = array(
					'id'     => get_the_id(),
					'title'  => $title,
					'class'  => apply_filters( 'iconic_cffv_field_group_class', array(
						'iconic-cffv-field-group',
						sprintf( 'iconic-cffv-field-group--%s', sanitize_title_with_dashes( $title ) ),
						$i == 0 ? 'iconic-cffv-field-group--first' : '',
						$i + 1 == $field_groups_query->found_posts ? 'iconic-cffv-field-group--last' : '',
					), get_the_id() ),
					'fields' => $fields,
				);

				$i ++;
			}
		}

		wp_reset_postdata();

		return $field_groups;
	}

	/**
	 * Helper: Get variation field group fields
	 *
	 * @param int $field_group_id
	 *
	 * @return array
	 */
	public static function get_variation_field_group_fields( $field_group_id ) {
		$formatted_fields = array();
		$fields           = get_post_meta( $field_group_id, 'iconic-cffv-field-data', true );

		if ( $fields && ! empty( $fields ) ) {
			foreach ( $fields as $i => $field_json ) {
				$field_json = stripcslashes( $field_json );
				$field_data = json_decode( $field_json, true );

				$formatted_fields[ $field_data['id'] ]['json'] = $field_json;
				$formatted_fields[ $field_data['id'] ]['data'] = $field_data;
			}
		}

		return $formatted_fields;
	}

	/**
	 * Frontend: Styles
	 */
	public function frontend_styles() {
		if ( is_product() ) {
			wp_register_style( 'iconic_cffv_styles', ICONIC_CFFV_URL . 'assets/frontend/css/main.min.css', array(), $this->version );

			wp_enqueue_style( 'iconic_cffv_styles' );
		}
	}

	/**
	 * Frontend: Scripts
	 */
	public function frontend_scripts() {
		if ( is_product() ) {
			wp_register_script( 'iconic_cffv_scripts', ICONIC_CFFV_URL . 'assets/frontend/js/main.min.js', array( 'jquery', 'wp-util' ), $this->version, true );

			wp_enqueue_script( 'iconic_cffv_scripts' );

			$vars = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'iconic-cffv' ),
			);

			wp_localize_script( 'iconic_cffv_scripts', 'iconic_cffv_vars', $vars );

			add_action( 'wp_footer', array( $this, 'output_fields_template' ) );
		}
	}

	/**
	 * Frontend: The template for our fields output
	 */
	public function output_fields_template() {
		?>
		<script type="text/template" id="tmpl-variation-fields">
			<div class="woocommerce-variation-fields">
				{{{ data.variation.variation_fields }}}
			</div>
		</script>
		<?php
	}

	/**
	 * Admin: Styles
	 */
	public function admin_styles() {
		global $pagenow, $post_type;

		if ( ! $this->is_field_group_edit_page() && ! $this->is_product_edit_page() ) {
			return;
		}

		wp_register_style( 'magnific', ICONIC_CFFV_URL . 'assets/vendor/magnific/magnific-popup.css', array(), $this->version );
		wp_register_style( 'iconic_cffv_admin_styles', ICONIC_CFFV_URL . 'assets/admin/css/main.min.css', array(), $this->version );

		wp_enqueue_style( 'magnific' );
		wp_enqueue_style( 'iconic_cffv_admin_styles' );
	}

	/**
	 * Admin: Scripts
	 */
	public function admin_scripts() {
		global $pagenow, $post_type;

		if ( ! $this->is_field_group_edit_page() && ! $this->is_product_edit_page() ) {
			return;
		}

		wp_register_script( 'magnific', ICONIC_CFFV_URL . 'assets/vendor/magnific/jquery.magnific-popup.min.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'iconic_cffv_admin_scripts', ICONIC_CFFV_URL . 'assets/admin/js/main.min.js', array( 'jquery' ), $this->version, true );

		wp_enqueue_script( 'magnific' );
		wp_enqueue_script( 'iconic_cffv_admin_scripts' );
	}

	/**
	 * Helper: Is field group edit page
	 *
	 * @return bool
	 */
	protected function is_field_group_edit_page() {
		global $pagenow, $post_type;

		return ( isset( $pagenow ) && ( $pagenow == "post.php" || $pagenow == "post-new.php" ) ) && ( isset( $post_type ) && $post_type == $this->field_groups_post_type_name );
	}

	/**
	 * Helper: Is product group edit page
	 *
	 * @return bool
	 */
	protected function is_product_edit_page() {
		global $pagenow, $post_type;

		return ( isset( $pagenow ) && ( $pagenow == "post.php" || $pagenow == "post-new.php" ) ) && ( isset( $post_type ) && $post_type == "product" );
	}

	/**
	 * Admin: Save the variation
	 *
	 * @param int $variation_id
	 * @param int $i
	 */
	public function save_product_variation( $variation_id, $i ) {
		if ( isset( $_POST['iconic_cffv'][ $i ] ) ) {
			foreach ( $_POST['iconic_cffv'][ $i ] as $key => $value ) {
				update_post_meta( $variation_id, 'iconic_cffv_' . $key, $value );
			}
		}
	}

	/**
	 * Helper: Output variation field
	 *
	 * @param array                $field_data
	 * @param int                  $loop The variation index
	 * @param WC_Product_Variation $variation
	 * @param array                $field_group
	 */
	public function output_variaition_field( $field_data, $loop, $variation, $field_group ) {
		$field_data['wrapper_class'] = 'form-row form-row-full';
		$field_data['desc_tip']      = true;
		$field_data['name']          = sprintf( 'iconic_cffv[%d][%d_%s]', $loop, $field_group['id'], $field_data['id'] );
		$field_data['options']       = $this->format_field_options( $field_data );
		$field_data['value']         = $this->get_field_value( $field_group['id'], $field_data, $variation );
		$field_data['label']         = sprintf( '%s:', $field_data['label'] );

		if ( $field_data['type'] === "text" ) {
			woocommerce_wp_text_input( $field_data );
		} elseif ( $field_data['type'] === "textarea" ) {
			$this->wp_textarea_input( $field_data );
		} elseif ( $field_data['type'] === "checkboxes" ) {
			$this->wp_checkboxes( $field_data );
		} elseif ( $field_data['type'] === "radio_buttons" ) {
			woocommerce_wp_radio( $field_data );
		} elseif ( $field_data['type'] === "select" ) {
			$field_data['options'] = array_merge( array( '' => __( 'Select an option', 'iconic-cffv' ) ), $field_data['options'] );

			woocommerce_wp_select( $field_data );
		}
	}

	/**
	 * Helper: Output checkboxes/checbox
	 *
	 * @param array $field
	 */
	public function wp_checkboxes( $field ) {
		$options_count  = count( $field['options'] );
		$field['name']  = sprintf( '%s[]', $field['name'] );
		$field['value'] = (array) $field['value'];
		$description    = false;
		$desc_tip       = isset( $field['desc_tip'] ) && false !== $field['desc_tip'];

		if ( ! empty( $field['description'] ) ) {
			if ( $desc_tip ) {
				$description = wc_help_tip( $field['description'] );
			} else {
				$description = '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
			}
		}

		printf( '<fieldset class="form-field %s_field %s"><legend>%s %s</legend><ul class="wc-checkboxes">', esc_attr( $field['id'] ), esc_attr( $field['wrapper_class'] ), wp_kses_post( $field['label'] ), $desc_tip ? $description : "" );

		foreach ( $field['options'] as $value => $label ) {
			printf( '<li><label><input type="checkbox" id="%s" class="checkbox" name="%s" value="%s" %s /> %s</label></li>', $field['id'], $field['name'], $value, in_array( $value, $field['value'] ) ? 'checked="checked"' : "", $label );
		}

		echo "</ul></fieldset>";

		if ( $description ) {
			if ( ! $desc_tip ) {
				echo $description;
			}
		}
	}

	/**
	 * Helper: Output textarea
	 *
	 * @param array $field
	 */
	public function wp_textarea_input( $field ) {
		global $thepostid, $post;

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
		$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

		// Custom attribute handling
		$custom_attributes = array();
		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
			foreach ( $field['custom_attributes'] as $attribute => $value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
			}
		}

		echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><textarea class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '"  name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" rows="2" cols="20" ' . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $field['value'] ) . '</textarea> ';

		if ( ! empty( $field['description'] ) ) {
			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				echo wc_help_tip( $field['description'] );
			} else {
				echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
			}
		}

		echo '</p>';
	}

	/**
	 * Helper: Format field options
	 *
	 * Convert an array with label/value keys to an assoc array
	 *
	 * @param array $field_data
	 *
	 * @return array
	 */
	public function format_field_options( $field_data ) {
		$options           = isset( $field_data['options'] ) ? $field_data['options'] : false;
		$formatted_options = array();

		if ( $options && ! empty( $options ) ) {
			$options = explode( PHP_EOL, $options );

			foreach ( $options as $option ) {
				$option = trim( $option );

				$formatted_options[ $option ] = $option;
			}
		}

		return $formatted_options;
	}

	/**
	 * Helper: Get field value
	 *
	 * @param int     $field_group_id
	 * @param array   $field_data
	 * @param WP_Post $variation
	 *
	 * @return string
	 */
	public function get_field_value( $field_group_id, $field_data, $variation ) {
		$key = sprintf( 'iconic_cffv_%d_%s', $field_group_id, $field_data['id'] );

		return get_post_meta( $variation->ID, $key, true );
	}

	/**
	 * Frontend: Alter variation JSON
	 *
	 * This hooks into the data attribute on the variations form for each variation
	 * we can get the custom field data here!
	 *
	 * @param array $variation_data
	 * @param mixed $wc_product_variable
	 * @param mixed $variation_obj
	 *
	 * @return array
	 */
	public function alter_variation_json( $variation_data, $wc_product_variable, $variation_obj ) {
		$saved_data = Iconic_CFFV_Fields::get_product_fields_data( $variation_obj->get_id() );

		if ( empty( $saved_data ) ) {
			return $variation_data;
		}

		$variation_data['variation_fields'] = '';

		foreach ( $saved_data as $field_id => $field ) {
			if ( empty( $field['value'] ) ) {
				continue;
			}

			if ( ! empty( $field['data']['display_frontend'] ) && $field['data']['display_frontend'] === 'no' ) {
				continue;
			}

			$field['data']['label_position'] = empty( $field['data']['label_position'] ) ? "above" : $field['data']['label_position'];
			$field['data']['label']          = ( $field['data']['display_label'] === "yes" ) ? sprintf( '<strong class="iconic-cffv-field__label iconic-cffv-field__label--%s">%s</strong>', $field['data']['label_position'], $field['data']['label'] ) : false;

			$variation_data['variation_fields'] .= '<div class="iconic-cffv-field">';

			if ( $field['data']['label_position'] === "above" ) {
				$variation_data['variation_fields'] .= $field['data']['label'];
			}

			// @to-do:
			// change template based on field type

			$value = $this->format_field_value( $field['value'], $field['data'] );

			$variation_data['variation_fields'] .= sprintf( '<div class="iconic-cffv-field__content">%s</div>', $value );

			$variation_data['variation_fields'] .= '</div>';
		}

		return $variation_data;
	}

	/**
	 * Helper: Format field value for frontend
	 *
	 * @param string|array $field_value
	 * @param array        $field_data
	 *
	 * @return string
	 */
	public function format_field_value( $field_value, $field_data ) {
		$return      = '';
		$field_label = $field_data['label'] && $field_data['label_position'] === "left" ? sprintf( '%s: ', $field_data['label'] ) : false;

		if ( is_array( $field_value ) ) {
			$field_data['display_as'] = empty( $field_data['display_as'] ) ? "comma_separated" : $field_data['display_as'];

			if ( $field_data['display_as'] == "list" ) {
				$return = "<ul>";

				foreach ( $field_value as $option ) {
					$return .= sprintf( '<li>%s</li>', $option );
				}

				$return .= "</ul>";
			} elseif ( $field_data['display_as'] == "comma_separated" ) {
				$return = sprintf( '<p>%s%s</p>', $field_label, implode( ', ', $field_value ) );
			}
		} else {
			$return = sprintf( '<p>%s%s</p>', $field_label, $field_value );
		}

		return $return;
	}
}

$iconic_cffv = new Iconic_CFFV();