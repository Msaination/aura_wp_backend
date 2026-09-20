<?php
/**
 * The template to display Admin notices
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.1
 */

$jacqueline_theme_slug = get_option( 'template' );
$jacqueline_theme_obj  = wp_get_theme( $jacqueline_theme_slug );
?>
<div class="jacqueline_admin_notice jacqueline_welcome_notice notice notice-info is-dismissible" data-notice="admin">
	<?php
	// Theme image
	$jacqueline_theme_img = jacqueline_get_file_url( 'screenshot.jpg' );
	if ( '' != $jacqueline_theme_img ) {
		?>
		<div class="jacqueline_notice_image"><img src="<?php echo esc_url( $jacqueline_theme_img ); ?>" alt="<?php esc_attr_e( 'Theme screenshot', 'jacqueline' ); ?>"></div>
		<?php
	}

	// Title
	?>
	<h3 class="jacqueline_notice_title">
		<?php
		echo esc_html(
			sprintf(
				// Translators: Add theme name and version to the 'Welcome' message
				__( 'Welcome to %1$s v.%2$s', 'jacqueline' ),
				$jacqueline_theme_obj->get( 'Name' ) . ( JACQUELINE_THEME_FREE ? ' ' . __( 'Free', 'jacqueline' ) : '' ),
				$jacqueline_theme_obj->get( 'Version' )
			)
		);
		?>
	</h3>
	<?php

	// Description
	?>
	<div class="jacqueline_notice_text">
		<p class="jacqueline_notice_text_description">
			<?php
			echo str_replace( '. ', '.<br>', wp_kses_data( $jacqueline_theme_obj->description ) );
			?>
		</p>
		<p class="jacqueline_notice_text_info">
			<?php
			echo wp_kses_data( __( 'Attention! Plugin "ThemeREX Addons" is required! Please, install and activate it!', 'jacqueline' ) );
			?>
		</p>
	</div>
	<?php

	// Buttons
	?>
	<div class="jacqueline_notice_buttons">
		<?php
		// Link to the page 'About Theme'
		?>
		<a href="<?php echo esc_url( admin_url() . 'themes.php?page=jacqueline_about' ); ?>" class="button button-primary"><i class="dashicons dashicons-nametag"></i> 
			<?php
			echo esc_html__( 'Install plugin "ThemeREX Addons"', 'jacqueline' );
			?>
		</a>
	</div>
</div>
