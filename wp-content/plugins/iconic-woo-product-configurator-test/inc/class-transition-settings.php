<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_PC_Transition_Settings.
 *
 * Transition any old settings to new ones
 *
 * @class    Iconic_PC_Transition_Settings
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_PC_Transition_Settings {

    /*
     * Init
     */
    public static function init_transition() {

        if( !get_option( 'jckpc_options' ) )
            return;

        self::transition_redux();

    }

    /**
     * Transition: Redux
     */
    public function transition_redux() {

        $new_settings = array();

        foreach( get_option( 'jckpc_options' ) as $key => $value ) {

            switch ($key) {
                case 'enable_img_cache':
                    $new_settings['general_cache_enable'] = $value;
                    break;
                case 'image_container_width':
                    $new_settings['display_images_width'] = str_replace(array('px','%','em'), '', $value['width']);
                    break;
                case 'image_container_align':
                    $new_settings['display_images_align'] = $value;
                    break;
                case 'enable_breakpoint':
                    $new_settings['display_responsive_enable'] = $value;
                    break;
                case 'breakpoint':
                    $new_settings['display_responsive_breakpoint'] = str_replace(array('px','%','em'), '', $value['width']);
                    break;
                case 'image_container_width_breakpoint':
                    $new_settings['display_responsive_width'] = str_replace(array('px','%','em'), '', $value['width']);
                    break;
                case 'image_container_align_breakpoint':
                    $new_settings['display_responsive_position'] = $value;
                    break;
                case 'show_thumbs':
                    $new_settings['thumbnails_general_enable'] = $value;
                    break;
                case 'thumb_cols_rows':
                    $new_settings['thumbnails_general_columns'] = $value;
                    break;
                case 'thumb_spacing':
                    $new_settings['thumbnails_general_spacing'] = $value;
                    break;
                case 'loading_overlay_colour':
                    $new_settings['loader_overlay_color'] = $value;
                    break;
                case 'loading_overlay_opacity':
                    $new_settings['loader_overlay_opacity'] = $value;
                    break;
                case 'loading_icon':
                    $new_settings['loader_icon_style'] = $value;
                    break;
                case 'loading_icon_colour':
                    $new_settings['loader_icon_color'] = $value;
                    break;
            }

        }

        add_option( 'iconic_woo_product_configurator_settings', $new_settings );
        delete_option( 'jckpc_options' );

    }

}