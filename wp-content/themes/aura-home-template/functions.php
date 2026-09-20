<?php
/**
 * Aura Home Template functions and setup.
 */

if (!defined('ABSPATH')) {
    exit;
}

function aura_home_template_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );
    add_theme_support('custom-logo');
    add_theme_support('responsive-embeds');
}
add_action('after_setup_theme', 'aura_home_template_setup');

function aura_home_template_enqueue_assets() {
    wp_enqueue_style(
        'aura-home-template-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'aura_home_template_enqueue_assets');
