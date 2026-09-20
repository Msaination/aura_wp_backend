<?php
/**
 * The template to display Admin notices
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0.64
 */

$jacqueline_skins_url  = get_admin_url( null, 'admin.php?page=trx_addons_theme_panel#trx_addons_theme_panel_section_skins' );
$jacqueline_skins_args = get_query_var( 'jacqueline_skins_notice_args' );
?>
<div class="jacqueline_admin_notice jacqueline_skins_notice notice notice-info is-dismissible" data-notice="skins">
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
		<?php esc_html_e( 'New skins are available', 'jacqueline' ); ?>
	</h3>
	<?php

	// Description
	$jacqueline_total      = $jacqueline_skins_args['update'];	// Store value to the separate variable to avoid warnings from ThemeCheck plugin!
	$jacqueline_skins_msg  = $jacqueline_total > 0
							// Translators: Add new skins number
							? '<strong>' . sprintf( _n( '%d new version', '%d new versions', $jacqueline_total, 'jacqueline' ), $jacqueline_total ) . '</strong>'
							: '';
	$jacqueline_total      = $jacqueline_skins_args['free'];
	$jacqueline_skins_msg .= $jacqueline_total > 0
							? ( ! empty( $jacqueline_skins_msg ) ? ' ' . esc_html__( 'and', 'jacqueline' ) . ' ' : '' )
								// Translators: Add new skins number
								. '<strong>' . sprintf( _n( '%d free skin', '%d free skins', $jacqueline_total, 'jacqueline' ), $jacqueline_total ) . '</strong>'
							: '';
	$jacqueline_total      = $jacqueline_skins_args['pay'];
	$jacqueline_skins_msg .= $jacqueline_skins_args['pay'] > 0
							? ( ! empty( $jacqueline_skins_msg ) ? ' ' . esc_html__( 'and', 'jacqueline' ) . ' ' : '' )
								// Translators: Add new skins number
								. '<strong>' . sprintf( _n( '%d paid skin', '%d paid skins', $jacqueline_total, 'jacqueline' ), $jacqueline_total ) . '</strong>'
							: '';
	?>
	<div class="jacqueline_notice_text">
		<p>
			<?php
			// Translators: Add new skins info
			echo wp_kses_data( sprintf( __( "We are pleased to announce that %s are available for your theme", 'jacqueline' ), $jacqueline_skins_msg ) );
			?>
		</p>
	</div>
	<?php

	// Buttons
	?>
	<div class="jacqueline_notice_buttons">
		<?php
		// Link to the theme dashboard page
		?>
		<a href="<?php echo esc_url( $jacqueline_skins_url ); ?>" class="button button-primary"><i class="dashicons dashicons-update"></i> 
			<?php
			esc_html_e( 'Go to Skins manager', 'jacqueline' );
			?>
		</a>
		<?php
		// Dismiss notice for 7 days
		?>
		<a href="#" role="button" class="button button-secondary jacqueline_notice_button_dismiss" data-notice="skins"><i class="dashicons dashicons-no-alt"></i> 
			<?php
			esc_html_e( 'Dismiss', 'jacqueline' );
			?>
		</a>
		<?php
		// Hide notice forever
		?>
		<a href="#" role="button" class="button button-secondary jacqueline_notice_button_hide" data-notice="skins"><i class="dashicons dashicons-no-alt"></i> 
			<?php
			esc_html_e( 'Never show again', 'jacqueline' );
			?>
		</a>
	</div>
</div>
