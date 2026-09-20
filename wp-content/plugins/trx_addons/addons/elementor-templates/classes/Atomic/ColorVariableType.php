<?php
namespace TrxAddons\ElementorTemplates\Atomic;

use Elementor\Modules\AtomicWidgets\PropTypes\Contracts\Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\String_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Utils\Prop_Types_Schema_Extender;
use Elementor\Modules\Variables\Classes\Variable_Types_Registry;
use Elementor\Modules\Variables\Transformers\Global_Variable_Transformer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Prop type for the custom color variable.
 *
 * Mirrors the built-in 'global-color-variable' with a plugin-scoped key,
 * so ThemeREX color variables are distinguishable from Elementor's own
 * global color variables in the Variables registry and in serialized data.
 */
class ColorVariablePropType extends String_Prop_Type {
	public static function get_key(): string {
		return 'trx_addons-color-variable';
	}
}

/**
 * Style-schema extender for ColorVariablePropType.
 *
 * Traverses every entry in the atomic-widgets style schema and adds
 * ColorVariablePropType to every prop type that accepts
 * a color value (i.e. contains a Color_Prop_Type anywhere in its tree).
 */
class ColorVariableSchemaExtender extends Prop_Types_Schema_Extender {
	protected function get_prop_types_to_add( Prop_Type $prop_type ): array {
		return [ ColorVariablePropType::make() ];
	}
}

/**
 * ColorVariableType.
 *
 * Registers the 'trx_addons-color-variable' variable type with
 * Elementor's Variables system so it behaves exactly like the built-in
 * 'global-color-variable':
 *
 *  - Stored as a plain string color value.
 *  - Rendered to CSS as `var(--{label})` via Global_Variable_Transformer.
 *  - Accepted on every color-capable prop in the atomic-widgets style schema.
 *  - Included in the editor's quota config.
 */
class ColorVariableType {

	/**
	 * Constructor.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function __construct() {
		// Register the type in Elementor's Variable_Types_Registry.
		add_action( 'elementor/variables/register', [ $this, 'register_variable_type' ], 20 );

		// Register a transformer so the variable resolves to a CSS var() reference.
		add_action( 'elementor/atomic-widgets/styles/transformers/register', [ $this, 'register_transformer' ] );

		// Extend the style schema after Elementor's own augmentation (priority 20).
		add_filter( 'elementor/atomic-widgets/styles/schema', [ $this, 'extend_style_schema' ], 20 );

		// Register the type on the JS side and extend the quota config (priority 20,
		// after Elementor's own enqueue at priority 10).
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ], 20 );

		// Block REST API creation, update, delete and restore of system variables —
		// users must not create, modify or remove variables of this type; they are
		// managed programmatically by SystemColorVariables.
		add_filter( 'rest_request_before_callbacks', [ $this, 'block_rest_create' ], 10, 3 );
		add_filter( 'rest_request_before_callbacks', [ $this, 'block_rest_update' ], 10, 3 );
		add_filter( 'rest_request_before_callbacks', [ $this, 'block_rest_delete' ], 10, 3 );
		add_filter( 'rest_request_before_callbacks', [ $this, 'block_rest_restore' ], 10, 3 );

		// Filter batch operations: silently strip 'update', 'delete' and 'restore'
		// operations for our type so that inline-list saves (which use /batch) do
		// not persist label/value changes or remove system variables. After a batch
		// save Elementor calls service.load(), which reloads from the server and
		// reverts the React state back to the original values.
		add_filter( 'rest_request_before_callbacks', [ $this, 'filter_rest_batch' ], 10, 3 );
	}

	/**
	 * register_variable_type
	 *
	 * Adds ColorVariablePropType to Elementor's Variable_Types_Registry.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param Variable_Types_Registry $registry
	 */
	public function register_variable_type( Variable_Types_Registry $registry ): void {
		$registry->register(
			ColorVariablePropType::get_key(),
			new ColorVariablePropType()
		);
	}

