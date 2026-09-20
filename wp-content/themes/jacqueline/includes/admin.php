<?php
/**
 * Admin utilities
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.1
 */

// Disable direct call
if ( ! defined( 'ABSPATH' ) ) {
	exit; }


//-------------------------------------------------------
//-- Theme init
//-------------------------------------------------------

// Theme init priorities:
// 1 - register filters to add/remove lists items in the Theme Options
// 2 - create Theme Options
// 3 - add/remove Theme Options elements
// 5 - load Theme Options
// 9 - register other filters (for installer, etc.)
//10 - standard Theme init procedures (not ordered)

if ( ! function_exists( 'jacqueline_admin_theme_setup' ) ) {
	add_action( 'after_setup_theme', 'jacqueline_admin_theme_setup' );
	function jacqueline_admin_theme_setup() {
		// Add theme icons
		add_action( 'admin_footer', 'jacqueline_admin_footer' );

		// Enqueue scripts and styles for admin
		add_action( 'admin_enqueue_scripts', 'jacqueline_admin_scripts' );
		add_action( 'admin_footer', 'jacqueline_admin_localize_scripts' );

		// Show admin notice with control panel
		add_action( 'admin_notices', 'jacqueline_admin_notice' );
		add_action( 'wp_ajax_jacqueline_hide_admin_notice', 'jacqueline_callback_hide_admin_notice' );

		// Show admin notice with "Rate Us" panel
		add_action( 'admin_notices', 'jacqueline_rate_notice' );
		add_action( 'wp_ajax_jacqueline_hide_rate_notice', 'jacqueline_callback_hide_rate_notice' );

		// After switch or update theme
		add_action( 'after_switch_theme', 'jacqueline_save_activation_date' );
		add_action( 'after_switch_theme', 'jacqueline_regenerate_merged_files' );
		add_action( 'admin_init', 'jacqueline_check_theme_version' );

		// TGM Activation plugin
		add_action( 'tgmpa_register', 'jacqueline_register_plugins' );

		// Init internal admin messages
		jacqueline_init_admin_messages();
	}
}


//-------------------------------------------------------
//-- After switch theme
//-------------------------------------------------------

if ( ! function_exists( 'jacqueline_save_activation_date' ) ) {
	/**
	 * Save the date with the theme activation
	 * 
	 * @hooked 'after_switch_theme'
	 */
	function jacqueline_save_activation_date() {
		$theme_time = (int) get_option( 'jacqueline_theme_activated' );
		if ( 0 == $theme_time ) {
			$theme_slug      = get_template();
			$stylesheet_slug = get_stylesheet();
			if ( $theme_slug == $stylesheet_slug ) {
				update_option( 'jacqueline_theme_activated', time() );
			}
		}
	}
}

if ( ! function_exists( 'jacqueline_regenerate_merged_files' ) ) {
	/**
	 * Regenerate merged files with styles and scripts after the current theme is switched
	 * 
	 * @hooked 'after_switch_theme'
	 */
	function jacqueline_regenerate_merged_files() {
		// Set a flag to regenerate styles and scripts on first run
		if ( apply_filters( 'jacqueline_filter_regenerate_merged_files_after_switch_theme', true ) ) {
			jacqueline_set_action_save_options();
		}
	}
}

if ( ! function_exists( 'jacqueline_check_theme_version' ) ) {
	/** 
	 * Regenerate merged files with styles and scripts after the current theme is updated
	 * 
	 * @hooked 'admin_init'
	 */
	function jacqueline_check_theme_version() {
		if ( ! wp_doing_ajax() ) {
			$theme_slug  = get_template();
			$theme       = wp_get_theme( $theme_slug );
			$version     = $theme->get( 'Version' );
			$cur_version = get_option( 'jacqueline_theme_version' );
			// If the theme was updated manually
			if ( $cur_version != $version ) {
				// Set a flag to regenerate styles and scripts on first run
				if ( apply_filters( 'jacqueline_filter_regenerate_merged_files_after_update_theme', true ) ) {
					jacqueline_set_action_save_options();
				}
				// Trigger action for a new version
				do_action( 'jacqueline_action_is_new_version_of_theme', $version, $cur_version );
				// Save current version
				update_option( 'jacqueline_theme_version', $version );
			}
		}
	}
}


