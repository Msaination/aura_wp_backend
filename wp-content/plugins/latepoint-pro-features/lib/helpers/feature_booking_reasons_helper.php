<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'OsFeatureBookingReasonsHelper' ) ) :

	/**
	 * Collects an optional free-text reason when a booking is cancelled or rescheduled, from the customer
	 * and from staff on the order form, stores it on the booking, and exposes it to admins/agents, the
	 * activity log and notifications.
	 * Pro feature — plugs into core entirely via hooks.
	 */
	class OsFeatureBookingReasonsHelper {

		public static function init_hooks() {
			// Settings: the cancellation enable toggle must always render so it can be switched on/off
			// (the reschedule toggle lives in the reschedule settings block, also always shown).
			add_action( 'latepoint_customer_cancellation_settings', 'OsFeatureBookingReasonsHelper::add_cancellation_settings' );

			$cancellation_on = self::is_enabled( 'cancellation' );
			$reschedule_on   = self::is_enabled( 'reschedule' );

			// Nothing else to wire unless at least one context is enabled.
			if ( ! $cancellation_on && ! $reschedule_on ) {
				return;
			}

			// Capture the submitted reason during the cancel/reschedule request (priority < 12 so it is
			// stored before OsProcessJobsHelper queues notifications, keeping {{...reason}} populated).
			add_action( 'latepoint_booking_updated', 'OsFeatureBookingReasonsHelper::capture_reason_on_booking_updated', 5, 2 );

			// Notifications smart variables, activity log, and admin/customer display.
			add_filter( 'latepoint_replace_booking_vars', 'OsFeatureBookingReasonsHelper::replace_booking_reason_vars', 10, 2 );
			add_action( 'latepoint_available_vars_booking', 'OsFeatureBookingReasonsHelper::add_reason_booking_vars', 15 );
			add_filter( 'latepoint_activity_codes', 'OsFeatureBookingReasonsHelper::add_reason_activity_codes' );
			add_action( 'latepoint_booking_full_summary_head_info_after', 'OsFeatureBookingReasonsHelper::output_booking_reasons_in_summary' );
			add_action( 'latepoint_booking_data_form_after', 'OsFeatureBookingReasonsHelper::output_booking_reasons_in_admin_form', 10, 2 );

			if ( $cancellation_on ) {
				// Cancel button -> open the reason lightbox instead of the native confirm.
				add_filter( 'latepoint_customer_cancel_booking_button', 'OsFeatureBookingReasonsHelper::filter_cancel_booking_button', 10, 4 );
			}

			if ( $reschedule_on ) {
				// Reschedule reason field, rendered inside the reschedule calendar lightbox.
				add_action( 'latepoint_dates_and_times_picker_after', 'OsFeatureBookingReasonsHelper::output_reschedule_reason_field', 10, 3 );
			}
		}

		/**
		 * Whether the reason prompt is enabled for the given context ('cancellation' or 'reschedule').
		 */
		public static function is_enabled( string $context ): bool {
			return OsSettingsHelper::is_on( 'enable_' . $context . '_reason' );
		}

		// --- Settings -----------------------------------------------------------------------------

		public static function add_cancellation_settings() {
			echo OsFormHelper::toggler_field(
				'settings[enable_cancellation_reason]',
				__( 'Ask customer for a cancellation reason', 'latepoint-pro-features' ),
				OsSettingsHelper::is_on( 'enable_cancellation_reason' ),
				false,
				false,
				[ 'sub_label' => __( 'If enabled, customers are prompted for a reason before cancelling', 'latepoint-pro-features' ) ]
			);
		}

		// --- Cancel button ------------------------------------------------------------------------

		/**
		 * Replaces the core cancel button with one that opens the reason lightbox.
		 *
		 * @param string         $html
		 * @param OsBookingModel $booking
		 * @param string         $route_prefix
		 * @param string|null    $key
		 */
		public static function filter_cancel_booking_button( string $html, OsBookingModel $booking, string $route_prefix, ?string $key = null ): string {
			$os_params   = OsUtilHelper::build_os_params( $key ? [ 'key' => $key ] : [ 'id' => $booking->id ] );
			$css_classes = ( 'manage_booking_by_key' === $route_prefix ) ? 'booking-summary-action-btn cancel-appointment-btn' : 'latepoint-btn latepoint-btn-danger latepoint-btn-link';
			ob_start();
			?>
			<a href="#" class="<?php echo esc_attr( $css_classes ); ?>"
			   data-os-output-target="lightbox"
			   data-os-lightbox-classes="width-450 booking-reason-lightbox"
			   data-os-after-call="latepoint_init_booking_cancellation"
			   data-os-action="<?php echo esc_attr( OsRouterHelper::build_route_name( 'booking_reasons', 'cancellation_form' ) ); ?>"
			   data-os-params="<?php echo esc_attr( $os_params ); ?>">
				<i class="latepoint-icon latepoint-icon-ui-24"></i>
				<span><?php esc_html_e( 'Cancel', 'latepoint-pro-features' ); ?></span>
			</a>
			<?php
			return ob_get_clean();
		}

		// --- Reschedule reason field --------------------------------------------------------------

		/**
		 * Renders the reschedule reason textarea after the date/time picker. Only in the reschedule
		 * lightbox (identified by the exclude_booking_ids setting passed only by the reschedule view),
		 * never in the normal booking flow. The field carries data-os-request-param so the core
		 * reschedule request includes its value.
		 *
		 * @param OsBookingModel $booking
		 * @param mixed          $target_date
		 * @param array          $settings
		 */
		public static function output_reschedule_reason_field( $booking, $target_date = null, $settings = [] ) {
			// Only render inside the reschedule lightbox (the only picker that passes exclude_booking_ids).
			if ( empty( $settings['exclude_booking_ids'] ) ) {
				return;
			}
			echo '<div class="booking-reason-form reschedule-reason-form">';
			echo OsFormHelper::textarea_field(
				'reschedule_reason',
				false,
				'',
				[
					'class'                 => 'latepoint-booking-reason',
					'skip_id'               => true,
					'data-os-request-param' => 'reschedule_reason',
					'placeholder'           => __( 'Reason for rescheduling...', 'latepoint-pro-features' ),
				]
			);
			echo '</div>';
		}

		// --- Storage ------------------------------------------------------------------------------

		/**
		 * Stored for whichever context the reason was written in, but only if that change happened.
		 *
		 * @param OsBookingModel      $booking
		 * @param OsBookingModel|null $old_booking
		 */
		public static function capture_reason_on_booking_updated( $booking, $old_booking = null ) {
			if ( empty( $booking->id ) ) {
				return;
			}
			foreach ( self::detect_change_contexts( $booking, $old_booking ) as $context ) {
				self::maybe_store_reason( $booking, $context );
			}
		}

		/**
		 * Contexts this update qualifies for. The order form fires on every save, so an agent or price edit
		 * must not file a reason. Both are returned if a save did both.
		 *
		 * @param OsBookingModel      $booking
		 * @param OsBookingModel|null $old_booking
		 *
		 * @return array<int, string>
		 */
		protected static function detect_change_contexts( $booking, $old_booking ): array {
			if ( empty( $old_booking ) ) {
				return [];
			}
			$contexts = [];
			if ( LATEPOINT_BOOKING_STATUS_CANCELLED === $booking->status && LATEPOINT_BOOKING_STATUS_CANCELLED !== $old_booking->status ) {
				$contexts[] = 'cancellation';
			}
			// Cast: start_time is an int after a form save, a string from the database.
			if ( (string) $booking->start_date !== (string) $old_booking->start_date
				|| (int) $booking->start_time !== (int) $old_booking->start_time ) {
				$contexts[] = 'reschedule';
			}

			return $contexts;
		}

		protected static function maybe_store_reason( $booking, string $context ) {
			if ( ! self::is_enabled( $context ) ) {
				return;
			}
			// Customers submit it as a plain param, staff through the order form. Null means the field was
			// not part of the request, an empty string means staff cleared it - only the first is ignored.
			$submitted = OsParamsHelper::get_param( $context . '_reason' );
			if ( null === $submitted || '' === $submitted ) {
				$submitted = self::get_reason_from_order_form( $booking, $context );
			}
			if ( null === $submitted ) {
				return;
			}
			$reason = trim( sanitize_textarea_field( $submitted ) );
			// The staff field is pre-filled, so an unchanged reason is not stored again.
			if ( $reason === self::get_booking_reason( $booking, $context ) ) {
				return;
			}
			// save_by_key deletes the row when the value is empty, so clearing the field removes the reason.
			OsMetaHelper::save_booking_meta_by_key( $context . '_reason', $reason, $booking->id );
			if ( '' !== $reason ) {
				self::log_booking_reason_activity( $booking, $context, $reason );
			}
		}

		/**
		 * Reason submitted from the order form, nested under the booking it belongs to so an order holding
		 * several captures one for each.
		 *
		 * @param OsBookingModel $booking
		 * @param string         $context
		 *
		 * @return string|null
		 */
		protected static function get_reason_from_order_form( $booking, string $context ): ?string {
			// A bundle keeps its bookings under one order item, recurring one per occurrence.
			foreach ( (array) OsParamsHelper::get_param( 'order_items' ) as $order_item ) {
				if ( isset( $order_item['bookings'][ $booking->id ][ $context . '_reason' ] ) ) {
					$reason = $order_item['bookings'][ $booking->id ][ $context . '_reason' ];

					return is_string( $reason ) ? $reason : null;
				}
			}

			return null;
		}

		public static function get_booking_reason( $booking, string $context ): string {
			return (string) OsMetaHelper::get_booking_meta_by_key( $context . '_reason', $booking->id, '' );
		}

		protected static function log_booking_reason_activity( $booking, string $context, string $reason ) {
			$description = 'cancellation' === $context
				// translators: %s the cancellation reason provided by the customer
				? sprintf( __( 'Cancellation reason: %s', 'latepoint-pro-features' ), $reason )
				// translators: %s the reschedule reason provided by the customer
				: sprintf( __( 'Reschedule reason: %s', 'latepoint-pro-features' ), $reason );
			OsActivitiesHelper::create_activity(
				[
					'booking_id'  => $booking->id,
					'customer_id' => $booking->customer_id,
					'code'        => 'booking_' . $context . '_reason',
					'description' => $description,
				]
			);
		}

		// --- Notifications / activity codes -------------------------------------------------------

		public static function add_reason_activity_codes( array $codes ): array {
			$codes['booking_cancellation_reason'] = __( 'Cancellation Reason Provided', 'latepoint-pro-features' );
			$codes['booking_reschedule_reason']   = __( 'Reschedule Reason Provided', 'latepoint-pro-features' );

			return $codes;
		}

		public static function replace_booking_reason_vars( $text, $booking ) {
			if ( false === strpos( $text, '{{cancellation_reason}}' ) && false === strpos( $text, '{{reschedule_reason}}' ) ) {
				return $text;
			}
			$needles      = [ '{{cancellation_reason}}', '{{reschedule_reason}}' ];
			$replacements = [
				self::get_booking_reason( $booking, 'cancellation' ),
				self::get_booking_reason( $booking, 'reschedule' ),
			];

			return str_replace( $needles, $replacements, $text );
		}

		public static function add_reason_booking_vars() {
			echo '<li><span class="var-label">' . esc_html__( 'Cancellation Reason:', 'latepoint-pro-features' ) . '</span> <span class="var-code os-click-to-copy">{{cancellation_reason}}</span></li>';
			echo '<li><span class="var-label">' . esc_html__( 'Reschedule Reason:', 'latepoint-pro-features' ) . '</span> <span class="var-code os-click-to-copy">{{reschedule_reason}}</span></li>';
		}

		// --- Display ------------------------------------------------------------------------------

		/**
		 * The context that matches where the booking currently stands.
		 *
		 * @param OsBookingModel $booking
		 */
		protected static function get_active_context( $booking ): string {
			return ( LATEPOINT_BOOKING_STATUS_CANCELLED === $booking->status ) ? 'cancellation' : 'reschedule';
		}

		/**
		 * Only the reason matching the current status is shown. A booking rescheduled and then cancelled
		 * keeps both stored, but only the cancellation explains where it ended up.
		 *
		 * @return array<int, array{context: string, label: string, reason: string}>
		 */
		protected static function get_populated_booking_reasons( $booking ): array {
			if ( empty( $booking->id ) ) {
				return [];
			}
			$context = self::get_active_context( $booking );
			$reason  = self::get_booking_reason( $booking, $context );
			if ( '' === $reason ) {
				return [];
			}
			$labels = [
				'cancellation' => __( 'Cancellation Reason', 'latepoint-pro-features' ),
				'reschedule'   => __( 'Reschedule Reason', 'latepoint-pro-features' ),
			];

			return [
				[
					'context' => $context,
					'label'   => $labels[ $context ],
					'reason'  => $reason,
				],
			];
		}

		public static function output_booking_reasons_in_summary( $booking ) {
			foreach ( self::get_populated_booking_reasons( $booking ) as $item ) {
				?>
				<div class="booking-reason-info booking-reason-<?php echo esc_attr( $item['context'] ); ?>">
					<span class="booking-reason-label"><?php echo esc_html( $item['label'] ); ?>:</span>
					<span class="booking-reason-value"><?php echo esc_html( $item['reason'] ); ?></span>
				</div>
				<?php
			}
		}

		/**
		 * A field per enabled context, pre-filled. Only one applies at a time, so the script swaps them and
		 * the other is disabled as well as hidden, keeping a stale value out of the submit.
		 *
		 * @param OsBookingModel $booking
		 * @param string         $order_item_id
		 */
		public static function output_booking_reasons_in_admin_form( $booking, $order_item_id ) {
			if ( $booking->is_new_record() ) {
				return;
			}
			$labels         = [
				'cancellation' => __( 'Cancellation Reason', 'latepoint-pro-features' ),
				'reschedule'   => __( 'Reschedule Reason', 'latepoint-pro-features' ),
			];
			$active_context = self::get_active_context( $booking );

			foreach ( $labels as $context => $label ) {
				if ( ! self::is_enabled( $context ) ) {
					continue;
				}
				$atts = [ 'theme' => 'simple' ];
				// Reschedule applies on every status but one, so it carries the status it is excluded by.
				$wrapper_atts = ( 'cancellation' === $context )
					? [ 'data-reason-active-status' => LATEPOINT_BOOKING_STATUS_CANCELLED ]
					: [ 'data-reason-except-status' => LATEPOINT_BOOKING_STATUS_CANCELLED ];
				if ( $context !== $active_context ) {
					$wrapper_atts['style'] = 'display:none';
					$atts['disabled']      = 'disabled';
				}

				echo OsFormHelper::textarea_field(
					'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][' . $context . '_reason]',
					$label,
					self::get_booking_reason( $booking, $context ),
					$atts,
					$wrapper_atts
				);
			}
		}
	}

endif;