	/**
	 * register_transformer
	 *
	 * Registers Global_Variable_Transformer for our variable type so that
	 * values stored as variable IDs are resolved to `var(--label)` CSS references.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param \Elementor\Modules\AtomicWidgets\PropsResolver\Transformers_Registry $transformers_registry
	 */
	public function register_transformer( $transformers_registry ): void {
		$transformers_registry->register(
			ColorVariablePropType::get_key(),
			new Global_Variable_Transformer()
		);
	}

	/**
	 * block_rest_create
	 *
	 * Intercepts REST API requests to the Elementor Variables create endpoint
	 * and rejects any attempt to create a variable of the system type
	 * 'trx_addons-color-variable'. Variables of this type are managed
	 * programmatically and must not be created by users.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param mixed           $response Current pre-dispatch response (null means proceed normally).
	 * @param array           $handler  Route handler array.
	 * @param WP_REST_Request $request  Current REST request.
	 * @return mixed Original $response or WP_Error if creation is blocked.
	 */
	public function block_rest_create( $response, $handler, $request ): mixed {
		if ( '/elementor/v1/variables/create' !== $request->get_route() ) {
			return $response;
		}

		if ( ColorVariablePropType::get_key() !== $request->get_param( 'type' ) ) {
			return $response;
		}

		return new \WP_Error(
			'trx_addons_system_variable',
			__( 'Variables of this type are managed programmatically and cannot be created manually.', 'trx-developer' ),
			[ 'status' => 403 ]
		);
	}

	/**
	 * block_rest_update
	 *
	 * Intercepts REST API requests to the Elementor Variables update endpoint
	 * and rejects any attempt to modify a variable of the system type
	 * 'trx_addons-color-variable'. Variables of this type are managed
	 * programmatically — their label and value must not be changed by users.
	 *
	 * The variable type is resolved by reading the Kit's '_elementor_global_variables'
	 * post meta, avoiding a dependency on an internal Elementor service.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param mixed           $response Current pre-dispatch response (null means proceed normally).
	 * @param array           $handler  Route handler array.
	 * @param WP_REST_Request $request  Current REST request.
	 * @return mixed Original $response or WP_Error if update is blocked.
	 */
	public function block_rest_update( $response, $handler, $request ): mixed {
		if ( '/elementor/v1/variables/update' !== $request->get_route() ) {
			return $response;
		}

		$id = $request->get_param( 'id' );
		if ( ! $id ) {
			return $response;
		}

		if ( ColorVariablePropType::get_key() !== $this->get_stored_variable_type( $id ) ) {
			return $response;
		}

		return new \WP_Error(
			'trx_addons_system_variable',
			__( 'Variables of this type are managed programmatically and cannot be modified manually.', 'trx-developer' ),
			[ 'status' => 403 ]
		);
	}

	/**
	 * block_rest_delete
	 *
	 * Intercepts REST API requests to the Elementor Variables delete endpoint
	 * and rejects any attempt to soft-delete a variable of the system type
	 * 'trx_addons-color-variable'. Variables of this type are managed
	 * programmatically — their lifecycle is driven by the theme color scheme,
	 * not by user action.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param mixed           $response Current pre-dispatch response (null means proceed normally).
	 * @param array           $handler  Route handler array.
	 * @param WP_REST_Request $request  Current REST request.
	 * @return mixed Original $response or WP_Error if deletion is blocked.
	 */
	public function block_rest_delete( $response, $handler, $request ): mixed {
		if ( '/elementor/v1/variables/delete' !== $request->get_route() ) {
			return $response;
		}

		$id = $request->get_param( 'id' );
		if ( ! $id ) {
			return $response;
		}

		if ( ColorVariablePropType::get_key() !== $this->get_stored_variable_type( $id ) ) {
			return $response;
		}

		return new \WP_Error(
			'trx_addons_system_variable',
			__( 'Variables of this type are managed programmatically and cannot be deleted manually.', 'trx-developer' ),
			[ 'status' => 403 ]
		);
	}

