<?php
namespace MBB;

class Control {
	/**
	 * Get a control.
	 *
	 * @param string $name      Control name.
	 * @param array  $arguments Control parameters.
	 *
	 * 0 => Setting name
	 * 1 => Props (if array) or label (if string)
	 * 2 => Default value (optional)
	 * 3 => Tab ('general' - default or 'advanced', optional)
	 */
	public static function __callStatic( $name, $arguments ) {
		// Convert title_case to TitleCase.
		$name = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $name ) ) );

		$setting = isset( $arguments[0] ) ? $arguments[0] : '';

		// Allow to pass only label (string) or an array of props.
		$props = isset( $arguments[1] ) ? $arguments[1] : '';
		if ( is_string( $props ) ) {
			$props = [ 'label' => $props ];
		}

		$defaultValue = isset( $arguments[2] ) ? $arguments[2] : self::get_default_value( $name );
		$tab          = isset( $arguments[3] ) ? $arguments[3] : 'general';

		return compact( 'name', 'setting', 'props', 'defaultValue', 'tab' );
	}

	private static function get_default_value( $name ) {
		$defaults = [
			'Checkbox'         => false,
			'Toggle'           => false,
			'KeyValue'         => [],
			'ReactSelect'      => [],
			'IncludeExclude'   => [],
			'ShowHide'         => [],
			'ConditionalLogic' => [],
			'CustomTable'      => [],
			'TextLimiter'      => [],
		];
		return isset( $defaults[ $name ] ) ? $defaults[ $name ] : '';
	}

	/**
	 * A public helper to insert a new control after a specific position.
	 */
	public static function insert( array $controls, string $setting, array $control ): array {
		$new = [];
		foreach ( $controls as $c ) {
			$new[] = $c;
			if ( $c === $setting || ( is_array( $c ) && $c['setting'] === $setting ) ) {
				$new[] = $control;
			}
		}
		return array_values( $new );
	}

	/**
	 * A public helper to insert a new control before a specific position.
	 */
	public static function insert_before( array $controls, string $setting, array $control ): array {
		$new = [];
		foreach ( $controls as $c ) {
			if ( $c === $setting || ( is_array( $c ) && $c['setting'] === $setting ) ) {
				$new[] = $control;
			}
			$new[] = $c;
		}
		return array_values( $new );
	}
}
