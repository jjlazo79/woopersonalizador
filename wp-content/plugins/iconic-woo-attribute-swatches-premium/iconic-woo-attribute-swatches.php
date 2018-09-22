<?php
/**
 * Plugin Name: WooCommerce Attribute Swatches by Iconic
 * Plugin URI: https://iconicwp.com
 * Description: Swatches for your variable products.
 * Version: 1.1.2
 * Author: Iconic <support@iconicwp.com>
 * Author URI: https://iconicwp.com
 * Text Domain: iconic-was
 * WC requires at least: 2.6.14
 * WC tested up to: 3.4.5
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

class Iconic_Woo_Attribute_Swatches {
	/**
	 * Long name
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var string $name
	 */
	protected $name = "WooCommerce Attribute Swatches";

	/**
	 * Short name
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var string $shortname
	 */
	protected $shortname = "Attribute Swatches";

	/**
	 * Slug - Hyphen
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var string $slug
	 */
	public $slug = "iconic-was";

	/**
	 * Class prefix
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var string $class_prefix
	 */
	protected $class_prefix = "Iconic_WAS_";

	/**
	 * Version.
	 *
	 * @var string
	 */
	public static $version = "1.1.2";

	/**
	 * Plugin URL
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var string $plugin_url trailing slash
	 */
	protected $plugin_url;

	/**
	 * Attributes
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var Iconic_WAS_Attributes
	 */
	public $attributes;

	/**
	 * Helpers
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var Iconic_WAS_Helpers
	 */
	public $helpers;

	/**
	 * Products
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var Iconic_WAS_Products
	 */
	public $products;

	/**
	 * Swatches
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var Iconic_WAS_Swatches
	 */
	public $swatches;

	/**
	 * Settings.
	 *
	 * @var array
	 */
	public $settings = array();

	/**
	 * Construct
	 */
	public function __construct() {
		if ( ! $this->is_plugin_active( 'woocommerce/woocommerce.php' ) && ! $this->is_plugin_active( 'woocommerce-old/woocommerce.php' ) ) {
			return;
		}

		$this->textdomain();
		$this->define_constants();
		$this->load_classes();

		if ( ! Iconic_WAS_Core_Licence::has_valid_licence() ) {
			return;
		}

		add_action( 'plugins_loaded', array( $this, 'initiate_hook' ) );
	}

	/**
	 * Load textdomain
	 */
	public function textdomain() {
		load_plugin_textdomain( 'iconic-was', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Load classes
	 */
	private function load_classes() {
		require_once( ICONIC_WAS_INC_PATH . 'class-core-autoloader.php' );

		Iconic_WAS_Core_Autoloader::run( array(
			'prefix'   => 'Iconic_WAS_',
			'inc_path' => ICONIC_WAS_INC_PATH,
		) );

		Iconic_WAS_Core_Licence::run( array(
			'basename' => ICONIC_WAS_BASENAME,
			'urls'     => array(
				'product'  => 'https://iconicwp.com/products/woocommerce-attribute-swatches/',
				'settings' => admin_url( 'admin.php?page=iconic-was-settings' ),
				'account'  => admin_url( 'admin.php?page=iconic-was-settings-account' ),
			),
			'paths'    => array(
				'inc'    => ICONIC_WAS_INC_PATH,
				'plugin' => ICONIC_WAS_PATH,
			),
			'freemius' => array(
				'id'         => '1041',
				'slug'       => 'iconic-woo-attribute-swatches',
				'public_key' => 'pk_7b128a35b24f5882ab7935dc845d4',
				'menu'       => array(
					'slug' => 'iconic-was-settings',
				),
			),
		) );

		Iconic_WAS_Core_Settings::run( array(
			'vendor_path'   => ICONIC_WAS_VENDOR_PATH,
			'title'         => 'WooCommerce Attribute Swatches',
			'version'       => self::$version,
			'menu_title'    => 'Attribute Swatches',
			'settings_path' => ICONIC_WAS_INC_PATH . 'admin/settings.php',
			'option_group'  => 'iconic_was',
			'docs'          => array(
				'collection'      => '/collection/134-woocommerce-attribute-swatches',
				'troubleshooting' => '/category/139-troubleshooting',
				'getting-started' => '/category/137-getting-started',
			),
			'cross_sells'   => array(
				'iconic-woo-show-single-variations',
				'iconic-woothumbs',
			),
		) );

		if ( ! Iconic_WAS_Core_Licence::has_valid_licence() ) {
			return;
		}

		$this->attributes_class()->run();
		$this->products_class()->run();
		Iconic_WAS_Ajax::run();
		Iconic_WAS_Compat_WPML::run();
		Iconic_WAS_Compat_Flatsome::run();
		Iconic_WAS_Compat_Woo_Variations_Table::run();
	}

	/**
	 * Class: Swatches
	 *
	 * Access the swatches class without loading multiple times
	 */
	public function swatches_class() {
		if ( ! $this->swatches ) {
			$this->swatches = new Iconic_WAS_Swatches;
		}

		return $this->swatches;
	}

	/**
	 * Class: Products
	 *
	 * Access the products class without loading multiple times
	 */
	public function products_class() {
		if ( ! $this->products ) {
			$this->products = new Iconic_WAS_Products;
		}

		return $this->products;
	}

	/**
	 * Class: Attributes
	 *
	 * Access the attributes class without loading multiple times
	 */
	public function attributes_class() {
		if ( ! $this->attributes ) {
			$this->attributes = new Iconic_WAS_Attributes;
		};

		return $this->attributes;
	}

	/**
	 * Class: Helpers
	 *
	 * Access the helpers class without loading multiple times
	 */
	public function helpers_class() {
		if ( ! $this->helpers ) {
			$this->helpers = new Iconic_WAS_Helpers;
		}

		return $this->helpers;
	}

	/**
	 * Autoloader
	 *
	 * Classes should reside within /inc and follow the format of
	 * Iconic_The_Name ~ class-the-name.php or Iconic_WAS_The_Name ~ class-the-name.php
	 */
	private function autoload( $class_name ) {
		/**
		 * If the class being requested does not start with our prefix,
		 * we know it's not one in our project
		 */
		if ( 0 !== strpos( $class_name, 'Iconic_' ) && 0 !== strpos( $class_name, $this->class_prefix ) ) {
			return;
		}

		$file_name = strtolower( str_replace( array(
			$this->class_prefix,
			'Iconic_',
			'_',
		),      // Prefix | Plugin Prefix | Underscores
			array( '', '', '-' ),                              // Remove | Remove | Replace with hyphens
			$class_name ) );

		// Compile our path from the current location
		$file = dirname( __FILE__ ) . '/inc/class-' . $file_name . '.php';

		// If a file is found
		if ( file_exists( $file ) ) {
			// Then load it up!
			require( $file );
		}
	}

	/**
	 * Set constants
	 */
	public function define_constants() {
		$this->define( 'ICONIC_WAS_PATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'ICONIC_WAS_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'ICONIC_WAS_INC_PATH', ICONIC_WAS_PATH . 'inc/' );
		$this->define( 'ICONIC_WAS_VENDOR_PATH', ICONIC_WAS_INC_PATH . 'vendor/' );
		$this->define( 'ICONIC_WAS_BASENAME', plugin_basename( __FILE__ ) );
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
		$this->settings = Iconic_WAS_Core_Settings::$settings;

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

			add_filter( 'jck_qv_modal_classes', array( $this, 'qv_modal_classes' ), 10, 1 );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ) );
		}
	}

	/**
	 * Frontend: Styles
	 */
	public function frontend_styles() {
		wp_register_style( 'iconic-was-styles', ICONIC_WAS_URL . 'assets/frontend/css/main.min.css', array( 'dashicons' ), self::$version );

		wp_enqueue_style( 'iconic-was-styles' );
	}

	/**
	 * Frontend: Scripts
	 */
	public function frontend_scripts() {
		wp_register_script( 'iconic-was-scripts', ICONIC_WAS_URL . 'assets/frontend/js/main.min.js', array( 'jquery' ), self::$version, true );

		wp_enqueue_script( 'iconic-was-scripts' );

		$vars = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( $this->slug ),
		);

		wp_localize_script( 'iconic-was-scripts', 'iconic_was_vars', $vars );
	}

	/**
	 * Admin: Styles
	 */
	public function admin_styles() {
		global $post, $pagenow;

		wp_register_style( 'iconic-was-admin-styles', ICONIC_WAS_URL . 'assets/admin/css/main.min.css', array(), self::$version );

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'iconic-was-admin-styles' );
	}

	/**
	 * Admin: Scripts
	 */
	public function admin_scripts() {
		global $post;

		$current_screen = get_current_screen();
		$min            = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$page           = ! empty( $_GET['page'] ) ? $_GET['page'] : false;
		$taxonomy       = ! empty( $_GET['taxonomy'] ) ? $_GET['taxonomy'] : false;
		$product_edit   = $current_screen->base == "post" && $current_screen->post_type == "product";

		wp_register_script( 'iconic-was-conditional', ICONIC_WAS_URL . 'assets/vendor/js/jquery.conditional.min.js', array(
			'jquery',
		), self::$version, true );

		wp_register_script( 'iconic-was-scripts', ICONIC_WAS_URL . 'assets/admin/js/main' . $min . '.js', array(
			'jquery',
			'wp-color-picker',
			'iconic-was-conditional',
		), self::$version, true );

		if ( $page == "product_attributes" || substr( $taxonomy, 0, 3 ) === "pa_" || $product_edit ) {
			wp_enqueue_media();
			wp_enqueue_script( 'iconic-was-conditional' );
			wp_enqueue_script( 'iconic-was-scripts' );

			$vars = array(
				'url_params' => $_GET,
			);

			wp_localize_script( 'iconic-was-scripts', 'iconic_was_vars', $vars );
		}
	}

	/**
	 * Check whether the plugin is active.
	 *
	 * @since 1.0.1
	 *
	 * @param string $plugin Base plugin path from plugins directory.
	 *
	 * @return bool True if inactive. False if active.
	 */
	public function is_plugin_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || $this->is_plugin_active_for_network( $plugin );
	}

	/**
	 * Check whether the plugin is active for the entire network.
	 *
	 * Only plugins installed in the plugins/ folder can be active.
	 *
	 * Plugins in the mu-plugins/ folder can't be "activated," so this function will
	 * return false for those plugins.
	 *
	 * @since 1.0.1
	 *
	 * @param string $plugin Base plugin path from plugins directory.
	 *
	 * @return bool True, if active for the network, otherwise false.
	 */
	public function is_plugin_active_for_network( $plugin ) {
		if ( ! is_multisite() ) {
			return false;
		}
		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[ $plugin ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add classes to quickview modal
	 *
	 * @since 1.0.1
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public function qv_modal_classes( $classes ) {
		$classes[] = "jck-qc-has-swatches";

		return $classes;
	}
}

$iconic_was = new Iconic_Woo_Attribute_Swatches();