	/**
	 * block_rest_restore
	 *
	 * Intercepts REST API requests to the Elementor Variables restore endpoint
	 * and rejects any attempt to restore a variable of the system type
	 * 'trx_addons-color-variable'. Since our variables cannot be deleted
	 * through normal flows, restore is never a legitimate user action for them.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param mixed           $response Current pre-dispatch response (null means proceed normally).
	 * @param array           $handler  Route handler array.
	 * @param WP_REST_Request $request  Current REST request.
	 * @return mixed Original $response or WP_Error if restore is blocked.
	 */
	public function block_rest_restore( $response, $handler, $request ): mixed {
		if ( '/elementor/v1/variables/restore' !== $request->get_route() ) {
			return $response;
		}

		$id = $request->get_param( 'id' );
		if ( ! $id ) {
			return $response;
		}

		if ( ColorVariablePropType::get_key() !== $this->get_stored_variable_type( $id ) ) {
			return $response;
		}

		return new \WP_Error(
			'trx_addons_system_variable',
			__( 'Variables of this type are managed programmatically and cannot be modified manually.', 'trx-developer' ),
			[ 'status' => 403 ]
		);
	}

	/**
	 * filter_rest_batch
	 *
	 * Modifies the operations array of the Elementor Variables batch endpoint
	 * before it is processed, removing any 'update', 'delete' or 'restore'
	 * operation that targets a variable of the system type
	 * 'trx_addons-color-variable'.
	 *
	 * The inline Variables Manager uses the batch endpoint when the user clicks
	 * "Save". By stripping our type's mutating operations here, those changes are
	 * silently discarded. Elementor then calls service.load() after a successful
	 * batch, which reloads from the server and reverts the React UI to the original
	 * values — no error is shown to the user.
	 *
	 * Note: this modifies $request in-place and returns the original $response
	 * (null) so the request continues to the normal batch callback.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param mixed           $response Current pre-dispatch response (null means proceed normally).
	 * @param array           $handler  Route handler array.
	 * @param WP_REST_Request $request  Current REST request.
	 * @return mixed Unmodified $response (processing continues with the filtered request).
	 */
	public function filter_rest_batch( $response, $handler, $request ): mixed {
		if ( null !== $response ) {
			return $response; // Another filter already short-circuited the request.
		}

		if ( '/elementor/v1/variables/batch' !== $request->get_route() ) {
			return $response;
		}

		$operations = $request->get_param( 'operations' );
		if ( ! is_array( $operations ) ) {
			return $response;
		}

		$key           = ColorVariablePropType::get_key();
		$filterable_ops = [ 'update', 'delete', 'restore' ];
		$filtered      = array_values(
			array_filter(
				$operations,
				function ( $op ) use ( $key, $filterable_ops ) {
					// Keep malformed ops and 'create'/'reorder' — 'create' has no id,
					// and reordering system variables is harmless.
					if ( ! isset( $op['type'] ) || ! in_array( $op['type'], $filterable_ops, true ) ) {
						return true;
					}
					// Drop the op if it targets a system variable; keep otherwise.
					$id = $op['id'] ?? '';
					return $id && $key !== $this->get_stored_variable_type( $id );
				}
			)
		);

		$request->set_param( 'operations', $filtered );

		return $response;
	}

	/**
	 * get_stored_variable_type
	 *
	 * Returns the stored type key for a variable ID by reading the Kit's
	 * '_elementor_global_variables' post meta directly (no internal service dependency).
	 *
	 * @since 3.3.0
	 * @access private
	 *
	 * @param string $id Variable ID.
	 * @return string|null Type key, or null if the variable is not found.
	 */
	private function get_stored_variable_type( string $id ): ?string {
		$kit_id = (int) get_option( 'elementor_active_kit' );
		if ( ! $kit_id ) {
			return null;
		}

		$raw = get_post_meta( $kit_id, '_elementor_global_variables', true );
		if ( ! $raw ) {
			return null;
		}

		$data = json_decode( $raw, true );

		return $data['data'][ $id ]['type'] ?? null;
	}

