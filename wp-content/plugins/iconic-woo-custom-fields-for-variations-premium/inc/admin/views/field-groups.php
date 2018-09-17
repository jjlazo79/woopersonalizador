<?php $field_groups = $this->get_variation_field_groups(); ?>

<?php if ( ! empty( $field_groups ) ) { ?>

	<div class="iconic-cffv-field-groups">

		<?php $saved_data = get_post_meta( $variation->ID, 'iconic_cffv', true ); ?>

		<?php foreach ( $field_groups as $i => $field_group ) { ?>

			<?php if ( ! empty( $field_group['fields'] ) ) { ?>

				<div class="<?php echo implode( ' ', $field_group['class'] ); ?>">

					<p class="iconic-cffv-field-group__title"><strong><?php echo $field_group['title']; ?></strong></p>

					<?php foreach ( $field_group['fields'] as $field_data ) {
						$this->output_variaition_field( $field_data['data'], $loop, $variation, $field_group );
					} ?>

				</div>

			<?php } ?>

		<?php } ?>

	</div>

<?php } ?>