<?php
/**
 * Header template part.
 *
 * Provides wp_head() and the opening markup so Elementor (and
 * wp_nav_menu) can render the single Primary Menu inside an
 * Elementor HTML section.
 */

$theme_assets = get_stylesheet_directory_uri() . '/assets';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('spa-shell'); ?>>
<?php wp_body_open(); ?>
