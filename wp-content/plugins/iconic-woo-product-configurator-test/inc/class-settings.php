<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_PC_Settings.
 *
 * @class    Iconic_PC_Settings
 * @version  1.0.0
 * @author   Iconic
 */
class Iconic_PC_Settings {
	/**
	 * Run.
	 */
	public static function run() {
		add_filter( 'iconic_woo_product_configurator_settings_validate', array( __CLASS__, 'tool_install_db' ), 10, 1 );
	}

	/**
	 *
	 */
	public static function tool_install_db( $settings ) {
		if ( ! isset( $_POST['iconic_pc_install_db'] ) ) {
			return $settings;
		}

		Iconic_PC_Inventory::install_db( true );

		add_settings_error( 'iconic_pc_install_db', esc_attr( 'jckpc-success' ), __( 'Tables successfully installed.', 'jckpc' ), 'updated' );
	}
}