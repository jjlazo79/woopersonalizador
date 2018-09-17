<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="jckpc-meta-toolbar">

    <?php woocommerce_wp_checkbox( array(
        'id' => 'jckpc_enabled',
        'label' => __('Enable Configurator', 'jckpc'),
        'description' => ''
    ) ); ?>

    <button type="button" id="jckpc-add-static-layer" class="button jckpc-meta-toolbar__button"><?php _e('Add Static Layer', 'jckpc'); ?></button>

</div>