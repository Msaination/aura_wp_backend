<?php
/**
 * Shortcode: Display any previously created layout (Elementor support)
 *
 * @package ThemeREX Addons
 * @since v1.6.06
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;


// Elementor Widget
//------------------------------------------------------
if (!function_exists('trx_addons_sc_layouts_add_in_elementor')) {
	add_action( trx_addons_elementor_get_action_for_widgets_registration(), 'trx_addons_sc_layouts_add_in_elementor' );
	function trx_addons_sc_layouts_add_in_elementor() {
		
		if (!class_exists('TRX_Addons_Elementor_Layouts_Widget')) return;	

		class TRX_Addons_Elementor_Widget_Layouts extends TRX_Addons_Elementor_Layouts_Widget {

			/**
			 * Widget base constructor.
			 *
			 * Initializing the widget base class.
			 *
			 * @since 1.6.44
			 * @access public
			 *
			 * @param array      $data Widget data. Default is an empty array.
			 * @param array|null $args Optional. Widget default arguments. Default is null.
			 */
			public function __construct( $data = [], $args = null ) {
				parent::__construct( $data, $args );
				$this->add_plain_params([
					'size' => 'size+unit',
					'size_widescreen' => 'size+unit',
					'size_laptop' => 'size+unit',
					'size_tablet_extra' => 'size+unit',
					'size_tablet' => 'size+unit',
					'size_mobile_extra' => 'size+unit',
					'size_mobile' => 'size+unit',
					'show_delay' => 'size+unit'
				]);
			}
			/**
			 * Retrieve widget name.
			 *
			 * @since 1.6.41
			 * @access public
			 *
			 * @return string Widget name.
			 */
			public function get_name() {
				return 'trx_sc_layouts';
			}

			/**
			 * Retrieve widget title.
			 *
			 * @since 1.6.41
			 * @access public
			 *
			 * @return string Widget title.
			 */
			public function get_title() {
				return __( 'Layouts', 'trx_addons' );
			}

			/**
			 * Get widget keywords.
			 *
			 * Retrieve the list of keywords the widget belongs to.
			 *
			 * @since 2.27.2
			 * @access public
			 *
			 * @return array Widget keywords.
			 */
			public function get_keywords() {
				return [ 'panel', 'popup', 'layouts', 'custom', 'content' ];
			}

			/**
			 * Retrieve widget icon.
			 *
			 * @since 1.6.41
			 * @access public
			 *
			 * @return string Widget icon.
			 */
			public function get_icon() {
				return 'eicon-gallery-masonry trx_addons_elementor_widget_icon';
			}

			/**
			 * Retrieve the list of categories the widget belongs to.
			 *
			 * Used to determine where to display the widget in the editor.
			 *
			 * @since 1.6.41
			 * @access public
			 *
			 * @return array Widget categories.
			 */
			public function get_categories() {
				return ['trx_addons-layouts'];
			}

			/**
			 * Register widget controls.
			 *
			 * Adds different input fields to allow the user to change and customize the widget settings.
			 *
			 * @since 1.6.41
			 * @access protected
			 */
			protected function register_controls() {
				// Content Tab
				$this->register_content_general_controls();
				$this->register_content_display_options_controls();
				// Style Tab
				$this->register_style_panel_controls();
			}

			/*-----------------------------------------------------------------------------------*/
			/*	TAB CONTENT
			/*-----------------------------------------------------------------------------------*/

			protected function register_content_general_controls() {

				// Detect edit mode
				$is_edit_mode = trx_addons_elm_is_edit_mode();

				// If open params in Elementor Editor
				$params = $this->get_sc_params();
				// Prepare lists
				$layouts = ! $is_edit_mode ? array() : trx_addons_array_merge(	array(
														0 => trx_addons_get_not_selected_text( __( 'Not selected', 'trx_addons' ) )
														),
													trx_addons_get_list_layouts()
													);
				$templates = ! $is_edit_mode ? array() : trx_addons_array_merge(	array(
														0 => trx_addons_get_not_selected_text( __( 'Not selected', 'trx_addons' ) )
														),
													trx_addons_get_list_elementor_templates()
													);
				$default = 0;
				$layout = !empty($params['layout']) ? $params['layout'] : $default;

				$this->start_controls_section(
					'section_sc_layouts',
					[
						'label' => __( 'Layouts', 'trx_addons' ),
					]
				);

				$this->add_control(
					'type',
					[
						'label' => __( 'Type', 'trx_addons' ),
						'label_block' => false,
						'type' => Controls_Manager::SELECT,
						'options' => ! $is_edit_mode ? array() : apply_filters('trx_addons_sc_type', trx_addons_get_list_sc_layouts_type(), 'trx_sc_layouts'),
						'default' => 'default'
					]
				);

				$this->add_control(
					'popup_id',
					[
						'label' => __( "Popup (panel) ID", 'trx_addons' ),
						'label_block' => false,
						'type' => Controls_Manager::TEXT,
						'placeholder' => __( "Popup (panel) ID is required!", 'trx_addons' ),
						'default' => '',
						'condition' => [
							'type' => ['popup', 'panel']
						]
					]
				);

				$this->add_control(
					'layout', 
					[
						'label' => __("Custom Layout", 'trx_addons'),
						'label_block' => false,
						'description' => wp_kses( __("Select any previously created layout to insert to this page", 'trx_addons')
														. '<br>'
														. sprintf('<a href="%1$s" class="trx_addons_post_editor' . (intval($layout)==0 ? ' trx_addons_hidden' : '') . '"' . trx_addons_external_links_target( true ) . '>%2$s</a>',
																	admin_url( sprintf( "post.php?post=%d&amp;action=elementor", $layout ) ),
																	__("Open selected layout in a new tab to edit", 'trx_addons')
																),
													'trx_addons_kses_content'
													),
						'type' => Controls_Manager::SELECT,
						'options' => $layouts,
						'default' => $default
					]
				);

				$this->add_control(
					'template', 
					[
						'label' => __("or Elementor's Template", 'trx_addons'),
						'label_block' => false,
						'description' => wp_kses( __("Select any previously created template to insert to this page", 'trx_addons')
														. '<br>'
														. sprintf('<a href="%1$s" class="trx_addons_post_editor' . (intval($layout)==0 ? ' trx_addons_hidden' : '') . '"' . trx_addons_external_links_target( true ) . '>%2$s</a>',
																	admin_url( sprintf( "post.php?post=%d&amp;action=elementor", $layout ) ),
																	__("Open selected template in a new tab to edit", 'trx_addons')
																),
													'trx_addons_kses_content'
													),
						'type' => Controls_Manager::SELECT,
						'options' => $templates,
						'default' => $default,
						'condition' => [
							'layout' => [ 0, '0' ]
						]
					]
				);

				$this->add_control(
					'content',
					[
						'label' => __( 'or text content', 'trx_addons' ),
						'label_block' => true,
						"description" => wp_kses_data( __("Alternative content to be used instead layouts and templates", 'trx_addons') ),
						'type' => Controls_Manager::WYSIWYG,
						'default' => '',
						'separator' => 'none',
						'condition' => [
							'layout' => [ 0, '0' ],
							'template' => [ 0, '0' ]
						]
					]
				);

				$this->end_controls_section();
			}

			protected function register_content_display_options_controls() {

				// Detect edit mode
				$is_edit_mode = trx_addons_elm_is_edit_mode();

				$this->start_controls_section(
					'section_sc_layouts_display_options',
					[
						'label' => __( 'Display Options', 'trx_addons' ),
					]
				);

				$this->add_control(
					'position', 
					[
						'label' => __("Panel position", 'trx_addons'),
						'label_block' => false,
						'description' => wp_kses_data( __("Dock the panel to the specified side of the window", 'trx_addons') ),
						'type' => Controls_Manager::SELECT,
						'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_layouts_panel_positions(),
						'default' => 'right',
						'condition' => ['type' => 'panel']
					]
				);

				$this->add_control(
					'effect', 
					[
						'label' => __("Display effect", 'trx_addons'),
						'label_block' => false,
						'description' => wp_kses_data( __("Effect to display this panel", 'trx_addons') ),
						'type' => Controls_Manager::SELECT,
						'options' => ! $is_edit_mode ? array() : trx_addons_get_list_sc_layouts_panel_effects(),
						'default' => 'slide',
						'condition' => ['type' => 'panel']
					]
				);

				$this->add_responsive_control(
					'size',
					[
						'label' => __( 'Size', 'trx_addons' ),
						'description' => wp_kses_data( __("Size (width or height) of the panel", 'trx_addons') ),
						'type' => Controls_Manager::SLIDER,
						'default' => [
							'size' => 300,
							'unit' => 'px'
						],
						'range' => [
							'%' => [
								'min' => 5,
								'max' => 100
							],
							'px' => [
								'min' => 30,
								'max' => 1920
							],
							'em' => [
								'min' => 3,
								'max' => 300
							],
							'rem' => [
								'min' => 3,
								'max' => 300
							]
						],
						'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
						'condition' => ['type' => 'panel']
					]
				);

				$this->add_control(
					'modal',
					[
						'label' => __( 'Modal', 'trx_addons' ),
						'label_block' => false,
						'description' => wp_kses_data( __("Disable clicks on the rest window area", 'trx_addons') ),
						'type' => Controls_Manager::SWITCHER,
						'label_off' => __( 'Off', 'trx_addons' ),
						'label_on' => __( 'On', 'trx_addons' ),
						'return_value' => '1',
						'condition' => ['type' => 'panel']
					]
				);

				$this->add_control(
					'shift_page',
					[
						'label' => __( 'Shift page', 'trx_addons' ),
						'label_block' => false,
						'description' => wp_kses_data( __("Shift page content when panel is opened", 'trx_addons') ),
						'type' => Controls_Manager::SWITCHER,
						'label_off' => __( 'Off', 'trx_addons' ),
						'label_on' => __( 'On', 'trx_addons' ),
						'return_value' => '1',
						'condition' => ['type' => 'panel']
					]
				);

				$this->add_control(
					'show_on', 
					[
						'label' => __("Show on", 'trx_addons'),
						'label_block' => false,
						'description' => wp_kses_data( __("The event by which the popup/panel should be displayed", 'trx_addons') ),
						'type' => Controls_Manager::SELECT,
						'options' => ! $is_edit_mode ? array() : trx_addons_get_list_layouts_show_on(),
						'default' => 'none',
						'condition' => [
							'type' => ['popup', 'panel']
						]
					]
				);

				$this->add_control(
					'show_delay',
					[
						'label' => __( 'Show delay', 'trx_addons' ),
						'description' => wp_kses_data( __("How many seconds to wait before the popup appears", 'trx_addons') ),
						'type' => Controls_Manager::SLIDER,
						'default' => [
							'size' => 0,
							'unit' => 'px'
						],
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 120
							]
						],
						'size_units' => ['px'],
						'condition' => [
							'type' => ['popup', 'panel'],
							'show_on' => ['on_page_load', 'on_page_load_once']
						]
					]
				);
				
				$this->end_controls_section();
			}

			/*-----------------------------------------------------------------------------------*/
			/*	TAB STYLE
			/*-----------------------------------------------------------------------------------*/

			/**
			 * Style > Panel (Popup)
			 *
			 * Tabs: Container / Overlay / Close / Title.
			 *
			 * @since 1.0
			 * @access protected
			 */
			protected function register_style_panel_controls() {

				$layouts       = '.sc_layouts_{{ID}}';
				$popup         = $layouts . '.sc_layouts_popup';
				$popup_overlay = '.mfp-bg';
				$panel         = $layouts . '.sc_layouts_panel';
				$panel_inner   = $panel . ' .sc_layouts_panel_inner';
				$panel_overlay  = '.sc_layouts_panel_hide_content_{{ID}}';

				$this->start_controls_section(
					'section_style_panel',
					array(
						'label'     => __( 'Panel / Popup', 'trx_addons' ),
						'tab'       => Controls_Manager::TAB_STYLE,
						'condition' => array(
							'type' => ['popup', 'panel']
 						)
					)
				);

				$this->start_controls_tabs( 'panel_tabs' );

				/* --- Container --- */
				$this->start_controls_tab( 'panel_tab_container', array( 'label' => __( 'Container', 'trx_addons' ) ) );

				$this->add_responsive_control(
					'panel_z_index',
					array(
						'label'       => __( 'Z-index', 'trx_addons' ),
						'description' => __( 'The theme header usually has a z-index of around 8 000. Therefore, if you want to place the panel on top of it, set a higher value.', 'trx_addons' ),
						'type'        => Controls_Manager::SLIDER,
						'size_units'  => array( 'px' ),
						'range'       => array(
							'px' => array( 'min' => 0, 'max' => 10000, 'step' => 100 )
						),
						'selectors'   => array(
							$layouts => 'z-index: {{SIZE}};'
						)
					)
				);

				$this->add_group_control(
					Group_Control_Background::get_type(),
					array(
						'name'     => 'panel_bg',
						'types'    => array( 'classic', 'gradient' ),
						'selector' => $popup . ',' . $panel_inner
					)
				);

				$this->add_group_control(
					Group_Control_Border::get_type(),
					array(
						'name'     => 'panel_border',
						'selector' => $popup . ',' . $panel_inner
					)
				);

				$this->add_control(
					'panel_radius',
					array(
						'label'      => __( 'Border Radius', 'trx_addons' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
						'selectors'  => array(
							$popup . ',' . $panel . ',' . $panel_inner => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						)
					)
				);

				$this->add_group_control(
					Group_Control_Box_Shadow::get_type(),
					array(
						'name'     => 'panel_shadow',
						'selector' => $popup . ',' . $panel_inner
					)
				);

				$this->add_responsive_control(
					'panel_padding',
					array(
						'label'      => __( 'Padding', 'trx_addons' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', '%', 'em', 'rem', 'vw', 'vh', 'custom' ),
						'selectors'  => array(
							$popup . ',' . $panel_inner => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						)
					)
				);

				$this->end_controls_tab();

				/* --- Overlay --- */
				$this->start_controls_tab( 'panel_tab_overlay', array( 'label' => __( 'Overlay', 'trx_addons' ) ) );

				$this->add_control(
					'overlay_color',
					array(
						'label'     => __( 'Overlay Color', 'trx_addons' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => 'rgba(0,0,0,0.6)',
						'selectors' => array(
							$popup_overlay . ',' . $panel_overlay => 'background-color: {{VALUE}};'
						),
					)
				);

				$this->end_controls_tab();

				/* --- Close --- */
				$this->start_controls_tab( 'panel_tab_close', array( 'label' => __( 'Close', 'trx_addons' ) ) );

				$close        = $popup . ' .mfp-close' . ',' . $panel . ' .sc_layouts_panel_close';
				$close_icon   = $popup . ' .mfp-close-icon' . ',' . $panel . ' .sc_layouts_panel_close_icon';
				$close_psevdo = $popup . ' .mfp-close-icon:before' . ',' . $popup . ' .mfp-close-icon:after'
								. ',' . $panel . ' .sc_layouts_panel_close_icon:before'. ',' . $panel . ' .sc_layouts_panel_close_icon:after';
				$close_svg    = $popup . ' .mfp-close-icon svg' . ',' . $panel . ' .sc_layouts_panel_close_icon svg';
				$close_hover        = $popup . ' .mfp-close:hover' . ',' . $panel . ' .sc_layouts_panel_close:hover';
				$close_icon_hover   = $popup . ' .mfp-close:hover .mfp-close-icon' . ',' . $panel . ' .sc_layouts_panel_close:hover .sc_layouts_panel_close_icon';
				$close_psevdo_hover = $popup . ' .mfp-close:hover .mfp-close-icon:before'
								. ',' . $popup . ' .mfp-close:hover .mfp-close-icon:after'
								. ',' . $panel . ' .sc_layouts_panel_close:hover .sc_layouts_panel_close_icon:before'
								. ',' . $panel . ' .sc_layouts_panel_close:hover .sc_layouts_panel_close_icon:after';
				$close_svg_hover    = $popup . ' .mfp-close:hover .mfp-close-icon svg' . ',' . $panel . ' .sc_layouts_panel_close:hover .sc_layouts_panel_close_icon svg';

				$this->add_control(
					'close_position',
					[
						'label' => __( 'Icon Position', 'trx_addons' ),
						'label_block' => false,
						'type' => Controls_Manager::SWITCHER,
						'label_off' => __( 'Outside', 'trx_addons' ),
						'label_on' => __( 'Inside', 'trx_addons' ),
						'return_value' => 'inside',
						'condition' => ['type' => 'popup']
					]
				);

				$this->add_control(
					'close_animation',
					[
						'label' => __( 'Icon Animation', 'trx_addons' ),
						'label_block' => false,
						'type' => Controls_Manager::SWITCHER,
						'default' => '1',
						'return_value' => '1',
						'condition' => [
							'type' => ['popup', 'panel']
						]
					]
				);

				$this->add_responsive_control(
					'close_size',
					array(
						'label'      => __( 'Icon Size', 'trx_addons' ),
						'type'       => Controls_Manager::SLIDER,
						'size_units' => array( 'px', 'em', 'rem', 'custom' ),
						'range'      => array(
							'px'  => array( 'min' => 8, 'max' => 60 ),
							'em'  => array( 'min' => 0.5, 'max' => 5, 'step' => 0.1 ),
							'rem' => array( 'min' => 0.5, 'max' => 5, 'step' => 0.1 )
						),
						'selectors'  => array(
							$close => 'font-size: {{SIZE}}{{UNIT}};'
						),
					)
				);

				$this->add_control(
					'close_color',
					array(
						'label'     => __( 'Icon Color', 'trx_addons' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							$close_icon => 'color: {{VALUE}};',
							$close_psevdo => 'border-color: {{VALUE}};',
							$close_svg => 'fill: {{VALUE}};'
						),
					)
				);

				$this->add_control(
					'close_color_hover',
					array(
						'label'     => __( 'Icon Hover Color', 'trx_addons' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							$close_icon_hover => 'color: {{VALUE}};',
							$close_psevdo_hover => 'border-color: {{VALUE}};',
							$close_svg_hover => 'fill: {{VALUE}};'
						),
					)
				);

				$this->add_control(
					'close_box_heading',
					array(
						'label'     => __( 'Box', 'trx_addons' ),
						'type'      => Controls_Manager::HEADING,
						'separator' => 'before'
					)
				);

				$this->add_control(
					'close_bg',
					array(
						'label'     => __( 'Background Color', 'trx_addons' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							$close => 'background-color: {{VALUE}};'
						)
					)
				);

				$this->add_control(
					'close_bg_hover',
					array(
						'label'     => __( 'Background Color Hover', 'trx_addons' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							$close_hover => 'background-color: {{VALUE}};'
						)
					)
				);

				$this->add_group_control(
					Group_Control_Border::get_type(),
					array(
						'name'     => 'close_border',
						'selector' => $close
					)
				);

				$this->add_control(
					'close_border_hover',
					array(
						'label'     => __( 'Border Color Hover', 'trx_addons' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							$close_hover => 'border-color: {{VALUE}};'
						)
					)
				);

				$this->add_control(
					'close_radius',
					array(
						'label'      => __( 'Border Radius', 'trx_addons' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
						'selectors'  => array(
							$close => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						)
					)
				);

				$this->add_responsive_control(
					'close_padding',
					array(
						'label'      => __( 'Padding', 'trx_addons' ),
						'type'       => Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', '%', 'em', 'rem', 'custom' ),
						'selectors'  => array(
							$close => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						)
					)
				);

				$this->end_controls_tab();

				$this->end_controls_tabs();

				$this->end_controls_section();
			}

			/*-----------------------------------------------------------------------------------*/
			/*	RENDER
			/*-----------------------------------------------------------------------------------*/

			/**
			 * Render widget's template for the editor.
			 *
			 * Written as a Backbone JavaScript template and used to generate the live preview.
			 *
			 * @since 1.6.41
			 * @access protected
			 */
			// Commented, because when the 'type' is 'default' -
			// we need to load a custom layout from a server
			// protected function content_template() {
			// 	$this->sc_show_placeholder( array(
			// 		'title' => 'type'
			// 	) );
			// }
		}
		
		// Register widget
		trx_addons_elm_register_widget( 'TRX_Addons_Elementor_Widget_Layouts' );
	}
}

// Disable our widgets (shortcodes) to use in Elementor
// because we create special Elementor's widgets instead
if (!function_exists('trx_addons_sc_layouts_black_list')) {
	add_action( 'elementor/widgets/black_list', 'trx_addons_sc_layouts_black_list' );
	function trx_addons_sc_layouts_black_list($list) {
		$list[] = 'TRX_Addons_SOW_Widget_Layouts';
		return $list;
	}
}
