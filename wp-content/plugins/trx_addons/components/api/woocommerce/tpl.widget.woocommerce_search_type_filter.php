<?php
/**
 * The style "filter" of the Widget "WooCommerce Search"
 *
 * @package ThemeREX Addons
 * @since v1.88.0
 */

$trx_addons_args = get_query_var('trx_addons_args_widget_woocommerce_search');
extract( $trx_addons_args );

// Before widget (defined by themes)
trx_addons_show_layout($before_widget);

// Widget title if one was input (before and after defined by themes)
trx_addons_show_layout($title, $before_title, $after_title);

// Widget body
global $TRX_ADDONS_STORAGE;
if ( empty( $TRX_ADDONS_STORAGE['trx_addons_woocommerce_search_number'] ) ) {
	$TRX_ADDONS_STORAGE['trx_addons_woocommerce_search_number'] = 0;
}
$TRX_ADDONS_STORAGE['trx_addons_woocommerce_search_number']++;
?><div class="sc_form trx_addons_woocommerce_search trx_addons_woocommerce_search_type_<?php 
		echo esc_attr( $trx_addons_args['type'] );
		if ( ! empty( $trx_addons_args['apply'] ) ) echo ' trx_addons_woocommerce_search_apply';
		if ( ! empty( $trx_addons_args['ajax'] ) ) echo ' trx_addons_woocommerce_search_ajax';
		if ( ! empty( $trx_addons_args['class'] ) ) echo ' ' . esc_attr( $trx_addons_args['class'] );
		?>"
		data-expanded="<?php echo ! empty( $trx_addons_args['expanded'] ) ? (int)$trx_addons_args['expanded'] : 0; ?>"
		data-number="<?php echo (int)$TRX_ADDONS_STORAGE['trx_addons_woocommerce_search_number']; ?>"<?php
	if ( ! empty( $trx_addons_args['css'] ) ) echo ' style="' . esc_attr( $trx_addons_args['css'] ) . '"'; 
