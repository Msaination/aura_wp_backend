<?php
/**
 * Astra-compatible document header with no default theme header.
 * Elementor supplies the site header when a Theme Builder template is assigned.
 *
 * @package Aura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><?php if ( function_exists( 'astra_html_before' ) ) { astra_html_before(); } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<?php if ( function_exists( 'astra_head_top' ) ) { astra_head_top(); } ?>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
	<?php if ( function_exists( 'astra_head_bottom' ) ) { astra_head_bottom(); } ?>
</head>
<body <?php body_class(); ?>>
<?php if ( function_exists( 'astra_body_top' ) ) { astra_body_top(); } ?>
<?php wp_body_open(); ?>
<div id="page" class="hfeed site">
<?php
if ( function_exists( 'astra_header_before' ) ) {
	astra_header_before();
}

if ( function_exists( 'elementor_theme_do_location' ) ) {
	elementor_theme_do_location( 'header' );
}

if ( function_exists( 'astra_header_after' ) ) {
	astra_header_after();
}

if ( function_exists( 'astra_content_before' ) ) {
	astra_content_before();
}
?>
<div id="content" class="site-content">
	<div class="ast-container">
		<?php if ( function_exists( 'astra_content_top' ) ) { astra_content_top(); } ?>
