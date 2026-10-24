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
 * Show WordPress admin bar (with WP logo) on LatePoint admin pages.
 *
 * LatePoint hides the WordPress admin bar via its CSS
 * (.latepoint-admin #wpadminbar { display: none; }), and WordPress
 * itself hides the wp-logo node in the admin area. We override both
 * so all backend users — not just administrators — can navigate back
 * to the WordPress dashboard.
 */
function aura_show_admin_bar_on_latepoint() {
	// Only on the LatePoint admin screen.
	if ( ! is_admin() || ! isset( $_GET['page'] ) || $_GET['page'] !== 'latepoint' ) {
		return;
	}

	echo '<style id="aura-latepoint-admin-bar">'
		. '#wpadminbar{display:block!important}'
		. '#wp-admin-bar-top-secondary{display:block!important}'
		. '#wp-admin-bar-wp-logo,#wp-admin-bar-site-name,#wp-admin-bar-comments,#wp-admin-bar-new-content,#wp-admin-bar-my-sites,#wp-admin-bar-search{display:none!important}'
		. 'body.latepoint-admin{margin-top:0!important}'
		. '</style>';
}
add_action( 'admin_head', 'aura_show_admin_bar_on_latepoint', 9999 );
/**
 * Apply 50% deposit with rounding for all LatePoint services.
 *
 * - Defaults payment portion to 'deposit' when not set
 * - Calculates 50% of the charge amount
 * - Rounds up to the nearest multiple of 10 (e.g. R293.75 → R300.00)
 *
 * @param float $charge_amount The charge amount.
 * @param OsCartModel $cart The LatePoint cart object.
 * @return float The deposit amount, rounded up.
 */

/**
 * Set default 50% deposit for LatePoint services that don't have one configured.
 * This makes the deposit amount available in the Edit Service form for all services.
 */
function aura_latepoint_set_default_deposits() {
    // Only run in admin for administrators
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Only run on plugins_loaded (ensures LatePoint is loaded)
    if ( ! did_action( 'plugins_loaded' ) ) {
        return;
    }

    // Only run when LatePoint is the current admin page
    $screen = get_current_screen();
    if ( ! $screen || ( strpos( $screen->id, 'latepoint' ) === false && $screen->id !== 'toplevel_page_latepoint' ) ) {
        return;
    }

    // Check if we've already set defaults
    if ( get_transient( 'aura_latepoint_deposits_set' ) ) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'latepoint_services';

    // Get services without a deposit and calculate 50% of their price
    $services = $wpdb->get_results(
        "SELECT id, price_min, price_max, charge_amount, deposit_amount FROM {$table} "
        . "WHERE (deposit_amount IS NULL OR deposit_amount = 0) AND status = 'active'"
    );

    foreach ( $services as $service ) {
        // Use price_min as the base price, or charge_amount if available
        $base_price = $service->price_min > 0 ? $service->price_min : $service->charge_amount;
        if ( $base_price <= 0 ) {
            $base_price = $service->price_max > 0 ? $service->price_max : 0;
        }
        // Set deposit to 50% of the base price
        $deposit = $base_price * 0.5;
        $wpdb->update(
            $table,
            array(
                'deposit_amount'       => $deposit,
                'is_deposit_required'  => 1,
            ),
            array( 'id' => $service->id ),
            array( '%f', '%d' ),
            array( '%d' )
        );
    }

    // Also clear the transient on plugin/theme update (force refresh on next load)
    set_transient( 'aura_latepoint_deposits_set', true, MINUTE_IN_SECONDS * 60 );
}
add_action( 'admin_init', 'aura_latepoint_set_default_deposits' );


/**
 * Round deposit amounts to nearest multiple of 10.
 */
function aura_latepoint_deposit_rounding( $charge_amount, $cart ) {
    // Only apply to WooCommerce payment processor
    if ( ! isset( $cart->payment_processor ) || $cart->payment_processor !== 'woocommerce' ) {
        return $charge_amount;
    }

    // Only apply to deposit payments (not full or remaining balance)
    $portion = isset( $cart->payment_portion ) ? $cart->payment_portion : '';
    if ( $portion !== 'deposit' ) {
        return $charge_amount;
    }

    // Calculate 50% deposit of the full charge amount
    $deposit = (float) $charge_amount * 0.5;

    // Round UP to nearest multiple of 10 (e.g. R293.75 → R300.00, R250 → R250)
    $rounded = (float) ceil( $deposit / 10 ) * 10;

    return number_format( $rounded, 2, '.', '' );
}
add_filter( 'latepoint_convert_charge_amount_to_requirements', 'aura_latepoint_deposit_rounding', 10, 2 );


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