//-------------------------------------------------------
//-- Welcome notice
//-------------------------------------------------------

if ( ! function_exists( 'jacqueline_admin_notice' ) ) {
	/**
	 * Show the admin notice with a welcome message and buttons to redirect to the Theme Options and Customizer
	 * 
	 * @hooked 'admin_notices'
	 */
	function jacqueline_admin_notice() {
		if ( jacqueline_exists_trx_addons()
			|| in_array( jacqueline_get_value_gp( 'action' ), array( 'vc_load_template_preview' ) )
			|| jacqueline_get_value_gp( 'page' ) == 'jacqueline_about'
			|| ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		if ( get_transient( 'jacqueline_hide_notice_admin' ) ) {
			return;
		}
		get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/admin-notice' ) );
	}
}

if ( ! function_exists( 'jacqueline_callback_hide_admin_notice' ) ) {
	/**
	 * Hide the admin notice for a week
	 * 
	 * @hooked 'wp_ajax_jacqueline_hide_admin_notice'
	 */
	function jacqueline_callback_hide_admin_notice() {
		jacqueline_verify_nonce();
		set_transient( 'jacqueline_hide_notice_admin', true, 7 * 24 * 60 * 60 );	// 7 days
		jacqueline_exit();
	}
}


//-------------------------------------------------------
//-- "Rate Us" notice
//-------------------------------------------------------

if ( ! function_exists( 'jacqueline_rate_notice' ) ) {
	/**
	 * Show "Rate Us" notice
	 * 
	 * @hooked 'admin_notices'
	 */
	function jacqueline_rate_notice() {
		if ( in_array( jacqueline_get_value_gp( 'action' ), array( 'vc_load_template_preview' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		// Display the message only on specified screens
		$allowed = array( 'dashboard', 'theme_options', 'trx_addons_options' );
		$screen  = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( ( is_object( $screen ) && ! empty( $screen->id ) && in_array( $screen->id, $allowed ) ) || in_array( jacqueline_get_value_gp( 'page' ), $allowed ) ) {
			$show  = get_option( 'jacqueline_rate_notice' );
			$start = get_option( 'jacqueline_theme_activated' );
			if ( ( false !== $show && 0 == (int) $show ) || ( $start > 0 && ( time() - $start ) / ( 24 * 3600 ) < 14 ) ) {
				return;
			}
			get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/admin-rate' ) );
		}
	}
}

if ( ! function_exists( 'jacqueline_callback_hide_rate_notice' ) ) {
	/**
	 * Hide the notice "Rate Us" forever
	 * 
	 * @hooked 'wp_ajax_jacqueline_hide_rate_notice'
	 */
	function jacqueline_callback_hide_rate_notice() {
		jacqueline_verify_nonce();
		update_option( 'jacqueline_rate_notice', '0' );
		jacqueline_exit();
	}
}


//-------------------------------------------------------
//-- Internal messages
//-------------------------------------------------------

if ( ! function_exists( 'jacqueline_init_admin_messages' ) ) {
	/**
	 * Init the internal admin messages system
	 */
	function jacqueline_init_admin_messages() {
		$msg = get_transient( 'jacqueline_admin_messages' );
		if ( is_array( $msg ) ) {
			delete_transient( 'jacqueline_admin_messages' );
		} else {
			$msg = array();
		}
		jacqueline_storage_set( 'admin_messages', $msg );
	}
}

if ( ! function_exists( 'jacqueline_add_admin_message' ) ) {
	/**
	 * Add the internal admin message
	 * 
	 * @param string $text  The message text
	 * @param string $type  The message type: 'success', 'info', 'warning', 'error'
	 * @param bool   $cur_session  If true, the message will be added to the current session (not saved in the database)
	 */
	function jacqueline_add_admin_message( $text, $type = 'success', $cur_session = false ) {
		if ( ! empty( $text ) ) {
			$new_msg = array(
				'message' => $text,
				'type'    => $type,
			);
			if ( $cur_session ) {
				jacqueline_storage_push_array( 'admin_messages', '', $new_msg );
			} else {
				$msg = get_transient( 'jacqueline_admin_messages' );
				if ( ! is_array( $msg ) ) {
					$msg = array();
				}
				$msg[] = $new_msg;
				set_transient( 'jacqueline_admin_messages', $msg, 60 * 60 );
			}
		}
	}
}

if ( ! function_exists( 'jacqueline_show_admin_messages' ) ) {
	/**
	 * Show internal admin messages (if any). Show them only on the Theme Options page now.
	 */
	function jacqueline_show_admin_messages() {
		$msg = jacqueline_storage_get( 'admin_messages' );
		if ( ! is_array( $msg ) || count( $msg ) == 0 ) {
			return;
		}
		?>
		<div class="jacqueline_admin_messages">
			<?php
			foreach ( $msg as $m ) {
				?>
				<div class="jacqueline_admin_message_item <?php echo esc_attr( str_replace( 'success', 'updated', $m['type'] ) ); ?>">
					<p><?php echo wp_kses_data( $m['message'] ); ?></p>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}
}


//-------------------------------------------------------
//-- Styles and scripts
//-------------------------------------------------------

if ( ! function_exists( 'jacqueline_admin_footer' ) ) {
	/**
	 * Add the theme icons selector support in the menu items
	 * 
	 * @hooked 'admin_footer'
	 */
	function jacqueline_admin_footer() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( is_object( $screen ) && 'nav-menus' == $screen->id ) {
			jacqueline_show_layout(
				jacqueline_show_custom_field(
					'jacqueline_icons_popup',
					array(
						'type'   => 'icons',
						'style'  => jacqueline_get_theme_setting( 'icons_type' ),
						'button' => false,
						'icons'  => true,
					),
					null
				)
			);
		}
	}
}

if ( ! function_exists( 'jacqueline_admin_scripts' ) ) {
	/**
	 * Load required styles and scripts for admin mode
	 * 
	 * @param bool $all  If true, load styles and scripts for all screens, otherwise - only for the current screen
	 * 
	 * @hooked 'admin_enqueue_scripts'
	 */
	function jacqueline_admin_scripts( $all = false ) {
	
		static $loaded = false;
		if ( $loaded ) {
			return;
		}
		$loaded = true;

		// Add theme admin styles
		wp_enqueue_style( 'jacqueline-admin', jacqueline_get_file_url( 'css/admin.css' ), array(), null );

		// Load RTL styles
		if ( is_rtl() ) {
			wp_enqueue_style( 'jacqueline-admin-rtl', jacqueline_get_file_url( 'css/admin-rtl.css' ), array(), null );
		}

		// Links to selected fonts
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( $all || is_object( $screen ) ) {
			if ( $all || jacqueline_options_allow_override( ! empty( $screen->post_type ) ? $screen->post_type : $screen->id ) ) {
				// Load font icons
				wp_enqueue_style( 'jacqueline-fontello', jacqueline_get_file_url( 'css/font-icons/css/fontello.css' ), array(), null );
				wp_enqueue_style( 'jacqueline-fontello-animation', jacqueline_get_file_url( 'css/font-icons/css/animation.css' ), array(), null );
				// Load theme fonts
				$links = jacqueline_theme_fonts_links();
				if ( count( $links ) > 0 ) {
					foreach ( $links as $slug => $link ) {
						wp_enqueue_style( sprintf( 'jacqueline-font-%s', $slug ), $link, array(), null );
					}
				}
			} elseif ( apply_filters( 'jacqueline_filter_allow_theme_icons', is_customize_preview() || in_array( $screen->id, array( 'nav-menus', 'update-core', 'update-core-network' ) ), ! empty( $screen->post_type ) ? $screen->post_type : $screen->id ) ) {
				// Load font icons
				wp_enqueue_style( 'jacqueline-fontello', jacqueline_get_file_url( 'css/font-icons/css/fontello.css' ), array(), null );
				wp_enqueue_style( 'jacqueline-fontello-animation', jacqueline_get_file_url( 'css/font-icons/css/animation.css' ), array(), null );
			}
		}

		// Add theme scripts
		wp_enqueue_script( 'jacqueline-utils', jacqueline_get_file_url( 'js/utils.js' ), array( 'jquery' ), null, true );
		wp_enqueue_script( 'jacqueline-admin', jacqueline_get_file_url( 'js/admin.js' ), array( 'jquery' ), null, true );
	}
}

if ( ! function_exists( 'jacqueline_admin_localize_scripts' ) ) {
	/**
	 * Localize (add js=variables) the admin scripts
	 * 
	 * @hooked 'admin_footer'
	 */
	function jacqueline_admin_localize_scripts() {
	
		static $loaded = false;
		if ( $loaded ) {
			return;
		}
		$loaded = true;

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		wp_localize_script(
			'jacqueline-admin', 'JACQUELINE_STORAGE', apply_filters(
				'jacqueline_filter_localize_script_admin', array(
					'admin_mode'                 => true,
					'screen_id'                  => is_object( $screen ) ? esc_attr( $screen->id ) : '',
					'user_logged_in'             => true,
					'ajax_url'                   => esc_url( admin_url( 'admin-ajax.php' ) ),
					'ajax_nonce'                 => esc_attr( wp_create_nonce( admin_url( 'admin-ajax.php' ) ) ),
					'msg_ajax_error'             => esc_html__( 'Server response error', 'jacqueline' ),
					'msg_icon_selector'          => esc_html__( 'Select the icon for this menu item', 'jacqueline' ),
					'msg_scheme_reset'           => esc_html__( 'Reset all changes of the current color scheme?', 'jacqueline' ),
					'msg_scheme_copy'            => esc_html__( 'Enter the name for a new color scheme', 'jacqueline' ),
					'msg_scheme_delete'          => esc_html__( 'Do you really want to delete the current color scheme?', 'jacqueline' ),
					'msg_scheme_delete_last'     => esc_html__( 'You cannot delete the last color scheme!', 'jacqueline' ),
					'msg_scheme_delete_internal' => esc_html__( 'You cannot delete the built-in color scheme!', 'jacqueline' ),
					'msg_reset'                  => esc_html__( 'Reset', 'jacqueline' ),
					'msg_reset_confirm'          => esc_html__( 'Are you sure you want to reset all Theme Options?', 'jacqueline' ),
					'msg_export'                 => esc_html__( 'Export', 'jacqueline' ),
					'msg_export_options'         => esc_html__( 'Copy options and save to the text file.', 'jacqueline' ),
					'msg_import'                 => esc_html__( 'Import', 'jacqueline' ),
					'msg_import_options'         => esc_html__( 'Paste previously saved options from the text file.', 'jacqueline' ),
					'msg_import_error'           => esc_html__( 'Error occurs while import options!', 'jacqueline' ),
					'msg_presets'                => esc_html__( 'Options presets', 'jacqueline' ),
					'msg_presets_add'            => esc_html__( 'Specify the name of a new preset:', 'jacqueline' ),
					'msg_presets_apply'          => esc_html__( 'Apply the selected preset?', 'jacqueline' ),
					'msg_presets_delete'         => esc_html__( 'Delete the selected preset?', 'jacqueline' ),
					'msg_exit_not_saved_options' => esc_html__( 'Changes not saved! Are you sure you want to leave this page?', 'jacqueline' ),
				)
			)
		);
	}
}



//-------------------------------------------------------
//-- TinyMCE editor
//-------------------------------------------------------

if ( ! function_exists( 'jacqueline_tinymce_init' ) ) {
	add_filter( 'tiny_mce_before_init', 'jacqueline_skin_tinymce_init', 1000 );
	/**
	 * Add the body class with the current color scheme to the TinyMCE editor
	 * 
	 * @param array $opt  The TinyMCE options
	 * 
	 * @hooked 'tiny_mce_before_init', 1000
	 * 
	 * @return array  The modified TinyMCE options
	 */
	function jacqueline_skin_tinymce_init( $opt ) {
		$opt['body_class'] = ( ! empty( $opt['body_class'] ) ? $opt['body_class'] . ' ' : '' ) . 'scheme_' . esc_attr( jacqueline_get_theme_option( 'color_scheme', 'default' ) );
		return $opt;
	}
}



//-------------------------------------------------------
//-- Third party plugins
//-------------------------------------------------------

if ( ! function_exists( 'jacqueline_register_plugins' ) ) {
	/**
	 * Register the theme-required plugins for the TGM Activation plugin
	 * 
	 * @hooked 'tgmpa_register'
	 * 
	 * @trigger 'jacqueline_filter_tgmpa_required_plugins'
	 */
	function jacqueline_register_plugins() {
		tgmpa(
			apply_filters(
				'jacqueline_filter_tgmpa_required_plugins', array(
				// Plugins to include in the autoinstall queue.
				)
			),
			array(
				'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
				'default_path' => '',                      // Default absolute path to bundled plugins.
				'menu'         => 'tgmpa-install-plugins', // Menu slug.
				'parent_slug'  => 'themes.php',            // Parent menu slug.
				'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
				'has_notices'  => true,                    // Show admin notices or not.
				'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
				'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
				'is_automatic' => false,                   // Automatically activate plugins after installation or not.
				'message'      => '',                      // Message to output right before the plugins table.
			)
		);
	}
}


if ( ! function_exists( 'jacqueline_add_group_and_logo_to_slave' ) ) {
	/**
	 * Copy a group and a logo from the parent plugin to the slave plugin settings.
	 * 
	 * @param array  $list    The list of plugins
	 * @param string $parent  The parent plugin slug
	 * @param string $slave   The slave plugin slug
	 * 
	 * @return array  The modified list of plugins
	 */
	function jacqueline_add_group_and_logo_to_slave( $list, $parent, $slave ) {
		$group = ! empty( $list[ $parent ]['group'] )
					? $list[ $parent ]['group']
					: jacqueline_storage_get_array( 'required_plugins', $parent, 'group' ); 
		if ( ! empty( $group ) ) {
			foreach ( $list as $k => $v ) {
				if ( substr( $k, 0, strlen( $slave ) ) == $slave ) {
					if ( empty( $v['group'] ) ) {
						$list[ $k ]['group'] = $group;
					}
					if ( empty( $v['logo'] ) ) {
						$logo = jacqueline_get_file_url( "plugins/{$parent}/{$k}.png" );
						$list[ $k ]['logo'] = empty( $logo )
												? ( ! empty( $list[ $parent ]['logo'] )
													? ( jacqueline_is_url( $list[ $parent ]['logo'] )
														? $list[ $parent ]['logo']
														: jacqueline_get_file_url( sprintf( 'plugins/%1$s/%2$s', $parent, $list[ $parent ]['logo'] ) )
														)
													: ''
													)
												: $logo;
					}
				}
			}
		}
		return $list;
	}
}


if ( ! function_exists( 'jacqueline_get_plugin_source_path' ) ) {
	/**
	 * Return a path (local or URL) to the plugin source
	 * 
	 * @param string $path  The plugin path relative to the 'plugins' directory in the theme folder
	 * 
	 * @return string  The local path or URL to the plugin source
	 */
	function jacqueline_get_plugin_source_path( $path ) {
		$local = jacqueline_get_file_dir( $path );
		$path  = empty( $local ) && ! jacqueline_get_theme_setting( 'tgmpa_upload' ) ? jacqueline_get_plugin_source_url( $path ) : $local;
		return $path;
	}
}


if ( ! function_exists( 'jacqueline_get_plugin_source_url' ) ) {
	/**
	 * Return URL to the plugin download from the ThemeREX Upgrader server
	 * 
	 * @param string $path  The plugin path relative to the 'plugins' directory in the theme folder
	 * 
	 * @return string  The URL to the plugin source
	 */
	function jacqueline_get_plugin_source_url( $path ) {
		$code = jacqueline_get_theme_activation_code();
		$url  = '';
		if ( ! empty( $code ) || jacqueline_is_theme_activated() || strpos($path, '/trx_addons/') !== false ) {   // Allow to install 'trx_addons' without theme activation
			$url = jacqueline_get_upgrade_url( array(
				'action' => 'install_plugin',
				'key'    => $code,
				'plugin' => str_replace( 'plugins/', '', $path )
			) );
		}
		return jacqueline_add_protocol( $url );
	}
}
