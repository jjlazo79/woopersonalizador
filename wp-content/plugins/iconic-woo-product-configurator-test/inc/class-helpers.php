<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_PC_Helpers.
 *
 * @class    Iconic_PC_Helpers
 * @version  1.0.1
 * @since    1.2.0
 * @author   Iconic
 */
class Iconic_PC_Helpers {
	/**
	 * Check whether the plugin is inactive.
	 *
	 * Reverse of is_plugin_active(). Used as a callback.
	 *
	 * @since 3.1.0
	 * @see   is_plugin_active()
	 *
	 * @param string $plugin Base plugin path from plugins directory.
	 *
	 * @return bool True if inactive. False if active.
	 */
	public static function is_plugin_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || self::is_plugin_active_for_network( $plugin );
	}

	/**
	 * Check whether the plugin is active for the entire network.
	 *
	 * Only plugins installed in the plugins/ folder can be active.
	 *
	 * Plugins in the mu-plugins/ folder can't be "activated," so this function will
	 * return false for those plugins.
	 *
	 * @since 3.0.0
	 *
	 * @param string $plugin Base plugin path from plugins directory.
	 *
	 * @return bool True, if active for the network, otherwise false.
	 */
	public static function is_plugin_active_for_network( $plugin ) {
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
	 * Get ajax URL.
	 *
	 * @return str
	 */
	public static function get_ajax_url() {
		return WC()->ajax_url();
	}

	/**
	 * Strip prefix.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function strip_prefix( $string ) {
		return str_replace( 'jckpc-', '', $string );
	}

	/**
	 * Sanitise string
	 *
	 * @param string $str
	 * @param string $alt_str
	 *
	 * @return string
	 */
	public static function sanitise_str( $str, $alt_str = "" ) {
		if ( empty( $str ) && empty( $alt_str ) ) {
			return '';
		}

		if ( function_exists( 'ctl_sanitize_title' ) ) {
			$alt_str = ! empty( $alt_str ) ? $alt_str : $str;
			$new_str = sanitize_title( $alt_str );
		} else {
			$new_str = str_replace( array( 'attribute_', '%' ), '', sanitize_title( $str ) );
		}

		if ( substr( $new_str, 0, 6 ) !== 'jckpc-' ) {
			$new_str = 'jckpc-' . $new_str;
		}

		return strtolower( $new_str );
	}

	/**
	 * Converts a multidimensional array of CSS rules into a CSS string.
	 *
	 * @param array $rules
	 *
	 * @return string
	 */
	public static function to_css( $rules ) {
		$css = '';

		foreach ( $rules as $key => $value ) {
			if ( is_array( $value ) ) {
				$selector   = $key;
				$properties = $value;

				$css .= "$selector { ";
				$css .= self::to_css( $properties );
				$css .= " } ";
			} else {
				$property = $key;
				$css      .= "$property: $value;";
			}
		}

		return $css;
	}

	/**
	 * Remove array item by value.
	 *
	 * @param array $array
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function remove_item_by_value( $array, $value ) {
		if ( ( $key = array_search( $value, $array ) ) !== false ) {
			unset( $array[ $key ] );
		}

		return $array;
	}
}