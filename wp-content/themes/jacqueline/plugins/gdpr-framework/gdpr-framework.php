<?php
/* The GDPR Framework support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if ( ! function_exists( 'jacqueline_gdpr_framework_theme_setup9' ) ) {
	add_action( 'after_setup_theme', 'jacqueline_gdpr_framework_theme_setup9', 9 );
	function jacqueline_gdpr_framework_theme_setup9() {
		if ( is_admin() ) {
			add_filter( 'jacqueline_filter_tgmpa_required_plugins', 'jacqueline_gdpr_framework_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( ! function_exists( 'jacqueline_gdpr_framework_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('jacqueline_filter_tgmpa_required_plugins',	'jacqueline_gdpr_framework_tgmpa_required_plugins');
	function jacqueline_gdpr_framework_tgmpa_required_plugins( $list = array() ) {
		if ( jacqueline_storage_isset( 'required_plugins', 'gdpr-framework' ) && jacqueline_storage_get_array( 'required_plugins', 'gdpr-framework', 'install' ) !== false ) {
			$list[] = array(
				'name'     => jacqueline_storage_get_array( 'required_plugins', 'gdpr-framework', 'title' ),
				'slug'     => 'gdpr-framework',
				'required' => false,
			);
		}
		return $list;
	}
}

// Check if this plugin installed and activated
if ( ! function_exists( 'jacqueline_exists_gdpr_framework' ) ) {
	function jacqueline_exists_gdpr_framework() {
		return defined( 'GDPR_FRAMEWORK_VERSION' );
	}
}
