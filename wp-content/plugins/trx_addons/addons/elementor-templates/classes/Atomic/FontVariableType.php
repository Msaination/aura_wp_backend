<?php
namespace TrxAddons\ElementorTemplates\Atomic;

use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\String_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Union_Prop_Type;
use Elementor\Modules\Variables\Classes\Variable_Types_Registry;
use Elementor\Modules\Variables\Transformers\Global_Variable_Transformer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Prop type for the custom font variable.
 *
 * Mirrors the built-in 'global-font-variable' with a plugin-scoped key,
 * so ThemeREX font variables are distinguishable from Elementor's own
 * global font variables in the Variables registry and in serialized data.
 */
class FontVariablePropType extends String_Prop_Type {
	public static function get_key(): string {
		return 'trx_addons-font-variable';
	}
}

/**
 * FontVariableType.
 *
 * Registers the 'trx_addons-font-variable' variable type with
 * Elementor's Variables system so it behaves exactly like the built-in
 * 'global-font-variable':
 *
 *  - Stored as a plain string font-family value.
 *  - Rendered to CSS as `var(--{label})` via Global_Variable_Transformer.
 *  - Accepted on the 'font-family' prop of the atomic-widgets style schema.
 *  - Included in the editor's quota config.
 */
class FontVariableType {

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
		// managed programmatically by SystemFontVariables.
		add_filter( 'rest_request_before_callbacks', [ $this, 'block_rest_create' ], 10, 3 );
		add_filter( 'rest_request_before_callbacks', [ $this, 'block_rest_update' ], 10, 3 );
		add_filter( 'rest_request_before_callbacks', [ $this, 'block_rest_delete' ], 10, 3 );
		add_filter( 'rest_request_before_callbacks', [ $this, 'block_rest_restore' ], 10, 3 );

