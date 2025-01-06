<?php

namespace MBDI;

class Output {
	/**
	 * Get output from field value.
	 *
	 * @param array $args
	 *
	 * @return string        Output.
	 */
	public static function from( array $args = [] ): string {
		if ( empty( $args['value'] ) ) {
			return '';
		}

		$field = $args['field'] ?? [];

		$template = self::get_template( $field['type'] );
		$template = new $template( $args );

		$rendered = $template->render();

		return et_core_esc_previously( $rendered );
	}

	/**
	 * Get template class name from field type.
	 *
	 * @param string $type Field type.
	 *
	 * @return string       Template class name.
	 */
	protected static function get_template( string $type ): string {
		$type     = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $type ) ) );
		$template = __NAMESPACE__ . '\\Templates\\' . $type;

		if ( class_exists( $template ) ) {
			return $template;
		}

		return __NAMESPACE__ . '\\Templates\\Text';
	}
}
