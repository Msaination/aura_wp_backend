<?php
/**
 * Aura Spa not found page.
 *
 * @package Aura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main aura-not-found">
	<div class="aura-not-found__inner">
		<p class="aura-not-found__eyebrow"><?php esc_html_e( 'Aura Spa', 'aura' ); ?></p>
		<p class="aura-not-found__number" aria-hidden="true">404</p>
		<h1><?php esc_html_e( 'Page not found', 'aura' ); ?></h1>
		<p class="aura-not-found__message">
			<?php esc_html_e( 'We couldn’t find the page you were looking for. Let’s take you back to your sanctuary.', 'aura' ); ?>
		</p>
		<a class="aura-not-found__button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Back to Home', 'aura' ); ?>
		</a>
	</div>
</main>
<?php
get_footer();
