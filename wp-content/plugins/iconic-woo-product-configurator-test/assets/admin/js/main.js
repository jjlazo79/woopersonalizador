/**
 * Scripts for the attribute add/edit pages
 */

(function($, document) {

    var iconic_pc_attribute = {

        cache: function() {
            iconic_pc_attribute.els = {};
            iconic_pc_attribute.vars = {};

            // common elements
            iconic_pc_attribute.els.upload = $('.jckpc-attribute-image__upload');
            iconic_pc_attribute.els.remove = $('.jckpc-attribute-image__remove');

        },

        on_ready: function() {

            // on ready stuff here
            iconic_pc_attribute.cache();
            iconic_pc_attribute.setup_image_fields();

        },

        /**
         * Setup image swatch fields
         */
        setup_image_fields: function() {

            // Uploading files
            var file_frame;

            iconic_pc_attribute.els.upload.on('click', function( event ){

                event.preventDefault();

                var $image_upload = $(this),
                    $image_wrapper = $image_upload.closest('.jckpc-attribute-image'),
                    $image_field = $image_wrapper.find('.jckpc-attribute-image__field'),
                    $image_preview = $image_wrapper.find('.jckpc-attribute-image__preview'),
                    $image_remove = $image_wrapper.find('.jckpc-attribute-image__remove');

                // Create the media frame.
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: $( this ).data( 'title' ),
                    button: {
                        text: $( this ).data( 'button-text' ),
                    },
                    multiple: false  // Set to true to allow multiple files to be selected
                });

                // When an image is selected, run a callback.
                file_frame.on( 'select', function() {

                    // We set multiple to false so only get one image from the uploader
                    attachment = file_frame.state().get('selection').first().toJSON();
                    attachment_url = typeof attachment.sizes.thumbnail !== "undefined" ? attachment.sizes.thumbnail.url : attachment.url;

                    $image_field.val( attachment.id );
                    $image_preview.html('<img src="'+attachment_url+'" class="attachment-thumbnail size-thumbnail">');
                    $image_upload.addClass('jckpc-attribute-image__upload--edit');
                    $image_remove.show();

                });

                // Finally, open the modal
                file_frame.open();

            });

            iconic_pc_attribute.els.remove.on('click', function( event ){

                event.preventDefault();

                var $image_wrapper = $(this).closest('.jckpc-attribute-image'),
                    $image_field = $image_wrapper.find('.jckpc-attribute-image__field'),
                    $image_preview = $image_wrapper.find('.jckpc-attribute-image__preview'),
                    $image_upload = $image_wrapper.find('.jckpc-attribute-image__upload');

                $image_field.val('');
                $image_preview.html('');
                $image_upload.removeClass('jckpc-attribute-image__upload--edit');
                $(this).hide();


            });

        }

    };

	$(document).ready( iconic_pc_attribute.on_ready() );

}(jQuery, document));
/**
 * Scripts for the attribute add/edit pages
 */

