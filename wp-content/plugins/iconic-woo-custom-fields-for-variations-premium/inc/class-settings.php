<?php
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

/**
 * Class Iconic_CFFV_Settings
 */
class Iconic_CFFV_Settings {
	/**
	 * Run.
	 */
	public static function run() {
		add_filter( 'wpsf_show_save_changes_button_iconic_cffv', '__return_false' );
		add_filter( 'wpsf_show_tab_links_iconic_cffv', '__return_false' );
	}
}