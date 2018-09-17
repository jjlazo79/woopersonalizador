<?php

include_once( get_template_directory() . '/lib/init.php' );

define( 'CHILD_THEME_NAME', 'customizer_bags');
define( 'CHILD_THEME_URL', 'https://github.com/jjlazo79/woopersonalizador/' );
define( 'CHILD_THEME_VERSION', '0.1' );

/**
 * HTML5 support
 */
add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

/**
 * Fonts to mobile
 */
add_theme_support( 'genesis-responsive-viewport' );

/**
 * Declare WooCommerce support
 * Reference: https://www.wpstud.io/3-ways-to-integrate-woocommerce-with-genesis/
 */
// function woocommerce_setup_genesis() {
//     woocommerce_content();
// }

/**
 * WooCommerce support AlphaBlossom Method
 * Reference: http://www.alphablossom.com/integrate-woocommerce-with-genesis-framework-wordpress/
 */
include_once( '/assets/woocommerce-genesis-theme-support.php' );

/**
 * Remove admin warning
 */
add_theme_support( 'woocommerce' );

/**
 * Proper way to enqueue scripts and styles.
 */
function customizer_bag_scripts() {
    wp_enqueue_script( 'personalizador', get_stylesheet_directory_uri() . '/assets/js/personalizador.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'customizer_bag_scripts' );


/**
 * Add template to
 */
function custom_single_product_template_include( $template ) {
    if ( is_singular('product') && (has_term( 'personalizador', 'product_cat')) ) {
        $template = get_stylesheet_directory() . '/woocommerce/single-product-personalizador.php';
    }
    return $template;
}
add_filter( 'template_include', 'custom_single_product_template_include', 50, 1 );