(function( $, document ) {

	var iconic_pc_product = {

		cache: function() {
			iconic_pc_product.els = {};
			iconic_pc_product.vars = {};
			iconic_pc_product.tmpl = {};

			iconic_pc_product.vars.file_frame = false;
			iconic_pc_product.vars.upload_class = '.jckpc-image-button--upload';
			iconic_pc_product.vars.remove_class = '.jckpc-image-button--remove';
			iconic_pc_product.vars.collapse_class = '.jckpc-layer-options__title--collapse';
			iconic_pc_product.vars.conditional_group_class = '.iconic-pc-conditional-group';
			iconic_pc_product.vars.add_conditional_group_button_class = '.iconic-pc-add-conditional-group';
			iconic_pc_product.vars.add_conditional_rule_button_class = '.iconic-pc-conditional-group__add-rule';
			iconic_pc_product.vars.remove_conditional_group_class = '.iconic-pc-conditional-group__remove';
			iconic_pc_product.vars.remove_conditional_rule_class = '.iconic-pc-conditional-group__rule-remove';
			iconic_pc_product.vars.conditional_rule_class = '.iconic-pc-conditional-group__rule';

			iconic_pc_product.els.add_static_layer = $( '#jckpc-add-static-layer' );

			iconic_pc_product.els.sort_order_input = $( '#jckpc_sort_order' );
			iconic_pc_product.els.sortable_items = $( '#jckpc_sortable' );

			// templates
			iconic_pc_product.tmpl.static_layer = wp.template( 'jckpc-static-layer' );

		},

		on_ready: function() {
			// on ready stuff here
			iconic_pc_product.cache();
			iconic_pc_product.setup_image_fields();
			iconic_pc_product.setup_sorting();
			iconic_pc_product.setup_collapse();
			iconic_pc_product.setup_static_layers();
			iconic_pc_product.setup_conditional_groups();
		},

		/**
		 * Setup image swatch fields
		 */
		setup_image_fields: function() {

			$( document ).on( 'click', iconic_pc_product.vars.upload_class, function( event ) {

				event.preventDefault();

				var $theBtn = $( this );

				// Create the media frame.
				iconic_pc_product.vars.file_frame = wp.media.frames.file_frame = wp.media( {
					title: $theBtn.attr( 'data-uploader_title' ),
					button: {
						text: $theBtn.attr( 'data-uploader_button_text' ),
					},
					multiple: false,
					library: {
						type: 'image/png'
					}
				} );

				// When an image is selected, run a callback.
				iconic_pc_product.vars.file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					var attachment = iconic_pc_product.vars.file_frame.state().get( 'selection' ).first().toJSON();

					if ( attachment.mime !== 'image/png' ) {
						alert( jckpc_vars.i18n.png_only );
						return;
					}

					var $theFiled = $( $theBtn.attr( 'data-uploader_field' ) );
					var $theThumbWrap = $( $theBtn.attr( 'data-uploader_field' ) + '_thumbwrap' );
					var image_src = typeof attachment.sizes.thumbnail !== "undefined" ? attachment.sizes.thumbnail.url : attachment.url;

					$theFiled.val( attachment.id );
					$theThumbWrap.prepend( '<img src="' + image_src + '" width="80" height="80" />' );

				} );

				// Finally, open the modal
				iconic_pc_product.vars.file_frame.open();
			} );

			$( document ).on( 'click', iconic_pc_product.vars.remove_class, function() {
				var $theBtn = $( this );
				var $imgField = $theBtn.attr( 'data-uploader_field' );
				$( $imgField ).val( '' );
				$( $imgField + '_thumbwrap img' ).remove();
				return false;
			} );

		},

		/**
		 * Setup sorting of layers
		 */
		setup_sorting: function() {

			iconic_pc_product.els.sortable_items.sortable( {
				revert: true,
				handle: '.jckpc-layer-options__handle',
				update: function( event, ui ) {
					iconic_pc_product.update_sort_field();
				}
			} );

		},

		/**
		 * Update sort field
		 */
		update_sort_field: function() {

			if ( iconic_pc_product.els.sort_order_input.length <= 0 ) {
				return;
			}

			var $sortable_items = iconic_pc_product.els.sortable_items.find( '.jckpc-layer-options' ),
				the_order = [];

			$sortable_items.each( function() {
				var attr_slug = $( this ).attr( 'data-layer-id' );
				the_order.push( attr_slug );
			} );

			iconic_pc_product.els.sort_order_input.val( the_order.join( ',' ) );

		},

		/**
		 * Setup collapsable layers
		 */
		setup_collapse: function() {
			$( document ).on( 'click', iconic_pc_product.vars.collapse_class, function() {
				var $title = $( this ),
					$toggle = $title.find( '.jckpc-layer-options__toggle' ),
					$layer = $title.closest( '.jckpc-layer-options' ),
					$content = $layer.find( '.jckpc-layer-options__content-wrapper' );

				$content.toggle();
				$toggle.toggleClass( 'jckpc-layer-options__toggle--collapsed' );
			} );
		},

		/**
		 * Setup static layers
		 */
		setup_static_layers: function() {

			iconic_pc_product.els.add_static_layer.on( 'click', function() {

				$( '#jckpc_sortable' ).prepend( iconic_pc_product.tmpl.static_layer( { index: iconic_pc_product.get_highest_static_layer_index() + 1 } ) );

				iconic_pc_product.update_sort_field();

			} );

			$( document ).on( 'click', '.jckpc-layer-options__remove', function() {

				$( this ).closest( '.jckpc-layer-options' ).remove();

				iconic_pc_product.update_sort_field();

			} );

		},

		/**
		 * Get highest static layer index
		 */
		get_highest_static_layer_index: function() {
			var num = $( "[data-static-layer-index]" ).map( function() {
				return $( this ).data( 'static-layer-index' );
			} ).get();

			var highest_index = Math.max.apply( Math, num );

			highest_index = isFinite( highest_index ) ? highest_index : - 1;

			return highest_index;
		},

		/**
		 * Setup conditional layers.
		 */
		setup_conditional_groups: function() {
			/**
			 * Add conditional group.
			 */
			$( document.body ).on( 'click', iconic_pc_product.vars.add_conditional_group_button_class, function( e ) {
				e.preventDefault();

				var $button = $( this ),
					original_data = $button.data( 'iconic-pc-add-conditional-group' ),
					data = original_data;

				$button.attr( 'disabled', true );

				if ( typeof data === 'undefined' ) {
					return;
				}

				data.action = 'iconic_pc_get_conditional_group';
				data.nonce = jckpc_vars.nonce;

				$.post( ajaxurl, data, function( response ) {
					$button.attr( 'disabled', false );

					if ( ! response.success ) {
						return;
					}

					original_data.condition_id = parseInt( original_data.condition_id ) + 1;

					$button.before( response.data.html ).data( 'iconic-pc-add-conditional-group', original_data );
				} );
			} );

			/**
			 * Add conditional rule.
			 */
			$( document.body ).on( 'click', iconic_pc_product.vars.add_conditional_rule_button_class, function( e ) {
				e.preventDefault();

				var $button = $( this ),
					$rules = $button.closest( '.iconic-pc-conditional-group__rules' ),
					$rule = $rules.find( '.iconic-pc-conditional-group__rule' ).first().clone(),
					$rule_selects = $rule.find( 'select' ),
					rule_index = parseInt( $button.data( 'iconic-pc-rule-id' ) );

				$rule_selects.val( '' );

				$rule_selects.each( function( index, rule_select ) {
					var $rule_select = $( rule_select ),
						name = $rule_select.attr( 'name' ),
						new_name = name.replace( '[rules][0]', '[rules][' + rule_index + ']' );

					$rule_select.attr( 'name', new_name );
					$button.data( 'iconic-pc-rule-id', rule_index + 1 );
				} );

				$rules.find( 'tbody' ).append( $rule );
			} );

			/**
			 * Remove conditional group.
			 */
			$( document.body ).on( 'click', iconic_pc_product.vars.remove_conditional_group_class, function( e ) {
				e.preventDefault();

				var $button = $( this ),
					$conditional_group = $button.closest( iconic_pc_product.vars.conditional_group_class );

				$conditional_group.remove();
			} );

			/**
			 * Remove conditional group.
			 */
			$( document.body ).on( 'click', iconic_pc_product.vars.remove_conditional_rule_class, function( e ) {
				e.preventDefault();

				var $button = $( this ),
					$rule = $button.closest( iconic_pc_product.vars.conditional_rule_class );

				$rule.remove();
			} );
		}
	};

	$( document ).ready( iconic_pc_product.on_ready() );

}( jQuery, document ));