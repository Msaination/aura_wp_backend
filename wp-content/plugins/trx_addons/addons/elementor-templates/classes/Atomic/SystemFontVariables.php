<?php
namespace TrxAddons\ElementorTemplates\Atomic;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * SystemFontVariables
 *
 * Seeds and synchronises read-only Elementor Atomic global variables for every
 * font-family defined by the theme. Each theme font becomes a variable of type
 * 'trx_addons-font-variable' whose label is 'tf-{key}' where {key} is the
 * font-family variable key produced by trx_addons_get_theme_font_vars()
 * (e.g. 'h1_font-family').
 *
 * Elementor's Global_Variable_Transformer renders a variable as
 * 'var(--{label})' in CSS, so a variable labelled 'tf-h1_font-family'
 * resolves to 'var(--tf-h1_font-family)'. An inline <style> block added in
 * add_inline_style_with_vars() maps '--tf-{key}' → 'var(--theme-font-{key})',
 * so the final font-family always follows the active theme typography.
 *
 * The variables are read-only: creation/modification/deletion is blocked by
 * FontVariableType. This class only writes to the storage programmatically.
 *
 * @since 3.3.0
 */
class SystemFontVariables {

	const ID_PREFIX = 'trx-gf-';
	const META_KEY  = '_elementor_global_variables';

	/**
	 * Request-level guard to avoid running sync more than once per request.
	 *
	 * @var bool
	 */
	private static $synced_in_request = false;

	/**
	 * Constructor.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function __construct() {
		// Ensure variables are populated when the editor page loads…
		// add_action( 'admin_init', array( $this, 'sync' ) );

		// …and when the editor issues REST requests (variables list, batch, etc.).
		// add_action( 'rest_api_init', array( $this, 'sync' ) );

		// Re-sync when the theme color scheme is saved via Site Settings > Global Colors in the Elementor Editor.
		add_filter( 'elementor/documents/ajax_save/return_data', array( $this, 'sync_on_save_global_fonts' ), 20, 2 );

		// Re-sync when the theme options (including fonts) are saved.
		$theme_slug = str_replace( '-', '_', get_template() );
		add_action( "{$theme_slug}_action_just_save_options", array( $this, 'sync_on_theme_save' ), 20 );

		// Add a custom style mapping --tf-{key} → var(--theme-font-{key}).
		add_action( 'wp_footer', array( $this, 'add_inline_style_with_vars' ) );
	}

	/**
	 * sync
	 *
	 * Idempotently ensures the active Kit's global variables storage contains
	 * one system variable per theme font. Safe to call on every request — the
	 * meta is only written when the stored state differs from the theme fonts.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function sync() {
		if ( self::$synced_in_request ) {
			return;
		}
		self::$synced_in_request = true;

		if ( ! function_exists( 'trx_addons_exists_elementor' ) || ! trx_addons_exists_elementor() ) {
			return;
		}

		$kit_id = (int) get_option( 'elementor_active_kit' );
		if ( ! $kit_id ) {
			return;
		}

		if ( ! function_exists( 'trx_addons_get_theme_font_vars' ) ) {
			return;
		}

		$theme_fonts = trx_addons_get_theme_font_vars();
		if ( empty( $theme_fonts ) || ! is_array( $theme_fonts ) ) {
			return;
		}

		$this->sync_kit( $kit_id, $theme_fonts );
	}

	/**
	 * sync_on_theme_save
	 *
	 * Forced sync after Theme Options are saved. Clears the request-level guard
	 * so sync runs even if it already ran earlier in the request.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function sync_on_theme_save() {
		self::$synced_in_request = false;
		$this->sync();
	}

	/**
	 * sync_on_save_global_fonts
	 *
	 * Forced sync after Site Settings - Global Fonts are saved. Clears the request-level guard
	 * so sync runs even if it already ran earlier in the request.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function sync_on_save_global_fonts( $response_data, $document ) {
		self::$synced_in_request = false;
		$this->sync();
		return $response_data;
	}

	/**
	 * sync_kit
	 *
	 * Reads the active Kit's variables meta, merges the theme fonts into it
	 * and writes the meta back — but only if anything actually changed.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @param int   $kit_id      Active Elementor Kit ID.
	 * @param array $theme_fonts Associative array produced by trx_addons_get_theme_font_vars().
	 */
	private function sync_kit( $kit_id, $theme_fonts ) {
		$raw       = get_post_meta( $kit_id, self::META_KEY, true );
		$data      = array();
		$watermark = 0;
		$version   = 1;

		if ( is_string( $raw ) && '' !== $raw ) {
			$decoded = json_decode( $raw, true );
			if ( is_array( $decoded ) ) {
				$data      = is_array( $decoded['data'] ?? null ) ? $decoded['data'] : array();
				$watermark = (int) ( $decoded['watermark'] ?? 0 );
				$version   = (int) ( $decoded['version'] ?? 1 );
			}
		}

		$type_key = FontVariablePropType::get_key();
		$now      = gmdate( 'Y-m-d H:i:s' );
		$changed  = false;

		// Highest existing order among active variables — new records get higher values.
		$order = 0;
		foreach ( $data as $var ) {
			if ( ! empty( $var['deleted'] ) ) {
				continue;
			}
			if ( isset( $var['order'] ) && (int) $var['order'] > $order ) {
				$order = (int) $var['order'];
			}
		}

		// Map existing system variables by label for O(1) lookup.
		$existing = array();
		foreach ( $data as $id => $var ) {
			if ( ( $var['type'] ?? '' ) !== $type_key ) {
				continue;
			}
			$label = (string) ( $var['label'] ?? '' );
			if ( '' !== $label ) {
				$existing[ $label ] = $id;
			}
		}

		$expected = array();

		foreach ( $theme_fonts as $slug => $meta ) {
			$label = $this->get_var_label( $slug, $meta );
			if ( '' === $label ) {
				continue;
			}
			$value = (string) ( $meta['font'] ?? '' );
			if ( '' === $value ) {
				continue;
			}

			$expected[ $label ] = true;

			if ( isset( $existing[ $label ] ) ) {
				// Update the existing system variable if the font value or deleted state changed.
				$id     = $existing[ $label ];
				$record = $data[ $id ];
				if ( ( $record['value'] ?? '' ) !== $value || ! empty( $record['deleted'] ) ) {
					$data[ $id ]['value']      = $value;
					$data[ $id ]['updated_at'] = $now;
					unset( $data[ $id ]['deleted'], $data[ $id ]['deleted_at'] );
					$changed = true;
				}
				continue;
			}

			// Skip creation if some other active variable already occupies this label.
			if ( $this->label_exists( $data, $label ) ) {
				continue;
			}

			$id          = $this->generate_unique_id( $data, (string) $slug );
			$data[ $id ] = array(
				'type'       => $type_key,
				'label'      => $label,
				'value'      => $value,
				'order'      => ++$order,
				'created_at' => $now,
				'updated_at' => $now,
			);
			$changed     = true;
		}

		// Soft-delete system variables that no longer correspond to a theme font.
		foreach ( $existing as $label => $id ) {
			if ( isset( $expected[ $label ] ) ) {
				continue;
			}
			if ( ! empty( $data[ $id ]['deleted'] ) ) {
				continue;
			}
			$data[ $id ]['deleted']    = true;
			$data[ $id ]['deleted_at'] = $now;
			$changed                   = true;
		}

		if ( ! $changed ) {
			return;
		}

		$watermark = ( PHP_INT_MAX === $watermark ) ? 1 : $watermark + 1;

		$record = array(
			'data'      => $data,
			'watermark' => $watermark,
			'version'   => $version,
		);

		update_post_meta( $kit_id, self::META_KEY, wp_slash( wp_json_encode( $record ) ) );

		// Invalidate the kit CSS so the new/updated variables are re-rendered.
		update_post_meta( $kit_id, '_elementor_css', '' );
		// Clear Files Manager cache was moved to a separate method hooked to the '{template}_action_save_options',
		// because inside the action '{template}_action_just_save_options' the files_manager instance is not yet available
		// if ( class_exists( '\Elementor\Plugin' ) ) {
		// 	\Elementor\Plugin::instance()->files_manager->clear_cache();
		// }
	}

