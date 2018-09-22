<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_PC_Templates.
 *
 * @class    Iconic_PC_Templates
 * @version  1.0.0
 * @since    1.3.1
 * @author   Iconic
 */
class Iconic_PC_Templates {
	/**
	 * Output conditional layer.
	 *
	 * @param string $layer_id
	 * @param array  $attributes
	 * @param null   $conditionals
	 */
	public static function conditional_layer( $layer_id, $attributes, $conditionals = null, $condition_id = null ) {
		if ( is_null( $conditionals ) ) {
			$conditionals              = array();
			$conditionals[ $layer_id ] = array(
				array(
					'rules'  => array(),
					'values' => array(),
				),
			);
		}

		if ( ! isset( $conditionals[ $layer_id ] ) ) {
			return;
		}

		global $jckpc, $post;

		$field_name_prefix = 'jckpc_conditionals[' . $layer_id . ']';
		$current_attribute = str_replace( 'jckpc-', '', $layer_id );
		$layer_data        = isset( $attributes[ $current_attribute ] ) ? $attributes[ $current_attribute ] : null;
		?>

		<?php foreach ( $conditionals[ $layer_id ] as $index => $data ) { ?>
			<?php $condition_id = $index === 0 && ! is_null( $condition_id ) ? $condition_id : $index; ?>

			<?php if ( empty( $data['rules'] ) ) {
				$data['rules'][] = array(
					'attribute' => '',
					'condition' => '',
					'value'     => '',
				);
			} ?>

			<div class="iconic-pc-conditional-group">
				<div class="iconic-pc-conditional-group__header">
					<strong><?php echo $layer_data['name']; ?>: Conditional Group</strong>
					<a href="javascript: void(0);" class="iconic-pc-conditional-group__remove">Remove</a>
				</div>

				<table class="iconic-pc-conditional-group__rules">
					<thead>
					<tr>
						<td colspan="4">
							<p><?php _e( 'Use these images when *all* of the following conditions are met:', 'iconic-pc' ); ?></p>
						</td>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $data['rules'] as $rule_index => $rule ) { ?>
						<tr class="iconic-pc-conditional-group__rule">
							<td>
								<select name="<?php echo $field_name_prefix . '[' . $condition_id . '][rules][' . $rule_index . '][attribute]'; ?>" id="">
									<option value="">Attribute</option>
									<?php foreach ( $attributes as $attribute_slug => $attribute_data ) { ?>
										<?php if ( $attribute_slug === $current_attribute ) {
											continue;
										} ?>
										<?php $slug = Iconic_PC_Helpers::sanitise_str( $attribute_slug ); ?>
										<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $rule['attribute'] ); ?>><?php echo $attribute_data['name']; ?></option>
									<?php } ?>
								</select>
							</td>
							<td>
								<select name="<?php echo $field_name_prefix . '[' . $condition_id . '][rules][' . $rule_index . '][condition]'; ?>" id="">
									<option value="">Condition</option>
									<option value="is_equal_to" <?php selected( 'is_equal_to', $rule['condition'] ); ?>>is equal to</option>
									<option value="is_not_equal_to" <?php selected( 'is_not_equal_to', $rule['condition'] ); ?>>is not equal to</option>
								</select>
							</td>
							<td>
								<select name="<?php echo $field_name_prefix . '[' . $condition_id . '][rules][' . $rule_index . '][value]'; ?>" id="">
									<option value="">Value</option>
									<?php foreach ( $attributes as $attribute_slug => $attribute_data ) { ?>
										<?php if ( $attribute_slug === $current_attribute ) {
											continue;
										} ?>
										<optgroup label="<?php echo esc_attr( $attribute_data['name'] ); ?>">
											<?php foreach ( $attribute_data['values'] as $value_data ) { ?>
												<option value="<?php echo esc_attr( $value_data['att_val_slug'] ); ?>" <?php selected( $value_data['att_val_slug'], $rule['value'] ); ?>><?php echo $value_data['att_val_name']; ?></option>
											<?php } ?>
										</optgroup>
									<?php } ?>
								</select>
							</td>
							<td>
								<a href="" class="iconic-pc-conditional-group__rule-remove">Remove</a>
							</td>
						</tr>
					<?php } ?>
					</tbody>
					<tfoot>
					<tr>
						<td colspan="4">
							<button type="button" class="button iconic-pc-conditional-group__add-rule" data-iconic-pc-rule-id="<?php echo esc_attr( count( $data['rules'] ) ); ?>">Add Condition</button>
						</td>
					</tr>
					</tfoot>
				</table>

				<!--div style="clear: both; margin: 0 0 15px;">
					Use these images when
					<select name="" id="" style="float: none; display: inline-block">
						<option value="">all</option>
						<option value="">any</option>
					</select>
					of the rules are met
				</div-->

				<table class="widefat fixed iconic-pc-conditional-group__values">
					<thead>
					<tr>
						<th><?php _e( 'Image', 'jckpc' ); ?></th>
						<th><?php _e( 'Value', 'jckpc' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php if ( is_array( $layer_data['values'] ) ) {
						$tr_i = 0;
						foreach ( $layer_data['values'] as $value ) {
							$attribute_value_slug = Iconic_PC_Helpers::sanitise_str( $value['att_val_slug'], $value['att_val_name'] );

							$row_name   = $value['att_val_name'];
							$field_name = $field_name_prefix . '[' . $condition_id . '][values][' . $attribute_value_slug . ']';

							$field_id = sprintf( '%s_%s_image_condition_%d', $layer_id, $attribute_value_slug, $condition_id );
							// $inventory_id      = sprintf( '%s_%s_%d_condition_%d', $layer_id, $attribute_value_slug, $post->ID, $condition_id );
							$popup_title       = sprintf( __( 'Set conditional image for when %s is %s', 'jckpc' ), esc_attr( $layer_data['name'] ), esc_attr( $value['att_val_name'] ) );
							$popup_button_text = __( 'Set Image', 'jckpc' );
							$button_text       = __( 'Add Image', 'jckpc' );
							$classes           = ( $tr_i % 2 == 0 ) ? 'alternate' : '';
							$value             = isset( $data['values'][ $attribute_value_slug ] ) ? $data['values'][ $attribute_value_slug ] : '';

							echo $jckpc->image_upload_row( array(
								'row_name'          => $row_name,
								'field_name'        => $field_name,
								'field_id'          => $field_id,
								'selected_image_id' => $value,
								'popup_title'       => $popup_title,
								'popup_button_text' => $popup_button_text,
								'button_text'       => $button_text,
								'classes'           => array( $classes ),
								'show_inventory'    => false,
							) );

							unset( $row_name, $field_name, $field_id, $popup_title, $popup_button_text, $button_text, $classes, $value );

							$tr_i ++;
						}
					} ?>
					</tbody>
				</table>
			</div>
		<?php } ?>
		<?php
	}
}