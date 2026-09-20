<?php
/**
 * Widget: WooCommerce Search (Advanced search form)
 *
 * @package ThemeREX Addons
 * @since v1.6.38
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Total number of fields in the widget
if ( ! defined( 'TRX_ADDONS_WOOCOMMERCE_SEARCH_FIELDS') ) define('TRX_ADDONS_WOOCOMMERCE_SEARCH_FIELDS', 8 );

// Whether to persist filter-widget query results in the WP object cache across requests.
// Per-request (static) memoization always runs; this flag only gates wp_cache_get/wp_cache_set.
// Set to true in wp-config.php (or via a mu-plugin) when a persistent backend (Redis/Memcached)
// is available — without one, wp_cache_* falls back to in-memory and gives no cross-request win.
if ( ! defined( 'TRX_ADDONS_WOOCOMMERCE_SEARCH_USE_WP_OBJECT_CACHE' ) ) define( 'TRX_ADDONS_WOOCOMMERCE_SEARCH_USE_WP_OBJECT_CACHE', false );

// Transient fallback for the "bare category" state — visiting a category page with no facets,
// no price/rating filter, and no search. That state produces the same result for every visitor
// and stays valid until products/terms change, so it is the most cacheable shape we render.
// Storing it in a WP transient lets the cache survive between requests even without a persistent
// object-cache backend. Restricting the fallback to the bare state keeps wp_options write
// pressure bounded (one row per category × widget config × query type).
if ( ! defined( 'TRX_ADDONS_WOOCOMMERCE_SEARCH_USE_TRANSIENTS' ) ) define( 'TRX_ADDONS_WOOCOMMERCE_SEARCH_USE_TRANSIENTS', true );

// Lifetime of bare-category transients. Stale entries are also invalidated implicitly by the
// version stamp embedded inside each value, so a long TTL is safe; this just bounds how long
// abandoned slots linger in wp_options after the corresponding category is deleted.
if ( ! defined( 'TRX_ADDONS_WOOCOMMERCE_SEARCH_TRANSIENT_TTL' ) ) define( 'TRX_ADDONS_WOOCOMMERCE_SEARCH_TRANSIENT_TTL', 6 * HOUR_IN_SECONDS );

if ( ! function_exists('trx_addons_widget_woocommerce_search_load') ) {
	add_action( 'widgets_init', 'trx_addons_widget_woocommerce_search_load', 21 );
	/**
	 * Register widget 'WooCommerce Search'
	 * 
	 * @hooked widgets_init, 21
	 */
	function trx_addons_widget_woocommerce_search_load() {
		if ( ! trx_addons_exists_woocommerce() ) {
			return;
		}
		register_widget( 'trx_addons_widget_woocommerce_search' );
	}
}

if ( ! function_exists( 'trx_addons_get_list_woocommerce_search_types' ) ) {
	/**
	 * Return list of the WooCommerce search types
	 * 
	 * @trigger trx_addons_filter_get_list_woocommerce_search_types
	 *
	 * @return array  List of the WooCommerce search types
	 */
	function trx_addons_get_list_woocommerce_search_types() {
		return apply_filters( 'trx_addons_filter_get_list_woocommerce_search_types', array(
			'inline' => esc_html__('Inline', 'trx_addons'),
			'form'   => esc_html__('Form', 'trx_addons'),
			'filter' => esc_html__('Filter', 'trx_addons'),
		) );
	}
}

if ( ! function_exists( 'trx_addons_get_list_woocommerce_search_filters' ) ) {
	/**
	 * Return list of the WooCommerce search filters
	 *
	 * @param string $none_key  Key for the 'Not selected' item
	 * 
	 * @return array  List of the WooCommerce search filters
	 */
	function trx_addons_get_list_woocommerce_search_filters( $none_key = 'none' ) {
		$list = array(
			$none_key		=> trx_addons_get_not_selected_text( __( 'Not selected', 'trx_addons' ) ),
			's'				=> __('Search string', 'trx_addons'),
			'product_cat'	=> __('Product Category', 'trx_addons'),
			'product_tag'	=> __('Product Tag', 'trx_addons'),
			'min_price'		=> __('Min. price', 'trx_addons'),
			'max_price'		=> __('Max. price', 'trx_addons'),
			'rating'		=> __('Rating', 'trx_addons')
		);
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if ( !empty( $attribute_taxonomies ) ) {
			foreach ( $attribute_taxonomies as $attribute ) {
				$tax_name = wc_attribute_taxonomy_name( $attribute->attribute_name );
				// Don't offer attributes that are disabled as filters via the
				// "Use as a filter" checkbox in the attribute settings.
				if ( ! trx_addons_widget_woocommerce_search_is_attribute_used_as_filter( $tax_name ) ) {
					continue;
				}
				$list[ $tax_name ] = $attribute->attribute_label;
			}
		}
		return $list;
	}
}

