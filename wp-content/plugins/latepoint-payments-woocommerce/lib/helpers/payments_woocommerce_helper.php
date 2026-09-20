<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OsPaymentsWoocommerceHelper {

	public static $processor_name = 'woocommerce';

	// Memoized values per-request
	private static $memoized_intent_keys = [];
	private static $memoized_is_deposit = null;

	public static function convert_charge_amount( $charge_amount ) {
		$number_of_decimals = OsSettingsHelper::get_settings_value( 'number_of_decimals', '2' );

		return number_format( (float) $charge_amount, $number_of_decimals, '.', '' );
	}

	public static function get_success_redirect_settings_value() {
		return OsSettingsHelper::get_settings_value( 'woocommerce_success_redirect_to', 'latepoint' );
	}


    /**
     * Get last sync date for products
     *
     * @return string
     */
	public static function get_products_last_sync_date(  ) {
        $date = OsSettingsHelper::get_settings_value( 'woocommerce_last_sync_date', '' );
        if (!empty($date)){
            $format = OsSettingsHelper::get_readable_date_format() . ' ' . OsSettingsHelper::get_readable_time_format();
			$utc_date = date_create_from_format( LATEPOINT_DATETIME_DB_FORMAT, $date );
			$wp_timezone_date = $utc_date->setTimezone(OsTimeHelper::get_wp_timezone());
			return date_format( $wp_timezone_date, $format );
        }
		return $date;
    }

	public static function build_url_to_woocommerce_products() {
		return admin_url( 'edit.php?post_type=product' );
	}


	public static function build_url_to_woocommerce_order($order_id) : string{
		return admin_url('post.php?post=' . $order_id . '&action=edit');
	}

	public static function generic_product_status_html() {
		ob_start();
        $last_sync_date = OsPaymentsWoocommerceHelper::get_products_last_sync_date();
		?>
		<div class="generic-product-info">
            <span><?php esc_html_e('Status:', 'latepoint-payments-woocommerce'); ?></span>
            <?php if($last_sync_date){ ?>
                <a class="gp-existing-product-link" target="_blank" href="<?php echo esc_url(OsPaymentsWoocommerceHelper::build_url_to_woocommerce_products()); ?>">
                    <span><i class="latepoint-icon latepoint-icon-check"></i></span>
                    <span><?php esc_html_e('Synchronized', 'latepoint-payments-woocommerce'); ?></span>
                    <i class="latepoint-icon latepoint-icon-external-link"></i>
                </a>
                <span><?php echo $last_sync_date; ?></span>
                <a class="gp-generate-link" href="#"
                   data-os-output-target=".generic-product-info-wrapper"
                   data-os-prompt="<?php _e('Are you sure you want to re-generate a products?', 'latepoint-payments-woocommerce'); ?>"
               		data-os-action="<?php echo OsRouterHelper::build_route_name('payments_woocommerce', 'generate_generic_products'); ?>">
                    <i class="latepoint-icon latepoint-icon-rotate-cw"></i>
                    <span><?php esc_html_e('re-sync', 'latepoint-payments-woocommerce'); ?></span>
                </a>
            <?php } else { ?>
                <div class="gp-not-found"><?php esc_html_e('Not Synchronized', 'latepoint-payments-woocommerce'); ?></div>
                <a class="gp-generate-link" href="#"
                   data-os-output-target=".generic-product-info-wrapper"
                   data-os-action="<?php echo OsRouterHelper::build_route_name( 'payments_woocommerce', 'generate_generic_products' ); ?>">
                    <i class="latepoint-icon latepoint-icon-refresh"></i>
                    <span><?php esc_html_e( 'sync', 'latepoint-payments-woocommerce' ); ?></span>
                </a>
            <?php } ?>
        </div>
		<?php
		$html = ob_get_clean();

		return $html;
	}


	/**
	 * Process Payment for Order Intent and Transaction Intent
	 *
	 * @param array $result
	 * @param OsOrderIntentModel | OsTransactionIntentModel $intent_model
	 *
	 * @return array
	 */
	public static function process_payment(array $result, $intent_model ): array {
		if( $intent_model->get_payment_data_value( 'method' ) == self::$processor_name ) {
            $wc_order_id = $intent_model->get_payment_data_value( 'token' );
				if ( $wc_order_id ) {
					$wc_order = wc_get_order( $wc_order_id );

                    if ( $wc_order && $wc_order->is_paid() ) {
                        $result['status']    = LATEPOINT_STATUS_SUCCESS;
                        $result['charge_id'] = $wc_order->get_id();
                        $result['processor'] = self::$processor_name;
                        $result['kind']      = LATEPOINT_TRANSACTION_KIND_CAPTURE;
                    } else {
	                    $result['status']  = LATEPOINT_STATUS_ERROR;
						$result['message'] = esc_html__( 'Payment Error', 'latepoint-payments-woocommerce' );
						$intent_model->add_error( 'send_to_step', $result['message'], 'payment' );
                    }
				} else {
					$result['status']  = LATEPOINT_STATUS_ERROR;
					$result['message'] = esc_html__( 'Payment Error, token missing', 'latepoint-payments-woocommerce' );
					$intent_model->add_error( 'payment_error', $result['message'] );
				}
		}
		return $result;
	}

	public static function process_refund($transaction_refund, OsTransactionModel $transaction, $custom_amount = null) {
		if ($transaction->processor != self::$processor_name) return $transaction_refund;

		if(!$transaction->can_refund() || empty($transaction->token)) throw new Exception(esc_html__('Invalid Transaction', 'latepoint-payments-woocommerce'));

        $order_id = $transaction->token;

		$order = wc_get_order($order_id);

		if (!$order) {
			throw new Exception(esc_html__( 'Order #' . $order_id . ' not found', 'latepoint-payments-woocommerce' ));
		}

		if (!$order->get_total() || !$order->has_status(wc_get_is_paid_statuses())) {
			throw new Exception(esc_html__('Cannot Refund', 'latepoint-payments-woocommerce'));
		}

        $custom_amount = is_null( $custom_amount ) ? $order->get_total() : $custom_amount;

		$refund = wc_create_refund(array(
			'amount'         => self::convert_charge_amount($custom_amount),
			'order_id'       => $order_id,
			'refund_payment' => true
		));

        if (is_wp_error($refund)) {
            throw new Exception($refund->get_error_message());
        }

		$transaction_refund = new OsTransactionRefundModel();
		$transaction_refund->transaction_id = $transaction->id;
		$transaction_refund->amount = self::convert_amount_back_from_specs_to_db_format($refund->get_amount());
		$transaction_refund->token = $refund->get_id();

		if($transaction_refund->save()){
			/**
			 * Transaction refund was issued
			 *
			 * @param {OsTransactionRefundModel} $transaction_refund instance of transaction refund model that was issued
			 *
			 * @since 5.1.0
			 * @hook latepoint_transaction_refund_created
			 *
			 */
			do_action( 'latepoint_transaction_refund_created', $transaction_refund );
			return $transaction_refund;
		} else {
			throw new Exception( implode( ', ', $transaction_refund->get_error_messages() ) );
		}
	}

	public static function convert_amount_back_from_specs_to_db_format( $charge_amount ) {
		return number_format( (float) $charge_amount, 4, '.', '' );
    }


	public static function get_not_paid_order_intent_invoice( $order_intent ) {
        if ($order_intent->is_new_record() || empty($order_intent->order_id)) {
            return false;
        }

        $invoice = new OsInvoiceModel();
        $invoice = $invoice->where( [ 'order_id' => $order_intent->order_id, 'status' => LATEPOINT_INVOICE_STATUS_OPEN ] )->set_limit( 1 )->get_results_as_models();

		if (empty($invoice)) {
			return false;
		}
		return $invoice;
	}

	public static function wc_order_has_lp_meta( $wc_order ):bool {
        return (!empty($wc_order->get_meta( '_latepoint_order_intent_key' )) || !empty($wc_order->get_meta( '_latepoint_transaction_intent_key' )));
    }

    public static function populate_checkout_customer( $intent_model ) {
        if ( ! $intent_model || $intent_model->is_new_record() || empty( $intent_model->customer_id ) || ! WC()->customer || ! WC()->session ) {
            return;
        }

        $latepoint_customer = new OsCustomerModel( $intent_model->customer_id );
        if ( $latepoint_customer->is_new_record() ) {
            return;
        }

        $woocommerce_customer = WC()->customer;
        $woocommerce_customer->set_billing_first_name( sanitize_text_field( $latepoint_customer->first_name ) );
        $woocommerce_customer->set_billing_last_name( sanitize_text_field( $latepoint_customer->last_name ) );
        $woocommerce_customer->set_billing_email( sanitize_email( $latepoint_customer->email ) );
        $woocommerce_customer->set_billing_phone( sanitize_text_field( $latepoint_customer->phone ) );

        self::ensure_valid_checkout_location( $woocommerce_customer );

        $session_store = new WC_Customer_Data_Store_Session();
        $session_store->save_to_session( $woocommerce_customer );
        WC()->session->set( 'store_api_customer_note', wc_sanitize_textarea( $latepoint_customer->notes ) );
    }

    public static function ensure_valid_checkout_location( $woocommerce_customer ) {
        if ( ! $woocommerce_customer instanceof WC_Customer ) {
            return;
        }

        $base_location  = wc_get_base_location();
        $billing_country = $woocommerce_customer->get_billing_country();
        if ( empty( $billing_country ) && ! empty( $base_location['country'] ) ) {
            $billing_country = $base_location['country'];
            $woocommerce_customer->set_billing_country( $billing_country );
        }

        $billing_states = WC()->countries->get_states( $billing_country );
        $billing_state  = $woocommerce_customer->get_billing_state();
        if ( is_array( $billing_states ) && ! empty( $billing_states ) && ! isset( $billing_states[ $billing_state ] ) ) {
            $base_state = $billing_country === $base_location['country'] ? $base_location['state'] : '';
            $woocommerce_customer->set_billing_state( isset( $billing_states[ $base_state ] ) ? $base_state : '' );
        }
    }

	public static function convert_order( $wc_order, $create_transaction_if_needed = false ) {
		if ( !$wc_order || !$wc_order->is_paid() ) {
			return;
		}
        $total_amount = $wc_order->get_total();
        $tax = $wc_order->get_total_tax();
        $discount = $wc_order->get_total_discount();

        $order_intent_key = $wc_order->get_meta( '_latepoint_order_intent_key' );
        if ( ! empty( $order_intent_key ) ) {
            $order_intent = OsOrderIntentHelper::get_order_intent_by_intent_key( $order_intent_key );
            $order_intent->set_payment_data_value( 'token', $wc_order->get_id() );

            $delta = $wc_order->get_total() - $order_intent->total;

            if ($order_intent->get_payment_data_value('portion') != LATEPOINT_PAYMENT_PORTION_DEPOSIT && $delta != 0) {
                $price_breakdown = json_decode( $order_intent->price_breakdown, true );
                $price_breakdown = self::append_adjustment_to_price_breakdown( $tax, $discount, $price_breakdown );

                $order_intent->total = $total_amount;
                $order_intent->charge_amount = $total_amount;
                $order_intent->specs_charge_amount = OsPaymentsWoocommerceHelper::convert_charge_amount($total_amount);
                $order_intent->price_breakdown = json_encode( $price_breakdown );
                $order_intent->save();
            }

            $order_id = $order_intent->convert_to_order(); //will be run process_payment
            if ( ! $order_id ) {
                OsDebugHelper::log( 'Error processing woocommerce webhook: converting order intent failed.' . $order_intent->get_error_messages() );
            }

            if ($create_transaction_if_needed) {
                $invoice = OsPaymentsWoocommerceHelper::get_not_paid_order_intent_invoice( $order_intent );

                if ( $invoice ) {
                    $payment_params = [
                        'payment_portion'   => $order_intent->get_payment_data_value( 'portion' ),
                        'payment_method'    => $order_intent->get_payment_data_value( 'method' ),
                        'payment_processor' => OsPaymentsWoocommerceHelper::$processor_name,
                    ];
                    $transaction_intent = OsTransactionIntentHelper::create_or_update_transaction_intent( $invoice, $payment_params );
                    $transaction_intent->set_payment_data_value( 'token', $wc_order->get_id() );
                    $transaction_intent->convert_to_transaction();
                }
            }
            return;
        }


        $transaction_intent_key = $wc_order->get_meta( '_latepoint_transaction_intent_key' );
        if ( ! empty( $transaction_intent_key ) ) {
            $transaction_intent = OsTransactionIntentHelper::get_transaction_intent_by_intent_key( $transaction_intent_key );
            $transaction_intent->set_payment_data_value( 'token', $wc_order->get_id() );

            $delta = $total_amount - $transaction_intent->charge_amount;

            if ($delta != 0) {

                $order = new OsOrderModel($transaction_intent->order_id);

                #1 update transaction intent
                $transaction_intent->charge_amount = $total_amount;
                $transaction_intent->specs_charge_amount = OsPaymentsWoocommerceHelper::convert_charge_amount($total_amount);

                $transaction_intent->set_payment_data_value( 'charge_amount', $total_amount );
                $transaction_intent->set_payment_data_value( 'specs_charge_amount',  $transaction_intent->specs_charge_amount );
                $transaction_intent->save();

                #2 update order
                $price_breakdown = json_decode( $order->price_breakdown, true );
                $price_breakdown = self::append_adjustment_to_price_breakdown( $tax, $discount, $price_breakdown );

                $order->update_attributes( [
                    'price_breakdown' => json_encode( $price_breakdown ),
                    'total'           => $order->total + $delta, // we can't use $total_amount because it can be part payment
                ] );

                #3 update invoice
                $updated_invoice_data = OsInvoicesHelper::generate_invoice_data_from_order( $order );
                $invoice                = (new OsInvoiceModel($transaction_intent->invoice_id));
                $invoice_data = json_decode( $invoice->data, true );
                $payments = !empty($invoice_data['totals']['payments']) ? $invoice_data['totals']['payments'] : false;
                if ($payments) {
                    $updated_invoice_data['totals']['payments'] = $payments;
                }
                $invoice->data = json_encode( $updated_invoice_data );
                $invoice->charge_amount += $delta;
                $invoice->save();
            }


            if ( ! $transaction_intent->convert_to_transaction() ) {
                OsDebugHelper::log( 'Error processing woocommerce webhook: converting transaction intent failed' );
            }
        }
	}

	public static function get_or_create_woo_product_by_model( $model ) {
		$product_id = $model->get_meta_by_key('woocommerce_product_id');

        if ( $product_id &&  wc_get_product($product_id) ) {
            return $product_id;
        }

		return self::create_woo_product( $model );
    }

	public static function get_intent_key_from_lp_cart( $intent_key ) {
		if ( array_key_exists( $intent_key, self::$memoized_intent_keys ) ) {
			return self::$memoized_intent_keys[ $intent_key ];
		}

		if ( ! function_exists('WC') || ! class_exists('WooCommerce') ) {
			self::$memoized_intent_keys[ $intent_key ] = false;
			return false;
		}

		if ( empty( WC()->cart ) ) {
			self::$memoized_intent_keys[ $intent_key ] = false;
			return false;
		}

		$cart_items = is_array( WC()->cart->get_cart() ) ? WC()->cart->get_cart() : [];
		foreach ( $cart_items as $cart_item ) {
			if ( ! empty( $cart_item[ $intent_key ] ) ) {
				self::$memoized_intent_keys[ $intent_key ] = $cart_item[ $intent_key ];
				return self::$memoized_intent_keys[ $intent_key ];
			}
		}

		self::$memoized_intent_keys[ $intent_key ] = false;
		return false;
	}

	/**
	 * Get model from order item - OsBundleModel | OsServiceModel
     * @param OsCartItemModel | OsOrderItemModel $order_item
     * @return OsBundleModel | OsServiceModel | false
	 */
	public static function get_model_from_order_item( $order_item ) {
		$item_data = json_decode( $order_item->item_data, true );

        if ($order_item->is_booking()) {
	        $service_id = $item_data['service_id'];
	        return new OsServiceModel( $service_id );
        }

        if ($order_item->is_bundle()) {
	        $bundle_id = $item_data['bundle_id'];
	        return new OsBundleModel( $bundle_id );
        }

        return false;
    }

	/**
     * Check if there are any LatePoint products in the cart.
	 * @return bool
	 */
	public static function has_latepoint_products_in_cart(  ): bool {
		if (!WC()->cart || WC()->cart->is_empty()) {
			return false;
		}

		foreach (WC()->cart->get_cart() as $cart_item) {
			$product_id = $cart_item['product_id'];

			if (get_post_meta($product_id, '_is_latepoint_product', true)) {
				return true;
			}
		}

		return false;
    }


	/**
     * Create or update WooCommerce product for LatePoint service or bundle.
	 * @param OsServiceModel | OsBundleModel  $model
	 * @return void
	 */
	public static function sync_product_with_woocommerce( $model ) {
		if ( ! ( $model instanceof OsServiceModel ) && ! ( $model instanceof OsBundleModel ) ) {
			return;
		}
		$product_id = $model->is_new_record() ? self::create_woo_product( $model ): self::update_woo_product( $model );

		if ($product_id) {
			$model->save_meta_by_key('woocommerce_product_id', $product_id);
		}
	}


	/**
	 * Sync Latepoint services and bundles with WooCommerce products.
	 * @return void
	 */
	public static function sync_products(  ) {
		$services = new OsServiceModel();
		$services = $services->should_be_active()->get_results_as_models();

		foreach ( $services as $service ) {
			OsPaymentsWoocommerceHelper::sync_product_with_woocommerce( $service );
		}

		$bundles = new OsBundleModel();
		$bundles = $bundles->should_be_active()->get_results_as_models();
		foreach ( $bundles as $bundle ) {
			OsPaymentsWoocommerceHelper::sync_product_with_woocommerce( $bundle );
		}

		OsSettingsHelper::save_setting_by_name('woocommerce_last_sync_date', OsTimeHelper::now_datetime_utc_in_db_format());
	}


	public static function create_woo_product( $model ) {
		$product = new WC_Product_Simple();
		$product->set_name( $model->name );
		$product->set_description( $model->short_description );
		$product->set_regular_price( $model->charge_amount );
		$product->set_status( 'publish' );
		$product->set_virtual( true );
		$product_id = $product->save();

		if ($product_id) {
			update_post_meta($product_id, '_is_latepoint_product', 'yes');
			$model->save_meta_by_key('woocommerce_product_id', $product_id);
            wp_set_object_terms($product_id, esc_html__('Latepoint', 'latepoint-payments-woocommerce'), 'product_cat');
			return $product_id;
		}
		return false;
	}


	public static function update_woo_product( $model ) {
		$product_id = $model->get_meta_by_key('woocommerce_product_id');

		$woo_product = $product_id ? wc_get_product($product_id) : false;

		if ( ! $woo_product ) {
			return self::create_woo_product( $model );
		}

		$woo_product->set_name( $model->name );
		$woo_product->set_regular_price( $model->charge_amount );
		$woo_product->set_description( $model->short_description );
		$product_id = $woo_product->save();
        wp_set_object_terms($product_id, esc_html__('Latepoint', 'latepoint-payments-woocommerce'), 'product_cat');

		return $product_id;
	}


    /**
     * Validates the order before creation based on the order intent key and its status.
     * @return void
     */
    public static function validate_order_before_creation(  ) {
        $order_intent_key = OsPaymentsWoocommerceHelper::get_intent_key_from_lp_cart( '_latepoint_order_intent_key' );

        if ( $order_intent_key ) {
            $order_intent = OsOrderIntentHelper::get_order_intent_by_intent_key( $order_intent_key );
            if (!$order_intent->is_bookable() ) {
                wc_add_notice( empty( $order_intent->get_error_messages() ) ? __( 'Booking slot is not available anymore.', 'latepoint' ) : implode( ', ', $order_intent->get_error_messages() ), 'error' );
            }
        }
    }

    /**
     * Validate the order before its creation in the store API.
     *
     * @param mixed $order The order object or data to validate before creation.
     * @param mixed|null $request Optional. The request object containing additional data to validate the order.
     *
     * @return void
     * @throws Exception If the booking slot is no longer available or other validation errors occur.
     */
    public static function validate_order_before_creation_store_api( $order, $request = null ) {
        $order_intent_key = OsPaymentsWoocommerceHelper::get_intent_key_from_lp_cart( '_latepoint_order_intent_key' );

        if ( $order_intent_key ) {
            $order_intent = OsOrderIntentHelper::get_order_intent_by_intent_key( $order_intent_key );

            if ( ! $order_intent->is_bookable() ) {
                $message = empty( $order_intent->get_error_messages() ) ? __( 'Booking slot is not available anymore.', 'latepoint' ) : implode( ', ', $order_intent->get_error_messages() );
                throw new Exception( $message );
            }
        }
    }

    public static function transfer_cart_item_data( $wc_order ) {
        $intent_model = false;
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if (!empty($cart_item['_latepoint_order_intent_key'])) {
                $wc_order->update_meta_data('_latepoint_order_intent_key', $cart_item['_latepoint_order_intent_key']);
                $intent_model = OsOrderIntentHelper::get_order_intent_by_intent_key( $cart_item['_latepoint_order_intent_key'] );
                break;
            }
            if (!empty($cart_item['_latepoint_transaction_intent_key'])) {
                $wc_order->update_meta_data('_latepoint_transaction_intent_key', $cart_item['_latepoint_transaction_intent_key']);
                $intent_model = OsTransactionIntentHelper::get_transaction_intent_by_intent_key( $cart_item['_latepoint_transaction_intent_key'] );
                break;
            }
        }

        if ( $intent_model && ! $intent_model->is_new_record() ) {
            $latepoint_customer = new OsCustomerModel( $intent_model->customer_id );
            if ( ! $latepoint_customer->is_new_record() ) {
                if ( ! $wc_order->get_billing_first_name() ) {
                    $wc_order->set_billing_first_name( $latepoint_customer->first_name );
                }
                if ( ! $wc_order->get_billing_last_name() ) {
                    $wc_order->set_billing_last_name( $latepoint_customer->last_name );
                }
                if ( ! $wc_order->get_billing_email() ) {
                    $wc_order->set_billing_email( $latepoint_customer->email );
                }
                if ( ! $wc_order->get_billing_phone() ) {
                    $wc_order->set_billing_phone( $latepoint_customer->phone );
                }
                if ( ! $wc_order->get_customer_note() ) {
                    $wc_order->set_customer_note( $latepoint_customer->notes );
                }
            }
        }
        $wc_order->save();
    }

    public static function create_new_order_for_bacs_and_cod( $wc_order ) {
        if ( ! in_array($wc_order->get_payment_method(), ['bacs', 'cod'] )) {
            return;
        }

        $order_intent_key = $wc_order->get_meta( '_latepoint_order_intent_key' );

        if (!empty($order_intent_key)) {
            $order_intent = OsOrderIntentHelper::get_order_intent_by_intent_key( $order_intent_key );
            $order_intent->set_payment_data_value( 'processor', 'latepoint' ); // to create order without payment
            $order_id = $order_intent->convert_to_order();
            $order_intent->set_payment_data_value( 'processor', self::$processor_name );

            if ( ! $order_id ) {
                OsDebugHelper::log( 'Error processing woocommerce webhook: converting order intent failed.' . $order_intent->get_error_messages() );
            }
        }
    }


    public static function add_woo_diff_to_cart_price_breakdown_rows( array $rows, OsCartModel $cart, $rows_to_hide ): array {

        if($cart->payment_portion == LATEPOINT_PAYMENT_PORTION_DEPOSIT){
            return $rows;
        }

        if ( $cart->order_id ) {
            $order = new OsOrderModel( $cart->order_id );
            $price_breakdown = json_decode( $order->price_breakdown, true );
            $diff_items = $price_breakdown['after_subtotal']['diff']['items'] ?? [];

            if ( $diff_items ) {
                $rows['after_subtotal']['diff'] = [ 'items' => $diff_items ];
            }

            return $rows;
        }


        if ($cart->payment_method != self::$processor_name || empty($cart->payment_token)) {
            return $rows;
        }

        // when generate_price_breakdown_rows - add diff to price breakdown
        $wc_order = wc_get_order($cart->payment_token);
        if (!$wc_order) {
            return $rows;
        }

        $tax = $wc_order->get_total_tax();
        $discount = $wc_order->get_total_discount();

        $rows = self::append_adjustment_to_price_breakdown( $tax, $discount, $rows );

        return $rows;
    }

    public static function remove_empty_woo_diff_from_price_breakdown_rows( array $rows ): array {
        if ( isset( $rows['after_subtotal']['diff'] ) && empty( $rows['after_subtotal']['diff']['items'] ) ) {
            unset( $rows['after_subtotal']['diff'] );
        }

        return $rows;
    }


    public static function apply_woo_diff_to_cart_calculations( OsCartModel $cart ) {
        if ( $cart->order_id && $cart->payment_portion != LATEPOINT_PAYMENT_PORTION_DEPOSIT) {
            $order   = new OsOrderModel( $cart->order_id );
            $price_breakdown = json_decode( $order->price_breakdown, true );
            $diff_items = $price_breakdown['after_subtotal']['diff']['items'] ?? [];

            $diff = array_sum(array_column($diff_items, 'raw_value'));

            if ( $diff != 0 ) {
                $cart->total = $cart->get_total() + $diff;
            }
        }
    }

	public static function is_payment_deposit( ) {
		if ( self::$memoized_is_deposit !== null ) {
			return self::$memoized_is_deposit;
		}

		$order_intent_key = OsPaymentsWoocommerceHelper::get_intent_key_from_lp_cart( '_latepoint_order_intent_key' );
		if ( ! empty( $order_intent_key ) ) {
			$intent_model = OsOrderIntentHelper::get_order_intent_by_intent_key( $order_intent_key );
			self::$memoized_is_deposit = ( $intent_model && $intent_model->get_payment_data_value( 'portion' ) == LATEPOINT_PAYMENT_PORTION_DEPOSIT );
			return self::$memoized_is_deposit;
		}

		self::$memoized_is_deposit = false;
		return false;
	}

    /**
     * @param $tax
     * @param $discount
     * @param $price_breakdown
     *
     * @return array
     */
    private static function append_adjustment_to_price_breakdown( $tax, $discount, $price_breakdown ): array {
        unset( $price_breakdown['after_subtotal']['diff'] );
        $items = [];

        $add_item = function (string $label, float $amount) use (&$items) {
            if ($amount == 0) return;

            $items[] = [
                    'label'     => $label,
                    'raw_value' => OsMoneyHelper::pad_to_db_format($amount),
                    'value'     => ($amount < 0 ? "-" : "") . OsMoneyHelper::format_price(abs($amount), true, false),
                    'type'      => ($amount < 0) ? 'credit' : ''
            ];
        };

        $add_item(__('Woocommerce Tax', 'latepoint-payments-woocommerce'), $tax);
        $add_item(__('Woocommerce Discount', 'latepoint-payments-woocommerce'), -$discount);

        if ( $items ) {
            $price_breakdown['after_subtotal']['diff'] = [ 'items' => $items ];
        }

        return $price_breakdown;
    }


    /**
     * Hide LatePoint products from the shop.
     * Adds a meta query condition to exclude products marked with the '_is_latepoint_product' meta key.
     *
     * @param WP_Query $query The main query object.
     *
     * @return void
     */
    public static function hide_latepoint_products_from_shop( WP_Query $query ) {
        if (!is_admin() && $query->is_main_query()) {
            if (is_shop() || is_product_category() || is_product_tag()) {
                $meta_query = $query->get('meta_query', array());
                $meta_query[] = array(
                        'key' => '_is_latepoint_product',
                        'compare' => 'NOT EXISTS'
                );
                $query->set('meta_query', $meta_query);
            }
        }
    }

    private static function should_disable_taxes_and_coupons(): bool {
        $is_cart_checkout = (function_exists('is_cart') && is_cart()) ||
                            (function_exists('is_checkout') && is_checkout());

        return $is_cart_checkout && self::is_payment_deposit();
    }

    public static function disable_coupons($enabled) {
        return self::should_disable_taxes_and_coupons() ? false : $enabled;
    }

    public static function disable_tax_option($value) {
        return self::should_disable_taxes_and_coupons() ? 'no' : $value;
    }

    public static function disable_tax_calc($calc_tax) {
        return self::should_disable_taxes_and_coupons() ? false : $calc_tax;
    }

    public static function disable_tax_rates($rates, $args) {
        return self::should_disable_taxes_and_coupons() ? array() : $rates;
    }

    public static function add_info_after_general_authentication_settings(  ) {
        ?>
        <div class="latepoint-message latepoint-message-subtle">
            <?php _e('If you choose to disable the login option, please make sure that guest checkout is enabled in WooCommerce settings', 'latepoint-payments-woocommerce'); ?>
            (<strong>Settings - Accounts & Privacy - Checkout</strong>)
        </div>
        <?php
    }

}