	/**
	 * label_exists
	 *
	 * Returns true if any active (non-deleted) variable already uses the label.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @param array  $data  Variables data map keyed by ID.
	 * @param string $label Label to look up (case-insensitive).
	 * @return bool
	 */
	private function label_exists( $data, $label ) {
		foreach ( $data as $var ) {
			if ( ! empty( $var['deleted'] ) ) {
				continue;
			}
			if ( strcasecmp( (string) ( $var['label'] ?? '' ), $label ) === 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * generate_unique_id
	 *
	 * Produces a stable, unique variable ID for a theme font slug. Stable IDs
	 * are important so that widgets referencing a system variable keep working
	 * across re-syncs.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @param array  $data Existing variables map.
	 * @param string $slug Theme font slug (e.g. 'h1_font-family').
	 * @return string
	 */
	private function generate_unique_id( $data, $slug ) {
		$base   = self::ID_PREFIX . sanitize_key( $slug );
		$id     = $base;
		$suffix = 1;
		while ( isset( $data[ $id ] ) ) {
			$id = $base . '-' . ( $suffix++ );
		}
		return $id;
	}

	/**
	 * Returns the label for a theme font variable based on its slug and metadata.
	 *
	 * The label uses the short 'tf-' prefix (e.g. 'tf-h1_font-family') to keep the
	 * Variables Manager list compact and easy to scan — long CSS variable names
	 * like 'theme-font-h1_font-family' would share a prefix and be hard to tell
	 * apart in the terse list UI.
	 *
	 * @param string $slug The slug of the theme font (e.g. 'h1_font-family').
	 * @param array  $meta The metadata associated with the theme font.
	 *
	 * @return string The generated label for the theme font variable (e.g. 'tf-h1_font-family').
	 */
	private function get_var_label( $slug, $meta ) {
		return 'tf-' . sanitize_key( $slug );
	}

	/**
	 * Add an inline <style> block that maps --tf-{key} → var(--theme-font-{key}),
	 * so the short-named system variables defined above resolve to the theme's
	 * own CSS custom properties at render time.
	 */
	public function add_inline_style_with_vars() {
		if ( ! function_exists( 'trx_addons_get_theme_font_vars' ) ) {
			return;
		}
		$theme_fonts = trx_addons_get_theme_font_vars();
		if ( empty( $theme_fonts ) || ! is_array( $theme_fonts ) ) {
			return;
		}
		$css = ':root, body, :where(.editor-styles-wrapper) {';
		foreach ( $theme_fonts as $key => $meta ) {
			$css .= "--tf-{$key}: var(--theme-font-{$key});";
		}
		$css .= '}';
		trx_addons_add_inline_css( $css );
	}
}
