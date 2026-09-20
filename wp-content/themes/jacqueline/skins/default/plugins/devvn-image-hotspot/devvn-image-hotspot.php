<?php
/* Image Hotspot by DevVN support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if ( ! function_exists( 'jacqueline_devvn_image_hotspot_theme_setup9' ) ) {
	add_action( 'after_setup_theme', 'jacqueline_devvn_image_hotspot_theme_setup9', 9 );
	function jacqueline_devvn_image_hotspot_theme_setup9() {
		if ( is_admin() ) {
			add_filter( 'jacqueline_filter_tgmpa_required_plugins', 'jacqueline_devvn_image_hotspot_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( ! function_exists( 'jacqueline_devvn_image_hotspot_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('jacqueline_filter_tgmpa_required_plugins',	'jacqueline_devvn_image_hotspot_tgmpa_required_plugins');
	function jacqueline_devvn_image_hotspot_tgmpa_required_plugins( $list = array() ) {
		if ( jacqueline_storage_isset( 'required_plugins', 'devvn-image-hotspot' ) && jacqueline_storage_get_array( 'required_plugins', 'devvn-image-hotspot', 'install' ) !== false ) {
			$list[] = array(
				'name'     => jacqueline_storage_get_array( 'required_plugins', 'devvn-image-hotspot', 'title' ),
				'slug'     => 'devvn-image-hotspot',
				'required' => false,
			);
		}
		return $list;
	}
}

// Check if this plugin installed and activated
if ( ! function_exists( 'jacqueline_exists_devvn_image_hotspot' ) ) {
	function jacqueline_exists_devvn_image_hotspot() {
        return defined( 'DEVVN_IHOTSPOT_DEV_MOD' );
	}
}
