<?php
/**
 * Plugin Name: LatePoint Addon - Payments Woocommerce
 * Plugin URI:  https://latepoint.com/
 * Description: LatePoint addon for payments via Woocommerce
 * Version:     1.4.1
 * Author:      LatePoint
 * Author URI:  https://latepoint.com/
 * Text Domain: latepoint-payments-woocommerce
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// If no LatePoint class exists - exit, because LatePoint plugin is required for this addon

if ( ! class_exists( 'LatePointPaymentsWoocommerce' ) ) :

	/**
	 * Main Addon Class.
	 *
	 */

	class LatePointPaymentsWoocommerce {

		/**
		 * Addon version.
		 *
		 */
		public $version = '1.4.1';
		public $db_version = '1.0.0';
		public $addon_name = 'latepoint-payments-woocommerce';

		public $processor_code = 'woocommerce';


		/**
		 * LatePoint Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->init_hooks();
		}

		/**
		 * Define LatePoint Constants.
		 */
		public function define_constants() {
		}


		public static function public_stylesheets() {
			return plugin_dir_url( __FILE__ ) . 'public/stylesheets/';
		}

		public static function public_javascripts() {
			return plugin_dir_url( __FILE__ ) . 'public/javascripts/';
		}

		public static function images_url() {
			return plugin_dir_url( __FILE__ ) . 'public/images/';
		}

		/**
		 * Define constant if not already set.
		 *
		 */
		public function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {

			// CONTROLLERS
			include_once( dirname( __FILE__ ) . '/lib/controllers/payments_woocommerce_controller.php' );

			// HELPERS
			include_once( dirname( __FILE__ ) . '/lib/helpers/payments_woocommerce_helper.php' );

			// MODELS

		}


		public function init_hooks() {
			add_action( 'latepoint_includes', [ $this, 'includes' ] );
			add_filter( 'latepoint_installed_addons', [ $this, 'register_addon' ] );

			add_filter( 'latepoint_payment_processors', [ $this, 'register_payment_processor' ] );
			add_action( 'latepoint_payment_processor_settings', [ $this, 'add_settings_fields' ], 10 );
			add_action( 'latepoint_step_payment__pay_content', [ $this, 'output_payment_step_contents' ], 10 );

			add_filter( 'latepoint_convert_charge_amount_to_requirements', [ $this, 'convert_charge_amount_to_requirements' ], 10, 2 );
			add_filter( 'latepoint_process_payment_for_order_intent', [ $this, 'process_payment_for_order_intent' ], 10, 2 );

			add_filter( 'latepoint_get_all_payment_times', [ $this, 'add_all_payment_methods_to_payment_times' ] );
			add_filter( 'latepoint_get_enabled_payment_times', [ $this, 'add_enabled_payment_methods_to_payment_times' ] );
			add_filter( 'latepoint_should_step_be_skipped', [ $this, 'keep_converted_cart_on_confirmation' ], 20, 5 );

			add_action( 'latepoint_wp_enqueue_scripts', [ $this, 'load_front_scripts_and_styles' ] );
			add_action( 'latepoint_admin_enqueue_scripts', [ $this, 'load_admin_scripts_and_styles' ] );
			add_action( 'latepoint_settings_updated', [ $this, 'process_payment_settings_update' ] );
			add_filter( 'latepoint_localized_vars_front', [ $this, 'localized_vars_for_front' ] );

			add_action( 'latepoint_transaction_edit_form_after', [ $this, 'add_woocommerce_order_link' ], 10, 2 );

			add_action( 'latepoint_order_payment__pay_content_after', [ $this, 'output_order_payment_pay_contents' ], 10 );
			add_filter( 'latepoint_process_payment_for_transaction_intent', [ $this, 'process_payment_for_transaction_intent' ], 10, 2 );
			add_filter( 'latepoint_transaction_intent_specs_charge_amount', [ $this, 'convert_transaction_intent_charge_amount_to_specs' ], 10, 2 );

			add_filter( 'latepoint_transaction_is_refund_available', [ $this, 'transaction_is_refund_available' ], 10, 2 );
			add_filter( 'latepoint_process_refund', 'OsPaymentsWoocommerceHelper::process_refund', 10, 3 );
            add_action('latepoint_settings_general_authentication_after', 'OsPaymentsWoocommerceHelper::add_info_after_general_authentication_settings');

			add_action( 'latepoint_model_save', 'OsPaymentsWoocommerceHelper::sync_product_with_woocommerce', 10, 1 );
            add_action('pre_get_posts', 'OsPaymentsWoocommerceHelper::hide_latepoint_products_from_shop');

			// WooCommerce-dependent hooks: register only if WooCommerce is active
            add_action( 'woocommerce_cart_calculate_fees', [$this, 'add_woocommerce_cart_fees' ] );
            add_action('woocommerce_before_calculate_totals', [$this, 'set_custom_cart_item_price'], 10, 1);
            add_filter( 'woocommerce_cart_get_total', [$this, 'add_woocommerce_cart_total'], 10, 1) ;
            add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'check_meta_and_redirect_checkout' ), 10, 2 );
            add_filter('woocommerce_add_to_cart_validation', [$this, 'prevent_mixed_cart_validation'], 10, 3);
			add_filter('woocommerce_is_sold_individually', [$this, 'allow_multiple_latepoint_products'], 10, 2);
			add_action( 'woocommerce_payment_complete', [ $this, 'handle_payment_complete' ], 10, 1 );
            add_action( 'woocommerce_order_status_changed', [ $this, 'handle_order_status_changed' ], 10, 4 );
            add_action( 'woocommerce_checkout_order_processed', [ $this, 'handle_checkout_order_processed' ], 10, 3 );
			add_action( 'wp', [ $this, 'ensure_checkout_customer_location' ] );


            // Classic and Store API
            add_action( 'woocommerce_checkout_create_order', [ $this, 'transfer_cart_item_data_to_order' ], 10, 2 );
            add_action( 'woocommerce_store_api_checkout_order_processed', [ $this, 'transfer_cart_item_data_to_order_store_api' ], 10, 2 );


            add_action('woocommerce_checkout_process', 'OsPaymentsWoocommerceHelper::validate_order_before_creation');
            add_action('woocommerce_store_api_checkout_update_order_from_request', 'OsPaymentsWoocommerceHelper::validate_order_before_creation_store_api', 10, 2);


            add_filter( 'woocommerce_checkout_fields', [ $this, 'prefill_checkout_fields' ] );

            add_filter( 'latepoint_cart_price_breakdown_rows', 'OsPaymentsWoocommerceHelper::add_woo_diff_to_cart_price_breakdown_rows', 20, 3 );
			add_filter( 'latepoint_order_price_breakdown_rows', 'OsPaymentsWoocommerceHelper::remove_empty_woo_diff_from_price_breakdown_rows', 20, 1 );
            add_action( 'latepoint_cart_calculate_prices', 'OsPaymentsWoocommerceHelper::apply_woo_diff_to_cart_calculations', 14, 1 ); // priority 14 because it should run after taxes and coupons

            add_filter('woocommerce_coupons_enabled', 'OsPaymentsWoocommerceHelper::disable_coupons', 10, 1);
            add_filter('pre_option_woocommerce_calc_taxes', 'OsPaymentsWoocommerceHelper::disable_tax_option');
            add_filter('woocommerce_calc_tax', 'OsPaymentsWoocommerceHelper::disable_tax_calc', 10, 1);
            add_filter('woocommerce_find_rates', 'OsPaymentsWoocommerceHelper::disable_tax_rates', 10, 2);


			/* Scripts & Styles for clean layout */
			add_filter( 'latepoint_clean_layout_js_files', [$this, 'add_scripts_to_clean_layout'], 10 );

			// addon specific filters

			add_action( 'init', array( $this, 'init' ), 0 );

			register_activation_hook( __FILE__, [ $this, 'on_activate' ] );
			register_deactivation_hook( __FILE__, [ $this, 'on_deactivate' ] );
		}

		public function add_woocommerce_order_link( OsTransactionModel $transaction, string $real_or_rand_id ) {
			if ( $transaction->processor == $this->processor_code ) {
				echo '<div class="os-woocommerce-order-link-wrapper">';
				echo '<a class="os-woocommerce-order-link" href="' . esc_url( OsPaymentsWoocommerceHelper::build_url_to_woocommerce_order( $transaction->token ) ) . '" target="_blank"><span>' . esc_html( __( 'Open WooCommerce Order', 'latepoint-payments-woocommerce' ) ) . '</span><i class="latepoint-icon latepoint-icon-external-link"></i></a>';
				echo '</div>';
			}
		}

		public function check_meta_and_redirect_checkout( $order_received_url, $wc_order ) {
			if ( ! $wc_order || OsPaymentsWoocommerceHelper::get_success_redirect_settings_value() == 'default' ) {
				return $order_received_url;
			}

			$order_intent_key = $wc_order->get_meta( '_latepoint_order_intent_key' );

			if ( ! empty( $order_intent_key ) ) {
				$order_received_url = add_query_arg( 'latepoint_order_intent_key', $order_intent_key, $order_received_url );
			}

			return $order_received_url;
		}

		public function transfer_cart_item_data_to_order( $wc_order, $data = null ) {
            OsPaymentsWoocommerceHelper::transfer_cart_item_data($wc_order);
		}

        public function transfer_cart_item_data_to_order_store_api( $wc_order, $request = null ) {
            OsPaymentsWoocommerceHelper::transfer_cart_item_data($wc_order);

            OsPaymentsWoocommerceHelper::create_new_order_for_bacs_and_cod($wc_order);
        }


		public function on_deactivate() {
		}


		public function prefill_checkout_fields( $fields ) {
			if (empty(WC()->cart)) {
				return $fields;
			}

			$order_intent_key       = OsPaymentsWoocommerceHelper::get_intent_key_from_lp_cart( '_latepoint_order_intent_key' );
			$transaction_intent_key = OsPaymentsWoocommerceHelper::get_intent_key_from_lp_cart( '_latepoint_transaction_intent_key' );


			if ( $order_intent_key ) {
				$intent_model = OsOrderIntentHelper::get_order_intent_by_intent_key( $order_intent_key );
			} elseif ( $transaction_intent_key ) {
				$intent_model = OsTransactionIntentHelper::get_transaction_intent_by_intent_key( $transaction_intent_key );
			} else {
				$intent_model = false;
			}

			if ( $intent_model ) {
				$customer = new OsCustomerModel( $intent_model->customer_id );
				if ( ! $customer->is_new_record() ) {
					$fields['billing']['billing_first_name']['default'] = $customer->first_name;
					$fields['billing']['billing_last_name']['default']  = $customer->last_name;
					$fields['billing']['billing_email']['default']      = $customer->email;
					$fields['billing']['billing_phone']['default']      = $customer->phone;
					$fields['order']['order_comments']['default']       = $customer->notes;
				}
			}

			return $fields;
		}

		public function ensure_checkout_customer_location() {
			if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) || ! WC()->customer || empty( WC()->cart ) ) {
				return;
			}

			$order_intent_key       = OsPaymentsWoocommerceHelper::get_intent_key_from_lp_cart( '_latepoint_order_intent_key' );
			$transaction_intent_key = OsPaymentsWoocommerceHelper::get_intent_key_from_lp_cart( '_latepoint_transaction_intent_key' );
			if ( ! $order_intent_key && ! $transaction_intent_key ) {
				return;
			}

			OsPaymentsWoocommerceHelper::ensure_valid_checkout_location( WC()->customer );
			$session_store = new WC_Customer_Data_Store_Session();
			$session_store->save_to_session( WC()->customer );
		}


		public function process_payment_settings_update( array $settings ) {
			if ( ! empty( $settings['enable_payment_processor_woocommerce'] ) && $settings['enable_payment_processor_woocommerce'] == 'on' ) {
                OsPaymentsWoocommerceHelper::sync_products();
			}
		}

		public function add_settings_fields( $processor_code ) {
			if ( $processor_code != $this->processor_code ) {
				return false;
			}
			?>
            <div class="sub-section-row">
                <div class="sub-section-label">
                    <h3><?php _e( 'General Settings', 'latepoint-payments-woocommerce' ); ?></h3>
                </div>
                <div class="sub-section-content">
                    <div class="latepoint-message latepoint-message-subtle"><?php _e( 'Make sure WooCommerce plugin is installed. On a checkout step of booking form, customer will be sent to WooCommerce checkout page to process the payment.', 'latepoint-payments-woocommerce' ); ?></div>
                    <div class="os-row">
                        <div class="os-col-6">
							<?php echo OsFormHelper::select_field( 'settings[woocommerce_success_redirect_to]', __( 'Confirmation', 'latepoint-payments-woocommerce' ), [ 'default'   => __( 'Default WooCommerce success page', 'latepoint-payments-woocommerce' ),
							                                                                                                                                              'latepoint' => __( 'Open LatePoint confirmation popup', 'latepoint-payments-woocommerce' )
							], OsPaymentsWoocommerceHelper::get_success_redirect_settings_value(), [ 'theme' => 'simple' ] ); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sub-section-row">
                <div class="sub-section-label">
                    <h3><?php _e( 'Products for Bookings', 'latepoint-payments-woocommerce' ); ?></h3>
                </div>
                <div class="sub-section-content">
                    <div class="latepoint-message latepoint-message-subtle"><?php _e( 'LatePoint creates a products in WooCommerce that is used for bookings. When a customer tries to pay for their appointment - a checkout page is being generated with that products in cart, and the price is set to match the total booking price.', 'latepoint-payments-woocommerce' ); ?></div>
                    <div class="generic-product-info-wrapper">
						<?php echo OsPaymentsWoocommerceHelper::generic_product_status_html(); ?>
                    </div>
                </div>
            </div>
			<?php
		}


		public function add_all_payment_methods_to_payment_times( array $payment_times ): array {
			$payment_methods = $this->get_supported_payment_methods();
			foreach ( $payment_methods as $payment_method_code => $payment_method_info ) {
				$payment_times[ LATEPOINT_PAYMENT_TIME_NOW ][ $payment_method_code ][ $this->processor_code ] = $payment_method_info;
			}

			return $payment_times;
		}

		public function add_enabled_payment_methods_to_payment_times( array $payment_times ): array {
			if ( OsPaymentsHelper::is_payment_processor_enabled( $this->processor_code ) ) {
				$payment_times = $this->add_all_payment_methods_to_payment_times( $payment_times );
			}

			return $payment_times;
		}


		public function keep_converted_cart_on_confirmation( bool $skip, string $step_code, OsCartModel $cart ): bool {
			if ( ! empty( $cart->order_id ) && $step_code === 'verify' ) {
				return true;
			}

			return $skip;
		}


		public function load_front_scripts_and_styles() {
			if ( OsPaymentsHelper::is_payment_processor_enabled( $this->processor_code ) ) {
				wp_enqueue_script( 'latepoint-payments-woocommerce-front', $this->public_javascripts() . 'latepoint-payments-woocommerce-front.js', array(
					'jquery',
					'latepoint-main-front'
				), $this->version );
				wp_enqueue_script( 'latepoint-datepicker-guard', $this->public_javascripts() . 'latepoint-datepicker-guard.js', array(
					'jquery',
					'latepoint-main-front'
				), $this->version );
			}

		}


		public function load_admin_scripts_and_styles() {
			if ( OsPaymentsHelper::is_payment_processor_enabled( $this->processor_code ) ) {
				// Stylesheets
				wp_enqueue_style( 'latepoint-payments-woocommerce-admin', $this->public_stylesheets() . 'latepoint-payments-woocommerce-admin.css', false, $this->version );

				// Javascripts
			}
		}

		public function process_payment_for_order_intent( array $result, OsOrderIntentModel $order_intent ): array {
			if ( OsPaymentsHelper::should_processor_handle_payment_for_order_intent( $this->processor_code, $order_intent ) ) {
				$result = OsPaymentsWoocommerceHelper::process_payment( $result, $order_intent );
			}

			return $result;
		}


		public function process_payment_for_transaction_intent( array $result, OsTransactionIntentModel $transaction_intent ): array {
			if ( OsPaymentsHelper::should_processor_handle_payment_for_transaction_intent( $this->processor_code, $transaction_intent ) ) {
				$result = OsPaymentsWoocommerceHelper::process_payment( $result, $transaction_intent );
			}

			return $result;
		}


		public function convert_charge_amount_to_requirements( $charge_amount, OsCartModel $cart ) {
			if ( OsPaymentsHelper::should_processor_handle_payment_for_cart( $this->processor_code, $cart ) ) {
				$charge_amount = OsPaymentsWoocommerceHelper::convert_charge_amount( $charge_amount );
			}

			return $charge_amount;
		}

		public function convert_transaction_intent_charge_amount_to_specs( $charge_amount, OsTransactionIntentModel $transaction_intent ) {
			if ( OsPaymentsHelper::should_processor_handle_payment_for_transaction_intent( $this->processor_code, $transaction_intent ) ) {
				$charge_amount = OsPaymentsWoocommerceHelper::convert_charge_amount( $charge_amount );
			}

			return $charge_amount;
		}


		public function localized_vars_for_front( $localized_vars ) {
			$localized_vars['woocommerce_route_start_checkout']       = OsRouterHelper::build_route_name( 'payments_woocommerce', 'start_checkout' );
			$localized_vars['woocommerce_route_order_start_checkout'] = OsRouterHelper::build_route_name( 'payments_woocommerce', 'start_order_checkout' );

			return $localized_vars;
		}


		public function output_payment_step_contents( OsCartModel $cart ) {
			if ( ! OsPaymentsHelper::should_processor_handle_payment_for_cart( $this->processor_code, $cart ) ) {
				return;
			}
			echo '<div class="lp-payment-method-content" data-payment-method="woocommerce">';
			echo '<div class="lp-payment-method-content-i">';
			echo '<div class="woocommerce-payment-element">' . esc_html__( 'Redirecting to checkout. Please wait...', 'latepoint-payments-woocommerce' ) . '</div>';
			echo '</div>';
			echo '</div>';
		}

		public function output_order_payment_pay_contents( OsTransactionIntentModel $transaction_intent ) {
			if ( ! OsPaymentsHelper::should_processor_handle_payment_for_transaction_intent( $this->processor_code, $transaction_intent ) ) {
				return;
			}

			echo '<div class="lp-payment-method-content" data-payment-method="woocommerce">';
			echo '<div class="lp-payment-method-content-i">';
			echo '<div class="woocommerce-payment-element">' . esc_html__( 'Redirecting to checkout. Please wait...', 'latepoint-payments-woocommerce' ) . '</div>';
			echo '</div>';
			echo '</div>';
		}


		public function get_supported_payment_methods(): array {
			return [
				'woocommerce' => [
					'name'      => __( 'Woocommerce', 'latepoint-payments-woocommerce' ),
					'label'     => __( 'Woocommerce', 'latepoint-payments-woocommerce' ),
					'image_url' => LATEPOINT_IMAGES_URL . 'payment_cards.png',
				]
			];
		}


		public function register_payment_processor( array $payment_processors ): array {
			$payment_processors[ $this->processor_code ] = [
				'code'       => $this->processor_code,
				'name'       => __( 'Woocommerce', 'latepoint-payments-woocommerce' ),
				'front_name' => __( 'Woocommerce', 'latepoint-payments-woocommerce' ),
				'image_url'  => $this->images_url() . 'processor-logo.png'
			];

			return $payment_processors;
		}

		public function init() {
			// Set up localisation.
			$this->load_plugin_textdomain();
			$this->repair_order_intents_schema();
		}

		private function repair_order_intents_schema() {
			if ( ! defined( 'LATEPOINT_TABLE_ORDER_INTENTS' ) || get_option( 'latepoint_woocommerce_restrictions_data_schema_repaired' ) ) {
				return;
			}

			global $wpdb;
			$table_name = LATEPOINT_TABLE_ORDER_INTENTS;
			$column     = $wpdb->get_var( "SHOW COLUMNS FROM `{$table_name}` LIKE 'restrictions_data'" );

			if ( ! $column ) {
				$wpdb->query( "ALTER TABLE `{$table_name}` ADD `restrictions_data` text AFTER `cart_items_data`" );
				$column = $wpdb->get_var( "SHOW COLUMNS FROM `{$table_name}` LIKE 'restrictions_data'" );
			}

			if ( $column ) {
				update_option( 'latepoint_woocommerce_restrictions_data_schema_repaired', 1, false );
			}
		}

		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'latepoint-payments-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		public function on_activate() {
			do_action( 'latepoint_on_addon_activate', $this->addon_name, $this->version );
		}

		public function register_addon( $installed_addons ) {
			$installed_addons[] = [ 'name' => $this->addon_name, 'db_version' => $this->db_version, 'version' => $this->version ];

			return $installed_addons;
		}


		public function transaction_is_refund_available( $result, OsTransactionModel $transaction_model ): bool {
			if ( OsPaymentsHelper::is_payment_processor_enabled( $this->processor_code ) && $transaction_model->processor == $this->processor_code ) {
				$result = true;
			}

			return $result;
		}


		public function add_scripts_to_clean_layout(array $js_files) : array{
			$js_files[] = 'latepoint-payments-woocommerce-front';
			return $js_files;
		}


        /**
         * Handle payment complete
         * @param int $wc_order_id
         */
		public function handle_payment_complete( $wc_order_id ) {
            $wc_order = wc_get_order( $wc_order_id );
			OsPaymentsWoocommerceHelper::convert_order( $wc_order );
		}

		/**
		 * If user select payment method cash on delivery or bank transfer - we need to create order intent
		 * to reserve date and time for the booking
		 * @param $wc_order_id
		 * @param $posted_data
		 * @param $wc_order
		 *
		 * @return void
		 */
		public function handle_checkout_order_processed( $wc_order_id, $posted_data, $wc_order ) {
            OsPaymentsWoocommerceHelper::create_new_order_for_bacs_and_cod($wc_order);
		}


		public function handle_order_status_changed( $wc_order_id, $old_status, $new_status, $wc_order ) {

            if (!OsPaymentsWoocommerceHelper::wc_order_has_lp_meta( $wc_order ) || !in_array( $new_status, ['completed', 'processing', 'paid'])) {
            	return;
            }


            if ( $wc_order->get_payment_method() == 'cod' && !$wc_order->get_meta( '_cod_converted' )) {
                $wc_order->update_meta_data( '_cod_converted', 'yes' );
                $wc_order->update_status( 'on-hold' );

                return;
            }

            OsPaymentsWoocommerceHelper::convert_order( $wc_order, true );
		}

		/**
         * Set custom price for cart items based on order intent or transaction intent.
		 */
		public function set_custom_cart_item_price( $cart ) {
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				return;
			}

			foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( ! empty( $cart_item['lp_custom_price'] ) ) {
					$cart_item['data']->set_price( $cart_item['lp_custom_price'] );
				}
			}

		}


		/**
		 * Woocommerce hook - Set cart total based on LatePoint cart data
		 */
		public function add_woocommerce_cart_total( $total ) {
			$order_intent_key = OsPaymentsWoocommerceHelper::get_intent_key_from_lp_cart( '_latepoint_order_intent_key' );

            if (!empty($order_intent_key)) {
	            $intent_model = OsOrderIntentHelper::get_order_intent_by_intent_key( $order_intent_key );
                if ($intent_model->get_payment_data_value('portion') == LATEPOINT_PAYMENT_PORTION_DEPOSIT) {
                    return $intent_model->charge_amount;
                }
            }
			return $total;
		}


		/**
		 * Woocommerce hook - Set fees based on LatePoint intent data
		 */
		public function add_woocommerce_cart_fees () {
            $discount = false;
            $tax = false;

			$order_intent_key = OsPaymentsWoocommerceHelper::get_intent_key_from_lp_cart( '_latepoint_order_intent_key' );

            if ($order_intent_key) {
	            $intent_model = OsOrderIntentHelper::get_order_intent_by_intent_key( $order_intent_key );
                if ($intent_model->get_payment_data_value('portion') != LATEPOINT_PAYMENT_PORTION_DEPOSIT) {
                    $discount = $intent_model->coupon_discount;
                    $tax = $intent_model->tax_total;
                }
            }

			$transaction_intent_key = OsPaymentsWoocommerceHelper::get_intent_key_from_lp_cart( '_latepoint_transaction_intent_key' );
            if ($transaction_intent_key) {
	            $intent_model = OsTransactionIntentHelper::get_transaction_intent_by_intent_key( $transaction_intent_key );
	            $order = new OsOrderModel( $intent_model->order_id );

	            $discount = $order->coupon_discount;
				$tax = $order->tax_total;

                if ( $order->get_initial_payment_data_value( 'portion' ) != LATEPOINT_PAYMENT_PORTION_FULL ) {
                    $deposit = $order->get_deposit_amount_to_charge();
                    if ($deposit && $deposit > 0) {
                        WC()->cart->add_fee( esc_html__( 'Payments and Credits' ), -abs( $deposit ) );
                    }
                }
            }

			if ( $discount && $discount > 0 ) {
				WC()->cart->add_fee( esc_html__( 'Discount' ), -abs( $discount ) );
			}
			if ( $tax && $tax > 0 ) {
				WC()->cart->add_fee( esc_html__( 'Tax' ), $tax );
			}
		}

		/**
         * If in cart added latepoint product and then added non-latepoint product - we need to empty cart
		 * @param $passed
		 * @param $product_id
		 * @param $quantity
		 *
		 * @return mixed
		 */
		public function prevent_mixed_cart_validation( $passed, $product_id, $quantity ) {
			$is_latepoint_product = get_post_meta($product_id, '_is_latepoint_product', true) == 'yes';

            if (!$is_latepoint_product && OsPaymentsWoocommerceHelper::has_latepoint_products_in_cart()) {
				WC()->cart->empty_cart();
			}
            return $passed;
        }

		public function allow_multiple_latepoint_products( $sold_individually, $product ) {
			if ( $product && get_post_meta( $product->get_id(), '_is_latepoint_product', true ) === 'yes' ) {
				return false;
			}
			return $sold_individually;
		}

	}

endif;

if ( in_array( 'latepoint/latepoint.php', get_option( 'active_plugins', array() ) ) || array_key_exists( 'latepoint/latepoint.php', get_site_option( 'active_sitewide_plugins', array() ) ) ) {
	$LATEPOINT_ADDON_PAYMENTS_WOOCOMMERCE = new LatePointPaymentsWoocommerce();
}
$latepoint_session_salt = 'MzAwZjY4NTAtZjRkYi00MjJkLTg3YjAtZWU0NDA1YjA0NDE2';
