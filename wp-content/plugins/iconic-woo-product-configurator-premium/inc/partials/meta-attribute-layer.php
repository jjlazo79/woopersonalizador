<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$layer_id = Iconic_PC_Helpers::sanitise_str( $layer_id, $layer_data['name'] );

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
						$attValSlug = Iconic_PC_Helpers::sanitise_str( $value['att_val_slug'], $value['att_val_name'] );
						$default    = ( isset( $defaults[ $layer_id ] ) ) ? $defaults[ $layer_id ] : '';
						?>

						<option value="<?php echo $attValSlug; ?>" <?php echo selected( $default, $attValSlug, 0 ); ?>><?php echo $value['att_val_name']; ?></option>
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

			<?php if ( is_array( $layer_data['values'] ) ) { ?>

				<?php $tr_i = 0;
				foreach ( $layer_data['values'] as $value ) { ?>

					<?php
					$attValSlug = Iconic_PC_Helpers::sanitise_str( $value['att_val_slug'], $value['att_val_name'] );

					$rowName   = $value['att_val_name'];
					$fieldName = 'jckpc_images[' . $layer_id . '][' . $attValSlug . ']';

					$fieldId      = sprintf( '%s_%s_image', $layer_id, $attValSlug );
					$inventory_id = sprintf( '%s_%s_%d', $layer_id, $attValSlug, $post->ID );

					$defaultImageId  = ( isset( $value['att_val_id'] ) ) ? jckpc::get_default_image( $value['att_val_id'] ) : "";
					$selectedImageId = ( isset( $setImages[ $layer_id ][ $attValSlug ] ) && $setImages[ $layer_id ][ $attValSlug ] != "" ) ? $setImages[ $layer_id ][ $attValSlug ] : $defaultImageId;

					$popupTitle  = sprintf( __( 'Set image for when %s is %s', 'jckpc' ), esc_attr( $layer_data['name'] ), esc_attr( $value['att_val_name'] ) );
					$popupBtnTxt = __( 'Set Image', 'jckpc' );
					$btnText     = __( 'Add Image', 'jckpc' );
					$classes     = ( $tr_i % 2 == 0 ) ? 'alternate' : '';

					echo $this->image_upload_row( array(
						'row_name'          => $rowName,
						'field_name'        => $fieldName,
						'field_id'          => $fieldId,
						'selected_image_id' => $selectedImageId,
						'popup_title'       => $popupTitle,
						'popup_button_text' => $popupBtnTxt,
						'button_text'       => $btnText,
						'classes'           => array( $classes ),
						'show_inventory'    => true,
					) );

					unset( $defaultImageId, $rowName, $fieldName, $fieldId, $selectedImageId, $popupTitle, $popupBtnTxt, $btnText, $classes );

					?>

					<?php $tr_i ++;
				} ?>

			<?php } ?>

		</table>

	</div>
</div>