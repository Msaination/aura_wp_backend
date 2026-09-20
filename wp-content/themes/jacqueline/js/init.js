/* global jQuery, JACQUELINE_STORAGE */

jQuery( document ).ready( function() {

	"use strict";

	var ready_busy = true;

	var theme_init_counter = 0;

	var $window   = jQuery( window ),
		$document = jQuery( document ),
		$body     = jQuery( 'body' );

	var $body_wrap,
		$page_wrap,
		$header,
		$footer,
		$menu_side_wrap,
		$menu_side_logo;

	var _header_height = 0,
		_footer_height = 0;

	var $page_content_wrap,
		$content,
		$sidebar,
		cur_page_title_selector,
		$cur_page_title_tag,
		// Single post parts
		$single_nav_links_fixed,
		$single_post_info_fixed,
		$single_post_scrollers,
		$stretch_width;

	var $nav_link_infinite;


	// Update links after the new post added
	$document.on( 'action.new_post_added', update_jquery_links );
	$document.on( 'action.got_ajax_response', update_jquery_links );
	$document.on( 'action.init_hidden_elements', update_jquery_links );
	var first_run = true;
	function update_jquery_links(e) {
		if ( first_run && e && e.namespace == 'init_hidden_elements' ) {
			first_run = false;
			return; 
		}
		$body_wrap              = jQuery( '.body_wrap' ),
		$page_wrap              = jQuery( '.page_wrap' ),
		$header                 = jQuery( '.top_panel' ),
		_header_height          = $header.length === 0 ? 0 : $header.height(),
		$footer                 = jQuery( '.footer_wrap' ),
		_footer_height          = $footer.length === 0 ? 0 : $footer.height(),
		$menu_side_wrap         = jQuery( '.menu_side_wrap' ),
		$menu_side_logo         = $menu_side_wrap.find( '.sc_layouts_logo' );
		$page_content_wrap      = jQuery( '.page_content_wrap' );
		$content                = jQuery( '.content' );
		$sidebar                = jQuery( '.sidebar:not(.sidebar_fixed_placeholder)' );
		cur_page_title_selector = jacqueline_apply_filters( 'jacqueline_filter_page_title_selector', '.sc_layouts_title_caption, head title' );
		$cur_page_title_tag     = jQuery( cur_page_title_selector ).eq(0);
		$nav_link_infinite      = jQuery( '.nav-links-infinite' );
		// Single post parts
		$single_nav_links_fixed = jQuery( '.nav-links-single.nav-links-fixed' );
		$single_post_info_fixed = jQuery( '.post_info_vertical.post_info_vertical_fixed' );
		$single_post_scrollers  = jQuery( '.nav-links-single-scroll' );
		$stretch_width          = jQuery( '.trx-stretch-width' );
	}
	update_jquery_links();


	// Init actions
	//-----------------------------------

	// Init intersection observer
	jacqueline_intersection_observer_init();

	// Init all other actions
	jacqueline_init_actions();

	function jacqueline_init_actions() {

		if (JACQUELINE_STORAGE['vc_edit_mode'] && jQuery( '.vc_empty-placeholder' ).length === 0 && theme_init_counter++ < 30) {
			setTimeout( jacqueline_init_actions, 200 );
			return;
		}

		// Resize handlers
		$window.on( 'resize', function() {
			jacqueline_resize_actions();
		} );

		// Scroll handlers
		JACQUELINE_STORAGE['scroll_busy'] = true;
		$window.on( 'scroll', function() {
			if (window.requestAnimationFrame) {
				if ( ! JACQUELINE_STORAGE['scroll_busy']) {
					window.requestAnimationFrame( function() {
						jacqueline_scroll_actions();
					});
					JACQUELINE_STORAGE['scroll_busy'] = true;
				}
			} else {
				jacqueline_scroll_actions();
			}
		} );

		// Add scheme class and js support
		document.documentElement.className = document.documentElement.className.replace( /\bno-js\b/,'js' );
		if (document.documentElement.className.indexOf( JACQUELINE_STORAGE['site_scheme'] ) == -1) {
			document.documentElement.className += ' ' + JACQUELINE_STORAGE['site_scheme'];
		}

		// First call to init core actions
		jacqueline_ready_actions();
		jacqueline_resize_actions();
		jacqueline_scroll_actions();

	}   // jacqueline_init_actions


	// Theme first load actions
	//==============================================
	function jacqueline_ready_actions() {

		// Skip to content link
		$body
			.on( 'focus', 'a.jacqueline_skip_link', function() {
				if ( ! $body.hasClass( 'show_outline' ) ) {
					$body.addClass( 'show_outline' );
				}
			} )
			.on( 'click', 'a.jacqueline_skip_link', function() {
				var id = jQuery(this).attr('href');
				jQuery(id).focus();
			} );


		// Detect the keyboard usage to show outline around links and fields
		$body.on( 'keydown', 'a,input,textarea,select,span[tabindex]', function( e ) {
			if ( 9 == e.which ) {
				if ( ! $body.hasClass( 'show_outline' ) ) {
					$body.addClass( 'show_outline' );
				}
			}
		} );


		// Menus
		//-------------------------------------
		$document
			.on( 'keyup', function(e) {
				if ( e.keyCode == 27 ) {
					if ( jQuery( '.menu_mobile.opened' ).length == 1 ) {
						jacqueline_mobile_menu_close();
						e.preventDefault();
						return false;
					}
				}
			} )
			.on( 'action.trx_addons_inner_links_click', function( e, link_obj, original_e ) {
				if ( $body.hasClass( 'menu_mobile_opened' ) ) {
					// Removing class before menu closing animations started
					// is need to animate inner links and scrolling to the anchor
					$body.removeClass( 'menu_mobile_opened' );
					jacqueline_mobile_menu_close();
				}
			} );


		// Pagination
		//------------------------------------

		// Infinite scroll in the blog streampages
		$document.on( 'action.scroll_jacqueline', function(e) {
			jacqueline_infinite_scroll_in_blog();
		} );

		// Infinite scroll in the single posts
		$document.on( 'action.scroll_jacqueline', function(e) {
			jacqueline_infinite_scroll_in_single();
		} );

		// Mark single post as readed
		if ( $body.hasClass('single') ) {
			jacqueline_add_to_read_list( jQuery('.content > article[data-post-id]').data('post-id') );
		}

		// Mark readed posts
		$document.on( 'action.init_hidden_elements', function( e, cont ) {
			var read_list = jacqueline_get_storage('jacqueline_post_read');
			if ( read_list && read_list.charAt(0) == '[' ) {
				read_list = JSON.parse( read_list );
				for ( var p = 0; p < read_list.length; p++ ) {
					var read_post = cont.find('[data-post-id="' + read_list[ p ] + '"]');
					if ( ! read_post.hasClass('full_post_read') && ! read_post.parent().hasClass('content') ) {
						read_post.addClass('full_post_read');
					}
				}
			}
		} );


		// Comments
		//------------------------------------

		// Scroll to comments (if hidden)
		if ( location.hash == '#comments' || location.hash == '#respond' ) {
			var $show_comments_button = jQuery( '.show_comments_button' );
			if ( $show_comments_button.length == 1 && ! $show_comments_button.hasClass( 'opened' ) ) {
				$show_comments_button.trigger( 'click' );
				jacqueline_document_animate_to( location.hash );
			}
		}

		// Other settings
		//------------------------------------
		$document.trigger( 'action.ready_jacqueline' );

		// Blocks with stretch width
		//----------------------------------------------
		// Action to prepare stretch blocks in the third-party plugins
		$document.trigger( 'action.prepare_stretch_width' );
		// Wrap stretch blocks
		$stretch_width = jQuery( '.trx-stretch-width' );
		$stretch_width.wrap( '<div class="trx-stretch-width-wrap"></div>' );
		$stretch_width.after( '<div class="trx-stretch-width-original"></div>' );
		jacqueline_stretch_width();

		// Add theme-specific handlers on 'action.init_hidden_elements'
		//---------------------------------------------------------------
		$document.on( 'action.init_hidden_elements', jacqueline_init_post_formats );
		$document.on( 'action.init_hidden_elements', jacqueline_add_toc_to_sidemenu );

 		// Init hidden elements
		$document.trigger( 'action.init_hidden_elements', [$body.eq(0)] );

	} // jacqueline_ready_actions



	// Post formats init
	//=====================================================
	function jacqueline_init_post_formats(e, cont) {

		// Menu
		jacqueline_init_menus( cont );

		// Wrap select with .select_container
		jacqueline_add_select_container( cont );

		// Use Bideo or Tubular to play a local video
		jacqueline_init_bg_video( cont );

		// Tabs
		jacqueline_init_tabs( cont );

		// Accordion
		jacqueline_init_accordion( cont );

		// Sidebar open/close
		jacqueline_init_sidebar_control( cont );

		// Masonry posts
		jacqueline_init_masonry( cont );

		// Load more
		jacqueline_init_load_more( cont );

		// Open single post right in the blog or in the shortcode "Blogger"
		jacqueline_init_load_single_post( cont );

		// MediaElement init
		jacqueline_init_media_elements( cont );

		// Video play button
		jacqueline_init_video_play_button( cont );

		// Show/Hide Comments
		jacqueline_init_comments_button( cont );

		// Show comments block when link '#comments' or '#respond' is clicked
		jacqueline_init_comments_links( cont );

		// Checkbox with "I agree..."
		jacqueline_init_checkbox_i_agree( cont );

	}  // jacqueline_init_post_formats


	// Wrap select with .select_container
	function jacqueline_add_select_container( cont ) {
		if ( JACQUELINE_STORAGE['select_container_disabled'] || $body.hasClass( 'wp-admin' ) || $body.hasClass( 'wp-customizer' ) ) {
			return;
		}
		var selector = jacqueline_apply_filters(
			'jacqueline_filter_select_container_selector',
			'select:not(.esg-sorting-select):not([class*="trx_addons_attrib_"]):not(#rating)'	//:not([size])
		);
		if ( selector ) {
			cont.find( selector ).each( function() {
				var $self = jQuery( this );
				if ( $self.css( 'display' ) != 'none'
					&& $self.parents( '.select_container' ).length === 0
					&& ! $self.next().hasClass( 'select2' )
					&& ! $self.hasClass( 'select2-hidden-accessible' )
					&& ! $self.hasClass( 'components-select-control__input' )
				) {
					var add_class = '';
					if ( $self.prop( 'size' ) > 1 ) {
						add_class += ' select_container_multirows';
					}
					if ( $self.prop( 'multiple' ) ) {
						add_class += ' select_container_multiple';
					}
					$self.wrap( '<div class="select_container' + add_class + '"></div>' );
					// Bubble submit() up for widget "Categories"
					if ( $self.parents( jacqueline_apply_filters( 'jacqueline_filter_bubble_submit_form', '.widget_categories' ) ).length > 0 ) {
						$self.parent().get(0).submit = function() {
							jQuery(this).closest('form').eq(0).submit();
						};
					}
				}
			} );
		}
	}
	// Add select_container on 'ajaxComplete'
	$document.on( 'ajaxComplete', function(e) {
		// Trigger event after timeout to allow other scripts add elements on the page
		setTimeout( function() {
			jacqueline_add_select_container( $body );
		}, 100 );
	} );


	// Use Bideo or Tubular to play a local video
	function jacqueline_init_bg_video( cont ) {
		var $top_panel_with_bg_video = cont.find( '.top_panel.with_bg_video:not(.inited)' ).addClass( 'inited' );
		if ( JACQUELINE_STORAGE['background_video'] && $top_panel_with_bg_video.length > 0 && window.Bideo ) {
			// Waiting 10ms after mejs init
			setTimeout(
				function() {
					$top_panel_with_bg_video.prepend( '<video id="background_video" loop muted></video>' );
					var bv = new Bideo();
					bv.init(
						{
							// Video element
							videoEl: document.querySelector( '#background_video' ),

							// Container element
							container: document.querySelector( '.top_panel' ),

							// Resize
							resize: true,

							// autoplay: false,

							isMobile: window.matchMedia( '(max-width: 768px)' ).matches,

							playButton: document.querySelector( '#background_video_play' ),
							pauseButton: document.querySelector( '#background_video_pause' ),

							// Array of objects containing the src and type
							// of different video formats to add
							// For example:
							//	src: [
							//			{	src: 'night.mp4', type: 'video/mp4' }
							//			{	src: 'night.webm', type: 'video/webm;codecs="vp8, vorbis"' }
							//		]
							src: [
							{
								src: JACQUELINE_STORAGE['background_video'],
								type: 'video/' + jacqueline_get_file_ext( JACQUELINE_STORAGE['background_video'] )
							}
							],

							// What to do once video loads (initial frame)
							onLoad: function () {
								// For example: document.querySelector('#background_video_cover').style.display = 'none';
							}
						}
					);
				}, 10
			);

			// Use Tubular to play video from Youtube
		} else if ( jQuery.fn.tubular ) {
			cont.find( '#background_video:not(.inited)' ).each( function() {
				var $self = jQuery( this ).addClass( 'inited' );
				var youtube_code = $self.data( 'youtube-code' );
				if ( youtube_code ) {
					$self.tubular( { videoId: youtube_code } );
					jQuery( '#tubular-player' ).appendTo( $self ).show();
					jQuery( '#tubular-container,#tubular-shield' ).remove();
				}
			} );
		}
	}


	// Init media elements
	//--------------------------------------------
	JACQUELINE_STORAGE['mejs_attempts'] = 0;
	function jacqueline_init_media_elements(cont) {
		var audio_selector = jacqueline_apply_filters( 'jacqueline_filter_mediaelements_audio_selector', 'audio:not(.inited):not([src*=".ogg"])' ),
			video_selector = jacqueline_apply_filters( 'jacqueline_filter_mediaelements_video_selector', 'video:not(.inited):not([nocontrols]):not([controls="0"]):not([controls="false"]):not([controls="no"])' ),	//:not([autoplay])
			media_selector = audio_selector + ( audio_selector && video_selector ? ',' : '') + video_selector;
		if (JACQUELINE_STORAGE['use_mediaelements'] && cont.find( media_selector ).length > 0) {
			if ( window.mejs ) {
				if (window.mejs.MepDefaults) {
					window.mejs.MepDefaults.enableAutosize = true;
				}
				if (window.mejs.MediaElementDefaults) {
					window.mejs.MediaElementDefaults.enableAutosize = true;
				}
				// Disable init for video[autoplay]
				cont.find(
						  // Old shortcode 'wp-video'
						  'video.wp-video-shortcode[autoplay],'
						+ 'video.wp-video-shortcode[nocontrols],'
						+ 'video.wp-video-shortcode[controls="0"],'
						+ 'video.wp-video-shortcode[controls="false"],'
						+ 'video.wp-video-shortcode[controls="no"],'
						// New block 'video'
						+ '.wp-block-video > video[autoplay],'
						+ '.wp-block-video > video[nocontrols],'
						+ '.wp-block-video > video[controls="0"],'
						+ '.wp-block-video > video[controls="false"],'
						+ '.wp-block-video > video[controls="no"]'
						)
					.removeClass('wp-video-shortcode');
				// Init mediaelements
				cont.find( media_selector ).each(
					function() {
						var $self = jQuery( this );
						// If item now invisible or it's a video in the Revolution Slider
						if (   $self.hasClass( 'sr7-media' )
							|| $self.parent().is( 'rs-bgvideo' )
							|| $self.parents( 'rs-slide,div:hidden,section:hidden,article:hidden' ).length > 0
						) {
							return;
						}
						var not_init_in_containers = jacqueline_apply_filters( 'jacqueline_filter_mediaelements_not_init_in_containers',
														'.mejs-mediaelement'
														+ ',.wp-block-cover'
														+ ',.wp-block-video'
														+ ',.wp-block-media-text'
														+ ',.elementor-background-video-container'
														+ ',.elementor-widget-video'
														+ ',.with_video_autoplay'
														+ ',.slider-slide'
														+ ',.sc_layouts_title'
													);
						if (   ! $self.hasClass( 'no-mejs' )
							&& ! $self.hasClass( 'no-mediaelement' )
							&& ! $self.hasClass( 'wp-block-cover__video-background' )
							&& $self.addClass( 'inited' ).parents( not_init_in_containers ).length === 0
							&& ( JACQUELINE_STORAGE['init_all_mediaelements']
								|| ( ! $self.hasClass( 'wp-audio-shortcode' )
									&& ! $self.hasClass( 'wp-video-shortcode' )
									&& ! $self.parent().hasClass( 'wp-playlist' )
									)
								)
						) {
							// Fix for iOS
							if ( jacqueline_browser_is_ios( true ) ) {
								// Video on the single car and property pages should be preloaded to show the poster
								if ( $self.parents( '.cars_page_video_wrap,.properties_page_video_wrap' ).length ) {
									$self.attr( {
										'preload': 'auto'
									} );
								}
							}
							var settings = {
								enableAutosize: true,
								videoWidth:  '100%',
								videoHeight: '100%',
								audioWidth:  '100%',
								audioHeight: 40,
								success: function(mejs) {
									if ( mejs.pluginType && 'flash' === mejs.pluginType && mejs.attributes ) {
										mejs.attributes.autoplay
											&& 'false' !== mejs.attributes.autoplay
											&& mejs.addEventListener( 'canplay', function () { mejs.play(); }, false );
										mejs.attributes.loop
											&& 'false' !== mejs.attributes.loop
											&& mejs.addEventListener( 'ended', function () { mejs.play(); }, false );
									}
								}
							};

							// If video is inside a container, get the container's width
							if ( $self.is( 'video' ) ) {
								var media_cont = $self.parents('.sc_layouts_popup').eq(0);
								// If video is inside a popup
								if ( media_cont.length ) {
									var w_attr = $self.attr( 'width' ),
										h_attr = $self.attr( 'height' ),
										h_cont = jacqueline_window_height() * 0.75;	// This coefficient must be the less than max-height of the popup (in the layouts.scss)
									if ( w_attr && h_attr ) {
										settings.videoWidth  = w_attr * h_cont / h_attr;
										settings.videoHeight = h_cont;
										media_cont.parents( '.mfp-content' ).eq(0).css( { 'min-width': 'unset' } );
									}
								} else {
									// Uncomment (remove false) to set the container's dimensions as the video's dimensions
									// Default dimensions are set 100% width and 100% height
									if ( false ) {
										var media_cont = $self.parents('.post_video').eq(0);
										if ( media_cont.length === 0 ) {
											media_cont = $self.parents('.video_frame').eq(0);
											if ( media_cont.length === 0 ) {
												media_cont = $self.parent();
											}
										}
										if ( media_cont.length > 0 ) {
											settings.videoWidth = media_cont.width();
											settings.videoHeight = media_cont.height();
										}
									}
								}
							}
							$self.mediaelementplayer( settings );
						}
					}
				);
			} else if ( JACQUELINE_STORAGE['mejs_attempts']++ < 5 ) {
				setTimeout( function() { jacqueline_init_media_elements( cont ); }, 400 );
			}
		}
		// Init all media elements after first run
		setTimeout( function() { JACQUELINE_STORAGE['init_all_mediaelements'] = true; }, 1000 );
	}


	// Video play button
	function jacqueline_init_video_play_button( cont ) {
		cont.find( '.post_featured.with_thumb .post_video_hover:not(.post_video_hover_popup):not(.inited)' )
			.addClass( 'inited' )
			.on( 'click', function(e) {
				var $self = jQuery( this ),
					$post_featured = $self.parents( '.post_featured' ).eq(0).addClass( 'post_video_play' );
				// Replace a content of the container with the video player
				if ( typeof trx_addons_insert_video_iframe === 'function' ) {
					trx_addons_insert_video_iframe( $post_featured.find( '.post_video' ), $self.data('video') );
				} else {
					$post_featured.find( '.post_video' ).html( $self.data( 'video' ) );
				}
				$document.trigger( 'action.init_hidden_elements', [ $post_featured ] );
				$window.trigger( 'resize' );
				e.preventDefault();
				return false;
			} )
			.parents('.post_featured')
			.on( 'click', function(e) {
				var $self = jQuery(this);
				if ( ! $self.hasClass( 'post_video_play' ) && ! jQuery( e.target ).is( 'a' ) && ! jQuery( e.target ).parents( 'a' ).length ) {
					jQuery(this).find( '.post_video_hover' ).trigger( 'click' );
					e.preventDefault();
					return false;
				}
			} );
	}


	// Accordion
	function jacqueline_init_accordion( cont ) {
		if (jQuery.ui && jQuery.ui.accordion) {
			cont.find( '.jacqueline_accordion:not(.inited)' ).each( function () {
				var $self = jQuery( this );
				var headers = $self.data( 'headers' ) || '.jacqueline_accordion_title';
				// Get height style
				var height_style = $self.data( 'height-style' ) || 'content';
				// Get collapsible
				var collapsible = $self.data( 'collapsible' ) || false;
				// Get initially opened tab
				var init = $self.data( 'active' );
				var active = false;
				if ( isNaN( init ) ) {
					init = 0;
					active = $self.find( headers + '[data-active="true"]' ).eq( 0 );
					if ( active.length > 0 ) {
						while ( ! active.parent().hasClass( 'jacqueline_accordion' ) ) {
							active = active.parent();
						}
						init = active.index();
						if ( isNaN( init ) || init < 0 ) init = 0;
					}
				} else {
					init = Math.max( 0, init );
				}

				// Init accordion
				$self.addClass( 'inited' ).accordion( {
					'active': init,
					'collapsible': collapsible,
					'header': headers,
					'heightStyle': height_style,
					'create': function( event, ui ) {
						if ( ui.panel.length > 0 && ! ready_busy ) {
							$document.trigger( 'action.create_accordion', [ui.panel] );
							$document.trigger( 'action.init_hidden_elements', [ui.panel] );
						} else if ( active !== false && active.length > 0 ) {
							// If headers and panels wrapped into div
							active.find( '>' + headers ).trigger( 'click' );
						}
					},
					'activate': function( event, ui ) {
						if ( ui.oldPanel.length > 0 && ! ready_busy ) {
							$document.trigger( 'action.deactivate_accordion', [ui.oldPanel] );
						}
						if ( ui.newPanel.length > 0 && ! ready_busy ) {
							$document.trigger( 'action.activate_accordion', [ui.newPanel] );
							$document.trigger( 'action.init_hidden_elements', [ui.newPanel] );
							$window.trigger( 'resize' );
						}
					}
				} );
			} );
		}
	}


	// Tabs
	function jacqueline_init_tabs( cont ) {
		if ( jQuery.ui && jQuery.ui.tabs ) {
			cont.find( '.jacqueline_tabs:not(.inited)' ).each( function () {
				var $self = jQuery( this );
				// Get initially opened tab
				var init = $self.data( 'active' );
				if ( isNaN( init ) ) {
					init       = 0;
					var active = $self.find( '> ul > li[data-active="true"]' ).eq( 0 );
					if ( active.length > 0 ) {
						init = active.index();
						if (isNaN( init ) || init < 0) {
							init = 0;
						}
					}
				} else {
					init = Math.max( 0, init );
				}
				// Init tabs
				$self.addClass( 'inited' ).tabs( {
					active: init,
					show: {
						effect: 'fadeIn',
						duration: 300
					},
					hide: {
						effect: 'fadeOut',
						duration: 300
					},
					create: function( event, ui ) {
						if ( ui.panel.length > 0 && ! ready_busy ) {
							$document.trigger( 'action.create_tab', [ui.panel] );
							$document.trigger( 'action.init_hidden_elements', [ui.panel] );
						}
					},
					activate: function( event, ui ) {
						if ( ui.oldPanel.length > 0 && ! ready_busy ) {
							$document.trigger( 'action.deactivate_tab', [ui.oldPanel] );
						}
						if ( ui.newPanel.length > 0 && ! ready_busy ) {
							$document.trigger( 'action.activate_tab', [ui.newPanel] );
							$document.trigger( 'action.init_hidden_elements', [ui.newPanel] );
							$window.trigger('resize');
						}
					}
				} );
				// AJAX loader
				if ( $self.hasClass( 'jacqueline_tabs_ajax' ) ) {
					// AJAX loader for the tabs
					$self.on( 'tabsbeforeactivate', function( event, ui ) {
						if ( ui.newPanel.data( 'need-content' ) ) {
							jacqueline_tabs_ajax_content_loader( ui.newPanel, 1, ui.oldPanel );
						}
					} );
					// AJAX loader for the pages in the tabs
					$self.on( 'click', '.nav-links a', function(e) {
						var $self = jQuery( this );
						var panel = $self.parents( '.jacqueline_tabs_content' );
						var page  = 1;
						var href  = $self.attr( 'href' );
						var pos   = -1;
						if ( ( pos = href.lastIndexOf( '/page/' ) ) != -1 ) {
							page = Number( href.substr( pos + 6 ).replace( "/", "" ) );
							if ( ! isNaN( page )) {
								page = Math.max( 1, page );
							}
						}
						jacqueline_tabs_ajax_content_loader( panel, page );
						e.preventDefault();
						return false;
					} );
				}
			} );
		}
	}


	// Pagination and AJAX loaders
	//==============================================

	// Load the tab's content
	function jacqueline_tabs_ajax_content_loader(panel, page, oldPanel) {
		if (panel.html().replace( /\s/g, '' ) === '') {
			var height = oldPanel === undefined ? panel.height() : oldPanel.height();
			if (isNaN( height ) || height < 100) {
				height = 100;
			}
			panel.html( '<div class="jacqueline_tab_holder" style="min-height:' + height + 'px;"></div>' );
		} else {
			panel.find( '> *' ).addClass( 'jacqueline_tab_content_remove' );
		}
		panel.data( 'need-content', false ).addClass( 'jacqueline_loading' );
		jQuery.post(
			JACQUELINE_STORAGE['ajax_url'], {
				nonce: JACQUELINE_STORAGE['ajax_nonce'],
				action: 'jacqueline_ajax_get_posts',
				blog_template: panel.data( 'blog-template' ),
				blog_style: panel.data( 'blog-style' ),
				posts_per_page: panel.data( 'posts-per-page' ),
				cat: panel.data( 'cat' ),
				parent_cat: panel.data( 'parent-cat' ),
				post_type: panel.data( 'post-type' ),
				taxonomy: panel.data( 'taxonomy' ),
				page: page
			}
		).done(	function( response ) {
			panel.removeClass( 'jacqueline_loading' );
			var rez = jacqueline_parse_ajax_response( response );
			if ( rez.error !== '' ) {
				panel.html( '<div class="jacqueline_error">' + rez.error + '</div>' );
			} else {
				// Get inline styles and add to the page styles
				if ( rez.css !== '' ) {
					jacqueline_import_inline_styles( '<style id="jacqueline-inline-styles-inline-css">' + rez.css + '</style>' );
				}
				// Append posts
				panel
					.prepend( rez.data )
					.fadeIn(
						function() {
							// Scroll page to the content started
							if ( false ) {
								jacqueline_document_animate_to('#content_skip_link_anchor');
							}
							$document.trigger( 'action.init_hidden_elements', [panel] );
							$window.trigger( 'scroll' );
							// Remove old content
							setTimeout( function() {
								panel
									.find( '.jacqueline_tab_holder,.jacqueline_tab_content_remove' ).remove();
								$window.trigger( 'scroll' );
							}, 300 );
						}
					);
				$document.trigger('action.after_add_content', [panel]);
			}
			$document.trigger( 'action.got_ajax_response', {
				action: 'jacqueline_ajax_get_posts',
				result: rez,
				panel: panel
			} );
		} );
	} // jacqueline_tabs_ajax_content_loader


	// Load more
	function jacqueline_init_load_more( cont ) {
		cont.find( '.nav-load-more:not(.inited)' )
			.addClass( 'inited' )
			.on( 'click', function(e) {
				var more = jQuery( this );
				if ( more.data( 'load_more_link_busy' ) ) {
					return false;
				}
				var page     = Number( more.data( 'page' ) );
				var max_page = Number( more.data( 'max-page' ) );
				if ( page >= max_page ) {
					more.parent().addClass( 'all_items_loaded' ).hide();
					return false;
				}
				more.data( 'load_more_link_busy', true )
					.parent().addClass( 'loading' );

				var panel = more.parents( '.jacqueline_tabs_content' );

				// Load simple page content
				if (panel.length === 0) {
					jQuery.get(
						location.href, {
							paged: page + 1
						}
					).done(
						function( response ) {
							// Get inline styles and add to the page styles
							jacqueline_import_inline_styles( response );
							// Get tags 'link' from response and add its to the 'head'
							jacqueline_import_tags_link( response );
							// Get new posts and append to the .posts_container
							var $response = jQuery( response );
							var $response_posts_container = $response.find('.content .posts_container');
							if ( $response_posts_container.length === 0 ) {
								$response_posts_container = $response.find('.posts_container');
							}
							if ( $response_posts_container.length > 0 ) {
								jacqueline_loadmore_add_items(
									$content.find( '.posts_container' ).eq( 0 ),
									$response_posts_container.find(
										'> .masonry_item,'
										+ '> div[class*="column-"],'
										+ '> article'
									)
								);
							}
							$document.trigger( 'action.got_ajax_response', {
								action: 'load_more_next_page',
								result: response
							});
						}
					);

					// Load tab's panel content
				} else {
					jQuery.post(
						JACQUELINE_STORAGE['ajax_url'], {
							nonce: JACQUELINE_STORAGE['ajax_nonce'],
							action: 'jacqueline_ajax_get_posts',
							blog_template: panel.data( 'blog-template' ),
							blog_style: panel.data( 'blog-style' ),
							posts_per_page: panel.data( 'posts-per-page' ),
							cat: panel.data( 'cat' ),
							parent_cat: panel.data( 'parent-cat' ),
							post_type: panel.data( 'post-type' ),
							taxonomy: panel.data( 'taxonomy' ),
							page: page + 1
						}
					).done(
						function( response ) {
							var rez = jacqueline_parse_ajax_response( response );
							if ( rez.error !== '' ) {
								panel.html( '<div class="jacqueline_error">' + rez.error + '</div>' );
							} else {
								// Get inline styles and add to the page styles
								if ( rez.css !== '' ) {
									jacqueline_import_inline_styles( '<style id="jacqueline-inline-styles-inline-css">' + rez.css + '</style>' );
								}
								// Get new posts and append to the .posts_container
								jacqueline_loadmore_add_items(
									panel.find( '.posts_container' ),
									jQuery( rez.data ).find(
										'> .masonry_item,'
										+ '> div[class*="column-"],'
										+ '> article'
									)
								);
							}
							$document.trigger( 'action.got_ajax_response', {
								action: 'jacqueline_ajax_get_posts',
								result: rez,
								panel: panel
							});
						}
					);
				}

				// Append items to the container
				function jacqueline_loadmore_add_items(container, items) {
					if (container.length > 0 && items.length > 0) {
						items.addClass( 'just_loaded_items' );
						container.append( items );
						$document.trigger('action.after_add_content', [container]);
						var just_loaded_items = container.find( '.just_loaded_items' );
						if ( container.hasClass( 'masonry_wrap' ) ) {
							just_loaded_items.addClass( 'hidden' );
							jacqueline_when_images_loaded(
								just_loaded_items, function() {
									just_loaded_items.removeClass( 'hidden' );
									container.masonry( 'appended', items ).masonry();
									// Remove TOC if exists (rebuild on init_hidden_elements)
									jQuery( '#toc_menu' ).remove();
									// Trigger actions to init new elements
									JACQUELINE_STORAGE['init_all_mediaelements'] = true;
									$document.trigger( 'action.init_hidden_elements', [container.parent()] );
								}
							);
						} else {
							just_loaded_items.removeClass( 'just_loaded_items hidden' );
							// Remove TOC if exists (rebuild on init_hidden_elements)
							jQuery( '#toc_menu' ).remove();
							// Trigger actions to init new elements
							JACQUELINE_STORAGE['init_all_mediaelements'] = true;
							$document.trigger( 'action.init_hidden_elements', [container.parent()] );
						}
						more.data( 'page', page + 1 ).parent().removeClass( 'loading' );
					}
					if ( page + 1 >= max_page ) {
						more.parent().addClass( 'all_items_loaded' ).hide();
					}
					more.data( 'load_more_link_busy', false );
					// Fire 'window.resize'
					$window.trigger( 'resize' );
					// Fire 'window.scroll'
					$window.trigger( 'scroll' );
				}

				e.preventDefault();
				return false;
			}
		);
	}


	// Open single post right in the blog or in the shortcode "Blogger"
	function jacqueline_init_load_single_post( cont ) {
		cont.find( '.posts_container:not(.inited_open_full_post),.sc_blogger_content.sc_item_posts_container:not(.inited_open_full_post)' )
			.addClass( 'inited_open_full_post' )
			.on( 'click', 'a', function(e) {
				var link = jQuery(this),
					link_url = link.attr( 'href' ),
					post = link.parents( '.post_item,.sc_blogger_item' ).eq(0),
					post_url = post.find( '.post_title > a,.entry-title > a' ).attr( 'href' ),
					posts_container = post.parents('.posts_container,.sc_item_posts_container').eq(0);
				if ( link_url && post_url && link_url == post_url
					&& ( posts_container.hasClass('open_full_post') || JACQUELINE_STORAGE['open_full_post'] )
					&& ! posts_container.hasClass('no_open_full_post')
					&& ! posts_container.hasClass('columns_wrap')
					&& ! posts_container.hasClass('masonry_wrap')
					&& posts_container.find('.sc_blogger_grid_wrap').length === 0
					&& posts_container.find('.masonry_wrap').length === 0
					&& posts_container.parents('.wp-block-columns').length === 0
					&& ( posts_container.parents('.wpb_column').length === 0 || posts_container.parents('.wpb_column').eq(0).hasClass('vc_col-sm-12') )
					&& ( posts_container.parents('.elementor-column').length === 0 || posts_container.parents('.elementor-column').eq(0).hasClass('elementor-col-100') )
				) {
					posts_container.find('.full_post_opened').removeClass('full_post_opened').show();
					posts_container.find('.full_post_content').remove();
					post.addClass('full_post_loading');
					jQuery.get( jacqueline_add_to_url( post_url, { 'action': 'full_post_loading' } ) ).done( function( response ) {
						if ( response ) {
							var $response = jQuery( response );
							var post_content = $response.find('.content');
							if ( post_content.length > 0 ) {
								// Get inline styles from response
								var inline_styles = response.match( /<style[^>]*id="trx_addons-inline-styles-inline-css"[^>]*>([^<]*)<\/style>/ );
								if ( inline_styles ) {
									jQuery( '#trx_addons-inline-styles-inline-css' ).append( inline_styles[1] );
								}
								// Get tags 'link' from response and add its to the 'head'
								jacqueline_import_tags_link( response );
								// Animate to posts_container
								var cs = post.offset().top - ( post.parents('.posts_container').length > 0 ? 100 : 200 );
								jacqueline_document_animate_to( cs );
								// Insert new content
								post.after( 
										'<div class="full_post_content">'
											+ '<button class="full_post_close" data-post-url="' + post_url + '"></button>'
											+ post_content.html()
										+ '</div>'
									)
									.removeClass('full_post_loading')
									.addClass('full_post_opened')
									.hide()	// instead .slideUp('fast')
									.next().slideDown( 'slow', function() {
										// Trigger actions to init new elements after posts_container is shown
										JACQUELINE_STORAGE['init_all_mediaelements'] = true;
										$document.trigger( 'action.init_hidden_elements', [posts_container] );
										$window.trigger( 'resize' );
									} );
								jacqueline_full_post_read_change_state();
								// Close full post content on click
								post.next().find('.full_post_close')
									.on( 'click', function(e) {
										var content = jQuery(this).parent(),
											cs = content.offset().top - (content.parents('.posts_container').length > 0 ? 100 : 200),
											post = content.prev();
										content.remove();
										jacqueline_full_post_read_change_state();
										post
											.removeClass('full_post_opened')
											.slideDown();
										jacqueline_document_animate_to( cs, 0 );
										e.preventDefault();
										return false;
									} );
								// Remove TOC if exists (rebuild on init_hidden_elements)
								jQuery( '#toc_menu' ).remove();
							}
							$document.trigger( 'action.got_ajax_response', {
								action: 'full_post_loading',
								result: response
							} );
						}
					} );
					e.preventDefault();
					return false;
				}
			} );
	}


	// Infinite scroll in the blog archive page
	function jacqueline_infinite_scroll_in_blog() {
		if ( ! $nav_link_infinite || $nav_link_infinite.length === 0 || $nav_link_infinite.hasClass( 'all_items_loaded' ) ) {
			return;
		}
		var container = $content.find( '> .posts_container,> .blog_archive > .posts_container,> .jacqueline_tabs > .jacqueline_tabs_content:visible > .posts_container' ).eq( 0 );
		if ( container.length == 1 && container.offset().top + container.height() < jacqueline_window_scroll_top() + jacqueline_window_height() * 1.5 ) {
			$nav_link_infinite.find( 'a' ).trigger( 'click' );
		}
	}


	// Infinite scroll in the single post
	function jacqueline_infinite_scroll_in_single() {
		if ( $single_post_scrollers.length === 0 ) {
			return;
		}

		var container      = JACQUELINE_STORAGE['which_block_load'] == 'article'
								? $content.eq( 0 )
								: $page_content_wrap.eq( 0 ),
			cur_page_link  = $single_post_scrollers.length ? $single_post_scrollers.eq(0).data( 'cur-post-link' ) : location.href,
			cur_page_title = $single_post_scrollers.length ? $single_post_scrollers.eq(0).data( 'cur-post-title' ) : ( $cur_page_title_tag.length ? $cur_page_title_tag.text() : '' );

		$single_post_scrollers.each( function() {
			var inf  = jQuery(this),
				link = inf.data('post-link'),
				off  = inf.offset().top,
				st   = jacqueline_window_scroll_top(),
				wh   = jacqueline_window_height();
			
			// Change location url
			if ( inf.hasClass('nav-links-single-scroll-loaded') ) {
				if (link && off < st + wh / 2) {
					cur_page_link  = link;
					cur_page_title = inf.data('post-title');
				}

			// Load next post
			} else if ( ! inf.hasClass('jacqueline_loading') && link && off < st + wh * 2) {
				jacqueline_add_to_read_list( container.find( '.previous_post_content:last-child > article[data-post-id]').data('post-id'));
				inf.addClass('jacqueline_loading');
				jQuery.get( jacqueline_add_to_url( link, { 'action': 'prev_post_loading' } ) ).done(
					function( response ) {
						// Get inline styles and add to the page styles
						jacqueline_import_inline_styles( response );
						// Get tags 'link' from response and add its to the 'head'
						jacqueline_import_tags_link( response );
						// Get article and add it to the page
						var $response = jQuery( response ),
							$response_page_content_wrap = $response.find( '.page_content_wrap' ),
							$response_content = $response.find( '.content' ),
							$response_sidebar = $response.find( '.sidebar' ),
							$response_post_content = JACQUELINE_STORAGE['which_block_load'] == 'article'
														? $response_content
														: $response_page_content_wrap;
						if ( $response_post_content.length > 0 ) {
							var html = $response_post_content.html(),
								response_body_classes = JACQUELINE_STORAGE['which_block_load'] == 'article'
														? null
														: response.match(/<body[^>]*class="([^"]*)"/);
							if ( JACQUELINE_STORAGE['which_block_load'] == 'wrapper' ) {
								if ( $response_sidebar.length === 0
									&& ! response_body_classes
									&& ! $body.hasClass( 'expand_content' )
									&& ! $body.hasClass( 'narrow_content' )
								) {
									$response_post_content.find( '.content' ).width( '100%' );
									html = $response_post_content.html();
								} else if ( $response_sidebar.length > 0 && $body.hasClass( 'narrow_content' ) ) {
									$response_post_content.find( '.post_item_single.post_type_post' ).width( '100%' );
									html = $response_post_content.html();
								}
							}
							container.append(
								'<div class="previous_post_content'
												+ ( response_body_classes
													? ' ' + response_body_classes[1]
													: '' )
												+ ( $response_page_content_wrap.attr( 'data-single-style' ) !== undefined
														? ' single_style_' + $response_page_content_wrap.attr( 'data-single-style' )
														: ''
													)
								+ '">'
									+ html
								+ '</div>'
							);
							inf.removeClass('jacqueline_loading').addClass( 'nav-links-single-scroll-loaded' );
							// Remove TOC if exists (rebuild on init_hidden_elements)
							jQuery( '#toc_menu' ).remove();
							// Trigger actions to init new elements
							JACQUELINE_STORAGE['init_all_mediaelements'] = true;
							$document
								.trigger( 'action.new_post_added', [container] )
								.trigger( 'action.init_hidden_elements', [container] );
							$window.trigger( 'resize' );
						}
						$document.trigger( 'action.got_ajax_response', {
							action: 'prev_post_loading',
							result: response
						});
					}
				);
			}						
		} );
		if ( cur_page_link != location.href ) {
			jacqueline_document_set_location( cur_page_link );
			jQuery( cur_page_title_selector ).html( cur_page_title );
		}
	}


	// Menu navigation
	//==============================================

	// Init menus after the page is loaded
	function jacqueline_init_menus( cont ) {

		// Stretch sidemenu after the logo is loaded
		if ( $body.hasClass( 'menu_side_present' ) && $menu_side_logo.length && ! $menu_side_logo.hasClass( 'inited_stretch' ) ) {
			$menu_side_logo.addClass( 'inited_stretch' );
			if ( ! jacqueline_is_images_loaded( $menu_side_logo ) ) {
				jacqueline_when_images_loaded( $menu_side_logo, function() {
					jacqueline_stretch_sidemenu();
				} );
			}
		}

		var $menus = cont.find( '.sc_layouts_menu:not(.inited_kbd)' ).addClass( 'inited_kbd' );

		// Add images to the menu items with classes image-xxx
		$menus.find( 'li[class*="image-"]' ).each(
			function() {
				var $self   = jQuery( this );
				var classes = $self.attr( 'class' ).split( ' ' );
				var icon    = '';
				for (var i = 0; i < classes.length; i++) {
					if (classes[i].indexOf( 'image-' ) >= 0) {
						icon = classes[i].replace( 'image-', '' );
						break;
					}
				}
				if (icon) {
					$self.find( '>a' ).css( 'background-image', 'url(' + JACQUELINE_STORAGE['theme_url'] + 'trx_addons/css/icons.png/' + icon + '.png' );
				}
			}
		);

		// Open/Close side menu
		cont.find( '.menu_side_button:not(.inited)' )
			.addClass( 'inited' )
			.on( 'click', function( e ) {
				jQuery( this ).parent().toggleClass( 'opened' );
				e.preventDefault();
				return false;
			} );

		// Add arrows to the mobile menu 
		// and close the mobile menu on a link with url is clicked
		// (to prevent display a mobile menu after 'Back' pressed)
		cont.find( '.menu_mobile:not(.inited_arrows),.sc_layouts_menu_dir_vertical:not(.inited_arrows)' )
			.addClass( 'inited_arrows' )
			.find( '.menu-item-has-children > a' )
				.append( '<span class="open_child_menu"></span>' ).end()
			.find ( 'a:not([href="#"])' )
				.on( 'click', function(e) {
					if ( ! jQuery( e.target ).hasClass( 'open_child_menu' ) && ! jQuery(this).is( '[data-tab]' ) ) {
						jacqueline_mobile_menu_close();
					}
				} );

		// Prevent blur focus from the menu_mobile
		cont.find( '.menu_mobile:not(.inited_blur)' )
			.addClass( 'inited_blur' )
			.on( 'keydown', function( e ) {

				if ( e.key !== 'Tab' ) return;

				const modal = document.querySelector('.menu_mobile.opened');
				if ( ! modal ) return;

				// Collect all focusable-in-modal elements
				const focusableElements = Array.from(
					modal.querySelectorAll('a, [tabindex]')
				).filter(el => {
					// Filter out elements that are hidden, disabled, or not visible
					return (
						! el.closest('[hidden]') &&
						! el.hasAttribute('disabled') &&
						el.offsetParent !== null
					);
				} );

				if ( focusableElements.length === 0 ) return;

				const first = focusableElements[0];
				const last = focusableElements[focusableElements.length - 1];
				const focused = document.activeElement;

				if ( e.shiftKey ) {
					// Shift + Tab: if focus on first element — jump to last
					if ( focused === first || ! modal.contains( focused ) ) {
						e.preventDefault();
						last.focus();
					}
				} else {
					// Tab: if focus on last element — jump to first
					if ( focused === last || ! modal.contains( focused ) ) {
						e.preventDefault();
						first.focus();
					}
				}
			} );

		// Open/Close mobile menu
		function jacqueline_mobile_menu_open( $bt ) {
			var $menu = cont.find( '.menu_mobile' );
			$menu
				.addClass( 'opened' )
				.data( 'menu-button', $bt )
				.prev( '.menu_mobile_overlay' ).fadeIn();
			$body.addClass( 'menu_mobile_opened' );
			setTimeout( function() {
				$menu.find( 'a' ).eq(0).focus();
			}, 100 );
			$document
				.trigger( 'action.stop_wheel_handlers' )
				.trigger( 'action.mobile_menu_open', [$menu] );
		}
		function jacqueline_mobile_menu_close() {
			var $menu = cont.find( '.menu_mobile' );
			$document.trigger( 'action.mobile_menu_close', [$menu] );
			setTimeout( function() {
				$menu
					.removeClass( 'opened' )
					.prev( '.menu_mobile_overlay' ).fadeOut();
				$body.removeClass( 'menu_mobile_opened' );
				if ( $menu.data( 'menu-button' ) ) {
					$menu.data( 'menu-button' ).eq(0).focus();
				}
				$document.trigger( 'action.start_wheel_handlers' );
			}, jacqueline_apply_filters( 'jacqueline_filter_mobile_menu_close_timeout', 0, $menu ) );
		}

		cont.find( '.sc_layouts_menu_mobile_button > a:not(.inited_click),.menu_mobile_button:not(.inited_click),.menu_mobile_description:not(.inited_click)' )
			.addClass( 'inited_click' )
			.on( 'click', function( e ) {
				var $self = jQuery( this );
				if ( $self.parent().hasClass( 'sc_layouts_menu_mobile_button_burger' )
					&& $self.next().hasClass( 'sc_layouts_menu_popup' )
				) {
					return;   // Not use 'return false' - it break event bubble and popup is not open
				}
				jacqueline_mobile_menu_open( $self );
				e.preventDefault();
				return false;
			}
		);

		cont.find( '.menu_mobile_overlay:not(.inited_click)' )
			.addClass( 'inited_click' )
			.on( 'click', function(e){
				jacqueline_mobile_menu_close();
				e.preventDefault();
				return false;
			} );
		cont.find( '.menu_mobile_close:not(.inited_click)' )
			.addClass( 'inited_click' )
			.on( 'click', function(e){
				jacqueline_mobile_menu_close();
				e.preventDefault();
				return false;
			} )
			.on( 'keyup', function(e) {
				if (e.keyCode == 13) {
					if (jQuery( '.menu_mobile.opened' ).length == 1) {
						jacqueline_mobile_menu_close();
						e.preventDefault();
						return false;
					}
				}
			} )
			.on( 'focus', function() {
				if ( ! $body.hasClass( 'menu_mobile_opened' ) ) {
					jQuery( '#content_skip_link_anchor' ).focus();
				}
			} );

		// Open/Close mobile submenu
		cont.find( '.menu_mobile:not(.inited_click),.sc_layouts_menu_dir_vertical:not([class*="sc_layouts_submenu_"]):not(.inited_click),.sc_layouts_menu.sc_layouts_submenu_dropdown:not(.inited_click)' )
			.addClass( 'inited_click' )
			.on( 'click', 'li a .open_child_menu,li a', function(e) {
				var $self = jQuery( this );
				var $a    = $self.hasClass( 'open_child_menu' ) ? $self.parent() : $self;
				if ($a.parent().hasClass( 'menu-item-has-children' )) {
					if ($a.attr( 'href' ) == '#' || $self.hasClass( 'open_child_menu' )) {
						if ($a.siblings( 'ul:visible' ).length > 0) {
							$a.siblings( 'ul' ).slideUp().parent().removeClass( 'opened' );
						} else {
							$self.parents( 'li' ).eq(0).siblings( 'li' ).find( 'ul.sub-menu:visible,ul.sc_layouts_submenu:visible' ).slideUp().parent().removeClass( 'opened' );
							$a.siblings( 'ul' ).slideDown(
								function() {
									var $self = jQuery( this );
									// Init layouts
									if ( ! $self.hasClass( 'layouts_inited' ) && $self.parents( '.menu_mobile' ).length > 0) {
										$self.addClass( 'layouts_inited' );
										$document.trigger( 'action.init_hidden_elements', [$self] );
									}
								}
							).parent().addClass( 'opened' );
						}
					}
				}
				if ( ! $self.hasClass( 'open_child_menu' ) && $self.parents( '.menu_mobile' ).length > 0 && jacqueline_is_local_link( $a.attr( 'href' ) )) {
					jQuery( '.menu_mobile_close' ).trigger( 'click' );
				}
				if ( $self.hasClass( 'open_child_menu' ) || $a.attr( 'href' ) == '#' ) {
					e.preventDefault();
					return false;
				}
			} )
			.on( 'keyup', 'li a', function(e) {
				if ( e.keyCode == 9 ) {
					jQuery(this).find( '.open_child_menu' ).trigger( 'click' );
				}
			} );

		if ( ! JACQUELINE_STORAGE['trx_addons_exist'] || jQuery( '.top_panel.top_panel_default .sc_layouts_menu_default' ).length > 0) {
			// Init superfish menus
			jacqueline_init_sfmenu( '.sc_layouts_menu:not(.inited):not(.sc_layouts_submenu_dropdown) > ul:not(.inited)' );
			// Show menu
			jQuery( '.sc_layouts_menu:not(.inited)' ).each(
				function() {
					var $self = jQuery( this );
					if ( $self.find( '>ul.inited' ).length == 1 ) {
						$self.addClass( 'inited' );
					}
				}
			);
			// Generate 'scroll' event after the menu is showed
			$window.trigger( 'scroll' );
		}
	}


	// Init Superfish menu
	function jacqueline_init_sfmenu(selector) {
		jQuery( selector ).show().each(
			function() {
				var $self = jQuery( this );
				// Do not init the mobile menu - only add class 'inited'
				if ($self.addClass( 'inited' ).parents( '.menu_mobile' ).length > 0) {
					return;
				}
				var animation_in = $self.parent().data( 'animation_in' );
				if (animation_in == undefined) {
					animation_in = "none";
				}
				var animation_out = $self.parent().data( 'animation_out' );
				if (animation_out == undefined) {
					animation_out = "none";
				}
				$self.superfish(
					{
						delay: 300,
						animation: {
							opacity: 'show'
						},
						animationOut: {
							opacity: 'hide'
						},
						speed: 		animation_in != 'none' ? 500 : 200,
						speedOut:	animation_out != 'none' ? 300 : 200,
						autoArrows: false,
						dropShadows: false,
						onBeforeShow: function(ul) {
							var $self = jQuery( this ),
								$ul   = $self.parents( "ul" );
							var par_offset = 0,
								par_width = 0,
								ul_width = 0,
								ul_height = 0;
							// Detect horizontal position (left | right)
							if ( $ul.length > 1 ) {
								var w      = $page_wrap.width();
								par_offset = $ul.eq(0).offset().left;
								par_width  = $ul.eq(0).outerWidth();
								ul_width   = $self.outerWidth();
								if (par_offset + par_width + ul_width > w - 20 && par_offset - ul_width > 0) {
									$self.addClass( 'submenu_left' );
								} else {
									$self.removeClass( 'submenu_left' );
								}
							}
							// Shift vertical if menu going out the window
							if ( $self.parents( '.top_panel' ).length > 0 ) {
								ul_height      = $self.outerHeight();
								par_offset     = 0;
								var w_height   = jacqueline_window_height(),
									row        = $self.parents( '.sc_layouts_row' ).eq(0),
									row_offset = 0,
									row_height = 0,
									par        = $self.parent();
								while (row.length > 0) {
									row_offset += row.outerHeight();
									if (row.hasClass( 'sc_layouts_row_fixed_on' )) {
										break;
									}
									row = row.prev();
								}
								while (par.length > 0) {
									par_offset += par.position().top + par.parent().position().top;
									row_height  = par.outerHeight();
									if (par.position().top === 0) {
										break;
									}
									par = par.parents( 'li' ).eq(0);
								}
								if (row_offset + par_offset + ul_height > w_height) {
									if (par_offset > ul_height) {
										$self.css( {
											'top': 'auto',
											'bottom': '-1.4em'
										} );
									} else {
										$self.css( {
											'top': '-' + (par_offset - row_height - 2) + 'px',
											'bottom': 'auto'
										} );
									}
								}
							}
							// Animation in
							if (animation_in != 'none') {
								$self.removeClass( 'animated faster ' + animation_out );
								$self.addClass( 'animated fast ' + animation_in );
							}
						},
						onBeforeHide: function(ul) {
							if (animation_out != 'none') {
								var $self = jQuery( this );
								$self.removeClass( 'animated fast ' + animation_in );
								$self.addClass( 'animated faster ' + animation_out );
							}
						},
						onShow: function(ul) {
							var $self = jQuery( this );
							// Init layouts
							if ( ! $self.hasClass( 'layouts_inited' )) {
								$self.addClass( 'layouts_inited' );
								$document.trigger( 'action.init_hidden_elements', [$self] );
							}
						}
					}
				);
			}
		);
	}  // jacqueline_init_sfmenu


	// Add TOC in the side menu
	// Make this function global because it used in the elementor.js
	function jacqueline_add_toc_to_sidemenu() {
		if ( jQuery( '.menu_side_inner' ).length > 0 && jQuery( '#toc_menu' ).length > 0 ) {
			jQuery( '#toc_menu' ).appendTo( '.menu_side_inner' );
			jacqueline_stretch_sidemenu();
		}
	}


	// Sidebar open/close
	function jacqueline_init_sidebar_control( cont ) {
		cont.find( '.sidebar_control:not(.inited)' )
			.addClass( 'inited' )
			.on( 'click', function( e ) {
				var $self = jQuery( this ),
					$parent = $self.parent();
				$parent.toggleClass( 'opened' );
				if ( $body.hasClass( 'sidebar_small_screen_above' ) ) {
					var $next = $self.next();
					$next.slideToggle();
					if ( $parent.hasClass( 'opened' ) ) {
						setTimeout( function() {
							$document.trigger( 'action.init_hidden_elements', [$next] );
						}, 310 );
					}
				}
				e.preventDefault();
				return false;
			} );
	}


	// Masonry posts
	function jacqueline_init_masonry( cont ) {
		cont.find( jacqueline_apply_filters( 'jacqueline_filter_masonry_wrap', '.masonry_wrap' ) ).each( function() {
			var masonry_wrap = jQuery( this );
			if ( masonry_wrap.parents( 'div:hidden,article:hidden' ).length > 0) return;
			if ( ! masonry_wrap.hasClass( 'inited' ) ) {
				masonry_wrap.addClass( 'inited' );
				jacqueline_when_images_loaded( masonry_wrap, function() {
					setTimeout( function() {
									masonry_wrap.masonry( {
										itemSelector: jacqueline_apply_filters( 'jacqueline_filter_masonry_item', '.masonry_item' ),
										columnWidth: jacqueline_apply_filters( 'jacqueline_filter_masonry_item', '.masonry_item' ),
										percentPosition: true
									} );
									$window.trigger('resize');
									$window.trigger('scroll');
								}, jacqueline_apply_filters( 'jacqueline_filter_masonry_init', 10 ) );
				});
			} else {
				setTimeout( function() {
					masonry_wrap.masonry();   // Relayout after activate tab
				}, jacqueline_apply_filters( 'jacqueline_filter_masonry_reinit', 510 ) );
			}
		} );
	}

	// Show/Hide Comments
	function jacqueline_init_comments_button( cont ) {
		cont.find( '.show_comments_button:not(.inited)' )
			.addClass( 'inited' )
			.on( 'click', function(e) {
				var bt = jQuery(this);
				if ( bt.attr( 'href' ) == '#' ) {
					bt.toggleClass( 'opened' ).text( bt.data( bt.hasClass( 'opened' ) ? 'hide' : 'show' ) );
					var $comments_wrap = bt.parent().next();	//jQuery( '.comments_wrap' )
					$comments_wrap.slideToggle( function() {
						$comments_wrap.toggleClass( 'opened' );
						$window.trigger( 'scroll' );
					});
					e.preventDefault();
					return false;
				}
			} );
	}


	// Show comments block when link '#comments' or '#respond' is clicked
	function jacqueline_init_comments_links( cont ) {
		cont.find( 'a[href$="#comments"]:not(.inited),a[href$="#respond"]:not(.inited)' )
			.addClass( 'inited' )
			.on( 'click', function(e) {
				var $self = jQuery(this);
				if ( jacqueline_is_local_link( $self.attr( 'href') ) ) {
					var $prev_post_content = $self.parents( '.previous_post_content' ),
						$comments_wrap = $prev_post_content.length
											? $prev_post_content.find( '.comments_wrap' ).eq(0)
											: jQuery( '.comments_wrap' ).eq(0),
						$show_comments_button = $comments_wrap.prev().find('.show_comments_button ');
					if ( $comments_wrap.length ) {
						if ( $comments_wrap.css( 'display' ) == 'none' ) {
							if ($show_comments_button.length ) {
								$show_comments_button.trigger( 'click' );
							}
						}
						if ($show_comments_button.length ) {
							jacqueline_document_animate_to( $show_comments_button.offset().top );
						}
					}
				}
			} );
	}


	// Checkbox with "I agree..."
	function jacqueline_init_checkbox_i_agree( cont ) {
		var $i_agree = cont.find('input[type="checkbox"][name="i_agree_privacy_policy"]:not(.inited)'
							+ ',input[type="checkbox"][name="gdpr_terms"]:not(.inited)'
							+ ',input[type="checkbox"][name="wpgdprc"]:not(.inited)'
							+ ',input[type="checkbox"][name="AGREE_TO_TERMS"]:not(.inited)'
							+ ',input[type="checkbox"][name="acceptance"]:not(.inited)'
							);
		if ( $i_agree.length > 0 ) {
			if ( true ) {	// New way: On click Submit button - checkbox is checked
				$i_agree.each( function() {
					var chk = jQuery( this ),
						form = chk.parents('form');
					chk.addClass( 'inited' );
					form.find( 'button,input[type="submit"]' ).on( 'click', function(e) {
						if ( ! chk.get(0).checked ) {
							form.find('.trx_addons_message_box').remove();
							form.append(
								'<div class="trx_addons_message_box trx_addons_message_box_error">'
									+ JACQUELINE_STORAGE['msg_i_agree_error']
								+ '</div>'
							);
							var error_msg = form.find('.trx_addons_message_box');
							error_msg.fadeIn();
							setTimeout( function() {
								error_msg.fadeOut( function() {
									error_msg.remove();
								} );
							}, 3000 );
							e.preventDefault();
							return false;
						}
					} );
				} );
			} else {		// Old way: Disable Submit button while checkbox is not checked
				$i_agree
					.addClass('inited')
					.on('change', function(e) {
						var $self = jQuery(this),
							$bt   = $self.parents('form').find('button,input[type="submit"]');
						if ($self.get(0).checked) {
							$bt.removeAttr('disabled');
						} else {
							$bt.attr('disabled', 'disabled');
						}
					})
					.trigger('change');
			}
		}
	}


	// Init intersection observer
	//==============================================
	function jacqueline_intersection_observer_init() {

		if ( typeof IntersectionObserver != 'undefined' ) {
			// Create observer
			if ( typeof JACQUELINE_STORAGE['intersection_observer'] == 'undefined' ) {
				JACQUELINE_STORAGE['intersection_observer'] = new IntersectionObserver( function(entries) {
						entries.forEach( function( entry ) {
							jacqueline_intersection_observer_in_out( jQuery(entry.target), entry.isIntersecting || entry.intersectionRatio > 0 ? 'in' : 'out', entry );
						} );
					},
					{
						root: null,			// avoiding 'root' or setting it to 'null' sets it to default value: viewport
						rootMargin: '1px',	// increase (if positive) or decrease (if negative) root area
											// 1px is used instead 0px to avoid a problem with appear sticky elements when window is scrolled down
						threshold: 0		// 0.0 - 1.0: 0.0 - fired when top of the object enter in the viewport
											//            0.5 - fired when half of the object enter in the viewport
											//            1.0 - fired when the whole object enter in the viewport
					}
				);
			}
		} else {
			// Emulate IntersectionObserver behaviour
			$window.on( 'scroll', function() {
				if ( typeof JACQUELINE_STORAGE['intersection_observer_items'] != 'undefined' ) {
					for ( var i in JACQUELINE_STORAGE['intersection_observer_items'] ) {
						if ( ! JACQUELINE_STORAGE['intersection_observer_items'][i] || JACQUELINE_STORAGE['intersection_observer_items'][i].length === 0 ) {
							continue;
						}
						var item = JACQUELINE_STORAGE['intersection_observer_items'][i],
							item_top = item.offset().top,
							item_height = item.height();
						jacqueline_intersection_observer_in_out( item, item_top + item_height > jacqueline_window_scroll_top() && item_top < jacqueline_window_scroll_top() + jacqueline_window_height() ? 'in' : 'out' );
					}
				}
			} );
		}

		// Change state of the entry
		window.jacqueline_intersection_observer_in_out = function( item, state, entry ) {
			var callback = null;
			if ( state == 'in' ) {
				if ( ! item.hasClass( 'jacqueline_in_viewport' ) ) {
					item.addClass( 'jacqueline_in_viewport' );
					callback = item.data('trx-addons-intersection-callback');
					if ( callback ) {
						callback( item, true, entry );
					}
				}
			} else {
				if ( item.hasClass( 'jacqueline_in_viewport' ) ) {
					item.removeClass( 'jacqueline_in_viewport' );
					callback = item.data('trx-addons-intersection-callback');
					if ( callback ) {
						callback( item, false, entry );
					}
				}
			}
		};

		// Add elements to the observer
		window.jacqueline_intersection_observer_add = function( items, callback ) {
			items.each( function() {
				var $self = jQuery( this ),
					id = $self.attr( 'id' );
				if ( ! $self.hasClass( 'jacqueline_intersection_inited' ) ) {
					if ( ! id ) {
						id = 'io-' + ( '' + Math.random() ).replace('.', '');
						$self.attr( 'id', id );
					}
					$self.addClass( 'jacqueline_intersection_inited' );
					if ( callback ) {
						$self.data( 'trx-addons-intersection-callback', callback );
					}
					if ( typeof JACQUELINE_STORAGE['intersection_observer_items'] == 'undefined' ) {
						JACQUELINE_STORAGE['intersection_observer_items'] = {};
					}
					JACQUELINE_STORAGE['intersection_observer_items'][id] = $self;
					if ( typeof JACQUELINE_STORAGE['intersection_observer'] !== 'undefined' ) {
						JACQUELINE_STORAGE['intersection_observer'].observe( $self.get(0) );
					}
				}
			} );
		};

		// Remove elements from the observer
		window.jacqueline_intersection_observer_remove = function( items ) {
			items.each( function() {
				var $self = jQuery( this ),
					id = $self.attr( 'id' );
				if ( $self.hasClass( 'jacqueline_intersection_inited' ) ) {
					$self.removeClass( 'jacqueline_intersection_inited' );
					delete JACQUELINE_STORAGE['intersection_observer_items'][id];
					if ( typeof JACQUELINE_STORAGE['intersection_observer'] !== 'undefined' ) {
						JACQUELINE_STORAGE['intersection_observer'].unobserve( $self.get(0) );
					}
				}
			} );
		};
	}



	// Scroll actions
	//==============================================

	// Do actions when page scrolled
	function jacqueline_scroll_actions() {

		// Call theme/plugins specific action (if exists)
		$document.trigger( 'action.scroll_jacqueline' );

		// Fix/unfix sidebar
		jacqueline_fix_sidebar();

		// Fix/unfix nav links
		jacqueline_fix_nav_links();

		// Fix/unfix share links
		jacqueline_fix_share_links();

		// Shift top and footer panels when header position is 'Under content'
		jacqueline_shift_under_panels();

		// Show full post reading progress
		jacqueline_full_post_reading();

		// Set flag about scroll actions are finished
		JACQUELINE_STORAGE['scroll_busy'] = false;
	}

	// Add post_id to the readed list
	function jacqueline_add_to_read_list(post_id) {
		if ( post_id > 0 ) {
			var read_list = jacqueline_get_storage('jacqueline_post_read');
			if ( read_list && read_list.charAt(0) == '[' ) {
				read_list = JSON.parse(read_list);
			} else {
				read_list = [];
			}
			if ( read_list.indexOf(post_id) == -1 ) {
				read_list.push(post_id);
			}
			jacqueline_set_storage('jacqueline_post_read', JSON.stringify(read_list));
		}
	}

	var fpr_bt, fpr_cont, fpr_cs, fpr_ch, fpr_pw;
	function jacqueline_full_post_read_change_state() {
		fpr_bt = jQuery('.full_post_close');
		if ( fpr_bt.length == 1 ) {
			fpr_cont = fpr_bt.parent();
			fpr_cs   = fpr_cont.offset().top;
			fpr_ch   = fpr_cont.height();
			fpr_pw   = fpr_bt.find('.full_post_progress');
		}
	}

	// Show full post reading progress
	function jacqueline_full_post_reading() {
		if ( typeof fpr_bt == 'undefined' ) {
			jacqueline_full_post_read_change_state();
		}
		if ( fpr_bt.length == 1 ) {
			var ws = jacqueline_window_scroll_top(),
				wh = jacqueline_window_height();
			if ( ws > fpr_cs ) {
				if ( ! fpr_pw || fpr_pw.length === 0 ) {
					fpr_bt.append(
						'<span class="full_post_progress">'
							+ '<svg viewBox="0 0 50 50">'
								+ '<circle class="full_post_progress_bar" cx="25" cy="25" r="22"></circle>'
							+ '</svg>'
						+ '</span>'
					);
					fpr_pw = fpr_bt.find('.full_post_progress');
				}
				var bar = fpr_pw.find('.full_post_progress_bar'),
					bar_max = parseFloat( bar.css('stroke-dasharray') );
				if ( fpr_cs + fpr_ch > ws + wh ) {
					var now = fpr_cs + fpr_ch - ( ws + wh ),
						delta = bar.data('delta');
					if ( delta == undefined ) {
						delta = now;
						bar.data('delta', delta);
					}
					bar.css( 'stroke-dashoffset', Math.min( 1, now / delta ) * bar_max + 'px' );
					if ( now / delta < 0.5 ) {
						var post = fpr_cont.prev(),
							post_id = post.data('post-id');
						post.addClass('full_post_read');
						jacqueline_add_to_read_list(post_id);
					}
				} else if ( ! fpr_bt.hasClass('full_post_read_complete') ) {
					fpr_bt.addClass('full_post_read_complete');
				} else if ( fpr_cs + fpr_ch + wh / 3 < ws + wh ) {
					fpr_bt.trigger( 'click' );
				}
			} else {
				// Disabled (add false &&) to prevent remove a progress bar when scroll to top
				if ( false && fpr_pw.length !== 0 ) {
					fpr_pw.remove();
					fpr_pw = null;
				}
			}
		}
	}  // jacqueline_full_post_reading

	// Shift top and footer panels when header position is 'Under content'
	function jacqueline_shift_under_panels() {

		if ($body.hasClass( 'header_position_under' ) && ! jacqueline_browser_is_mobile()) {

			// Disable 'under' behavior on small screen
			if ( $body.hasClass( 'mobile_layout' ) ) {	//jacqueline_window_width() < JACQUELINE_STORAGE['mobile_breakpoint_underpanels_off'] ) {
				if ( $header.css( 'position' ) == 'fixed' ) {
					// Header
					$header.css(
						{
							'position': 'relative',
							'left': 'auto',
							'top': 'auto',
							'width': 'auto',
							'transform': 'none',
							'zIndex': 3
						}
					);
					$header.find( '.top_panel_mask' ).hide();
					// Content
					$page_content_wrap.css(
						{
							'marginTop': 0,
							'marginBottom': 0,
							'zIndex': 2
						}
					);
					// Footer
					$footer.css(
						{
							'position': 'relative',
							'left': 'auto',
							'bottom': 'auto',
							'width': 'auto',
							'transform': 'none',
							'zIndex': 1
						}
					);
					$footer.find( '.top_panel_mask' ).hide();
				}
				return;
			}

			// Header
			var delta           = 50;
			var scroll_offset   = jacqueline_window_scroll_top();
			var header_height   = _header_height;
			var shift           = ! (/Chrome/.test( navigator.userAgent ) && /Google Inc/.test( navigator.vendor ))
									|| $header.find( '.slider_engine_revo' ).length === 0
										? 0	//1.2		// Parallax speed (if 0 - disable parallax)
										: 0;
			var mask            = $header.find( '.top_panel_mask' );
			var mask_opacity    = 0;
			var css             = {};
			if (mask.length === 0) {
				$header.append( '<div class="top_panel_mask"></div>' );
				mask = $header.find( '.top_panel_mask' );
			}
			if ( $header.css( 'position' ) !== 'fixed' ) {
				$page_content_wrap.css(
					{
						'zIndex': 5,
						'marginTop': header_height + 'px'
					}
				);
				$header.css(
					{
						'position': 'fixed',
						'left': 0,
						'top': jacqueline_adminbar_height() + 'px',
						'width': '100%',
						'zIndex': 3
					}
				);
			} else {
				$page_content_wrap.css( 'marginTop', header_height + 'px' );
			}
			if (scroll_offset > 0) {
				var offset = scroll_offset;	// - jacqueline_adminbar_height();
				if (offset <= header_height) {
					mask_opacity = Math.max( 0, Math.min( 0.8, (offset - delta) / header_height ) );
					// Don't shift header with Revolution slider in Chrome
					if (shift) {
						$header.css( 'transform', 'translate3d(0px, ' + (-Math.round( offset / shift )) + 'px, 0px)' );
					}
					mask.css(
						{
							'opacity': mask_opacity,
							'display': offset === 0 ? 'none' : 'block'
						}
					);
				} else {
					if (shift) {
						$header.css( 'transform', 'translate3d(0px, ' + (-Math.round( offset / shift )) + 'px, 0px)' );
					}
				}
			} else {
				if (shift) {
					$header.css( 'transform', 'none' );
				}
				if (mask.css( 'display' ) != 'none') {
					mask.css(
						{
							'opacity': 0,
							'display': 'none'
						}
					);
				}
			}

			// Footer
			var footer_height  = Math.min( _footer_height, jacqueline_window_height() );
			var footer_visible = (scroll_offset + jacqueline_window_height()) - ( $header.outerHeight() + $page_content_wrap.outerHeight() );
			if ( $footer.css( 'position' ) !== 'fixed' ) {
				$page_content_wrap.css(
					{
						'marginBottom': footer_height + 'px'
					}
				);
				$footer.css(
					{
						'position': 'fixed',
						'left': 0,
						'bottom': 0,
						'width': '100%',
						'zIndex': 2
					}
				);
			} else {
				$page_content_wrap.css( 'marginBottom', footer_height + 'px' );
			}
			if ( footer_visible > 0 ) {
				if ( $footer.css( 'zIndex' ) == 2 ) {
					$footer.css( 'zIndex', 4 );
				}
				mask = $footer.find( '.top_panel_mask' );
				if (mask.length === 0) {
					$footer.append( '<div class="top_panel_mask"></div>' );
					mask = $footer.find( '.top_panel_mask' );
				}
				if (footer_visible <= footer_height) {
					mask_opacity = Math.max( 0, Math.min( 0.8, (footer_height - footer_visible) / footer_height ) );
					// Don't shift header with Revolution slider in Chrome
					if (shift) {
						$footer.css( 'transform', 'translate3d(0px, ' + Math.round( (footer_height - footer_visible) / shift ) + 'px, 0px)' );
					}
					mask.css(
						{
							'opacity': mask_opacity,
							'display': footer_height - footer_visible <= 0 ? 'none' : 'block'
						}
					);
				} else {
					if (shift) {
						$footer.css( 'transform', 'none' );
					}
					if (mask.css( 'display' ) != 'none') {
						mask.css(
							{
								'opacity': 0,
								'display': 'none'
							}
						);
					}
				}
			} else {
				if ( $footer.css( 'zIndex' ) == 4 ) {
					$footer.css( 'zIndex', 2 );
				}
			}
		}
	}  // jacqueline_shift_under_panels


	// Fix/unfix footer
	function jacqueline_fix_footer() {
		if ( $body.hasClass( 'header_position_under' ) && ! jacqueline_browser_is_mobile() ) {
			if ( $footer.length > 0 ) {
				var ft_height = $footer.outerHeight( false ),
				pc            = $page_content_wrap,
				pc_offset     = pc.offset().top,
				pc_height     = pc.height();
				if ( pc_offset + pc_height + ft_height < jacqueline_window_height() ) {
					if ( $footer.css( 'position' ) != 'absolute' ) {
						$footer.css( {
							'position': 'absolute',
							'left': 0,
							'bottom': 0,
							'width' :'100%'
						} );
					}
				} else {
					if ( $footer.css( 'position' ) != 'relative' ) {
						$footer.css( {
							'position': 'relative',
							'left': 'auto',
							'bottom': 'auto'
						} );
					}
				}
			}
		}
	}  // jacqueline_fix_footer

	// Fix/unfix sidebar
	function jacqueline_fix_sidebar(force) {
		// Fix sidebar only if css sticky behaviour is not used
		if ( $body.hasClass( 'fixed_blocks_sticky' ) ) {
			return;
		}
		$sidebar.each( function() {
			var sb        = jQuery( this );
			var content   = sb.siblings( '.content' );
			var old_style = '';

			if ( content.length == 0 ) {
				return;
			}

			// Unfix when sidebar is under content
			if (content.css( 'float' ) == 'none') {
				old_style = sb.data( 'old_style' );
				if (old_style !== undefined) {
					sb.attr( 'style', old_style ).removeAttr( 'data-old_style' );
				}

			} else {

				var sb_height      = sb.outerHeight();
				var sb_shift       = 30;
				var content_height = content.outerHeight();
				var content_top    = content.offset().top;
				var content_shift  = content.position().top;

				// If sidebar shorter then content and page scrolled below the content's top
				if (sb_height < content_height && jacqueline_window_scroll_top() + jacqueline_fixed_rows_height() > content_top) {

					var sb_init = {
						'position': 'undefined',
						'float': 'none',
						'top': 'auto',
						'bottom': 'auto',
						'marginLeft': '0',
						'marginRight': '0'
					};

					if (typeof JACQUELINE_STORAGE['scroll_offset_last'] == 'undefined') {
						JACQUELINE_STORAGE['sb_top_last']        = content_top;
						JACQUELINE_STORAGE['scroll_offset_last'] = jacqueline_window_scroll_top();
						JACQUELINE_STORAGE['scroll_dir_last']    = 1;
					}
					var scroll_dir = jacqueline_window_scroll_top() - JACQUELINE_STORAGE['scroll_offset_last'];
					if (scroll_dir === 0) {
						scroll_dir = JACQUELINE_STORAGE['scroll_dir_last'];
					} else {
						scroll_dir = scroll_dir > 0 ? 1 : -1;
					}

					var sb_big = sb_height + sb_shift >= jacqueline_window_height() - jacqueline_fixed_rows_height(),
					sb_top     = sb.offset().top;

					if (sb_top < 0) {
						sb_top = JACQUELINE_STORAGE['sb_top_last'];
					}

					// If sidebar height greater then window height
					if (sb_big) {

						// If change scrolling dir
						if (scroll_dir != JACQUELINE_STORAGE['scroll_dir_last'] && sb.css( 'position' ) == 'fixed') {
							sb_init.position = 'absolute';
							sb_init.top      = sb_top + content_shift - content_top;

						// If scrolling down
						} else if (scroll_dir > 0) {
							if (jacqueline_window_scroll_top() + jacqueline_window_height() >= content_top + content_height) {
								sb_init.position = 'absolute';
								sb_init.bottom   = 0;
							} else if (jacqueline_window_scroll_top() + jacqueline_window_height() >= (sb.css( 'position' ) == 'absolute' ? sb_top : content_top) + sb_height + sb_shift) {
								sb_init.position = 'fixed';
								sb_init.bottom   = sb_shift;
							}

						// If scrolling up
						} else {
							if (jacqueline_window_scroll_top() + jacqueline_fixed_rows_height() <= sb_top) {
								sb_init.position = 'fixed';
								sb_init.top      = jacqueline_fixed_rows_height();
							}
						}

					// If sidebar height less then window height
					} else {
						if (jacqueline_window_scroll_top() + jacqueline_fixed_rows_height() >= content_top + content_height - sb_height) {
							sb_init.position = 'absolute';
							sb_init.bottom   = 0;
						} else {
							sb_init.position = 'fixed';
							sb_init.top      = jacqueline_fixed_rows_height();
						}
					}
					
					if (force && sb_init.position == 'undefined' && sb.css('position') == 'absolute') {
						sb_init.position = 'absolute';
						if (sb.css('top') != 'auto') {
							sb_init.top = sb.css('top');
						} else {
							sb_init.bottom = sb.css('bottom');
						}
					}

					// Check bounds
					if ( sb_init.position == 'absolute' || sb_init.position == 'undefined' ) {
						if ( sb_init.top == 'auto' && sb_init.bottom == 'auto' ) {
							sb_init.top = sb.offset().top;
						}
						if ( sb_init.top != 'auto' ) {
							if ( sb_init.top + sb_height > content_height ) {
								sb_init.position = 'absolute';
								sb_init.top = content_shift + content_height - sb_height;
								force = true;
							}
							if ( sb_init.top + sb_height <= content_shift + content_height && sb_init.top >= jacqueline_window_scroll_top() + jacqueline_window_height() ) {
								sb_init.position = 'fixed';
								sb_init.top      = 'auto';
								sb_init.bottom   = sb_shift;
								force = true;
							}
						}
					} else if ( sb_init.position == 'fixed' ) {
						if ( sb_init.top == 'auto' && sb_init.bottom == 'auto' && sb.css('top') != 'auto') {
							sb_init.top = parseFloat( sb.css('top') );
						}
						if ( sb_init.top != 'auto' && jacqueline_window_scroll_top() + sb_init.top + sb_height > content_top + content_height ) {
							sb_init.position = 'absolute';
							sb_init.top = content_shift + content_height - sb_height;
							force = true;
						}
					}

					// Set new position of the sidebar
					if (sb_init.position != 'undefined') {
						// Insert placeholder before sidebar
						var style = sb.attr('style');
						if (!style) style = '';
						if (!sb.prev().hasClass('sidebar_fixed_placeholder')) {
							sb.css(sb_init);
							JACQUELINE_STORAGE['scroll_dir_last'] = 0;
							sb.before('<div class="sidebar_fixed_placeholder '+sb.attr('class')+'"'
									   		+ (sb.data('sb') ? ' data-sb="' + sb.data('sb') + '"' : '')
									   + '></div>');
						}
						// Detect horizontal position
						if ( sb_init.position == 'absolute' && $body.hasClass( 'body_style_fullwide' ) && $body.hasClass( 'sidebar_right' ) ) {
							sb_init.left = sb.parent().innerWidth() - sb.prev().width() - parseFloat( sb.parent().css( 'paddingLeft' ) );
						} else if ( sb_init.position == 'fixed' || $body.hasClass('body_style_fullwide') || $body.hasClass('body_style_fullscreen') ) {
							sb_init.left = sb.prev().offset().left;
						} else {
							sb_init.left = sb.prev().position().left;
						}
						sb_init.right = 'auto';
						sb_init.width = sb.prev().width() + parseFloat(sb.prev().css('paddingLeft')) + parseFloat(sb.prev().css('paddingRight'));
						// Set position
						if (force
							|| sb.css('position') != sb_init.position 
							|| JACQUELINE_STORAGE['scroll_dir_last'] != scroll_dir
							|| sb.width() != sb_init.width) {
							if (sb.data('old_style') === undefined) {
								sb.attr('data-old_style', style);
							}
							sb.css(sb_init);
						}
					}

					JACQUELINE_STORAGE['sb_top_last']        = sb_top;
					JACQUELINE_STORAGE['scroll_offset_last'] = jacqueline_window_scroll_top();
					JACQUELINE_STORAGE['scroll_dir_last']    = scroll_dir;

				} else {

					// Unfix when page scrolling to top
					old_style = sb.data( 'old_style' );
					if (old_style !== undefined) {
						sb.attr( 'style', old_style ).removeAttr( 'data-old_style' );
						if (sb.prev().hasClass('sidebar_fixed_placeholder')) {
							sb.prev().remove();
						}
					}

				}
			}
		} );
	}  // jacqueline_fix_sidebar

	// Fix/unfix .nav_links_fixed
	function jacqueline_fix_nav_links() {
		if ( $single_nav_links_fixed.length > 0 && $single_nav_links_fixed.css( 'position' ) == 'fixed') {
			var window_bottom = jacqueline_window_scroll_top() + jacqueline_window_height(),
				article = jQuery('.post_item_single'),
				article_top = article.length > 0 ? article.offset().top : jacqueline_window_height(),
				article_bottom = article_top + ( article.length > 0 ? article.height() * 2 / 3 : 0 ),
				footer_top = $footer.length > 0 ? $footer.offset().top : 100000;
			if ( article_bottom < window_bottom && footer_top > window_bottom ) {
				if ( ! $single_nav_links_fixed.hasClass('nav-links-visible') ) {
					$single_nav_links_fixed.addClass('nav-links-visible');
				}
			} else {
				if ( $single_nav_links_fixed.hasClass('nav-links-visible') ) {
					$single_nav_links_fixed.removeClass('nav-links-visible');
				}					
			}
		}
	}

	// Fix/unfix .post_info_vertical_fixed
	function jacqueline_fix_share_links() {
		if ( $single_post_info_fixed.length > 0 ) {
			var frh = jacqueline_fixed_rows_height() + 10,
				st = jacqueline_window_scroll_top() + frh;
			$single_post_info_fixed.each( function() {
				var share_links = jQuery(this),
					share_links_top = share_links.offset().top,
					share_links_left = share_links.offset().left,
					share_links_height = share_links.height(),
					share_links_position = share_links.css( 'position' ),
					article = share_links.parents('.post_content'),
					article_top = article.offset().top,
					article_bottom = article_top + article.height();
				if ( share_links_position == 'absolute') {
					if ( st >= article_top && st + share_links_height < article_bottom ) {
						share_links
							.data('abs-pos', {
								'left': share_links.css('left'), 
								'top':  share_links.css('top')})
							.addClass('post_info_vertical_fixed_on')
							.css({
								'top':  frh,
								'left': share_links_left
							});
					}
				} else if ( share_links_position == 'fixed' ) {
					if ( st < article_top ) {
						if ( share_links.hasClass( 'post_info_vertical_fixed_on' ) ) {
							var abs_pos = share_links.data('abs-pos');
							share_links
								.removeClass( 'post_info_vertical_fixed_on' )
								.css({
									'top': abs_pos.top,
									'left': abs_pos.left
								});
						}
					} else if ( st + share_links_height >= article_bottom ) {
						share_links.fadeOut();
					} else if ( share_links.css('display') == 'none' ) {
						share_links.fadeIn();
					}
				}
			});
		}
	}




	// Resize actions
	//==============================================

	// Do actions when page scrolled
	function jacqueline_resize_actions(cont) {

		// Update global values
		_header_height = $header.length === 0 ? 0 : $header.height();
		_footer_height = $footer.length === 0 ? 0 : $footer.height();
		
		// Call handlers
		jacqueline_check_layout();
		jacqueline_fix_sidebar(true);
		jacqueline_fix_footer();
		jacqueline_fix_nav_links();
		jacqueline_stretch_width( cont );
		jacqueline_stretch_bg_video();
		jacqueline_vc_row_fullwidth_to_boxed( cont );
		jacqueline_stretch_sidemenu();
		jacqueline_resize_video( cont );
		jacqueline_shift_under_panels();

		// Call theme/plugins specific action (if exists)
		//----------------------------------------------
		$document.trigger( 'action.resize_jacqueline', [cont] );
	}

	// Stretch sidemenu (if present)
	function jacqueline_stretch_sidemenu() {
		var toc_items = $menu_side_wrap.find( '.toc_menu_item' );
		if (toc_items.length === 0) {
			return;
		}
		var toc_items_height = jacqueline_window_height()
							- jacqueline_adminbar_height()
							- $menu_side_logo.outerHeight()
							- toc_items.length;
		var th               = Math.floor( toc_items_height / toc_items.length );
		var th_add           = toc_items_height - th * toc_items.length;
		if (JACQUELINE_STORAGE['menu_side_stretch'] && toc_items.length >= 5 && th >= 30) {
			toc_items.find( ".toc_menu_description,.toc_menu_icon" ).css(
				{
					'height': th + 'px',
					'lineHeight': th + 'px'
				}
			);
			toc_items.eq( 0 ).find( ".toc_menu_description,.toc_menu_icon" ).css(
				{
					'height': (th + th_add) + 'px',
					'lineHeight': (th + th_add) + 'px'
				}
			);
		}
	}

	// Scroll sidemenu (if present)
	$document.on( 'action.toc_menu_item_active', function() {
		var toc_menu = $menu_side_wrap.find( '#toc_menu' );
		if (toc_menu.length === 0) {
			return;
		}
		var toc_items = toc_menu.find( '.toc_menu_item' );
		if (toc_items.length === 0) {
			return;
		}
		var th           = toc_items.eq( 0 ).height(),
		toc_menu_pos     = parseFloat( toc_menu.css( 'top' ) ),
		toc_items_height = toc_items.length * th,
		menu_side_height = jacqueline_window_height()
							- jacqueline_adminbar_height()
							- $menu_side_logo.outerHeight()
							- $menu_side_logo.next( '.toc_menu_item' ).outerHeight();
		if ( toc_items_height > menu_side_height ) {
			var toc_item_active = $menu_side_wrap.find( '.toc_menu_item_active' ).eq( 0 );
			if ( toc_item_active.length == 1 ) {
				var toc_item_active_pos = (toc_item_active.index() + 1) * th;
				if (toc_menu_pos + toc_item_active_pos > menu_side_height - th) {
					toc_menu.css( 'top', Math.max( -toc_item_active_pos + 3 * th, menu_side_height - toc_items_height ) );
				} else if (toc_menu_pos < 0 && toc_item_active_pos < -toc_menu_pos + 2 * th) {
					toc_menu.css( 'top', Math.min( -toc_item_active_pos + 3 * th, 0 ) );
				}
			}
		} else if ( toc_menu_pos < 0 ) {
			toc_menu.css( 'top', 0 );
		}
	} );

	// Check for mobile layout
	function jacqueline_check_layout() {
		var resize = true;
		if ( $body.hasClass( 'no_layout' ) ) {
			$body.removeClass( 'no_layout' );
			resize = false;
		}
		var w = window.innerWidth;
		if (w == undefined) {
			w = jacqueline_window_width() + ( jacqueline_window_height() < jacqueline_document_height() || jacqueline_window_scroll_top() > 0 ? 16 : 0 );
		}
		if ( w < JACQUELINE_STORAGE['mobile_layout_width'] ) {
			if ( ! $body.hasClass( 'mobile_layout' )) {
				$body.removeClass( 'desktop_layout' ).addClass( 'mobile_layout' );
				$document.trigger( 'action.switch_to_mobile_layout' );
				if (resize) {
					$window.trigger( 'resize' );
				}
			}
		} else {
			if ( ! $body.hasClass( 'desktop_layout' )) {
				$body.removeClass( 'mobile_layout' ).addClass( 'desktop_layout' );
				jQuery( '.menu_mobile' ).removeClass( 'opened' );
				jQuery( '.menu_mobile_overlay' ).hide();
				$document.trigger( 'action.switch_to_desktop_layout' );
				if ( resize ) {
					$window.trigger( 'resize' );
				}
			}
		}
		if (JACQUELINE_STORAGE['mobile_device'] || jacqueline_browser_is_mobile()) {
			$body.addClass( 'mobile_device' );
		}
	}

	// Stretch area to full window width
	function jacqueline_stretch_width(cont) {
		if (cont === undefined) {
			cont = $body;
		}
		$stretch_width.each(
			function() {
				var $el             = jQuery( this );
				var $el_cont        = $el.parents( '.page_wrap' );
				var $el_cont_offset = 0;
				if ($el_cont.length === 0) {
					$el_cont = $window;
				} else {
					$el_cont_offset = $el_cont.offset().left;
				}
				var $el_full        = $el.next( '.trx-stretch-width-original' );
				var el_margin_left  = parseInt( $el.css( 'margin-left' ), 10 );
				var el_margin_right = parseInt( $el.css( 'margin-right' ), 10 );
				var offset          = $el_cont_offset - $el_full.offset().left - el_margin_left;
				var width           = $el_cont.width();
				if ( ! $el.hasClass( 'inited' )) {
					$el.addClass( 'inited invisible' );
					$el.css(
						{
							'position': 'relative',
							'box-sizing': 'border-box'
						}
					);
				}
				$el.css(
					{
						'left': offset,
						'width': $el_cont.width()
					}
				);
				if ( ! $el.hasClass( 'trx-stretch-content' ) ) {
					var padding      = Math.max( 0, -1 * offset );
					var paddingRight = Math.max( 0, width - padding - $el_full.width() + el_margin_left + el_margin_right );
					$el.css( { 'padding-left': padding + 'px', 'padding-right': paddingRight + 'px' } );
				}
				$el.removeClass( 'invisible' );
			}
		);
	}  // jacqueline_stretch_width

	// Resize video frames
	function jacqueline_resize_video(cont) {
		if (cont === undefined) {
			cont = $body;
		}
		// Resize tags 'video'
		cont.find( 'video' ).each( function() {
			var $self = jQuery( this );
			// If item now invisible
			if ( ( ! JACQUELINE_STORAGE['resize_tag_video'] && $self.parents('.mejs-mediaelement').length === 0 )
				|| $self.hasClass( 'trx_addons_resize' )
				|| $self.hasClass( 'trx_addons_noresize' )
				|| $self.parents( 'div:hidden,section:hidden,article:hidden' ).length > 0
			) {
				return;
			}
			var video     = $self.addClass( 'jacqueline_resize' ).eq( 0 );
			var ratio     = (video.data( 'ratio' ) !== undefined ? video.data( 'ratio' ).split( ':' ) : [16,9]);
			ratio         = ratio.length != 2 || ratio[0] === 0 || ratio[1] === 0 ? 16 / 9 : ratio[0] / ratio[1];
			var mejs_cont = video.parents( '.mejs-video' ).eq(0);
			var mfp_cont  = video.parents( '.mfp-content' ).eq(0);
			var w_attr    = video.data( 'width' );
			var h_attr    = video.data( 'height' );
			if ( ! w_attr || ! h_attr) {
				w_attr = video.attr( 'width' );
				h_attr = video.attr( 'height' );
				if ( ! w_attr || ! h_attr) {
					if ( ( ! w_attr || ! h_attr ) && mejs_cont.length > 0 ) {
						w_attr = Math.ceil( mejs_cont.width() );
						h_attr = Math.ceil( mejs_cont.height() );
					}
					if ( ! w_attr || ! h_attr ) {
						return;
					}
				}
				video.data( {'width': w_attr, 'height': h_attr} );
			}
			var percent = ('' + w_attr).slice( -1 ) == '%';
			w_attr      = parseInt( w_attr, 10 );
			h_attr      = parseInt( h_attr, 10 );
			var par     = mejs_cont.length > 0 ? mejs_cont.parents('.post_video,.post_featured').eq(0) : video.parents('.post_video,.post_featured').eq(0);
			if ( ! par.length ) {
				par = mejs_cont.length > 0 ? mejs_cont.parents('div,section,article').eq(0) : video.parents('div,section,article').eq(0);
			}
			var w_real  = Math.ceil( Math.min( percent ? 10000 : w_attr, par.width() ) );
			if ( mfp_cont.length > 0 ) {
				w_real  = Math.max( mfp_cont.width(), w_real );
			}
			var h_real  = Math.ceil( percent ? w_real / ratio : w_real / w_attr * h_attr );
			if ( parseInt( video.attr( 'data-last-width' ), 10 ) == w_real ) {
				return;
			}
			if ( percent ) {
				video.height( h_real );
			} else if ( video.parents( '.wp-video-playlist' ).length > 0 ) {
				if ( mejs_cont.length === 0 ) {
					video.attr( {'width': w_real, 'height': h_real} );
				}
			} else {
				video.attr( {'width': w_real, 'height': h_real} ).css( {'width': w_real + 'px', 'height': h_real + 'px'} );
				// If a height of the video is less than h_real - a max-height is applied, recalculate the width
				var h_video = video.height();
				if ( h_video < h_real ) {
					w_real = Math.ceil( w_real * h_video / h_real );
					h_real = h_video;
					video.attr( {'width': w_real, 'height': h_real}).css({'width': w_real+'px', 'height': h_real+'px'} );
				}
				// Set dimensions for Media Elements player
				if ( mejs_cont.length > 0 ) {
					jacqueline_set_mejs_player_dimensions( video, w_real, h_real );
				}
			}
			video.attr( 'data-last-width', w_real );
		} );

		// Resize tags 'iframe'
		if ( JACQUELINE_STORAGE['resize_tag_iframe'] ) {
			cont.find( '.video_frame iframe,iframe' ).each( function() {
				var $self = jQuery( this );
				// If item now invisible
				if (   $self.hasClass( 'trx_addons_resize' )
					|| $self.hasClass( 'trx_addons_noresize' )
					|| $self.parent().is( 'rs-bgvideo' )
					|| $self.parents( 'rs-slide' ).length > 0
					|| $self.addClass( 'jacqueline_resize' ).parents( 'div:hidden,section:hidden,article:hidden' ).length > 0
					|| document.fullscreenElement
					|| document.mozFullScreenElement
					|| document.webkitFullscreenElement
					|| document.msFullscreenElement
				) {
					return;
				}
				var iframe = $self.eq( 0 );
				if (iframe.length === 0 || iframe.attr( 'src' ) === undefined || iframe.attr( 'src' ).indexOf( 'soundcloud' ) > 0) {
					return;
				}
				var w_attr = iframe.attr( 'width' );
				var h_attr = iframe.attr( 'height' );
				if ( ! w_attr || ! h_attr || w_attr <= jacqueline_apply_filters( 'jacqueline_filter_noresize_iframe_width', 325 ) ) {
					return;
				}
				var ratio  = iframe.data( 'ratio' ) !== undefined
								? iframe.data( 'ratio' ).split( ':' )
								: ( iframe.parent().data( 'ratio' ) !== undefined
									? iframe.parent().data( 'ratio' ).split( ':' )
									: ( iframe.find( '[data-ratio]' ).length > 0
										? iframe.find( '[data-ratio]' ).data( 'ratio' ).split( ':' )
										: [w_attr, h_attr]
										)
									);
				ratio      = ratio.length != 2 || ratio[0] === 0 || ratio[1] === 0 ? 16 / 9 : ratio[0] / ratio[1];
				var percent   = ( '' + w_attr ).slice( -1 ) == '%';
				w_attr        = parseInt( w_attr, 10 );
				h_attr        = parseInt( h_attr, 10 );
				var par       = iframe.parents( '.post_video,.post_featured' ).eq(0);
				if ( ! par.length ) {
					par = iframe.parents( '.mfp-content' ).eq(0);
					if ( ! par.length ) {
						par = iframe.parents( 'div,section,article' ).eq(0);
					}
				}
				var	contains  = iframe.data('contains-in-parent')=='1' || iframe.hasClass('contains-in-parent'),
					nostretch = iframe.data('no-stretch-to-parent')=='1' || iframe.hasClass('no-stretch-to-parent'),
					pw        = Math.ceil( par.width() ),
					ph        = Math.ceil( par.height() ),
					w_real    = nostretch ? Math.min( w_attr, pw ) : pw,
					h_real    = Math.ceil( percent ? w_real / ratio : w_real / w_attr * h_attr );
				if ( contains && par.css( 'position' ) == 'absolute' && h_real > ph) {
					h_real = ph;
					w_real = Math.ceil( percent ? h_real * ratio : h_real * w_attr / h_attr );
				}
				if (parseInt( iframe.attr( 'data-last-width' ), 10 ) == w_real) {
					return;
				}
				iframe.css( {'width': w_real + 'px', 'height': h_real + 'px'} );
				iframe.attr( 'data-last-width', w_real );
			} );
		}
	}  // jacqueline_resize_video

	// Set Media Elements player dimensions
	function jacqueline_set_mejs_player_dimensions(video, w, h) {
		if (mejs) {
			for (var pl in mejs.players) {
				if (mejs.players[pl].media.src == video.attr( 'src' )) {
					if (mejs.players[pl].media.setVideoSize) {
						mejs.players[pl].media.setVideoSize( w, h );
					} else if (mejs.players[pl].media.setSize) {
						mejs.players[pl].media.setSize( w, h );
					}
					mejs.players[pl].setPlayerSize( w, h );
					mejs.players[pl].setControlsSize();
				}
			}
		}
	}

	// Stretch background video
	function jacqueline_stretch_bg_video() {
		var video_wrap = jQuery( 'div#background_video,.tourmaster-background-video' );
		if (video_wrap.length === 0) {
			return;
		}
		var cont = video_wrap.hasClass( 'tourmaster-background-video' ) ? video_wrap.parent() : video_wrap,
		w        = cont.width(),
		h        = cont.height(),
		video    = video_wrap.find( '>iframe,>video' );
		if (w / h < 16 / 9) {
			w = h / 9 * 16;
		} else {
			h = w / 16 * 9;
		}
		video
			.attr( {'width': w, 'height': h} )
			.css( {'width': w, 'height': h} );
	}

	// Recalculate width of the vc_row[data-vc-full-width="true"] when content boxed or menu_side=='left|right'
	$document.on('vc-full-width-row action.before_resize_trx_addons', function(e, container) {
		jacqueline_vc_row_fullwidth_to_boxed( jQuery(container) );
	});
	function jacqueline_vc_row_fullwidth_to_boxed(cont) {
		if ( $body.hasClass( 'body_style_boxed' )
			|| $body.hasClass( 'menu_side_present' )
			|| parseInt($page_wrap.css('paddingLeft'), 10) > 0
		) {
			if (cont === undefined || ! cont.hasClass( '.vc_row' ) || ! cont.data( 'vc-full-width' )) {
				cont = jQuery( '.vc_row[data-vc-full-width="true"]' );
			}
			var rtl                = jQuery( 'html' ).attr( 'dir' ) == 'rtl';
			var page_wrap_pl       = parseInt( $page_wrap.css('paddingLeft'), 10 );
			if ( isNaN( page_wrap_pl ) ) {
				page_wrap_pl = 0;
			}
			var page_wrap_pr       = parseInt( $page_wrap.css('paddingRight'), 10 );
			if ( isNaN( page_wrap_pr ) ) {
				page_wrap_pr = 0;
			}
			var page_wrap_width    = $page_wrap.outerWidth() - page_wrap_pl - page_wrap_pr;
			var content_wrap       = $page_content_wrap.find( '.content_wrap' );
			var content_wrap_width = content_wrap.width();
			var indent             = ( page_wrap_width - content_wrap_width ) / 2;
			cont.each( function() {
				var $self           = jQuery( this );
				var mrg             = parseInt( $self.css( 'marginLeft' ), 10 );
				var stretch_content = $self.attr( 'data-vc-stretch-content' );
				var stretch_row     = $self.attr( 'data-vc-full-width' );
				var in_content      = $self.parents( '.content_wrap' ).length > 0;
				$self.css( {
					'width':         in_content && ! stretch_content && ! stretch_row ? Math.min( page_wrap_width, content_wrap_width ) : page_wrap_width,
					'left':          rtl ? 'auto' : ( in_content ? -indent : 0 ) - mrg,
					'right':         ! rtl ? 'auto' : ( n_content ? -indent : 0 ) - mrg,
					'padding-left':  stretch_content ? 0 : indent + mrg,
					'padding-right': stretch_content ? 0 : indent + mrg
				} );
			} );
		}
	}

	ready_busy = false;

} );
