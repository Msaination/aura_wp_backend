<?php
/**
 * Plugin Name: Aura Headless API
 * Description: Stable REST endpoints for the Aura Spa headless booking experience.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Aura_Headless_API {
	private const REST_NAMESPACE = 'aura/v1';
	private const PAY_LATER_ONLY = true;

	public static function init() {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/services',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_services' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/services/(?P<service_id>\d+)/therapists',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_therapists' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'service_id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
						'validate_callback' => static function ( $value ) {
							return is_numeric( $value ) && (int) $value > 0;
						},
					),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/availability',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_availability' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'serviceId' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'therapistId' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'duration' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'startDate' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'days' => array(
						'default'           => 14,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/customer-fields',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_customer_fields' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/review',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_review' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'serviceId' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'duration' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/checkout-intents',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( self::class, 'create_checkout_intent' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/checkout/(?P<token>[A-Za-z0-9]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'bootstrap_checkout' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function get_services() {
		if ( ! class_exists( 'OsServiceModel' ) || ! class_exists( 'OsServiceCategoryModel' ) ) {
			return new WP_Error(
				'aura_latepoint_unavailable',
				__( 'The booking service is temporarily unavailable.', 'aura-headless-api' ),
				array( 'status' => 503 )
			);
		}

		$categories       = ( new OsServiceCategoryModel() )
			->order_by( 'order_number ASC, name ASC' )
			->get_results_as_models();
		$response_groups  = array();
		$total_services   = 0;
		$category_results = is_array( $categories ) ? $categories : array();

		foreach ( $category_results as $category ) {
			$services = $category->get_active_services();

			if ( ! is_array( $services ) || empty( $services ) ) {
				continue;
			}

			$mapped_services  = array_map( array( self::class, 'map_service' ), $services );
			$total_services  += count( $mapped_services );
			$response_groups[] = array(
				'id'               => (int) $category->id,
				'parentId'         => $category->parent_id ? (int) $category->parent_id : null,
				'name'             => (string) $category->name,
				'shortDescription' => wp_strip_all_tags( (string) $category->short_description ),
				'imageUrl'         => $category->selection_image_id ? $category->get_selection_image_url() : null,
				'services'         => $mapped_services,
			);
		}

		$uncategorized = ( new OsServiceModel() )
			->where( array( 'category_id' => array( 'OR' => array( 0, 'IS NULL' ) ) ) )
			->should_be_active()
			->should_not_be_hidden()
			->order_by( 'order_number ASC, name ASC' )
			->get_results_as_models();

		if ( is_array( $uncategorized ) && ! empty( $uncategorized ) ) {
			$mapped_services  = array_map( array( self::class, 'map_service' ), $uncategorized );
			$total_services  += count( $mapped_services );
			$response_groups[] = array(
				'id'               => 0,
				'parentId'         => null,
				'name'             => __( 'Other Treatments', 'aura-headless-api' ),
				'shortDescription' => '',
				'imageUrl'         => null,
				'services'         => $mapped_services,
			);
		}

		$response = rest_ensure_response(
			array(
				'categories' => $response_groups,
				'total'      => $total_services,
			)
		);
		$response->header( 'Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=600' );

		return $response;
	}

	public static function get_therapists( $request ) {
		if ( ! class_exists( 'OsServiceModel' ) || ! class_exists( 'OsAgentModel' ) || ! class_exists( 'OsConnectorHelper' ) ) {
			return new WP_Error(
				'aura_latepoint_unavailable',
				__( 'The booking service is temporarily unavailable.', 'aura-headless-api' ),
				array( 'status' => 503 )
			);
		}

		$service_id = absint( $request['service_id'] );
		$service    = new OsServiceModel( $service_id );

		if ( ! $service->exists() || ! $service->is_active() || $service->is_hidden() ) {
			return new WP_Error(
				'aura_service_not_found',
				__( 'The requested treatment was not found.', 'aura-headless-api' ),
				array( 'status' => 404 )
			);
		}

		$agent_ids = OsConnectorHelper::get_connected_object_ids(
			'agent_id',
			array( 'service_id' => $service_id )
		);
		$agents    = array();

		if ( is_array( $agent_ids ) && ! empty( $agent_ids ) ) {
			$agent_models = ( new OsAgentModel() )
				->where_in( 'id', $agent_ids )
				->should_be_active()
				->order_by( 'first_name ASC, last_name ASC' )
				->get_results_as_models();

			if ( is_array( $agent_models ) ) {
				$agents = array_map( array( self::class, 'map_agent' ), $agent_models );
			}
		}

		$response = rest_ensure_response(
			array(
				'service'    => array(
					'id'              => (int) $service->id,
					'name'            => (string) $service->name,
					'durationMinutes' => (int) $service->duration,
				),
				'allowAny'   => OsSettingsHelper::is_on( 'allow_any_agent' ),
				'therapists' => $agents,
				'total'      => count( $agents ),
			)
		);
		$response->header( 'Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=600' );

		return $response;
	}

	public static function get_availability( $request ) {
		if ( ! class_exists( 'OsServiceModel' ) || ! class_exists( 'OsAgentModel' ) || ! class_exists( 'OsConnectorHelper' ) ) {
			return new WP_Error(
				'aura_latepoint_unavailable',
				__( 'The booking service is temporarily unavailable.', 'aura-headless-api' ),
				array( 'status' => 503 )
			);
		}

		$service_id   = absint( $request['serviceId'] );
		$therapist_id = sanitize_text_field( $request['therapistId'] );
		$duration      = absint( $request['duration'] );
		$start_date    = sanitize_text_field( $request['startDate'] );
		$days          = min( 31, max( 1, absint( $request['days'] ) ) );
		$service       = new OsServiceModel( $service_id );

		if ( ! $service->exists() || ! $service->is_active() || $service->is_hidden() ) {
			return new WP_Error(
				'aura_service_not_found',
				__( 'The requested treatment was not found.', 'aura-headless-api' ),
				array( 'status' => 404 )
			);
		}

		$start_date_object = OsWpDateTime::os_createFromFormat( 'Y-m-d', $start_date );
		if ( ! $start_date_object || $start_date_object->format( 'Y-m-d' ) !== $start_date ) {
			return new WP_Error(
				'aura_invalid_start_date',
				__( 'The start date must use Y-m-d format.', 'aura-headless-api' ),
				array( 'status' => 400 )
			);
		}

		$allowed_durations = array_map(
			static function ( $duration_option ) {
				return (int) $duration_option['duration'];
			},
			$service->get_all_durations_arr()
		);
		if ( ! in_array( $duration, $allowed_durations, true ) ) {
			return new WP_Error(
				'aura_invalid_duration',
				__( 'The selected duration is not available for this treatment.', 'aura-headless-api' ),
				array( 'status' => 400 )
			);
		}

		$connected_agent_ids = array_map(
			'intval',
			OsConnectorHelper::get_connected_object_ids(
				'agent_id',
				array( 'service_id' => $service_id )
			)
		);
		$is_any_agent = 'any' === $therapist_id;

		if ( $is_any_agent ) {
			if ( ! OsSettingsHelper::is_on( 'allow_any_agent' ) ) {
				return new WP_Error(
					'aura_any_therapist_disabled',
					__( 'Any available therapist is not enabled.', 'aura-headless-api' ),
					array( 'status' => 400 )
				);
			}
			$ability_agent_id = 0;
			$therapist_summary = array(
				'id'   => 'any',
				'name' => __( 'Any available therapist', 'aura-headless-api' ),
			);
		} else {
			$ability_agent_id = absint( $therapist_id );
			$agent            = new OsAgentModel( $ability_agent_id );

			if ( ! $agent->exists() || ! in_array( $ability_agent_id, $connected_agent_ids, true ) || LATEPOINT_AGENT_STATUS_DISABLED === $agent->status ) {
				return new WP_Error(
					'aura_therapist_not_available',
					__( 'The selected therapist is not available for this treatment.', 'aura-headless-api' ),
					array( 'status' => 400 )
				);
			}

			$therapist_summary = array(
				'id'   => (int) $agent->id,
				'name' => (string) $agent->name_for_front,
			);
		}

		if ( ! class_exists( 'LatePointAbilityGetAvailableSlots' ) ) {
			require_once LATEPOINT_ABSPATH . 'lib/abilities/abstract-ability.php';
			require_once LATEPOINT_ABSPATH . 'lib/abilities/calendar/abstract-calendar-ability.php';
			require_once LATEPOINT_ABSPATH . 'lib/abilities/calendar/get-available-slots.php';
		}

		$ability      = new LatePointAbilityGetAvailableSlots();
		$availability = array();

		for ( $offset = 0; $offset < $days; $offset++ ) {
			$date        = ( clone $start_date_object )->modify( '+' . $offset . ' days' )->format( 'Y-m-d' );
			$slot_result = $ability->execute(
				array(
					'service_id' => $service_id,
					'agent_id'   => $ability_agent_id,
					'location_id' => 0,
					'duration'   => $duration,
					'date'       => $date,
				)
			);

			if ( is_wp_error( $slot_result ) ) {
				return $slot_result;
			}

			$slots_by_time = array();
			foreach ( $slot_result['slots'] as $slot ) {
				$key = (int) $slot['start_time'] . ':' . (int) $slot['end_time'];
				if ( ! isset( $slots_by_time[ $key ] ) ) {
					$slots_by_time[ $key ] = array(
						'startMinutes' => (int) $slot['start_time'],
						'endMinutes'   => (int) $slot['end_time'],
						'therapistIds' => array(),
					);
				}
				$slots_by_time[ $key ]['therapistIds'][] = (int) $slot['agent_id'];
			}

			$availability[] = array(
				'date'  => $date,
				'slots' => array_values( $slots_by_time ),
			);
		}

		$response = rest_ensure_response(
			array(
				'service'    => array(
					'id'   => (int) $service->id,
					'name' => (string) $service->name,
				),
				'therapist'  => $therapist_summary,
				'duration'    => $duration,
				'timezone'    => OsTimeHelper::get_wp_timezone_name(),
				'dates'       => $availability,
			)
		);
		$response->header( 'Cache-Control', 'no-store, max-age=0' );

		return $response;
	}

	public static function get_customer_fields() {
		if ( ! class_exists( 'OsSettingsHelper' ) ) {
			return new WP_Error(
				'aura_latepoint_unavailable',
				__( 'The booking service is temporarily unavailable.', 'aura-headless-api' ),
				array( 'status' => 503 )
			);
		}

		$settings = OsSettingsHelper::get_default_fields_for_customer();
		$fields   = array();

		foreach ( array( 'first_name', 'last_name', 'email', 'phone', 'notes' ) as $name ) {
			if ( empty( $settings[ $name ]['active'] ) ) {
				continue;
			}

			$fields[] = array(
				'name'     => $name,
				'label'    => wp_strip_all_tags( (string) $settings[ $name ]['label'] ),
				'type'     => 'notes' === $name ? 'textarea' : ( 'email' === $name ? 'email' : ( 'phone' === $name ? 'tel' : 'text' ) ),
				'required' => 'email' === $name || ! empty( $settings[ $name ]['required'] ),
			);
		}

		$response = rest_ensure_response( array( 'fields' => $fields ) );
		$response->header( 'Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=600' );

		return $response;
	}

	public static function get_review( $request ) {
		if ( ! class_exists( 'OsServiceModel' ) || ! class_exists( 'OsBookingModel' ) || ! class_exists( 'OsBookingHelper' ) ) {
			return new WP_Error(
				'aura_latepoint_unavailable',
				__( 'The booking service is temporarily unavailable.', 'aura-headless-api' ),
				array( 'status' => 503 )
			);
		}

		$service_id = absint( $request['serviceId'] );
		$duration   = absint( $request['duration'] );
		$service    = new OsServiceModel( $service_id );

		if ( ! $service->exists() || ! $service->is_active() || $service->is_hidden() ) {
			return new WP_Error(
				'aura_service_not_found',
				__( 'The requested treatment was not found.', 'aura-headless-api' ),
				array( 'status' => 404 )
			);
		}

		$allowed_durations = array_map(
			static function ( $duration_option ) {
				return (int) $duration_option['duration'];
			},
			$service->get_all_durations_arr()
		);
		if ( ! in_array( $duration, $allowed_durations, true ) ) {
			return new WP_Error(
				'aura_invalid_duration',
				__( 'The selected duration is not available for this treatment.', 'aura-headless-api' ),
				array( 'status' => 400 )
			);
		}

		$booking             = new OsBookingModel();
		$booking->service_id = $service_id;
		$booking->duration   = $duration;
		$total               = (float) OsBookingHelper::calculate_full_amount_for_booking( $booking );
		$payment_methods     = array();
		if ( class_exists( 'OsPaymentsHelper' ) && OsPaymentsHelper::is_local_payments_enabled() ) {
			$payment_methods[] = array(
				'id'          => LATEPOINT_PAYMENT_METHOD_LOCAL,
				'name'        => __( 'Pay Later', 'aura-headless-api' ),
				'description' => __( 'Book now and pay locally at your appointment.', 'aura-headless-api' ),
			);
		}

		if (
			! self::PAY_LATER_ONLY &&
			class_exists( 'OsPaymentsHelper' ) &&
			OsPaymentsHelper::is_payment_processor_enabled( 'woocommerce' ) &&
			function_exists( 'WC' ) && WC()->payment_gateways()
		) {
			foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
				if ( 'yes' !== $gateway->enabled ) {
					continue;
				}

				$payment_methods[] = array(
					'id'          => sanitize_key( $gateway->id ),
					'name'        => wp_strip_all_tags( $gateway->get_title() ),
					'description' => wp_strip_all_tags( $gateway->get_description() ),
				);
			}
		}

		$response = rest_ensure_response(
			array(
				'service' => array(
					'id'       => (int) $service->id,
					'name'     => (string) $service->name,
					'duration' => $duration,
				),
				'total' => array(
					'amount'    => $total,
					'formatted' => OsMoneyHelper::format_price( $total ),
				),
				'paymentMethods' => $payment_methods,
			)
		);
		$response->header( 'Cache-Control', 'no-store, max-age=0' );

		return $response;
	}

	public static function create_checkout_intent( $request ) {
		if (
			! class_exists( 'OsBookingModel' ) ||
			! class_exists( 'OsCartModel' ) ||
			! class_exists( 'OsCartItemModel' ) ||
			! class_exists( 'OsOrderIntentHelper' )
		) {
			return new WP_Error(
				'aura_checkout_unavailable',
				__( 'Secure checkout is temporarily unavailable.', 'aura-headless-api' ),
				array( 'status' => 503 )
			);
		}

		$rate_limit_key = 'aura_checkout_rate_' . md5( self::get_request_ip() );
		$request_count  = (int) get_transient( $rate_limit_key );
		if ( $request_count >= 10 ) {
			return new WP_Error(
				'aura_checkout_rate_limited',
				__( 'Too many checkout attempts. Please wait a moment and try again.', 'aura-headless-api' ),
				array( 'status' => 429 )
			);
		}
		set_transient( $rate_limit_key, $request_count + 1, MINUTE_IN_SECONDS );

		$payload = $request->get_json_params();
		if ( ! is_array( $payload ) || ! empty( $payload['company'] ) ) {
			return new WP_Error(
				'aura_invalid_checkout',
				__( 'The checkout request is invalid.', 'aura-headless-api' ),
				array( 'status' => 400 )
			);
		}

		$idempotency_key = isset( $payload['idempotencyKey'] ) ? sanitize_text_field( $payload['idempotencyKey'] ) : '';
		if ( ! preg_match( '/^[A-Za-z0-9-]{20,64}$/', $idempotency_key ) ) {
			return new WP_Error(
				'aura_invalid_idempotency_key',
				__( 'The checkout request key is invalid.', 'aura-headless-api' ),
				array( 'status' => 400 )
			);
		}

		$idempotency_cache_key = 'aura_checkout_idem_' . md5( $idempotency_key );
		$existing_result       = get_transient( $idempotency_cache_key );
		if ( is_array( $existing_result ) && ! empty( $existing_result['response'] ) ) {
			return rest_ensure_response( $existing_result['response'] );
		}
		$existing_token = $existing_result;
		if ( is_string( $existing_token ) && get_transient( 'aura_checkout_' . $existing_token ) ) {
			return rest_ensure_response(
				array( 'checkoutUrl' => rest_url( self::REST_NAMESPACE . '/checkout/' . $existing_token ) )
			);
		}

		$service_id   = absint( $payload['serviceId'] ?? 0 );
		$agent_id     = absint( $payload['therapistId'] ?? 0 );
		$duration     = absint( $payload['duration'] ?? 0 );
		$start_date   = sanitize_text_field( $payload['startDate'] ?? '' );
		$start_time   = isset( $payload['startMinutes'] ) && is_numeric( $payload['startMinutes'] ) ? (int) $payload['startMinutes'] : -1;
		$gateway_id   = sanitize_key( $payload['paymentMethod'] ?? '' );
		$is_pay_later = 'local' === $gateway_id;
		$customer_data = is_array( $payload['customer'] ?? null ) ? $payload['customer'] : array();
		$service      = new OsServiceModel( $service_id );
		$agent        = new OsAgentModel( $agent_id );

		if ( self::PAY_LATER_ONLY && ! $is_pay_later ) {
			return new WP_Error(
				'aura_invalid_payment_method',
				__( 'Pay Later is the only payment method currently available.', 'aura-headless-api' ),
				array( 'status' => 400 )
			);
		}

		if ( ! $service->exists() || ! $service->is_active() || $service->is_hidden() ) {
			return new WP_Error( 'aura_service_not_found', __( 'The requested treatment was not found.', 'aura-headless-api' ), array( 'status' => 404 ) );
		}

		$allowed_durations = array_map(
			static function ( $duration_option ) {
				return (int) $duration_option['duration'];
			},
			$service->get_all_durations_arr()
		);
		$connected_agent_ids = array_map(
			'intval',
			OsConnectorHelper::get_connected_object_ids( 'agent_id', array( 'service_id' => $service_id ) )
		);
		if (
			! in_array( $duration, $allowed_durations, true ) ||
			! $agent->exists() ||
			! in_array( $agent_id, $connected_agent_ids, true ) ||
			LATEPOINT_AGENT_STATUS_DISABLED === $agent->status ||
			! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) ||
			$start_time >= 24 * 60
		) {
			return new WP_Error(
				'aura_invalid_appointment',
				__( 'The selected appointment is invalid.', 'aura-headless-api' ),
				array( 'status' => 400 )
			);
		}

		if ( $is_pay_later ) {
			if ( ! class_exists( 'OsPaymentsHelper' ) || ! OsPaymentsHelper::is_local_payments_enabled() ) {
				return new WP_Error(
					'aura_invalid_payment_method',
					__( 'Pay Later is currently unavailable.', 'aura-headless-api' ),
					array( 'status' => 400 )
				);
			}
		} else {
			if ( ! class_exists( 'OsPaymentsWoocommerceHelper' ) || ! function_exists( 'WC' ) ) {
				return new WP_Error(
					'aura_checkout_unavailable',
					__( 'Secure checkout is temporarily unavailable.', 'aura-headless-api' ),
					array( 'status' => 503 )
				);
			}
			$gateways = WC()->payment_gateways()->payment_gateways();
			if ( empty( $gateways[ $gateway_id ] ) || 'yes' !== $gateways[ $gateway_id ]->enabled ) {
				return new WP_Error(
					'aura_invalid_payment_method',
					__( 'The selected payment method is unavailable.', 'aura-headless-api' ),
					array( 'status' => 400 )
				);
			}
		}

		$booking               = new OsBookingModel();
		$booking->service_id    = $service_id;
		$booking->agent_id      = $agent_id;
		$booking->location_id   = LATEPOINT_ANY_LOCATION;
		$booking->start_date    = $start_date;
		$booking->start_time    = $start_time;
		$booking->duration      = $duration;
		$booking->status        = $service->get_default_booking_status();
		$capacity_min           = ! empty( $service->capacity_min ) ? (int) $service->capacity_min : 1;
		$capacity_max           = ! empty( $service->capacity_max ) ? (int) $service->capacity_max : 1;
		$total_attendees        = isset( $payload['totalAttendees'] ) ? absint( $payload['totalAttendees'] ) : 0;
		$booking->total_attendees = $total_attendees > 0 ? max( $capacity_min, min( $total_attendees, $capacity_max ) ) : $capacity_min;
		if ( class_exists( 'OsServiceExtrasHelper' ) ) {
			$booking_service_extras = self::normalize_booking_service_extras( $service_id, $payload['addonServices'] ?? [] );
			if ( ! empty( $booking_service_extras ) ) {
				$booking->service_extras = $booking_service_extras;
			}
		}
		$booking->set_buffers();
		$booking->calculate_end_date_and_time();

		if ( ! $booking->is_bookable( array( 'skip_customer_check' => true ) ) ) {
			return new WP_Error(
				'aura_slot_unavailable',
				__( 'This appointment time is no longer available.', 'aura-headless-api' ),
				array( 'status' => 409 )
			);
		}

		$customer_result = self::find_or_create_customer( $customer_data );
		if ( is_wp_error( $customer_result ) ) {
			return $customer_result;
		}

		$booking->customer_id = (int) $customer_result->id;
		if ( ! $booking->is_bookable() ) {
			return new WP_Error(
				'aura_slot_unavailable',
				__( 'This appointment time is no longer available.', 'aura-headless-api' ),
				array( 'status' => 409 )
			);
		}

		$cart                    = new OsCartModel();
		$cart->payment_time      = $is_pay_later ? LATEPOINT_PAYMENT_TIME_LATER : LATEPOINT_PAYMENT_TIME_NOW;
		$cart->payment_portion   = LATEPOINT_PAYMENT_PORTION_FULL;
		$cart->payment_method    = $is_pay_later ? LATEPOINT_PAYMENT_METHOD_LOCAL : 'woocommerce';
		$cart->payment_processor = $is_pay_later ? 'latepoint' : 'woocommerce';
		$cart_item               = new OsCartItemModel();
		$cart_item->variant      = LATEPOINT_ITEM_VARIANT_BOOKING;
		$cart_item->item_data    = wp_json_encode( $booking->generate_params_for_booking_form() );
		$cart->add_item( $cart_item, false, true );

		$order_intent = OsOrderIntentHelper::create_or_update_order_intent(
			$cart,
			array(),
			array(),
			esc_url_raw( $payload['returnUrl'] ?? '' ),
			$customer_result->id
		);

		if ( $order_intent->is_new_record() || ! $order_intent->is_bookable() ) {
			return new WP_Error(
				'aura_checkout_intent_failed',
				__( 'The appointment could not be prepared for checkout.', 'aura-headless-api' ),
				array( 'status' => 409 )
			);
		}

		if ( $is_pay_later ) {
			$order_id = $order_intent->convert_to_order();
			if ( ! $order_id ) {
				return new WP_Error(
					'aura_booking_failed',
					__( 'The appointment could not be booked. Please choose another time.', 'aura-headless-api' ),
					array( 'status' => 409 )
				);
			}

			$order             = new OsOrderModel( $order_id );
			$order_bookings    = $order->get_bookings_from_order_items( true );
			$confirmed_booking = $order_bookings ? reset( $order_bookings ) : false;
			if ( ! $confirmed_booking || $confirmed_booking->is_new_record() ) {
				foreach ( $order->get_items( true ) as $order_item ) {
					if ( ! $order_item->is_booking() ) {
						continue;
					}
					$item_data = json_decode( $order_item->item_data, true );
					if ( ! is_array( $item_data ) || empty( $item_data['service_id'] ) ) {
						continue;
					}
					$candidate_booking = OsBookingHelper::build_booking_model_from_item_data( $item_data );
					if ( ! empty( $candidate_booking->service_id ) && ! empty( $candidate_booking->start_date ) && ! empty( $candidate_booking->start_time ) ) {
						$confirmed_booking = $candidate_booking;
						break;
					}
				}
			}
			if ( ! $confirmed_booking || empty( $confirmed_booking->service_id ) || empty( $confirmed_booking->start_date ) || empty( $confirmed_booking->start_time ) ) {
				return new WP_Error(
					'aura_confirmation_unavailable',
					__( 'Your appointment was booked, but its confirmation could not be loaded.', 'aura-headless-api' ),
					array( 'status' => 500 )
				);
			}

			$ical_string            = OsBookingHelper::generate_ical_event_string( $confirmed_booking );
			$calendar_qr            = class_exists( 'chillerlan\\QRCode\\QRCode' )
				? ( new \chillerlan\QRCode\QRCode() )->render( $ical_string )
				: '';
			$addon_service_details  = self::get_booking_service_extra_details( $confirmed_booking );
			$total_duration_minutes = (int) $confirmed_booking->duration + array_sum(
				array_map( static fn( $item ) => (int) ( $item['durationMinutes'] ?? 0 ), $addon_service_details )
			);
			$response = array(
				'confirmation' => array(
					'code'               => (string) $confirmed_booking->booking_code,
					'serviceName'        => (string) $service->name,
					'primaryServiceName' => (string) $service->name,
					'therapistName'      => (string) $agent->name_for_front,
					'date'               => (string) $confirmed_booking->start_date,
					'startMinutes'       => (int) $confirmed_booking->start_time,
					'endMinutes'         => (int) $confirmed_booking->end_time,
					'duration'           => (int) $confirmed_booking->duration,
					'totalDurationMinutes' => $total_duration_minutes,
					'status'             => (string) $confirmed_booking->status,
					'paymentMethod'      => __( 'Pay Later', 'aura-headless-api' ),
					'total'              => OsMoneyHelper::format_price( (float) $order->total ),
					'totalPrice'         => (float) $order->total,
					'addonServices'      => $addon_service_details,
					'calendarQr'         => $calendar_qr,
					'calendarDataUri'    => 'data:text/calendar;base64,' . base64_encode( $ical_string ),
				),
			);
			set_transient( $idempotency_cache_key, array( 'response' => $response ), 15 * MINUTE_IN_SECONDS );

			return rest_ensure_response( $response );
		}

		$token = wp_generate_password( 48, false, false );
		set_transient(
			'aura_checkout_' . $token,
			array(
				'gatewayId'     => $gateway_id,
				'orderIntentKey' => $order_intent->intent_key,
			),
			15 * MINUTE_IN_SECONDS
		);
		set_transient( $idempotency_cache_key, $token, 15 * MINUTE_IN_SECONDS );

		return rest_ensure_response(
			array( 'checkoutUrl' => rest_url( self::REST_NAMESPACE . '/checkout/' . $token ) )
		);
	}

	public static function bootstrap_checkout( $request ) {
		$token   = sanitize_text_field( $request['token'] );
		$payload = get_transient( 'aura_checkout_' . $token );

		if ( ! is_array( $payload ) || empty( $payload['orderIntentKey'] ) || empty( $payload['gatewayId'] ) ) {
			return new WP_Error(
				'aura_checkout_expired',
				__( 'This checkout link has expired. Please return to Aura Spa and try again.', 'aura-headless-api' ),
				array( 'status' => 410 )
			);
		}

		$order_intent = OsOrderIntentHelper::get_order_intent_by_intent_key( $payload['orderIntentKey'] );
		if ( $order_intent->is_new_record() || $order_intent->is_converted() || ! $order_intent->is_bookable() ) {
			return new WP_Error(
				'aura_slot_unavailable',
				__( 'This appointment time is no longer available.', 'aura-headless-api' ),
				array( 'status' => 409 )
			);
		}

		if ( null === WC()->session || null === WC()->cart ) {
			wc_load_cart();
		}

		$gateways = WC()->payment_gateways()->payment_gateways();
		if ( empty( $gateways[ $payload['gatewayId'] ] ) || 'yes' !== $gateways[ $payload['gatewayId'] ]->enabled ) {
			return new WP_Error(
				'aura_invalid_payment_method',
				__( 'The selected payment method is unavailable.', 'aura-headless-api' ),
				array( 'status' => 400 )
			);
		}

		$latepoint_cart = $order_intent->build_cart_object();
		WC()->cart->empty_cart();
		wc_clear_notices();
		OsPaymentsWoocommerceHelper::populate_checkout_customer( $order_intent );

		foreach ( $latepoint_cart->get_items() as $cart_item ) {
			$model = OsPaymentsWoocommerceHelper::get_model_from_order_item( $cart_item );
			if ( ! $model ) {
				continue;
			}

			$product_id = OsPaymentsWoocommerceHelper::get_or_create_woo_product_by_model( $model );
			$price      = $cart_item->subtotal;
			$added      = WC()->cart->add_to_cart(
				$product_id,
				1,
				0,
				array(),
				array(
					'lp_custom_price'            => OsPaymentsWoocommerceHelper::convert_charge_amount( $price ),
					'_latepoint_order_intent_key' => $order_intent->intent_key,
				)
			);

			if ( ! $added ) {
				return new WP_Error(
					'aura_checkout_cart_failed',
					__( 'The appointment could not be added to checkout.', 'aura-headless-api' ),
					array( 'status' => 500 )
				);
			}
		}

		WC()->session->set( 'chosen_payment_method', $payload['gatewayId'] );
		WC()->cart->calculate_totals();

		$response = new WP_REST_Response( null, 302 );
		$response->header( 'Location', wc_get_checkout_url() );
		$response->header( 'Cache-Control', 'no-store, max-age=0' );

		return $response;
	}

	private static function normalize_booking_service_extras( $service_id, $addon_services ) {
		if ( ! is_array( $addon_services ) || empty( $addon_services ) ) {
			return [];
		}
		$normalized = [];
		foreach ( $addon_services as $addon_service ) {
			if ( ! is_array( $addon_service ) ) {
				continue;
			}
			$service_extra_id = absint( $addon_service['serviceId'] ?? $addon_service['id'] ?? 0 );
			if ( ! $service_extra_id ) {
				continue;
			}
			if ( ! class_exists( 'OsServiceExtraModel' ) ) {
				continue;
			}
			$service_extra = new OsServiceExtraModel( $service_extra_id );
			if ( ! $service_extra->exists() || ! $service_extra->has_service( (int) $service_id ) ) {
				continue;
			}
			$quantity = max( 1, absint( $addon_service['quantity'] ?? 1 ) );
			$normalized[ (string) $service_extra_id ] = $quantity;
		}
		return $normalized;
	}

	private static function get_booking_service_extra_details( $booking ) {
		if ( ! class_exists( 'OsServiceExtrasHelper' ) || ! class_exists( 'OsServiceExtraModel' ) ) {
			return [];
		}
		$service_extras = OsServiceExtrasHelper::get_service_extras_for_booking( $booking );
		if ( empty( $service_extras ) ) {
			return [];
		}
		$extra_models = ( new OsServiceExtraModel() )
			->where( [ 'id' => array_keys( $service_extras ) ] )
			->get_results_as_models();
		if ( ! is_array( $extra_models ) || empty( $extra_models ) ) {
			return [];
		}

		$details = [];
		foreach ( $extra_models as $extra_model ) {
			$quantity = max( 1, (int) ( $service_extras[ $extra_model->id ] ?? 1 ) );
			$details[] = array(
				'serviceId'        => (int) $extra_model->id,
				'serviceName'      => (string) $extra_model->name,
				'durationMinutes'  => (int) $extra_model->duration * $quantity,
				'price'            => (float) $extra_model->charge_amount * $quantity,
				'amount'           => (float) $extra_model->charge_amount * $quantity,
				'quantity'         => $quantity,
				'type'             => 'extra',
			);
		}
		return $details;
	}

	private static function find_or_create_customer( $customer_data ) {
		$field_settings = OsSettingsHelper::get_default_fields_for_customer();
		$customer_params = array(
			'first_name' => sanitize_text_field( $customer_data['first_name'] ?? '' ),
			'last_name'  => sanitize_text_field( $customer_data['last_name'] ?? '' ),
			'email'      => sanitize_email( $customer_data['email'] ?? '' ),
			'phone'      => OsUtilHelper::sanitize_phone_number( $customer_data['phone'] ?? '' ),
			'notes'      => sanitize_textarea_field( $customer_data['notes'] ?? '' ),
		);

		foreach ( $field_settings as $name => $settings ) {
			if ( ! empty( $settings['active'] ) && ! empty( $settings['required'] ) && empty( $customer_params[ $name ] ) ) {
				return new WP_Error(
					'aura_customer_details_required',
					sprintf( __( '%s is required.', 'aura-headless-api' ), wp_strip_all_tags( $settings['label'] ) ),
					array( 'status' => 400 )
				);
			}
		}

		if ( empty( $customer_params['email'] ) || ! is_email( $customer_params['email'] ) ) {
			return new WP_Error(
				'aura_invalid_customer_email',
				__( 'A valid email address is required.', 'aura-headless-api' ),
				array( 'status' => 400 )
			);
		}

		$customer = false;
		$merge_by = OsSettingsHelper::get_settings_value( 'default_contact_merge_behavior', 'email' );

		// The headless booking flow should reuse a matching LatePoint customer record
		// instead of forcing a WordPress sign-in when the same contact details are
		// already present. The frontend API does not have a customer login flow and
		// should attach the existing record to the booking rather than reject it.
		if ( 'email' === $merge_by && ! empty( $customer_params['email'] ) ) {
			$customer = ( new OsCustomerModel() )->where( array( 'email' => $customer_params['email'] ) )->set_limit( 1 )->get_results_as_models();
		} elseif ( 'phone' === $merge_by && ! empty( $customer_params['phone'] ) ) {
			$customer = ( new OsCustomerModel() )->where( array( 'phone' => $customer_params['phone'] ) )->set_limit( 1 )->get_results_as_models();
		}

		if ( $customer ) {
			return $customer;
		}

		if ( OsAuthHelper::is_customer_auth_enabled() ) {
			if ( OsSettingsHelper::is_on( 'steps_require_setting_password' ) || OsSettingsHelper::is_on( 'require_otp_for_new_contacts' ) ) {
				return new WP_Error(
					'aura_customer_verification_required',
					__( 'Customer verification must be completed before checkout.', 'aura-headless-api' ),
					array( 'status' => 409 )
				);
			}
		}

		$customer = new OsCustomerModel();
		$customer->set_data( $customer_params, LATEPOINT_PARAMS_SCOPE_PUBLIC );
		if ( ! $customer->save() ) {
			$message = $customer->get_error_messages();
			return new WP_Error(
				'aura_customer_invalid',
				is_array( $message ) ? implode( ', ', $message ) : $message,
				array( 'status' => 400 )
			);
		}

		do_action( 'latepoint_customer_created', $customer );
		return $customer;
	}

	private static function get_request_ip() {
		return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
	}

	private static function map_service( $service ) {
		$durations = array_map(
			static function ( $duration ) {
				$amount = (float) $duration['charge_amount'];

				return array(
					'id'             => (string) $duration['id'],
					'name'           => (string) $duration['name'],
					'durationMinutes' => (int) $duration['duration'],
					'price'          => $amount,
					'formattedPrice' => OsMoneyHelper::format_price( $amount ),
				);
			},
			$service->get_all_durations_arr()
		);
		$display_price = $service->price_min > 0 ? (float) $service->price_min : (float) $service->charge_amount;

		return array(
			'id'               => (int) $service->id,
			'categoryId'       => (int) $service->category_id,
			'name'             => (string) $service->name,
			'shortDescription' => wp_strip_all_tags( (string) $service->short_description ),
			'imageUrl'         => $service->selection_image_id ? $service->get_selection_image_url() : null,
			'durationMinutes'  => (int) $service->duration,
			'capacityMin'      => (int) ( $service->capacity_min ?? 1 ),
			'capacityMax'      => (int) ( $service->capacity_max ?? 1 ),
			'price'            => array(
				'amount'    => $display_price,
				'formatted' => OsMoneyHelper::format_price( $display_price ),
				'min'       => (float) $service->price_min,
				'max'       => (float) $service->price_max,
				'isVariable' => (bool) $service->is_price_variable || (float) $service->price_min !== (float) $service->price_max,
			),
			'durations'        => $durations,
			'extras'           => self::map_service_extras( $service ),
		);
	}

	private static function map_service_extras( $service ) {
		if ( ! class_exists( 'OsServiceExtraModel' ) || ! class_exists( 'OsServiceExtrasConnectorHelper' ) ) {
			return array();
		}

		$extra_ids = OsServiceExtrasConnectorHelper::get_connected_extras_ids_to_service( (int) $service->id );
		if ( empty( $extra_ids ) ) {
			return array();
		}

		$extras = ( new OsServiceExtraModel() )
			->where( array( 'id' => $extra_ids ) )
			->should_be_active()
			->get_results_as_models();

		if ( ! is_array( $extras ) || empty( $extras ) ) {
			return array();
		}

		return array_values( array_map( array( self::class, 'map_service_extra' ), $extras ) );
	}

	private static function map_service_extra( $extra ) {
		$amount = (float) ( $extra->charge_amount ?? 0 );

		return array(
			'id'               => (int) $extra->id,
			'name'             => (string) $extra->name,
			'shortDescription' => wp_strip_all_tags( (string) ( $extra->short_description ?? '' ) ),
			'durationMinutes'  => (int) ( $extra->duration ?? 0 ),
			'price'            => array(
				'amount'    => $amount,
				'formatted' => OsMoneyHelper::format_price( $amount ),
			),
		);
	}

	private static function map_agent( $agent ) {
		return array(
			'id'          => (int) $agent->id,
			'name'        => (string) $agent->name_for_front,
			'title'       => (string) $agent->title,
			'bio'         => wp_strip_all_tags( (string) $agent->bio ),
			'initials'    => (string) $agent->get_initials(),
			'avatarUrl'   => $agent->avatar_image_id ? $agent->get_avatar_url() : null,
			'bioImageUrl' => $agent->bio_image_id ? $agent->get_bio_image_url() : null,
			'features'    => array_map(
				static function ( $feature ) {
					return array(
						'label' => sanitize_text_field( (string) $feature['label'] ),
						'value' => sanitize_text_field( (string) $feature['value'] ),
					);
				},
				$agent->get_features_arr()
			),
		);
	}
}

Aura_Headless_API::init();