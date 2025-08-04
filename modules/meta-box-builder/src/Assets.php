<?php
namespace MBB;

use MBB\Helpers\Data;

class Assets {
	public static function enqueue(): void {
		self::enqueue_meta_box_assets();
		self::enqueue_extensions_assets();
	}

	private static function enqueue_meta_box_assets(): void {
		wp_enqueue_style( 'rwmb', RWMB_CSS_URL . 'style.css', [], RWMB_VER );
		if ( is_rtl() ) {
			wp_enqueue_style( 'rwmb-rtl', RWMB_CSS_URL . 'style-rtl.css', [], RWMB_VER );
		}

		wp_enqueue_style( 'rwmb-input', RWMB_CSS_URL . 'input.css', [], RWMB_VER );
		wp_enqueue_style( 'rwmb-input-list', RWMB_CSS_URL . 'input-list.css', [], RWMB_VER );
		wp_enqueue_style( 'rwmb-select', RWMB_CSS_URL . 'select.css', [], RWMB_VER );
		wp_enqueue_style( 'rwmb-button-group', RWMB_CSS_URL . 'button-group.css', [], RWMB_VER );

		// Required for block settings and color field preview.
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style( 'rwmb-fieldset-text', RWMB_CSS_URL . 'fieldset-text.css', [], RWMB_VER );
		wp_enqueue_style( 'rwmb-image-select', RWMB_CSS_URL . 'image-select.css', [], RWMB_VER );
		wp_enqueue_style( 'rwmb-key-value', RWMB_CSS_URL . 'key-value.css', [], RWMB_VER );

		// Select advanced.
		wp_enqueue_style( 'rwmb-select2', RWMB_CSS_URL . 'select2/select2.css', [], '4.0.10' );
		wp_enqueue_style( 'rwmb-select-advanced', RWMB_CSS_URL . 'select-advanced.css', [], RWMB_VER );
		wp_enqueue_script( 'rwmb-select2', RWMB_JS_URL . 'select2/select2.min.js', [ 'jquery' ], '4.0.10', true );

		// jQueryUI slider.
		$url = RWMB_CSS_URL . 'jqueryui';
		wp_enqueue_style( 'jquery-ui-core', "$url/core.css", [], '1.13.2' );
		wp_enqueue_style( 'jquery-ui-theme', "$url/theme.css", [], '1.13.2' );
		wp_enqueue_style( 'jquery-ui-slider', "$url/slider.css", [ 'jquery-ui-core', 'jquery-ui-theme' ], '1.13.2' );
		wp_enqueue_style( 'rwmb-slider', RWMB_CSS_URL . 'slider.css', [ 'jquery-ui-slider' ], RWMB_VER );

		wp_enqueue_style( 'rwmb-switch', RWMB_CSS_URL . 'switch.css', [], RWMB_VER );
		wp_enqueue_style( 'rwmb-text-list', RWMB_CSS_URL . 'text-list.css', [], RWMB_VER );

		wp_enqueue_editor();
		wp_enqueue_style( 'rwmb-wysiwyg', RWMB_CSS_URL . 'wysiwyg.css', [], RWMB_VER );

		wp_enqueue_style( 'rwmb-range', RWMB_CSS_URL . 'range.css', [], RWMB_VER );
		wp_enqueue_style( 'rwmb-select-tree', RWMB_CSS_URL . 'select-tree.css', [ 'rwmb-select' ], RWMB_VER );
		wp_enqueue_style( 'rwmb-file-input', RWMB_CSS_URL . 'file-input.css', [], RWMB_VER );
		wp_enqueue_style( 'rwmb-media', RWMB_CSS_URL . 'media.css', [], RWMB_VER );
		wp_enqueue_style( 'rwmb-upload', RWMB_CSS_URL . 'upload.css', [ 'rwmb-media' ], RWMB_VER );
		wp_enqueue_style( 'rwmb-divider', RWMB_CSS_URL . 'divider.css', [], RWMB_VER );
		wp_enqueue_style( 'rwmb-heading', RWMB_CSS_URL . 'heading.css', [], RWMB_VER );
		wp_enqueue_style( 'rwmb-background', RWMB_CSS_URL . 'background.css', [], RWMB_VER );

		self::enqueue_datetime_assets();
	}

	private static function enqueue_datetime_assets(): void {
		// jQueryUI base theme: https://github.com/jquery/jquery-ui/tree/1.13.2/themes/base
		$url = RWMB_CSS_URL . 'jqueryui';
		wp_enqueue_style( 'jquery-ui-core', "$url/core.css", [], '1.13.2' );
		wp_enqueue_style( 'jquery-ui-theme', "$url/theme.css", [], '1.13.2' );
		wp_enqueue_style( 'jquery-ui-datepicker', "$url/datepicker.css", [ 'jquery-ui-core', 'jquery-ui-theme' ], '1.13.2' );

		wp_enqueue_style( 'rwmb-date', RWMB_CSS_URL . 'date.css', [ 'jquery-ui-datepicker' ], RWMB_VER );

		// jQueryUI timepicker addon: https://github.com/trentrichardson/jQuery-Timepicker-Addon
		wp_enqueue_style( 'jquery-ui-slider', "$url/slider.css", [ 'jquery-ui-core', 'jquery-ui-theme' ], '1.13.2' );
		wp_enqueue_style( 'jquery-ui-timepicker', "$url/jquery-ui-timepicker-addon.min.css", [ 'rwmb-date', 'jquery-ui-slider' ], '1.6.3' );

		$url = RWMB_JS_URL . 'jqueryui';
		wp_enqueue_script( 'jquery-ui-timepicker', "$url/jquery-ui-timepicker-addon.min.js", [ 'jquery-ui-datepicker', 'jquery-ui-slider' ], '1.6.3', true );
		wp_enqueue_script( 'jquery-ui-timepicker-slider', "$url/jquery-ui-sliderAccess.js", [ 'jquery-ui-datepicker', 'jquery-ui-slider' ], '0.3', true );
	}

	private static function enqueue_extensions_assets(): void {
		self::enqueue_extension_css( 'meta-box-text-limiter', 'text-limiter.css' );
		self::enqueue_extension_css( 'meta-box-tooltip', 'css/tooltip.css' );
		self::enqueue_extension_css( 'meta-box-group', 'group.css' );
	}

	private static function enqueue_extension_css( string $extension, string $relative_path ): void {
		if ( ! Data::is_extension_active( $extension ) ) {
			return;
		}
		$base = self::get_extensions_base();

		$path = $base[0] . "$extension/$relative_path";
		$url  = $base[1] . "$extension/$relative_path";

		if ( file_exists( $path ) ) {
			wp_enqueue_style( $extension, $url, [], filemtime( $path ) );
		}
	}

	private static function get_extensions_base(): array {
		$path = trailingslashit( dirname( MBB_DIR ) );
		$url  = trailingslashit( dirname( MBB_URL ) );
		return [ $path, $url ];
	}
}
