<?php
class MB_Rank_Math {
	/**
	 * Store IDs of fields that need to analyze content.
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Enqueue plugin script.
	 *
	 * @param RW_Meta_Box $meta_box The meta box object.
	 */
	public function enqueue( RW_Meta_Box $meta_box) {
		// Only for posts.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! is_object( $screen ) || 'post' !== $screen->base ) {
			return;
		}

		$this->add_fields( $meta_box->fields );

		if ( empty( $this->fields ) ) {
			return;
		}

		// Use helper function to get correct URL to current folder, which can be used in themes/plugins.
		list( , $url ) = RWMB_Loader::get_path( dirname( __FILE__ ) );
		wp_enqueue_script( 'mb-rank-math', $url . 'script.js', array( 'jquery', 'rwmb', 'wp-hooks', 'rank-math-analyzer' ), '1.0.0', true );

		// Send list of fields to JavaScript.
		wp_localize_script( 'mb-rank-math', 'MBRankMath', $this->fields );
	}

	protected function add_fields( $fields ) {
		array_walk( $fields, array( $this, 'add_field' ) );
	}

	protected function add_field( $field ) {
		if ( empty( $field['id_attr'] ) ) {
			$field['id_attr'] = $field['id'];
		}

		// Add sub-fields recursively.
		if ( isset( $field['fields'] ) ) {
			foreach ( $field['fields'] as &$sub_field ) {
				$sub_field['id_attr'] = $field['id_attr'] . '_' . $sub_field['id'];
			}
			$this->add_fields( $field['fields'] );
		}

		// Add the single field.
		if ( $this->is_analyzable( $field ) ) {
			$this->fields[] = $field['id_attr'];
		}
	}

	protected function is_analyzable( $field ) {
		return ! in_array( $field['id'], $this->fields, true ) && ! empty( $field['rank_math_analysis'] );
	}
}
