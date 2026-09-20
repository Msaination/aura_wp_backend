<?php
/**
 * The Header: Logo and main menu
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js<?php
	// Class scheme_xxx need in the <html> as context for the <body>!
	echo ' scheme_' . esc_attr( jacqueline_get_theme_option( 'color_scheme' ) );
?>">

<head>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php
	if ( function_exists( 'wp_body_open' ) ) {
		wp_body_open();
	} else {
		do_action( 'wp_body_open' );
	}

	$jacqueline_full_post_loading = ( jacqueline_is_singular( 'post' ) || jacqueline_is_singular( 'attachment' ) ) && jacqueline_get_value_gp( 'action' ) == 'full_post_loading';
	$jacqueline_prev_post_loading = ( jacqueline_is_singular( 'post' ) || jacqueline_is_singular( 'attachment' ) ) && jacqueline_get_value_gp( 'action' ) == 'prev_post_loading';

	// Don't display the short links while actions 'full_post_loading' and 'prev_post_loading'
	if ( ! $jacqueline_full_post_loading && ! $jacqueline_prev_post_loading ) {
		// Short links to fast access to the content, sidebar and footer from the keyboard
		?><a class="skip-link jacqueline_skip_link skip_to_content_link" href="#content_skip_link_anchor" tabindex="<?php echo esc_attr( apply_filters( 'jacqueline_filter_skip_links_tabindex', 0 ) ); ?>"><?php esc_html_e( "Skip to content", 'jacqueline' ); ?></a><?php
		if ( jacqueline_sidebar_present() ) {
			?><a class="skip-link jacqueline_skip_link skip_to_sidebar_link" href="#sidebar_skip_link_anchor" tabindex="<?php echo esc_attr( apply_filters( 'jacqueline_filter_skip_links_tabindex', 0 ) ); ?>"><?php esc_html_e( "Skip to sidebar", 'jacqueline' ); ?></a><?php
		}
		?><a class="skip-link jacqueline_skip_link skip_to_footer_link" href="#footer_skip_link_anchor" tabindex="<?php echo esc_attr( apply_filters( 'jacqueline_filter_skip_links_tabindex', 0 ) ); ?>"><?php esc_html_e( "Skip to footer", 'jacqueline' ); ?></a><?php
	}

	do_action( 'jacqueline_action_before_body' );
	?>

	<div class="<?php echo esc_attr( apply_filters( 'jacqueline_filter_body_wrap_class', 'body_wrap' ) ); ?>" <?php do_action('jacqueline_action_body_wrap_attributes'); ?>>

		<?php do_action( 'jacqueline_action_before_page_wrap' ); ?>

		<div class="<?php echo esc_attr( apply_filters( 'jacqueline_filter_page_wrap_class', 'page_wrap' ) ); ?>" <?php do_action('jacqueline_action_page_wrap_attributes'); ?>>

			<?php do_action( 'jacqueline_action_page_wrap_start' ); ?>

			<?php

			// Don't display the header elements while actions 'full_post_loading' and 'prev_post_loading'
			if ( ! $jacqueline_full_post_loading && ! $jacqueline_prev_post_loading ) {

				do_action( 'jacqueline_action_before_header' );

				// Header
				$jacqueline_header_type = jacqueline_get_theme_option( 'header_type' );
				if ( 'custom' == $jacqueline_header_type && ! jacqueline_is_layouts_available() ) {
					$jacqueline_header_type = 'default';
				}
				get_template_part( apply_filters( 'jacqueline_filter_get_template_part', "templates/header-" . sanitize_file_name( $jacqueline_header_type ) ) );

				// Side menu
				if ( in_array( jacqueline_get_theme_option( 'menu_side', 'none' ), array( 'left', 'right' ) ) ) {
					get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/header-navi-side' ) );
				}

				// Mobile menu
				if ( apply_filters( 'jacqueline_filter_use_navi_mobile', jacqueline_sc_layouts_showed( 'menu_button' ) || $jacqueline_header_type == 'default' ) ) {
					get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/header-navi-mobile' ) );
				}

				do_action( 'jacqueline_action_after_header' );

			}
			?>

			<?php do_action( 'jacqueline_action_before_page_content_wrap' ); ?>

			<div class="page_content_wrap<?php
				if ( jacqueline_is_off( jacqueline_get_theme_option( 'remove_margins' ) ) ) {
					if ( empty( $jacqueline_header_type ) ) {
						$jacqueline_header_type = jacqueline_get_theme_option( 'header_type' );
					}
					if ( 'custom' == $jacqueline_header_type && jacqueline_is_layouts_available() ) {
						$jacqueline_header_id = jacqueline_get_custom_header_id();
						if ( $jacqueline_header_id > 0 ) {
							$jacqueline_header_meta = jacqueline_get_custom_layout_meta( $jacqueline_header_id );
							if ( ! empty( $jacqueline_header_meta['margin'] ) ) {
								?> page_content_wrap_custom_header_margin<?php
							}
						}
					}
					$jacqueline_footer_type = jacqueline_get_theme_option( 'footer_type' );
					if ( 'custom' == $jacqueline_footer_type && jacqueline_is_layouts_available() ) {
						$jacqueline_footer_id = jacqueline_get_custom_footer_id();
						if ( $jacqueline_footer_id ) {
							$jacqueline_footer_meta = jacqueline_get_custom_layout_meta( $jacqueline_footer_id );
							if ( ! empty( $jacqueline_footer_meta['margin'] ) ) {
								?> page_content_wrap_custom_footer_margin<?php
							}
						}
					}
				}
				do_action( 'jacqueline_action_page_content_wrap_class', $jacqueline_prev_post_loading );
				?>"<?php
				if ( apply_filters( 'jacqueline_filter_is_prev_post_loading', $jacqueline_prev_post_loading ) ) {
					?> data-single-style="<?php echo esc_attr( jacqueline_get_theme_option( 'single_style' ) ); ?>"<?php
				}
				do_action( 'jacqueline_action_page_content_wrap_data', $jacqueline_prev_post_loading );
			?>>
				<?php
				do_action( 'jacqueline_action_page_content_wrap', $jacqueline_full_post_loading || $jacqueline_prev_post_loading );

				// Single posts banner
				if ( apply_filters( 'jacqueline_filter_single_post_header', jacqueline_is_singular( 'post' ) || jacqueline_is_singular( 'attachment' ) ) ) {
					if ( $jacqueline_prev_post_loading ) {
						if ( jacqueline_get_theme_option( 'posts_navigation_scroll_which_block', 'article' ) != 'article' ) {
							do_action( 'jacqueline_action_between_posts' );
						}
					}
					// Single post thumbnail and title
					$jacqueline_path = apply_filters( 'jacqueline_filter_get_template_part', 'templates/single-styles/' . jacqueline_get_theme_option( 'single_style' ) );
					if ( jacqueline_get_file_dir( $jacqueline_path . '.php' ) != '' ) {
						get_template_part( $jacqueline_path );
					}
				}

				// Widgets area above page
				$jacqueline_body_style   = jacqueline_get_theme_option( 'body_style' );
				$jacqueline_widgets_name = jacqueline_get_theme_option( 'widgets_above_page', 'hide' );
				$jacqueline_show_widgets = ! jacqueline_is_off( $jacqueline_widgets_name ) && is_active_sidebar( $jacqueline_widgets_name );
				if ( $jacqueline_show_widgets ) {
					if ( 'fullscreen' != $jacqueline_body_style ) {
						?>
						<div class="content_wrap">
							<?php
					}
					jacqueline_create_widgets_area( 'widgets_above_page' );
					if ( 'fullscreen' != $jacqueline_body_style ) {
						?>
						</div>
						<?php
					}
				}

				// Content area
				do_action( 'jacqueline_action_before_content_wrap' );
				?>
				<div class="content_wrap<?php echo 'fullscreen' == $jacqueline_body_style ? '_fullscreen' : ''; ?>">

					<?php do_action( 'jacqueline_action_content_wrap_start' ); ?>

					<div class="content">
						<?php
						do_action( 'jacqueline_action_page_content_start' );

						// Skip link anchor to fast access to the content from keyboard
						?>
						<span id="content_skip_link_anchor" class="jacqueline_skip_link_anchor"></span>
						<?php
						// Single posts banner between prev/next posts
						if ( ( jacqueline_is_singular( 'post' ) || jacqueline_is_singular( 'attachment' ) )
							&& $jacqueline_prev_post_loading 
							&& jacqueline_get_theme_option( 'posts_navigation_scroll_which_block', 'article' ) == 'article'
						) {
							do_action( 'jacqueline_action_between_posts' );
						}

						// Widgets area above content
						jacqueline_create_widgets_area( 'widgets_above_content' );

						do_action( 'jacqueline_action_page_content_start_text' );
