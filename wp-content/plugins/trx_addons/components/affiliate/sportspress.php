<?php
/**
 * Affiliate links: SportsPress
 *
 * @package ThemeREX Addons
 * @since v2.41.1
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// A referal attribute to the "Go Pro" link
define( 'TRX_ADDONS_AFF_LINKS_SPORTSPRESS_GO_PRO_REF', '47' );

// An array with links to replace all redirections to the plugin's site with affiliate links
define( 'TRX_ADDONS_AFF_LINKS_SPORTSPRESS', array(
	'//www.themeboy.com' => '',
	'//tboy.co/pro' => 'https://www.themeboy.com/sportspress-pro/?redacted=' . TRX_ADDONS_AFF_LINKS_SPORTSPRESS_GO_PRO_REF,
	'//tboy.co/account' => 'https://account.themeboy.com/?redacted=' . TRX_ADDONS_AFF_LINKS_SPORTSPRESS_GO_PRO_REF,
) );

// An array with pages to replace all redirections to the plugin's site with affiliate links
define( 'TRX_ADDONS_AFF_PAGES_SPORTSPRESS', array(
	'admin.php?page=sportspress',
	'plugins.php'
) );

if ( ! function_exists( 'trx_addons_sportspress_change_url_in_js' ) ) {
	// add_filter( 'trx_addons_filter_localize_script', 'trx_addons_sportspress_change_url_in_js' );
	add_filter( 'trx_addons_filter_localize_script_admin', 'trx_addons_sportspress_change_url_in_js' );
	/**
	 * Prepare variables to change links to our affiliate link in JavaScript
	 * 
	 * @hooked trx_addons_filter_localize_script
	 * @hooked trx_addons_filter_localize_script_admin
	 * 
	 * @param array $vars  List of variables to localize
	 * 
	 * @return array       Modified list of variables to localize
	 */
	function trx_addons_sportspress_change_url_in_js( $vars ) {
		if ( ! isset( $vars['add_to_links_url'] ) ) {
			$vars['add_to_links_url'] = array();
		}
		if ( is_array( TRX_ADDONS_AFF_LINKS_SPORTSPRESS ) ) {
			foreach( TRX_ADDONS_AFF_LINKS_SPORTSPRESS as $mask => $url ) {
				$args = array(
					'slug' => 'sportspress',
					'page' => defined( 'TRX_ADDONS_AFF_PAGES_SPORTSPRESS' ) && is_array( TRX_ADDONS_AFF_PAGES_SPORTSPRESS ) && count( TRX_ADDONS_AFF_PAGES_SPORTSPRESS ) > 0 ? TRX_ADDONS_AFF_PAGES_SPORTSPRESS : false,
					'mask' => $mask,	// if a link href contains this substring - replace it
				);
				if ( empty( $url ) ) {
					$args['args'] = array( 'redacted' => TRX_ADDONS_AFF_LINKS_SPORTSPRESS_GO_PRO_REF );		// url atts to add to the link
				} else {
					$args['link'] = $url;		// new link to replace
				}
				$vars['add_to_links_url'][] = $args;
			}
		}
		return $vars;
	}
}
