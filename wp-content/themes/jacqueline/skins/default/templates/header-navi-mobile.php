<?php
/**
 * The template to show mobile menu (used only header_style == 'default')
 *
 * @package JACQUELINE
 * @since JACQUELINE 1.0
 */

$jacqueline_show_widgets = jacqueline_get_theme_option( 'widgets_menu_mobile_fullscreen' );
$jacqueline_show_socials = jacqueline_get_theme_option( 'menu_mobile_socials' );

?>
<div class="menu_mobile_overlay scheme_dark"></div>
<div class="menu_mobile menu_mobile_<?php echo esc_attr( jacqueline_get_theme_option( 'menu_mobile_fullscreen' ) > 0 ? 'fullscreen' : 'narrow' ); ?> scheme_dark">
	<div class="menu_mobile_inner<?php echo esc_attr( $jacqueline_show_widgets == 1  ? ' with_widgets' : '' ); ?>">
        <div class="menu_mobile_header_wrap">
            <?php
            // Logo
            set_query_var( 'jacqueline_logo_args', array( 'type' => 'mobile' ) );
            get_template_part( apply_filters( 'jacqueline_filter_get_template_part', 'templates/header-logo' ) );
            set_query_var( 'jacqueline_logo_args', array() ); ?>

            <a class="menu_mobile_close menu_button_close" tabindex="0"><span class="menu_button_close_text"><?php esc_html_e('Close', 'jacqueline')?></span><span class="menu_button_close_icon"></span></a>
        </div>
        <div class="menu_mobile_content_wrap content_wrap">
            <div class="menu_mobile_content_wrap_inner<?php echo esc_attr($jacqueline_show_socials ? '' : ' without_socials'); ?>"><?php
            // Mobile menu
            $jacqueline_menu_mobile = jacqueline_get_nav_menu( 'menu_mobile' );
            if ( empty( $jacqueline_menu_mobile ) ) {
                $jacqueline_menu_mobile = apply_filters( 'jacqueline_filter_get_mobile_menu', '' );
                if ( empty( $jacqueline_menu_mobile ) ) {
                    $jacqueline_menu_mobile = jacqueline_get_nav_menu( 'menu_main' );
                    if ( empty( $jacqueline_menu_mobile ) ) {
                        $jacqueline_menu_mobile = jacqueline_get_nav_menu();
                    }
                }
            }
            if ( ! empty( $jacqueline_menu_mobile ) ) {
                // Change attribute 'id' - add prefix 'mobile-' to prevent duplicate id on the page
                $jacqueline_menu_mobile = preg_replace( '/([\s]*id=")/', '${1}mobile-', $jacqueline_menu_mobile );
                // Change main menu classes
                $jacqueline_menu_mobile = str_replace(
                array( 'menu_main',   'sc_layouts_menu_nav', 'sc_layouts_menu ' ), // , 'sc_layouts_hide_on_mobile', 'hide_on_mobile'
                array( 'menu_mobile', '', ' ' ), // , '', ''
                    $jacqueline_menu_mobile
                );
                // Wrap menu to the <nav> if not present
                if ( strpos( $jacqueline_menu_mobile, '<nav ' ) !== 0 ) {	// condition !== false is not allowed, because menu can contain inner <nav> elements (in the submenu layouts)
				$jacqueline_menu_mobile = jacqueline_is_on( jacqueline_get_theme_option( 'seo_snippets' ) )
					? sprintf( '<nav class="menu_mobile_nav_area" itemscope="itemscope" itemtype="%1$s//schema.org/SiteNavigationElement">%2$s</nav>', esc_attr( jacqueline_get_protocol( true ) ), $jacqueline_menu_mobile )
					: sprintf( '<nav class="menu_mobile_nav_area">%s</nav>', $jacqueline_menu_mobile );
                }
                // Show menu
                jacqueline_show_layout( apply_filters( 'jacqueline_filter_menu_mobile_layout', $jacqueline_menu_mobile ) );
            }
            // Social icons
            if($jacqueline_show_socials) {
                jacqueline_show_layout( jacqueline_get_socials_links(), '<div class="socials_mobile">', '</div>' );
            }            
            ?>
            </div>
		</div><?php

        if ( $jacqueline_show_widgets == 1 )  {
            ?><div class="menu_mobile_widgets_area"><?php
            // Create Widgets Area
            jacqueline_create_widgets_area( 'widgets_additional_menu_mobile_fullscreen' );
            ?></div><?php
        } ?>

    </div>
</div>
