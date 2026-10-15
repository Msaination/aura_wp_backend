<?php
/**
 * Astra-compatible document footer with no default theme footer.
 * Elementor supplies the site footer when a Theme Builder template is assigned.
 *
 * @package Aura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'astra_content_bottom' ) ) {
	astra_content_bottom();
}
?>
    </div><!-- .ast-container -->
</div><!-- #content -->
<?php
if ( function_exists( 'astra_content_after' ) ) {
	astra_content_after();
}

if ( function_exists( 'astra_footer_before' ) ) {
	astra_footer_before();
}

if ( function_exists( 'elementor_theme_do_location' ) ) {
	elementor_theme_do_location( 'footer' );
}

if ( function_exists( 'astra_footer_after' ) ) {
	astra_footer_after();
}
?>
</div><!-- #page -->
<?php if ( function_exists( 'astra_body_bottom' ) ) { astra_body_bottom(); } ?>
<?php wp_footer(); ?>
</body>
</html>
