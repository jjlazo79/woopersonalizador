<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_PC_Deprecation.
 *
 * @class    Iconic_PC_Deprecation
 * @version  1.0.0
 * @since    1.2.0
 * @author   Iconic
 */
class Iconic_PC_Deprecation {

    /**
     * Get hook.
     *
     * @param str $hook
     * @return str
     */
    public static function get_hook( $hook ) {

        // new => old
        $hooks = array(
            'woocommerce_product_data_panels' => 'woocommerce_product_write_panels'
        );

        if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
            return $hooks[ $hook ];
        } else {
            return $hook;
        }

    }

}