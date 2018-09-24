jQuery(document).ready(function() {
    //First hide elements
    jQuery('.variations .label label').hide();
    jQuery('.variations .value').hide();
    jQuery('.iconic-was-swatches__item').hide();
    // Show first level
    jQuery('.select-zone .seccion_imagen').click(function() {
        jQuery('.select-zone .seccion_imagen').removeClass('selected');
        jQuery(this).addClass('selected');
        jQuery('.variations .value').hide('slow');
        jQuery('.iconic-was-swatches__item').hide('slow');
        var elemento = jQuery(this).attr('id');
        jQuery('[for=' + elemento + ']').parent().next('.value').show('slow');
        jQuery('[for=' + elemento + ']').parent().next('.value').children('ul').children('.iconic-was-swatches__label:first').nextUntil('.iconic-was-swatches__label').css('display', 'inline-block');
    });
    // Show second level
    jQuery('.iconic-was-swatches__label').click(function() {
        // jQuery('.iconic-was-swatches__label').hide();
        jQuery('.iconic-was-swatches__item').hide();
        jQuery(this).nextUntil('.iconic-was-swatches__label').css('display', 'inline-block');
    });
    // Get selected values and draw in rigth place
    jQuery(document).on('change', '.variations select', function() {
        var selectID = jQuery(this).attr('id'),
            selectedAttributte = jQuery(this).text(),
            selectedValue = jQuery(this).val(),
            CapitalValue = selectedValue.charAt(0).toUpperCase() + selectedValue.slice(1);
        console.log('Select id ' + selectID);
        console.log('Select attr ' + selectedAttributte);
        console.log('Selected value ' + selectedValue);

        jQuery('#js-selected-' + selectID).text(CapitalValue);
    });
    // Stamped text
    jQuery('.js-toggle-next').click(function() {
        jQuery(this).next('div').toggle('slow');
        jQuery(this).next('div').next('label').next('input').val('');
    });
    jQuery('.stamped-text-input').on('change', function() {
        if (!jQuery(this).val()) {
            jQuery('#js-stamped-text-selected-container').addClass('d-none');
        } else {
            jQuery('#js-stamped-text-selected-container').removeClass('d-none');
            jQuery('#js-stamped-text-selected').text(jQuery(this).val());
        }
    });
    // TO-DO move add to cart to rigth side
    // jQuery(".single_variation_wrap").appendTo("#js-ad-to-cart");
});