if ( ! function_exists( 'trx_addons_get_list_woocommerce_search_expanded' ) ) {
	/**
	 * Return list of options for the field 'Expanded' in the widget 'WooCommerce Search'
	 * 
	 * @trigger trx_addons_filter_get_list_woocommerce_search_expanded
	 *
	 * @return array  List of options
	 */
	function trx_addons_get_list_woocommerce_search_expanded() {
		$list = array(
					0 => esc_html__('Collapse all filters', 'trx_addons'),
					999 => esc_html__('Expand all filters', 'trx_addons'),
					1 => esc_html__('Expand first item only', 'trx_addons'),
				);
		for ( $i = 2; $i < TRX_ADDONS_WOOCOMMERCE_SEARCH_FIELDS; $i++ ) {
			$list[ $i ] = sprintf( esc_html__('Expand first %d items', 'trx_addons'), $i );
		}
		return apply_filters( 'trx_addons_filter_get_list_woocommerce_search_expanded', $list );
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_woocommerce_output_start' ) ) {
	add_action( 'woocommerce_before_main_content', 'trx_addons_widget_woocommerce_search_woocommerce_output_start', 1 );
	/**
	 * Mark start of inline classes inside WooCommerce output (used in AJAX)
	 * 
	 * @hooked woocommerce_before_main_content, 1
	 */
	function trx_addons_widget_woocommerce_search_woocommerce_output_start() {
		trx_addons_add_inline_css( '#woocommerce_output_start{}' );
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_woocommerce_output_end' ) ) {
	add_action( 'woocommerce_after_main_content', 'trx_addons_widget_woocommerce_search_woocommerce_output_end', 1000 );
	/**
	 * Mark end of inline classes inside WooCommerce output (used in AJAX)
	 * 
	 * @hooked woocommerce_after_main_content, 1000
	 */
	function trx_addons_widget_woocommerce_search_woocommerce_output_end() {
		trx_addons_add_inline_css( '#woocommerce_output_end{}' );
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_add_checkbox_use_as_filter_to_attribute' ) ) {
	add_action( 'woocommerce_after_add_attribute_fields', 'trx_addons_widget_woocommerce_search_add_checkbox_use_as_filter_to_attribute' );
	add_action( 'woocommerce_after_edit_attribute_fields', 'trx_addons_widget_woocommerce_search_add_checkbox_use_as_filter_to_attribute' );
	/**
	 * Add checkbox 'Use as a filter' to the WooCommerce attribute edit form
	 * 
	 * @hooked woocommerce_after_add_attribute_fields, 10
	 * @hooked woocommerce_after_edit_attribute_fields, 10
	 */
	function trx_addons_widget_woocommerce_search_add_checkbox_use_as_filter_to_attribute() {
		if ( ! apply_filters( 'trx_addons_filter_woocommerce_add_use_as_filter_to_attribute', true ) ) {
			return;
		}
		$att_filter = true;
		$edit = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
		if ( $edit > 0 ) {
			$att_name = trx_addons_woocommerce_get_attribute_by_id( $edit, 'attribute_name' );
			if ( ! empty( $att_name ) ) {
				$att_filter = (int)trx_addons_woocommerce_get_attributes_data( $att_name, 'attribute_filter', true ) > 0;
			}
		}
		?>
		<tr class="form-field form-required">
			<th scope="row" valign="top">
				<label for="attribute_filter"><?php esc_html_e( 'Use as a filter', 'trx_addons' ); ?></label>
			</th>
			<td>
				<input name="attribute_filter" id="attribute_filter" type="checkbox" value="1" <?php checked( $att_filter, true ); ?> />
				<p class="description"><?php esc_html_e( 'This attribute can be used to filter products in a category.', 'trx_addons' ); ?></p>
			</td>
		</tr>
		<?php
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_save_checkbox_use_as_filter_to_attribute' ) ) {
	add_action( 'woocommerce_attribute_added', 'trx_addons_widget_woocommerce_search_save_checkbox_use_as_filter_to_attribute', 10, 2 );
	add_action( 'woocommerce_attribute_updated', 'trx_addons_widget_woocommerce_search_save_checkbox_use_as_filter_to_attribute', 10, 3 );
	/**
	 * Save the state of the checkbox 'Use as a filter' to the WooCommerce attribute meta
	 * 
	 * @hooked woocommerce_attribute_added, 10
	 * @hooked woocommerce_attribute_updated, 10
	 * 
	 * @param int $id  Attribute ID
	 * @param array $data  Attribute data
	 * @param string $old_slug  Old attribute slug
	 */
	function trx_addons_widget_woocommerce_search_save_checkbox_use_as_filter_to_attribute( $id, $data, $old_slug = '' ) {
		if ( ! apply_filters( 'trx_addons_filter_woocommerce_add_use_as_filter_to_attribute', true ) ) {
			return;
		}
		if ( $id > 0
			&& trx_addons_check_url( 'edit.php' )
			&& trx_addons_get_value_gp( 'post_type' ) == 'product'
			&& trx_addons_get_value_gp( 'page' ) == 'product_attributes'
			&& current_user_can( 'manage_product_terms' )
		) {
			if ( current_action() == 'woocommerce_attribute_added' ) {
				check_admin_referer( 'woocommerce-add-new_attribute' );
			} else {
				check_admin_referer( 'woocommerce-save-attribute_' . $id );
			}
			$att_name = trx_addons_woocommerce_get_attribute_by_id( $id, 'attribute_name' );
			if ( ! empty( $att_name ) ) {
				trx_addons_woocommerce_set_attributes_data(
					isset( $_POST['attribute_filter'] ) ? (int)$_POST['attribute_filter'] : 0,
					$att_name,
					'attribute_filter'
				);
			}
		}
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_is_attribute_used_as_filter' ) ) {
	/**
	 * Check whether a product attribute is allowed to be used as a filter in the
	 * WooCommerce Search widget.
	 *
	 * The state is stored per attribute in the 'attribute_filter' field of the
	 * 'woocommerce_attributes_data' option (see the "Use as a filter" checkbox added
	 * to the WooCommerce attribute edit form). Attributes without a saved value are
	 * treated as enabled, for backward compatibility with installs made before this
	 * checkbox existed.
	 *
	 * Only product attribute taxonomies (pa_*) carry this flag - all other filters
	 * (s, product_cat, product_tag, min_price, max_price, rating, etc.) are never
	 * gated and always return true.
	 *
	 * @param string $tax_name  Filter/taxonomy name (e.g. 'pa_color').
	 *
	 * @return bool  True if the attribute (or non-attribute filter) may be used as a filter.
	 */
	function trx_addons_widget_woocommerce_search_is_attribute_used_as_filter( $tax_name ) {
		if ( ! is_string( $tax_name ) || strpos( $tax_name, 'pa_' ) !== 0 ) {
			return true;
		}
		return (int) trx_addons_woocommerce_get_attributes_data( $tax_name, 'attribute_filter', true ) > 0;
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_add_attributes_to_filters' ) ) {
	/**
	 * Add attributes from the current category to the filters list
	 *
	 * @param array $fields  Filters list
	 * @param array $atts_counts  Attributes counts - an array of objects with 'taxonomy' and 'count' properties
	 * 
	 * @return array  Filters list
	 */
	function trx_addons_widget_woocommerce_search_add_attributes_to_filters( $fields, $atts_counts ) {
		$insert_point = 0;
		if ( is_array( $fields ) ) {
			foreach( $fields as $k => $v ) {
				if ( $v['filter'] == 'product_cat' ) {
					$insert_point = $k;
				} else if ( substr( $v['filter'], 0, 3 ) == 'pa_' ) {
					$insert_point = $k;
					break;
				}
			}
		}
		$processed = array();
		if ( is_array( $atts_counts ) ) {
			foreach( $atts_counts as $att ) {
				if ( ! empty( $att->taxonomy ) && ! isset( $processed[ $att->taxonomy ] ) && substr( $att->taxonomy, 0, 3 ) == 'pa_' ) {
					$processed[ $att->taxonomy ] = true;
					if ( trx_addons_widget_woocommerce_search_is_attribute_used_as_filter( $att->taxonomy ) && is_array( $fields ) ) {
						$found = false;
						foreach( $fields as $k => $v ) {
							if ( $v['filter'] == $att->taxonomy ) {
								$found = true;
							}
						}
						if ( ! $found ) {
							$tax_obj = get_taxonomy( $att->taxonomy );
							trx_addons_array_insert_after( $fields, $insert_point++, array( array(
								'text' => $tax_obj->labels->singular_name,
								'filter' => $att->taxonomy
							) ) );
						}
					}
				}
			}
		}
		return $fields;
	}
}


/**
 * Widget: WooCommerce Search
 */
class trx_addons_widget_woocommerce_search extends TRX_Addons_Widget {

	protected $search_fields = TRX_ADDONS_WOOCOMMERCE_SEARCH_FIELDS;

	/**
	 * Widget's constructor
	 * 
	 * @trigger trx_addons_filter_widget_woocommerce_filters_total
	 */
	function __construct() {
		$widget_ops = array('classname' => 'widget_woocommerce_search', 'description' => esc_html__('Advanced search form for products', 'trx_addons'));
		parent::__construct( 'trx_addons_widget_woocommerce_search', esc_html__('ThemeREX Product Filters', 'trx_addons'), $widget_ops );
		$this->search_fields = apply_filters( 'trx_addons_filter_widget_woocommerce_filters_total', $this->search_fields );
	}

	/**
	 * Display widget
	 * 
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget($args, $instance) {

		$style = isset($instance['style']) ? $instance['style'] : 'default';
		$type  = isset($instance['type']) ? $instance['type'] : 'inline';
		$apply = isset($instance['apply']) ? $instance['apply'] : 0;
		$ajax  = isset($instance['ajax']) ? $instance['ajax'] : 0;
		$force_checkboxes = isset($instance['force_checkboxes']) ? $instance['force_checkboxes'] : 0;
		$show_counters = isset($instance['show_counters']) ? $instance['show_counters'] : 1;
		$show_selected = isset($instance['show_selected']) ? $instance['show_selected'] : 1;
		$expanded = isset($instance['expanded']) ? (int) $instance['expanded'] : 0;
		$autofilters = isset($instance['autofilters']) ? $instance['autofilters'] : 0;
		
		// Hide widget on the single product, cart, checkout and user's account pages
		if ( apply_filters( 'trx_addons_filter_woocommerce_search',
							( $type!='inline' && ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_product_taxonomy() )
							||
							( $type=='inline' && ( is_product() || is_cart() || is_checkout() || is_account_page() ) )
						) 
		) {
			return;
		}

		$title = apply_filters('widget_title', isset($instance['title']) ? $instance['title'] : '', $instance, $this->id_base);
		if (!isset($instance['fields'])) {
			$fields = array();
			for ($i=1; $i<=$this->search_fields; $i++) {
				$fields[] = array(
					'text' => isset($instance["field{$i}_text"]) ? $instance["field{$i}_text"] : '',
					'filter' => isset($instance["field{$i}_filter"]) ? $instance["field{$i}_filter"] : ''
				);
			}
		} else {
			$fields = $instance['fields'];
		}

		$last_text = isset($instance['last_text']) ? $instance['last_text'] : '';
		$button_text = !empty($instance['button_text']) ? $instance['button_text'] : '';

		if ( $type == 'filter' ) {
			wp_enqueue_script('jquery-ui-slider', false, array( 'jquery', 'jquery-ui-core' ), null, true );
		}

		trx_addons_get_template_part(array(
										TRX_ADDONS_PLUGIN_API . 'woocommerce/tpl.widget.woocommerce_search_type_' . trx_addons_esc( trx_addons_sanitize_file_name( $type ) ) . '.php',
										TRX_ADDONS_PLUGIN_API . 'woocommerce/tpl.widget.woocommerce_search_type_form.php'
										),
									'trx_addons_args_widget_woocommerce_search',
									apply_filters(
										'trx_addons_filter_widget_args',
										array_merge( $args, compact( 'title', 'type', 'apply', 'ajax', 'force_checkboxes', 'show_selected',
																	 'show_counters', 'expanded', 'style', 'autofilters', 'fields',
																	 'last_text', 'button_text'
																	) ),
										$instance,
										'trx_addons_widget_woocommerce_search'
										)
								);
	}

	/**
	 * Update widget options
	 * 
	 * @trigger trx_addons_filter_widget_args_update
	 * 
	 * @param array $new_instance  New options
	 * @param array $instance      Old options
	 */
	function update( $new_instance, $instance ) {
		$instance = array_merge($instance, $new_instance);
		$instance['apply'] = isset( $new_instance['apply'] ) && (int)$new_instance['apply'] > 0 ? 1 : 0;
		$instance['ajax'] = isset( $new_instance['ajax'] ) && (int)$new_instance['ajax'] > 0 ? 1 : 0;
		$instance['force_checkboxes'] = isset( $new_instance['force_checkboxes'] ) && (int)$new_instance['force_checkboxes'] > 0 ? 1 : 0;
		$instance['show_selected'] = isset( $new_instance['show_selected'] ) && (int)$new_instance['show_selected'] > 0 ? 1 : 0;
		$instance['show_counters'] = isset( $new_instance['show_counters'] ) && (int)$new_instance['show_counters'] > 0 ? 1 : 0;
		$instance['expanded'] = (int)$instance['expanded'];
		$instance['autofilters'] = isset( $new_instance['autofilters'] ) && (int)$new_instance['autofilters'] > 0 ? 1 : 0;
		return apply_filters('trx_addons_filter_widget_args_update', $instance, $new_instance, 'trx_addons_widget_woocommerce_search');
	}

	/**
	 * Display widget form
	 * 
	 * @trigger trx_addons_filter_widget_args_default
	 * @trigger trx_addons_action_before_widget_fields
	 * @trigger trx_addons_action_after_widget_title
	 * @trigger trx_addons_action_after_widget_fields
	 * 
	 * @param array $instance  Widget options
	 */
	function form($instance) {

		// Set up some default widget settings
		$default = array(
			'title' => '',
			'type' => 'inline',
			'apply' => 1,
			'ajax' => 1,
			'force_checkboxes' => 0,
			'show_selected' => 1,
			'show_counters' => 1,
			'expanded' => 0,
			'autofilters' => 0,
			'last_text' => '',
			'button_text' => ''
		);
		for ($i=1; $i<=$this->search_fields; $i++) {
			$default["field{$i}_text"] = '';
			$default["field{$i}_filter"] = '';
		}
		$instance = wp_parse_args( (array) $instance, apply_filters('trx_addons_filter_widget_args_default', $default, 'trx_addons_widget_woocommerce_search')
		);
		
		do_action('trx_addons_action_before_widget_fields', $instance, 'trx_addons_widget_woocommerce_search', $this);
		
		$this->show_field(array('name' => 'title',
								'title' => __('Widget title:', 'trx_addons'),
								'value' => $instance['title'],
								'type' => 'text'));
		
		do_action('trx_addons_action_after_widget_title', $instance, 'trx_addons_widget_woocommerce_search', $this);

		$this->show_field(array('name' => "type",
								'title' => __('Type', 'trx_addons'),
								'value' => $instance["type"],
								'options' => trx_addons_get_list_woocommerce_search_types(),
								'type' => 'select'));

		$this->show_field(array('name' => "ajax",
								'title' => __('Use AJAX to reload products', 'trx_addons'),
								'label' => __('Use AJAX', 'trx_addons'),
								'description' => __('Use AJAX to refresh the product list in the background instead of reloading the entire page.', 'trx_addons'),
								'dependency' => array(
									'type' => array('filter')
								),
								'value' => $instance["ajax"],
								'type' => 'checkbox'));

		$this->show_field(array('name' => "apply",
								'title' => __('Use "Apply" Button for Filtering', 'trx_addons'),
								'label' => __('Use "Apply" Button', 'trx_addons'),
								'description' => __('Select multiple filter values without the page reloading.', 'trx_addons'),
								'dependency' => array(
									'type' => array('filter')
								),
								'value' => $instance["apply"],
								'type' => 'checkbox'));

		$this->show_field(array('name' => "force_checkboxes",
								'title' => __('Simple view', 'trx_addons'),
								'label' => __('Simple fileds', 'trx_addons'),
								'description' => __('Display colors, images and buttons as checkboxes.', 'trx_addons'),
								'dependency' => array(
									'type' => array('filter')
								),
								'value' => $instance["force_checkboxes"],
								'type' => 'checkbox'));

		$this->show_field(array('name' => "show_counters",
								'title' => __('Show counters', 'trx_addons'),
								'label' => __('Show', 'trx_addons'),
								'description' => __('Show product counters after each item.', 'trx_addons'),
								'dependency' => array(
									'type' => array('filter')
								),
								'value' => $instance["show_counters"],
								'type' => 'checkbox'));

		$this->show_field(array('name' => "show_selected",
								'title' => __('Show selected items', 'trx_addons'),
								'label' => __('Show', 'trx_addons'),
								'description' => __('Show selected items counter and "Clear all" button.', 'trx_addons'),
								'dependency' => array(
									'type' => array('filter')
								),
								'value' => $instance["show_selected"],
								'type' => 'checkbox'));

		$this->show_field(array('name' => "expanded",
								'title' => __('Initial toggle state', 'trx_addons'),
								'description' => __('For sidebar placement ONLY!', 'trx_addons'),
								'value' => $instance["expanded"],
								'dependency' => array(
									'type' => array('filter')
								),
								'options' => trx_addons_get_list_woocommerce_search_expanded(),
								'type' => 'select'));

		$this->show_field(array('name' => "autofilters",
								'title' => __('Auto filters in categories', 'trx_addons'),
								'label' => __('Auto filters', 'trx_addons'),
								'description' => __('Use product attributes as filters for current category.', 'trx_addons'),
								'dependency' => array(
									'type' => array('filter')
								),
								'value' => $instance["autofilters"],
								'type' => 'checkbox'));

		for ( $i = 1; $i <= $this->search_fields; $i++ ) {
			$this->show_field(array('name' => "field{$i}_text",
									'title' => sprintf(__('Field %d text', 'trx_addons'), $i),
									'value' => $instance["field{$i}_text"],
									'type' => 'text'));
			$this->show_field(array('name' => "field{$i}_filter",
									'title' => sprintf(__('Field %d filter:', 'trx_addons'), $i),
									'value' => $instance["field{$i}_filter"],
									'options' => trx_addons_get_list_woocommerce_search_filters(),
									'type' => 'select'));
		}

		$this->show_field(array('name' => "last_text",
								'title' => __('Last text', 'trx_addons'),
								'value' => $instance["last_text"],
								'type' => 'text'));

		$this->show_field(array('name' => "button_text",
								'title' => __('Button text', 'trx_addons'),
								'value' => $instance["button_text"],
								'type' => 'text'));

		do_action('trx_addons_action_after_widget_fields', $instance, 'trx_addons_widget_woocommerce_search', $this);
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_query_params' ) ) {
	/**
	 * Parse query params from GET/POST and wp_query_parameters
	 *
	 * @param array $fields  Array of fields to parse values for
	 * @param boolean $return_id  Use a term ID instead of slug for taxonomy fields. Default: false
	 * 
	 * @return array  Array of parsed params
	 */
	function trx_addons_widget_woocommerce_search_query_params( $fields, $return_id = false ) {
		$params = array();
		$q_obj = get_queried_object();
		// Add both price - min and max
		$need_min = $need_max = 1;
		foreach ( $fields as $fld ) {
			if ( $fld['filter'] == 'min_price' ) {
				$need_min = 0;
			} else if ( $fld['filter'] == 'max_price' ) {
				$need_max = 0;
			}
		}
		if ( $need_min + $need_max == 1 ) {	// If present only one of couple fields
			if ( $need_min ) {
				$fields[] = array( 'filter' => 'min_price' );
			} else {
				$fields[] = array( 'filter' => 'max_price' );
			}
		}
		// Fill values
		foreach ( $fields as $fld ) {
			if ( trx_addons_is_off( $fld['filter'] ) ) {
				continue;
			}
			$tax_name = $fld['filter'];
			if ( $tax_name == 'product_cat' && is_tax( $tax_name ) ) {
				$params[ $tax_name ] = $return_id ? $q_obj->term_id : urldecode( $q_obj->slug );
			} else if ( ( $value = trx_addons_get_value_gp( $tax_name ) ) != '' ) {
				$params[ $tax_name ] = sanitize_text_field( $value );
			} else if ( ( $value = trx_addons_get_value_gp( trx_addons_woocommerce_get_filter_name_from_attribute( $tax_name ) ) ) != '' ) {
				$params[ $tax_name ] = sanitize_text_field( $value );
			} else if ( ( $value = trx_addons_get_value_gp( trx_addons_woocommerce_get_filter_name_from_attribute( $tax_name, true ) ) ) != '' ) {
				$params[ $tax_name ] = sanitize_text_field( $value );
			} else {
				$params[ $tax_name ] = '';
			}
		}
		return $params;
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_parse_title_with_counter' ) ) {
	/**
	 * Parse a title with counter
	 * 
	 * @trigger trx_addons_filter_parse_title_with_counter
	 *
	 * @param string $title  Title to parse in format "Title (123)"
	 * 
	 * @return array  Array of parsed params with keys 'title' and 'total'
	 */
	function trx_addons_widget_woocommerce_search_parse_title_with_counter( $title ) {
		$result = array(
			'title' => '',
			'total' => ''
		);
		if ( preg_match_all( '/(.*)\\([\\d]+\\)$/', $title, $matches ) ) {
			$result['title'] = $matches[1];
			$result['total'] = $matches[2];
		}
		return apply_filters( 'trx_addons_filter_parse_title_with_counter', $result, $title );
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_cache_version' ) ) {
	/**
	 * Compute the composite cache-version suffix used by the widget's object cache.
	 *
	 * Combines two independent transient versions:
	 *   - WC's 'product' version — auto-bumped by wc_delete_product_transients() on product
	 *     save/delete/stock changes, so we inherit WC's invalidation for free.
	 *   - Our 'trx_addons_woocommerce_search' version — bumped from the hooks registered below
	 *     to cover the events WC does not touch: term rename/create/delete in product
	 *     taxonomies, product-attribute taxonomy changes, and the attribute-lookup option.
	 *
	 * Memoized per request so repeat calls during one render avoid re-reading the transient.
	 *
	 * @return string  Version suffix, or 'nowc' when WooCommerce is not loaded.
	 */
	function trx_addons_widget_woocommerce_search_cache_version() {
		static $version = null;
		if ( null !== $version ) {
			return $version;
		}
		if ( ! class_exists( 'WC_Cache_Helper' ) ) {
			$version = 'nowc';
			return $version;
		}
		$version = WC_Cache_Helper::get_transient_version( 'product' )
			. '_'
			. WC_Cache_Helper::get_transient_version( 'trx_addons_woocommerce_search' );
		return $version;
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_is_bare_category' ) ) {
	/**
	 * Detect the "bare category" request state: a category page with no active facets,
	 * no price/rating filter, and no search query.
	 *
	 * That state is the only one whose cached results are safely shareable across all
	 * visitors of a category — outside of it, the result depends on the visitor's selected
	 * filters and is too volatile to merit a transient slot. Used by ..._cache_get/_set()
	 * to gate the wp_options-backed fallback that survives without a persistent object cache.
	 *
	 * Memoized per request — WC's main query is fixed for one render.
	 *
	 * @return bool  True iff the current main query is a bare product category page.
	 */
	function trx_addons_widget_woocommerce_search_is_bare_category() {
		static $is_bare = null;
		if ( null !== $is_bare ) {
			return $is_bare;
		}
		if ( ! class_exists( 'WC_Query' ) ) {
			$is_bare = false;
			return $is_bare;
		}

		// A non-empty search string makes the result session-specific.
		$search = WC_Query::get_main_search_query_sql();
		if ( ! empty( $search ) ) {
			$is_bare = false;
			return $is_bare;
		}

		// Every clause in the main tax query must be a single product_cat IN (...) — anything
		// else (pa_* attribute, product_visibility for rating filter, NOT IN, OR relation, etc.)
		// means a facet is active. The only exception is WC's baseline product_visibility NOT IN
		// clause (exclude-from-catalog and, optionally, outofstock): WC adds it to every catalog
		// page and it is identical for all visitors, so it does not make the state visitor-specific.
		$cat_clauses = 0;
		$tax_query   = WC_Query::get_main_tax_query();
		if ( is_array( $tax_query ) ) {
			foreach ( $tax_query as $k => $v ) {
				if ( $k === 'relation' ) {
					continue;
				}
				if ( ! is_array( $v ) || empty( $v['taxonomy'] ) ) {
					$is_bare = false;
					return $is_bare;
				}
				$operator = isset( $v['operator'] ) ? strtoupper( $v['operator'] ) : 'IN';
				// Baseline visibility exclusion — skip. A product_visibility IN clause is the
				// rating filter (a real facet) and still disqualifies below.
				if ( $v['taxonomy'] === 'product_visibility' && $operator === 'NOT IN' && empty( $v['rating_filter'] ) ) {
					continue;
				}
				if ( $v['taxonomy'] !== 'product_cat' ) {
					$is_bare = false;
					return $is_bare;
				}
				if ( $operator !== 'IN' ) {
					$is_bare = false;
					return $is_bare;
				}
				$cat_clauses++;
			}
		}

		// Any meta_query clause (price filter, rating filter on older WC, etc.) is a facet.
		// Exception: a site-wide "hide out of stock" clause on _stock_status is a global
		// catalog setting, identical for every visitor, so it does not disqualify.
		$meta_query = WC_Query::get_main_meta_query();
		if ( is_array( $meta_query ) ) {
			foreach ( $meta_query as $k => $v ) {
				if ( $k === 'relation' ) {
					continue;
				}
				if ( ! is_array( $v ) ) {
					continue;
				}
				if ( isset( $v['key'] ) && $v['key'] === '_stock_status' ) {
					continue;
				}
				$is_bare = false;
				return $is_bare;
			}
		}

		$is_bare = ( $cat_clauses === 1 );
		return $is_bare;
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_cache_get' ) ) {
	/**
	 * Read from the widget's cache layer.
	 *
	 * Lookup order:
	 *   1. wp_cache (when TRX_ADDONS_WOOCOMMERCE_SEARCH_USE_WP_OBJECT_CACHE is enabled and a
	 *      persistent backend is loaded). Otherwise behaves as request-scoped only.
	 *   2. wp_options-backed transient (when TRX_ADDONS_WOOCOMMERCE_SEARCH_USE_TRANSIENTS is on
	 *      and the request is in the bare-category state — see ..._is_bare_category()).
	 *
	 * Invalidation: each cached entry carries the current cache_version() stamp. A version
	 * mismatch on read returns a miss, so we never delete transients explicitly — the next
	 * write reuses the same slot and overwrites the stale data, keeping wp_options bounded.
	 *
	 * @param string $key  Raw cache key (pre-version).
	 *
	 * @return mixed  Cached value, or false on miss / when WC isn't loaded.
	 */
	function trx_addons_widget_woocommerce_search_cache_get( $key ) {
		if ( ! class_exists( 'WC_Cache_Helper' ) ) {
			return false;
		}
		$version = trx_addons_widget_woocommerce_search_cache_version();

		if ( TRX_ADDONS_WOOCOMMERCE_SEARCH_USE_WP_OBJECT_CACHE ) {
			$value = wp_cache_get( $key . '_' . $version, 'trx_addons_woocommerce_search' );
			if ( false !== $value ) {
				return $value;
			}
		}

		if ( TRX_ADDONS_WOOCOMMERCE_SEARCH_USE_TRANSIENTS
			&& trx_addons_widget_woocommerce_search_is_bare_category()
		) {
			$entry = get_transient( 'trx_addons_wcs_' . md5( $key ) );
			// Embed the version inside the value so a single transient slot is reused across
			// invalidations (instead of orphaning rows in wp_options on every product save).
			if ( is_array( $entry ) && isset( $entry['v'], $entry['d'] ) && $entry['v'] === $version ) {
				return $entry['d'];
			}
		}

		return false;
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_cache_set' ) ) {
	/**
	 * Write to the widget's cache layer. See ..._cache_get() for the read order and invalidation
	 * model.
	 *
	 * @param string $key    Raw cache key (pre-version).
	 * @param mixed  $value  Value to cache. Must not be boolean false (conflicts with miss sentinel).
	 */
	function trx_addons_widget_woocommerce_search_cache_set( $key, $value ) {
		if ( ! class_exists( 'WC_Cache_Helper' ) ) {
			return;
		}
		$version = trx_addons_widget_woocommerce_search_cache_version();

		if ( TRX_ADDONS_WOOCOMMERCE_SEARCH_USE_WP_OBJECT_CACHE ) {
			wp_cache_set( $key . '_' . $version, $value, 'trx_addons_woocommerce_search', DAY_IN_SECONDS );
		}

		if ( TRX_ADDONS_WOOCOMMERCE_SEARCH_USE_TRANSIENTS
			&& trx_addons_widget_woocommerce_search_is_bare_category()
		) {
			set_transient(
				'trx_addons_wcs_' . md5( $key ),
				array( 'v' => $version, 'd' => $value ),
				TRX_ADDONS_WOOCOMMERCE_SEARCH_TRANSIENT_TTL
			);
		}
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_invalidate_cache' ) ) {
	add_action( 'woocommerce_attribute_added',   'trx_addons_widget_woocommerce_search_invalidate_cache' );
	add_action( 'woocommerce_attribute_updated', 'trx_addons_widget_woocommerce_search_invalidate_cache' );
	add_action( 'woocommerce_attribute_deleted', 'trx_addons_widget_woocommerce_search_invalidate_cache' );
	add_action( 'update_option_woocommerce_attribute_lookup_enabled', 'trx_addons_widget_woocommerce_search_invalidate_cache' );
	/**
	 * Bump our private transient version so every cached entry is orphaned.
	 */
	function trx_addons_widget_woocommerce_search_invalidate_cache() {
		if ( class_exists( 'WC_Cache_Helper' ) ) {
			WC_Cache_Helper::get_transient_version( 'trx_addons_woocommerce_search', true );
		}
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_maybe_invalidate_on_term' ) ) {
	add_action( 'edited_term', 'trx_addons_widget_woocommerce_search_maybe_invalidate_on_term', 10, 3 );
	add_action( 'created_term', 'trx_addons_widget_woocommerce_search_maybe_invalidate_on_term', 10, 3 );
	add_action( 'delete_term', 'trx_addons_widget_woocommerce_search_maybe_invalidate_on_term', 10, 3 );
	/**
	 * Invalidate the widget cache when a term in a product-related taxonomy is created,
	 * renamed, or deleted — WC's 'product' transient version does not cover these events.
	 *
	 * @param int    $term_id  Term ID (unused).
	 * @param int    $tt_id    Term taxonomy ID (unused).
	 * @param string $taxonomy Taxonomy the term belongs to.
	 */
	function trx_addons_widget_woocommerce_search_maybe_invalidate_on_term( $term_id, $tt_id, $taxonomy ) {
		if ( ! is_string( $taxonomy ) ) {
			return;
		}
		if ( $taxonomy === 'product_cat' || $taxonomy === 'product_tag' || strpos( $taxonomy, 'pa_' ) === 0 ) {
			trx_addons_widget_woocommerce_search_invalidate_cache();
		}
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_get_filtered_price' ) ) {
	/**
	 * Get filtered price: min and max values for the current query
	 *
	 * @return object  stdClass with min_price and max_price properties (always — even when WC is off)
	 */
	function trx_addons_widget_woocommerce_search_get_filtered_price() {
		$default = (object) array(
			'min_price' => 0,
			'max_price' => 0,
		);

		if ( ! trx_addons_exists_woocommerce() ) {
			return $default;
		}

		global $wpdb;

		// Per-request memoization: main query + search are fixed for a page render.
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}

		$args       = WC()->query->get_main_query()->query_vars;
		$tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
		$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

		if ( ! is_post_type_archive( 'product' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
			// get_main_tax_query() returns an array of clauses — merge, don't nest.
			$tax_query = array_merge( $tax_query, WC()->query->get_main_tax_query() );
		}

		// Drop current price/rating filter from meta_query so the returned range spans the full
		// result set independent of those facets. (rating_filter lives in tax_query and stays.)
		foreach ( $meta_query as $key => $query ) {
			if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
				unset( $meta_query[ $key ] );
			}
		}

		$search = WC_Query::get_main_search_query_sql();

		// Cross-request cache lookup — key on the exact inputs that shape the SQL.
		$wp_cache_key = 'price_' . md5( wp_json_encode( array( $tax_query, $meta_query, $search ) ) );
		$cached       = trx_addons_widget_woocommerce_search_cache_get( $wp_cache_key );
		if ( false !== $cached ) {
			$cache = $cached;
			return $cache;
		}

		$meta_query_obj = new WP_Meta_Query( $meta_query );
		$tax_query_obj  = new WP_Tax_Query( $tax_query );

		$meta_query_sql   = $meta_query_obj->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql    = $tax_query_obj->get_sql( $wpdb->posts, 'ID' );
		$search_query_sql = $search ? ' AND ' . $search : '';

		$post_types = array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) );

		$sql = "SELECT MIN( min_price ) AS min_price, MAX( max_price ) AS max_price"
			. " FROM {$wpdb->wc_product_meta_lookup}"
			. " WHERE product_id IN ("
			. "  SELECT ID FROM {$wpdb->posts}"
			. "  {$tax_query_sql['join']} {$meta_query_sql['join']}"
			. "  WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", $post_types ) . "')"
			. "    AND {$wpdb->posts}.post_status = 'publish'"
			. "    {$tax_query_sql['where']} {$meta_query_sql['where']}"
			. "    {$search_query_sql}"
			. " )";

		$sql = apply_filters( 'woocommerce_price_filter_sql', $sql, $meta_query_sql, $tax_query_sql );
		$row = $wpdb->get_row( $sql ); // WPCS: unprepared SQL ok.

		// $wpdb->get_row returns NULL on no rows / error. Also coerce NULL min/max (empty result set).
		if ( ! is_object( $row ) || null === $row->min_price ) {
			$cache = $default;
		} else {
			$cache = $row;
		}

		trx_addons_widget_woocommerce_search_cache_set( $wp_cache_key, $cache );

		return $cache;
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_get_filtered_product_counts_by_ratings' ) ) {
	/**
	 * Get filtered product counts for all rating levels (1..5) in a single query.
	 * Result is memoized for the current request.
	 *
	 * @return array  Map [ rating => count ] for ratings 1..5. Missing ratings default to 0.
	 */
	function trx_addons_widget_woocommerce_search_get_filtered_product_counts_by_ratings() {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}

		$cache = array( 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0 );

		if ( ! trx_addons_exists_woocommerce() ) {
			return $cache;
		}

		global $wpdb;

		$tax_query  = WC_Query::get_main_tax_query();
		$meta_query = WC_Query::get_main_meta_query();

		// Drop the current rating filter from the base query — we compute counts for every
		// rating independently. No `break`: remove all rating_filter entries, not just the first.
		foreach ( $tax_query as $key => $query ) {
			if ( ! empty( $query['rating_filter'] ) ) {
				unset( $tax_query[ $key ] );
			}
		}

		$search = WC_Query::get_main_search_query_sql();

		// Cross-request cache lookup — key on the normalized inputs (rating filter already removed).
		// Prefix bumped to 'ratings_pml_' so any entries from the previous (term_relationships-based)
		// implementation are orphaned: same shape, but the underlying query now uses different
		// rounding/edge-case semantics.
		$wp_cache_key = 'ratings_pml_' . md5( wp_json_encode( array( $tax_query, $meta_query, $search ) ) );
		$cached       = trx_addons_widget_woocommerce_search_cache_get( $wp_cache_key );
		if ( false !== $cached ) {
			$cache = $cached;
			return $cache;
		}

		// Bail out gracefully if WC's product meta lookup table is unavailable (very old WC).
		if ( empty( $wpdb->wc_product_meta_lookup ) ) {
			trx_addons_widget_woocommerce_search_cache_set( $wp_cache_key, $cache );
			return $cache;
		}

		$meta_query_obj = new WP_Meta_Query( $meta_query );
		$tax_query_obj  = new WP_Tax_Query( $tax_query );
		$meta_query_sql = $meta_query_obj->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query_obj->get_sql( $wpdb->posts, 'ID' );

		$search_sql = $search ? ' AND ' . $search : '';

		// Single aggregated query against wc_product_meta_lookup.average_rating (one row per
		// product with a dedicated index) instead of joining term_relationships on five
		// rated-N term_taxonomy_ids — a major win on large catalogs.
		//
		// ROUND() reproduces WC_Product_Data_Store_CPT::update_visibility_terms(), which assigns
		// the rated-N term where N = round( average_rating, 0 ). Bucket N therefore counts
		// products whose visibility carries 'rated-N' — matching the legacy implementation.
		$sql = "SELECT ROUND( pml.average_rating ) AS bucket,"
			. " COUNT( DISTINCT {$wpdb->posts}.ID ) AS cnt"
			. " FROM {$wpdb->posts}"
			. " INNER JOIN {$wpdb->wc_product_meta_lookup} pml ON {$wpdb->posts}.ID = pml.product_id"
			. " {$tax_query_sql['join']} {$meta_query_sql['join']}"
			. " WHERE {$wpdb->posts}.post_type = 'product'"
			. " AND {$wpdb->posts}.post_status = 'publish'"
			. " AND pml.average_rating > 0"
			. " {$tax_query_sql['where']} {$meta_query_sql['where']}"
			. $search_sql
			. " GROUP BY ROUND( pml.average_rating )";

		$rows = $wpdb->get_results( $sql ); // WPCS: unprepared SQL ok.

		foreach ( (array) $rows as $row ) {
			$bucket = (int) $row->bucket;
			if ( $bucket >= 1 && $bucket <= 5 ) {
				$cache[ $bucket ] = (int) $row->cnt;
			}
		}

		trx_addons_widget_woocommerce_search_cache_set( $wp_cache_key, $cache );

		return $cache;
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_get_filtered_product_count_by_rating' ) ) {
	/**
	 * Get filtered product count by rating
	 *
	 * @param int $rating  Rating to filter (1..5)
	 *
	 * @return int  Count of products
	 */
	function trx_addons_widget_woocommerce_search_get_filtered_product_count_by_rating( $rating ) {
		$rating = absint( $rating );
		if ( $rating < 1 || $rating > 5 ) {
			return 0;
		}
		$counts = trx_addons_widget_woocommerce_search_get_filtered_product_counts_by_ratings();
		return isset( $counts[ $rating ] ) ? (int) $counts[ $rating ] : 0;
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_get_filtered_terms_in_category' ) ) {
	/**
	 * Get filtered term counts for the taxonomies displayed by the filter widget.
	 *
	 * Counting is restricted to a whitelist (default: 'product_tag' plus all registered
	 * product attribute taxonomies). The caller never reads counts for product_visibility,
	 * product_type, language, etc. - including them in the GROUP BY produces tens of thousands
	 * of throwaway rows on a large catalog and is the dominant cost on big stores.
	 *
	 * Filters tt.taxonomy via a sargable IN (...) list so the index on term_taxonomy.taxonomy
	 * can be used (the previous LEFT(tt.taxonomy, 3) != 'pa_' predicate disabled the index).
	 *
	 * @param array|null $taxonomies   Whitelist of taxonomies to count. Pass NULL to use the
	 *                                 default; pass an array to override (e.g. for callers that
	 *                                 need product_cat counts as well).
	 * @param bool       $with_counts  If true (default), the SELECT computes COUNT(DISTINCT posts.ID)
	 *                                 per (taxonomy, term). If false, COUNT is replaced with a
	 *                                 constant 0 — the caller does not need per-term counts and
	 *                                 we skip the dominant cost on large catalogs while keeping
	 *                                 the same row shape, so the template can render terms unchanged.
	 *
	 * @return array  List of stdClass with: count, term_id, slug, name, taxonomy.
	 */
	function trx_addons_widget_woocommerce_search_get_filtered_terms_in_category( $taxonomies = null, $with_counts = true ) {
		if ( ! trx_addons_exists_woocommerce() ) {
			return array();
		}

		global $wpdb;

		// Resolve the taxonomy whitelist. Default = the only taxonomies the widget consumes.
		if ( null === $taxonomies ) {
			$default_taxonomies = array( 'product_tag' );
			if ( function_exists( 'wc_get_attribute_taxonomy_names' ) ) {
				// Only count attributes that are enabled as filters ("Use as a filter" checkbox).
				// Attributes disabled as filters are never rendered, so including them in the
				// GROUP BY / IN (...) here would scan and aggregate rows we always discard - the
				// dominant cost on shops with dozens of attribute taxonomies.
				foreach ( (array) wc_get_attribute_taxonomy_names() as $tax_name ) {
					if ( trx_addons_widget_woocommerce_search_is_attribute_used_as_filter( $tax_name ) ) {
						$default_taxonomies[] = $tax_name;
					}
				}
			}
			$taxonomies = apply_filters( 'trx_addons_filter_widget_woocommerce_search_count_taxonomies', $default_taxonomies );
		}
		$taxonomies = array_values( array_filter( array_unique( (array) $taxonomies ) ) );
		if ( empty( $taxonomies ) ) {
			return array();
		}

		// Per-request memoization: the main tax query, search, whitelist, and count flag are fixed for a render.
		static $cache = array();
		$tax_query  = WC_Query::get_main_tax_query();
		$search_sql = WC_Query::get_main_search_query_sql();
		$cache_key  = md5( wp_json_encode( $tax_query ) . '|' . $search_sql . '|' . implode( ',', $taxonomies ) . '|c=' . ( $with_counts ? 1 : 0 ) );
		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		// Cross-request cache lookup. The 'nocount_' prefix segregates the no-count variant —
		// same key shape but the rows carry count=0 placeholders, so they must not collide.
		$wp_cache_key = ( $with_counts ? 'terms_' : 'terms_nocount_' ) . $cache_key;
		$cached       = trx_addons_widget_woocommerce_search_cache_get( $wp_cache_key );
		if ( false !== $cached ) {
			$cache[ $cache_key ] = $cached;
			return $cached;
		}

		// Split the whitelist into attribute (pa_*) and non-attribute taxonomies — they take
		// different code paths when the WC attribute lookup table is enabled.
		$attr_taxonomies     = array();
		$non_attr_taxonomies = array();
		foreach ( $taxonomies as $tx ) {
			if ( strpos( $tx, 'pa_' ) === 0 ) {
				$attr_taxonomies[] = $tx;
			} else {
				$non_attr_taxonomies[] = $tx;
			}
		}

		// Build JOINs/WHERE that filter POSTS by non-attribute taxonomies from the main tax query.
		// Each clause is expressed as a JOIN (semijoin for IN, LEFT JOIN + IS NULL for NOT IN)
		// instead of the previous `ID IN (SELECT ... FROM term_relationships)` correlated
		// subqueries, which on big catalogs were not always rewritten to semi-joins by MySQL.
		// Attribute taxonomies (pa_*) are skipped on purpose so that attribute counts reflect
		// the rest of the filter state, not the attribute's own facet.
		$post_join  = '';
		$post_where = '';
		$join_idx   = 0;
		if ( is_array( $tax_query ) ) {
			foreach ( $tax_query as $k => $v ) {
				if ( $k === 'relation' || ! is_array( $v ) || empty( $v['taxonomy'] ) ) {
					continue;
				}
				if ( strpos( $v['taxonomy'], 'pa_' ) === 0 ) {
					continue;
				}

				$raw_terms = isset( $v['terms'] ) ? (array) $v['terms'] : array();
				$field     = isset( $v['field'] ) ? $v['field'] : 'term_id';
				$operator  = isset( $v['operator'] ) ? strtoupper( $v['operator'] ) : 'IN';

				// Resolve raw values to term_taxonomy_ids (the FK stored in term_relationships).
				$ttids = array();
				foreach ( $raw_terms as $t ) {
					if ( $field === 'term_taxonomy_id' ) {
						$ttids[] = (int) $t;
						continue;
					}
					$term_obj = ( $field === 'slug' || $field === 'name' )
						? get_term_by( $field, $t, $v['taxonomy'] )
						: get_term( (int) $t, $v['taxonomy'] );
					if ( ! $term_obj || is_wp_error( $term_obj ) ) {
						continue;
					}
					$ttids[] = (int) $term_obj->term_taxonomy_id;

					if ( ! empty( $v['include_children'] ) ) {
						$children = get_term_children( (int) $term_obj->term_id, $v['taxonomy'] );
						if ( is_array( $children ) ) {
							foreach ( $children as $c ) {
								$child = get_term( (int) $c, $v['taxonomy'] );
								if ( $child && ! is_wp_error( $child ) ) {
									$ttids[] = (int) $child->term_taxonomy_id;
								}
							}
						}
					}
				}

				$ttids = array_filter( array_unique( $ttids ) );
				if ( empty( $ttids ) ) {
					continue;
				}

				// Whitelist operator — anything else coerced to IN to avoid SQL injection.
				if ( ! in_array( $operator, array( 'IN', 'NOT IN' ), true ) ) {
					$operator = 'IN';
				}

				$alias     = 'trx_post_tr_' . $join_idx++;
				$ttid_list = implode( ',', $ttids );

				if ( $operator === 'NOT IN' ) {
					$post_join  .= " LEFT JOIN {$wpdb->term_relationships} {$alias}"
						. " ON {$alias}.object_id = {$wpdb->posts}.ID"
						. " AND {$alias}.term_taxonomy_id IN ({$ttid_list})";
					$post_where .= " AND {$alias}.object_id IS NULL";
				} else {
					$post_join  .= " INNER JOIN {$wpdb->term_relationships} {$alias}"
						. " ON {$alias}.object_id = {$wpdb->posts}.ID"
						. " AND {$alias}.term_taxonomy_id IN ({$ttid_list})";
				}
			}
		}

		$search_where = $search_sql ? ' AND ' . $search_sql : '';

		// Derived table that yields the qualifying product set, deduplicated. Wrapping the
		// post selection in DISTINCT before joining the term tables collapses row duplication
		// coming from $post_join (multi-facet INNER JOINs). Without this wrap, the previous
		// COUNT(DISTINCT posts.ID) had to dedupe within every aggregation group — the dominant
		// cost on large catalogs. After the wrap (q.ID, term_taxonomy_id) is unique by
		// term_relationships' PK, so COUNT(*) is exact for the tr-based paths.
		$qualifying = "SELECT DISTINCT {$wpdb->posts}.ID"
			. " FROM {$wpdb->posts}"
			. $post_join
			. " WHERE {$wpdb->posts}.post_type = 'product'"
			. " AND {$wpdb->posts}.post_status = 'publish'"
			. $post_where
			. $search_where;

		// Path-specific count selects:
		//   - tr path (term_relationships): (q.ID, tt_id) is unique → COUNT(*) is exact.
		//   - pal path (wc_product_attributes_lookup): (product_or_parent_id, taxonomy, term_id)
		//     is NOT unique because multiple variations of the same parent can share the same
		//     attribute term value. COUNT(DISTINCT pal.product_or_parent_id) collapses those.
		// When $with_counts is false the SELECT emits a 0 placeholder and the engine skips the
		// distinct-collection bookkeeping entirely.
		$count_select_tr  = $with_counts ? "COUNT(*) AS count," : "0 AS count,";
		$count_select_pal = $with_counts ? "COUNT(DISTINCT pal.product_or_parent_id) AS count," : "0 AS count,";

		// WC's attribute lookup table carries one row per (product, attribute term) with
		// dedicated indexes. It is populated only when the option below is 'yes'.
		$use_attr_lookup = 'yes' === get_option( 'woocommerce_attribute_lookup_enabled' )
			&& ! empty( $wpdb->wc_product_attributes_lookup );

		$terms = array();

		if ( $use_attr_lookup ) {
			// Two queries: non-attribute taxonomies via term_relationships, attribute
			// taxonomies via the lookup table.
			if ( ! empty( $non_attr_taxonomies ) ) {
				$non_attr_in = "'" . implode( "','", array_map( 'esc_sql', $non_attr_taxonomies ) ) . "'";

				$non_attr_sql = "SELECT {$count_select_tr}"
					. " t.term_id, t.slug, t.name, tt.taxonomy"
					. " FROM ( {$qualifying} ) q"
					. " INNER JOIN {$wpdb->term_relationships} tr ON q.ID = tr.object_id"
					. " INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id"
					. " INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id"
					. " WHERE tt.taxonomy IN ({$non_attr_in})"
					. " GROUP BY tt.term_taxonomy_id";

				$terms = array_merge( $terms, (array) $wpdb->get_results( $non_attr_sql ) );
			}

			if ( ! empty( $attr_taxonomies ) ) {
				$attr_in = "'" . implode( "','", array_map( 'esc_sql', $attr_taxonomies ) ) . "'";

				// product_or_parent_id maps variations up to their parent product.
				$attr_sql = "SELECT {$count_select_pal}"
					. " t.term_id, t.slug, t.name, pal.taxonomy"
					. " FROM ( {$qualifying} ) q"
					. " INNER JOIN {$wpdb->wc_product_attributes_lookup} pal ON pal.product_or_parent_id = q.ID"
					. " INNER JOIN {$wpdb->terms} t ON pal.term_id = t.term_id"
					. " WHERE pal.taxonomy IN ({$attr_in})"
					. " GROUP BY pal.taxonomy, pal.term_id";

				$terms = array_merge( $terms, (array) $wpdb->get_results( $attr_sql ) );
			}
		} else {
			// Single query covering all whitelisted taxonomies via term_relationships.
			$all_in = "'" . implode( "','", array_map( 'esc_sql', $taxonomies ) ) . "'";

			$sql = "SELECT {$count_select_tr}"
				. " t.term_id, t.slug, t.name, tt.taxonomy"
				. " FROM ( {$qualifying} ) q"
				. " INNER JOIN {$wpdb->term_relationships} tr ON q.ID = tr.object_id"
				. " INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id"
				. " INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id"
				. " WHERE tt.taxonomy IN ({$all_in})"
				. " GROUP BY tt.term_taxonomy_id";

			$terms = (array) $wpdb->get_results( $sql );
		}

		$cache[ $cache_key ] = $terms;
		trx_addons_widget_woocommerce_search_cache_set( $wp_cache_key, $terms );
		return $terms;
	}
}

if ( ! function_exists( 'trx_addons_widget_woocommerce_search_localize_script' ) ) {
	add_filter( "trx_addons_filter_localize_script", 'trx_addons_widget_woocommerce_search_localize_script' );
	/**
	 * Add Woocommerce Search widget specific variables to the localized script
	 *
	 * @param array $vars Localized script array
	 * 
	 * @return array    Modified array
	 */
	function trx_addons_widget_woocommerce_search_localize_script( $vars ) {
		$vars['msg_no_products_found'] = addslashes( esc_html__("No products found! Please, change query parameters and try again.", 'trx_addons') );
		return $vars;
	}
}


// Add shortcodes
//----------------------------------------------------------------------------

require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'woocommerce/widget.woocommerce_search-sc.php';

// Add shortcodes to Elementor
if ( trx_addons_exists_woocommerce() && trx_addons_exists_elementor() && function_exists('trx_addons_elm_init') ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'woocommerce/widget.woocommerce_search-sc-elementor.php';
}

// Add shortcodes to VC
if ( trx_addons_exists_woocommerce() && trx_addons_exists_vc() && function_exists( 'trx_addons_vc_add_id_param' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'woocommerce/widget.woocommerce_search-sc-vc.php';
}
