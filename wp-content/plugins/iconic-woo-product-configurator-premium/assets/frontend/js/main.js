/*!
 * imagesLoaded PACKAGED v3.1.6
 * JavaScript is all like "You images are done yet or what?"
 * MIT License
 */

(function(){function e(){}function t(e,t){for(var n=e.length;n--;)if(e[n].listener===t)return n;return-1}function n(e){return function(){return this[e].apply(this,arguments)}}var i=e.prototype,r=this,o=r.EventEmitter;i.getListeners=function(e){var t,n,i=this._getEvents();if("object"==typeof e){t={};for(n in i)i.hasOwnProperty(n)&&e.test(n)&&(t[n]=i[n])}else t=i[e]||(i[e]=[]);return t},i.flattenListeners=function(e){var t,n=[];for(t=0;e.length>t;t+=1)n.push(e[t].listener);return n},i.getListenersAsObject=function(e){var t,n=this.getListeners(e);return n instanceof Array&&(t={},t[e]=n),t||n},i.addListener=function(e,n){var i,r=this.getListenersAsObject(e),o="object"==typeof n;for(i in r)r.hasOwnProperty(i)&&-1===t(r[i],n)&&r[i].push(o?n:{listener:n,once:!1});return this},i.on=n("addListener"),i.addOnceListener=function(e,t){return this.addListener(e,{listener:t,once:!0})},i.once=n("addOnceListener"),i.defineEvent=function(e){return this.getListeners(e),this},i.defineEvents=function(e){for(var t=0;e.length>t;t+=1)this.defineEvent(e[t]);return this},i.removeListener=function(e,n){var i,r,o=this.getListenersAsObject(e);for(r in o)o.hasOwnProperty(r)&&(i=t(o[r],n),-1!==i&&o[r].splice(i,1));return this},i.off=n("removeListener"),i.addListeners=function(e,t){return this.manipulateListeners(!1,e,t)},i.removeListeners=function(e,t){return this.manipulateListeners(!0,e,t)},i.manipulateListeners=function(e,t,n){var i,r,o=e?this.removeListener:this.addListener,s=e?this.removeListeners:this.addListeners;if("object"!=typeof t||t instanceof RegExp)for(i=n.length;i--;)o.call(this,t,n[i]);else for(i in t)t.hasOwnProperty(i)&&(r=t[i])&&("function"==typeof r?o.call(this,i,r):s.call(this,i,r));return this},i.removeEvent=function(e){var t,n=typeof e,i=this._getEvents();if("string"===n)delete i[e];else if("object"===n)for(t in i)i.hasOwnProperty(t)&&e.test(t)&&delete i[t];else delete this._events;return this},i.removeAllListeners=n("removeEvent"),i.emitEvent=function(e,t){var n,i,r,o,s=this.getListenersAsObject(e);for(r in s)if(s.hasOwnProperty(r))for(i=s[r].length;i--;)n=s[r][i],n.once===!0&&this.removeListener(e,n.listener),o=n.listener.apply(this,t||[]),o===this._getOnceReturnValue()&&this.removeListener(e,n.listener);return this},i.trigger=n("emitEvent"),i.emit=function(e){var t=Array.prototype.slice.call(arguments,1);return this.emitEvent(e,t)},i.setOnceReturnValue=function(e){return this._onceReturnValue=e,this},i._getOnceReturnValue=function(){return this.hasOwnProperty("_onceReturnValue")?this._onceReturnValue:!0},i._getEvents=function(){return this._events||(this._events={})},e.noConflict=function(){return r.EventEmitter=o,e},"function"==typeof define&&define.amd?define("eventEmitter/EventEmitter",[],function(){return e}):"object"==typeof module&&module.exports?module.exports=e:this.EventEmitter=e}).call(this),function(e){function t(t){var n=e.event;return n.target=n.target||n.srcElement||t,n}var n=document.documentElement,i=function(){};n.addEventListener?i=function(e,t,n){e.addEventListener(t,n,!1)}:n.attachEvent&&(i=function(e,n,i){e[n+i]=i.handleEvent?function(){var n=t(e);i.handleEvent.call(i,n)}:function(){var n=t(e);i.call(e,n)},e.attachEvent("on"+n,e[n+i])});var r=function(){};n.removeEventListener?r=function(e,t,n){e.removeEventListener(t,n,!1)}:n.detachEvent&&(r=function(e,t,n){e.detachEvent("on"+t,e[t+n]);try{delete e[t+n]}catch(i){e[t+n]=void 0}});var o={bind:i,unbind:r};"function"==typeof define&&define.amd?define("eventie/eventie",o):e.eventie=o}(this),function(e,t){"function"==typeof define&&define.amd?define(["eventEmitter/EventEmitter","eventie/eventie"],function(n,i){return t(e,n,i)}):"object"==typeof exports?module.exports=t(e,require("eventEmitter"),require("eventie")):e.imagesLoaded=t(e,e.EventEmitter,e.eventie)}(this,function(e,t,n){function i(e,t){for(var n in t)e[n]=t[n];return e}function r(e){return"[object Array]"===d.call(e)}function o(e){var t=[];if(r(e))t=e;else if("number"==typeof e.length)for(var n=0,i=e.length;i>n;n++)t.push(e[n]);else t.push(e);return t}function s(e,t,n){if(!(this instanceof s))return new s(e,t);"string"==typeof e&&(e=document.querySelectorAll(e)),this.elements=o(e),this.options=i({},this.options),"function"==typeof t?n=t:i(this.options,t),n&&this.on("always",n),this.getImages(),a&&(this.jqDeferred=new a.Deferred);var r=this;setTimeout(function(){r.check()})}function c(e){this.img=e}function f(e){this.src=e,v[e]=this}var a=e.jQuery,u=e.console,h=u!==void 0,d=Object.prototype.toString;s.prototype=new t,s.prototype.options={},s.prototype.getImages=function(){this.images=[];for(var e=0,t=this.elements.length;t>e;e++){var n=this.elements[e];"IMG"===n.nodeName&&this.addImage(n);var i=n.nodeType;if(i&&(1===i||9===i||11===i))for(var r=n.querySelectorAll("img"),o=0,s=r.length;s>o;o++){var c=r[o];this.addImage(c)}}},s.prototype.addImage=function(e){var t=new c(e);this.images.push(t)},s.prototype.check=function(){function e(e,r){return t.options.debug&&h&&u.log("confirm",e,r),t.progress(e),n++,n===i&&t.complete(),!0}var t=this,n=0,i=this.images.length;if(this.hasAnyBroken=!1,!i)return this.complete(),void 0;for(var r=0;i>r;r++){var o=this.images[r];o.on("confirm",e),o.check()}},s.prototype.progress=function(e){this.hasAnyBroken=this.hasAnyBroken||!e.isLoaded;var t=this;setTimeout(function(){t.emit("progress",t,e),t.jqDeferred&&t.jqDeferred.notify&&t.jqDeferred.notify(t,e)})},s.prototype.complete=function(){var e=this.hasAnyBroken?"fail":"done";this.isComplete=!0;var t=this;setTimeout(function(){if(t.emit(e,t),t.emit("always",t),t.jqDeferred){var n=t.hasAnyBroken?"reject":"resolve";t.jqDeferred[n](t)}})},a&&(a.fn.imagesLoaded=function(e,t){var n=new s(this,e,t);return n.jqDeferred.promise(a(this))}),c.prototype=new t,c.prototype.check=function(){var e=v[this.img.src]||new f(this.img.src);if(e.isConfirmed)return this.confirm(e.isLoaded,"cached was confirmed"),void 0;if(this.img.complete&&void 0!==this.img.naturalWidth)return this.confirm(0!==this.img.naturalWidth,"naturalWidth"),void 0;var t=this;e.on("confirm",function(e,n){return t.confirm(e.isLoaded,n),!0}),e.check()},c.prototype.confirm=function(e,t){this.isLoaded=e,this.emit("confirm",this,t)};var v={};return f.prototype=new t,f.prototype.check=function(){if(!this.isChecked){var e=new Image;n.bind(e,"load",this),n.bind(e,"error",this),e.src=this.src,this.isChecked=!0}},f.prototype.handleEvent=function(e){var t="on"+e.type;this[t]&&this[t](e)},f.prototype.onload=function(e){this.confirm(!0,"onload"),this.unbindProxyEvents(e)},f.prototype.onerror=function(e){this.confirm(!1,"onerror"),this.unbindProxyEvents(e)},f.prototype.confirm=function(e,t){this.isConfirmed=!0,this.isLoaded=e,this.emit("confirm",this,t)},f.prototype.unbindProxyEvents=function(e){n.unbind(e.target,"load",this),n.unbind(e.target,"error",this)},s});
(function( $, document ) {
		/* define all vars at the start */

		var $imgWrap = null,
			$loader = null,
			hasSwatches = $( "[data-attribute-name]" ).length > 0,
			hasConfigurator = $( '.iconic-pc-images' ).length > 0,
			hasWooThumbs = $( '.iconic-woothumbs-images' ).length > 0,
			$variationsForm = $( 'form.variations_form' ),
			varSelects = '.variations select',
			loading_class = 'iconic-pc-loader--loading',
			default_images = {},
			loaded_images = {},
			load_ids = {
				global: 0
			};

		// Compatibility with WooCommerce Variations Swatches and Photos Plugin
		if ( jQuery( '.variations_form.swatches' ).length ) { 
			var variationAttributesInputs = '.variations_form.swatches .variation_form_section input:hidden';
			varSelects += ', ' + variationAttributesInputs;
			hasSwatches = false;
			jQuery( variationAttributesInputs ).each(function () {
				var $input = jQuery(this);
				$input.attr( 'data-attribute-name', $input.attr( 'name' ) );
				
				$input.parent().find( '.select-option a' ).click( function () {
					setTimeout( function () {
						$input.trigger( 'change' );
					}, 200 );
				} );
			} );
		}

		/**
		 * Setup default images.
		 */
		function setupDefaultImages() {
			$imgWrap.find( 'div' ).each( function( index, div ) {
				var $div = $( div ),
					id = $div.attr( 'class' );

				default_images[ '.' + id ] = $div.html();
			} );
		}

		/**
		 * Sanitise string. Similar to generating a slug.
		 *
		 * @param str
		 * @return {*}
		 */
		function sanitise_str( str ) {
			if ( str && str !== "" ) {
				str = 'jckpc-' + str;
				str = str.replace( '/', '' )
					.replace( / +/g, '-' )
					.replace( /[^a-zA-Z0-9-_]/g, '-' )
					.replace( /(-)\1+/g, '-' )
					.replace( /(_)\1+/g, '_' );

				return str.toLowerCase();
			}

			return "";
		}

		/**
		 * Get layer ID.
		 *
		 * @param selectedAttName
		 * @return {string}
		 */
		function getLayerId( selectedAttName ) {
			var prefix = '.iconic-pc-image-';

			if ( selectedAttName.startsWith( prefix ) ) {
				return selectedAttName;
			}

			selectedAttName = selectedAttName.replace( 'attribute_', '' );

			return prefix + selectedAttName;
		}

		/**
		 * Get default image.
		 *
		 * @param layer_id
		 */
		function getDefaultImage( layer_id ) {
			return default_images[ layer_id ];
		}

		/**
		 * Clear layer.
		 *
		 * @param layer_id
		 */
		function clearLayer( layer_id ) {
			layer_id = layer_id.startsWith( "." ) ? layer_id : getLayerId( layer_id );

			var $img = getDefaultImage( layer_id );

			$( layer_id ).html( $img );
		}

		/**
		 * Load image layer.
		 *
		 * @param selectedVal
		 * @param selectedAtt
		 */
		function load_image_layer( selectedVal, selectedAtt ) {
			var product_id = $imgWrap.data( 'product-id' );

			selectedAtt = selectedAtt.replace( 'attribute_', '' );

			var layer_id = getLayerId( selectedAtt ),
				load_id = get_load_id( layer_id );

			if ( typeof loaded_images[ layer_id ] === 'undefined' ) {
				loaded_images[ layer_id ] = {};
			}

			if ( typeof loaded_images[ layer_id ][ selectedVal ] !== 'undefined' ) {
				update_image_layer( layer_id, loaded_images[ layer_id ][ selectedVal ], load_id );
				return;
			}

			set_loading( true );

			var ajaxargs = {
				'action': 'jckpc_get_image_layer',
				'nonce': jckpc.nonce,
				'prodid': product_id,
				'selectedVal': selectedVal,
				'selectedAtt': selectedAtt,
				'request_id': load_id
			};

			$.ajax( {
				url: jckpc.ajaxurl,
				data: ajaxargs,
				dataType: 'json',
				type: 'POST'
			} ).success( function( data ) {
				if ( data.response === 'success' ) {
					loaded_images[ layer_id ][ selectedVal ] = data.image;
					update_image_layer( layer_id, data.image, data.request_id );
				}
			} );
		}

		/**
		 * Set loading status.
		 *
		 * @param loading
		 */
		function set_loading( loading ) {
			if ( loading === 'clear' ) {
				load_ids.global = 0;
				$loader.removeClass( loading_class );
				return;
			}

			if ( loading ) {
				$loader.addClass( loading_class );
				load_ids.global ++;
				return;
			}

			if ( load_ids.global > 0 ) {
				load_ids.global --;
			}

			if ( load_ids.global === 0 ) {
				$loader.removeClass( loading_class );
			}
		}

		/**
		 * Update image layer.
		 *
		 * @param layer_id
		 * @param image
		 * @param request_id
		 */
		function update_image_layer( layer_id, image, request_id ) {
			var current_load_id = get_load_id( layer_id );

			set_loading( false );

			if ( request_id !== current_load_id ) {
				return;
			}

			preload_image( image, function( image ) {
				if ( image ) {
					$( layer_id ).html( image );
				} else {
					clearLayer( layer_id );
				}

				$( document.body ).trigger( 'iconic_pc_image_layer_updated' );
			} );
		}

		/**
		 * Preload image.
		 *
		 * @param image
		 * @param callback
		 */
		function preload_image( image, callback ) {
			if ( ! image ) {
				callback( image );
				return;
			}

			var $temp_images = $( '<div />' ).html( image );

			images_loaded( $temp_images, function() {
				$temp_images.remove();

				if ( typeof callback === 'function' ) {
					callback( image );
				}
			} );
		}

		/**
		 * Generate dynamic image url.
		 *
		 * @param productId
		 * @return {*|string|string|boolean}
		 */
		function generateImageUrl( productId ) {
			var url = jckpc.ajaxurl;

			url += '?action=jckpc_generate_image';
			url += '&prodid=' + productId;
			url += '&' + getSelectedAttributes( 'string' );

			return url;
			// http://iconic-plugins.local/wp-admin/admin-ajax.php?action=jckpc_generate_image&prodid=1177&attribute_strap=tan-leather&attribute_case=rose-gold&attribute_face=blue&attribute_pa_size=9&attribute_continents=anguilla
		}

		/**
		 * Get selected attributes.
		 *
		 * @param format
		 * @return {*}
		 */
		function getSelectedAttributes( format ) {
			format = format || 'array';

			var selected = {},
				$selects = $( ".variations select[name^='attribute_']" );

			$selects.each( function( index, select ) {
				var $select = $( select ),
					attribute = $select.data( 'attribute_name' ),
					value = $select.val();

				if ( value === '' ) {
					return;
				}

				selected[ attribute ] = value;
			} );

			if ( format === 'string' ) {
				return $.param( selected, true );
			}

			return selected;
		}

		/**
		 * Setup image switcher.
		 */
		function setupImgSwitcher() {
			if ( hasConfigurator ) {
				$( document ).on( 'change', varSelects, function() {
					var $selectField = $( this ),
						select_data = get_select_field_data( $selectField );

					increment_load_id( select_data.attribute );

					if ( select_data.value ) {
						load_image_layer( select_data.value, select_data.attribute );
					} else {
						clearLayer( select_data.attribute );
					}

					$( document.body ).trigger( 'iconic_pc_image_layer_updated' );
				} );

				$( document.body ).on( 'iconic_pc_image_layer_updated', function() {
					update_large_image();
				} );
			}
		}

		/**
		 * Increment load ID.
		 *
		 * @param attribute
		 */
		function increment_load_id( attribute ) {
			var layer_id = getLayerId( attribute );

			if ( ! get_load_id( layer_id ) ) {
				load_ids[ layer_id ] = 0;
			}

			load_ids[ layer_id ] ++;
		}

		/**
		 * Get current load ID.
		 *
		 * @param attribute
		 * @return {*}
		 */
		function get_load_id( attribute ) {
			var layer_id = getLayerId( attribute );

			if ( typeof load_ids[ layer_id ] === 'undefined' ) {
				return false;
			}

			return load_ids[ layer_id ];
		}

		/**
		 * Setup swatches.
		 */
		function setupSwatches() {
			if ( hasSwatches && hasConfigurator ) {
				$( '.swatch-anchor' ).on( 'click', function() {
					var $selectedSwatchAnchor = $( this ),
						$variationsForm = $selectedSwatchAnchor.closest( 'form' ),
						variationsMap = JSON.parse( $variationsForm.attr( 'data-variations_map' ) ),
						select = $selectedSwatchAnchor.closest( '.select' ),
						swatch = $selectedSwatchAnchor.closest( '.select-option' ),
						selectedAttName = select.attr( 'data-attribute-name' ),
						selectedValHash = swatch.attr( 'data-value' ),
						selectedAtt = sanitise_str( selectedAttName ),
						selectedVal = variationsMap[ selectedAttName ][ selectedValHash ];

					if ( ! swatch.hasClass( 'selected' ) ) {
						load_image_layer( selectedVal, selectedAtt );
					} else {
						clearLayer( selectedAtt );
					}

					$( document.body ).trigger( 'iconic_pc_image_layer_updated' );
				} );
			}
		}

		/**
		 * get swatch value.
		 *
		 * @param att_name
		 * @param att_val_hash
		 */
		function get_swatch_value( att_name, att_val_hash ) {
			var variationsMap = JSON.parse( $variationsForm.attr( 'data-variations_map' ) );

			return variationsMap[ att_name ][ att_val_hash ];
		}

		/**
		 * Reset layers.
		 */
		function resetLayers() {
			$( '#variations_clear' ).on( 'click', function() {
				$.each( default_images, function( layer_id, image ) {
					$imgWrap.find( layer_id ).html( image );
				} );
			} );
		}

		/**
		 * Setup inventory.
		 */
		function setupInventory() {
			$variationsForm.on( 'woocommerce_update_variation_values', function() {
				if ( typeof jckpc_inventory !== "undefined" ) {
					$.each( jckpc_inventory, function( attribute_name, values ) {
						var $select = $( '#' + attribute_name );

						$.each( values, function( attribute_option, inventory ) {
							var $option = $select.find( 'option[value="' + attribute_option + '"]' ),
								$va_picker = $( '.va-picker[data-attribute="' + attribute_name + '"][data-term="' + attribute_option + '"]' );

							if ( inventory !== "" && parseInt( inventory ) <= 0 ) {
								$option.attr( 'disabled', 'disabled' ).removeClass( 'enabled' );

								if ( $va_picker.length > 0 ) {
									$va_picker.hide();
								}
							}
						} );
					} );
				}
			} );

			// inventory for swatches plugin

			if ( hasSwatches && hasConfigurator && typeof jckpc_inventory !== "undefined" ) {
				var $attribute_fields = $( '[data-attribute-name]' );

				$attribute_fields.each( function() {
					var $element = $( this ),
						attribute_name = $element.attr( 'data-attribute-name' ),
						$options = null;

					if ( $element.is( "select" ) ) {
						$options = $element.find( 'option' );

						$options.each( function( index, option ) {

							var attribute_hash = $( option ).val();

							if ( attribute_hash !== "" ) {
								var attribute_value = get_swatch_value( attribute_name, attribute_hash ),
									attribute_name_formatted = attribute_name.replace( 'attribute_', '' ),
									attribute_value_formatted = attribute_value.replace( 'jckpc-', '' ),
									inventory = jckpc_inventory[ attribute_name_formatted ][ attribute_value_formatted ];

								if ( inventory !== "" && parseInt( inventory ) <= 0 ) {
									$( option ).remove();
								}
							}
						} );
					} else {
						$options = $element.find( '.select-option' );

						$options.each( function( index, option ) {
							var attribute_hash = $( option ).attr( 'data-value' );

							if ( attribute_hash !== "" ) {
								var attribute_value = get_swatch_value( attribute_name, attribute_hash ),
									attribute_name_formatted = attribute_name.replace( 'attribute_', '' ),
									attribute_value_formatted = attribute_value.replace( 'jckpc-', '' ),
									inventory = jckpc_inventory[ attribute_name_formatted ][ attribute_value_formatted ];

								if ( inventory !== "" && parseInt( inventory ) <= 0 ) {
									$( option ).remove();
								}
							}
						} );
					}
				} );
			}
		}

		/**
		 * Set global elements.
		 */
		function set_elements() {
			$imgWrap = $( '.iconic-pc-image-wrap' );
			$loader = $( '.iconic-pc-loading' );
		}

		/**
		 * Setup WooThumbs.
		 */
		function setupWooThumbs() {
			if ( ! hasWooThumbs ) {
				return;
			}

			$( document.body ).on( 'iconic_woothumbs_images_loaded', function( event, product_object ) {
				set_elements();
				set_loading( 'clear' );

				if ( typeof product_object === 'undefined' ) {
					return;
				}

				var $selects = product_object.variations_form.find( 'select' );

				if ( typeof $selects.length <= 0 ) {
					return;
				}

				$selects.each( function( index, select ) {
					var $select = $( select ),
						select_data = get_select_field_data( $select );

					if ( ! select_data.value || select_data.value.length <= 0 ) {
						return;
					}

					load_image_layer( select_data.value, select_data.attribute );
				} );
			} );

			$( document.body ).on( 'iconic_pc_image_layer_updated', function() {
				var $images =  $( '.iconic-woothumbs-images.slick-initialized' );

				if ( $images.length <= 0 ) {
					return;
				}

				$images.slick( 'slickGoTo', 0 );
				$images.trigger( 'init_zoom' );
			} );
		}

		/**
		 * Update large image.
		 */
		function update_large_image() {
			var product_id = parseInt( $( '.iconic-pc-image-wrap' ).data( 'product-id' ) ),
				url = generateImageUrl( product_id ),
				$zoom_img = $( '.iconic-pc-image-wrap .zoomImg' );

			$( '.iconic-pc-image-background' ).attr( 'data-large_image', url );

			// update default Woo zoom image.
			if ( $zoom_img.length > 0 ) {
				$( '.iconic-pc-image-zoom' ).attr( 'data-large_image', url );
				$zoom_img.attr( 'src', '' ).attr( 'src', url );
			}
		}

		/**
		 * Get select field data.
		 *
		 * @param $select
		 * @return {{attribute: null, value: null}}
		 */
		function get_select_field_data( $select ) {
			var data = {
				'attribute': null,
				'value': null
			};

			if ( hasSwatches ) {
				var variationsMap = JSON.parse( $variationsForm.attr( 'data-variations_map' ) ),
					selectedAttName = $select.attr( 'data-attribute-name' ),
					selectedValHash = $select.val();

				data.attribute = sanitise_str( selectedAttName );
				data.value = variationsMap[ selectedAttName ][ selectedValHash ];

				return data;
			}

			data.attribute = sanitise_str( $select.attr( 'name' ) );
			data.value = $select.val();

			return data;
		}

		/**
		 * Images loaded with srcset.
		 */
		function images_loaded( selector, on_complete, on_progress ) {
			var $images = $( selector ).find( 'img' );
			var success = 0;
			var error = 0;
			var iteration = 0;
			var total = $images.length;
			var check = function( el, status ) {
				iteration ++;
				var data = {
					img: el,
					iteration: iteration,
					success: success,
					error: error,
					total: total,
					status: status
				};

				if ( $.isFunction( on_progress ) ) {
					on_progress( data );
				}

				if ( success + error === total && $.isFunction( on_complete ) ) {
					on_complete( data );
				}
			};
			$images.each( function() {
				this.onload = function() {
					success ++;
					check( this, 'success' );
				};
				this.onerror = function() {
					error ++;
					check( this, 'error' );
				};
			} );
		}

		/* on doc ready */

		$( document ).ready( function() {
			set_elements();
			setupDefaultImages();
			setupImgSwitcher();
			setupSwatches();
			setupInventory();
			setupWooThumbs();

			if ( hasSwatches ) {
				resetLayers();
			}
		} );
	}

	( jQuery, document )
);