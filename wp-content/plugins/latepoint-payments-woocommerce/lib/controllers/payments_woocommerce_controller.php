<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsPaymentsWoocommerceController' ) ) :


	class OsPaymentsWoocommerceController extends OsController {

		function __construct() {
			parent::__construct();

			$this->action_access['public']   = array_merge( $this->action_access['public'], [ 'start_order_checkout', 'start_checkout'] );
			$this->action_access['customer'] = array_merge( $this->action_access['customer'], [] );
			$this->views_folder              = plugin_dir_path( __FILE__ ) . '../views/payments_woocommerce/';
		}

		/**
		 * Create or update WooCommerce products for services and bundles.
		 */
		public function generate_generic_products() {
			OsPaymentsWoocommerceHelper::sync_products();

			$this->send_json( [ 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => OsPaymentsWoocommerceHelper::generic_product_status_html() ] );
		}

		public function start_checkout() {
			try {
				OsStepsHelper::set_required_objects( $this->params );

				$booking_form_page_url = $this->params['booking_form_page_url'] ?? wp_get_original_referer();
				$order_intent          = OsOrderIntentHelper::create_or_update_order_intent( OsStepsHelper::$cart_object, OsStepsHelper::$restrictions, OsStepsHelper::$presets, $booking_form_page_url, OsStepsHelper::get_customer_object_id() );

				if ( ! $order_intent->is_bookable() ) {
					throw new Exception( empty( $order_intent->get_error_messages() ) ? __( 'Booking slot is not available anymore.', 'latepoint' ) : implode( ', ', $order_intent->get_error_messages() ) );
				}

				$lp_cart = $order_intent->build_cart_object();

				WC()->cart->empty_cart();
				wc_clear_notices();
				OsPaymentsWoocommerceHelper::populate_checkout_customer( $order_intent );

				foreach ( $lp_cart->get_items() as $cart_item ) {
					$model = OsPaymentsWoocommerceHelper::get_model_from_order_item($cart_item);

					if ($model) {
						$product_id = OsPaymentsWoocommerceHelper::get_or_create_woo_product_by_model($model);
						$product_price = ($lp_cart->payment_portion == LATEPOINT_PAYMENT_PORTION_DEPOSIT) ? $cart_item->deposit_amount_to_charge() : $cart_item->subtotal;

						WC()->cart->add_to_cart( $product_id, 1, 0, array(), array(
							'lp_custom_price' => OsPaymentsWoocommerceHelper::convert_charge_amount($product_price),
							'_latepoint_order_intent_key' => $order_intent->intent_key
						) );
					}
				}

				$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => wc_get_checkout_url() ) );
			} catch ( Exception $e ) {
				if ( $this->get_return_format() == 'json' ) {
					$this->send_json( array( 'status' => LATEPOINT_STATUS_ERROR, 'message' => $e->getMessage() ) );
				}
			}
		}


		public function start_order_checkout() {
			if ( ! filter_var( $this->params['invoice_id'], FILTER_VALIDATE_INT ) ) {
				exit();
			}

			$invoice            = new OsInvoiceModel( $this->params['invoice_id'] );
			$transaction_intent = OsTransactionIntentHelper::create_or_update_transaction_intent( $invoice, $this->params );
			$order = new OsOrderModel( $invoice->order_id );

			WC()->cart->empty_cart();
			wc_clear_notices();
			OsPaymentsWoocommerceHelper::populate_checkout_customer( $transaction_intent );

			foreach ( $order->get_items() as $order_item ) {
				$model = OsPaymentsWoocommerceHelper::get_model_from_order_item( $order_item );
				if ($model) {
					$product_id = OsPaymentsWoocommerceHelper::get_or_create_woo_product_by_model($model);

					WC()->cart->add_to_cart( $product_id, 1, 0, array(), array(
						'lp_custom_price' => $order_item->subtotal,
						'_latepoint_transaction_intent_key' => $transaction_intent->intent_key
					) );
				}
			}

			$transaction_intent->update_attributes( [
				'order_form_page_url' => $this->params['order_form_page_url'] ?? wp_get_original_referer(),
			] );

			$this->send_json( array( 'status' => LATEPOINT_STATUS_SUCCESS, 'message' => wc_get_checkout_url() ) );
		}
	}

endif;