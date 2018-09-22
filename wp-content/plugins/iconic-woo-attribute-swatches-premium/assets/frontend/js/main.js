(function( $, document ) {

	var iconic_was = {

		cache: function() {
			iconic_was.els = {};
			iconic_was.vars = {};

			// common vars
			iconic_was.vars.swatch_group_class = ".iconic-was-swatches";
			iconic_was.vars.swatch_class = ".iconic-was-swatch";
			iconic_was.vars.selected_class = "iconic-was-swatch--selected";
			iconic_was.vars.disabled_class = "iconic-was-swatch--disabled";
			iconic_was.vars.follow_class = "iconic-was-swatch--follow";
			iconic_was.vars.variations_form_class = ".variations_form";
			iconic_was.vars.attribute_labels_class = ".variations .label";
			iconic_was.vars.chosen_attribute_class = ".iconic-was-chosen-attribute";
			iconic_was.vars.attribute_selects_selector = ".variations select";
			iconic_was.vars.change_image_links_class = ".iconic-was-swatch--change-image";

		},

		on_ready: function() {

			// on ready stuff here
			iconic_was.cache();
			iconic_was.setup_swatches();
			iconic_was.setup_change_image_links();

		},

		/**
		 * Setup the swatches on the frontend.
		 */
		setup_swatches: function() {

			/**
			 * When a swatch is clicked
			 */
			$( document ).on( 'click', iconic_was.vars.swatch_class, function( event ) {
				var $swatch = $( this ),
					$form = $swatch.closest( iconic_was.vars.variations_form_class ),
					$swatch_wrapper = $swatch.closest( iconic_was.vars.swatch_group_class ),
					attribute = $swatch_wrapper.data( 'attribute' ),
					attribute_value = $swatch.data( 'attribute-value' ),
					attribute_value_name = $swatch.data( 'attribute-value-name' ),
					$select = $form.find( 'select[id="' + attribute.replace( 'attribute_', '' ) + '"]' ),
					select_name = $select.attr( 'name' ),
					$cell = $swatch.closest( '.value' ),
					$label_selected = $cell.prev( '.label' ).find( iconic_was.vars.chosen_attribute_class ),
					is_visual = $swatch.hasClass( 'iconic-was-swatch--colour-swatch' ) || $swatch.hasClass( 'iconic-was-swatch--image-swatch' ),
					selected = iconic_was.get_current_values( $form ),
					reselect_values = false;

				if ( $swatch.hasClass( iconic_was.vars.follow_class ) ) {
					return true;
				}

				// do nothing if swatch is disabled
				if ( $swatch.hasClass( iconic_was.vars.disabled_class ) ) {
					iconic_was.reset_form( event, $form );
					delete selected[ select_name ];
					reselect_values = true;
				}

				// trigger focusin on the select field to run WooCommerce triggers
				// this refreshes the select field with available options
				$select.trigger( 'focusin' );

				// deselect if swatch is already selected
				if ( $swatch.hasClass( iconic_was.vars.selected_class ) ) {
					$swatch.removeClass( iconic_was.vars.selected_class );
					iconic_was.select_value( $select, false );
					$label_selected.text( '' );

					return;
				}

				var $is_ajax_variations = $( iconic_was.vars.variations_form_class ).data( 'product_variations' ) === false,
					$option_selector = '[value="' + iconic_was.esc_double_quotes( attribute_value ) + '"]';

				if ( ! $is_ajax_variations ) {
					$option_selector = '.enabled' + $option_selector;
				}

				// if the select field has the value we want still, select it
				if ( $select.find( $option_selector ).length > 0 ) {
					iconic_was.deselect_swatch_group( $form, attribute );
					$swatch.addClass( iconic_was.vars.selected_class );
					iconic_was.select_value( $select, attribute_value );
					$label_selected.text( attribute_value_name );

					if ( reselect_values ) {
						iconic_was.select_values( $form, selected );
					}

					return;
				}

				return false;
			} );

			/**
			 * Trigger focusin on the select field to run WooCommerce triggers
			 * this refreshes the select field with available options
			 */
			$( document ).on( 'mouseenter mouseleave', iconic_was.vars.swatch_class, function( e ) {
				var $select = $( this ).closest( iconic_was.vars.swatch_group_class ).next( 'div' ).find( 'select' );

				if ( $select.length <= 0 ) {
					return;
				}

				$select.trigger( 'focusin' );
			} );

			/**
			 * When select fields are updated to reflect available atts
			 */
			$( document ).on( 'woocommerce_update_variation_values', iconic_was.vars.variations_form_class, function() {
				var $form = $( this ),
					$selects = $form.find( 'select' );

				$selects.each( function( index ) {
					var $select = $( this ),
						$options = $select.find( 'option' ),
						attribute = $select.data( 'attribute_name' ),
						$swatch_group = $form.find( iconic_was.vars.swatch_group_class + '[data-attribute="' + iconic_was.esc_double_quotes( attribute ) + '"]' );

					$swatch_group.find( iconic_was.vars.swatch_class ).addClass( iconic_was.vars.disabled_class );

					$options.each( function( index, option ) {
						var $option = $( option ),
						    attribute_value = $option.val(),
							$swatch = $swatch_group.find( '[data-attribute-value="' + iconic_was.esc_double_quotes( attribute_value ) + '"]' );

						if ( ! $option.hasClass( 'enabled' ) ) {
							$swatch.removeClass( iconic_was.vars.selected_class );
							return;
						}

						$swatch.removeClass( iconic_was.vars.disabled_class );
					} );
				} );
			} );

			/**
			 * When select fields change
			 */
			$( document ).on( 'change', iconic_was.vars.attribute_selects_selector, function() {
				iconic_was.change_label( $( this ) );
			} );

			/**
			 * When form data is reset
			 */
			$( document ).on( 'click', '.reset_variations', function( event ) {
				var $form = $( this ).closest( iconic_was.vars.variations_form_class );

				iconic_was.reset_form( event, $form );
			} );

			/**
			 * On page load
			 */
			iconic_was.swatches_on_load();
		},

		/**
		 * Selected values from array
		 *
		 * @param element $form
		 * @param array values
		 */
		select_values: function( $form, values ) {
			var $selects = $form.find( 'select' );

			if ( $selects.length <= 0 ) {
				return false;
			}

			$selects.each( function( index, select ) {
				var $select = $( this ),
					name = $select.attr( 'name' );

				if ( typeof values[ name ] === "undefined" ) {
					return;
				}

				var $option = $select.find( 'option[value="' + iconic_was.esc_double_quotes( values[ name ] ) + '"]' );

				if ( $option.length <= 0 ) {
					return;
				}

				if ( ! $option.hasClass( 'enabled' ) ) {
					return;
				}

				iconic_was.select_value( $select, values[ name ] );

				$form.find( iconic_was.vars.swatch_class + '[data-attribute-value="' + iconic_was.esc_double_quotes( values[ name ] ) + '"]' ).click();
			} );
		},

		/**
		 * Reset variations form
		 *
		 * @param obj event
		 * @param element $form
		 */
		reset_form: function( event, $form ) {
			event.preventDefault();

			$form
				.find( iconic_was.vars.attribute_selects_selector )
				.find( "option" ).prop( "selected", false ).end()
				.change().end()
				.find( iconic_was.vars.swatch_class ).removeClass( iconic_was.vars.selected_class ).end()
				.find( iconic_was.vars.attribute_labels_class + ' ' + iconic_was.vars.chosen_attribute_class ).text( '' ).end()
				.trigger( 'reset_data' );

			iconic_was.deselect_all_swatch_groups( $form );
		},

		/**
		 * Get currently selected values
		 *
		 * @param element $form
		 */
		get_current_values: function( $form ) {
			var values = {},
				$selects = $form.find( 'select' );

			if ( $selects.length <= 0 ) {
				return false;
			}

			$selects.each( function() {

				var $select = $( this ),
					name = $select.attr( 'name' ),
					value = $select.val();

				if ( ! value || value === "" ) {
					return;
				}

				values[ name ] = value;

			} );

			return values;
		},

		/**
		 * Deselect a group of swatches
		 *
		 * @param element $form
		 * @param str attribute
		 */
		deselect_swatch_group: function( $form, attribute ) {
			$form.find( '[data-attribute="' + iconic_was.esc_double_quotes( attribute ) + '"] ' + iconic_was.vars.swatch_class ).removeClass( iconic_was.vars.selected_class );
		},

		/**
		 * Deselect all swatches
		 *
		 * @param element $form
		 */
		deselect_all_swatch_groups: function( $form ) {
			$form.find( iconic_was.vars.swatch_class ).removeClass( iconic_was.vars.selected_class );
		},

		/**
		 * Trigger swatch selections on load
		 */
		swatches_on_load: function() {
			var $selected_swatches = $( '.' + iconic_was.vars.selected_class );

			if ( $selected_swatches.length <= 0 ) {
				return;
			}

			$selected_swatches.each( function() {

				var $swatch = $( this ),
					$cell = $swatch.closest( '.value' ),
					$select = $cell.find( 'select' );

				iconic_was.change_label( $select );

			} );
		},

		/**
		 * Helper: Select value
		 *
		 * @param $select
		 * @param value
		 */
		select_value: function( $select, value ) {
			$select.val( value ).change();
		},

		/**
		 * Change selected label
		 */
		change_label: function( $select ) {
			var value = $select.val(),
				attribute_value_name = $select.find( 'option[value="' + iconic_was.esc_double_quotes( value ) + '"]' ).text(),
				$cell = $select.closest( '.value' ),
				$label_selected = $cell.prev( '.label' ).find( iconic_was.vars.chosen_attribute_class );

			if ( value === "" || typeof attribute_value_name === "undefined" ) {
				return;
			}

			$label_selected.text( attribute_value_name );
		},

		/**
		 * Setup change image links
		 */
		setup_change_image_links: function() {
			$( document ).on( 'click', iconic_was.vars.change_image_links_class, function() {

				var $link = $( this ),
					src = $link.attr( 'href' ),
					srcset = $link.data( 'srcset' ),
					sizes = $link.data( 'sizes' ),
					$parent = $link.closest( '.product' ),
					$main_image = $parent.find( 'a img:first' );

				$main_image
					.attr( 'src', src )
					.attr( 'srcset', srcset )
					.attr( 'sizes', sizes );

				return false;

			} );
		},

		/**
		 * Escape double quotes.
		 *
		 * @return string
		 */
		esc_double_quotes: function( string ) {
			return String( string ).replace( /"/g, '\\\"' );
		}

	};

	$( document ).ready( iconic_was.on_ready() );

}( jQuery, document ));