?>>
	<form class="trx_addons_woocommerce_search_form sc_form_form sc_form_custom" action="<?php echo esc_url( trx_addons_woocommerce_get_shop_page_link() ); ?>" method="get"><?php

		do_action( 'trx_addons_action_woocommerce_search_form_before_fields_wrap', $trx_addons_args );

		?><div class="trx_addons_woocommerce_search_form_fields_wrap"><?php

			?><div class="trx_addons_woocommerce_search_header">
				<a href="#" role="button" class="trx_addons_woocommerce_search_clear_all"><?php echo esc_html( apply_filters( 'trx_addons_filter_woocommerce_search_clear_all_text', __( 'Clear all', 'trx_addons' ) ) ); ?></a>
				<a href="#" role="button" class="trx_addons_woocommerce_search_close trx_addons_button_close" title="<?php esc_html_e( 'Close', 'trx_addons' ); ?>"><span class="trx_addons_button_close_icon"></span></a>
			</div><?php

			?><div class="trx_addons_woocommerce_search_form_fields_wrap_inner"><?php

				if ( is_array( $trx_addons_args['fields'] ) ) {

					do_action( 'trx_addons_action_woocommerce_search_form_before_fields', $trx_addons_args );

					$filters_filled = 0;
					$not_empty = false;
					$price_out = false;
					$field_number = 0;

					// Get term counts if a product category is active.
					//
					// Two knobs shape the call:
					//   - Taxonomy whitelist: when autofilters=0 we restrict the GROUP BY to the
					//     pa_* / product_tag taxonomies actually referenced by this widget instance.
					//     The default whitelist is "all pa_* + product_tag" and is the dominant cost
					//     on shops with dozens of attribute taxonomies — pointless when the widget
					//     shows only a handful of them.
					//   - with_counts: when show_counters=0 the SQL returns a 0 placeholder instead
					//     of COUNT(DISTINCT posts.ID). The field template already gates the (123)
					//     badge on show_counters, so the COUNT result would be discarded anyway.
					$atts_counts       = array();
					$atts_counts_index = array();
					$tax_query = WC_Query::get_main_tax_query();
					if ( is_array( $tax_query ) ) {
						$on_category = false;
						foreach( $tax_query as $k => $v ) {
							if ( $k === 'relation' || ! is_array( $v ) ) {
								continue;
							}
							if ( ! empty( $v['taxonomy'] ) && $v['taxonomy'] == 'product_cat' && ! empty( $v['terms'][0] ) ) {
								$on_category = true;
								break;
							}
						}
						if ( $on_category ) {
							$used_taxonomies = array();
							if ( is_array( $trx_addons_args['fields'] ) ) {
								foreach ( $trx_addons_args['fields'] as $fld ) {
									if ( empty( $fld['filter'] ) ) {
										continue;
									}
									if ( $fld['filter'] === 'product_tag' || strpos( $fld['filter'], 'pa_' ) === 0 ) {
										$used_taxonomies[] = $fld['filter'];
									}
								}
								$used_taxonomies = array_values( array_unique( $used_taxonomies ) );
							}

							// autofilters needs the default whitelist to discover taxonomies present
							// in the category. Without it we restrict to what this widget consumes,
							// and skip the call outright when no relevant taxonomies are configured.
							$count_taxonomies = ! empty( $trx_addons_args['autofilters'] ) ? null : $used_taxonomies;
							$with_counts      = ! empty( $trx_addons_args['show_counters'] );

							if ( null === $count_taxonomies || ! empty( $count_taxonomies ) ) {
								$atts_counts = trx_addons_widget_woocommerce_search_get_filtered_terms_in_category( $count_taxonomies, $with_counts );
								usort( $atts_counts, function( $a, $b ) {
									return $a->taxonomy <=> $b->taxonomy;
								} );
							}

							// Index $atts_counts by (taxonomy, term_id) so the per-field loop can
							// look up matches in O(1) instead of walking the array per term — and
							// so we can pass `include => term_ids_for_taxonomy` to get_terms() and
							// avoid pulling every term of large attribute taxonomies just to drop
							// most of them.
							foreach ( $atts_counts as $att ) {
								if ( ! empty( $att->taxonomy ) && ! empty( $att->term_id ) ) {
									$atts_counts_index[ $att->taxonomy ][ (int) $att->term_id ] = $att;
								}
							}
						}
						// Add all taxonomies from the current category
						if ( ! empty( $trx_addons_args['autofilters'] ) ) {
							$trx_addons_args['fields'] = trx_addons_widget_woocommerce_search_add_attributes_to_filters( $trx_addons_args['fields'], $atts_counts );
						}
					}
					
					// Fill params from the search query
					$params = trx_addons_widget_woocommerce_search_query_params( $trx_addons_args['fields'] );

					// Display fields
					foreach ( $trx_addons_args['fields'] as $fld ) {

						if ( trx_addons_is_off( $fld['filter'] ) ) continue;

						// Skip product attributes that are disabled as filters via the
						// "Use as a filter" checkbox in the attribute settings.
						if ( ! trx_addons_widget_woocommerce_search_is_attribute_used_as_filter( $fld['filter'] ) ) continue;

						$field_number++;
						$tax_name = $fld['filter'];

						if ( ! trx_addons_is_off( $params[$tax_name] ) ) {
							$not_empty = true;
						}

						if ( $tax_name == 's' ) {
							if ( ! empty( $params[$tax_name] ) ) {
								$filters_filled++;
							}
							trx_addons_get_template_part( TRX_ADDONS_PLUGIN_API . 'woocommerce/tpl.widget.woocommerce_search_field.php',
															'trx_addons_args_widget_woocommerce_search_field',
															array_merge( $trx_addons_args,
																		array(
																			'field_title'  	=> ! empty( $fld['text'] ) ? $fld['text'] : __( 'Search', 'trx_addons' ),
																			'field_class'   => ! empty( $trx_addons_args['expanded'] ) && $field_number <= (int)$trx_addons_args['expanded'] ? 'sc_form_field_expanded' : '',
																			'field_name'  	=> $tax_name,
																			'field_value' 	=> $params[$tax_name],
																			'field_type'  	=> 'text'
																			)
																		)
														);
						
						} else if ( in_array( $tax_name, array( 'min_price', 'max_price' ) ) ) {
							if ( ! $price_out ) {
								$price_out = true;

								// Round values to nearest 10 by default.
								$step = max( apply_filters( 'woocommerce_price_filter_widget_step', 10 ), 1 );

								// Find min and max price in current result set.
								$prices    = trx_addons_widget_woocommerce_search_get_filtered_price();
								$min_price = $prices->min_price;
								$max_price = $prices->max_price;

								// Check to see if we should add taxes to the prices if store are excl tax but display incl.
								$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

								if ( wc_tax_enabled() && ! wc_prices_include_tax() && 'incl' === $tax_display_mode ) {
									$tax_class = apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ); // Uses standard tax class.
									$tax_rates = WC_Tax::get_rates( $tax_class );

									if ( $tax_rates ) {
										$min_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $min_price, $tax_rates ) );
										$max_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $max_price, $tax_rates ) );
									}
								}

								$min_price = apply_filters( 'woocommerce_price_filter_widget_min_amount', floor( $min_price / $step ) * $step );
								$max_price = apply_filters( 'woocommerce_price_filter_widget_max_amount', ceil( $max_price / $step ) * $step );

								// If both min and max are equal, we don't need a slider.
								if ( $min_price !== $max_price ) {

									$current_min_price = !empty( $params['min_price'] ) ? floor( floatval( $params['min_price'] ) / $step ) * $step : $min_price; // WPCS: input var ok, CSRF ok.
									$current_max_price = !empty( $params['max_price'] ) ? ceil( floatval( $params['max_price'] ) / $step ) * $step : $max_price; // WPCS: input var ok, CSRF ok.

									if ( $current_min_price != $min_price || $current_max_price != $max_price ) {
										$filters_filled++;
									}

									trx_addons_get_template_part( TRX_ADDONS_PLUGIN_API . 'woocommerce/tpl.widget.woocommerce_search_field.php',
																'trx_addons_args_widget_woocommerce_search_field',
																array_merge( $trx_addons_args,
																			array(
																				'field_title'  	=> ! empty( $fld['text'] ) ? $fld['text'] : __( 'Price', 'trx_addons' ),
																				'field_class'   => ! empty( $trx_addons_args['expanded'] ) && $field_number <= $trx_addons_args['expanded'] ? 'sc_form_field_expanded' : '',
																				'field_name'  	=> 'price',
																				'field_min'		=> $min_price,
																				'field_max'		=> $max_price,
																				'field_step'	=> $step,
																				'field_value' 	=> $current_min_price . ',' . $current_max_price,
																				'field_type'  	=> 'range'
																				)
																			)
															);
								}
							}

						} else if ( $tax_name == 'rating' ) {
							$rating_filter  = isset( $params[$tax_name] ) ? implode( ',', array_filter( array_map( 'absint', explode( ',', wp_unslash( $params[$tax_name] ) ) ) ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
							$rating_options = array();
							// Skip the per-rating count query when the widget hides counters — the
							// field template gates the (123) badge on show_counters anyway.
							$count_ratings  = ! empty( $trx_addons_args['show_counters'] );
							for ( $rating = 5; $rating >= 1; $rating-- ) {
								$rating_options[ $rating ] = (object) array(
									'term_id'   => $rating,
									'name'      => '<span class="star-rating">' . wc_get_star_rating_html( $rating ) . '</span>',
									'count'     => $count_ratings ? trx_addons_widget_woocommerce_search_get_filtered_product_count_by_rating( $rating ) : 0
								);
							}
							if ( ! empty( $params[$tax_name] ) ) {
								$filters_filled += count( explode( ',', $params[$tax_name] ) );
							}
							trx_addons_get_template_part( TRX_ADDONS_PLUGIN_API . 'woocommerce/tpl.widget.woocommerce_search_field.php',
															'trx_addons_args_widget_woocommerce_search_field',
															array_merge( $trx_addons_args,
																		array(
																			'field_title'  	  => ! empty( $fld['text'] ) ? $fld['text'] : __( 'Rating', 'trx_addons'),
																			'field_class'     => ! empty( $trx_addons_args['expanded'] ) && $field_number <= $trx_addons_args['expanded'] ? 'sc_form_field_expanded' : '',
																			'field_name'  	  => $tax_name,
																			'field_value' 	  => $params[$tax_name],
																			'field_multiple'  => true,
																			'field_options'   => $rating_options,
																			'field_return' 	  => 'id',	// id | slug
																			'field_type'  	  => 'select'
																			)
																		)
														);

						} else {
							$tax_obj = get_taxonomy( $tax_name );
							$type = '';
							if ( empty( $trx_addons_args['force_checkboxes'] ) && function_exists( 'trx_addons_woocommerce_attrib_get_type' ) ) {
								$type = trx_addons_woocommerce_attrib_get_type( $tax_name );
							}
							if ( empty( $type ) ) {
								$type = 'select';
							}
							if ( $tax_name == 'product_cat' || empty( $params['product_cat'] ) ) {	// || empty( $trx_addons_args['show_counters'] )
								$terms = get_terms( array(
														// Don't use order and orderby - it's break a custom ordering
														//'orderby' => 'name',
														//'order' => 'ASC',
														'hide_empty' => $tax_name != 'product_cat',
														'hierarchical' => $tax_name == 'product_cat',
														'taxonomy' => $tax_name,
													) );
								if ( $tax_name == 'product_cat' ) {
									$terms = array_merge(
												array( (object) array(
																	'term_id' => 0,
																	'slug' => '',
																	'hierarchy_level' => 0,
																	'name' => trx_addons_get_not_selected_text( __( 'All categories', 'trx_addons' ) )
																	)
												),
												trx_addons_get_hierarchical_terms( $terms, 0, 1 )
											);
								}
							} else {
								$terms = array();
								// Pull only the terms that actually appear in the filtered set, in the
								// sort order WooCommerce defines for this attribute. The pre-built
								// $atts_counts_index gives us the term_id list for $tax_name; passing
								// it as `include` makes get_terms() restrict the result to that set,
								// so taxonomies with thousands of values don't materialize in full.
								// The lookup in the loop is O(1) via the same index — replacing the
								// previous trx_addons_array_search() linear scan per term.
								if ( ! empty( $atts_counts_index[ $tax_name ] ) ) {
									$term_ids = array_keys( $atts_counts_index[ $tax_name ] );
									$terms2 = get_terms(
										array(
											'taxonomy'   => $tax_name,
											'hide_empty' => false,
											'include'    => $term_ids,
										)
									);
									if ( is_array( $terms2 ) ) {
										foreach( $terms2 as $term ) {
											$tid = ! empty( $term->term_id ) ? (int) $term->term_id : 0;
											if ( $tid && isset( $atts_counts_index[ $tax_name ][ $tid ] ) ) {
												$terms[] = $atts_counts_index[ $tax_name ][ $tid ];
											}
										}
									}
								}
							}
							$filters_selected = '';
							if ( ! empty( $params[$tax_name] ) ) {
								$parts = explode( ',', $params[$tax_name] );
								$filters_filled += count( $parts );
								foreach ( $terms as $term ) {
									if ( in_array( urldecode( $term->slug ), $parts ) ) {
										$filters_selected .= ( ! empty( $filters_selected ) ? ', ' : '' ) . $term->name;
									}
								}
							}
							if ( ( is_array( $terms ) && count( $terms ) > 0 ) || ! apply_filters( 'trx_addons_filters_woocommerce_search_hide_empty_filters', true ) ) {
								trx_addons_get_template_part( TRX_ADDONS_PLUGIN_API . 'woocommerce/tpl.widget.woocommerce_search_field.php',
																'trx_addons_args_widget_woocommerce_search_field',
																array_merge( $trx_addons_args,
																			array(
																				'field_title'  	  => ! empty( $fld['text'] ) ? $fld['text'] : ( ! empty( $tax_obj->label ) ? $tax_obj->label : '' ),
																				'field_class'     => ! empty( $trx_addons_args['expanded'] ) && $field_number <= $trx_addons_args['expanded'] ? 'sc_form_field_expanded' : '',
																				'field_name'  	  => $tax_name,
																				'field_value' 	  => urldecode( $params[$tax_name] ),
																				'field_multiple'  => $tax_name != 'product_cat',
																				'field_options'   => is_array( $terms ) ? $terms : array(),
																				'field_selected'  => $filters_selected,
																				'field_return' 	  => 'slug',	// id | slug
																				'field_type'  	  => $type
																				)
																			)
															);
							}
						}
					}

					do_action( 'trx_addons_action_woocommerce_search_form_after_fields', $trx_addons_args );

				}

				if ( ! empty($trx_addons_args['last_text']) ) {
					?><label class="trx_addons_woocommerce_search_last_text"><?php echo esc_html($trx_addons_args['last_text']); ?></label><?php
				}
			?></div><?php

			?><a href="#" role="button" class="trx_addons_woocommerce_search_button_show sc_button">
				<span class="trx_addons_woocommerce_search_button_show_text"><?php esc_html_e( 'Show Products', 'trx_addons' ); ?></span>
				<span class="trx_addons_woocommerce_search_button_show_total"><?php echo esc_html( wc_get_loop_prop( 'total' ) ); ?></span>
			</a><?php

		?></div><?php

		do_action( 'trx_addons_action_woocommerce_search_form_after_fields_wrap', $trx_addons_args );

		?><a href="#" role="button" class="trx_addons_woocommerce_search_button_filters sc_button sc_button_size_small">
			<span class="trx_addons_woocommerce_search_button_filters_text"><?php echo empty($trx_addons_args['button_text']) ? esc_html__('Filters', 'trx_addons') : esc_html( $trx_addons_args['button_text'] ); ?></span>
			<span class="trx_addons_woocommerce_search_button_filters_total<?php if ( $filters_filled == 0 ) echo ' trx_addons_woocommerce_search_button_filters_total_empty'; ?>"><?php echo esc_html( $filters_filled ); ?></span>
		</a><?php
	?></form>
</div><?php

// After widget (defined by themes)
trx_addons_show_layout($after_widget);
