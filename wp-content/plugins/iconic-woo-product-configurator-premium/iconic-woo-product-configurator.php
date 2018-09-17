<?php
/**
 * Plugin Name: WooCommerce Product Configurator by Iconic
 * Plugin URI: https://iconicwp.com/products/woocommerce-product-configurator/
 * Description: Product Configurator plugin for WooCommerce
 * Version: 1.3.2
 * Author: Iconic
 * Author Email: support@iconicwp.com
 * Author URI: https://iconicwp.com/
 * WC requires at least: 2.6.14
 * WC tested up to: 3.4.5
 */

class jckpc {
	/**
	 * Name
	 *
	 * @var $name
	 * @access protected
	 */
	protected $name = 'WooCommerce Product Configurator';

	/**
	 * Slug
	 *
	 * @var $slug
	 * @access protected
	 */
	protected $slug = 'jckpc';

	/**
	 * Version
	 *
	 * @var $version
	 * @access protected
	 */
	public static $version = "1.3.2";

	/**
	 * Uplaods path
	 *
	 * @var $uploads_path
	 * @access protected
	 */
	protected $uploads_path;

	/**
	 * Uploads URL
	 *
	 * @var $uploads_url
	 * @access protected
	 */
	protected $uploads_url;

	/**
	 * Upload Directory
	 *
	 * @var $upload_dir
	 * @access protected
	 */
	protected $upload_dir;

	/**
	 * Notices class
	 *
	 * @since  1.1.4
	 * @access public
	 * @var Iconic_Transient_Notices
	 */
	public $notices;

	/**
	 * Class prefix
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var string $class_prefix
	 */
	protected $class_prefix = "Iconic_PC_";

