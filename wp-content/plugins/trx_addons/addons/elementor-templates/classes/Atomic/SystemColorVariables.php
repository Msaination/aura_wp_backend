<?php
namespace TrxAddons\ElementorTemplates\Atomic;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * SystemColorVariables
 *
 * Seeds and synchronises read-only Elementor Atomic global variables for every
 * color defined in the theme's active color scheme. Each theme color becomes a
 * variable of type 'trx_addons-color-variable' whose label mirrors the theme's
 * CSS variable name without the leading '--' (e.g. 'theme-color-accent1').
 *
 * Elementor's Global_Variable_Transformer renders a variable as
 * 'var(--{label})' in CSS, so a variable labelled 'theme-color-accent1'
 * resolves to 'var(--theme-color-accent1)' — the very CSS custom property
 * defined by the theme, so the final color always follows the active scheme.
 *
 * The variables are read-only: creation/modification/deletion is blocked by
 * ColorVariableType. This class only writes to the storage programmatically.
 *
 * @since 3.3.0
 */
class SystemColorVariables {

	const ID_PREFIX = 'trx-gc-';
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
		add_filter( 'elementor/documents/ajax_save/return_data', array( $this, 'sync_on_save_global_colors' ), 20, 2 );

		// Re-sync when the theme color scheme is saved via Theme Options.
		$theme_slug = str_replace( '-', '_', get_template() );
		add_action( "{$theme_slug}_action_just_save_options", array( $this, 'sync_on_theme_save' ), 20 );

		// Add a custom styles with a theme colors as an Elementor variable colors
		add_action( 'wp_footer', array( $this, 'add_inline_style_with_vars' ) );
	}

	/**
	 * sync
	 *
	 * Idempotently ensures the active Kit's global variables storage contains
	 * one system variable per theme color. Safe to call on every request — the
	 * meta is only written when the stored state differs from the theme colors.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function sync() {
		if ( self::$synced_in_request ) {
			return;
		}
		self::$synced_in_request = true;

		// No Elementor — nothing to seed.
		if ( ! function_exists( 'trx_addons_exists_elementor' ) || ! trx_addons_exists_elementor() ) {
			return;
		}

		$kit_id = (int) get_option( 'elementor_active_kit' );
		if ( ! $kit_id ) {
			return;
		}

		if ( ! function_exists( 'trx_addons_get_theme_color_vars' ) ) {
			return;
		}

		$theme_colors = trx_addons_get_theme_color_vars();
		if ( empty( $theme_colors ) || ! is_array( $theme_colors ) ) {
			return;
		}

		$this->sync_kit( $kit_id, $theme_colors );
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
	 * sync_on_save_global_colors
	 *
	 * Forced sync after Site Settings - Global Colors are saved. Clears the request-level guard
	 * so sync runs even if it already ran earlier in the request.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function sync_on_save_global_colors( $response_data, $document ) {
		self::$synced_in_request = false;
		$this->sync();
		return $response_data;
	}

	/**
	 * sync_kit
	 *
	 * Reads the active Kit's variables meta, merges the theme colors into it
	 * and writes the meta back — but only if anything actually changed.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @param int   $kit_id       Active Elementor Kit ID.
	 * @param array $theme_colors Associative array produced by trx_addons_get_theme_color_vars().
	 */
	private function sync_kit( $kit_id, $theme_colors ) {
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

		$type_key = ColorVariablePropType::get_key();
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

		foreach ( $theme_colors as $slug => $meta ) {
			$label = $this->get_var_label( $slug, $meta );
			if ( '' === $label ) {
				continue;
			}
			$value = (string) ( $meta['color'] ?? '' );
			if ( '' === $value ) {
				continue;
			}

			$expected[ $label ] = true;

			if ( isset( $existing[ $label ] ) ) {
				// Update the existing system variable if the color or deleted state changed.
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

		// Soft-delete system variables that no longer correspond to a theme color
		// (e.g. the color scheme was edited and a slug was removed).
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

		// wp_slash avoids meta unslashing eating JSON escape sequences.
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
	 * @param string $label Label to look up (case-insensitive, matching Elementor's duplicate check).
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
	 * Produces a stable, unique variable ID for a theme color slug. Stable IDs
	 * are important so that widgets referencing a system variable keep working
	 * across re-syncs.
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @param array  $data Existing variables map.
	 * @param string $slug Theme color slug.
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
	 * Returns the label for a theme color variable based on its slug and metadata.
	 * 
	 * The label is generated in a user-friendly format (e.g. 'tc-accent1') to make it easier for users to identify the variables in Elementor's editor,
	 * as opposed to using the raw CSS variable names which can be long and similar to each other.
	 * 
	 * @param string $slug The slug of the theme color (e.g. 'accent1').
	 * @param array $meta The metadata associated with the theme color, which may include the
	 * 
	 * @return string The generated label for the theme color variable (e.g. 'tc-accent1').
	 */
	private function get_var_label( $slug, $meta ) {
		// Way 1: The label is the CSS variable name without the leading '--' (e.g. 'theme-color-accent1').
		// $label = empty( $meta['variable'] ) ? '' : ltrim( (string) $meta['variable'], '-' );
		// Way 2: The label is 'tc-{slug}' (e.g. 'tc-accent1').
		// It's more user-friendly, because the list of variables in Elementor's editor is very terse and the CSS variable names can be long and similar to each other.
		$label = 'tc-' . sanitize_key( $slug );
		return $label;
	}

	/**
	 * Add a custom styles with a theme colors as an Elementor variable colors
	 */
	public function add_inline_style_with_vars() {
		$schemes = trx_addons_get_theme_color_schemes();
		$default_scheme = trx_addons_get_theme_option( 'color_scheme', 'default' );
		if ( ! empty( $schemes ) && is_array( $schemes ) && isset( $schemes[ $default_scheme ] ) ) {
			$css = ".scheme_{$default_scheme},body.scheme_{$default_scheme},.scheme_{$default_scheme}:where(.editor-styles-wrapper) {";
			foreach ( $schemes[ $default_scheme ]['colors'] as $key => $value ) {
				$css .= "--tc-{$key}: var(--theme-color-{$key});";
			}
			$css .= "}";
			trx_addons_add_inline_css( $css );
		}
	}
}
