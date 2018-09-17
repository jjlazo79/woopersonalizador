<?php

if ( class_exists( 'Iconic_Helper_Meta_Boxes' ) ) {
	return;
}

/**
 * Helper Class for dealing with meta boxes and fields
 *
 * @version 1.0.1
 * @author  James Kemp
 */
class Iconic_Helper_Meta_Boxes {
	/**
	 * Array of all post types registered by this plugin
	 *
	 * @since 1.0.0
	 * @var array $registered
	 */
	public $meta_boxes = array();

	/**
	 * Method: Add
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 */
	public function add( $options ) {
		$defaults = array(
			"id"            => false,
			"title"         => false,
			"callback"      => false,
			"screen"        => null,
			"context"       => 'advanced',
			"priority"      => 'default',
			"callback_args" => null,
		);

		$options = wp_parse_args( $options, $defaults );

		if ( $options['id'] ) {
			$this->meta_boxes[ $options['id'] ] = $options;
		}
	}

	/**
	 * Admin: Add our meta boxes on the add_meta_boxes hook
	 *
	 * @since 1.0.0
	 */
	public function run() {
		if ( ! empty( $this->meta_boxes ) ) {
			foreach ( $this->meta_boxes as $id => $meta_box ) {
				add_meta_box( $id, $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'], $meta_box['callback_args'] );
			}
		}
	}

	/**
	 * Admin: Display meta box
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post
	 */
	public function display_meta_box( $post ) {
		echo "contents";
	}
}