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
		add_filter( 'mbb_field_controls', [ $this, 'add_field_controls' ] );
		add_filter( 'mbb_field_settings', [ $this, 'parse_field_settings' ] );
	}

	public function add_field_controls( $controls ) {
		$controls[] = Control::Checkbox( 'tooltip_enable', [
			'name'  => 'tooltip[enable]',
			'label' => '<a href="https://metabox.io/plugins/meta-box-tooltip/" target="_blank" rel="nofollow noopenner">' . __( 'Tooltip', 'meta-box-builder' ) . '</a>',
		] );
		$controls[] = Control::Input( 'tooltip_icon', [
			'name'       => 'tooltip[icon]',
			'label'      => '<a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="nofollow noopenner">' . __( 'Icon', 'meta-box-builder' ) . '</a>',
			'tooltip'    => __( 'Can be "info" (default), "help", Dashicons or URL of the custom icon image', 'meta-box-builder' ),
			'dependency' => 'tooltip_enable:true',
		] );
		$controls[] = Control::Select( 'tooltip_position', [
			'name'       => 'tooltip[position]',
			'label'      => __( 'Position', 'meta-box-builder' ),
			'dependency' => 'tooltip_enable:true',
			'options'    => [
				'top'    => __( 'Top', 'meta-box-builder' ),
				'bottom' => __( 'Bottom', 'meta-box-builder' ),
				'left'   => __( 'Left', 'meta-box-builder' ),
				'right'  => __( 'Right', 'meta-box-builder' ),
			],
		], 'top' );
		$controls[] = Control::Input( 'tooltip_content', [
			'name'       => 'tooltip[content]',
			'label'      => __( 'Content', 'meta-box-builder' ),
			'dependency' => 'tooltip_enable:true',
		] );

		return $controls;
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
