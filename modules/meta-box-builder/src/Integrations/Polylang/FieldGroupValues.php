<?php
namespace MBB\Integrations\Polylang;

use MBB\Control;

class FieldGroupValues {
	const MODES = [ 'translate', 'copy', 'ignore', 'advanced' ];

	public function __construct() {
		add_filter( 'mbb_settings_controls', [ $this, 'add_translation_control' ] );

		// Tell Polylang whether to translate or copy a field
		add_filter( 'pll_copy_post_metas', [ $this, 'copy_post_metas' ], 10, 3 );
	}

	public function add_translation_control( array $controls ): array {
		// Add the control after the custom settings control (index 40)
		$controls[50] = Control::Select( 'translation', [
			'label'   => __( 'Translation', 'meta-box-builder' ),
			'tooltip' => __( 'Choose how to handle field translations in this field group', 'meta-box-builder' ),
			'options' => [
				'ignore'    => __( 'Do not translate any fields in this field group', 'meta-box-builder' ),
				'translate' => __( 'Translate all fields in this field group', 'meta-box-builder' ),
				'copy'      => __( 'Synchronize values across languages', 'meta-box-builder' ),
				'advanced'  => __( 'Set translation mode per field', 'meta-box-builder' ),
			],
		], 'ignore' );

		return $controls;
	}

	public function copy_post_metas( $keys, $sync, $from ): array {
		$fields = $this->get_translatable_fields( $from );

		if ( $sync ) {
			$keys = array_merge( $keys, $fields['copy'] );
		} else {
			$keys = array_merge( $keys, $fields['copy'], $fields['translate'] );
		}

		$keys = array_diff( $keys, $fields['ignore'] );

		return $keys;
	}

	private function get_translatable_fields( $post_id ): array {
		$meta_boxes = rwmb_get_registry( 'meta_box' )->get_by( [ 'object_type' => 'post' ] );
		array_walk( $meta_boxes, 'rwmb_check_meta_box_supports', [ 'post', $post_id ] );
		$meta_boxes = array_filter( $meta_boxes );

		$fields = [
			'copy'      => [],
			'translate' => [],
			'ignore'    => [],
		];
		foreach ( $meta_boxes as $meta_box ) {
			foreach ( $meta_box->fields as $field ) {
				if ( empty( $field['id'] ) ) {
					continue;
				}

				$mode = $meta_box->translation ?: 'ignore';
				if ( $mode === 'advanced' ) {
					$mode = $field['translation'] ?? 'ignore';
				}

				$fields[ $mode ][] = $field['id'];
			}
		}

		return $fields;
	}
}