	/**
	 * Construct
	 */
	public function __construct() {
		$this->upload_dir   = wp_upload_dir();
		$this->uploads_path = $this->upload_dir['basedir'] . '/jckpc-uploads';
		$this->uploads_url  = $this->upload_dir['baseurl'] . '/jckpc-uploads';

		$this->define_constants();
		$this->load_classes();

		if ( ! Iconic_PC_Helpers::is_plugin_active( 'woocommerce/woocommerce.php' ) && ! Iconic_PC_Helpers::is_plugin_active( 'woocommerce-old/woocommerce.php' ) ) {
			return;
		}

		if ( ! Iconic_PC_Core_Licence::has_valid_licence() ) {
			return;
		}

		// Hook up to the init and plugins_loaded actions
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'init', array( $this, 'initiate' ) );
		add_action( 'admin_init', array( $this, 'add_attribute_term_fields' ), 10 );
	}

	/**
	 * Define Constants.
	 */
	private function define_constants() {
		$this->define( 'ICONIC_PC_PATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'ICONIC_PC_INC_PATH', ICONIC_PC_PATH . 'inc/' );
		$this->define( 'ICONIC_PC_VENDOR_PATH', ICONIC_PC_INC_PATH . 'vendor/' );
		$this->define( 'ICONIC_PC_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'ICONIC_PC_BASENAME', plugin_basename( __FILE__ ) );
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
	 * Load classes
	 */
	private function load_classes() {
		require_once( ICONIC_PC_INC_PATH . 'class-core-autoloader.php' );

		Iconic_PC_Core_Autoloader::run( array(
			'prefix'   => 'Iconic_PC_',
			'inc_path' => ICONIC_PC_INC_PATH,
		) );

		Iconic_PC_Core_Licence::run( array(
			'basename' => ICONIC_PC_BASENAME,
			'urls'     => array(
				'product'  => 'https://iconicwp.com/products/woocommerce-product-configurator/',
				'settings' => admin_url( 'admin.php?page=iconic-woo-product-configurator-settings' ),
				'account'  => admin_url( 'admin.php?page=iconic-woo-product-configurator-settings-account' ),
			),
			'paths'    => array(
				'inc'    => ICONIC_PC_INC_PATH,
				'plugin' => ICONIC_PC_PATH,
			),
			'freemius' => array(
				'id'         => '1039',
				'slug'       => 'iconic-woo-product-configurator',
				'public_key' => 'pk_fed17532221f66e11a200b70db56c',
				'menu'       => array(
					'slug' => 'iconic-woo-product-configurator-settings',
				),
			),
		) );

		Iconic_PC_Core_Settings::run( array(
			'vendor_path'   => ICONIC_PC_VENDOR_PATH,
			'title'         => $this->name,
			'version'       => self::$version,
			'menu_title'    => 'Configurator',
			'settings_path' => ICONIC_PC_INC_PATH . 'admin/settings.php',
			'option_group'  => 'iconic_woo_product_configurator',
			'docs'          => array(
				'collection'      => '/collection/126-woocommerce-product-configurator',
				'troubleshooting' => '/category/131-troubleshooting',
				'getting-started' => '/category/129-getting-started',
			),
			'cross_sells'   => array(
				'iconic-woo-attribute-swatches',
				'iconic-woothumbs',
			),
		) );

		$this->settings = Iconic_PC_Core_Settings::$settings;

		if ( ! Iconic_PC_Core_Licence::has_valid_licence() ) {
			return;
		}

		$this->notices = new Iconic_Transient_Notices();
		Iconic_PC_Settings::run();
		Iconic_PC_WPML::run();
		Iconic_PC_Shortcodes::run();
		Iconic_PC_Inventory::run();
		Iconic_PC_Compat_WooThumbs::run();
	}

	/**
	 * Run on plugins_loaded
	 */
	public function plugins_loaded() {
		add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'cart_thumbnail' ), 10, 3 );
	}

	/**
	 * Run on init
	 */
	public function initiate() {
		load_plugin_textdomain( 'jckpc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		$this->create_uploads_folder();

		if ( is_admin() ) {
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'product_config_tab' ) );
			add_action( Iconic_PC_Deprecation::get_hook( 'woocommerce_product_data_panels' ), array(
				$this,
				'product_config_tab_options',
			) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'process_meta_product_config_tab' ), 10, 2 );
			add_action( 'wp_ajax_jckpc_generate_image', array( $this, 'generate_image' ) );
			add_action( 'wp_ajax_nopriv_jckpc_generate_image', array( $this, 'generate_image' ) );
			add_action( 'wp_ajax_jckpc_get_composite_img_url', array( $this, 'get_composite_img_url' ) );
			add_action( 'wp_ajax_nopriv_jckpc_get_composite_img_url', array( $this, 'get_composite_img_url' ) );
			add_action( 'wp_ajax_jckpc_get_image_layer', array( $this, 'get_image_layer' ) );
			add_action( 'wp_ajax_nopriv_jckpc_get_image_layer', array( $this, 'get_image_layer' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts_and_styles' ) );
		} else {
			add_action( 'woocommerce_before_single_product', array( $this, 'setup_configurator_image' ), 20 );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_and_styles' ) );
			add_action( 'woocommerce_order_item_class', array( $this, 'order_item_class' ), 20, 3 );
			add_filter( 'woocommerce_order_item_thumbnail', array( $this, 'order_item_thumbnail' ), 10, 2 );
			add_filter( 'woocommerce_email_order_items_args', array( $this, 'email_order_items_args' ), 10, 1 );
		}
	}

	/**
	 * Create uplaods folder
	 */
	private function create_uploads_folder() {
		if ( file_exists( $this->uploads_path ) ) {
			return;
		}

		mkdir( $this->uploads_path, 0775, true );
	}

	/**
	 * Add default image to attribute column
	 *
	 * @param string $content
	 * @param string $column_name
	 * @param int    $term_id
	 *
	 * @return string
	 */
	public function attribute_column_content( $content, $column_name, $term_id ) {
		switch ( $column_name ) {
			case 'jckpc_default_img':

				$defaultImgId  = self::get_default_image( $term_id );
				$defaultImgSrc = wp_get_attachment_image_src( $defaultImgId, 'thumbnail' );
				$defaultImgSrc = ( $defaultImgId != "" ) ? $defaultImgSrc[0] : wc_placeholder_img_src();
				$content       = '<div style="padding: 2px; background: #fff; border: 1px solid #ccc; float: left; margin: 0 5px 5px 0;"><img src="' . $defaultImgSrc . '" style="width:34px; height: auto; display: block;"></div>';
				break;

			default:
				break;
		}

		return $content;
	}

	/**
	 * Add attribute column
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_attribute_column( $columns ) {
		$columns = array( 'jckpc_default_img' => 'Configurator' ) + $columns;

		return $columns;
	}

	/**
	 * Setup configurator image
	 */
	public function setup_configurator_image() {
		$configurator_enabled = Iconic_PC_Product::is_configurator_enabled();

		if ( ! $configurator_enabled ) {
			return;
		}

		$this->remove_hooks();
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'display_product_image' ), 20 );
	}

	/**
	 * Remove hooks from single product page
	 */
	public function remove_hooks() {
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

		// Remove images from Bazar theme
		if ( class_exists( 'YITH_WCMG' ) ) {
			$this->remove_filters_for_anonymous_class( 'woocommerce_before_single_product_summary', 'YITH_WCMG_Frontend', 'show_product_images', 20 );
			$this->remove_filters_for_anonymous_class( 'woocommerce_product_thumbnails', 'YITH_WCMG_Frontend', 'show_product_thumbnails', 20 );
		}
	}

	/**
	 * Check if configurator is allowed for product
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	public function configurator_allowed( $product_id ) {
		$product = wc_get_product( $product_id );

		if ( $product->is_type( 'variable' ) ) {
			$prodAtts = $product->get_variation_attributes();

			if ( is_array( $prodAtts ) && ! empty( $prodAtts ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add configurator tab to product meta
	 *
	 * @param arr $tabs
	 *
	 * @return arr
	 */
	public function product_config_tab( $tabs ) {
		global $post;

		if ( ! $post ) {
			return;
		}

		echo '<li class="jckpc_options_tab"><a href="#jckpc_options"><span>' . __( 'Configurator', 'jckpc' ) . '</span></a></li>';
	}

	/**
	 * Get sort order
	 *
	 * @param int $post_id
	 *
	 * @return arr
	 */
	public static function get_sort_order( $post_id = false ) {
		if ( ! $post_id ) {
			return false;
		}

		$sort_order = get_post_meta( $post_id, 'jckpc_sort_order', true );

		return array(
			'string' => $sort_order,
			'array'  => explode( ',', $sort_order ),
		);
	}

	/**
	 * Configurator tab contents
	 */
	public function product_config_tab_options() {
		global $post;

		if ( ! $post ) {
			return;
		}

		echo '<div id="jckpc_options" class="panel woocommerce_options_panel wc-metaboxes-wrapper hidden">';

		if ( ! $this->configurator_allowed( $post->ID ) ) {
			?>

			<div class="inline jckpc-notice notice woocommerce-message">
				<p><?php _e( "Before you can manage the configurator layers you need to add some variations on the <strong>Variations</strong> tab. Once you're done, refresh the page.", 'jckpc' ); ?></p>
				<p>
					<a class="button-primary" href="<?php echo esc_url( apply_filters( 'woocommerce_docs_url', 'https://docs.woocommerce.com/document/variable-product/', 'product-variations' ) ); ?>" target="_blank"><?php _e( 'Learn more', 'woocommerce' ); ?></a>
				</p>
			</div>

			<?php
		} else {
			$setImages  = get_post_meta( $post->ID, 'jckpc_images', true );
			$defaults   = get_post_meta( $post->ID, 'jckpc_defaults', true );
			$sort_order = self::get_sort_order( $post->ID );

			include( 'inc/partials/meta-toolbar.php' );

			$atts          = Iconic_PC_Product::get_attributes( $post->ID );
			$static_layers = $this->get_static_layers( $setImages );

			$layers = array_merge( $atts, $static_layers );

			$layers = ( ! empty( $sort_order['array'] ) ) ? $this->sortArrayByArray( $layers, $sort_order['array'] ) : $layers;

			if ( $layers && is_array( $layers ) ):

				echo '<input type="hidden" name="jckpc_sort_order" id="jckpc_sort_order" value="' . $sort_order['string'] . '">';

				echo '<div id="jckpc_sortable">';

				foreach ( $layers as $layer_id => $layer_data ):

					if ( isset( $layer_data['type'] ) && $layer_data['type'] === "static" ) {
						$this->get_static_layer_template( $layer_id, $layer_data );
					} else {
						include( 'inc/partials/meta-attribute-layer.php' );
					}

				endforeach;

				echo '</div>';

			endif;

			echo '<div class="jckpc-layer-options jckpc-layer-options--no-sort options_group custom_tab_options">';

			echo '<h2 class="jckpc-layer-options__title">' . __( 'Background Image', 'jckpc' ) . '</h2>';

			echo '<div class="jckpc-layer-options__content-wrapper">';

			echo '<table class="widefat fixed">';

			echo '<thead>';
			echo '<tr>';
			echo '<th>' . __( 'Image', 'jckpc' ) . '</th>';
			echo '</tr>';
			echo '</thead>';

			$fieldName       = 'jckpc_images[background]';
			$fieldId         = 'jckpc_background_image';
			$selectedImageId = ( isset( $setImages['background'] ) ) ? $setImages['background'] : '';
			$popupTitle      = __( 'Set background image', 'jckpc' );
			$popupBtnTxt     = __( 'Set Image', 'jckpc' );
			$btnText         = __( 'Add Image', 'jckpc' );

			echo $this->image_upload_row( array(
				'field_name'        => $fieldName,
				'field_id'          => $fieldId,
				'selected_image_id' => $selectedImageId,
				'popup_title'       => $popupTitle,
				'popup_button_text' => $popupBtnTxt,
				'button_text'       => $btnText,
				'classes'           => array( 'alternate' ),
			) );

			echo '</table>';

			echo '</div>';

			echo '</div>';

			$this->get_static_layer_template();
		}

		echo '</div>';
	}

	/**
	 * Get static layer template
	 *
	 * @param arr $layer_data
	 */
	public static function get_static_layer_template( $layer_id = false, $layer_data = false ) {
		$blank = ! $layer_id;

		include( 'inc/partials/meta-static-layer.php' );
	}

	/**
	 * Get static layers
	 *
	 * @param arr $set_images
	 *
	 * @return arr
	 */
	public function get_static_layers( $set_images = null ) {
		if ( empty( $set_images ) ) {
			return array();
		}

		$static_layers = array();

		foreach ( $set_images as $layer_id => $image_id ) {
			if ( strpos( $layer_id, 'jckpc-static-' ) === false ) {
				continue;
			}

			$index = absint( str_replace( 'jckpc-static-', '', $layer_id ) );

			$static_layers[ $layer_id ] = array(
				'type'     => 'static',
				'image_id' => $image_id,
				'index'    => $index,
			);
		}

		return $static_layers;
	}

	/**
	 * Save configurator tab
	 */
	function process_meta_product_config_tab( $post_id ) {
		if ( $this->configurator_allowed( $post_id ) ) {
			$enabled = filter_input( INPUT_POST, 'jckpc_enabled', FILTER_SANITIZE_STRING );
			update_post_meta( $post_id, 'jckpc_enabled', $enabled );

			if ( isset( $_POST['jckpc_sort_order'] ) ) {
				update_post_meta( $post_id, 'jckpc_sort_order', $_POST['jckpc_sort_order'] );
			}

			$images = $this->validate_image_layers();
			update_post_meta( $post_id, 'jckpc_images', $images );

			$defaults = ( isset( $_POST['jckpc_defaults'] ) && is_array( $_POST['jckpc_defaults'] ) ) ? $_POST['jckpc_defaults'] : array();
			update_post_meta( $post_id, 'jckpc_defaults', $defaults );
		}
	}

	/**
	 * Validate image layers for product
	 */
	public function validate_image_layers() {
		$validated_images = array();
		$enabled          = filter_input( INPUT_POST, 'jckpc_enabled', FILTER_VALIDATE_BOOLEAN );
		$images           = filter_input( INPUT_POST, 'jckpc_images', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );

		if ( empty( $images ) ) {
			return $images;
		}

		foreach ( $images as $attribute => $values ) {
			// validate background image
			if ( $attribute == "background" ) {
				$image_id = $values;

				if ( empty( $image_id ) ) {
					if ( ! $enabled ) {
						continue;
					}

					$this->notices->add_notice( 'error', __( 'You must add a background image in the configurator tab.', 'jckpc' ) );
					continue;
				}

				if ( ! $this->validate_image( $image_id, 'png' ) ) {
					continue;
				}

				$validated_images[ $attribute ] = $image_id;
				continue;
			}

			// validate static layer
			if ( strpos( $attribute, 'jckpc-static-' ) !== false ) {
				$image_id = $values;

				if ( ! $this->validate_image( $image_id, 'png' ) ) {
					continue;
				}

				$validated_images[ $attribute ] = $image_id;
				continue;
			}

			if ( empty( $values ) ) {
				continue;
			}

			// validate attribute value images
			foreach ( $values as $value => $image_id ) {
				if ( empty( $image_id ) ) {
					$term = get_term_by( 'slug', str_replace( 'jckpc-', '', $value ), str_replace( 'jckpc-', '', $attribute ) );

					if ( $term ) {
						$image_id = self::get_default_image( $term->term_id );
					}
				}

				if ( empty( $image_id ) ) {
					continue;
				}

				if ( ! $this->validate_image( $image_id, 'png' ) ) {
					continue;
				}

				$validated_images[ $attribute ][ $value ] = $image_id;
			}
		}

		return $validated_images;
	}

	/**
	 * Layout helper for image table rows
	 */
	public function image_upload_row( $args ) {
		global $post_id, $wpdb;

		$defaults = array(
			'row_name'          => false,
			'field_name'        => false,
			'field_id'          => false,
			'selected_image_id' => false,
			'popup_title'       => false,
			'popup_button_text' => false,
			'button_text'       => false,
			'classes'           => array(),
			'show_inventory'    => false,
			'show_fee'          => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$args['classes'][] = "uploader";

		$return = '';
		$return .= '<tr class="' . implode( ' ', $args['classes'] ) . '">';

		$selectedImageSrc = ( $args['selected_image_id'] && $args['selected_image_id'] != '' ) ? wp_get_attachment_image_src( $args['selected_image_id'], 'thumbnail' ) : false;
		$selectedImageUrl = ( $selectedImageSrc ) ? $selectedImageSrc[0] : false;

		$return .= '<td>';
		$return .= '<input type="hidden" name="' . $args['field_name'] . '" id="' . $args['field_id'] . '" value="' . $args['selected_image_id'] . '" />';
		$return .= '<div id="' . $args['field_id'] . '_thumbwrap" class="jckpc_attthumb jckpc-layer-options__thumbnail">';
		if ( $selectedImageSrc ) {
			$return .= '<img src="' . $selectedImageUrl . '" width="80" height="80">';
		}
		$return .= '<a href="#" class="jckpc-image-button jckpc-image-button--remove" data-uploader_field="#' . $args['field_id'] . '">' . __( 'Remove Image', 'jckpc' ) . '</a>';
		$return .= '<a href="#" class="jckpc-image-button jckpc-image-button--upload" id="' . $args['field_id'] . '_button" data-uploader_title="' . $args['popup_title'] . '" data-uploader_button_text="' . $args['popup_button_text'] . '" data-uploader_field="#' . $args['field_id'] . '">' . $args['button_text'] . '</a>';
		$return .= '</div>';
		$return .= '</td>';

		if ( $args['row_name'] !== false ) {
			$return .= '<td>' . $args['row_name'] . '</td>';
		}

		if ( $args['show_fee'] ) {
			$fee_att_val_id = str_replace( '_image', '', $args['field_id'] );
			$fee_field_name = 'jckpc_fee[' . $fee_att_val_id . ']';

			$fee = '';

			$return .= '<td>';
			$return .= '<input type="number" name="' . $fee_field_name . '" id="' . $fee_att_val_id . '_fee" value="' . $fee . '" />';
			$return .= '</td>';
		}

		if ( $args['show_inventory'] ) {
			$inventory_att_val_id = str_replace( '_image', '', $args['field_id'] );
			$inventory_field_name = 'jckpc_inventory[' . $inventory_att_val_id . ']';

			$inventory = Iconic_PC_Inventory::get_inventory( array(
				'product_id' => $post_id,
				'att_val_id' => $inventory_att_val_id,
			) );

			$return .= '<td>';
			$return .= '<input type="number" name="' . $inventory_field_name . '" id="' . $inventory_att_val_id . '_inventory" value="' . $inventory . '" />';
			$return .= '</td>';
		}

		$return .= '</tr>';

		return $return;
	}

	/**
	 * Get WooCommerce attribute taxonomy names
	 *
	 * @return array
	 */
	public function get_woo_attribute_taxonomies() {
		$attributes = wc_get_attribute_taxonomies();
		$return     = array();

		if ( $attributes && is_array( $attributes ) && ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				$return[] = esc_html( wc_attribute_taxonomy_name( $attribute->attribute_name ) );
			}
		}

		return $return;
	}

	/**
	 * Get default image for attribute term
	 *
	 * @param int|bool $term_id
	 *
	 * @return string|bool
	 */
	public static function get_default_image( $term_id = false ) {
		if ( ! $term_id ) {
			return false;
		}

		return get_term_meta( $term_id, 'jckpc_default_image', true );
	}

	/**
	 * Display configurator image.
	 *
	 * @param bool     $show_thumbnails
	 * @param int|null $product_id
	 */
	public function display_product_image( $show_thumbnails = true, $product_id = null ) {
		global $post;

		$product_id      = $product_id ? $product_id : $post->ID;
		$show_thumbnails = $show_thumbnails === "" ? true : $show_thumbnails;

		$layers         = array();
		$images         = array();
		$setImages      = get_post_meta( $product_id, 'jckpc_images', true );
		$defaults       = get_post_meta( $product_id, 'jckpc_defaults', true );
		$image_size     = Iconic_PC_Product::get_image_size( 'single' );
		$querySelectors = $_GET;

		if ( ! empty( $querySelectors ) ) {
			$sanitisedQuerySelectors = array();

			foreach ( $querySelectors as $attSlug => $attVal ) {
				$sanitisedQuerySelectors[ Iconic_PC_Helpers::sanitise_str( $attSlug ) ] = Iconic_PC_Helpers::sanitise_str( $attVal );
			}

			$querySelectors = $sanitisedQuerySelectors;
		}

		if ( is_array( $setImages ) ) {
			$setImages = array_reverse( $setImages );

			foreach ( $setImages as $attSlug => $attVals ) {
				if ( strpos( $attSlug, 'jckpc-static-' ) !== false ) {
					$default = $attVals;
				} else {
					$default = isset( $defaults[ $attSlug ] ) && isset( $setImages[ $attSlug ][ $defaults[ $attSlug ] ] ) ? $setImages[ $attSlug ][ $defaults[ $attSlug ] ] : false;
					$default = isset( $querySelectors[ $attSlug ] ) && isset( $setImages[ $attSlug ][ $querySelectors[ $attSlug ] ] ) ? $setImages[ $attSlug ][ $querySelectors[ $attSlug ] ] : $default;
				}

				$layers[ $attSlug ] = absint( $default );
			}
		}

		foreach ( $layers as $layer_id => $image_id ) {
			if ( $layer_id === 'background' ) {
				$full_src      = wp_get_attachment_image_src( $setImages['background'], 'full' );
				$dynamic_image = $this->get_product_image_url( array_merge(
					array( 'prodid' => $product_id ),
					wp_parse_args( $querySelectors, $defaults )
				) );
				// Dummy image for product zoom.
				$images['dummy_zoom'] = sprintf( '<img src="%1$s" class="iconic-pc-image-zoom" data-large_image="%1$s" data-large_image_width="%2$s" data-large_image_height="%3$s">', $dynamic_image, $full_src[1], $full_src[2] );
				$images[]             = wp_get_attachment_image( $setImages['background'], $image_size, false, array(
					'class'                   => 'iconic-pc-image-background',
					'data-large_image'        => $dynamic_image,
					'data-large_image_width'  => $full_src[1],
					'data-large_image_height' => $full_src[2],
				) );
				continue;
			}

			$image = $image_id ? wp_get_attachment_image( $image_id, $image_size ) : '';

			$images[] = sprintf( '<div class="iconic-pc-image-%s">%s</div>', esc_attr( $layer_id ), $image );
		}

		$images = apply_filters( 'iconic_pc_image_layers', $images, $product_id, $setImages, $defaults );

		// output images

		$wrapper_classes = apply_filters( 'iconic_pc_images_wrapper_classes', array(
			'iconic-pc-images',
			'woocommerce-product-gallery',
		) );
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
			<div class="iconic-pc-image-wrap woocommerce-product-gallery__image" data-product-id="<?php echo esc_attr( $product_id ); ?>">
				<?php do_action( 'iconic_pc_before_layers', $product_id ); ?>

				<?php if ( is_array( $images ) ) {
					foreach ( $images as $image ) {
						echo $image;
					}
				} ?>

				<?php do_action( 'iconic_pc_after_layers', $product_id ); ?>

				<?php if ( $this->settings['loader_icon_style'] !== "none" ) { ?>
					<div class="iconic-pc-loading">
						<i class="jckpc-icn-<?php echo esc_attr( $this->settings['loader_icon_style'] ); ?> animate-spin"></i>
					</div>
				<?php } ?>
			</div>

			<?php if ( $show_thumbnails && $this->settings['thumbnails_general_enable'] ) {
				self::get_thumbnails();
			} ?>
		</div>
		<?php
	}

	/**
	 * Display product thumbnails
	 */
	public static function get_thumbnails() {
		?>
		<div class="iconic-pc-thumbnails">
			<span class="iconic-pc-thumbnails__placeholder"></span>
			<?php woocommerce_show_product_thumbnails(); ?>
		</div>
		<?php
	}

	/**
	 * Get image data
	 *
	 * @param int $pid
	 * @param arr $atts
	 *
	 * @return arr
	 */
	public function get_image_data( $pid, $atts ) {
		$imgData = array(
			'prodid' => $pid,
		);

		if ( ! empty( $atts ) ) {
			foreach ( $atts as $attSlug => $attVal ) {
				if ( substr( $attSlug, 0, 10 ) != "attribute_" ) {
					$attSlug = 'attribute_' . $attSlug;
				}
				$imgData[ $attSlug ] = sanitize_title( $attVal );
			}
		}

		return $imgData;
	}

	/**
	 * Get single image layer
	 */
	public function get_image_layer() {
		do_action( 'iconic_pc_before_get_image_layer' );

		$product_id   = filter_input( INPUT_POST, 'prodid', FILTER_SANITIZE_NUMBER_INT );
		$selected_val = filter_input( INPUT_POST, 'selectedVal', FILTER_SANITIZE_STRING );
		$selected_att = filter_input( INPUT_POST, 'selectedAtt', FILTER_SANITIZE_STRING );
		$request_id   = absint( filter_input( INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT ) );

		$selected_val = Iconic_PC_Helpers::sanitise_str( $selected_val );

		$selected_val_stripped = Iconic_PC_Helpers::strip_prefix( $selected_val );
		$selected_att_stripped = Iconic_PC_Helpers::strip_prefix( $selected_att );
		$image_size            = Iconic_PC_Product::get_image_size( 'single' );

		$setImages    = Iconic_PC_Product::get_set_images( $product_id );
		$theTerm      = get_term_by( 'slug', $selected_val_stripped, $selected_att_stripped );
		$defaultImgId = $theTerm ? self::get_default_image( $theTerm->term_id ) : "";

		$response = array(
			'response'   => 'success',
			'image'      => false,
			'request_id' => $request_id,
		);

		if ( empty( $selected_val ) ) {
			$defaults     = get_post_meta( $product_id, 'jckpc_defaults', true );
			$selected_val = isset( $defaults[ $selected_att ] ) ? $defaults[ $selected_att ] : false;
		}

		if ( ! empty( $selected_val ) ) {
			$imgID = ! empty( $setImages[ $selected_att ][ $selected_val ] ) ? $setImages[ $selected_att ][ $selected_val ] : $defaultImgId;

			if ( ! empty( $imgID ) ) {
				$response['image'] = wp_get_attachment_image( $imgID, $image_size, false, array(
					'class' => sprintf( 'iconic-pc-image-%s', esc_attr( $selected_att ) ),
				) );
			}
		}

		$response['post'] = $_POST;

		wp_send_json( $response );
	}

	/**
	 * Ajax image get params
	 */
	public function ajax_img_get_params() {
		$params = array();

		$sort_order = self::get_sort_order( $_GET['prodid'] );

		$params['setImages']  = get_post_meta( $_GET['prodid'], 'jckpc_images', true );
		$params['chosenAtts'] = $this->get_atts_from_querystring( $_GET['prodid'] );
		$images               = $this->get_images_from_chosen_atts( $params['setImages'], $params['chosenAtts'] );
		$params['images']     = array_reverse( array_values( $this->sortArrayByArray( $images, $sort_order['array'] ) ) );

		$params['imgData'] = $this->generate_img_paths( $_GET['prodid'], $params['chosenAtts'] );

		return $params;
	}

	/**
	 * Generate image paths
	 *
	 * @param int   $prodid
	 * @param array $chosenAtts
	 *
	 * @return array
	 */
	public function generate_img_paths( $prodid, $chosenAtts ) {
		$imgName = $prodid . '-' . md5( implode( '-', array_filter( $chosenAtts ) ) );

		return array(
			'imgName'      => $imgName,
			'finalImgPath' => $this->uploads_path . '/' . $imgName . '.png',
			'finalImgUrl'  => $this->uploads_url . '/' . $imgName . '.png',
		);
	}

	/**
	 * Generate final image
	 */
	public function generate_image() {
		$params = $this->ajax_img_get_params();

		// Set up image space
		list( $compW, $compH ) = getimagesize( $params['images'][0] );
		$bg = imagecreatetruecolor( $compW, $compH );

		imagesavealpha( $bg, true );

		$trans_colour = imagecolorallocatealpha( $bg, 0, 0, 0, 127 );
		imagefill( $bg, 0, 0, $trans_colour );

		if ( is_array( $params['images'] ) ) {
			foreach ( $params['images'] as $index => $srcImagePath ) {
				$img = imagecreatefrompng( $srcImagePath );

				$x = 0;
				$y = 0;

				imagecopyresized( $bg, $img, 0, 0, 0, 0, $compW, $compH, $compW, $compH );
				imagedestroy( $img );
			}
		}

		header( 'Content-Type: image/png' );
		$createFinalImg = imagepng( $bg, $params['imgData']['finalImgPath'] );

		if ( $createFinalImg ) {
			set_transient( $params['imgData']['imgName'], $params['imgData']['finalImgUrl'], $this->settings['general_cache_duration'] * HOUR_IN_SECONDS );
			imagepng( $bg );
		}

		die;
	}

	/**
	 * Get attributes from query string
	 *
	 * @param int|bool   $prodid
	 * @param array|bool $qarr
	 *
	 * @return array
	 */
	public function get_atts_from_querystring( $prodid = false, $qarr = false ) {
		if ( ! $qarr ) {
			$qarr = $_GET;
		}

		// Get defaults
		$atts = get_post_meta( $prodid, 'jckpc_defaults', true );

		if ( is_array( $qarr ) ) {
			foreach ( $qarr as $key => $value ) {
				if ( substr( $key, 0, 10 ) == "attribute_" ) {
					$atts[ Iconic_PC_Helpers::sanitise_str( str_replace( 'attribute_', '', $key ) ) ] = Iconic_PC_Helpers::sanitise_str( $value );
				}
			}
		}

		return apply_filters( 'iconic_pc_query_string_attributes', $atts );
	}

	/**
	 * Get images from chosen attributes
	 */
	public function get_images_from_chosen_atts( $setImages, $chosenAtts ) {
		$images = array();

		if ( is_array( $setImages ) ) {
			foreach ( $setImages as $layer_id => $layer_data ) {
				if ( strpos( $layer_id, 'jckpc-static-' ) === false ) {
					continue;
				}

				$images[ $layer_id ] = $this->get_attachment_image_path( $layer_data );
			}
		}

		if ( is_array( $chosenAtts ) ) {
			foreach ( $chosenAtts as $attSlug => $attVal ) {
				if ( empty( $setImages[ $attSlug ][ $attVal ] ) ) {
					continue;
				}

				$images[ $attSlug ] = $this->get_attachment_image_path( $setImages[ $attSlug ][ $attVal ] );
			}
		}

		if ( isset( $setImages['background'] ) && $setImages['background'] != "" ) {
			$images['background'] = $this->get_attachment_image_path( $setImages['background'] );
		}

		// reverse so the layering is correct
		return $images;
	}

	/**
	 * Get image path from ID
	 *
	 * @param int    $attachment_id
	 * @param string $size
	 *
	 * @return bool|string
	 */
	public function get_attachment_image_path( $attachment_id, $size = 'single' ) {
		$size = Iconic_PC_Product::get_image_size( $size );

		$image_src = wp_get_attachment_image_src( $attachment_id, $size );

		if ( ! $image_src ) {
			return false;
		}

		$img_path = realpath( str_replace( $this->upload_dir['baseurl'], $this->upload_dir['basedir'], self::strip_query_string( $image_src[0] ) ) );

		return $img_path;
	}

	/**
	 * Strip query string from URL.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function strip_query_string( $url ) {
		$url_exploded = explode( '?', $url );

		return reset( $url_exploded );
	}

	/**
	 * Get product image URL
	 */
	public function get_product_image_url( $imgData ) {
		foreach ( $imgData as $key => $value ) {
			if ( strpos( $key, 'jckpc-' ) === false ) {
				continue;
			}

			$new_key             = str_replace( 'jckpc-', 'attribute_', $key );
			$imgData[ $new_key ] = str_replace( 'jckpc-', '', $value );

			unset( $imgData[ $key ] );
		}

		$qstring = http_build_query( array_filter( $imgData ) );

		$chosenAtts = $this->get_atts_from_querystring( $imgData['prodid'], $imgData );
		$imgPaths   = $this->generate_img_paths( $imgData['prodid'], $chosenAtts );

		if ( ! $this->settings['general_cache_enable'] ) {
			delete_transient( $imgPaths['imgName'] );
		}

		if ( false === get_transient( $imgPaths['imgName'] ) ) {
			return sprintf( '%s?action=jckpc_generate_image&%s', admin_url( 'admin-ajax.php' ), $qstring );
		} else {
			return $imgPaths['finalImgUrl'];
		}
	}

	/**
	 * Modify cart thumbnail
	 */
	public function cart_thumbnail( $thumb, $cart_item, $cart_item_key = false ) {
		$cart_item['wrap']       = isset( $cart_item['wrap'] ) ? $cart_item['wrap'] : false;
		$cart_item['image_size'] = isset( $cart_item['image_size'] ) ? $cart_item['image_size'] : array(
			get_option( 'thumbnail_size_w' ),
			get_option( 'thumbnail_size_h' ),
		);
		$cart_item['image_size'] = apply_filters( 'jckpc-thumbnail-image-size', $cart_item['image_size'] );

		// check if product has configurator enable, otherwise echo $thumb instead

		$configurator_enabled = Iconic_PC_Product::is_configurator_enabled( $cart_item['product_id'] );

		if ( $configurator_enabled ) {
			$atts = ( isset( $cart_item['variation'] ) && ! empty( $cart_item['variation'] ) ) ? $cart_item['variation'] : array();

			$imgData = $this->get_image_data( $cart_item['product_id'], $atts );
			$img_url = $this->get_product_image_url( $imgData );

			$image = '<img src="' . $img_url . '" width="' . $cart_item['image_size'][0] . '" height="' . $cart_item['image_size'][1] . '">';

			if ( $cart_item['wrap'] ) {
				return sprintf( '<div style="margin-bottom: 5px;">%s</div>', $image );
			} else {
				return $image;
			}
		}

		return $thumb;
	}

	/**
	 * Add class to order item
	 */
	public function order_item_class( $class, $item, $order ) {
		$prodid = $item['product_id'];

		$configurator_enabled = Iconic_PC_Product::is_configurator_enabled( $prodid );

		if ( $configurator_enabled ) {
			$class .= ' jckpc_configurated';
		}

		return $class;
	}

	/**
	 * Register scripts and styles
	 */
	public function register_scripts_and_styles() {
		if ( is_admin() ) {
			global $pagenow, $post;

			if (
				( $pagenow == "post.php" && $post && $post->post_type == "product" ) ||
				( $pagenow == "term.php" )
			) {
				wp_enqueue_media();
				$this->load_file( 'jckpc-script', '/assets/admin/js/main.min.js', true, array( 'wp-util' ) );

				wp_register_style( 'jckpc_admin_styles', ICONIC_PC_URL . 'assets/admin/css/main.min.css', array(), self::$version );

				wp_enqueue_style( 'jckpc_admin_styles' );

				$vars = array(
					'i18n' => array(
						'png_only' => __( 'Please upload a PNG image.', 'jckpc' ),
					),
				);

				wp_localize_script( 'jckpc-script', 'jckpc_vars', $vars );
			}
		} else {
			global $post;

			if ( ! $post ) {
				return;
			}

			if ( $post->post_type === 'product' ) {
				$configurator_enabled = Iconic_PC_Product::is_configurator_enabled( $post->ID );
			} else {
				$configurator_enabled = has_shortcode( $post->post_content, 'product_page' ) || has_shortcode( $post->post_content, 'iconic-wpc-gallery' );
			}

			if ( ! $configurator_enabled ) {
				return;
			}

			wp_register_style( 'jckpc_styles', ICONIC_PC_URL . 'assets/frontend/css/main.min.css', array(), self::$version );

			wp_enqueue_style( 'jckpc_styles' );
			wp_add_inline_style( 'jckpc_styles', $this->get_inline_styles() );

			$this->load_file( $this->slug . '-script', '/assets/frontend/js/main.min.js', true );

			$vars = apply_filters( 'iconic_pc_localize_script', array(
				'ajaxurl'  => WC()->ajax_url(),
				'nonce'    => wp_create_nonce( 'jckpc_ajax' ),
				'settings' => $this->settings,
			) );

			wp_localize_script( $this->slug . '-script', $this->slug, $vars );
		}
	}

	/**
	 * Get inline styles.
	 *
	 * @return string
	 */
	public function get_inline_styles() {
		$thumb_spacing_h = $this->settings['thumbnails_general_spacing'] / 2;
		$thumb_spacing_v = $this->settings['thumbnails_general_spacing'];
		$thumb_width     = 100 / (int) $this->settings['thumbnails_general_columns'];

		$rules = array(
			'.iconic-pc-images'                                                  => array(
				'width' => $this->settings['display_images_width'] . '%',
				'float' => $this->settings['display_images_position'],
			),
			'.iconic-pc-thumbnails'                                              => array(
				'margin' => $thumb_spacing_v . 'px ' . - $thumb_spacing_h . 'px ' . - $thumb_spacing_v . 'px',
			),
			'.iconic-pc-thumbnails .woocommerce-product-gallery__image'          => array(
				'float'   => 'left',
				'display' => 'inline-block',
				'width'   => $thumb_width . '%',
				'padding' => '0 ' . $thumb_spacing_h . 'px ' . $thumb_spacing_v . 'px',
			),
			'.iconic-pc-image-wrap .iconic-pc-loading'                           => array(
				'background' => $this->settings['loader_overlay_color'],
			),
			'.iconic-pc-image-wrap .iconic-pc-loading.iconic-pc-loader--loading' => array(
				'opacity' => $this->settings['loader_overlay_opacity'],
			),
			'.iconic-pc-image-wrap .iconic-pc-loading i'                         => array(
				'font-size'   => '20px',
				'line-height' => '20px',
				'margin-top'  => '-10px',
				'color'       => $this->settings['loader_icon_color'],
			),
		);

		if ( $this->settings['display_images_position'] == "centre" ) {
			$rules['.iconic-pc-images']['margin-left']  = 'auto';
			$rules['.iconic-pc-images']['margin-right'] = 'auto';
			$rules['.iconic-pc-images']['float']        = 'none';
		}

		if ( $this->settings['display_responsive_enable'] ) {
			$breakpoint = '@media (max-width: ' . $this->settings['display_responsive_breakpoint'] . 'px)';

			$rules[ $breakpoint ] = array(
				'.iconic-pc-images' => array(
					'width' => $this->settings['display_responsive_width'] . '%',
					'float' => $this->settings['display_responsive_position'],
				),
			);

			if ( $this->settings['display_responsive_position'] == "centre" ) {
				$rules[ $breakpoint ]['.iconic-pc-images']['float']        = 'none';
				$rules[ $breakpoint ]['.iconic-pc-images']['margin-left']  = 'auto';
				$rules[ $breakpoint ]['.iconic-pc-images']['margin-right'] = 'auto';
			}
		}

		return Iconic_PC_Helpers::to_css( $rules );
	}

	/**
	 * Helper function to enqueue styles/scripts
	 */
	private function load_file( $name, $file_path, $is_script = false, $deps = array( 'jquery' ), $inFooter = true ) {
		$url  = plugins_url( $file_path, __FILE__ );
		$file = plugin_dir_path( __FILE__ ) . $file_path;

		if ( file_exists( $file ) ) {
			if ( $is_script ) {
				wp_register_script( $name, $url, $deps, self::$version, $inFooter ); //depends on jquery
				wp_enqueue_script( $name );
			} else {
				wp_register_style( $name, $url, array(), self::$version );
				wp_enqueue_style( $name );
			}
		}
	}

	/**
	 * Remove filters/hooks from anonymous classes
	 */
	public function remove_filters_for_anonymous_class( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
		global $wp_filter;

		// Take only filters on right hook name and priority
		if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
			return false;
		}

		// Loop on filters registered
		foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
			// Test if filter is an array ! (always for class/method)
			if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
				// Test if object is a class, class and method is equal to param !
				if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
					unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
				}
			}
		}

		return false;
	}

	/**
	 * Sort one array by another
	 *
	 * @param arr $array
	 * @param arr $order_array
	 *
	 * @return arr
	 */
	function sortArrayByArray( Array $array, Array $order_array ) {
		$ordered = array();

		foreach ( $order_array as $key ) {
			if ( array_key_exists( $key, $array ) ) {
				$ordered[ $key ] = $array[ $key ];
				unset( $array[ $key ] );

				continue;
			}

			$key = str_replace( 'jckpc-', '', $key );

			if ( array_key_exists( $key, $array ) ) {
				$ordered[ $key ] = $array[ $key ];
				unset( $array[ $key ] );

				continue;
			}
		}

		return $ordered + $array;
	}

	/**
	 * Thumbnail: Change thumbnail in order emails
	 *
	 * @param str $thumbnail_html
	 * @param obj $item
	 *
	 * @return str
	 */
	public function order_item_thumbnail( $thumbnail_html, $item ) {
		$meta = Iconic_PC_Order_Item::get_meta( $item );

		$args = apply_filters( 'iconic_email_order_item_thumbnail', array(
			'product_id' => $item['product_id'],
			'variation'  => $meta,
			'image_size' => array( 32, 32 ),
			'wrap'       => true,
		) );

		$thumbnail_html = $this->cart_thumbnail( $thumbnail_html, $args );

		return $thumbnail_html;
	}

	/**
	 * Extract attribute value pairs from array
	 *
	 * @param arr $array
	 *
	 * @return arr
	 */
	public function extract_att_value_pairs( $array ) {
		if ( ! $array || empty( $array ) ) {
			return array();
		}

		$pairs = array();

		foreach ( $array as $key => $value ) {
			if ( substr( $key, 0, 10 ) == "attribute_" || substr( $key, 0, 3 ) == "pa_" ) {
				$pairs[ $key ] = is_array( $value ) ? $value[0] : $value;
			}
		}

		return $pairs;
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

			//add_filter( sprintf('manage_edit-pa_%s_columns', $attribute->attribute_name), array( $this, 'add_attribute_columns' ) );
			//add_filter( sprintf('manage_pa_%s_custom_column', $attribute->attribute_name), array( $this, 'add_attribute_column_content' ), 10, 3 );

		}
	}

	/**
	 * Admin: Add attribute term fields
	 *
	 * @param int $term the concrete term
	 */
	public function output_attribute_term_fields( $term = false ) {
		global $iconic_was;

		if ( empty( $_GET['taxonomy'] ) || empty( $_GET['tag_ID'] ) ) {
			return;
		}

		$field_name   = 'jckpc_attribute_image';
		$field_id     = 'jckpc-attribute-image';
		$field_label  = __( 'Default Image', 'jckpc' );
		$value        = get_term_meta( $_GET['tag_ID'], 'jckpc_default_image', true );
		$img          = $value ? wp_get_attachment_image( $value, 'thumbnail' ) : false;
		$upload_class = $value ? sprintf( '%1$s__upload %1$s__upload--edit', $field_id ) : sprintf( '%s__upload', $field_id );

		$fields = array(
			array(
				'label'       => sprintf(
					'<label for="%s-field">%s</label>',
					$field_id,
					$field_label
				),
				'field'       => sprintf(
					'<div class="%1$s">
                        <div class="%1$s__preview">%2$s</div>
                        <input id="%1$s-field" type="hidden" name="%3$s" value="%4$s" class="%1$s__field regular-text">

                        <a href="javascript: void(0);" class="%1$s__button %9$s" title="%5$s" id="upload-%1$s" data-title="%5$s" data-button-text="%6$s"><span class="dashicons dashicons-edit"></span><span class="dashicons dashicons-plus"></span></a>

                        <a href="javascript: void(0);" class="%1$s__button %1$s__remove" title="%7$s" %8$s><span class="dashicons dashicons-no"></span></a>
                    </div>',
					$field_id,
					$img,
					$field_name,
					$value,
					__( 'Upload/Add Image', 'iconic-was' ),
					__( 'Insert Image', 'iconic-was' ),
					__( 'Remove Image', 'iconic-was' ),
					$img ? false : 'style="display: none;"',
					$upload_class
				),
				'description' => '',
			),
		);

		$is_edit_page = is_object( $term );

		if ( $fields ) {
			if ( $is_edit_page ) {
				printf( '<h3>%s</h3>', __( 'Configurator Options', 'iconic-was' ) );

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
		if ( isset( $_POST['jckpc_attribute_image'] ) ) {
			if ( ! $this->validate_image( $_POST['jckpc_attribute_image'], 'png' ) ) {
				return;
			}

			update_term_meta( $term_id, 'jckpc_default_image', $_POST['jckpc_attribute_image'] );
		}
	}

	/**
	 * Helper: Validate image
	 *
	 * @param int $image_id
	 * @param str $file_type
	 *
	 * @return bool
	 */
	public function validate_image( $image_id, $file_type ) {
		$image_src = wp_get_attachment_image_src( $image_id );

		if ( ! $image_src ) {
			$this->notices->add_notice( 'error', __( 'Sorry, an issue occurred when attaching the image you selected. Please try again.', 'jckpc' ) );

			return false;
		}

		$image_src = explode( '?', $image_src[0] ); // Remove query string if present (#2243)
		$filetype  = wp_check_filetype( $image_src[0] );

		if ( strtolower( $filetype['ext'] ) !== strtolower( $file_type ) ) {
			$this->notices->add_notice( 'error', sprintf( __( 'Please make sure your image is a %s file.' ), $file_type ), 'jckpc' );

			return false;
		}

		return true;
	}

	/**
	 * Show image in emails
	 *
	 * @param arr $args
	 *
	 * @return arr
	 */
	public static function email_order_items_args( $args ) {
		$args['show_image'] = true;

		return $args;
	}
}

$jckpc = new jckpc();