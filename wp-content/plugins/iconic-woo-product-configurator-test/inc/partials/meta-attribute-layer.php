<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$product_id = absint( filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT ) );
$layer_id   = Iconic_PC_Helpers::sanitise_str( $layer_id, $layer_data['name'] );
?>

<div class="jckpc-layer-options  options_group custom_tab_options" data-layer-id="<?php echo $layer_id; ?>">
	<h2 class="jckpc-layer-options__title jckpc-layer-options__title--collapse">
		<i class="jckpc-layer-options__handle"></i>
		<?php echo $layer_data['name']; ?>
		<span class="jckpc-layer-options__toggle toggle-indicator"></span>
	</h2>

	<div class="jckpc-layer-options__content-wrapper">
		<p class="form-field">
			<label><?php _e( 'Default Value', 'jckpc' ); ?></label>

			<?php $selectName = sprintf( 'jckpc_defaults[%s]', $layer_id ); ?>

			<select name="<?php echo $selectName; ?>">
				<option value=""><?php _e( 'Select a default...', 'jckpc' ); ?></option>

				<?php if ( is_array( $layer_data['values'] ) ) { ?>
					<?php foreach ( $layer_data['values'] as $value ) { ?>
						<?php
						$attribute_value_slug = Iconic_PC_Helpers::sanitise_str( $value['att_val_slug'], $value['att_val_name'] );
						$default              = ( isset( $defaults[ $layer_id ] ) ) ? $defaults[ $layer_id ] : '';
						?>

						<option value="<?php echo $attribute_value_slug; ?>" <?php echo selected( $default, $attribute_value_slug, 0 ); ?>><?php echo $value['att_val_name']; ?></option>
					<?php } ?>
				<?php } ?>
			</select>
		</p>

		<table class="widefat fixed">
			<thead>
			<tr>
				<th><?php _e( 'Image', 'jckpc' ); ?></th>
				<th><?php _e( 'Value', 'jckpc' ); ?></th>
				<th><?php _e( 'Stock Qty', 'jckpc' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( is_array( $layer_data['values'] ) ) {
				$tr_i = 0;
				foreach ( $layer_data['values'] as $value ) {
					$attribute_value_slug = Iconic_PC_Helpers::sanitise_str( $value['att_val_slug'], $value['att_val_name'] );

					$row_name   = $value['att_val_name'];
					$field_name = 'jckpc_images[' . $layer_id . '][' . $attribute_value_slug . ']';

					$field_id     = sprintf( '%s_%s_image', $layer_id, $attribute_value_slug );
					$inventory_id = sprintf( '%s_%s_%d', $layer_id, $attribute_value_slug, $post->ID );

					$default_image_id  = ( isset( $value['att_val_id'] ) ) ? jckpc::get_default_image( $value['att_val_id'] ) : "";
					$selected_image_id = ( isset( $set_images[ $layer_id ][ $attribute_value_slug ] ) && $set_images[ $layer_id ][ $attribute_value_slug ] != "" ) ? $set_images[ $layer_id ][ $attribute_value_slug ] : $default_image_id;

					$popup_title       = sprintf( __( 'Set image for when %s is %s', 'jckpc' ), esc_attr( $layer_data['name'] ), esc_attr( $value['att_val_name'] ) );
					$popup_button_text = __( 'Set Image', 'jckpc' );
					$button_text       = __( 'Add Image', 'jckpc' );
					$classes           = ( $tr_i % 2 == 0 ) ? 'alternate' : '';

					echo $this->image_upload_row( array(
						'row_name'          => $row_name,
						'field_name'        => $field_name,
						'field_id'          => $field_id,
						'selected_image_id' => $selected_image_id,
						'popup_title'       => $popup_title,
						'popup_button_text' => $popup_button_text,
						'button_text'       => $button_text,
						'classes'           => array( $classes ),
						'show_inventory'    => true,
					) );

					unset( $default_image_id, $row_name, $field_name, $field_id, $selected_image_id, $popup_title, $popup_button_text, $button_text, $classes );

					$tr_i ++;
				}
			} ?>
			</tbody>
		</table>

		<?php Iconic_PC_Templates::conditional_layer( $layer_id, $attributes, $conditionals ); ?>

		<button type="button" class="button iconic-pc-add-conditional-group" data-iconic-pc-add-conditional-group="<?php echo esc_attr( json_encode( array(
			'layer_id'     => $layer_id,
			'product_id'   => $product_id,
			'condition_id' => isset( $conditionals[ $layer_id ] ) ? count( $conditionals[ $layer_id ] ) : 0,
		) ) ); ?>">Add Conditional Group
		</button>

	</div>
</div>