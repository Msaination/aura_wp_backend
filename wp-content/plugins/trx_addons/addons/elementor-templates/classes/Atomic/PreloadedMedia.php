<?php
namespace TrxAddons\ElementorTemplates\Atomic;

use Elementor\Modules\AtomicWidgets\PropsResolver\Props_Resolver_Context;
use Elementor\Modules\AtomicWidgets\PropsResolver\Transformer_Base;
use Elementor\Modules\AtomicWidgets\PropsResolver\Transformers_Registry;
use Elementor\Modules\AtomicWidgets\PropTypes\Image_Src_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Svg_Src_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Video_Src_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Image_Attachment_Id_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Video_Attachment_Id_Prop_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Import transformer for the atomic media prop types ('image-src', 'svg-src', 'video-src')
 * of the templates from our templates library.
 *
 * Elementor's own import transformers always download the media from the URL stored in the prop.
 * They reuse an already downloaded file only if the prop also contains a (source site) attachment id,
 * which is not the case for the most of our templates - as a result, a template with 20 equal icons
 * adds 20 equal files to the media library.
 *
 * That's why the media of our templates is downloaded before the import by 'Library::download_images()',
 * which reuses the media downloaded earlier (see the option 'trx_addons_elementor_templates_loaded_media').
 * It leaves the prop with a local attachment id and without a URL - a shape that Elementor's transformers
 * simply drop. This transformer passes such a "preloaded" prop through untouched and delegates any other
 * prop to the transformer registered before it (Elementor's one).
 */
class PreloadedMediaImportTransformer extends Transformer_Base {

	/**
	 * @var string  Class name of the media prop type ('image-src', 'svg-src' or 'video-src')
	 */
	private $prop_type;

	/**
	 * @var string  Class name of the attachment id prop type inside the media prop
	 */
	private $id_prop_type;

	/**
	 * @var Transformer_Base|null  A transformer registered before ours (usually Elementor's one). Used as a fallback.
	 */
	private $next;

	/**
	 * Constructor.
	 *
	 * @param string                $prop_type     Class name of the media prop type.
	 * @param string                $id_prop_type  Class name of the attachment id prop type inside the media prop.
	 * @param Transformer_Base|null $next          A transformer registered before ours, used as a fallback.
	 */
	public function __construct( $prop_type, $id_prop_type, $next = null ) {
		$this->prop_type = $prop_type;
		$this->id_prop_type = $id_prop_type;
		$this->next = $next instanceof Transformer_Base ? $next : null;
	}

	/**
	 * Transform the media prop on the template import.
	 *
	 * @param string                $value    Value of the media prop (already resolved by its shape).
	 * @param Props_Resolver_Context $context  Context of the props resolver.
	 *
	 * @return array|null  Transformed prop or null if the prop should be removed.
	 */
	public function transform( $value, Props_Resolver_Context $context ) {
		// A media file is already downloaded to the media library by 'Library::download_images()':
		// the prop refers to a local attachment and has no URL any more - keep it as is.
		if ( PreloadedMedia::is_media_preloaded() && empty( $value['url']['value'] ) && ! empty( $value['id']['value'] ) ) {
			$prop_type = $this->prop_type;
			$id_prop_type = $this->id_prop_type;
			return $prop_type::generate( array(
				'id'  => $id_prop_type::generate( $value['id']['value'] ),
				'url' => null,
			) );
		}

		return $this->next !== null ? $this->next->transform( $value, $context ) : null;
	}
}

/**
 * PreloadedMedia.
 *
 * Registers import transformers for the atomic media prop types to prevent a repeated download of the media
 * files that are already downloaded to the media library before the import (see 'Library::download_images()').
 *
 * The transformers are registered at priority 20, i.e. AFTER Elementor's own registration (priority 10)
 * and after our 'video-src' transformers (priority 5), because ours must be the outermost one. Each of them
 * wraps the transformer registered before it and delegates all not preloaded media to it, so any future
 * changes in Elementor's transformers are still applied.
 */
class PreloadedMedia {

	/**
	 * @var bool  True while a template with the already downloaded media is being imported.
	 */
	private static $media_preloaded = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'elementor/atomic-widgets/import/transformers/register', array( $this, 'register_import_transformers' ), 20 );
	}

	/**
	 * Mark the media of the imported template as already downloaded to the media library (or not).
	 *
	 * @param bool $preloaded  Whether the media of the imported template is already downloaded.
	 */
	public static function set_media_preloaded( $preloaded ) {
		self::$media_preloaded = (bool) $preloaded;
	}

	/**
	 * Check if the media of the imported template is already downloaded to the media library.
	 *
	 * @return bool  True if the media is already downloaded.
	 */
	public static function is_media_preloaded() {
		return self::$media_preloaded;
	}

	/**
	 * Register the import transformers for the atomic media prop types.
	 *
	 * @param Transformers_Registry $transformers  Registry of the import transformers.
	 */
	public function register_import_transformers( $transformers ) {
		$prop_types = array(
			Image_Src_Prop_Type::class => Image_Attachment_Id_Prop_Type::class,
			Svg_Src_Prop_Type::class   => Image_Attachment_Id_Prop_Type::class,
			Video_Src_Prop_Type::class => Video_Attachment_Id_Prop_Type::class,
		);
		foreach ( $prop_types as $prop_type => $id_prop_type ) {
			if ( ! class_exists( $prop_type ) || ! class_exists( $id_prop_type ) ) {
				continue;
			}
			$key = $prop_type::get_key();
			$transformers->register( $key, new PreloadedMediaImportTransformer( $prop_type, $id_prop_type, $transformers->get( $key ) ) );
		}
	}
}
