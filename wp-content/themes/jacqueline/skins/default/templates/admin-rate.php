<?php
/**
 * The template to display Admin notices
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.1
 */

$jacqueline_theme_slug = get_template();
$jacqueline_theme_obj  = wp_get_theme( $jacqueline_theme_slug );

?>
<div class="jacqueline_admin_notice jacqueline_rate_notice notice notice-info is-dismissible" data-notice="rate">
	<?php
	// Theme image
	$jacqueline_theme_img = jacqueline_get_file_url( 'screenshot.jpg' );
	if ( '' != $jacqueline_theme_img ) {
		?>
		<div class="jacqueline_notice_image"><img src="<?php echo esc_url( $jacqueline_theme_img ); ?>" alt="<?php esc_attr_e( 'Theme screenshot', 'jacqueline' ); ?>"></div>
		<?php
	}

	// Title
	$jacqueline_theme_name = '"' . $jacqueline_theme_obj->get( 'Name' ) . ( JACQUELINE_THEME_FREE ? ' ' . __( 'Free', 'jacqueline' ) : '' ) . '"';
	?>
	<h3 class="jacqueline_notice_title"><a href="<?php echo esc_url( jacqueline_storage_get( 'theme_rate_url' ) ); ?>" target="_blank">
		<?php
		echo esc_html(
			sprintf(
				// Translators: Add theme name to the 'Welcome' message
				__( 'Help Us Grow - Rate %s Today!', 'jacqueline' ),
				$jacqueline_theme_name
			)
		);
		?>
	</a></h3>
	<?php

	// Description
	?>
	<div class="jacqueline_notice_text">
		<p><?php
			// Translators: Add theme name to the 'Welcome' message
			echo wp_kses_data( sprintf( __( "Thank you for choosing the %s theme for your website! We're excited to see how you've customized your site, and we hope you've enjoyed working with our theme.", 'jacqueline' ), $jacqueline_theme_name ) );
		?></p>
		<p><?php
			// Translators: Add theme name to the 'Welcome' message
			echo wp_kses_data( sprintf( __( "Your feedback really matters to us! If you've had a positive experience, we'd love for you to take a moment to rate %s and share your thoughts on the customer service you received.", 'jacqueline' ), $jacqueline_theme_name ) );
		?></p>
	</div>
	<?php

	// Buttons
	?>
	<div class="jacqueline_notice_buttons">
		<?php
		// Link to the theme download page
		?>
		<a href="<?php echo esc_url( jacqueline_storage_get( 'theme_rate_url' ) ); ?>" class="button button-primary" target="_blank"><i class="dashicons dashicons-star-filled"></i> 
			<?php
			// Translators: Add the theme name to the button caption
			echo esc_html( sprintf( __( 'Rate %s Now', 'jacqueline' ), $jacqueline_theme_name ) );
			?>
		</a>
		<?php
		// Link to the theme support
		?>
		<a href="<?php echo esc_url( jacqueline_storage_get( 'theme_support_url' ) ); ?>" class="button" target="_blank"><i class="dashicons dashicons-sos"></i> 
			<?php
			esc_html_e( 'Support', 'jacqueline' );
			?>
		</a>
		<?php
		// Link to the theme documentation
		?>
		<a href="<?php echo esc_url( jacqueline_storage_get( 'theme_doc_url' ) ); ?>" class="button" target="_blank"><i class="dashicons dashicons-book"></i> 
			<?php
			esc_html_e( 'Documentation', 'jacqueline' );
			?>
		</a>
	</div>
</div>
