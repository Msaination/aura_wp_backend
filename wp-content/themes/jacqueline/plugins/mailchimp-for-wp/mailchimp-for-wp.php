<?php
/* Mail Chimp support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if ( ! function_exists( 'jacqueline_mailchimp_theme_setup9' ) ) {
	add_action( 'after_setup_theme', 'jacqueline_mailchimp_theme_setup9', 9 );
	function jacqueline_mailchimp_theme_setup9() {
		if ( jacqueline_exists_mailchimp() ) {
			add_action( 'wp_enqueue_scripts', 'jacqueline_mailchimp_frontend_scripts', 1100 );
			add_action( 'trx_addons_action_load_scripts_front_mailchimp', 'jacqueline_mailchimp_frontend_scripts', 10, 1 );
			add_filter( 'jacqueline_filter_merge_styles', 'jacqueline_mailchimp_merge_styles' );
		}
		if ( is_admin() ) {
			add_filter( 'jacqueline_filter_tgmpa_required_plugins', 'jacqueline_mailchimp_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( ! function_exists( 'jacqueline_mailchimp_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('jacqueline_filter_tgmpa_required_plugins',	'jacqueline_mailchimp_tgmpa_required_plugins');
	function jacqueline_mailchimp_tgmpa_required_plugins( $list = array() ) {
		if ( jacqueline_storage_isset( 'required_plugins', 'mailchimp-for-wp' ) && jacqueline_storage_get_array( 'required_plugins', 'mailchimp-for-wp', 'install' ) !== false ) {
			$list[] = array(
				'name'     => jacqueline_storage_get_array( 'required_plugins', 'mailchimp-for-wp', 'title' ),
				'slug'     => 'mailchimp-for-wp',
				'required' => false,
			);
		}
		return $list;
	}
}

// Check if plugin installed and activated
if ( ! function_exists( 'jacqueline_exists_mailchimp' ) ) {
	function jacqueline_exists_mailchimp() {
		return function_exists( '__mc4wp_load_plugin' ) || defined( 'MC4WP_VERSION' );
	}
}



// Custom styles and scripts
//------------------------------------------------------------------------

// Enqueue styles for frontend
if ( ! function_exists( 'jacqueline_mailchimp_frontend_scripts' ) ) {
	//Handler of the add_action( 'wp_enqueue_scripts', 'jacqueline_mailchimp_frontend_scripts', 1100 );
	//Handler of the add_action( 'trx_addons_action_load_scripts_front_mailchimp', 'jacqueline_mailchimp_frontend_scripts', 10, 1 );
	function jacqueline_mailchimp_frontend_scripts( $force = false ) {
		jacqueline_enqueue_optimized( 'mailchimp', $force, array(
			'css' => array(
				'jacqueline-mailchimp-for-wp' => array( 'src' => 'plugins/mailchimp-for-wp/mailchimp-for-wp.css' ),
			)
		) );
	}
}

// Merge custom styles
if ( ! function_exists( 'jacqueline_mailchimp_merge_styles' ) ) {
	//Handler of the add_filter( 'jacqueline_filter_merge_styles', 'jacqueline_mailchimp_merge_styles');
	function jacqueline_mailchimp_merge_styles( $list ) {
		$list[ 'plugins/mailchimp-for-wp/mailchimp-for-wp.css' ] = false;
		return $list;
	}
}


// Add plugin-specific colors and fonts to the custom CSS
if ( jacqueline_exists_mailchimp() ) {
	$jacqueline_fdir = jacqueline_get_file_dir( 'plugins/mailchimp-for-wp/mailchimp-for-wp-style.php' );
	if ( ! empty( $jacqueline_fdir ) ) {
		require_once $jacqueline_fdir;
	}
}

