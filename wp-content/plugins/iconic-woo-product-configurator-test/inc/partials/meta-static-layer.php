<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php
if ( ! empty( $blank ) ) {
	$layer_id            = 'jckpc-static-{{data.index}}';
	$layer_data['index'] = "{{data.index}}";
	$selected_image_id   = false;
} else {
	$selected_image_id  = ! empty( $layer_data['image_id'] ) ? $layer_data['image_id'] : false;
	$selected_image_src = $selected_image_id ? wp_get_attachment_image_src( $selected_image_id, 'thumbnail' ) : false;
	$selected_image_url = $selected_image_src ? $selected_image_src[0] : false;
}
?>

<?php if ( ! empty( $blank ) ) { ?>
	<script type="text/html" id="tmpl-jckpc-static-layer">
		<?php } ?>

		<div class="jckpc-layer-options custom_tab_options" data-layer-id="<?php echo $layer_id; ?>" data-static-layer-index="<?php echo $layer_data['index']; ?>">

			<h2 class="jckpc-layer-options__title jckpc-layer-options__title--collapse">
				<i class="jckpc-layer-options__handle"></i>
				<?php _e( 'Static Layer', 'jckpc' ); ?>
				<span class="jckpc-layer-options__toggle toggle-indicator"></span>
				<a href="javascript: void(0);" class="jckpc-layer-options__remove"><?php _e( 'Remove', 'jckpc' ); ?></a>
			</h2>

			<div class="jckpc-layer-options__content-wrapper">

				<table class="jckpc-layer-options__table widefat fixed">

					<thead>
					<tr>
						<th><?php _e( 'Image', 'jckpc' ); ?></th>
					</tr>
					</thead>

					<tbody>
					<tr class="alternate uploader">
						<td>
							<input type="hidden" name="jckpc_images[<?php echo $layer_id; ?>]" id="<?php echo $layer_id; ?>" value="<?php echo $selected_image_id; ?>" />
							<div id="<?php echo $layer_id; ?>_thumbwrap" class="jckpc_attthumb jckpc-layer-options__thumbnail">
								<?php if ( $selected_image_id ) { ?>
									<img src="<?php echo $selected_image_url; ?>" width="50" height="50">
								<?php } ?>
								<a href="#" class="jckpc-image-button jckpc-image-button--remove" data-uploader_field="#<?php echo $layer_id; ?>"><?php _e( 'Remove Image', 'jckpc' ); ?></a>
								<a href="#" class="jckpc-image-button jckpc-image-button--upload" id="<?php echo $layer_id; ?>_button" data-uploader_title="Static Layer" data-uploader_button_text="Add" data-uploader_field="#<?php echo $layer_id; ?>"><?php _e( 'Add Image', 'jckpc' ); ?></a>
							</div>
						</td>
					</tr>
					</tbody>

				</table>

			</div>

		</div>

		<?php if ( ! empty( $blank ) ) { ?>
	</script>
<?php } ?>