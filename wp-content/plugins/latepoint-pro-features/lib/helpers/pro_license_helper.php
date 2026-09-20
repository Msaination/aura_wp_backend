<?php

class OsProLicenseHelper {

	// Number of consecutive unreachable/error revalidation attempts to tolerate before
	// giving up and surfacing it to the admin. A license server outage must never look
	// like an invalid license, so this only ever affects the notice, never `is_active_license`.
	const MAX_VALIDATION_FAILURES_BEFORE_NOTICE = 3;

	public static function init() {
		add_filter( 'cron_schedules', [ __CLASS__, 'add_license_validation_cron_schedule' ] );
		// Periodic re-check against the license server, so a cloned/moved site's license
		// doesn't stay "active" forever just because the settings row was copied.
		add_action( 'latepoint_validate_license', [ __CLASS__, 'revalidate_license' ] );
		add_action( 'admin_init', [ __CLASS__, 'on_admin_init' ] );
		add_action( 'admin_notices', [ __CLASS__, 'license_domain_mismatch_notice' ] );
	}

	/**
	 * Everything the license needs to do on an admin page load, in order.
	 */
	public static function on_admin_init() {
		// Cheap, no-HTTP check that catches a domain change immediately on any admin page load.
		self::check_domain_binding();

		// Covers installs that were already active before license revalidation existed and so
		// never ran `on_activate()` to schedule the cron.
		self::ensure_validation_cron_scheduled();
	}

	/**
	 * WP core only ships hourly/twicedaily/daily/weekly - registers the interval
	 * license revalidation runs on, since none of those match.
	 */
	public static function add_license_validation_cron_schedule( $schedules ) {
		if ( ! isset( $schedules[ LATEPOINT_LICENSE_VALIDATION_SCHEDULE ] ) ) {
			$schedules[ LATEPOINT_LICENSE_VALIDATION_SCHEDULE ] = array(
				'interval' => 2 * DAY_IN_SECONDS,
				'display'  => __( 'Once every 2 days', 'latepoint-pro-features' ),
			);
		}

		return $schedules;
	}

	/**
	 * Surfaces the license/domain mismatch state on every admin page, so it isn't
	 * something an admin only discovers by opening the License page. Only shown when a
	 * license key is on file and it's currently inactive - a site that never had a key
	 * (the common free-plugin-only case) gets no notice.
	 *
	 * If the license is still active but revalidation has failed repeatedly (e.g. the
	 * license server has been unreachable), show a softer warning instead - the license
	 * itself stays active either way, this is purely informational.
	 */
	public static function license_domain_mismatch_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$license = OsLicenseHelper::get_license_info();

		if ( empty( $license['license_key'] ) ) {
			return;
		}

		if ( ! self::is_license_active() ) {
			if ( self::has_domain_mismatch() ) {
				// Names LatePoint and bolds both domains directly in the sentence, since this
				// renders through wp_kses_post() (HTML-safe) - unlike the plain-text version
				// check_domain_binding() stores, which _license_form.php shows via esc_html().
				$message = sprintf(
					/* translators: 1: domain the license is registered to, 2: this site's domain */
					__( 'The LatePoint license is registered to %1$s, but this site is %2$s. Please reactivate the license for this site.', 'latepoint-pro-features' ),
					'<strong>' . esc_html( self::get_license_domain() ) . '</strong>',
					'<strong>' . esc_html( self::get_current_domain() ) . '</strong>'
				);
			} else {
				// Fixed, self-identifying message.
				$message = __( 'Your LatePoint license is not active. Please activate your license to continue receiving LatePoint updates and premium features.', 'latepoint-pro-features' );
			}

			printf(
				'<div class="notice notice-error"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
				wp_kses_post( $message ),
				esc_url( OsRouterHelper::build_link( [ 'updates', 'status' ] ) ),
				esc_html__( 'Manage License', 'latepoint-pro-features' )
			);
			return;
		}

		$failure_count = (int) OsSettingsHelper::get_settings_value( 'license_validation_failure_count', 0 );

