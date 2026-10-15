<?php
/**
 * Aura Spa Astra child theme.
 *
 * @package Aura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enable Elementor compatibility while inheriting the rest of Astra.
 */
function aura_theme_setup() {
	add_theme_support( 'elementor' );
	add_theme_support( 'elementor-header-footer' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
}
add_action( 'after_setup_theme', 'aura_theme_setup', 20 );

/**
 * Keep the existing Elementor homepage full width and load Aura styles.
 */
function aura_theme_enqueue_styles() {
	if ( is_front_page() ) {
		wp_dequeue_style( 'astra-theme-css' );
		wp_dequeue_style( 'astra-theme-dynamic' );

		wp_enqueue_style(
			'aura-reference-layout',
			get_stylesheet_directory_uri() . '/aura-reference-layout.css',
			array(),
			'1.1.0'
		);
	}

	if ( is_404() ) {
		wp_enqueue_style(
			'aura-404-fonts',
			'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600&family=Italiana&display=swap',
			array(),
			null
		);
	}

	wp_enqueue_style(
		'aura-child-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'aura_theme_enqueue_styles', 30 );

/**
 * Check whether the current basket request is a gateway-cancellation return.
 */
function aura_is_cancelled_order_return() {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return false;
	}

	$cancel_order = isset( $_GET['cancel_order'] ) && is_string( $_GET['cancel_order'] )
		? sanitize_text_field( wp_unslash( $_GET['cancel_order'] ) )
		: '';

	return 'true' === $cancel_order;
}

/**
 * Show an Aura-branded route home after a cancelled checkout.
 */
function aura_render_cancelled_order_home_button() {
	if ( ! aura_is_cancelled_order_return() ) {
		return;
	}

	echo aura_cancelled_order_home_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Build the branded home link for a cancelled checkout return.
 */
function aura_cancelled_order_home_markup() {
	$logo_url = content_url( '/uploads/2026/10/AuraLogo.png' );

	return sprintf(
		'<div class="aura-cancelled-order-home-wrap"><a class="aura-cancelled-order-home" data-aura-clear-cache href="%1$s"><img src="%2$s" alt="%3$s"><span>%4$s</span></a></div>',
		esc_url( 'https://auraretreat.co.za/' ),
		esc_url( $logo_url ),
		esc_attr__( 'Aura Spa logo', 'aura' ),
		esc_html__( 'Go Home', 'aura' )
	);
}
add_action( 'woocommerce_proceed_to_checkout', 'aura_render_cancelled_order_home_button', 30 );
add_action( 'woocommerce_cart_is_empty', 'aura_render_cancelled_order_home_button', 20 );

/**
 * Add the home link to the visible WooCommerce Cart Block state.
 */
function aura_append_cancelled_order_home_to_cart_block( $block_content, $block ) {
	if ( ! aura_is_cancelled_order_return() ) {
		return $block_content;
	}

	$block_name = isset( $block['blockName'] ) ? $block['blockName'] : '';
	if ( ! in_array( $block_name, array( 'woocommerce/proceed-to-checkout-block', 'woocommerce/empty-cart-block' ), true ) ) {
		return $block_content;
	}

	$cart = function_exists( 'WC' ) ? WC()->cart : null;
	if ( ! $cart ) {
		return $block_content;
	}

	$expected_block = $cart->is_empty()
		? 'woocommerce/empty-cart-block'
		: 'woocommerce/proceed-to-checkout-block';
	if ( $block_name !== $expected_block ) {
		return $block_content;
	}

	return $block_content . aura_cancelled_order_home_markup();
}
add_filter( 'render_block', 'aura_append_cancelled_order_home_to_cart_block', 20, 2 );

/**
 * Clear browser Cache Storage before returning from a cancelled checkout.
 */
function aura_enqueue_cancelled_order_home_script() {
	if ( ! aura_is_cancelled_order_return() ) {
		return;
	}

	wp_enqueue_script(
		'aura-cancelled-order-home',
		get_stylesheet_directory_uri() . '/cancelled-order-home.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aura_enqueue_cancelled_order_home_script', 40 );