		// Filter batch operations: silently strip 'update', 'delete' and 'restore'
		// operations for our type so that inline-list saves (which use /batch) do
		// not persist label/value changes or remove system variables.
		add_filter( 'rest_request_before_callbacks', [ $this, 'filter_rest_batch' ], 10, 3 );
	}

	/**
	 * register_variable_type
	 *
	 * Adds FontVariablePropType to Elementor's Variable_Types_Registry.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param Variable_Types_Registry $registry
	 */
	public function register_variable_type( Variable_Types_Registry $registry ): void {
		$registry->register(
			FontVariablePropType::get_key(),
			new FontVariablePropType()
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
			FontVariablePropType::get_key(),
			new Global_Variable_Transformer()
		);
	}

	/**
	 * block_rest_create
	 *
	 * Intercepts REST API requests to the Elementor Variables create endpoint
	 * and rejects any attempt to create a variable of the system type
	 * 'trx_addons-font-variable'.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function block_rest_create( $response, $handler, $request ): mixed {
		if ( '/elementor/v1/variables/create' !== $request->get_route() ) {
			return $response;
		}

		if ( FontVariablePropType::get_key() !== $request->get_param( 'type' ) ) {
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
	 * 'trx_addons-font-variable'.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function block_rest_update( $response, $handler, $request ): mixed {
		if ( '/elementor/v1/variables/update' !== $request->get_route() ) {
			return $response;
		}

		$id = $request->get_param( 'id' );
		if ( ! $id ) {
			return $response;
		}

		if ( FontVariablePropType::get_key() !== $this->get_stored_variable_type( $id ) ) {
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
	 * 'trx_addons-font-variable'.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function block_rest_delete( $response, $handler, $request ): mixed {
		if ( '/elementor/v1/variables/delete' !== $request->get_route() ) {
			return $response;
		}

		$id = $request->get_param( 'id' );
		if ( ! $id ) {
			return $response;
		}

		if ( FontVariablePropType::get_key() !== $this->get_stored_variable_type( $id ) ) {
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
	 * 'trx_addons-font-variable'.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function block_rest_restore( $response, $handler, $request ): mixed {
		if ( '/elementor/v1/variables/restore' !== $request->get_route() ) {
			return $response;
		}

		$id = $request->get_param( 'id' );
		if ( ! $id ) {
			return $response;
		}

		if ( FontVariablePropType::get_key() !== $this->get_stored_variable_type( $id ) ) {
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
	 * 'trx_addons-font-variable'.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function filter_rest_batch( $response, $handler, $request ): mixed {
		if ( null !== $response ) {
			return $response;
		}

		if ( '/elementor/v1/variables/batch' !== $request->get_route() ) {
			return $response;
		}

		$operations = $request->get_param( 'operations' );
		if ( ! is_array( $operations ) ) {
			return $response;
		}

		$key           = FontVariablePropType::get_key();
		$filterable_ops = [ 'update', 'delete', 'restore' ];
		$filtered      = array_values(
			array_filter(
				$operations,
				function ( $op ) use ( $key, $filterable_ops ) {
					if ( ! isset( $op['type'] ) || ! in_array( $op['type'], $filterable_ops, true ) ) {
						return true;
					}
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
	 * '_elementor_global_variables' post meta directly.
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
	 * Adds FontVariablePropType to the 'font-family' prop in the atomic-widgets
	 * style schema. Mirrors Elementor's own Style_Schema::update_font_family,
	 * which only augments the single 'font-family' key (fonts do not apply to
	 * every string prop, unlike colors which apply everywhere Color_Prop_Type
	 * appears).
	 *
	 * Runs at priority 20, after Elementor's Style_Schema::augment() (priority 10),
	 * so the 'font-family' entry is already wrapped in Union_Prop_Type and we
	 * only need to append to the existing union.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @param array $schema
	 * @return array
	 */
	public function extend_style_schema( array $schema ): array {
		if ( ! isset( $schema['font-family'] ) ) {
			return $schema;
		}

		$font_family = $schema['font-family'];

		if ( $font_family instanceof Union_Prop_Type ) {
			$font_family->add_prop_type( FontVariablePropType::make() );
		} elseif ( $font_family instanceof String_Prop_Type ) {
			$schema['font-family'] = Union_Prop_Type::create_from( $font_family )
				->add_prop_type( FontVariablePropType::make() );
		}

		return $schema;
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
	 *    the TextIcon and creating a new propTypeUtil with our key via
	 *    window.elementorV2.editorProps.createPropUtils(). The value field is
	 *    a disabled TextField showing the font-family value.
	 *
	 * Runs at priority 20, after Elementor's own enqueue (priority 10).
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function enqueue_editor_scripts(): void {
		$key = FontVariablePropType::get_key();

		// Set quota to a high value so canAdd() and canEdit() return true for this
		// type. The restriction is a system policy, not a plan limitation. Actual
		// saving is blocked via service.update interception (JS) and REST API filters (PHP).
		wp_add_inline_script(
			'elementor-common',
			'window.ElementorVariablesQuotaConfig = window.ElementorVariablesQuotaConfig || {};'
			. ' window.ElementorVariablesQuotaConfig["' . $key . '"] = 100000;',
			'before'
		);

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
				// — the Variables Manager attaches its own React Tooltip to the label.
				var _trxFontStyle = document.createElement( \'style\' );
				_trxFontStyle.textContent = \'tr:has([data-trx-system-var]) > td:nth-child(2) { cursor: default !important; }\';
				document.head.appendChild( _trxFontStyle );

				// Block the inline-edit double-click in the capture phase so that the
				// React handler installed by the Variables Manager never sees the event
				// for system-variable rows.
				document.addEventListener( \'dblclick\', function( e ) {
					var row = e.target && e.target.closest ? e.target.closest( \'tr\' ) : null;
					if ( ! row || ! row.querySelector( \'[data-trx-system-var]\' ) ) { return; }
					if ( ! e.target.closest( \'td:nth-child(2)\' ) ) { return; }
					e.stopImmediatePropagation();
					e.preventDefault();
				}, true );

				// ── Prop type utility ─────────────────────────────────────────
				var propTypeUtil = editorProps.createPropUtils( KEY, schema.z.string() );

				// ── Font value field ─────────────────────────────────────────
				// Disabled TextField that simply shows the font-family value —
				// there is no font picker because this type is read-only.
				var FontField = function( props ) {
					return React.createElement( ui.TextField, {
						id: "trx-developer-font-variable-field",
						size: "tiny",
						fullWidth: true,
						value: props.value,
						disabled: 1,
					} );
				};

				// ── Font preview swatch ──────────────────────────────────────
				// Shows "Aa" rendered in the variable\'s font-family so users can
				// visually distinguish each font variable in the list.
				var FontIndicator = function( props ) {
					return React.createElement(
						\'span\',
						{
							\'data-trx-system-var\': \'true\',
							style: {
								display: \'inline-flex\',
								alignItems: \'center\',
								justifyContent: \'center\',
								width: \'1em\',
								height: \'1em\',
								fontFamily: props.value,
								fontSize: \'inherit\',
								lineHeight: 1,
							},
						},
						\'Aa\'
					);
				};

				// ── Register the type ─────────────────────────────────────────
				// No defaultValue — intentionally omitted so that this type is
				// excluded from the "Add variable" menu (filtered by !!defaultValue).
				editorVariables.registerVariableType( {
					key:                 KEY,
					//defaultValue:        \'Roboto\',
					icon:                icons.TextIcon,
					startIcon:           FontIndicator,
					valueField:          FontField,
					propTypeUtil:        propTypeUtil,
					fallbackPropTypeUtil: editorProps.stringPropTypeUtil,
					variableType:        "font",
					isActive:            0,
				} );

				// ── Block modal saves via service.update() ────────────────────
				var svc = editorVariables.service;
				if ( svc && typeof svc.update === "function" ) {
					var _origUpdate = svc.update.bind( svc );
					svc.update = function( id, data ) {
						var vars = svc.variables ? svc.variables() : {};
						if ( vars[ id ] && vars[ id ].type === KEY ) {
							return Promise.resolve( { id: id, variable: vars[ id ] } );
						}
						return _origUpdate( id, data );
					};
				}

				// ── Refresh variables cache when Site Settings panel closes ───
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
