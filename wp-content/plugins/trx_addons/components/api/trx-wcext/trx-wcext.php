<?php
/**
 * Plugin support: ThemeRex Woocommerce Extensions
 *
 * @package ThemeREX Addons
 * @since v2.39.0
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


if ( ! function_exists( 'trx_addons_exists_trx_wcext' ) ) {
	/**
	 * Check if plugin 'ThemeRex Wcext' is installed and activated
	 * 
	 * @return bool  True if plugin is installed and activated
	 */
	function trx_addons_exists_trx_wcext() {
		return class_exists( 'TrxWcext\Plugin' );
	}
}


if ( ! function_exists( 'trx_addons_trx_wcext_elementor_animate_items' ) ) {
	add_filter( 'trx_addons_filter_elementor_animate_items', 'trx_addons_trx_wcext_elementor_animate_items', 10, 1 );
	/** Add Product Grid items and Product Category items to the separate animation list
	 * 
	 * @hooked filter 'trx_addons_filter_elementor_animate_items'
	 * 
	 * @param array $list  List of selectors to animate
	 * 
	 * @return array  List of selectors to animate
	 */
	function trx_addons_trx_wcext_elementor_animate_items( $list ) {
		if ( is_array( $list ) && ! in_array( '.trx-wcext-pf-category .trx-wcext-pf-items-grid .trx-wcext-pf-item', $list ) ) {
			$list[] = '.trx-wcext-pf-category .trx-wcext-pf-items-grid .trx-wcext-pf-item';
			$list[] = '.trx-wcext-swiper-container-wrap > .trx-wcext-swiper-controls-wrap';
			$list[] = '.trx-wcext-swiper-container-wrap > .swiper-pagination';
			$list[] = '.trx-wcext-swiper-container-wrap > .trx-wcext-slider-arrow';
		}
		return $list;
	}
}


if ( ! function_exists( 'trx_addons_trx_wcext_woo_products_render' ) ) {
	add_filter( 'trx_addons_woo_products_skin_before_render', 'trx_addons_trx_wcext_woo_products_render', 10, 3 );
	/** 
	 * Load scripts for swatches from the 'ThemeRex WCEext' plugin if the 'Quick View' option is enabled in the 'Woo Products' widget
	 * 
	 * @hooked filter 'trx_addons_woo_products_skin_before_render'
	 * 
	 * @param object $skin  Skin object
	 * @param string $style  Skin style
	 * @param array $settings  Skin settings
	 */
	function trx_addons_trx_wcext_woo_products_render( $skin, $style, $settings ) {
		$quick_view = is_object( $skin ) ? $skin->get_instance_value( 'quick_view' ) : '';
		if ( $quick_view == 'yes' ) {
			do_action( 'trx_wcext_action/load_scripts', 'swatches' );
		}
	}
}


if ( ! function_exists( 'trx_addons_trx_wcext_modify_theme_required_plugins' ) ) {
	add_action( 'after_setup_theme', 'trx_addons_trx_wcext_modify_theme_required_plugins', 3 );
	/**
	 * Modify a theme-specific data for the 'trx-wcext' plugin - setting 'install' must be set to true if the plugin 'WooCommerce' is installed and activated, * but the plugin 'ThemeRex WCEext' is not installed and activated
	 *
	 * @hooked after_setup_theme, 3
	 */
	function trx_addons_trx_wcext_modify_theme_required_plugins() {
		if ( ! trx_addons_exists_woocommerce()
			|| trx_addons_exists_trx_wcext()
			|| ! apply_filters( 'trx_addons_filter_allow_sc_styles_in_elementor', false, 'trx-wcext-required' )
		) {
			return;
		}
		$list = trx_addons_call_theme_function( 'storage_get', array( 'required_plugins' ) );
		if ( is_array( $list )
			&& isset( $list['woocommerce'] )	// && isset( $list['woocommerce']['install'] ) && $list['woocommerce']['install'] === false
			&& isset( $list['trx-wcext'] ) && isset( $list['trx-wcext']['install'] ) && $list['trx-wcext']['install'] === false
		) {
			$list['trx-wcext']['install'] = true;
			trx_addons_call_theme_function( 'storage_set', array( 'required_plugins', $list ) );
		}
	}
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'trx-wcext/trx-wcext-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_trx_wcext() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'trx-wcext/trx-wcext-demo-ocdi.php';
}
