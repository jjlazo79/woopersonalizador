jQuery(document).ready(function() {
    // jQuery('.variations .value').hide();
    // jQuery('.iconic-was-swatches__item').hide();
    // jQuery('.reset_variations').click(function() {
    //     jQuery('.variations .value').hide();
    //     jQuery('.iconic-was-swatches__item').hide();
    // });
    // jQuery('.iconic-was-swatches__label').click(function() {
    //     jQuery('.variations .value').hide();
    //     jQuery('.iconic-was-swatches__item').hide();
    //     jQuery(this).nextUntil('.iconic-was-swatches__label').css('display', 'inline-block');
    // });
    // jQuery('.label').click(function() {
    //     jQuery('.variations .value').hide();
    //     jQuery(this).next('.value').css('display', 'inline-block');
    // });



    //First hide elements
    jQuery('.variations .label label').hide();
    jQuery('.variations .value').hide();
    jQuery('.iconic-was-swatches__item').hide();
    // Show first level
    jQuery('.select-zone .seccion_imagen').click(function() {
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
});