	/**
	 * extend_style_schema
	 *
	 * Adds ColorVariablePropType to every color-capable prop type
	 * in the atomic-widgets style schema.
	 *
	 * Runs at priority 20, after Elementor's Style_Schema::augment() (priority 10),
	 * so Color_Prop_Type entries are already wrapped in Union_Prop_Type and
	 * the schema extender correctly detects and extends them.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param array $schema
	 * @return array
	 */
	public function extend_style_schema( array $schema ): array {
		return ( new ColorVariableSchemaExtender() )->get_extended_style_schema( $schema );
	}

	/**
	 * enqueue_editor_scripts
	 *
	 * Adds two inline scripts attached to elementor-v2-editor-variables:
	 *
	 * 1. Before: extends window.ElementorVariablesQuotaConfig so the Variables
	 *    Manager UI enforces the correct limit for our custom type.
	 *
	 * 2. After: registers the type in the JS Variable_Type_Registry by reusing
	 *    the UI components (ColorField, icon, startIcon) of the built-in
	 *    'global-color-variable' type and creating a new propTypeUtil with
	 *    our key via window.elementorV2.editorProps.createPropUtils().
	 *
	 * Runs at priority 20, after Elementor's own enqueue (priority 10).
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function enqueue_editor_scripts(): void {
		$key = ColorVariablePropType::get_key();

		// Set quota to a high value so canAdd() and canEdit() return true for this
		// type. This prevents Elementor from showing the "Upgrade to Pro" promotion
		// chip on system variable rows — the restriction is a system policy, not a
		// plan limitation. Actual saving is blocked via service.update interception
		// (JS) and REST API filters (PHP).
		wp_add_inline_script(
			'elementor-common',
			'window.ElementorVariablesQuotaConfig = window.ElementorVariablesQuotaConfig || {};'
			. ' window.ElementorVariablesQuotaConfig["' . $key . '"] = 100000;',
			'before'
		);

		// Register the type in the JS Variable_Type_Registry and intercept
		// service.update to silently discard any save attempt for our type.
		//
		// Editing flow in Elementor's Variables Manager:
		//   • Inline list edits  → saved via /batch. PHP filter_rest_batch()
		//     strips our type's update operations, so after the batch Elementor
		//     calls service.load() and the React state reverts to server values.
		//   • Modal edits        → saved via service.update() → /update. We
		//     monkey-patch service.update so it returns a fake "success" without
		//     touching the server. The modal closes; the list re-reads storage
		//     (unchanged) and shows the original values.
		//
		// Runs as an inline script *after* editor-variables.js, so init() has
		// already been called and service / variable types are fully initialised.
		wp_add_inline_script(
			'elementor-v2-editor-variables',
			'(function() {
				var React           = window.React;
				var editorVariables = window.elementorV2 && window.elementorV2.editorVariables;
				var editorProps     = window.elementorV2 && window.elementorV2.editorProps;
				var schema          = window.elementorV2 && window.elementorV2.schema;
				var ui              = window.elementorV2 && window.elementorV2.ui;
				var icons           = window.elementorV2 && window.elementorV2.icons;

				if ( ! React || ! editorVariables || ! editorProps || ! schema || ! ui || ! icons ) { return; }

				var KEY = ' . wp_json_encode( $key ) . ';

				// Inject CSS: show a default cursor on the label cell of system-variable
				// rows, signalling non-editability, but do NOT disable pointer events
				// — the Variables Manager attaches its own React Tooltip to the label
				// on mouseenter, and we want that tooltip to trigger on our rows too
				// (long labels like "theme-color-accent1" share a prefix and are only
				// distinguishable once the full label is visible in the tooltip).
				var _trxStyle = document.createElement( \'style\' );
				_trxStyle.textContent = \'tr:has([data-trx-system-var]) > td:nth-child(2) { cursor: default !important; }\';
				document.head.appendChild( _trxStyle );

				// Block the inline-edit double-click in the capture phase so that the
				// React handler installed by the Variables Manager never sees the event
				// for system-variable rows. Hover/mouseenter still propagate normally,
				// which preserves the Tooltip behaviour for truncated labels.
				document.addEventListener( \'dblclick\', function( e ) {
					var row = e.target && e.target.closest ? e.target.closest( \'tr\' ) : null;
					if ( ! row || ! row.querySelector( \'[data-trx-system-var]\' ) ) { return; }
					if ( ! e.target.closest( \'td:nth-child(2)\' ) ) { return; }
					e.stopImmediatePropagation();
					e.preventDefault();
				}, true );

				// ── Prop type utility ─────────────────────────────────────────
				var propTypeUtil = editorProps.createPropUtils( KEY, schema.z.string() );

				// ── Color input field ─────────────────────────────────────────
				// Mirrors ColorField from editor-variables internals using the
				// publicly available window.elementorV2.ui.UnstableColorField.
				var ColorField = function( props ) {
					var localState = React.useState( props.value );

					return React.createElement( ui.UnstableColorField, {
						id: "trx-developer-color-variable-field",
						size: "tiny",
						fullWidth: true,
						value: localState[0],
						disabled: 1,
					} );
				};

				// ── Color swatch indicator ────────────────────────────────────
				var ColorIndicator = ui.styled( ui.UnstableColorIndicator )( function( t ) {
					return {
						borderRadius: t.theme.shape.borderRadius / 2 + "px",
						marginRight: t.theme.spacing( 0.25 ),
					};
				} );

				// ── Register the type ─────────────────────────────────────────
				// No defaultValue — intentionally omitted so that this type is
				// excluded from the "Add variable" menu (filtered by !!defaultValue).
				editorVariables.registerVariableType( {
					key:                 KEY,
					//defaultValue:        \'#ffffff\',
					icon:                icons.BrushIcon,
					startIcon:           function( props ) {
						// Wrap in a span with a data attribute so the CSS :has() rule can
						// identify this row and disable pointer events on the label cell.
						return React.createElement(
							\'span\',
							{ \'data-trx-system-var\': \'true\', style: { display: \'contents\' } },
							React.createElement( ColorIndicator, {
								size: "inherit",
								component: "span",
								value: props.value,
							} )
						);
					},
					valueField:          ColorField,
					propTypeUtil:        propTypeUtil,
					fallbackPropTypeUtil: editorProps.colorPropTypeUtil,
					variableType:        "color",
					isActive:            0,
				} );

				// ── Block modal saves via service.update() ────────────────────
				// The Variables Manager saves modal edits by calling
				// service.update(id, { label, value }). We replace this method so
				// that any update targeting a system variable resolves immediately
				// with the original data — no HTTP request is made, the service
				// storage is not modified, and the modal closes cleanly. The list
				// re-reads from storage and shows the unchanged original values.
				var svc = editorVariables.service;
				if ( svc && typeof svc.update === "function" ) {
					var _origUpdate = svc.update.bind( svc );
					svc.update = function( id, data ) {
						var vars = svc.variables ? svc.variables() : {};
						if ( vars[ id ] && vars[ id ].type === KEY ) {
							// Fake a successful response. extractId() will pull
							// out the id; the .then() callback closes the modal.
							return Promise.resolve( { id: id, variable: vars[ id ] } );
						}
						return _origUpdate( id, data );
					};
				}

				// ── Refresh variables cache when Site Settings panel closes ───
				// Changing a theme color in Site Settings updates the preview
				// immediately (live CSS), but does NOT update the in-memory
				// editorVariables.service cache, so the swatches next to color
				// controls keep showing the old value until the editor reloads.
				// Listening to panel/global/close covers both flows — Save+Close
				// and bare Close without save: we unconditionally ask the service
				// to re-fetch /variables/list, and SystemColorVariables::sync()
				// runs on rest_api_init to guarantee the response reflects the
				// current theme color scheme.
				if ( window.$e && window.$e.commands && typeof window.$e.commands.on === "function" ) {
					window.$e.commands.on( "run:after", function( component, command ) {
						if ( "panel/global/close" !== command ) { return; }
						if ( svc && typeof svc.load === "function" ) {
							svc.load();
						}
					} );
				}
			})();',
			'after'
		);
	}
}
