<?php
namespace MBB\Extensions;

use MBB\Control;
use MBB\Helpers\Data;
use MetaBox\Support\Arr;

class Tooltip {
	public function __construct() {
		if ( ! Data::is_extension_active( 'meta-box-tooltip' ) ) {
			return;
		}
		add_filter( 'mbb_field_controls', [ $this, 'add_field_controls' ], 10, 2 );
		add_filter( 'mbb_field_settings', [ $this, 'parse_field_settings' ] );
	}

	public function add_field_controls( array $controls, string $type ): array {
		if ( in_array( $type, [ 'tab' ] ) ) {
			return $controls;
		}

		$control = Control::TooltipSettings( 'tooltip', '', [
			'enable'  => false,
			'icon'     => 'info',
			'position' => 'top',
			'content'  => '',
		], 'appearance' );

		return Control::insert_before( $controls, 'label_description', $control );
	}

	public function parse_field_settings( $settings ) {
		if ( ! Arr::get( $settings, 'tooltip.enable' ) || ! Arr::get( $settings, 'tooltip.content' ) ) {
			unset( $settings['tooltip'] );
		}
		if ( isset( $settings['tooltip'] ) ) {
			unset( $settings['tooltip']['enable'] );
		}
		return $settings;
	}
}
