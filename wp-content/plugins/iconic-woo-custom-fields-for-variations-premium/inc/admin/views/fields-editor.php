<?php
global $post;

$fields = get_post_meta( $post->ID, 'iconic-cffv-field-data', true );
?>

<table class="wp-list-table widefat fixed striped iconic-cffv-fields" style="margin-top: 15px;">

	<thead>
	<tr>
		<th scope="col"><?php _e( 'Field Order', 'iconic-cffv' ); ?></th>

		<th scope="col"><?php _e( 'Field Label', 'iconic-cffv' ); ?></th>

		<th scope="col"><?php _e( 'Field Type', 'iconic-cffv' ); ?></th>
	</tr>
	</thead>

	<tfoot>
	<tr>
		<td colspan="3">
			<a href="javascript: void(0);" class="button button-primary iconic-cffv-edit-field"><?php _e( 'Add Field', 'iconic-cffv' ); ?></a>
		</td>
	</tr>
	</tfoot>

	<tbody>

	<?php if ( $fields && ! empty( $fields ) ) { ?>

		<?php foreach ( $fields as $i => $field_json ) { ?>

			<?php
			$field_json = stripcslashes( $field_json );
			$field_data = json_decode( $field_json, true );
			?>

			<tr class="iconic-cffv-fields__field" data-index="<?php echo $i; ?>">
				<td>
					<strong class="iconic-cffv-fields__field-order"><?php echo $i + 1; ?></strong>
					<input type="hidden" class="iconic-cffv-field-data" name="iconic-cffv-field-data[]" value="<?php echo filter_var( $field_json, FILTER_SANITIZE_SPECIAL_CHARS ); ?>">
				</td>

				<td class="title column-title has-row-actions column-primary page-title">
					<strong><a href="#" class="row-title iconic-cffv-edit-field iconic-cffv-field-label"><?php echo $field_data['label']; ?></a></strong>
					<div class="row-actions">
						<a href="javascript: void(0);" class="iconic-cffv-edit-field" title="Edit Field">Edit</a> |
						<span class="trash"><a href="javascript: void(0);" class="iconic-cffv-delete-field" title="Delete Field">Bin</a></span>
					</div>
				</td>

				<td class="iconic-cffv-field-type"><?php echo $field_data['type']; ?></td>
			</tr>

		<?php } ?>

	<?php } ?>

	</tbody>

</table>