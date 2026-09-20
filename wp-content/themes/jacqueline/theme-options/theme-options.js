/* global jQuery, JACQUELINE_STORAGE */

// Add 'sticky' behaviour to the options header
//---------------------------------------------------------
jQuery( window ).on( 'scroll', function() {

	"use strict";

	var header = jQuery( '.jacqueline_options_header' );
	if ( header.length !== 0 ) {
		var placeholder = jQuery( '.jacqueline_options_header_placeholder' );
		if ( jQuery( '.jacqueline_options_header_placeholder' ).length === 0 ) {
			jQuery( '.jacqueline_options_header' ).before( '<div class="jacqueline_options_header_placeholder"></div>' );
			placeholder = jQuery( '.jacqueline_options_header_placeholder' );
		}
		if ( placeholder.length !== 0 ) {
			header.toggleClass( 'sticky', placeholder.offset().top < jQuery( window ).scrollTop() + jQuery( '#wpadminbar' ).height() );
		}
	}
} );


// Init options
//---------------------------------------------------------
jQuery( document ).ready( function() {

	"use strict";

	// Scroll to the theme options after page reloaded
	if ( location.href.indexOf( 'page=theme_options' ) > 0 ) {
		jacqueline_document_animate_to( jQuery( '.jacqueline_admin_message_item, .jacqueline_options' ).eq(0) );
	}

	var jacqueline_options_changed_state = false;

	// Set a new options state or return a current state (if no param specified)
	function jacqueline_options_changed( state ) {
		if ( state !== undefined ) {
			jacqueline_options_changed_state = state;
		}
		return jacqueline_options_changed_state;
	}

	// Check to exit while options changed
	jQuery( window ).on( 'beforeunload', function( e ) {
		if ( jacqueline_options_changed()
			&& ( jQuery( '#trx_addons_theme_panel' ).length === 0 || jQuery( '#trx_addons_theme_panel_section_qsetup' ).is( ':visible' ) )
		) {
			e.preventDefault();
			return e.returnValue = JACQUELINE_STORAGE[ 'msg_exit_not_saved_options' ];
		}
	} );

	// Add some hooks after delay to allow Gutenberg to init its controls
	setTimeout( function() {
		// Set a global state 'changed' on any field is changed
		jQuery( '.jacqueline_options .jacqueline_options_item_field [name^="jacqueline_options_field_"]' )
			.on( 'change', function () {
				jacqueline_options_changed( true );
			} );
	
		// Clear a global state 'changed' on the button "Publish" is pressed
		jQuery( '#submitpost #publish,.editor-header__settings .editor-post-publish-button' ).on( 'click', function(e) {
			jacqueline_options_changed( false );
		} );

	}, 600 );

	// --------------------------- SAVE / RESET & EXPORT / IMPORT OPTIONS ------------------------------

	// Save options
	jQuery( '.jacqueline_options_button_submit' )
		.on( 'click', function( e ) {
			var form = jQuery( this ).parents( '.jacqueline_options' ).find( 'form' );
			// Prevent to send unchanged values
			if ( jacqueline_options_vars['save_only_changed_options'] ) {
				form.find('[data-param]').each( function() {
					if ( jQuery( this ).data( 'param-changed' ) === undefined ) {
						jQuery( this ).find( 'input,select,textarea' ).each( function() {
							// Disable fields to prevent send to the server
							jQuery( this ).get( 0 ).disabled = true;
							// or another way - remove fields: jQuery( this ).remove()
						} );
					}
				});
			}
			jacqueline_options_changed( false );
			// Send data to the server
			form.submit();
			e.preventDefault();
			return false;
		} );

	// Reset options
	jQuery( '.jacqueline_options_button_reset' )
		.on( 'click', function( e ) {
			var form = jQuery( this ).parents( '.jacqueline_options' ).find( 'form' );
			if ( typeof trx_addons_msgbox_agree != 'undefined' ) {
				trx_addons_msgbox_agree(
					JACQUELINE_STORAGE[ 'msg_reset_confirm' ],
					JACQUELINE_STORAGE[ 'msg_reset' ],
					function( btn ) {
						if ( btn === 1 ) {
							form.find( 'input[name="jacqueline_options_field_reset_options"]' ).val( 1 );
							jacqueline_options_changed( false );
							form.submit();
						}
					}
				);
			} else if ( confirm( JACQUELINE_STORAGE[ 'msg_reset_confirm' ] ) ) {
				form.find( 'input[name="jacqueline_options_field_reset_options"]' ).val( 1 );
				jacqueline_options_changed( false );
				form.submit();
			}
			e.preventDefault();
			return false;
		} );

	// Export options
	jQuery( '.jacqueline_options_button_export' )
		.on( 'click', function( e ) {
			var form = jQuery( this ).parents( '.jacqueline_options' ).find( 'form' ),
				data = '';
			form.find('[data-param]').each( function() {
				jQuery( this )
					.find('[name^="jacqueline_options_field_' + jQuery(this).data('param') + '"]')
					.each(function() {
						var fld = jQuery(this),
							fld_name = fld.attr('name'),
							fld_type = fld.attr('type') ? fld.attr('type') : fld.get(0).tagName.toLowerCase();
						if ( fld_type == 'checkbox' ) {
							data += ( data ? '&' : '' ) + fld_name + '=' + encodeURIComponent( fld.get(0).checked ? fld.val() : 0 );
						} else if ( fld_type != 'radio' || fld.get(0).checked ) {
							data += ( data ? '&' : '' ) + fld_name + '=' + encodeURIComponent( fld.val() );
						}
					});
			});
			if ( typeof trx_addons_msgbox_info != 'undefined' ) {
				trx_addons_msgbox_info(
					jQuery.jacqueline_encoder.encode( data ),
					JACQUELINE_STORAGE[ 'msg_export' ] + ': ' + JACQUELINE_STORAGE[ 'msg_export_options' ],
					'info',
					0
				);
			} else {
				alert( JACQUELINE_STORAGE[ 'msg_export_options' ] + ':\n\n' + jQuery.jacqueline_encoder.encode( data ) );
			}
			e.preventDefault();
			return false;
		} );

	// Import options
	jQuery( '.jacqueline_options_button_import' )
		.on( 'click', function( e ) {
			var form = jQuery( this ).parents( '.jacqueline_options' ).find( 'form' ),
				data = '';
			if ( typeof trx_addons_msgbox_dialog != 'undefined' ) {
				trx_addons_msgbox_dialog(
					'<textarea rows="10" cols="100"></textarea>',
					JACQUELINE_STORAGE[ 'msg_import' ] + ': ' + JACQUELINE_STORAGE[ 'msg_import_options' ],
					null,
					function(btn, box) {
						if ( btn === 1 ) {
							jacqueline_options_import_data( box.find('textarea').val() );
						}
					}
				);
			} else if ( ( data = prompt( JACQUELINE_STORAGE[ 'msg_import_options' ], '' ) ) !== '' ) {
				jacqueline_options_import_data( data );
			}

			function jacqueline_options_import_data( data ) {
				if ( data ) {
					data = jQuery.jacqueline_encoder.decode( data ).split( '&' );
					var i, fld, val, globals, fld_globals, item_globals;
					for ( i in data ) {
						var param = data[i].split('=');
						if ( param.length == 2 && param[0].slice(-6) != '_nonce' ) {
							fld = form.find('[name="'+param[0]+'"]');
							val = decodeURIComponent(param[1]);
							if ( fld.attr('type') == 'radio' || fld.attr('type') == 'checkbox' ) {
								fld.removeAttr( 'checked' );
								fld.each( function() {
									var item = jQuery(this);
									if ( item.val() == val ) {
										item.get(0).checked = true;
										item.attr('checked', 'checked');
									}
								} );
							} else if ( fld.hasClass('jacqueline_color_selector') ) {
								// Update global color selector
								if ( val.slice( 0, 4 ) == 'var(' ) {
									globals = fld.parents('.jacqueline_options_item_field').find('.jacqueline_color_selector_globals');
									if ( globals.length > 0 ) {
										globals.find( '.jacqueline_color_selector_globals_list_item_active' ).removeClass( 'jacqueline_color_selector_globals_list_item_active' );
										item_globals = globals.find( '.jacqueline_color_selector_globals_list_item[data-value="' + val + '"]' );
										fld_globals = globals.find( 'input[type="hidden"]' );
										// Set a new value to the hidden field after a short delay to prevent recursive calls
										setTimeout( function() {
											fld_globals.val( value ).trigger( 'change' );
											item_globals.addClass( 'jacqueline_color_selector_globals_list_item_active' );
											globals.addClass( 'jacqueline_color_selector_globals_active' );
										}, 10 );
										val = item_globals.data( 'color' );
									}
								}
								// Update a color field and a color picker
								fld.val( val );
								if ( fld.hasClass( 'wpColorPicker' ) ) {
									fld.wpColorPicker( 'color', val );
								} else if ( fld.hasClass( 'spectrumColorPicker' ) ) {
									fld.spectrum( 'set', val );
								}
							} else if ( fld.next().hasClass('jacqueline_icon_selector') ) {
								if ( val == '' || val == 'none' ) {
									fld.next().attr( 'class', 'jacqueline_icon_selector' );
								} else {
									fld.next().addClass( val ).css('background-image', 'none');
								}
								fld.val( val );
							} else {
								fld.val( val );
							}
							// If a current field is 'load_fonts-N-name' - update a list options in the select 'font-family' fields
							if ( param[0].indexOf( 'load_fonts-' ) > 0 && ( param[0].slice( -5 ) == '-name' || param[0].slice( -7 ) == '-family' ) ) {
								jacqueline_options_update_load_fonts();
							}
						}
					}
					jacqueline_options_changed( false );
					form.submit();
				} else {
					if ( typeof trx_addons_msgbox_warning != 'undefined' ) {
						trx_addons_msgbox_warning(
							JACQUELINE_STORAGE[ 'msg_import_error' ],
							JACQUELINE_STORAGE[ 'msg_import' ]
						);
					}
				}
			}

			e.preventDefault();
			return false;

		} );



	// --------------------------- PRESETS ------------------------------

	// Create preset with options
	jQuery( '.jacqueline_options_presets_add' )
		.on( 'click', function( e ) {
			if ( typeof trx_addons_msgbox_dialog != 'undefined' ) {
				var preset_name = '';
				trx_addons_msgbox_dialog(
					'<label>' + JACQUELINE_STORAGE[ 'msg_presets_add' ]
						+ '<br><input type="text" value="" name="preset_name">'
						+ '</label>',
					JACQUELINE_STORAGE[ 'msg_presets' ],
					null,
					function(btn, box) {
						if ( btn === 1 ) {
							var preset_name = box.find('input[name="preset_name"]').val();
							if ( preset_name !== '' ) {
								jacqueline_options_presets_create( preset_name );
							}
						}
					}
				);
			} else if ( ( preset_name = prompt( JACQUELINE_STORAGE[ 'msg_presets_add' ], '' ) ) !== '' ) {
				jacqueline_options_presets_create( preset_name );
			}

			// Create new preset: send it to server and add to the presets list
			function jacqueline_options_presets_create( preset_name ) {
				var form = jQuery( '.jacqueline_tabs' ),
					data = '';
				form.find('[data-param]').each( function() {
					jQuery( this )
						.find('[name^="jacqueline_options_field_' + jQuery(this).data('param') + '"]')
						.each(function() {
							var fld = jQuery(this),
								fld_name = fld.attr('name'),
								fld_type = fld.attr('type') ? fld.attr('type') : fld.get(0).tagName.toLowerCase(),
								in_group = fld_name.indexOf('[') > 0;
							if ( fld_name == 'jacqueline_options_field_presets' ) {
								return;
							} else if ( fld.parents( in_group ? '.jacqueline_options_group' : '.jacqueline_options_item' ).hasClass( 'jacqueline_options_inherit_on' ) ) {
								data += ( data ? '&' : '' ) + fld_name + '=inherit';
							} else if ( fld_type == 'checkbox' ) {
								data += ( data ? '&' : '' ) + fld_name + '=' + encodeURIComponent( fld.get(0).checked ? fld.val() : 0 );
							} else if ( fld_type != 'radio' || fld.get(0).checked ) {
								data += ( data ? '&' : '' ) + fld_name + '=' + encodeURIComponent( fld.val() );
							}
						});
				});
				data = jQuery.jacqueline_encoder.encode( data );
				jQuery.post(JACQUELINE_STORAGE['ajax_url'], {
					action: 'jacqueline_add_options_preset',
					nonce: JACQUELINE_STORAGE['ajax_nonce'],
					preset_name: preset_name,
					preset_data: data,
					preset_type: jQuery( '.jacqueline_options_presets_list' ).data( 'type' ),
					is_admin_request: 1
				}).done( function( response ) {
					var rez = jacqueline_parse_ajax_response( response );
					if ( rez.success ) {
						var presets_list = jQuery( '.jacqueline_options_presets_list' ).get(0),
							idx = jacqueline_find_listbox_item_by_text( presets_list, preset_name );
						if ( idx >= 0 ) {
							presets_list.options[idx].value = data;
						} else {
							jacqueline_add_listbox_item( presets_list, data, preset_name );
						}
						jacqueline_select_listbox_item_by_text( presets_list, preset_name );
					}
					if ( typeof window.trx_addons_msgbox != 'undefined' ) {
						trx_addons_msgbox({
							msg: rez.error ? rez.error : rez.success,
							hdr: JACQUELINE_STORAGE[ 'msg_presets' ],
							icon: rez.error ? 'cancel' : 'check',
							type: rez.error ? 'error' : 'success',
							delay: 0,
							buttons: [ TRX_ADDONS_STORAGE['msg_caption_ok'] ],
							callback: null
						});
					} else {
						alert( rez.error ? rez.error : rez.success );
					}
				});
			}

			e.preventDefault();
			return false;

		} );


	// Apply selected preset
	jQuery( '.jacqueline_options_presets_apply' )
		.on( 'click', function( e ) {
			var preset_data = jQuery( '.jacqueline_options_presets_list' ).val();
			if ( preset_data !== '' ) {
				if ( typeof trx_addons_msgbox_confirm != 'undefined' ) {
					trx_addons_msgbox_confirm(
						JACQUELINE_STORAGE[ 'msg_presets_apply' ],
						JACQUELINE_STORAGE[ 'msg_presets' ],
						function(btn, box) {
							if ( btn === 1 ) {
								jacqueline_options_presets_apply( preset_data );
							}
						}
					);
				} else if ( confirm( JACQUELINE_STORAGE[ 'msg_presets_apply' ] ) ) {
					jacqueline_options_presets_apply( preset_data );
				}
			}

			function jacqueline_options_presets_apply( data ) {
				var form = jQuery( '.jacqueline_tabs' );
				data = jQuery.jacqueline_encoder.decode( data ).split( '&' );
				for ( var i in data ) {
					var param = data[i].split('=');
					if ( param.length == 2 && param[0].substr(-6) != '_nonce' && param[0].substr(-8) != '_presets' ) {
						var fld = form.find('[name="'+param[0]+'"]'),
							val = decodeURIComponent(param[1]),
							pos = param[0].indexOf('[');
						if ( pos > 0 ) {
							var base = param[0].substring(0, pos),
								fields = form.find( '[name^="' + base + '["]' ).eq(0).parents('.jacqueline_options_group_fields');
							if ( fields.length > 0 ) {
								if ( ! fields.data( 'clear' ) ) {
									fields.data( 'clear', true );
									var items = fields.find( '.jacqueline_options_clone' );
									items.each( function( idx ) {
										if ( idx > 0 ) {
											jQuery(this).remove();
										}
									} );
								}
								if ( fld.length === 0 ) {									
									fields.find( '.jacqueline_options_clone_button_add' ).trigger( 'click' );
									fld = form.find('[name="'+param[0]+'"]');
								}
							}
						} else if ( fld.length === 0 ) {
							continue;
						}
						var type = fld.parents('[data-type]').data( 'type' );
						if ( val != 'inherit' ) {
							if ( type == 'switch' ) {
								fld.next().get( 0 ).checked = val == 1;
								fld.next().trigger('change');
							} else if ( fld.attr('type') == 'radio' || fld.attr('type') == 'checkbox' ) {
								fld.removeAttr( 'checked' );
								fld.each( function() {
									var item = jQuery(this);
									if ( item.val() == val ) {
										item.get(0).checked = true;
										item.attr('checked', 'checked');
									}
								} );
							} else {
								fld.val( val );
								if ( type == 'choice' ) {
									var choices = fld.next();
									choices.find('.jacqueline_list_active').removeClass('jacqueline_list_active');
									choices.find('[data-choice="'+val+'"]').addClass('jacqueline_list_active');
								} else if ( type == 'image' ) {
									var images = val.split( ','),
										preview = fld.next();
									preview.empty();
									for (var img=0; img < images.length; img++) {
										if ( images[img].trim() !== '' ) {
											preview
												.append(
													'<span class="jacqueline_media_selector_preview_image" tabindex="0">'
														+ '<img src="' + images[img].trim() + '">'
														+ '</span>'
												)
												.css( {
													'display': 'block'
												} );
										}
									}
								}
							}
							fld.trigger( 'change' );
						}
						var item = pos > 0 ? fld.parents( '.jacqueline_options_group ' ) : fld.parents( '.jacqueline_options_item' );
						if ( ( val == 'inherit' && ! item.hasClass( 'jacqueline_options_inherit_on' ) )
							|| ( val != 'inherit' && ! item.hasClass( 'jacqueline_options_inherit_off' ) )
						) {
							item.find( '.jacqueline_options_inherit_lock' ).trigger( 'click' );
						}
					}
				}
				// Remove data from groups
				form.find( '.jacqueline_options_group_fields' ).each( function() {
					jQuery(this).data( 'clear', false );
				} );
			}
			e.preventDefault();
			return false;
		} );

	// Delete selected preset
	jQuery( '.jacqueline_options_presets_delete' )
		.on( 'click', function( e ) {
			var presets_list = jQuery( '.jacqueline_options_presets_list' ).get(0),
				preset_data  = jacqueline_get_listbox_selected_value( presets_list ),
				preset_name  = jacqueline_get_listbox_selected_text( presets_list );
			if ( preset_data ) {
				if ( typeof trx_addons_msgbox_agree != 'undefined' ) {
					trx_addons_msgbox_agree(
						JACQUELINE_STORAGE[ 'msg_presets_delete' ],
						JACQUELINE_STORAGE[ 'msg_presets' ],
						function(btn, box) {
							if ( btn === 1 ) {
								jacqueline_options_presets_delete( preset_name );
							}
						}
					);
				} else if ( confirm( JACQUELINE_STORAGE[ 'msg_presets_delete' ] ) ) {
					jacqueline_options_presets_delete( preset_name );
				}
			}
			
			function jacqueline_options_presets_delete( preset_name ) {
				jQuery.post(JACQUELINE_STORAGE['ajax_url'], {
					action: 'jacqueline_delete_options_preset',
					nonce: JACQUELINE_STORAGE['ajax_nonce'],
					preset_name: preset_name,
					preset_type: jQuery( '.jacqueline_options_presets_list' ).data( 'type' ),
					is_admin_request: 1
				}).done( function( response ) {
					var rez = jacqueline_parse_ajax_response( response );
					if ( rez.success ) {
						jacqueline_del_listbox_item_by_text( presets_list, preset_name );
						jacqueline_select_listbox_item_by_value( presets_list, '' );
					}
					if ( typeof window.trx_addons_msgbox != 'undefined' ) {
						trx_addons_msgbox({
							msg: rez.error ? rez.error : rez.success,
							hdr: JACQUELINE_STORAGE[ 'msg_presets' ],
							icon: rez.error ? 'cancel' : 'check',
							type: rez.error ? 'error' : 'success',
							delay: 0,
							buttons: [ TRX_ADDONS_STORAGE['msg_caption_ok'] ],
							callback: null
						});
					} else {
						alert( rez.error ? rez.error : rez.success );
					}
				});
			}
			e.preventDefault();
			return false;
		} );




	// -------------------------- CHANGE 'LOAD FONTS' LIST -------------------------------

	// Blur the "load fonts" fields - regenerate options lists in the font-family controls
	jQuery( '.jacqueline_options [name^="jacqueline_options_field_load_fonts"]' )
		.on( 'change', jacqueline_options_update_load_fonts );

	// Change theme fonts options if load fonts is changed
	function jacqueline_options_update_load_fonts() {
		var opt_list = [], i, tag, sel, opt, name = '', family = '', val = '', new_val = '', sel_idx = 0;
		for (i = 1; i <= jacqueline_options_vars['max_load_fonts']; i++) {
			name = jQuery( '[name="jacqueline_options_field_load_fonts-' + i + '-name"]' ).val();
			if (name === '') {
				continue;
			}
			family = jQuery( '[name="jacqueline_options_field_load_fonts-' + i + '-family"]' ).val();
			opt_list.push( [name, family] );
		}
		for (tag in jacqueline_theme_fonts) {
			sel = jQuery( '[name="jacqueline_options_field_' + tag + '_font-family"]' );
			if (sel.length == 1) {
				opt     = sel.find( 'option' );
				sel_idx = sel.find( ':selected' ).index();
				// Remove empty options
				if (opt_list.length < opt.length - 1) {
					for (i = opt.length - 1; i > opt_list.length; i--) {
						opt.eq( i ).remove();
					}
				}
				// Add new options
				if (opt_list.length >= opt.length) {
					for (i = opt.length - 1; i <= opt_list.length - 1; i++) {
						val = jacqueline_get_load_fonts_family_string( opt_list[i][0], opt_list[i][1] );
						sel.append( '<option value="' + val + '">' + opt_list[i][0] + '</option>' );
					}
				}
				// Set new value
				new_val = '';
				for (i = 0; i < opt_list.length; i++) {
					val = jacqueline_get_load_fonts_family_string( opt_list[i][0], opt_list[i][1] );
					if (sel_idx - 1 == i) {
						new_val = val;
					}
					opt.eq( i + 1 ).val( val ).text( opt_list[i][0] );
				}
				sel.val( sel_idx > 0 && sel_idx <= opt_list.length && new_val ? new_val : 'inherit' );
			}
		}
	}



	// -------------------------- INIT FIELDS -------------------------------
	jacqueline_options_init_fields();
	jQuery(document).on( 'action.init_hidden_elements', jacqueline_options_init_fields );

	// Init fields at first run and after clone group
	function jacqueline_options_init_fields(e, container) {
		
		if (container === undefined) {
			container = jQuery('.jacqueline_options,#customize-theme-controls,#elementor-panel,body').eq(0);
		}

		// Checkbox
		container.find( '.jacqueline_options_item_checkbox:not(.inited)' ).addClass( 'inited' )
			.on( 'keydown', '.jacqueline_options_item_holder', function( e ) {
				// If 'Enter' or 'Space' is pressed - switch state of the checkbox
				if ( [ 13, 32 ].indexOf( e.which ) >= 0 ) {
					jQuery( this ).prev().get( 0 ).checked = ! jQuery( this ).prev().get( 0 ).checked;
					e.preventDefault();
					return false;
				}
				return true;
			} );
		
		// Radio
		container.find( '.jacqueline_options_item_radio:not(.inited)' ).addClass( 'inited' )
			.on( 'keydown', '.jacqueline_options_item_holder', function( e ) {
				// If 'Enter' or 'Space' is pressed - switch state of the checkbox
				if ( [ 13, 32 ].indexOf( e.which ) >= 0 ) {
					jQuery( this ).parents( 'jacqueline_options_item_field' ).find( 'input:checked' ).each( function() {
						this.checked = false;
					});
					jQuery( this ).prev().get( 0 ).checked = true;
					e.preventDefault();
					return false;
				}
				return true;
			} );

		// Button with action
		container.find('.jacqueline_options_item_button input[type="button"]:not(.inited),.jacqueline_options_item_button .jacqueline_options_button:not(.inited)').addClass('inited')
			.on('click', function(e) {
				var button = jQuery(this),
					cb = button.data('callback');
				if (cb !== undefined && typeof window[cb] !== 'undefined') {
					window[cb]();
				} else {
					jQuery.post(JACQUELINE_STORAGE['ajax_url'], {
						action: button.data('action'),
						nonce: JACQUELINE_STORAGE['ajax_nonce'],
						is_admin_request: 1
					}).done( function( response ) {
						var rez = jacqueline_parse_ajax_response( response );
						if ( typeof window.trx_addons_msgbox != 'undefined' ) {
							trx_addons_msgbox({
								msg: typeof rez.data != 'undefined' ? rez.data : '',
								hdr: '',
								icon: 'check',
								type: 'success',
								delay: 0,
								buttons: [ TRX_ADDONS_STORAGE['msg_caption_ok'] ],
								callback: null
							});
						} else {
							alert(rez.error ? rez.error : rez.success);
						}
					});
				}
				e.preventDefault();
				return false;
			} );


		// Cloned fields
		jacqueline_options_clone_toggle_buttons( container );
		container.find( '.jacqueline_options_group:not(.inited)' ).addClass( 'inited' ).each(function() {
			// Clone buttons
			jQuery( this )
				// Button 'Add new'
				.on( 'click', '.jacqueline_options_clone_button_add', function ( e ) {
					var clone_obj = jQuery(this).parents('.jacqueline_options_clone_buttons').prev('.jacqueline_options_clone').eq(0),
						group = clone_obj.parents('.jacqueline_options_group');
					// Clone fields
					jacqueline_options_clone(clone_obj);
					// Enable/Disable clone buttons
					jacqueline_options_clone_toggle_buttons(group);
					// Mark group as changed
					group.data( 'param-changed', 1 ).find('[data-param]').data( 'param-changed', 1 );
					// Prevent bubble event
					e.preventDefault();
					return false;
				} )
				// Button 'Clone'
				.on( 'click', '.jacqueline_options_clone > .jacqueline_options_clone_control_add', function ( e ) {
					var clone_obj = jQuery(this).parents('.jacqueline_options_clone'),
						group = clone_obj.parents('.jacqueline_options_group');
					// Clone fields
					jacqueline_options_clone(clone_obj, true);
					// Enable/Disable clone buttons
					jacqueline_options_clone_toggle_buttons(group);
					// Mark group as changed
					group.data( 'param-changed', 1 ).find('[data-param]').data( 'param-changed', 1 );
					// Prevent bubble event
					e.preventDefault();
					return false;
				} )
				// Button 'Delete'
				.on( 'click', '.jacqueline_options_clone > .jacqueline_options_clone_control_delete', function ( e ) {
					var clone_obj = jQuery(this).parents('.jacqueline_options_clone'),
						clone_idx = clone_obj.prevAll('.jacqueline_options_clone').length,
						group = clone_obj.parents('.jacqueline_options_group');
					// Delete clone
					clone_obj.remove();
					// Change fields index
					jacqueline_options_clone_change_index(group, clone_idx);
					// Enable/Disable clone buttons
					jacqueline_options_clone_toggle_buttons(group);
					// Mark group as changed
					group.data( 'param-changed', 1 ).find('[data-param]').data( 'param-changed', 1 );
					// Prevent bubble event
					e.preventDefault();
					return false;
				} );
			
			// Sort clones
			if ( jQuery.ui.sortable ) {
				var id = jQuery(this).attr( 'id' );
				if ( id === undefined ) {
					jQuery( this ).attr( 'id', 'jacqueline_options_sortable_' + ( '' + Math.random() ).replace( '.', '' ) );
				}
				jQuery( this )
					.sortable( {
						items: '.jacqueline_options_clone',
						handle: '.jacqueline_options_clone_control_move',
						placeholder: ' jacqueline_options_clone jacqueline_options_clone_placeholder',
						start: function( event, ui ) {
							// Make the placeholder has the same height as dragged item
							ui.placeholder.height( ui.item.height() );
						},
						update: function( event, ui ) {
							// Change fields index
							jacqueline_options_clone_change_index( ui.item.parents('.jacqueline_options_group'), 0 );
							// Mark group as changed
							ui.item.parents('.jacqueline_options_group').data( 'param-changed', 1 ).find('[data-param]').data( 'param-changed', 1 );
						}
					});
			}
		});
		
		// Check clone controls for enable/disable
		function jacqueline_options_clone_toggle_buttons( container ) {
			if ( ! container.hasClass('jacqueline_options_group') ) {
				container = container.find('.jacqueline_options_group');
			}
			container.each( function() {
				var group = jQuery(this);
				if ( group.find('.jacqueline_options_clone').length > 1 ) {
					group.find('.jacqueline_options_clone_control_delete,.jacqueline_options_clone_control_move').show();
				} else {
					group.find('.jacqueline_options_clone_control_delete,.jacqueline_options_clone_control_move').hide();
				}
			});
		}
		
		// Replace number in the param's name like 'floor_plans[0][image]'
		function jacqueline_options_clone_replace_index( name, idx_new ) {
			name = name.replace(/\[\d{1,2}\]/, '['+idx_new+']');
			return name;
		}
		
		// Change index in each field in the clone
		function jacqueline_options_clone_change_index( group, from_idx ) {
			group.find('.jacqueline_options_clone').each( function( idx ) {
				if ( idx < from_idx ) return;
				jQuery(this).find('.jacqueline_options_item_field').each( function() {
					var field = jQuery(this),
						param_old = field.data('param'),
						param_old_id = param_old.replace(/\[/g, '_').replace(/\]/g, ''),
						param_new = jacqueline_options_clone_replace_index( param_old, idx ),
						param_new_id = param_new.replace(/\[/g, '_').replace(/\]/g, '');
					// Change data-param
					field.attr('data-param', param_new );
					// Change name and id in inputs
					field.find(':input').each( function() {
						var input = jQuery(this),
							id = input.attr('id'),
							name = input.attr('name');
						if (!name) return;
						name = jacqueline_options_clone_replace_index(name, idx);
						input.attr( 'name', name );
						if ( id ) {
							var id_new = name.replace(/\[/g, '_').replace(/\]/g, '');
							input.attr('id', id_new);
							var linked_field = field.find('[data-linked-field="'+id+'"]');
							if ( linked_field.length > 0 ) {
								linked_field.attr('data-linked-field', id_new);
								if ( linked_field.attr('id') ) {
									linked_field.attr('id', linked_field.attr('id').replace(id, id_new));
								}
							}
						}
						// Fix a checked radio button and checkboxes (check it again if an attribute 'checked' is set)
						if ( ( input.is(':radio') || input.is(':checkbox') ) && input.attr('checked') ) {
							input.prop('checked', true);
						}
					} );
					// Change name and id in any tags
					field.find('[id*="'+param_old_id+'"],[name*="'+param_old_id+'"]').each( function() {
						var $self = jQuery(this),
							name = $self.attr('name'),
							id = $self.attr('id'),
							data_id = $self.data( 'wp-editor-id' );
						if ( name ) {
							$self.attr( 'name', name.replace( param_old_id, param_new_id ) );
						}
						if ( id ) {
							$self.attr( 'id', id.replace( param_old_id, param_new_id ) );
						}
						if ( data_id ) {
							$self.attr( 'data-wp-editor-id', data_id.replace( param_old_id, param_new_id ) );
						}
					} );
				} );
			} );
		}
		
		// Clone set of the fields
		function jacqueline_options_clone( obj, copy_values ) {
			var group = obj.parent(),
				clone = obj.clone(),
				obj_idx = obj.prevAll('.jacqueline_options_clone').length;
			// Remove class 'inited' from all elements
			clone.find('.inited').removeClass('inited');
			clone.find('.icons_inited').removeClass('icons_inited');
			// Reset text editor area
			var editor = clone.find('.jacqueline_text_editor');
			if ( editor.length ) {
				editor.html( editor.data( 'editor-html' ) );
			}
			// Reset value for other fields
			clone.find('.jacqueline_options_item_field :input').each( function() {
				var input = jQuery(this),
					std = copy_values ? input.val() : input.data('std');
				if ( input.is(':radio') || input.is(':checkbox') ) {
					input.prop( 'checked', std !== undefined && std == input.val() );
				} else if ( input.is('select') ) {
					input.prop( 'selectedIndex', -1 );
					if ( std !== undefined ) {
						var opt = input.find('option[value="'+std+'"]');
						if ( opt.length > 0 ) {
							input.prop('selectedIndex', opt.index());
						}
					}
				} else if ( ! input.is(':button') ) {
					input.val( std !== undefined ? std : '' );
				}
				// Remove image preview
				input.parents('.jacqueline_options_item_field').find('.jacqueline_media_selector_preview').empty();
				// Remove class 'inited' from selectors
				input.next('[class*="_selector"].inited').removeClass('inited');
				// Mark all cloned fields as 'changed' on any cloned field is changed
				if (input.attr('name') && input.attr('name').indexOf("jacqueline_options_field_") === 0) {
					input.on( 'change', function () {
						jQuery( this ).parents('.jacqueline_options_group').data( 'param-changed', 1 ).find('[data-param]').data( 'param-changed', 1 );
					} );
				}
			});
			//Remove UI sliders
			clone.find('.ui-slider-range, .ui-slider-handle').remove();
			// Insert Clone
			clone.insertAfter(obj);
			// Change fields index. Must run before trigger clone event
			jacqueline_options_clone_change_index(group, obj_idx);
			// Init of the cloned text editor
			if ( editor.length && typeof tinymce !== 'undefined' ) {
				var old_id = group.find( '.wp-editor-area' ).eq(0).attr('id'),
					new_id = editor.find( '.wp-editor-area' ).attr( 'id' ),
					init   = typeof tinyMCEPreInit != 'undefined' && typeof tinyMCEPreInit.mceInit != 'undefined' && typeof tinyMCEPreInit.mceInit[ old_id ] != 'undefined'
								? tinyMCEPreInit.mceInit[ old_id ]
								: { tinymce: true };
				if ( init.body_class ) {
					init.body_class = init.body_class.replace( old_id, new_id );
				}
				if ( init.selector ) {
					init.selector = init.selector.replace( old_id, new_id );
				}
				if ( typeof tinyMCEPreInit != 'undefined' ) {
					tinyMCEPreInit.mceInit[ new_id ] = init;
				}

				var $wrap;

				if ( typeof tinymce !== 'undefined' ) {
					if ( tinymce.Env.ie && tinymce.Env.ie < 11 ) {
						tinymce.$( '.wp-editor-wrap ' ).removeClass( 'tmce-active' ).addClass( 'html-active' );
					} else {
						$wrap = tinymce.$( '#wp-' + new_id + '-wrap' );
						if ( ( $wrap.hasClass( 'tmce-active' ) || ! tinyMCEPreInit.qtInit.hasOwnProperty( new_id ) ) && ! init.wp_skip_init ) {
							tinymce.init( init );
							if ( ! window.wpActiveEditor ) {
								window.wpActiveEditor = new_id;
							}
						}
						if ( typeof quicktags !== 'undefined' && tinyMCEPreInit.qtInit.hasOwnProperty( new_id ) ) {
							quicktags( tinyMCEPreInit.qtInit[new_id] );
							if ( ! window.wpActiveEditor ) {
								window.wpActiveEditor = new_id;
							}
						}
					}
				}

				// ToDo: check and remove next line
				// ??? wp.editor.initialize( new_id, init );
			}
			// Fire init actions for other cloned fields
			jQuery(document).trigger( 'action.init_hidden_elements', [clone.parents('.jacqueline_options')] );
		}

	}



	// -------------------------- 'LINKED' FIELDS -------------------------------

	// Refresh linked field
	jQuery( '#jacqueline_options_tabs' )
		.on( 'change', '[data-linked] select,[data-linked] input', function (e) {
			var chg_name          = jQuery( this ).parent().data( 'param' );
			var chg_value         = jQuery( this ).val();
			var linked_name       = jQuery( this ).parent().data( 'linked' );
			var linked_data       = jQuery( '#jacqueline_options_tabs [data-param="' + linked_name + '"]' );
			var linked_field      = linked_data.find( 'select' );
			var linked_field_type = 'select';
			if (linked_field.length === 0) {
				linked_field      = linked_data.find( 'input' );
				linked_field_type = 'input';
			}
			var linked_lock = linked_data.parent().parent().find( '.jacqueline_options_inherit_lock' ).addClass( 'jacqueline_options_wait' );
			// Prepare data
			var data = {
				action: 'jacqueline_get_linked_data',
				nonce: JACQUELINE_STORAGE['ajax_nonce'],
				chg_name: chg_name,
				chg_value: chg_value,
				is_admin_request: 1
			};
			jQuery.post(
				JACQUELINE_STORAGE['ajax_url'], data, function( response ) {
					var rez = jacqueline_parse_ajax_response( response );
					if ( rez.error === '' ) {
						if (linked_field_type == 'select') {
							var opt_list = '';
							for (var i in rez.list) {
								opt_list += '<option value="' + i + '">' + rez.list[i] + '</option>';
							}
							linked_field.html( opt_list );
						} else {
							linked_field.val( rez.value );
						}
						linked_lock.removeClass( 'jacqueline_options_wait' );
					}
				}
			);
			e.preventDefault();
			return false;
		} );



	// ---------------------------- MARK FIELDS AS 'CHANGED' --------------------------

	// Mark field as 'changed' on any field change
	jQuery( '.jacqueline_options .jacqueline_options_item_field [name^="jacqueline_options_field_"]' )
		.on( 'change', function () {
			jacqueline_options_mark_field_changed( jQuery( this ) );
		} );

	// Mark select fields as 'changed' on page load if no 'selected' items are present
	jQuery( '.jacqueline_options .jacqueline_options_item_select select' ).each( function() {
		var obj = jQuery( this );
		if ( obj.find('option[selected]').length === 0 ) {
			jacqueline_options_mark_field_changed( obj );
		}
	} );

	// Mark radio fields as 'changed' on page load if no 'checked' items are present
	jQuery( '.jacqueline_options .jacqueline_options_item_radio' ).each( function() {
		var obj = jQuery( this );
		if ( obj.find('input[type="radio"][checked]').length === 0 ) {
			jacqueline_options_mark_field_changed( obj.find('input[type="radio"]').eq(0) );
		}
	} );

	// Mark field as 'changed'
	function jacqueline_options_mark_field_changed( obj ) {
		var par = obj.parents('.jacqueline_options_group');
		if ( par.length > 0 ) {
			// On change any field of a group - mark all fields in this group as changed
			par.data( 'param-changed', 1 ).find('[data-param]').data( 'param-changed', 1 );
		} else {
			// On change other fields - mark only this field
			obj.parents('[data-param]').eq(0).data( 'param-changed', 1 );
		}
	}



	// -------------------------- 'INHERIT' FIELDS -------------------------------

	// Toggle inherit button and cover
	jQuery( '#jacqueline_options_tabs' )
		.on( 'keydown', '.jacqueline_options_inherit_lock', function( e ) {
			// If 'Enter' or 'Space' is pressed - trigger click on this object
			if ( [ 13, 32 ].indexOf( e.which ) >= 0 ) {
				jQuery( this ).trigger( 'click' );
				e.preventDefault();
				return false;
			}
			return true;
		} )
		.on( 'click', '.jacqueline_options_inherit_lock,.jacqueline_options_inherit_cover', function (e) {
			var obj = jQuery( this );
			if ( ! obj.hasClass( 'jacqueline_options_pro_only_lock' ) && ! obj.hasClass( 'jacqueline_options_pro_only_cover' ) ) {
				var parent  = obj.parents( '.jacqueline_options_item,.jacqueline_options_group' );
				var inherit = parent.hasClass( 'jacqueline_options_inherit_on' );
				var cover   = parent.find( '.jacqueline_options_inherit_cover' );
				var hidden  = cover.find( 'input[type="hidden"]' );
				var hidden_name = hidden.attr( 'name' ) || '';
				var fld     = parent.find( '[name="' + hidden_name.replace( '_inherit_', '_field_' ) + '"]' );
				if (inherit) {
					parent.removeClass( 'jacqueline_options_inherit_on' ).addClass( 'jacqueline_options_inherit_off' );
					cover.fadeOut();
					hidden.val( '' ).trigger('change');
				} else {
					parent.removeClass( 'jacqueline_options_inherit_off' ).addClass( 'jacqueline_options_inherit_on' );
					cover.fadeIn();
					hidden.val( 'inherit' ).trigger('change');
				}
				if ( fld.length ) {
					fld.trigger( 'change' );
				}
				e.preventDefault();
				return false;
			}
		} );



	// -------------------------- RESPONSIVE FIELDS -------------------------------

	// Switch responsive mode
	jQuery( '#jacqueline_options_tabs' )
		.on( 'keydown', '.jacqueline_options_responsive_button', function( e ) {
			// If 'Enter' or 'Space' is pressed - trigger click on this object
			if ( [ 13, 32 ].indexOf( e.which ) >= 0 ) {
				jQuery( this ).trigger( 'click' );
				e.preventDefault();
				return false;
			}
			return true;
		} )
		.on( 'click', '.jacqueline_options_responsive_button', function (e) {
			var $wrap = jQuery( '.jacqueline_options' ),
				$buttons_wrap = jQuery( this ).parents( '.jacqueline_options_responsive_buttons' ),
				cur_mode = $wrap.data( 'responsive' ),
				new_mode = jQuery( this ).data( 'responsive' );
			if ( ! $buttons_wrap.hasClass( 'opened' ) ) {
				jQuery( '.jacqueline_options_responsive_buttons' ).removeClass( 'opened' );
				$buttons_wrap.addClass( 'opened' );
			} else {
				$buttons_wrap.removeClass( 'opened' );
				if ( cur_mode != new_mode ) {
					$wrap
						.attr( 'data-responsive', new_mode )	// Change an attribute for CSS
						.data( 'responsive', new_mode );
				}
			}
			e.preventDefault();
			return false;
		} );

	// ---------------------------- CHECK FOR DEPENDENCIES -------------------------------
	jacqueline_options_check_internal_dependencies();

} );


