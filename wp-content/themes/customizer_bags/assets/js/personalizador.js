jQuery(document).ready(function() {
    // jQuery('.variations .value').hide();
    // jQuery("[for=asa]").next().find('.value').show();

    // jQuery('.variations .value').each(function(i, el) {
    //     jQuery(el).click(function() {
    //         jQuery('.variations .value').hide('slow');
    //         jQuery(this).toggle('slow');
    //     });
    // });

    var swatch = jQuery('.swatch-control'),
        content = jQuery('.content-list');

    swatch.each(function(swatchgroup, swatchindex) {
        // console.log(swatchindex);
        // console.log(swatchgroup);
        var idEl = jQuery(this).attr('id');
        // console.log(idEl);

        var sortable = jQuery(this),
            groups = [];

        sortable.find("div.select-option").each(function() {
            var group = jQuery(this).data("value");

            if (jQuery.inArray(group, groups) === -1) {
                groups.push(group);
            }
        });

        var result = groups.map(function(s) {
            return s.split(/\s+/).slice(1, 3);
        });

        console.log(result);

        result.forEach(function(group, index) {
            // var liElements = sortable.find("div[data-value~='" + group[0] + "']");
            // console.log(liElements);
            // console.log(index);
            // groupUl = jQuery('#' + idEl).append(liElements);
            // groupUl.append('<div class="clear"></div>' +
            //     '<p>' + 'Nombre del selector' + '</p>');
            // content.append(groupUl);
            // console.log(groupUl);
        });

    });

});