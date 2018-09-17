<?php

/**
 * Storefront automatically loads the core CSS even if using a child theme as it is more efficient
 * than @importing it in the child theme style.css file.
 *
 * Uncomment the line below if you'd like to disable the Storefront Core CSS.
 *
 * If you don't plan to dequeue the Storefront Core CSS you can remove the subsequent line and as well
 * as the sf_child_theme_dequeue_style() function declaration.
 */
//add_action( 'wp_enqueue_scripts', 'sf_child_theme_dequeue_style', 999 );

/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
    wp_dequeue_style( 'storefront-style' );
    wp_dequeue_style( 'storefront-woocommerce-style' );
}

/**
 * Note: DO NOT! alter or remove the code above this text and only add your custom PHP functions below this text.
 */

/**
 * Proper way to enqueue scripts and styles.
 */
function pilvia_scripts() {
    wp_enqueue_script( 'personalizador', get_stylesheet_directory_uri() . '/assets/js/personalizador.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'pilvia_scripts' );


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
