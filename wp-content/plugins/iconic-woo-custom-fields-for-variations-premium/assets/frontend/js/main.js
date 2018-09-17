(function($, document) {

    var iconic_cffv = {

        cache: function() {
            iconic_cffv.els = {};
            iconic_cffv.vars = {};
            iconic_cffv.tmpl = {};

            // common vars
            //iconic_cffv.vars.a_variable = 1;

            // common elements
            iconic_cffv.els.variations_form = $('.variations_form');
            iconic_cffv.els.single_variation = iconic_cffv.els.variations_form.find( '.single_variation' );

            // templates
            iconic_cffv.tmpl.fields = wp.template( 'variation-fields' );

        },

        on_ready: function() {

            // on ready stuff here
            iconic_cffv.cache();
            iconic_cffv.setup_variations();

        },

        setup_variations: function() {

            $(document).on( 'found_variation', '.variations_form', function( event, variation ) {

                if ( variation.variation_is_visible ) {

                    var $variation_description = iconic_cffv.els.single_variation.find('.woocommerce-variation-description');

                    $template_html = iconic_cffv.tmpl.fields( {
    					variation:    variation
    				} );
    				// w3 total cache inline minification adds CDATA tags around our HTML (sigh)
    				$template_html = $template_html.replace( '/*<![CDATA[*/', '' );
    				$template_html = $template_html.replace( '/*]]>*/', '' );

    				if( $variation_description.length > 0 ) {
    				    $variation_description.after( $template_html );
    				} else {
        				iconic_cffv.els.single_variation.prepend( $template_html );
    				}

                }

            });

            iconic_cffv.els.single_variation.on( 'show_variation', function( event, variation, purchasable ){

                iconic_cffv.els.single_variation.stop().hide().height('').show();

            });

        }

    };

	$(document).ready( iconic_cffv.on_ready() );

}(jQuery, document));