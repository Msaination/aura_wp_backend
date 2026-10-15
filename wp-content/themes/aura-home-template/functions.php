<?php
/**
 * Aura Home Template functions and setup.
 */

if (!defined('ABSPATH')) {
    exit;
}

function aura_home_template_setup() {
    // Register a single primary navigation menu.
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'aura-home-template'),
    ));

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

    // Elementor support — allows the Nav Menu widget to render
    // the primary menu inside an Elementor HTML section.
    add_theme_support('elementor');
    add_theme_support('elementor-header-footer');
}
add_action('after_setup_theme', 'aura_home_template_setup');

/**
 * Fallback menu — used when no Primary Menu is assigned.
 * Mirrors the original hardcoded links so the site is usable
 * before the admin assigns a menu.
 */
function aura_home_template_menu_fallback($args = array()) {
    $default_items = array(
        array('href' => '#',               'label' => 'Home'),
        array('href' => '#gift-vouchers',  'label' => 'Shop'),
        array('href' => '/book',           'label' => 'Book Appointment'),
        array('href' => '#gift-vouchers',  'label' => 'Gift Cards'),
        array('href' => '#about',          'label' => 'About'),
        array('href' => '#contact',        'label' => 'Contact'),
    );

    $items = array_map(
        static function ($item) {
            return sprintf(
                '<li><a href="%s">%s</a></li>',
                esc_url($item['href']),
                esc_html($item['label'])
            );
        },
        $default_items
    );

    $menu_class = isset($args['menu_class']) ? $args['menu_class'] : 'menu';

    echo '<ul class="' . esc_attr($menu_class) . '">' . implode('', $items) . '</ul>';
}

function aura_home_template_enqueue_assets() {
    wp_enqueue_style(
        'aura-home-template-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'aura_home_template_enqueue_assets');
