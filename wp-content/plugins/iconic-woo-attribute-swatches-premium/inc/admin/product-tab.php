<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div id="<?php echo $iconic_was->slug ?>-options" class="panel wc-metaboxes-wrapper">

	<?php $attributes = $iconic_was->attributes_class()->get_variation_attributes_for_product( $_GET['post'] ); ?>

	<?php if ( ! empty( $attributes ) ) { ?>

		<div class="wc-metaboxes">

			<?php foreach ( $attributes as $attribute ) { ?>

				<?php
				$product_id   = $_GET['post'];
				$saved_values = $this->get_product_swatch_data_for_attribute( $product_id, $attribute['slug'] );
				$swatch_type  = isset( $saved_values['swatch_type'] ) ? $saved_values['swatch_type'] : "";
				$fields       = $iconic_was->attributes_class()->get_attribute_fields( array(
					'attribute_slug' => $attribute['slug'],
					'product_id'     => $product_id,
				) );
				?>

				<div data-taxonomy="<?php echo $attribute['slug']; ?>" data-product-id="<?php echo $_GET['post']; ?>" class="wc-metabox closed taxonomy <?php echo $attribute['slug']; ?> iconic-was-attribute-wrapper">

					<h3 class="attribute-name iconic-was-attribute-name">
						<div class="handlediv" title="Click to toggle" aria-expanded="true"></div>
						<strong><?php echo $attribute['label']; ?></strong>:
						<span class="iconic-was-swatch-type"><?php echo $iconic_was->swatches_class()
						                                                           ->get_swatch_label( $swatch_type, __( 'Default', 'iconic-was' ) ); ?></span>
					</h3>

					<div class="wc-metabox-content" style="display: none;">

						<table cellpadding="0" cellspacing="0" class="iconic-was-attributes">
							<tbody>
							<?php if ( $fields ) { ?>
								<?php foreach ( $fields as $key => $field ) { ?>

									<tr
										class="iconic-was-attribute-row iconic-was-attributes__<?php echo str_replace( '_', '-', $key ); ?> <?php echo implode( ' ', $field['class'] ); ?>"
										<?php if ( $field['condition'] ) { ?>
											data-condition="<?php echo is_array( $field['condition'] ) ? esc_js( json_encode( $field['condition'] ) ) : esc_attr( $field['condition'] ); ?>"
											data-match="<?php echo esc_js( json_encode( $field['match'] ) ); ?>"
										<?php } ?>
									>
										<td><?php echo $field['label']; ?></td>
										<td><?php echo $field['field']; ?></td>
									</tr>

								<?php } ?>
							<?php } ?>
							<tr class="iconic-was-attributes__swatch-options">
								<td colspan="2">

									<?php include( 'product-attribute-options.php' ); ?>

								</td>
							</tr>
							</tbody>
						</table>

					</div>

				</div>

			<?php } ?>

		</div>

	<?php } else { ?>

		<div class="inline notice woocommerce-message">
			<p><?php _e( 'Before you can modify swatches, you need to add some attributes for variations on the <strong>Attributes</strong> tab. Once you\'ve saved your product, you can come back here!', 'iconic-was' ); ?></p>
		</div>

	<?php } ?>

</div>