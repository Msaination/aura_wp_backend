<?php
/**
 * Blank-by-default page canvas for Elementor content.
 *
 * @package Aura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div id="primary" class="site-main aura-page-content aura-content">
		<?php the_content(); ?>
	</div>
	<?php
endwhile;

get_footer();