// Check for dependencies
//--------------------------------------------------------

// Check for external dependencies (for example, "Page template" in the page edit mode)
jQuery( window ).on( 'load', function() {
	"use strict";

	var attempts = 5,
		attempts_timer = setInterval( function() {
			if ( --attempts < 0 && attempts_timer ) {
				clearInterval( attempts_timer );
				attempts_timer = null;
				return;
			}
			jQuery( '.jacqueline_options .jacqueline_options_section' ).each( function () {
				jacqueline_options_check_dependencies( jQuery( this ) );
			} );
		}, 3000 );

} );

// Check for internal dependencies
function jacqueline_options_check_internal_dependencies() {
	// Check all inner dependencies
	jQuery( '.jacqueline_options .jacqueline_options_section' ).each( function () {
		jacqueline_options_check_dependencies( jQuery( this ) );
	} );

	// Check dependencies on any field change
	jQuery( '.jacqueline_options .jacqueline_options_item_field [name^="jacqueline_options_field_"]' ).on( 'change', function () {
		jacqueline_options_check_dependencies( jQuery( this ).parents( '.jacqueline_options_section' ) );
	} );

	// Check dependencies on a field with a page template is appear
	jQuery( document ).on( 'trx_addons_action_page_template_selector_appear', function() {			
		jQuery( '.jacqueline_options .jacqueline_options_section' ).each( function () {
			jacqueline_options_check_dependencies( jQuery( this ) );
		} );
	} );
}

