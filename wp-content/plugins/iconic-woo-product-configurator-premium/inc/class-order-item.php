<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_PC_Order_Item.
 *
 * @class    Iconic_PC_Order_Item
 * @version  1.0.0
 * @since    1.2.0
 * @author   Iconic
 */
class Iconic_PC_Order_Item {

    /**
     * Get item meta.
     *
     * @param WC_Product $item
     * @return arr
     */
    public static function get_meta( $item ) {

        $meta = method_exists( $item, 'get_meta_data' ) ? $item->get_meta_data() : $item['item_meta'];

        if( empty( $meta ) || is_wp_error( $meta ) )
            return array();

        $formatted_meta = array();

        foreach( $meta as $key => $value ) {

            if( is_object( $value ) ) {

                $formatted_meta[ $value->key ] = $value->value;

            } else {

                if( substr($key, 0, 1) == "_" )
                    continue;

                $formatted_meta[ $key ] = $value[0];

            }

        }

        return $formatted_meta;

    }

}