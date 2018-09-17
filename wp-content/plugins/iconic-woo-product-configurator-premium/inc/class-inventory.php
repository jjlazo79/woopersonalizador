<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_PC_Inventory.
 *
 * @class    Iconic_PC_Inventory
 * @version  1.0.0
 * @since    1.2.3
 * @author   Iconic
 */
class Iconic_PC_Inventory {
	/**
	 * Inventory table name
	 *
	 * @var $inventory_table_name
	 * @access protected
	 */
	protected static $table_name = 'jckpc_inventory';

	/**
	 * DB version
	 *
	 * @var $db_version
	 * @access protected
	 */
	protected static $db_version = "1.0.0";

	/**
	 * Run.
	 */
	public static function run() {
		self::install_db();
		add_action( 'woocommerce_before_add_to_cart_form', array( __CLASS__, 'output_inventory_json' ) );
		add_action( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'add_to_cart_inventory_check' ), 1, 5 );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'process_product_meta' ), 10, 2 );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'order_status_changed' ), 10, 4 );
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'order_processed' ), 10, 3 );
		add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'checkout_validation' ), 10, 2 );
	}

	/**
	 * Get table name.
	 */
	protected static function get_table_name() {
		global $wpdb;

		return $wpdb->prefix . self::$table_name;
	}

	/**
	 * Install the inventory DB
	 */
	public static function install_db( $force = false ) {
		if ( ! is_admin() ) {
			return;
		}

		if ( $force ) {
			delete_option( 'jckpc_db_version' );
		}

		$installed_ver = get_option( 'jckpc_db_version' );
		$table_name    = self::get_table_name();

		if ( $installed_ver != self::$db_version ) {
			$sql = "CREATE TABLE {$table_name} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			product_id mediumint(9),
			att_val_id text,
			inventory mediumint(9),
			UNIQUE KEY id (id)
			);";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			update_option( 'jckpc_db_version', self::$db_version );
		}
	}

	/**
	 * Process product meta.
	 *
	 * @param int $post_id
	 */
	public static function process_product_meta( $post_id ) {
		if ( empty( $_POST['jckpc_inventory'] ) ) {
			return;
		}

		self::clear_inventory( $post_id );

		foreach ( $_POST['jckpc_inventory'] as $att_val_id => $inventory ) {
			$inventory = $inventory === "" ? "" : (int) $inventory;

			if ( $inventory < 0 || $inventory === "" ) {
				continue;
			}

			self::add_inventory( array(
				'product_id' => $post_id,
				'att_val_id' => $att_val_id,
				'inventory'  => $inventory,
			) );
		}
	}

	/**
	 * Clear Inventory
	 *
	 * Clear all inventory then re-save it, should help delete
	 * any unused rows
	 *
	 * @param int|bool $product_id
	 *
	 * @return void
	 */
	public static function clear_inventory( $product_id = false ) {
		global $wpdb;

		if ( ! $product_id ) {
			return;
		}

		$table_name = self::get_table_name();

		$wpdb->delete( $table_name, array( 'product_id' => $product_id ) );
	}

	/**
	 * Add Inventory
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public static function add_inventory( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'product_id' => false,
			'att_val_id' => '',
			'inventory'  => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! $args['product_id'] ) {
			return;
		}

		$table_name = self::get_table_name();

		$wpdb->insert(
			$table_name,
			array(
				'inventory'  => $args['inventory'],
				'product_id' => $args['product_id'],
				'att_val_id' => $args['att_val_id'],
			),
			array(
				'%d',
				'%d',
				'%s',
			)
		);
	}

	/**
	 * Update Inventory
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public static function update_inventory( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'product_id' => false,
			'att_val_id' => '',
			'inventory'  => false,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! $args['product_id'] ) {
			return;
		}

		$table_name = self::get_table_name();

		$wpdb->update(
			$table_name,
			array(
				'inventory' => $args['inventory'],
			),
			array(
				'product_id' => $args['product_id'],
				'att_val_id' => $args['att_val_id'],
			),
			array(
				'%d',
			),
			array(
				'%d',
				'%s',
			)
		);
	}

	/**
	 * Get Inventory
	 *
	 * @param array $args
	 *
	 * @return int|null
	 */
	public static function get_inventory( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'product_id' => false,
			'att_val_id' => '',
		);

		$args       = wp_parse_args( $args, $defaults );
		$table_name = self::get_table_name();

		$inventory_result = $wpdb->get_row( $wpdb->prepare( "
			SELECT * FROM $table_name 
			WHERE product_id = %d
			AND att_val_id = '%s'
		", $args['product_id'], $args['att_val_id'] ) );

		return ( $inventory_result ) ? (int) $inventory_result->inventory : null;
	}

	/**
	 * Check Inventory when adding to Cart
	 *
	 * @param bool       $passed
	 * @param int        $product_id
	 * @param int        $quantity
	 * @param int|bool   $variation_id
	 * @param array|bool $variations
	 *
	 * @return bool
	 */
	public static function add_to_cart_inventory_check( $passed, $product_id, $quantity, $variation_id = false, $variations = false ) {
		if ( ! $variation_id ) {
			return $passed;
		}

		if ( empty( $variations ) ) {
			return $passed;
		}

		$configurator_enabled = Iconic_PC_Product::is_configurator_enabled( $product_id );

		if ( ! $configurator_enabled ) {
			return $passed;
		}

		$cart_items  = WC()->cart->get_cart();
		$qty_in_cart = array();

		if ( ! empty( $cart_items ) ) {
			foreach ( $cart_items as $cart_item => $item_data ) {
				$qty_in_cart[ $item_data['product_id'] ] = 0;
				$qty_in_cart[ $item_data['product_id'] ] += $item_data['quantity'];
			}
		}

		$available_attributes = Iconic_PC_Product::get_attributes( $product_id );

		foreach ( $variations as $att_slug => $att_val ) {
			$prefixed_attribute_slug = str_replace( 'attribute_', '', $att_slug );

			if ( empty( $available_attributes[ $prefixed_attribute_slug ] ) ) {
				continue;
			}

			$att_name   = $available_attributes[ $prefixed_attribute_slug ]['name'];
			$att_val_id = Iconic_PC_Product::get_attribute_value_id( $product_id, $att_slug, $att_val );

			$inventory = self::get_inventory( array(
				'product_id' => $product_id,
				'att_val_id' => $att_val_id,
			) );

			if ( is_null( $inventory ) ) {
				continue;
			}

			if ( $inventory <= 0 ) {
				wc_add_notice( sprintf( __( 'Sorry, "%s - %s" is out of stock. Please make another selection.', 'jckpc' ), $att_name, $att_val ), 'error' );
				$passed = 0;
			} elseif ( isset( $qty_in_cart[ $product_id ] ) && $inventory <= $qty_in_cart[ $product_id ] ) {
				wc_add_notice( sprintf( __( 'Sorry, you\'ve got the last "%s - %s" in your cart; try a different selection.', 'jckpc' ), $att_name, $att_val ), 'error' );
				$passed = 0;
			} elseif ( $inventory < $quantity ) {
				wc_add_notice( sprintf( __( 'Sorry, there is only %d "%s - %s" left. Please adjust the quantity.', 'jckpc' ), $inventory, $att_name, $att_val ), 'error' );
				$passed = 0;
			}
		}

		return $passed;
	}

	/**
	 * Output Inventory JSON.
	 *
	 * @return void
	 */
	public static function output_inventory_json() {
		global $post;

		$configurator_enabled = Iconic_PC_Product::is_configurator_enabled( $post->ID );

		if ( ! $configurator_enabled ) {
			return;
		}

		$attributes = Iconic_PC_Product::get_attributes( $post->ID );

		if ( empty( $attributes ) ) {
			return;
		}

		$json_array = array();

		foreach ( $attributes as $att_slug => $att_data ) {
			$att_slug   = Iconic_PC_Helpers::sanitise_str( $att_slug, $att_data['name'] );
			$rel_select = str_replace( 'jckpc-', '', $att_slug );

			if ( ! is_array( $att_data['values'] ) ) {
				continue;
			}

			foreach ( $att_data['values'] as $value ) {
				$rel_value = $value['att_val_slug'];

				$att_val_slug = Iconic_PC_Helpers::sanitise_str( $value['att_val_slug'], $value['att_val_name'] );
				$att_val_id   = sprintf( '%s_%s', $att_slug, $att_val_slug );

				$inventory = self::get_inventory( array(
					'product_id' => $post->ID,
					'att_val_id' => $att_val_id,
				) );

				$json_array[ $rel_select ][ $rel_value ] = $inventory;
			}
		}

		echo '<script type="text/javascript">';
		echo '/* <![CDATA[ */ var jckpc_inventory = ' . json_encode( $json_array ) . ' /* ]]> */';
		echo '</script>';
	}

	/**
	 * Get inventory of order items.
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public static function get_order_items_inventory( $order ) {
		static $inventories = array();

		$order_id = $order->get_id();

		if ( isset( $inventories[ $order_id ] ) ) {
			return $inventories[ $order_id ];
		}

		$inventories[ $order_id ] = array();
		$order_items              = $order->get_items();

		if ( ! $order_items ) {
			return $inventories[ $order_id ];
		}

		foreach ( $order_items as $order_item_id => $order_item ) {
			$configurator_enabled = Iconic_PC_Product::is_configurator_enabled( $order_item['product_id'] );

			if ( ! $configurator_enabled ) {
				continue;
			}

			$attributes = Iconic_PC_Product::get_attributes( $order_item['product_id'] );

			if ( empty( $attributes ) ) {
				continue;
			}

			foreach ( $attributes as $att_slug => $att_data ) {
				if ( ! isset( $order_item[ $att_slug ] ) ) {
					continue;
				}

				$att_val_slug = Iconic_PC_Helpers::sanitise_str( $order_item[ $att_slug ] );
				$att_slug     = Iconic_PC_Helpers::sanitise_str( $att_slug );
				$att_val_id   = sprintf( '%s_%s', $att_slug, $att_val_slug );

				$inventory = self::get_inventory( array(
					'product_id' => $order_item['product_id'],
					'att_val_id' => $att_val_id,
				) );

				if ( is_null( $inventory ) ) {
					continue;
				}

				$inventories[ $order_id ][] = array(
					'product_id' => $order_item['product_id'],
					'att_val_id' => $att_val_id,
					'inventory'  => $inventory,
					'qty'        => (int) $order_item['qty'],
				);
			}
		}

		return $inventories[ $order_id ];
	}

	/**
	 * Order successfully processed - decrease stock.
	 *
	 * @param int      $order_id
	 * @param array    $posted_data
	 * @param WC_Order $order
	 *
	 * @return void
	 */
	public static function order_processed( $order_id, $posted_data, $order ) {
		$order_item_inventory = self::get_order_items_inventory( $order );

		if ( empty( $order_item_inventory ) ) {
			return;
		}

		foreach ( $order_item_inventory as $order_item_data ) {
			$order_item_data['inventory'] = self::modify_inventory_amount( $order_item_data['inventory'], $order_item_data['qty'] );

			self::update_inventory( array(
				'product_id' => $order_item_data['product_id'],
				'att_val_id' => $order_item_data['att_val_id'],
				'inventory'  => $order_item_data['inventory'],
			) );
		}
	}

	/**
	 * Order cancelled - increase stock.
	 *
	 * @param int      $order_id
	 * @param string   $status_from
	 * @param string   $status_to
	 * @param WC_Order $order
	 */
	public static function order_status_changed( $order_id, $status_from, $status_to, $order ) {
		$order_item_inventory = self::get_order_items_inventory( $order );

		if ( empty( $order_item_inventory ) ) {
			return;
		}

		$change   = false;
		$decrease = array( 'processing', 'pending', 'on-hold', 'completed' );
		$increase = array( 'cancelled', 'refunded', 'failed' );

		if ( in_array( $status_from, $increase ) && in_array( $status_to, $decrease ) ) {
			$change = 'decrease';
		}

		if ( in_array( $status_from, $decrease ) && in_array( $status_to, $increase ) ) {
			$change = 'increase';
		}

		if ( ! $change ) {
			return;
		}

		foreach ( $order_item_inventory as $order_item_data ) {
			$order_item_data['inventory'] = self::modify_inventory_amount( $order_item_data['inventory'], $order_item_data['qty'], $change );

			self::update_inventory( array(
				'product_id' => $order_item_data['product_id'],
				'att_val_id' => $order_item_data['att_val_id'],
				'inventory'  => $order_item_data['inventory'],
			) );
		}
	}

	/**
	 * Modify inventory amount.
	 *
	 * @param int    $inventory
	 * @param int    $qty
	 * @param string $type
	 *
	 * @return mixed
	 */
	public static function modify_inventory_amount( $inventory, $qty, $type = 'decrease' ) {
		if ( $type === 'decrease' ) {
			$inventory = $inventory <= 0 ? 0 : $inventory - $qty;
		} else {
			$inventory = $inventory + $qty;
		}

		return $inventory;
	}

	/**
	 * Check inventory at checkout again.
	 *
	 * @param  array    $data An array of posted data.
	 * @param  WP_Error $errors
	 */
	public static function checkout_validation( $data, $errors ) {
		$cart_items = WC()->cart->get_cart();

		if ( empty( $cart_items ) ) {
			return;
		}

		foreach ( $cart_items as $cart_item => $item_data ) {
			if ( empty( $item_data['variation'] ) ) {
				continue;
			}

			$configurator_enabled = Iconic_PC_Product::is_configurator_enabled( $item_data['product_id'] );

			if ( ! $configurator_enabled ) {
				continue;
			}

			foreach ( $item_data['variation'] as $attribute => $term ) {
				$attribute  = str_replace( 'attribute_', '', $attribute );
				$att_val_id = Iconic_PC_Product::get_attribute_value_id( $item_data['product_id'], $attribute, $term );

				$inventory = self::get_inventory( array(
					'product_id' => $item_data['product_id'],
					'att_val_id' => $att_val_id,
				) );

				if ( is_null( $inventory ) || $inventory > 0 ) {
					continue;
				}

				$product         = wc_get_product( $item_data['product_id'] );
				$product_title   = $product->get_title();
				$attribute_label = wc_attribute_label( $attribute, $product );
				$error           = sprintf( __( 'Sorry, "%s - %s" is now out of stock for %s. Please make another selection.', 'jckpc' ), $attribute_label, $term, $product_title );
				$errors->add( 'jckpc', $error );
			}
		}
	}
}