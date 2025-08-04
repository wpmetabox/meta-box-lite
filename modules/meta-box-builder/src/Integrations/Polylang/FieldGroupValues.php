<?php
namespace MBB\Integrations\Polylang;

class FieldGroupValues {
	public function __construct() {
		add_filter( 'mbb_app_data', [ $this, 'add_app_data' ] );

		// Tell Polylang whether to translate or copy a field
		add_filter( 'pll_copy_post_metas', [ $this, 'copy_post_metas' ], 10, 3 );
	}

	public function add_app_data( array $data ): array {
		$data['polylang'] = true;
		return $data;
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
