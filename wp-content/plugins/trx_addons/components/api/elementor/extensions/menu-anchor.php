<?php
/**
 * Elementor extension: Add settings with animation type and duration to the Elementor's widget 'Meu Anchor' to allow to scroll to the anchor with animation
 *
 * @package ThemeREX Addons
 * @since v2.43.0
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


if ( ! function_exists( 'trx_addons_elm_add_params_animation_to_menu_anchor' ) ) {
	add_action( 'elementor/element/before_section_end', 'trx_addons_elm_add_params_animation_to_menu_anchor', 10, 3 );
	/**
	 * Add settings with animation type and duration to the Elementor's widget 'Meu Anchor' to allow to scroll to the anchor with animation
	 * 
	 * @hooked elementor/element/before_section_end
	 * 
	 * @param object $element Current element
	 * @param string $section_id Section ID
	 * @param array $args Section arguments
	 */
	function trx_addons_elm_add_params_animation_to_menu_anchor( $element, $section_id, $args ) {
		if ( is_object( $element ) ) {
			$el_name = $element->get_name();
			if ( 'menu-anchor' == $el_name && 'section_anchor' === $section_id ) {
				$element->add_control(
					'anchor_animation_heading',
					[
						'label' => esc_html__( 'Scroll Settings', 'trx_addons' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'after',
					]
				);

				$element->add_control(
					'anchor_animation_description',
					[
						'raw' => __( 'If no offset is specified and animation is not enabled, the anchor behaves as usual by default.', 'trx_addons' ),
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
						'type' => \Elementor\Controls_Manager::RAW_HTML,
					]
				);

				$element->add_responsive_control(
					'anchor_animation_offset',
					[
						'label' => esc_html__( 'Offset after scroll (px)', 'trx_addons' ),
						'description' => esc_html__( 'How far from the top of the window or the bottom of the scrollbar should the anchor stop after scrolling?', 'trx_addons' ),
						'type' => \Elementor\Controls_Manager::NUMBER,
						'default' => '',
						'min' => 0,
						'step' => 50,
					]
				);

				$element->add_control(
					'anchor_animation_offset_from',
					[
						'label' => esc_html__( 'Offset from', 'trx_addons' ),
						'description' => esc_html__( 'What should the scroll offset be based on—the top of the window or the bottom of the sticky rows?', 'trx_addons' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'top' => esc_html__( 'Top of the window', 'trx_addons' ),
							'fixed_rows' => esc_html__( 'Bottom of sticky rows', 'trx_addons' ),
						],
						'default' => 'fixed_rows',
						// Show this control if an offset is specified for any device (breakpoint)
						'conditions' => [
							'relation' => 'or',
							'terms' => trx_addons_elm_get_menu_anchor_offset_conditions(),
						],
					]
				);

				$element->add_control(
					'anchor_animation',
					[
						'label' => esc_html__( 'Scroll Animation', 'trx_addons' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'label_on' => esc_html__( 'Yes', 'trx_addons' ),
						'label_off' => esc_html__( 'No', 'trx_addons' ),
						'return_value' => 'yes',
						'default' => '',
					]
				);

				$element->add_control(
					'anchor_animation_ease',
					[
						'label' => esc_html__( 'Animation Easing', 'trx_addons' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => 'easeOutQuad',
						'options' => trx_addons_get_list_ease( false, 'jquery' ),
						'condition' => [
							'anchor_animation!' => '',
						],
					]
				);

				$element->add_control(
					'anchor_animation_duration',
					[
						'label' => esc_html__( 'Animation Duration (ms)', 'trx_addons' ),
						'type' => \Elementor\Controls_Manager::NUMBER,
						'default' => '',
						'min' => 0,
						'step' => 50,
						'condition' => [
							'anchor_animation!' => '',
						],
					]
				);

				$element->add_control(
					'anchor_animation_delay',
					[
						'label' => esc_html__( 'Animation Delay (ms)', 'trx_addons' ),
						'type' => \Elementor\Controls_Manager::NUMBER,
						'default' => '',
						'min' => 0,
						'step' => 50,
						'condition' => [
							'anchor_animation!' => '',
						],
					]
				);
			}
		}
	}
}

if ( ! function_exists( 'trx_addons_elm_get_menu_anchor_offset_conditions' ) ) {
	/**
	 * Return a list of the conditions to show a control 'anchor_animation_offset_from':
	 * it should be displayed if an offset is specified for any device (breakpoint)
	 *
	 * @return array  List of the conditions
	 */
	function trx_addons_elm_get_menu_anchor_offset_conditions() {
		$conditions = array();
		$bp = function_exists( 'trx_addons_elm_get_breakpoints' ) ? trx_addons_elm_get_breakpoints() : array( 'desktop' => 999999 );
		foreach ( $bp as $bp_name => $bp_max ) {
			$conditions[] = array(
				'name' => 'anchor_animation_offset' . ( 'desktop' == $bp_name ? '' : '_' . $bp_name ),
				'operator' => '!==',
				'value' => '',
			);
		}
		return $conditions;
	}
}

if ( ! function_exists( 'trx_addons_elm_get_menu_anchor_offset_breakpoints' ) ) {
	/**
	 * Return a list of the offsets for each breakpoint (as a pairs "max_window_width" => "offset")
	 * to allow the JS to select a value for the current window width
	 *
	 * @param array $settings  Settings of the widget 'Menu Anchor'
	 *
	 * @return array  List of the offsets for each breakpoint
	 */
	function trx_addons_elm_get_menu_anchor_offset_breakpoints( $settings ) {
		$offsets = array();
		$responsive = false;
		$bp = function_exists( 'trx_addons_elm_get_breakpoints' ) ? trx_addons_elm_get_breakpoints() : array( 'desktop' => 999999 );
		foreach ( $bp as $bp_name => $bp_max ) {
			$key = 'anchor_animation_offset' . ( 'desktop' == $bp_name ? '' : '_' . $bp_name );
			if ( isset( $settings[ $key ] ) && $settings[ $key ] !== '' ) {
				$offsets[ $bp_max ] = intval( $settings[ $key ] );
				if ( 'desktop' != $bp_name ) {
					$responsive = true;
				}
			}
		}
		// Sort a list on a direct order (from 'mobile' to 'widescreen')
		ksort( $offsets );
		// If a value is specified for the 'desktop' only - a list of breakpoints is not needed
		return $responsive ? $offsets : array();
	}
}

if ( ! function_exists( 'trx_addons_elm_add_params_animation_to_menu_anchor_before_render' ) ) {
	// Before Elementor 2.1.0
	add_action( 'elementor/frontend/element/before_render', 'trx_addons_elm_add_params_animation_to_menu_anchor_before_render', 10, 1 );
	// After Elementor 2.1.0
	add_action( 'elementor/frontend/widget/before_render', 'trx_addons_elm_add_params_animation_to_menu_anchor_before_render', 10, 1 );
	/**
	 * Add attributes data-anchor-animation-ease, data-anchor-animation-duration and data-anchor-animation-delay to the Elementor's widget 'Menu Anchor'
	 * 
	 * @hooked elementor/frontend/element/before_render (before Elementor 2.1.0)
	 * @hooked elementor/frontend/widget/before_render (after Elementor 2.1.0)
	 * 
	 * @param object $element Current element
	 */
	function trx_addons_elm_add_params_animation_to_menu_anchor_before_render( $element ) {
		if ( is_object( $element ) ) {
			$el_name = $element->get_name();
			if ( 'menu-anchor' == $el_name ) {
				$settings = $element->get_settings();
				$enabled = ! empty( $settings['anchor_animation'] ) && 'yes' === $settings['anchor_animation'];
				if ( $enabled ) {
					wp_enqueue_script( 'jquery-easing' );
					$element->add_render_attribute( 'inner', 'data-anchor-animation-ease', ! empty( $settings['anchor_animation_ease'] ) ? $settings['anchor_animation_ease'] : 'easeOutQuad' );
					if ( isset( $settings['anchor_animation_duration'] ) ) {
						$element->add_render_attribute( 'inner', 'data-anchor-animation-duration', $settings['anchor_animation_duration'] );
					}
					$element->add_render_attribute( 'inner', 'data-anchor-animation-delay', ! empty( $settings['anchor_animation_delay'] ) ? $settings['anchor_animation_delay'] : 0 );
				} else {
					$element->add_render_attribute( 'inner', 'data-anchor-animation-ease', 'none' );
					$element->add_render_attribute( 'inner', 'data-anchor-animation-duration', 0 );
					$element->add_render_attribute( 'inner', 'data-anchor-animation-delay', 0 );
				}
				// Offsets for each breakpoint (if a responsive values are specified)
				$offsets = trx_addons_elm_get_menu_anchor_offset_breakpoints( $settings );
				if ( isset( $settings['anchor_animation_offset'] ) && $settings['anchor_animation_offset'] !== '' ) {
					$element->add_render_attribute( 'inner', 'data-anchor-animation-offset', $settings['anchor_animation_offset'] );
				} else if ( count( $offsets ) > 0 ) {
					// An offset is specified for the some device only - add an empty attribute
					// to allow a script to detect that this anchor should be processed
					$element->add_render_attribute( 'inner', 'data-anchor-animation-offset', 0 );
				}
				if ( count( $offsets ) > 0 ) {
					$element->add_render_attribute( 'inner', 'data-anchor-animation-offset-breakpoints', wp_json_encode( $offsets ) );
				}
				$element->add_render_attribute( 'inner', 'data-anchor-animation-offset-from', ! empty( $settings['anchor_animation_offset_from'] ) ? $settings['anchor_animation_offset_from'] : 'fixed_rows' );
			}
		}
	}
}