		if ( $failure_count >= self::MAX_VALIDATION_FAILURES_BEFORE_NOTICE ) {
			printf(
				'<div class="notice notice-warning"><p>%1$s</p></div>',
				esc_html__( 'LatePoint has not been able to reach the license server recently to verify your license. We\'ll automatically try again later.', 'latepoint-pro-features' )
			);
		}
	}

	/**
	 * Normalizes a URL into the same bare-domain form the license server uses, so the two
	 * sides can be compared for a domain mismatch. Must stay in sync with `clean_url()` in
	 * the license server's Wp::LicensesController.
	 */
	public static function normalize_domain( $url ) {
		$domain = (string) $url;
		$domain = str_replace( 'www.', '', $domain );
		$domain = str_replace( 'https://', '', $domain );
		$domain = str_replace( 'http://', '', $domain );
		$domain = rtrim( $domain, '/' );

		return $domain;
	}

	public static function get_license_domain() {
		return OsSettingsHelper::get_settings_value( 'license_domain', '' );
	}

	public static function get_current_domain() {
		return self::normalize_domain( OsUtilHelper::get_site_url() );
	}

	/**
	 * Transforms a normalized domain into a form that survives a domain search-replace -
	 * the standard step in every WP migration/clone tool (wp search-replace, Duplicator, WP
	 * Migrate DB, All-in-One WP Migration, ...). Those tools do a literal substring replace
	 * of the old domain with the new one across the whole database, so a plain domain
	 * string stored here would get silently "corrected" to the new domain by the very tool
	 * used to clone the site - defeating the mismatch check it's cloned in the first place
	 * to catch. strrev() is a bijection (no two distinct domains can ever collide) and bears
	 * no textual resemblance to the real domain, so no literal-substring search-replace will
	 * ever touch it.
	 */
	public static function fingerprint_domain( $domain ) {
		return strrev( $domain );
	}

	public static function get_current_domain_fingerprint() {
		return self::fingerprint_domain( self::get_current_domain() );
	}

	/**
	 * True only when the license is bound to a domain and this site is a different one.
	 * An empty stored fingerprint means the license has never been bound yet (e.g. on
	 * upgrade from a version that didn't track it) - that is "unknown", not a mismatch.
	 * An empty *current* fingerprint gets the same treatment - if `get_site_url()` ever
	 * transiently returns empty, that's a local resolution problem, not evidence the
	 * license is actually invalid, and must never cause a false-positive deactivation.
	 *
	 * Compares fingerprints rather than the plaintext `license_domain` setting because the
	 * plaintext value is exactly the kind of literal domain string a site-clone
	 * search-replace rewrites - see fingerprint_domain().
	 */
	public static function has_domain_mismatch() {
		$stored_fingerprint = OsSettingsHelper::get_settings_value( 'license_domain_fingerprint', '' );

		if ( empty( $stored_fingerprint ) ) {
			return false;
		}

		$current_fingerprint = self::get_current_domain_fingerprint();

		return ! empty( $current_fingerprint ) && $stored_fingerprint !== $current_fingerprint;
	}

	/**
	 * Whether the stored license flag is active. This is a plain read - it does not
	 * recompute domain-mismatch live. Domain correctness is enforced by whichever process
	 * last wrote to `is_active_license`: `check_domain_binding()` (immediate, on
	 * `admin_init`) or `revalidate_license()` (periodic cron / live check on the License
	 * page) - both run before any caller of this method, so the flag is always already
	 * correct by the time it's read here. Callers that need to distinguish *why* a
	 * license is inactive (never activated vs. domain mismatch) should check
	 * `has_domain_mismatch()` separately, as `updates_controller::status()` already does
	 * for the License page's three-state view.
	 *
	 * Also requires a non-empty license key on file - `is_active_license` alone shouldn't
	 * be trusted in isolation (e.g. a direct DB edit or botched migration/import could in
	 * theory leave the flag set with no key behind it).
	 */
	public static function is_license_active() {
		return ! empty( OsLicenseHelper::get_license_key() ) && OsSettingsHelper::get_settings_value( 'is_active_license', 'no' ) == 'yes';
	}

	/**
	 * Clears the license (via the free plugin's helper) plus the domain-binding and
	 * revalidation bookkeeping this plugin adds on top of it.
	 */
	public static function clear_license_and_domain_binding() {
		OsLicenseHelper::clear_license();
		OsSettingsHelper::save_setting_by_name( 'license_domain', '' );
		OsSettingsHelper::save_setting_by_name( 'license_domain_fingerprint', '' );
		OsSettingsHelper::save_setting_by_name( 'license_last_validated_at', '' );
		OsSettingsHelper::save_setting_by_name( 'license_validation_failure_count', 0 );
		OsSettingsHelper::save_setting_by_name( 'license_activation_token', '' );
		OsSettingsHelper::save_setting_by_name( 'license_activation_timestamp', '' );
	}

	public static function verify_license_key( $license_data ) {

		$license_key         = trim( strtolower( $license_data['license_key'] ) );
		$license_owner_name  = $license_data['full_name'];
		$license_owner_email = $license_data['email'];

		if ( empty( $license_data['license_key'] ) ) {
			return [
				'status'  => LATEPOINT_STATUS_ERROR,
				'message' => __( 'Please enter your license key', 'latepoint-pro-features' ),
			];
		}

		$glued_license = implode( '*|||*', array( $license_owner_name, $license_owner_email, $license_key ) );

		OsSettingsHelper::save_setting_by_name( 'license', $glued_license );

		$is_valid_license = false;
		$current_domain   = self::get_current_domain();
		// connect
		$post = array(
			'_nonce'      => wp_create_nonce( 'activate_licence' ),
			'license_key' => $license_key,
			'domain'      => OsUtilHelper::get_site_url(),
			'user_ip'     => OsUtilHelper::get_user_ip(),
			'data'        => $glued_license,
		);

		$url = OsUpdatesHelper::get_remote_url( '/wp/activate-license' );


		$request = wp_remote_post(
			$url,
			array(
				'body'      => $post,
				'sslverify' => LATEPOINT_PRO_SSL_VERIFY,
			)
		);

		if ( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) === 200 ) {
			$response = json_decode( $request['body'], true );
			if ( empty( $response['status'] ) ) {
				$message = __( 'Unable to verify license. Please try again or contact us at license@latepoint.com. Error code: UDF732S83', 'latepoint-pro-features' );
			} else {
				$message = $response['message'];
				if ( $response['status'] == 200 ) {
					$is_valid_license = true;
				}
			}
		} else {
			if ( is_wp_error( $request ) ) {
				OsDebugHelper::log( 'Update plugin error', 'update_plugin_error', [ 'error' => $request->get_error_messages() ] );
			}
			$message = __( 'Unable to verify license. Please try again or contact us at license@latepoint.com. Error code: SUYF8362', 'latepoint-pro-features' );
		}

		if ( $is_valid_license ) {
			$status = LATEPOINT_STATUS_SUCCESS;
			OsSettingsHelper::save_setting_by_name( 'is_active_license', 'yes' );
			OsSettingsHelper::save_setting_by_name( 'license_status_message', $message );
			// Bind the license to whichever domain the server confirmed activation for
			// (falling back to the domain we sent if the server didn't echo one back).
			$activated_domain = ! empty( $response['domain'] ) ? self::normalize_domain( $response['domain'] ) : $current_domain;
			OsSettingsHelper::save_setting_by_name( 'license_domain', $activated_domain );
			OsSettingsHelper::save_setting_by_name( 'license_domain_fingerprint', self::fingerprint_domain( $activated_domain ) );
			OsSettingsHelper::save_setting_by_name( 'license_activation_token', isset( $response['token'] ) ? $response['token'] : '' );
			OsSettingsHelper::save_setting_by_name( 'license_activation_timestamp', isset( $response['timestamp'] ) ? $response['timestamp'] : '' );
			OsSettingsHelper::save_setting_by_name( 'license_last_validated_at', time() );
			OsSettingsHelper::save_setting_by_name( 'license_validation_failure_count', 0 );
		} else {
			$status = LATEPOINT_STATUS_ERROR;
			OsSettingsHelper::save_setting_by_name( 'is_active_license', 'no' );
			OsSettingsHelper::save_setting_by_name( 'license_status_message', $message );
			// A failed activation should never leave behind a stale domain binding.
			OsSettingsHelper::save_setting_by_name( 'license_domain', '' );
			OsSettingsHelper::save_setting_by_name( 'license_domain_fingerprint', '' );
		}

		return [
			'status'  => $status,
			'message' => $message,
		];
	}

	/**
	 * Re-validates the stored license against the license server (which itself defers to
	 * SureCart as the source of truth) for the current domain, without consuming a seat.
	 *
	 * A definitive "not activated for this domain" response deactivates the license locally
	 * - this is how a cloned/migrated site loses Pro access. A transport error or an
	 * "unknown" server response never deactivates anything; it only increments a failure
	 * counter, so a license-server outage can't take down a paying customer's site.
	 */
	public static function revalidate_license() {
		$license_key = OsLicenseHelper::get_license_key();

		if ( empty( $license_key ) ) {
			// No key on file - if the active flag somehow still says otherwise (a direct DB
			// edit, a botched migration/import touching one setting without the other), fix
			// it here instead of leaving it to drift indefinitely.
			if ( OsSettingsHelper::get_settings_value( 'is_active_license', 'no' ) === 'yes' ) {
				OsSettingsHelper::save_setting_by_name( 'is_active_license', 'no' );
			}
			return;
		}

		// This method only ever confirms or invalidates an already-active license - it
		// never activates one. Activation is exclusively verify_license_key()'s job,
		// triggered only by an admin explicitly submitting a key. If it's not active
		// locally, there's nothing to revalidate, and no server call to make.
		if ( OsSettingsHelper::get_settings_value( 'is_active_license', 'no' ) !== 'yes' ) {
			return;
		}

		$current_domain = OsUtilHelper::get_site_url();

		if ( empty( $current_domain ) ) {
			return;
		}

		$post = array(
			'license_key' => $license_key,
			'domain'      => $current_domain,
		);

		$url = OsUpdatesHelper::get_remote_url( '/wp/check-license' );

		$request = wp_remote_post(
			$url,
			array(
				'body'      => $post,
				'timeout'   => 15,
				'sslverify' => LATEPOINT_PRO_SSL_VERIFY,
			)
		);

		if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {
			if ( is_wp_error( $request ) ) {
				OsDebugHelper::log( 'License revalidation error', 'license_revalidation_error', [ 'error' => $request->get_error_messages() ] );
			}
			self::record_validation_failure();
			return;
		}

		$response = json_decode( $request['body'], true );

		if ( ! isset( $response['license_active'] ) ) {
			self::record_validation_failure();
			return;
		}

		if ( $response['license_active'] ) {
			// Already 'yes' by the guard above - nothing to activate here, only to confirm.
			OsSettingsHelper::save_setting_by_name( 'license_domain', self::get_current_domain() );
			OsSettingsHelper::save_setting_by_name( 'license_domain_fingerprint', self::get_current_domain_fingerprint() );
			OsSettingsHelper::save_setting_by_name( 'license_last_validated_at', time() );
			OsSettingsHelper::save_setting_by_name( 'license_validation_failure_count', 0 );
		} else {
			// Definitive: the server determined this license/domain pair is not valid -
			// `reason` (invalid_key / domain_not_activated) distinguishes why, `message`
			// is what gets shown to the admin.
			OsSettingsHelper::save_setting_by_name( 'is_active_license', 'no' );
			OsSettingsHelper::save_setting_by_name(
				'license_status_message',
				! empty( $response['message'] ) ? $response['message'] : __( 'This license is not activated for this domain.', 'latepoint-pro-features' )
			);
		}
	}

	/**
	 * Cheap, no-HTTP check that catches a cloned/moved site immediately, before the
	 * periodic revalidation cron would otherwise get to it. Runs on every `admin_init`,
	 * including unauthenticated `admin-ajax.php` requests - once a mismatch is recorded,
	 * skip re-writing the same settings on every subsequent request instead of doing so
	 * indefinitely until the license is manually reactivated.
	 */
	public static function check_domain_binding() {
		if ( OsSettingsHelper::get_settings_value( 'is_active_license', 'no' ) !== 'yes' ) {
			return;
		}

		if ( ! self::has_domain_mismatch() ) {
			return;
		}

		$old_domain = self::get_license_domain();
		$new_domain = self::get_current_domain();

		OsSettingsHelper::save_setting_by_name( 'is_active_license', 'no' );
		OsSettingsHelper::save_setting_by_name(
			'license_status_message',
			sprintf(
				/* translators: 1: domain the license is registered to, 2: this site's domain */
				__( 'This license is registered to %1$s but this site is %2$s. Please reactivate the license for this domain.', 'latepoint-pro-features' ),
				$old_domain,
				$new_domain
			)
		);
	}

	/**
	 * Covers installs that were already active before license revalidation existed - since
	 * the plugin was active before, `on_activate()` won't fire again to schedule the cron.
	 * Scheduling it at `time()` makes the first run due immediately, so a site with no
	 * domain binding yet gets validated on the next page load rather than waiting an
	 * interval.
	 */
	public static function ensure_validation_cron_scheduled() {
		if ( ! wp_next_scheduled( 'latepoint_validate_license' ) ) {
			wp_schedule_event( time(), LATEPOINT_LICENSE_VALIDATION_SCHEDULE, 'latepoint_validate_license' );
		}
	}

	protected static function record_validation_failure() {
		$failures = (int) OsSettingsHelper::get_settings_value( 'license_validation_failure_count', 0 );
		OsSettingsHelper::save_setting_by_name( 'license_validation_failure_count', $failures + 1 );
	}
}
