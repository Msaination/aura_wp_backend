<?php
namespace TrxAddons\ElementorTemplates\Atomic;

use Elementor\Plugin;
use Elementor\Modules\AtomicWidgets\PropsResolver\Props_Resolver_Context;
use Elementor\Modules\AtomicWidgets\PropsResolver\Transformer_Base;
use Elementor\Modules\AtomicWidgets\PropsResolver\Transformers_Registry;
use Elementor\Modules\AtomicWidgets\PropTypes\Video_Src_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Video_Attachment_Id_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Url_Prop_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Export transformer for the atomic 'video-src' prop type.
 *
 * Elementor (at least up to 4.2.0) registers import/export transformers only for
 * 'image-src' and 'svg-src'. As a result a self-hosted Atomic Video ('e-self-hosted-video')
 * is exported as a bare attachment id, without a URL, and can never be re-downloaded
 * on the target site. This transformer mirrors Svg_Src_Export_Transformer: it resolves
 * the source attachment id to its URL and embeds that URL into the exported template,
 * so the importing site has something to download.
 */
class VideoSrcExportTransformer extends Transformer_Base {
	public function transform( $value, Props_Resolver_Context $context ) {
		// Already a URL - keep it as is. Note: 'video-src' requires exactly one of id/url
		// to be set (see Video_Src_Prop_Type::validate_value()), so we always emit url-only.
		if ( ! empty( $value['url'] ) ) {
			return Video_Src_Prop_Type::generate( array(
				'id'  => null,
				'url' => $value['url'],
			), $context->is_disabled() );
		}

		// Resolve the attachment id to a URL so the target site can download the video.
		if ( ! empty( $value['id']['value'] ) ) {
			$url = wp_get_attachment_url( $value['id']['value'] );

			if ( ! $url ) {
				return null;
			}

			return Video_Src_Prop_Type::generate( array(
				'id'  => null,
				'url' => Url_Prop_Type::generate( $url ),
			), $context->is_disabled() );
		}

		return null;
	}
}

/**
 * Import transformer for the atomic 'video-src' prop type.
 *
 * Mirrors Image_Src_Import_Transformer: downloads the video referenced by the URL
 * (embedded on export) into the media library and replaces the reference with the
 * new local attachment id. Import_Images::import() is media-agnostic and handles
 * video files (mp4/webm/...) the same way it handles images.
 */
class VideoSrcImportTransformer extends Transformer_Base {
	public function transform( $value, Props_Resolver_Context $context ) {
		if ( empty( $value['url']['value'] ) ) {
			return null;
		}

		$uploaded = Plugin::$instance->templates_manager->get_import_images_instance()->import( array(
			'id'  => $value['id']['value'] ?? null,
			'url' => $value['url']['value'],
		) );

		if ( ! $uploaded ) {
			return null;
		}

		return Video_Src_Prop_Type::generate( array(
			'id'  => Video_Attachment_Id_Prop_Type::generate( $uploaded['id'] ),
			'url' => null,
		) );
	}
}

/**
 * VideoSrc.
 *
 * Registers custom import/export transformers for the atomic 'video-src' prop type,
 * filling the gap in Elementor which ships transformers only for 'image-src' and
 * 'svg-src'. Without them the "Atomic Video" widget does not import its video into
 * the media library.
 *
 * The transformers are registered at priority 5, i.e. BEFORE Elementor's own
 * registration (default priority 10). Since Transformers_Registry::register()
 * overwrites by key, this keeps the fix forward-compatible: if a future Elementor
 * version registers its own 'video-src' transformer, it will overwrite ours.
 */
class VideoSrc {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'elementor/atomic-widgets/export/transformers/register', array( $this, 'register_export_transformer' ), 5 );
		add_action( 'elementor/atomic-widgets/import/transformers/register', array( $this, 'register_import_transformer' ), 5 );
	}

	/**
	 * Register the export transformer for the 'video-src' prop type.
	 *
	 * @param Transformers_Registry $transformers  Registry of export transformers.
	 */
	public function register_export_transformer( $transformers ) {
		if ( ! class_exists( '\Elementor\Modules\AtomicWidgets\PropTypes\Video_Src_Prop_Type' ) ) {
			return;
		}
		$transformers->register( Video_Src_Prop_Type::get_key(), new VideoSrcExportTransformer() );
	}

	/**
	 * Register the import transformer for the 'video-src' prop type.
	 *
	 * @param Transformers_Registry $transformers  Registry of import transformers.
	 */
	public function register_import_transformer( $transformers ) {
		if ( ! class_exists( '\Elementor\Modules\AtomicWidgets\PropTypes\Video_Src_Prop_Type' ) ) {
			return;
		}
		$transformers->register( Video_Src_Prop_Type::get_key(), new VideoSrcImportTransformer() );
	}
}