// Check for dependencies
function jacqueline_options_check_dependencies(cont) {
	if ( typeof jacqueline_dependencies == 'undefined' || JACQUELINE_STORAGE['check_dependencies_now'] ) {
		return;
	}
	JACQUELINE_STORAGE['check_dependencies_now'] = true;
	cont.find( '.jacqueline_options_item_field,.jacqueline_options_group[data-param]' ).each( function() {
		var ctrl = jQuery( this ),
			id = ctrl.data( 'param' );
		if (id === undefined) {
			return;
		}
		var depend = false, fld;
		for (fld in jacqueline_dependencies) {
			if (fld == id) {
				depend = jacqueline_dependencies[id];
				break;
			}
		}
		if (depend) {
			var dep_cnt    = 0, dep_all = 0;
			var dep_cmp    = typeof depend.compare != 'undefined' ? depend.compare.toLowerCase() : 'and';
			var dep_strict = typeof depend.strict != 'undefined';
			var val        = undefined;
			var name       = '', subname = '';
			var parts      = '', parts2 = '';
			var i;
			fld = null;
			for (i in depend) {
				if (i == 'compare' || i == 'strict') {
					continue;
				}
				dep_all++;
				val     = undefined;
				name    = i;
				subname = '';
				if (name.indexOf( '[' ) > 0) {
					parts   = name.split( '[' );
					name    = parts[0];
					subname = parts[1].replace( ']', '' );
				}
				// If a name is a selector to the DOM-object 
				if ( name.charAt( 0 ) == '#' || name.charAt( 0 ) == '.' || name.slice( 0, 8 ) == '@editor/' ) {
					if ( name.charAt( 0 ) == '#' || name.charAt( 0 ) == '.' ) {
						fld = jQuery( name );
					}
					if ( fld && fld.length > 0 ) {
						var panel = fld.closest('.edit-post-sidebar');
						if ( panel.length === 0 ) {
							if ( ! fld.hasClass('jacqueline_inited') ) {
								fld.addClass('jacqueline_inited').on('change', function () {
									jQuery('.jacqueline_options .jacqueline_options_section').each( function () {
										jacqueline_options_check_dependencies(jQuery(this));
									} );
								} );
							}
						} else {
							if ( ! panel.hasClass('jacqueline_inited') ) {
								panel.addClass('jacqueline_inited').on('change', fld, function () {
									jQuery('.jacqueline_options .jacqueline_options_section').each( function () {
										jacqueline_options_check_dependencies(jQuery(this));
									} );
								} );
							}
						}
					} else if ( location.href.indexOf( 'page=theme_options' ) < 0 && ( name == '#page_template' || name == '.editor-page-attributes__template select' || name.slice( 0, 8 ) == '@editor/' ) ) {
						var prop_check = 'template';
						if ( name.slice( 0, 8 ) == '@editor/' ) {
							prop_check = name.slice( 8 );
						}
						if ( typeof wp == 'object' && typeof wp.data == 'object' && typeof wp.data.select( 'core/editor' ) == 'object' ) {
							if ( typeof JACQUELINE_STORAGE['editor_props'] == 'undefined' ) {
								JACQUELINE_STORAGE['editor_props'] = {};
							}
							if ( typeof JACQUELINE_STORAGE['editor_props'][ prop_check ] == 'undefined' ) {
								var prop_val = wp.data.select( 'core/editor' ).getEditedPostAttribute( prop_check );
								if ( prop_val !== undefined ) {
									JACQUELINE_STORAGE['editor_props'][ prop_check ] = prop_val;
								}
							}
							val = typeof JACQUELINE_STORAGE['editor_props'][ prop_check ] != 'undefined' ? JACQUELINE_STORAGE['editor_props'][ prop_check ] : '';
							var $body = jQuery( 'body' );
							if ( ! $body.hasClass( 'jacqueline_editor_props_listener_inited' ) ) {
								$body.addClass( 'jacqueline_editor_props_listener_inited' );
								// Call a check_dependencies() on a page template is changed
								wp.data.subscribe( function() {
									var prop_val = wp.data.select( 'core/editor' ).getEditedPostAttribute( prop_check );
									if ( prop_val !== undefined && ( typeof JACQUELINE_STORAGE['editor_props'][ prop_check ] == 'undefined' || prop_val != JACQUELINE_STORAGE['editor_props'][ prop_check ] ) ) {
										JACQUELINE_STORAGE['editor_props'][ prop_check ] = prop_val;
										jQuery('.jacqueline_options .jacqueline_options_section').each( function () {
											jacqueline_options_check_dependencies( jQuery( this ) );
										} );
									}

								} );
							}
						}
					}
				// A name is a field from options
				} else {
					fld = cont.find( '[name="jacqueline_options_field_' + name + '"]' );
				}
				if ( val !== undefined || ( fld && fld.length > 0 ) ) {
					if ( val === undefined ) {
						val = jacqueline_options_get_field_value( fld );
					}
					if ( val == 'inherit' ) {
						dep_cnt = 0;
						dep_all = 1;
						var parent = ctrl,
							tag;
						if ( ! parent.hasClass('jacqueline_options_group') ) {
							parent = parent.parents('.jacqueline_options_item');
						}
						var lock = parent.find( '.jacqueline_options_inherit_lock' );
						if ( lock.length ) {
							if ( ! parent.hasClass( 'jacqueline_options_inherit_on' ) ) {
								lock.trigger( 'click' );
							}
						} else if ( ctrl.data('type') == 'select' ) {
							tag = ctrl.find('select');
							if ( tag.find('option[value="inherit"]').length && tag.val() != 'inherit' ) {
								tag.val('inherit').trigger('change');
							}
						} else if ( ctrl.data('type') == 'radio' ) {
							tag = ctrl.find('input[type="radio"][value="inherit"]');
							if ( tag.length && ! tag.get(0).checked ) {
								ctrl.find('input[type="radio"]:checked').get(0).checked = false;
								tag.get(0).checked = true;
								tag.trigger('change');
							}
						}
						break;
					} else {
						if (subname !== '') {
							parts = val.split( '|' );
							for (var p = 0; p < parts.length; p++) {
								parts2 = parts[p].split( '=' );
								if (parts2[0] == subname) {
									val = parts2[1];
								}
							}
						}
						if ( typeof depend[i] != 'object' && typeof depend[i] != 'array' ) {
							depend[i] = { '0': depend[i] };
						}
						for (var j in depend[i]) {
							if (
								(depend[i][j] == 'not_empty' && val !== '')   // Main field value is not empty - show current field
								|| (depend[i][j] == 'is_empty' && val === '') // Main field value is empty - show current field
								|| (val !== '' && ( ! isNaN( depend[i][j] )   // Main field value equal to specified value - show current field
												? val == depend[i][j]
												: (dep_strict
														? val == depend[i][j]
														: ('' + val).indexOf( depend[i][j] ) === 0
													)
											)
								)
								|| (val !== '' && ("" + depend[i][j]).charAt( 0 ) == '^' && ('' + val).indexOf( depend[i][j].substr( 1 ) ) == -1)
																			// Main field value not equal to specified value - show current field
							) {
								dep_cnt++;
								break;
							}
						}
					}
				} else {
					dep_all--;
				}
				if (dep_cnt > 0 && dep_cmp == 'or') {
					break;
				}
			}
			if ( ! ctrl.hasClass('jacqueline_options_group') ) {
				ctrl = ctrl.parents('.jacqueline_options_item');
			}
			var section = ctrl.parents('.jacqueline_tabs_section'),
				tab = jQuery( '[aria-labelledby="' + section.attr('aria-labelledby') + '"]' );
			if (((dep_cnt > 0 || dep_all === 0) && dep_cmp == 'or') || (dep_cnt == dep_all && dep_cmp == 'and')) {
				ctrl.slideDown().removeClass( 'jacqueline_options_no_use' );
				if ( section.find('>.jacqueline_options_item:not(.jacqueline_options_item_info),>.jacqueline_options_group[data-param]').length != section.find('.jacqueline_options_no_use').length ) {
					if ( tab.hasClass( 'jacqueline_options_item_hidden' ) ) {
						tab.removeClass('jacqueline_options_item_hidden');
					}
				}
			} else {
				ctrl.slideUp().addClass( 'jacqueline_options_no_use' );
				if ( section.find('>.jacqueline_options_item:not(.jacqueline_options_item_info),>.jacqueline_options_group[data-param]').length == section.find('.jacqueline_options_no_use').length ) {
					if ( ! tab.hasClass( 'jacqueline_options_item_hidden' ) ) {
						tab.addClass('jacqueline_options_item_hidden');
						if ( tab.hasClass('ui-state-active') ) {
							tab.parents('.jacqueline_tabs').find(' > ul > li:not(.jacqueline_options_item_hidden)').eq(0).find('> a').trigger('click');
						}
					}
				}
			}
		}

		// Individual dependencies
		//------------------------------------

		// Remove 'false' to disable color schemes less then main scheme!
		// This behavious is not need for the version with sorted schemes (leave false)
		if (false && id == 'color_scheme') {
			fld = ctrl.find( '[name="jacqueline_options_field_' + id + '"]' );
			if (fld.length > 0) {
				val     = jacqueline_options_get_field_value( fld );
				var num = jacqueline_options_get_field_value( fld, true );
				cont.find( '.jacqueline_options_item_field' ).each(
					function() {
						var ctrl2 = jQuery( this ), id2 = ctrl2.data( 'param' );
						if (id2 == undefined) {
							return;
						}
						if (id2 == id || id2.substr( -7 ) != '_scheme') {
							return;
						}
						var fld2 = ctrl2.find( '[name="jacqueline_options_field_' + id2 + '"]' ),
						val2     = jacqueline_options_get_field_value( fld2 );
						if (fld2.attr( 'type' ) != 'radio') {
							fld2 = fld2.find( 'option' );
						}
						fld2.each(
							function(idx2) {
								var dom_obj      = jQuery( this ).get( 0 );
								dom_obj.disabled = idx2 !== 0 && idx2 < num;
								if (dom_obj.disabled) {
									if (jQuery( this ).val() == val2) {
										if (fld2.attr( 'type' ) == 'radio') {
											fld2.each(
												function(idx3) {
													jQuery( this ).get( 0 ).checked = idx3 === 0;
												}
											);
										} else {
											fld2.each(
												function(idx3) {
													jQuery( this ).get( 0 ).selected = idx3 === 0;
												}
											);
										}
									}
								}
							}
						);
					}
				);
			}
		}
	} );
	JACQUELINE_STORAGE['check_dependencies_now'] = false;
}

// Return value of the field or number (index) of selected item (if second param is true)
function jacqueline_options_get_field_value(fld, num) {
	var item = fld.parents( '.jacqueline_options_item' );
	var ctrl = fld.parents( '.jacqueline_options_item_field' );
	var val  = fld.attr( 'type' ) == 'checkbox' || fld.attr( 'type' ) == 'radio'
			? (ctrl.find( '[name^="jacqueline_options_field_"]:checked' ).length > 0
				? (num === true
					? ctrl.find( '[name^="jacqueline_options_field_"]:checked' ).parent().index() + 1
					: (ctrl.find( '[name^="jacqueline_options_field_"]:checked' ).val() !== ''
						&& '' + ctrl.find( '[name^="jacqueline_options_field_"]:checked' ).val() != '0'
							? ctrl.find( '[name^="jacqueline_options_field_"]:checked' ).val()
							: 1
						)
					)
				: 0
				)
			: (num === true ? fld.find( ':selected' ).index() + 1 : fld.val());
	if ( item.length && item.hasClass( 'jacqueline_options_inherit_on' ) ) {
		val = num === true ? 0 : 'inherit';
	} else if (val === undefined || val === null) {
		val = num === true ? 0 : '';
	}
	return val;
}
