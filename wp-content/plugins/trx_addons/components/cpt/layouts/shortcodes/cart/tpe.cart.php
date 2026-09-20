<?php
/**
 * Template to represent shortcode as a widget in the Elementor preview area
 *
 * Written as a Backbone JavaScript template and using to generate the live preview in the Elementor's Editor
 *
 * @package ThemeREX Addons
 * @since v1.6.41
 */

extract(get_query_var('trx_addons_args_sc_layouts_cart'));
$allow_sc_styles_in_elementor = apply_filters( 'trx_addons_filter_allow_sc_styles_in_elementor', false, 'sc_layouts_cart' );
?><#
var id = settings._element_id ? settings._element_id + '_sc' : 'sc_layouts_cart_'+(''+Math.random()).replace('.', '');

var custom_classes = '';
var iconHTMLAll = '';
// Hide the product count badge when the cart is empty (preview always has 0 items)
var items_short_classes = 'sc_layouts_cart_items_short';
if ( settings.hide_empty_badge == '1' ) {
	items_short_classes += ' sc_layouts_cart_hide_empty sc_layouts_cart_items_short_empty';
}
#>
<?php if ( $allow_sc_styles_in_elementor ) { ?>
	<#
	if ( settings.product_count != '' ) {
		custom_classes += ' trx_addons_sc_cart_product_count_' + settings.product_count;

		if ( settings.badge_position != '' && settings.product_count == 'badge' ) {
			custom_classes += ' trx_addons_sc_cart_badge_position_' + settings.badge_position;
		}
	}

	if ( settings.select_cart_icon != '' && settings.select_cart_icon.value != '' ) {

		var iconHTML = elementor.helpers.renderIcon( view, settings.select_cart_icon, { 'aria-hidden': true }, 'span', 'object');
		if ( typeof iconHTML == 'object' ) {
			iconHTML = iconHTML.value || '';
		}

		iconHTMLAll += '<span class="sc_layouts_item_icon sc_layouts_cart_icon sc_icon_type_icons">' + iconHTML + '</span>';
	}
	#>
<?php } ?>
<div id="{{ id }}" class="sc_layouts_cart<?php $element->sc_add_common_classes('sc_layouts_cart'); ?>{{{ custom_classes }}}">
	<# if ( '' != iconHTMLAll ) { #>
		{{{ iconHTMLAll }}}
	<# } else { #>
		<span class="sc_layouts_item_icon sc_layouts_cart_icon sc_icon_type_icons trx_addons_icon-basket"></span>
	<# } #>
	<span class="sc_layouts_item_details sc_layouts_cart_details<#
		if ( settings.text == '' && <?php echo (int)apply_filters( 'trx_addons_filter_sc_layouts_cart_hide_empty_label', false ); ?> ) {
			print( ' sc_layouts_item_details_without_line1');
		}
	#>">
		<# if (settings.text != '') { #>
			<span class="sc_layouts_item_details_line1 sc_layouts_cart_label">{{{ settings.text }}}</span>
		<# } #>

		<span class="sc_layouts_item_details_line2 sc_layouts_cart_totals">
			<span class="sc_layouts_cart_items">0 <?php esc_html_e('items', 'trx_addons'); ?></span>
			<span class="sc_layouts_cart_summa_delimiter">-</span>
			<span class="sc_layouts_cart_summa">$0.00</span>
		</span>
	</span>
	<span class="{{ items_short_classes }}">0</span>
</div>