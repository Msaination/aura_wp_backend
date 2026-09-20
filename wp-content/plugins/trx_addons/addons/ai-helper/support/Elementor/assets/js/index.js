(function ($) {

	"use strict";

    window.addEventListener( 'elementor/init', () => {

		function trx_addons_ai_helper_elm_gen_text_add_modal_type( view ) {
			DialogsManager.addWidgetType( 'trx_addons_ai_elementor_generate_text', DialogsManager.getWidgetType( 'lightbox' ).extend( 'trx_addons_ai_elementor_generate_text', {
				onReady: function() {

					DialogsManager.getWidgetType( 'lightbox' ).prototype.onReady.apply( this, arguments );

					var self = this;

					// Create modal Header
					var $header = $( '\
						<div class="trx_addons_ai_elm_gen_text_header_inner">\
							<div class="trx_addons_ai_elm_gen_text_header_left">\
								<p class="trx_addons_ai_elm_gen_text_header_logo">' + TRX_ADDONS_STORAGE['elm_ai_generate_text_btn_label'] + '</p>\
							</div>\
							<div class="trx_addons_ai_elm_gen_text_header_right">\
								<a href="javascript:void(0);" role="button" class="trx_addons_ai_elm_gen_text_close trx_addons_button_close" title="' + TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_close'] + '">\
									<span class="trx_addons_button_close_icon"></span>\
								</a>\
							</div>\
						</div>' );

					$header.find( '.trx_addons_ai_elm_gen_text_close' ).on( 'click', function(e) {
						e.preventDefault();
						self.hide();
					} );

					self.getElements( 'header' ).append( $header );


					// Create modal Message
					var title_cases = '', title_case_default = TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_title_case_default'] || 'title';
					if ( TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_title_case_list'] ) {
						$.each( TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_title_case_list'], function( key, value ) {
							title_cases += '<label><input type="radio" name="trx_addons_ai_elm_gen_text_msg_input_title_case" id=" name="trx_addons_ai_elm_gen_text_msg_input_title_case_' + key + '" value="' + key + '"' + ( title_case_default == key ? ' checked' : '' ) + '>' + value + '</label>';
						} );
					}
					var form_html = '<div class="trx_addons_ai_elm_gen_text_msg_inner">\
							<div class="trx_addons_ai_elm_gen_text_msg_input">\
								<label class="trx_addons_ai_elm_gen_text_msg_label" for="trx_addons_ai_elm_gen_text_msg_input_purpose">' + TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_purpose_label'] + '</label>\
								<input type="text" id="trx_addons_ai_elm_gen_text_msg_input_purpose" class="trx_addons_ai_elm_gen_text_msg_input_purpose" value="' + ( ['Container', 'Section', 'Column'].indexOf( view.model.getTitle() ) == -1 ? view.model.getTitle() : TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_purpose_pl'] ) + '" />\
							</div>\
							<div class="trx_addons_ai_elm_gen_text_msg_input">\
								<label class="trx_addons_ai_elm_gen_text_msg_label" for="trx_addons_ai_elm_gen_text_msg_input_title_case_title">' + TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_title_case_label'] + '</label>\
								' + title_cases + '\
							</div>\
							<div class="trx_addons_ai_elm_gen_text_msg_input">\
								<label class="trx_addons_ai_elm_gen_text_msg_label" for="trx_addons_ai_elm_gen_text_msg_input_temperature">' + TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_temperature_label'] + '</label>\
								<input type="number" id="trx_addons_ai_elm_gen_text_msg_input_temperature" min="0" max="2" step="0.1" class="trx_addons_ai_elm_gen_text_msg_input_temperature" value="' + TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_temperature_pl'] + '" />\
							</div>\
							<div class="trx_addons_ai_elm_gen_text_msg_input">\
								<label class="trx_addons_ai_elm_gen_text_msg_label" for="trx_addons_ai_elm_gen_text_msg_input_prompt">' + TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_prompt_label'] + '</label>\
								<textarea id="trx_addons_ai_elm_gen_text_msg_input_prompt" type="text" class="trx_addons_ai_elm_gen_text_msg_input_prompt"></textarea>\
							</div>\
							<div class="trx_addons_ai_elm_gen_text_msg_sbm">\
								<a href="javascript:void(0);" role="button" class="elementor-button e-primary trx_addons_ai_elm_gen_text_msg_sbm_btn" title="' + TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_submit'] + '">\
									<span class="trx_addons_ai_elm_gen_text_msg_sbm_btn_label">' + TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_submit'] + '</span>\
								</a>\
							</div>\
						</div>';

					var $form = $( form_html );

					$form.find( '.trx_addons_ai_elm_gen_text_msg_sbm_btn' ).on( 'click', function (e) {
						e.preventDefault();
						$(this).addClass( 'trx_addons_loading' );
						self.submitData();
						// Hide modal after the server response is received
						// self.hide();
						return false;
					} );

					self.getElements( 'message' ).append( $form );
				},
				submitData: () => {},
			} ) );
		}

		// Apply only the changed settings of the regenerated tree to the existing
		// containers, instead of deleting and re-creating the original element.
		// This keeps Atomic Editor V4 element identity (id, styles, classes,
		// custom CSS, interactions, base styles) intact and avoids re-mounting
		// React-based atomic widgets, which is fragile.
		// Children are matched by id first (the AI keeps element ids unchanged),
		// with a fallback to positional match for safety.
		function trx_addons_ai_helper_elm_apply_text_changes( newModel, container ) {
			if ( ! newModel || ! container ) {
				return;
			}

			var newSettings = ( newModel.settings && typeof newModel.settings === 'object' ) ? newModel.settings : {};
			var currentSettings = ( container.settings && typeof container.settings.toJSON === 'function' )
				? container.settings.toJSON()
				: {};
			var changedSettings = {};
			var hasChanges = false;

			Object.keys( newSettings ).forEach( function( key ) {
				// Deep-compare via JSON serialization — sufficient for plain prop
				// values produced by atomic_props_schema and for legacy strings.
				if ( JSON.stringify( newSettings[ key ] ) !== JSON.stringify( currentSettings[ key ] ) ) {
					changedSettings[ key ] = newSettings[ key ];
					hasChanges = true;
				}
			} );

			if ( hasChanges ) {
				$e.run( 'document/elements/settings', {
					container: container,
					settings: changedSettings,
				} );
			}

			if ( ! Array.isArray( newModel.elements ) || newModel.elements.length === 0 ) {
				return;
			}

			var children = ( container.children && container.children.length ) ? container.children : [];

			newModel.elements.forEach( function( childModel, idx ) {
				var childContainer = null;

				// 1. Match by id among direct children.
				if ( childModel && childModel.id ) {
					for ( var i = 0; i < children.length; i++ ) {
						if ( children[ i ] && children[ i ].id === childModel.id ) {
							childContainer = children[ i ];
							break;
						}
					}
					// 2. Fallback: lookup the container globally by id.
					if ( ! childContainer && window.elementor && elementor.getContainer ) {
						childContainer = elementor.getContainer( childModel.id ) || null;
					}
				}

				// 3. Fallback: match by index inside the parent.
				if ( ! childContainer && children[ idx ] ) {
					childContainer = children[ idx ];
				}

				if ( childContainer ) {
					trx_addons_ai_helper_elm_apply_text_changes( childModel, childContainer );
				}
			} );
		}

		// Recursively populate missing settings on every element/widget with their
		// declared defaults so the AI helper sees the text that is currently shown
		// in the editor, even if the user has not edited that widget since the
		// page was loaded.
		//
		// Two storage layouts are supported:
		//   * Atomic Editor V4 widgets — defaults live in
		//     widgetsCache[widgetType].atomic_props_schema[propKey].default and
		//     are NEVER injected into the Backbone model attributes; toJSON() can
		//     therefore omit a prop entirely.
		//   * Classic widgets — defaults live in
		//     widgetsCache[widgetType].controls[controlKey].default. They ARE
		//     injected into model attributes by BaseSettingsModel.initialize(),
		//     but toJSON({remove:['default']}) strips back any value that still
		//     equals its default (see BaseSettingsModel.removeDataDefaults), so
		//     unedited text controls disappear from the payload as well.
		//
		// In both cases we only fill keys that are missing from the current
		// settings object — values the user has explicitly changed are kept as is.
		function trx_addons_ai_helper_elm_fill_defaults( element ) {
			if ( ! element || typeof element !== 'object' ) {
				return element;
			}

			var widgetsCache = ( window.elementor && elementor.widgetsCache ) || {};
			var typeKey = 'widget' === element.elType ? element.widgetType : element.elType;
			var cache = typeKey ? widgetsCache[ typeKey ] : null;

			if ( cache ) {
				if ( ! element.settings || typeof element.settings !== 'object' ) {
					element.settings = {};
				}

				var defaultsSource = null;
				if ( cache.atomic_props_schema ) {
					defaultsSource = cache.atomic_props_schema;
				} else if ( cache.controls ) {
					defaultsSource = cache.controls;
				}

				if ( defaultsSource ) {
					Object.keys( defaultsSource ).forEach( function( key ) {
						var def = defaultsSource[ key ];
						if ( ! def || typeof def.default === 'undefined' || def.default === null ) {
							return;
						}
						var current = element.settings[ key ];
						if ( typeof current === 'undefined' || current === null ) {
							element.settings[ key ] = JSON.parse( JSON.stringify( def.default ) );
						}
					} );
				}
			}

			if ( Array.isArray( element.elements ) ) {
				element.elements.forEach( trx_addons_ai_helper_elm_fill_defaults );
			}

			return element;
		}

		window.handleAIElementorGenerateText = function ( groups, view ) {

			var self = this,
				options = {};

			self.addButtonContextMenu = function () {
	
				groups.forEach( ( group ) => {
					if ( 'save' === group.name ) {
						var $new_actions = [];
						group.actions.forEach( ( action ) => {
							if ( 'save' == action.name ) {
								$new_actions.push( {
									name: 'ai-generate-texts',
									icon: 'eicon-ai',
									title: TRX_ADDONS_STORAGE['elm_ai_generate_text_btn_label'],
									isEnabled: () => true,
									callback: () => { self.addNotice() },
								} );
							}
	
							$new_actions.push( action );
						} );
						group.actions = $new_actions;
					}
				} );
	
				return groups;

			};

			self.addNotice = function () {

				if ( typeof DialogsManager == 'undefined' ) return false;

				trx_addons_ai_helper_elm_gen_text_add_modal_type( view );

				var modal = elementorCommon.dialogsManager.createWidget( 'trx_addons_ai_elementor_generate_text', {
					id: 'trx-addons-ai-helper-elementor-generate-text-modal',
					className: 'trx-addons-ai-helper-elementor-generate-text-modal',
				} );

				modal.submitData = () => {
					options.purpose_title = modal.getElements( 'message' ).find( '.trx_addons_ai_elm_gen_text_msg_input_purpose' ).val() || TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_purpose_pl'];
					options.case_title = modal.getElements( 'message' ).find( 'input[name="trx_addons_ai_elm_gen_text_msg_input_title_case"]:checked' ).val() || TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_title_case_default'];
					options.temperature = modal.getElements( 'message' ).find( '.trx_addons_ai_elm_gen_text_msg_input_temperature' ).val() || TRX_ADDONS_STORAGE['elm_ai_generate_text_modal_temperature_pl'];
					options.prompt = modal.getElements( 'message' ).find( '.trx_addons_ai_elm_gen_text_msg_input_prompt' ).val() || '';
					options.modal = modal;
					self.btnCallback();
				};

				modal.show();

			};

			self.btnCallback = function () {

				var content = view.model.toJSON( { remove: ['default'] } );
				trx_addons_ai_helper_elm_fill_defaults( content );

				$.post( TRX_ADDONS_STORAGE['ajax_url'], {
					nonce: TRX_ADDONS_STORAGE['ajax_nonce'],
					action: 'trx_addons_ai_helper_elementor_generate_text',
					purpose: options.purpose_title,
					case: options.case_title,
					temperature: options.temperature,
					prompt: options.prompt,
					// ai_helper: options.ai_helper,
					content: JSON.stringify( content ),
					is_admin_request: 1
				}, function( response ) {
					if ( response ) {
						self.replaceTexts( response );
					}
				} );
			};

			self.fetch_answer = function ( data ) {

				var content = view.model.toJSON( { remove: ['default'] } );//after test - remove
				trx_addons_ai_helper_elm_fill_defaults( content );

				jQuery.post( TRX_ADDONS_STORAGE['ajax_url'], {
					nonce: TRX_ADDONS_STORAGE['ajax_nonce'],
					action: 'trx_addons_ai_helper_elementor_generate_text_fetch',
					thread_id: data.thread_id,
					run_id: data.run_id,
					content: JSON.stringify( content ),  //after test - remove
					is_admin_request: 1
				}, function( response ) {
					if ( response ) {
						self.replaceTexts( response );
					}
				} );
			};

			self.replaceTexts = function( response ) {
				var rez = trx_addons_parse_ajax_response( response, TRX_ADDONS_STORAGE['msg_ai_helper_error'] );

				// If queued - fetch answer again
				if ( rez.finish_reason == 'queued' ) {
					var time = rez.fetch_time ? rez.fetch_time : 2000;
					setTimeout( function() {
						self.fetch_answer( rez );
					}, time );
				} else {
					if ( ! rez.error ) {
						if ( rez.data && rez.data.fields ) {
							var historyId = $e.internal( 'document/history/start-log', {
								type: 'change',
								title: "".concat( TRX_ADDONS_STORAGE['elm_ai_generate_text_btn_label'] ) + ' ' + options.purpose_title,
							} );

							trx_addons_ai_helper_elm_apply_text_changes( rez.data.fields, view.container );

							$e.internal( "document/history/end-log", {
								id: historyId
							} );
						}
					}

					// Hide modal after the server response is received
					options.modal.getElements( 'message' ).find( '.trx_addons_ai_elm_gen_text_msg_sbm_btn' ).removeClass( 'trx_addons_loading' );
					options.modal.hide();

					if ( rez.error ) {
						trx_addons_msgbox_warning( rez.error, '' );
					}
				}
			};

		}

		function iniAIHandlerElementor( groups, view ) {
			var instance = new handleAIElementorGenerateText( groups, view );
			return instance.addButtonContextMenu();
		}

        elementor.hooks.addFilter( 'elements/container/contextMenuGroups', iniAIHandlerElementor );
        elementor.hooks.addFilter( 'elements/section/contextMenuGroups', iniAIHandlerElementor );
        elementor.hooks.addFilter( 'elements/e-flexbox/contextMenuGroups', iniAIHandlerElementor );
        elementor.hooks.addFilter( 'elements/e-grid/contextMenuGroups', iniAIHandlerElementor );
        elementor.hooks.addFilter( 'elements/e-div-block/contextMenuGroups', iniAIHandlerElementor );
        elementor.hooks.addFilter( 'elements/e-tabs/contextMenuGroups', iniAIHandlerElementor );

    } );
}( jQuery ) );